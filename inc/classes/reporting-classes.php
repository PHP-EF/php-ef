<?php

class Reporting {
  private $db;
  private $core;

  public function __construct($core,$db) {
    $this->db = $db;
    $this->core = $core;
    $this->createReportingTables();
  }

  private function createReportingTables() {
    // Create tracking table if it doesn't exist
    $this->db->exec("CREATE TABLE IF NOT EXISTS reporting_tracking (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      username TEXT,
      ipAddress TEXT,
      tId TEXT,
      browser TEXT,
      os TEXT,
      scheme TEXT,
      domain TEXT,
      path TEXT,
      pageCategory TEXT,
      pageName TEXT,
      timeSpent INT,
      clicks INT,
      mouseMovements INT,
      dateTime DATETIME
    )");

    // Create assessments table if it doesn't exist
    $this->db->exec("CREATE TABLE IF NOT EXISTS reporting_assessments (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      type TEXT,
      userid INTEGER,
      apiuser TEXT,
      customer TEXT,
      realm TEXT,
      created DATETIME,
      uuid TEXT,
      status TEXT
    )");
  }

  // Web Tracking
  public function track($data,$auth) {
    $DateTime = (new DateTime())->format('Y-m-d H:i:s');
    $execute = [];
    $prepare = [
      'tId',
      'username',
      'ipAddress',
      'browser',
      'os',
      'scheme',
      'domain',
      'path',
      'timeSpent',
      'clicks',
      'mouseMovements',
      'dateTime'
    ];
    $execute = [
      ':tId' => $data['tId'],
      ':ipAddress' => $auth['IPAddress'],
      ':browser' => $data['browserInfo']['browserName'],
      ':os' => $data['browserInfo']['osName'],
      ':scheme' => $data['urlComponents']['protocol'],
      ':domain' => $data['urlComponents']['host'],
      ':path' => $data['urlComponents']['pathname'],
      ':timeSpent' => $data['timeSpent'],
      ':clicks' => count($data['clicks']),
      ':mouseMovements' => count($data['mouseMovements']),
      ':dateTime' => $DateTime
    ];
    if ($data['pageDetails'] != null) {
      $prepare[] = 'pageCategory';
      $execute[':pageCategory'] = $data['pageDetails']['pageCategory'];
      $prepare[] = 'pageName';
      $execute[':pageName'] = $data['pageDetails']['pageName'];
    }
    if ($auth['Authenticated']) {
      $execute[':username'] = $auth['Username'];
    } else {
      $execute[':username'] = 'None';
    }
    $valueArray = array_map(function($value) {
      return ':' . $value;
    }, $prepare);
    $stmt = $this->db->prepare("INSERT INTO reporting_tracking (".implode(", ",$prepare).") VALUES (".implode(', ', $valueArray).")");
    $stmt->execute($execute);
  }

  public function getTrackingRecords($granularity,$filters,$start,$end) {
    $execute = [];
    $Select = $this->sqlSelectByGranularity($granularity,'dateTime','reporting_tracking',$start,$end);

    if ($granularity == 'custom') {
      if ($start != null && $end != null) {
        $StartDateTime = (new DateTime($start))->format('Y-m-d H:i:s');
        $EndDateTime = (new DateTime($end))->format('Y-m-d H:i:s');
        $execute[':start'] = $StartDateTime;
        $execute[':end'] = $EndDateTime;
      }
    }

    if ($filters['page'] != 'all') {
      $Select = $Select.' AND pageName = :pageName';
      $execute[':pageName'] = $filters['page'];
    }
    if ($filters['browser'] != 'all') {
      $Select = $Select.' AND browser = :browser';
      $execute[':browser'] = $filters['browser'];
    }
    if ($filters['os'] != 'all') {
      $Select = $Select.' AND os = :os';
      $execute[':os'] = $filters['os'];
    }
    
    if (isset($Select)) {
      if (isset($Select['Status'])) {
        return $Select;
      } else {
        try {
          $stmt = $this->db->prepare($Select);
          $stmt->execute($execute);
          return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return array(
                'Status' => 'Error',
                'Message' => $e
            );
        }        
      }
    } else {
      return array(
        'Status' => 'Error',
        'Message' => 'Invalid Granularity'
      );
    }
  }

