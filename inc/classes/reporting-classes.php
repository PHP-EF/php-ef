<?php

class Reporting {
    private $db;
  
    public function __construct($db) {
      $this->db = $db;
      $this->createAssessmentReportingTable();
    }
  
    private function createAssessmentReportingTable() {
      // Create users table if it doesn't exist
      $this->db->exec("CREATE TABLE IF NOT EXISTS reporting_assessments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        type TEXT,
        userid INTEGER,
        apiuser TEXT,
        customer TEXT,
        created DATETIME
      )");
    }

    public function newReportEntry($type,$apiuser,$customer,$userid = '') {
        $stmt = $this->db->prepare("INSERT INTO reporting_assessments (type, apiuser, customer, created) VALUES (:type, :apiuser, :customer, :created)");
        $stmt->execute([':type' => $type,':apiuser' => $apiuser,':customer' => $customer,':created' => date('Y-m-d H:i:s')]);
    }
  
    public function getAssessmentReportTypes() {
      try {
        $stmt = $this->db->prepare("SELECT DISTINCT type FROM reporting_assessments");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
      } catch (PDOException $e) {
          return array(
              'Status' => 'Error',
              'Message' => $e
          );
      }
    }
  
    public function getAssessmentReports($granularity) {
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
        case 'all':
          $Select = 'SELECT * FROM reporting_assessments';
          break;
      }
      if (isset($Select)) {
        try {
          $stmt = $this->db->prepare($Select);
          $stmt->execute();
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
      //$stmt = $this->db->prepare('SELECT SUM(CASE WHEN DATE(created) = DATE("now") THEN 1 ELSE 0 END) AS count_today, SUM(CASE WHEN strftime("%Y-%m", created) = strftime("%Y-%m", "now") THEN 1 ELSE 0 END) AS count_this_month, SUM(CASE WHEN strftime("%Y", created) = strftime("%Y", "now") THEN 1 ELSE 0 END) AS count_this_year FROM reporting_assessments');
      $stmt = $this->db->prepare('SELECT type, SUM(CASE WHEN DATE(created) = DATE("now") THEN 1 ELSE 0 END) AS count_today, SUM(CASE WHEN strftime("%Y-%m", created) = strftime("%Y-%m", "now") THEN 1 ELSE 0 END) AS count_this_month, SUM(CASE WHEN strftime("%Y", created) = strftime("%Y", "now") THEN 1 ELSE 0 END) AS count_this_year FROM reporting_assessments GROUP BY type UNION ALL SELECT "Total" AS type, SUM(CASE WHEN DATE(created) = DATE("now") THEN 1 ELSE 0 END) AS count_today, SUM(CASE WHEN strftime("%Y-%m", created) = strftime("%Y-%m", "now") THEN 1 ELSE 0 END) AS count_this_month, SUM(CASE WHEN strftime("%Y", created) = strftime("%Y", "now") THEN 1 ELSE 0 END) AS count_this_year FROM reporting_assessments');
      $stmt->execute();
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAssessmentReportsStats($granularity) {
        $data = $this->getAssessmentReports($granularity);
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
              case 'lastYear':
                  $dateKey = $createdDate->format('Y-m');
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