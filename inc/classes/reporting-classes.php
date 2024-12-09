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
      mouseMovements INT
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

  public function track($data,$auth) {
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
      'mouseMovements'
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
      ':mouseMovements' => count($data['mouseMovements'])
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
      $execute[':username'] = 'unauthenticated';
    }
    $valueArray = array_map(function($value) {
      return ':' . $value;
    }, $prepare);
    $stmt = $this->db->prepare("INSERT INTO reporting_tracking (".implode(", ",$prepare).") VALUES (".implode(', ', $valueArray).")");
    $stmt->execute($execute);
  }

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

  public function getAssessmentReports($granularity,$type = 'all',$realm = 'all',$user = 'all',$customer = 'all',$start = null,$end = null) {
    $execute = [];
    switch ($granularity) {
      case 'today':
        $Select = 'SELECT * FROM reporting_assessments WHERE date(created) = date("now")';
        break;
      case 'thisWeek':
        $Select = 'SELECT * FROM reporting_assessments WHERE strftime("%Y-%W", created) = strftime("%Y-%W","now")';
        break;
      case 'thisMonth':
        $Select = 'SELECT * FROM reporting_assessments WHERE strftime("%Y-%m", created) = strftime("%Y-%m","now")';
        break;
      case 'thisYear':
        $Select = 'SELECT * FROM reporting_assessments WHERE strftime("%Y", created) = strftime("%Y","now")';
        break;
      case 'last30Days':
        $Select = 'SELECT * FROM reporting_assessments WHERE created >= date("now", "-30 days")';
        break;
      case 'lastMonth':
        $Select = 'SELECT * FROM reporting_assessments WHERE strftime("%Y-%m", created) = strftime("%Y-%m", date("now", "-1 month"))';
        break;
      case 'lastYear':
        $Select = 'SELECT * FROM reporting_assessments WHERE strftime("%Y", created) = strftime("%Y", date("now", "-1 year"))';
        break;
      case 'custom':
        if ($start != null && $end != null) {
          $StartDateTime = (new DateTime($start))->format('Y-m-d H:i:s');
          $EndDateTime = (new DateTime($end))->format('Y-m-d H:i:s');
          $execute[':start'] = $StartDateTime;
          $execute[':end'] = $EndDateTime;
          $Select = 'SELECT * FROM reporting_assessments WHERE created > :start AND created < :end';
        } else {
          return array(
            'Status' => 'Error',
            'Message' => 'Start and/or End date missing'
          );
        }
        break;
      case 'all':
        $Select = 'SELECT * FROM reporting_assessments';
        break;
    }

    if ($type != 'all') {
      $Select = $Select.' AND type = :type';
      $execute[':type'] = $type;
    }
    if ($realm != 'all') {
      $Select = $Select.' AND realm = :realm';
      $execute[':realm'] = $realm;
    }
    if ($user != 'all') {
      $Select = $Select.' AND apiuser = :apiuser';
      $execute[':apiuser'] = $user;
    }
    if ($customer != 'all') {
      $Select = $Select.' AND customer = :customer';
      $execute[':customer'] = $customer;
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

  public function getAssessmentReportsStats($granularity,$type,$realm,$user,$customer,$start,$end) {
    $data = $this->getAssessmentReports($granularity,$type,$realm,$user,$customer,$start,$end);
    $filteredData = $this->filterDataByGranularity($data, $granularity);
    $summary = $this->summarizeByTypeAndDate($filteredData, $granularity);
    return $summary;
  }

  // Function to filter data based on granularity
  private function filterDataByGranularity($data, $granularity) {
    $filteredData = [];
    $currentDate = new DateTime();

    foreach ($data as $item) {
      $createdDate = new DateTime($item['created']);
      switch ($granularity) {
        case 'today':
          if ($createdDate->format('Y-m-d') == $currentDate->format('Y-m-d')) {
              $filteredData[] = $item;
          }
          break;
        case 'thisWeek':
          if ($createdDate->format('W') == $currentDate->format('W') && $createdDate->format('Y') == $currentDate->format('Y')) {
              $filteredData[] = $item;
          }
          break;
        case 'thisMonth':
          if ($createdDate->format('Y-m') == $currentDate->format('Y-m')) {
              $filteredData[] = $item;
          }
          break;
        case 'thisYear':
          if ($createdDate->format('Y') == $currentDate->format('Y')) {
              $filteredData[] = $item;
          }
          break;
        case 'last30Days':
          $thirtyDaysAgo = (new DateTime())->modify('-30 days');
          if ($createdDate >= $thirtyDaysAgo) {
            $filteredData[] = $item;
          }
          break;
        case 'lastMonth':
          $lastMonth = (clone $currentDate)->modify('-1 month');
          if ($createdDate->format('Y-m') == $lastMonth->format('Y-m')) {
              $filteredData[] = $item;
          }
          break;
        case 'lastYear':
          $lastYear = (clone $currentDate)->modify('-1 year');
          if ($createdDate->format('Y') == $lastYear->format('Y')) {
              $filteredData[] = $item;
          }
          break;
        case 'all':
          $filteredData[] = $item;
          break;
        default:
          $filteredData[] = $item;
          break;
      }
    }
    return $filteredData;
  }

  // Function to summarize data by type and date
  private function summarizeByTypeAndDate($data, $granularity) {
    $summary = [];
    foreach ($data as $item) {
      $type = $item['type'];
      $createdDate = new DateTime($item['created']);

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
}