  public function getTrackingSummary() {
    $stmt = $this->db->prepare('SELECT "Total" AS type, SUM(CASE WHEN DATE(dateTime) = DATE("now") THEN 1 ELSE 0 END) AS count_today, SUM(CASE WHEN strftime("%Y-%m", dateTime) = strftime("%Y-%m", "now") THEN 1 ELSE 0 END) AS count_this_month, SUM(CASE WHEN strftime("%Y", dateTime) = strftime("%Y", "now") THEN 1 ELSE 0 END) AS count_this_year, COUNT(DISTINCT CASE WHEN DATE(dateTime) = DATE("now") THEN tId ELSE NULL END) AS unique_visitors_today, COUNT(DISTINCT CASE WHEN strftime("%Y-%m", dateTime) = strftime("%Y-%m", "now") THEN tId ELSE NULL END) AS unique_visitors_this_month, COUNT(DISTINCT CASE WHEN strftime("%Y", dateTime) = strftime("%Y", "now") THEN tId ELSE NULL END) AS unique_visitors_this_year FROM reporting_tracking;');
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getTrackingStats($granularity,$filters,$start,$end) {
    $data = $this->getTrackingRecords($granularity,$filters,$start,$end);
    $summary = $this->summarizeByDate($data, $granularity, 'dateTime');
    return $summary;
  }

  // Web Tracking End

  // Assessment Reports
  public function getReportById($id) {
    $stmt = $this->db->prepare("SELECT * FROM reporting_assessments WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($report) {
      return $report;
    } else {
      return false;
    }
  }

  public function getReportByUuid($uuid) {
    $stmt = $this->db->prepare("SELECT * FROM reporting_assessments WHERE uuid = :uuid");
    $stmt->execute([':uuid' => $uuid]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($report) {
      return $report;
    } else {
      return false;
    }
  }

  public function newReportEntry($type,$apiuser,$customer,$realm,$uuid,$status) {
    $stmt = $this->db->prepare("INSERT INTO reporting_assessments (type, apiuser, customer, realm, created, uuid, status) VALUES (:type, :apiuser, :customer, :realm, :created, :uuid, :status)");
    $stmt->execute([':type' => $type,':apiuser' => $apiuser,':customer' => $customer,':realm' => $realm,':created' => date('Y-m-d H:i:s'),':uuid' => $uuid,':status' => $status]);
    return $this->db->lastInsertId();
  }

  public function updateReportEntry($id,$type,$apiuser,$customer,$realm,$uuid,$status) {
    if ($this->getReportById($id)) {
      $prepare = [];
      $execute = [];
      $execute[':id'] = $id;
      if ($type !== null) {
        $prepare[] = 'type = :type';
        $execute[':type'] = $type;
      }
      if ($apiuser !== null) {
        $prepare[] = 'apiuser = :apiuser';
        $execute[':apiuser'] = $apiuser;
      }
      if ($customer !== null) {
        $prepare[] = 'customer = :customer';
        $execute[':customer'] = $customer;
      }
      if ($realm !== null) {
        $prepare[] = 'realm = :realm';
        $execute[':realm'] = $realm;
      }
      if ($uuid !== null) {
        $prepare[] = 'uuid = :uuid';
        $execute[':uuid'] = $uuid;
      }
      if ($status !== null) {
        $prepare[] = 'status = :status';
        $execute[':status'] = $status;
      }
      $stmt = $this->db->prepare('UPDATE reporting_assessments SET '.implode(", ",$prepare).' WHERE id = :id');
      $stmt->execute($execute);
      return array(
        'Status' => 'Success',
        'Message' => 'Report Record updated successfully'
      );
    } else {
      return array(
        'Status' => 'Error',
        'Message' => 'Report Record does not exist'
      );
    }
  }

  public function updateReportEntryStatus($uuid,$status) {
    $stmt = $this->db->prepare('UPDATE reporting_assessments SET status = :status WHERE uuid = :uuid');
    $stmt->execute([':uuid' => $uuid,':status' => $status]);
  }

  public function getAssessmentReports($granularity,$filters,$start = null,$end = null) {
    $execute = [];
    $Select = $this->sqlSelectByGranularity($granularity,'created','reporting_assessments',$start,$end);
    if ($granularity == 'custom') {
      if ($start != null && $end != null) {
        $StartDateTime = (new DateTime($start))->format('Y-m-d H:i:s');
        $EndDateTime = (new DateTime($end))->format('Y-m-d H:i:s');
        $execute[':start'] = $StartDateTime;
        $execute[':end'] = $EndDateTime;
      }
    }
    if ($filters['type'] != 'all') {
      $Select = $Select.' AND type = :type';
      $execute[':type'] = $filters['type'];
    }
    if ($filters['realm'] != 'all') {
      $Select = $Select.' AND realm = :realm';
      $execute[':realm'] = $filters['realm'];
    }
    if ($filters['user'] != 'all') {
      $Select = $Select.' AND apiuser = :apiuser';
      $execute[':apiuser'] = $filters['user'];
    }
    if ($filters['customer'] != 'all') {
      $Select = $Select.' AND customer = :customer';
      $execute[':customer'] = $filters['customer'];
    }
    if (isset($Select)) {
      try {
        $stmt = $this->db->prepare($Select);
        $stmt->execute($execute);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
      } catch (PDOException $e) {
          return array(
              'Status' => 'Error',
              'Message' => $e
          );
      }
    } else {
      return array(
        'Status' => 'Error',
        'Message' => 'Invalid Granularity'
      );
    }
  }

  public function getAssessmentReportsSummary() {
    // Include grouping by type
    //$stmt = $this->db->prepare('SELECT type, SUM(CASE WHEN DATE(created) = DATE("now") THEN 1 ELSE 0 END) AS count_today, SUM(CASE WHEN strftime("%Y-%m", created) = strftime("%Y-%m", "now") THEN 1 ELSE 0 END) AS count_this_month, SUM(CASE WHEN strftime("%Y", created) = strftime("%Y", "now") THEN 1 ELSE 0 END) AS count_this_year, COUNT(DISTINCT CASE WHEN DATE(created) = DATE("now") THEN apiuser ELSE NULL END) AS unique_apiusers_today, COUNT(DISTINCT CASE WHEN strftime("%Y-%m", created) = strftime("%Y-%m", "now") THEN apiuser ELSE NULL END) AS unique_apiusers_this_month, COUNT(DISTINCT CASE WHEN strftime("%Y", created) = strftime("%Y", "now") THEN apiuser ELSE NULL END) AS unique_apiusers_this_year, COUNT(DISTINCT CASE WHEN DATE(created) = DATE("now") THEN customer ELSE NULL END) AS unique_customers_today, COUNT(DISTINCT CASE WHEN strftime("%Y-%m", created) = strftime("%Y-%m", "now") THEN customer ELSE NULL END) AS unique_customers_this_month, COUNT(DISTINCT CASE WHEN strftime("%Y", created) = strftime("%Y", "now") THEN customer ELSE NULL END) AS unique_customers_this_year FROM reporting_assessments GROUP BY type UNION ALL SELECT "Total" AS type, SUM(CASE WHEN DATE(created) = DATE("now") THEN 1 ELSE 0 END) AS count_today, SUM(CASE WHEN strftime("%Y-%m", created) = strftime("%Y-%m", "now") THEN 1 ELSE 0 END) AS count_this_month, SUM(CASE WHEN strftime("%Y", created) = strftime("%Y", "now") THEN 1 ELSE 0 END) AS count_this_year, COUNT(DISTINCT CASE WHEN DATE(created) = DATE("now") THEN apiuser ELSE NULL END) AS unique_apiusers_today, COUNT(DISTINCT CASE WHEN strftime("%Y-%m", created) = strftime("%Y-%m", "now") THEN apiuser ELSE NULL END) AS unique_apiusers_this_month, COUNT(DISTINCT CASE WHEN strftime("%Y", created) = strftime("%Y", "now") THEN apiuser ELSE NULL END) AS unique_apiusers_this_year, COUNT(DISTINCT CASE WHEN DATE(created) = DATE("now") THEN customer ELSE NULL END) AS unique_customers_today, COUNT(DISTINCT CASE WHEN strftime("%Y-%m", created) = strftime("%Y-%m", "now") THEN customer ELSE NULL END) AS unique_customers_this_month, COUNT(DISTINCT CASE WHEN strftime("%Y", created) = strftime("%Y", "now") THEN customer ELSE NULL END) AS unique_customers_this_year FROM reporting_assessments;');
    $stmt = $this->db->prepare('SELECT "Total" AS type, SUM(CASE WHEN DATE(created) = DATE("now") THEN 1 ELSE 0 END) AS count_today, SUM(CASE WHEN strftime("%Y-%m", created) = strftime("%Y-%m", "now") THEN 1 ELSE 0 END) AS count_this_month, SUM(CASE WHEN strftime("%Y", created) = strftime("%Y", "now") THEN 1 ELSE 0 END) AS count_this_year, COUNT(DISTINCT CASE WHEN DATE(created) = DATE("now") THEN apiuser ELSE NULL END) AS unique_apiusers_today, COUNT(DISTINCT CASE WHEN strftime("%Y-%m", created) = strftime("%Y-%m", "now") THEN apiuser ELSE NULL END) AS unique_apiusers_this_month, COUNT(DISTINCT CASE WHEN strftime("%Y", created) = strftime("%Y", "now") THEN apiuser ELSE NULL END) AS unique_apiusers_this_year, COUNT(DISTINCT CASE WHEN DATE(created) = DATE("now") THEN customer ELSE NULL END) AS unique_customers_today, COUNT(DISTINCT CASE WHEN strftime("%Y-%m", created) = strftime("%Y-%m", "now") THEN customer ELSE NULL END) AS unique_customers_this_month, COUNT(DISTINCT CASE WHEN strftime("%Y", created) = strftime("%Y", "now") THEN customer ELSE NULL END) AS unique_customers_this_year FROM reporting_assessments;');
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getAssessmentReportsStats($granularity,$filters,$start,$end) {
    $data = $this->getAssessmentReports($granularity,$filters,$start,$end);
    $summary = $this->summarizeByTypeAndDate($data, $granularity);
    return $summary;
  }

  // Function to summarize data by type and date
  private function summarizeByTypeAndDate($data, $granularity) {
    $summary = [];
    foreach ($data as $item) {
      $type = $item['type'];
      $dateKey = $this->summerizeDateByGranularity($item,$granularity,'created');
      if (!isset($summary[$type])) {
          $summary[$type] = [];
      }
      if (!isset($summary[$type][$dateKey])) {
          $summary[$type][$dateKey] = 0;
      }
      $summary[$type][$dateKey]++;
    }
    return $summary;
  }
  // Assessment Reports End


  // ** Shared Functions ** //
  // Function to summarize data date
  private function summarizeByDate($data, $granularity, $dateField) {
    $summary = [];
    foreach ($data as $item) {
      $dateKey = $this->summerizeDateByGranularity($item,$granularity,$dateField);
      if (!isset($summary[$dateKey])) {
          $summary[$dateKey] = 0;
      }
      $summary[$dateKey]++;
    }
    return $summary;
  }

  // Function to summarize date by granularity
  private function summerizeDateByGranularity($item, $granularity, $dateField) {
    $createdDate = new DateTime($item[$dateField]);
    switch ($granularity) {
      case 'today':
        $dateKey = $createdDate->format('Y-m-d H:00');
        break;
      case 'thisWeek':
        $dateKey = $createdDate->format('Y-m-d');
        break;
      case 'thisMonth':
        $dateKey = $createdDate->format('Y-m-d');
        break;
      case 'last30Days':
        $dateKey = $createdDate->format('Y-m-d');
        break;
      case 'lastMonth':
        $dateKey = $createdDate->format('Y-m-d');
        break;
      case 'thisYear':
        $dateKey = $createdDate->format('Y-m');
        break;
      case 'lastYear':
        $dateKey = $createdDate->format('Y-m');
        break;
      default:
        $dateKey = $createdDate->format('Y-m-d');
        break;
    }
    return $dateKey;
  }

  private function sqlSelectByGranularity($granularity,$dateField,$table,$start,$end) {
    switch ($granularity) {
      case 'today':
        $Select = 'SELECT * FROM '.$table.' WHERE date('.$dateField.') = date("now")';
        break;
      case 'thisWeek':
        $Select = 'SELECT * FROM '.$table.' WHERE strftime("%Y-%W", '.$dateField.') = strftime("%Y-%W","now")';
        break;
      case 'thisMonth':
        $Select = 'SELECT * FROM '.$table.' WHERE strftime("%Y-%m", '.$dateField.') = strftime("%Y-%m","now")';
        break;
      case 'thisYear':
        $Select = 'SELECT * FROM '.$table.' WHERE strftime("%Y", '.$dateField.') = strftime("%Y","now")';
        break;
      case 'last30Days':
        $Select = 'SELECT * FROM '.$table.' WHERE '.$dateField.' >= date("now", "-30 days")';
        break;
      case 'lastMonth':
        $Select = 'SELECT * FROM '.$table.' WHERE strftime("%Y-%m", '.$dateField.') = strftime("%Y-%m", date("now", "-1 month"))';
        break;
      case 'lastYear':
        $Select = 'SELECT * FROM '.$table.' WHERE strftime("%Y", '.$dateField.') = strftime("%Y", date("now", "-1 year"))';
        break;
      case 'custom':
        if ($start != null && $end != null) {
          $Select = 'SELECT * FROM '.$table.' WHERE '.$dateField.' > :start AND '.$dateField.' < :end';
        } else {
          return array(
            'Status' => 'Error',
            'Message' => 'Start and/or End date missing'
          );
        }
        break;
      case 'all':
        $Select = 'SELECT * FROM '.$table.'';
        break;
    }
    return $Select;
  }
}