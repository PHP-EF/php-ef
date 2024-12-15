<?php
// **
// USED TO DEFINE PLUGIN INFORMATION & CLASS
// **

// PLUGIN INFORMATION
$GLOBALS['plugins']['ib'] = [ // Plugin Name
	'name' => 'ib', // Plugin Name
	'author' => 'TehMuffinMoo', // Who wrote the plugin
	'category' => 'Infoblox', // One to Two Word Description
	'link' => 'https://github.com/TehMuffinMoo/ib-sa-report', // Link to plugin info
	'version' => '1.0.0', // SemVer of plugin
	'image' => 'logo.png', // 1:1 non transparent image for plugin
	'settings' => true, // does plugin need a settings modal?
	'api' => '/api/plugins/ib/settings', // api route for settings page (All Lowercase)
];

use Label305\PptxExtractor\Basic\BasicExtractor;
use Label305\PptxExtractor\Basic\BasicInjector;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ibPlugin extends ib {
	public $SecurityAssessment;

	public function __construct() {
	   parent::__construct();
	} 

	public function _pluginGetSettings() {
        return include_once(__DIR__ . DIRECTORY_SEPARATOR . 'config.php');
	}

	public function getDir() {
		return array(
			'Files' => __DIR__.'/../../../files',
			'Assets' => __DIR__.'/../../../assets'
		);
	}
}

class ibPortal extends ibPlugin {
	protected $APIKey;
	protected $Realm;

	public function __construct() {
		parent::__construct();
	}

	public function SetCSPConfiguration($APIKey = null,$Realm = "US") {
		$this->Realm = $Realm;
		if (isset($_COOKIE['crypt'])) {
			$this->APIKey = decrypt($_COOKIE['crypt'],$this->config->getConfig("Security","salt"));
		} else {
			$this->APIKey = $APIKey;
		}
		if ($this->CheckAPIKey()) {
			return true;
		}
	}

	public function GetCSPConfiguration($Uri = null) {
		$CSPHeaders = array(
			'Authorization' => "Token {$this->APIKey}",
			'Content-Type' => "application/json"
		);
	  
		$ErrorOnEmpty = true;
	  
		if ($Uri == null || strpos($Uri,"https://csp.") === FALSE) {
		  if ($this->Realm == "US") {
			$Url = "https://csp.infoblox.com/".$Uri;
		  } elseif ($this->Realm == "EU") {
			$Url = "https://csp.eu.infoblox.com/".$Uri;
		  } else {
			echo 'Error. Invalid Realm';
			return false;
		  }
		} else {
		  $Url = $Uri;
		}
	  
		$Options = array(
		  'timeout' => $this->config->getConfig("System","CURL-Timeout"),
		  'connect_timeout' => $this->config->getConfig("System","CURL-ConnectTimeout")
		);
	  
		return array(
		  "APIKey" => $this->APIKey,
		  "Realm" => $this->Realm,
		  "Url" => $Url,
		  "Options" => $Options,
		  "Headers" => $CSPHeaders
		);
	}
	  
	function QueryCSPMultiRequestBuilder($Method = 'GET', $Uri = '', $Data = null, $Id = "") {
		return array(
			"Id" => $Id,
			"Method" => $Method,
			"Uri" => $Uri,
			"Data" => $Data
		);
	}
	
	function QueryCSPMulti($MultiQuery) {
		$CSPConfig = $this->GetCSPConfiguration(null);
		
		// Prepare the requests
		$requests = [];
		foreach ($MultiQuery as $Multi) {
			if (isset($Multi['Data'])) { $Data = $Multi['Data']; } else { $Data = null; }
			$requests[] = array(
				"id" => $Multi['Id'],
				"type" => constant('Requests::' . strtoupper($Multi['Method'])),
				"url" => $CSPConfig['Url'].$Multi['Uri'],
				"data" => $Data,
				"headers" => $CSPConfig['Headers'],
				"options" => $CSPConfig['Options']
			);
		}
		// Send the requests simultaneously
		$responses = Requests::request_multiple($requests);
		
		$Results = [];
		$IdStepIn = 0;
		foreach ($responses as $index => $response) {
			if ($requests[$index]['id'] != "") {
				$Id = $requests[$index]['id'];
			} else {
				$Id = $IdStepIn;
				$IdStepIn++;
			}
			$Results[$Id] = array(
				'Response' => $response,
				'Body' => json_decode($response->body)
			);
		}
		return $Results;
	}
	
	function QueryCSP($Method, $Uri, $Data = null) {
		$CSPConfig = $this->GetCSPConfiguration($Uri);
		try {
			switch ($Method) {
				case 'get':
					$Result = Requests::get($CSPConfig['Url'], $CSPConfig['Headers'], $CSPConfig['Options']);
					break;
				case 'post':
					if ($Data != null) {
						$Result = Requests::post($CSPConfig['Url'], $CSPConfig['Headers'], json_encode($Data,JSON_UNESCAPED_SLASHES), $CSPConfig['Options']);
					} else {
						$Result = Requests::post($CSPConfig['Url'], $CSPConfig['Headers'], $Data, $CSPConfig['Options']);
					}
					break;
				case 'put':
					$Result = Requests::put($CSPConfig['Url'], $CSPConfig['Headers'], json_encode($Data,JSON_UNESCAPED_SLASHES), $CSPConfig['Options']);
					break;
				case 'patch':
					$Result = Requests::patch($CSPConfig['Url'], $CSPConfig['Headers'], json_encode($Data,JSON_UNESCAPED_SLASHES), $CSPConfig['Options']);
					break;
				case 'delete':
					$Result = Requests::delete($CSPConfig['Url'], $CSPConfig['Headers'], $CSPConfig['Options']);
					break;
			}
		} catch (Exception $e) {
			return array(
				'Status' => 'Error',
				'Error' => $e->getMessage()
			);
		}
		
		$LogArr = array(
			"Method" => $Method,
			"Url" => $CSPConfig['Url'],
			"Options" => $CSPConfig['Options']
		);
		
		if ($Result) {
			switch ($Result->status_code) {
				case '401':
					$LogArr['Error'] = "Invalid API Key.";
					$this->logging->writeLog("CSP","Failed to authenticate to the CSP","debug",$LogArr);
					$this->api->setAPIResponse('Error','Invalid API Key');
					return false;
				default:
					$Output = json_decode($Result->body);
					$this->logging->writeLog("CSP","Queried the CSP","debug",$LogArr);
					return $Output;
				}
		} elseif ($ErrorOnEmpty) {
			echo "Warning. No results from API.".$CSPConfig->Url;
		}
	}
	
	public function QueryCubeJSMulti($MultiQuery) {
		$BuildQuery = [];
		foreach ($MultiQuery as $Id => $Query) {
			$BuildQuery[] = $this->QueryCSPMultiRequestBuilder("get","api/cubejs/v1/query?query=".urlencode($Query),null,$Id);
		}
		$Results = $this->QueryCSPMulti($BuildQuery);
		return $Results;
	}
	
	public function QueryCubeJS($Query) {
		$BuildQuery = urlencode($Query);
		$Result = $this->QueryCSP("get","api/cubejs/v1/query?query=".$BuildQuery);
		return $Result;
	}

	public function CheckAPIKey() {
		if ($this->APIKey) {
			if ($this->GetCSPCurrentUser()) {
				return true;
			}
		}
		return false;
	}
	
	public function GetCSPCurrentUser() {
		$UserInfo = $this->QueryCSP("get","v2/current_user");
		return $UserInfo;
	}
}

class TemplateConfig extends ibPlugin {
    public function __construct() {
		parent::__construct();
        // Create or open the SQLite database
        $this->createTemplateTable();

		if (!is_dir($this->getDir()['Files'].'/templates')) {
			mkdir($this->getDir()['Files'].'/templates', 0755, true);
		}
    }

    private function createTemplateTable() {
        // Create template table if it doesn't exist
        $this->db->exec("CREATE TABLE IF NOT EXISTS templates (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          Status TEXT,
          FileName TEXT,
          TemplateName TEXT,
          Description TEXT,
          ThreatActorSlide INTEGER,
          Created DATE
        )");
    }

    public function getTemplateConfigs() {
        $stmt = $this->db->prepare("SELECT * FROM templates");
        $stmt->execute();
        $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $templates;
    }

    public function getTemplateConfigById($id) {
        $stmt = $this->db->prepare("SELECT * FROM templates WHERE id = :id");
        $stmt->execute([':id' => $id]);

        $templates = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($templates) {
          return $templates;
        } else {
          return false;
        }
    }

    public function getActiveTemplate() {
        $stmt = $this->db->prepare("SELECT * FROM templates WHERE Status = :Status");
        $stmt->execute([':Status' => 'Active']);

        $template = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($template) {
          return $template;
        } else {
          return false;
        }
    }

    public function newTemplateConfig($Status,$FileName,$TemplateName,$Description,$ThreatActorSlide) {
        try {
            // Check if filename already exists
            $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM templates WHERE FileName = :FileName OR TemplateName = :TemplateName");
            $checkStmt->execute([':FileName' => $FileName, ':TemplateName' => $TemplateName]);
            if ($checkStmt->fetchColumn() > 0) {
				$this->api->setAPIResponse('Error','Template Name already exists');
				return false;
            }
        } catch (PDOException $e) {
			$this->api->setAPIResponse('Error',$e);
        }
        $stmt = $this->db->prepare("INSERT INTO templates (Status, FileName, TemplateName, Description, ThreatActorSlide, Created) VALUES (:Status, :FileName, :TemplateName, :Description, :ThreatActorSlide, :Created)");
        try {
            $CurrentDate = new DateTime();
            $stmt->execute([':Status' => urldecode($Status), ':FileName' => urldecode($FileName), ':TemplateName' => urldecode($TemplateName), ':Description' => urldecode($Description), ':ThreatActorSlide' => urldecode($ThreatActorSlide), ':Created' => $CurrentDate->format('Y-m-d H:i:s')]);
            $id = $this->db->lastInsertId();
            if ($Status == 'Active') {
                $statusStmt = $this->db->prepare("SELECT id FROM templates WHERE Status == :Status");
                $statusStmt->execute([':Status' => 'Active']);
                $ActiveTemplates = $statusStmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($ActiveTemplates as $AT) {
                    $setStatusStmt = $this->db->prepare("UPDATE templates SET Status = :Status WHERE id == :id AND id != :thisid");
                    $setStatusStmt->execute([':Status' => 'Inactive',':id' => $AT['id'],':thisid' => $id]);
                }
            }
            $this->logging->writeLog("Templates","Created New Security Assessment Template","info");
			$this->api->setAPIResponseMessage('Template added successfully');
        } catch (PDOException $e) {
			$this->api->setAPIResponse('Error',$e);
        }
    }

    public function setTemplateConfig($id,$Status,$FileName,$TemplateName,$Description,$ThreatActorSlide) {
        $templateConfig = $this->getTemplateConfigById($id);
        if ($templateConfig) {
            if ($FileName !== null || $TemplateName !== null) {
                try {
                    // Check if new filename/template name already exists
                    $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM templates WHERE (FileName = :FileName OR TemplateName = :TemplateName) AND id != :id");
                    $checkStmt->execute([':FileName' => $FileName, ':TemplateName' => $TemplateName, ':id' => $id]);
                    if ($checkStmt->fetchColumn() > 0) {
						$this->api->setAPIResponse('Error','Template name already exists');
						return false;
                    }
                } catch (PDOException $e) {
					$this->api->setAPIResponse('Error',$e);
                }
            }

            $prepare = [];
            $execute = [];
            $execute[':id'] = $id;
            if ($Status !== null) {
                if ($Status == 'Active') {
                    $statusStmt = $this->db->prepare("SELECT id FROM templates WHERE Status == :Status");
                    $statusStmt->execute([':Status' => 'Active']);
                    $ActiveTemplates = $statusStmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($ActiveTemplates as $AT) {
                        $setStatusStmt = $this->db->prepare("UPDATE templates SET Status = :Status WHERE id == :id AND id != :thisid");
                        $setStatusStmt->execute([':Status' => 'Inactive',':id' => $AT['id'],':thisid' => $id]);
                    }
                }
                $prepare[] = 'Status = :Status';
                $execute[':Status'] = urldecode($Status);
            }
            if ($FileName !== null) {
                $prepare[] = 'FileName = :FileName';
                $execute[':FileName'] = urldecode($FileName);
            }
            if ($TemplateName !== null) {
                $prepare[] = 'TemplateName = :TemplateName';
                $execute[':TemplateName'] = urldecode($TemplateName);
            }
            if ($Description !== null) {
                $prepare[] = 'Description = :Description';
                $execute[':Description'] = urldecode($Description);
            }
            if ($ThreatActorSlide !== null) {
                $prepare[] = 'ThreatActorSlide = :ThreatActorSlide';
                $execute[':ThreatActorSlide'] = urldecode($ThreatActorSlide);
            }
            $stmt = $this->db->prepare('UPDATE templates SET '.implode(", ",$prepare).' WHERE id = :id');
            $stmt->execute($execute);
            if ($FileName !== null) {
                $uploadDir = $this->getDir()['Files'].'/templates/';
                if ($templateConfig['FileName']) {
                    if (file_exists($uploadDir.$templateConfig['FileName'])) {
                        if (!unlink($uploadDir.$templateConfig['FileName'])) {
							$this->api->setAPIResponse('Error','Failed to delete old template file');
							return false;
                        }
                    }
                }
            }
            $this->logging->writeLog("Templates","Updated Security Assessment Template: ".$TemplateName,"info");
			$this->api->setAPIResponseMessage('Template updated successfully');
        } else {
			$this->api->setAPIResponse('Error','Template does not exist');
        }
    }

    public function removeTemplateConfig($id) {
        $templateConfig = $this->getTemplateConfigById($id);
        if ($templateConfig) {
          $uploadDir = $this->getDir()['Files'].'/templates/';
          if ($templateConfig['FileName']) {
            if (file_exists($uploadDir.$templateConfig['FileName'])) {
                if (!unlink($uploadDir.$templateConfig['FileName'])) {
					$this->api->setAPIResponse('Error','Failed to delete template file');
					return false;
                }
            }
          }
          $stmt = $this->db->prepare("DELETE FROM templates WHERE id = :id");
          $stmt->execute([':id' => $id]);
          if ($this->getTemplateConfigById($id)) {
			$this->api->setAPIResponse('Error','Failed to delete template');
          } else {
            $this->logging->writeLog("Templates","Removed Security Assessment Template: ".$id,"warning");
			$this->api->setAPIResponseMessage('Template deleted successfully');
          }
        }
    }
}

class AssessmentReporting extends ibPlugin {
    public function __construct() {
		parent::__construct();
        // Create or open the SQLite database
        $this->createReportingTables();
    }

	private function createReportingTables() {
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
		$Select = $this->reporting->sqlSelectByGranularity($granularity,'created','reporting_assessments',$start,$end);
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
			$dateKey = $this->reporting->summerizeDateByGranularity($item,$granularity,'created');
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

class ThreatActors extends ibPortal {
	public function __construct() {
		parent::__construct();
		$this->createThreatActorTable();

		$imagesDir = $this->getDir()['Assets'].'/images/Threat Actors/';
		if (!is_dir($imagesDir)) {
			mkdir($imagesDir, 0755, true);
		}
		$uploadDir = $this->getDir()['Assets'].'/images/Threat Actors/Uploads/';
		if (!is_dir($uploadDir)) {
			mkdir($uploadDir, 0755, true);
		}
	}

    private function createThreatActorTable() {
        // Create users table if it doesn't exist
        $this->db->exec("CREATE TABLE IF NOT EXISTS threat_actors (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          Name TEXT UNIQUE,
          SVG TEXT,
          PNG TEXT,
          URLStub TEXT
        )");
    }

    public function getThreatActorConfigById($id) {
        $stmt = $this->db->prepare("SELECT * FROM threat_actors WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $threatActors = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($threatActors) {
          return $threatActors;
        } else {
          return false;
        }
    }

    public function getThreatActorConfigByName($name) {
        $stmt = $this->db->prepare("SELECT * FROM threat_actors WHERE LOWER(Name) = LOWER(:name)");
        $stmt->execute([':name' => $name]);
        $threatActors = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($threatActors) {
          return $threatActors;
        } else {
          return false;
        }
    }

    public function getThreatActorConfigs() {
        $stmt = $this->db->prepare("SELECT * FROM threat_actors");
        $stmt->execute();
        $threatActors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $threatActors;
    }

    public function newThreatActorConfig($Name,$SVG,$PNG,$URLStub) {
        if ($Name != "") {
            $ThreatActorConfig = $this->getThreatActorConfigs();
            try {
                // Check if filename already exists
                $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM threat_actors WHERE Name = :Name");
                $checkStmt->execute([':Name' => urldecode($Name)]);
                if ($checkStmt->fetchColumn() > 0) {
					$this->api->setAPIResponse('Error','Threat Actor Already Exists');
					return false;
                }
            } catch (PDOException $e) {
				$this->api->setAPIResponse('Error',$e);
            }
            $stmt = $this->db->prepare("INSERT INTO threat_actors (Name, SVG, PNG, URLStub) VALUES (:Name, :SVG, :PNG, :URLStub)");
            try {
                $stmt->execute([':Name' => urldecode($Name), ':SVG' => urldecode($SVG), ':PNG' => urldecode($PNG), ':URLStub' => urldecode($URLStub)]);
                $this->logging->writeLog("ThreatActors","Created new Threat Actor: ".$Name,"info");
				$this->api->setAPIResponseMessage('Threat Actor added successfully');
            } catch (PDOException $e) {
				$this->api->setAPIResponse('Error',$e);
            }
        } else {
			$this->api->setAPIResponse('Error','Threat Actor name missing');
        }
    }

    public function setThreatActorConfig($id,$Name,$SVG,$PNG,$URLStub) {
        if ($Name != "") {
            $ThreatActorConfig = $this->getThreatActorConfigById($id);
            if ($ThreatActorConfig) {
                $prepare = [];
                $execute = [];
                $execute[':id'] = $id;
                if ($Name !== null) {
                    try {
                        // Check if new filename/template name already exists
                        $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM threat_actors WHERE Name = :Name AND id != :id");
                        $checkStmt->execute([':Name' => $Name, ':id' => $id]);
                        if ($checkStmt->fetchColumn() > 0) {
							$this->api->setAPIResponse('Error','Threat Actor name already exists');
							return false;
                        }
                    } catch (PDOException $e) {
						$this->api->setAPIResponse('Error',$e);
                    }
                    $prepare[] = 'Name = :Name';
                    $execute[':Name'] = urldecode($Name);
                }
                if ($SVG !== null) {
                    $prepare[] = 'SVG = :SVG';
                    $execute[':SVG'] = urldecode($SVG);
                }
                if ($PNG !== null) {
                    $prepare[] = 'PNG = :PNG';
                    $execute[':PNG'] = urldecode($PNG);
                }
                if ($URLStub !== null) {
                    $prepare[] = 'URLStub = :URLStub';
                    $execute[':URLStub'] = urldecode($URLStub);
                }
                $stmt = $this->db->prepare('UPDATE threat_actors SET '.implode(", ",$prepare).' WHERE id = :id');
                $stmt->execute($execute);
                $this->logging->writeLog("ThreatActors","Updated Threat Actor: ".$Name,"info");
				$this->api->setAPIResponseMessage('Threat Actor updated successfully');
            } else {
				$this->api->setAPIResponse('Error','Threat Actor does not exist');
            }
        } else {
			$this->api->setAPIResponse('Error','Threat Actor name missing');
        }
    }

    public function removeThreatActorConfig($id) {
        $ThreatActorConfig = $this->getThreatActorConfigById($id);
        if ($ThreatActorConfig) {
          $uploadDir = $this->getDir()['Assets'].'/images/Threat Actors/Uploads/';
          if ($ThreatActorConfig['PNG']) {
            if (file_exists($uploadDir.$ThreatActorConfig['PNG'])) {
                if (!unlink($uploadDir.$ThreatActorConfig['PNG'])) {
					$this->api->setAPIResponse('Error','Failed to delete PNG file');
                    return false;
                }
            }
          }
          if ($ThreatActorConfig['SVG']) {
            if (file_exists($uploadDir.$ThreatActorConfig['SVG'])) {
                if (!unlink($uploadDir.$ThreatActorConfig['SVG'])) {
					$this->api->setAPIResponse('Error','Failed to delete SVG file');
                    return false;
                }
            }
          }
          $stmt = $this->db->prepare("DELETE FROM threat_actors WHERE id = :id");
          $stmt->execute([':id' => $id]);
          if ($this->getThreatActorConfigById($id)) {
			$this->api->setAPIResponse('Error','Failed to delete Threat Actor');
			return false;
          } else {
            $this->logging->writeLog("ThreatActors","Removed Threat Actor: ".$id,"warning");
			$this->api->setAPIResponseMessage('Successfully deleted Threat Actor');
			return false;
          }
        }
    }

	// Function called from API
	public function GetThreatActors($data) {
		if ((isset($data['APIKey']) OR isset($_COOKIE['crypt'])) AND isset($data['StartDateTime']) AND isset($data['EndDateTime']) AND isset($data['Realm'])) {
			$UserInfo = $this->GetCSPCurrentUser();
			if (isset($UserInfo->result->name)) {
				$this->logging->writeLog("ThreatActors",$UserInfo->result->name." queried list of Threat Actors","info");
				$Actors = $this->GetB1ThreatActorIds($data['StartDateTime'],$data['EndDateTime']);
				if (!isset($Actors->Error)) {
					$this->api->setAPIResponseData($this->GetB1ThreatActorsById($Actors,$data['unnamed'],$data['substring']));
				} else {
					$this->api->setAPIResponse('Error','Unable to get list of Threat Actors','502',$Actors);
				};
			}
		} else {
			$this->api->setAPIResponse('Error','API Key, Start & End Date and Realm are required fields.');
		}
	}

	// Get list of Threat Actor IDs from CubeJS
	public function GetB1ThreatActorIds($StartDateTime,$EndDateTime) {
		$StartDimension = str_replace('Z','',$StartDateTime);
		$EndDimension = str_replace('Z','',$EndDateTime);
		// Workaround
		//$Actors = QueryCubeJS('{"segments":[],"timeDimensions":[{"dimension":"PortunusAggIPSummary.timestamp","granularity":null,"dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"ungrouped":false,"order":{"PortunusAggIPSummary.timestampMax":"desc"},"measures":["PortunusAggIPSummary.count"],"dimensions":["PortunusAggIPSummary.threat_indicator","PortunusAggIPSummary.actor_id"],"limit":1000,"filters":[{"and":[{"operator":"set","member":"PortunusAggIPSummary.threat_indicator"},{"operator":"set","member":"PortunusAggIPSummary.actor_id"}]}]}');
		$Actors = $this->QueryCubeJS('{"measures":[],"segments":[],"dimensions":["ThreatActors.storageid","ThreatActors.ikbactorid","ThreatActors.domain","ThreatActors.ikbfirstsubmittedts","ThreatActors.vtfirstdetectedts","ThreatActors.firstdetectedts","ThreatActors.lastdetectedts"],"timeDimensions":[{"dimension":"ThreatActors.lastdetectedts","granularity":null,"dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"ungrouped":false}');
		if (isset($Actors->result->data)) {
		  return $Actors->result->data;
		} else {
		  return $Actors;
		}
	  }
	
	public function GetB1ThreatActor($ActorID,$Page = 1) {
		$Results = $this->QueryCSP('get','/tide/threat-enrichment/clusterfox/actors/search?actor_id='.$ActorID.'&page='.$Page);
		if ($Results) {
			$this->api->setAPIResponseData($Results);
		} else {
			$this->api->setAPIResponse('Error','Unable to retrieve list of Threat Actors');
		}
	}
	  
	function GetB1ThreatActorsById($Actors,$unnamed,$substring) {
		$ActorArr = json_decode(json_encode($Actors),true);
		$UniqueIds = array_unique(array_column($ActorArr, 'ThreatActors.ikbactorid'));
		$Results = array();
		$ActorInfo = array();
		$ArrayChunk = array_chunk($UniqueIds, 10);
		$Requests = [];
		foreach ($ArrayChunk as $Chunk) {
		  	$CsvString = implode(',',$Chunk);
		  	$Requests[] = $this->QueryCSPMultiRequestBuilder('get','/tide/threat-enrichment/clusterfox/actors/search?actor_id='.$CsvString);
		}
		$Responses = $this->QueryCSPMulti($Requests);
		foreach ($Responses as $Response) {
		  foreach ($Response['Body']->actors as $Actor) {
				$ObservedIOCKeys = array_keys(array_column($ActorArr, 'ThreatActors.ikbactorid'),$Actor->actor_id);
				$ObservedIOCCount = count($ObservedIOCKeys);
				$ObservedIOCs = [];
			foreach ($ObservedIOCKeys as $ObservedIOCKey) {
			  	$ObservedIOCs[] = $ActorArr[$ObservedIOCKey];
			}
			if ($Actor->actor_id != "" && $Actor->actor_name != "") {
				// Ignore Unnamed & Substring Actors
				$UnnamedActor = str_starts_with($Actor->actor_name,'unnamed_actor');
				$SubstringActor = str_starts_with($Actor->actor_name,'substring');
				if (($UnnamedActor && $unnamed == 'true') || ($SubstringActor && $substring == 'true') || (!$UnnamedActor && !$SubstringActor)) {
					$NewArr = array(
						'actor_id' => $Actor->actor_id,
						'actor_name' => $Actor->actor_name,
						'actor_description' => $Actor->actor_description,
						'related_count' => $Actor->related_count,
						'observed_count' => $ObservedIOCCount,
						'observed_iocs' => $ObservedIOCs
					);
					if (isset($Actor->external_references)) {
						$NewArr['external_references'] = $Actor->external_references;
					} else {
						$NewArr['external_references'] = [];
					}
					if (isset($Actor->infoblox_references)) {
						$NewArr['infoblox_references'] = $Actor->infoblox_references;
					} else {
						$NewArr['infoblox_references'] = [];
					}
					if (isset($Actor->purpose)) {
						$NewArr['purpose'] = $Actor->purpose;
					} else {
						$NewArr['purpose'] = [];
					}
					if (isset($Actor->ttp)) {
						$NewArr['ttp'] = $Actor->ttp;
					} else {
						$NewArr['ttp'] = [];
					}
						array_push($Results,$NewArr);
					}
				}
		  	}
		}
		return $Results;
	}

	function GetB1ThreatActorsByIdEU($Actors) {
		$UniqueIds = array_unique(array_column($Actors, 'PortunusAggIPSummary.actor_id'));
		$Results = array();
		$ActorInfo = array();
		foreach ($UniqueIds as $UniqueId) {
		  // Workaround for problematic Threat Actors
		  // These timeout when using the 'batch_actor_summary_with_indicators' API Endpoint
		  $WorkaroundArr = array(
			//'c2303ad0-0f9e-4349-a71e-821794e202bd' // Revolver Rabbit - TIDE-850
		  );
		  if (in_array($UniqueId, $WorkaroundArr)) {
			$ActorQuery = QueryCSP('get','tide-ng-threat-actor/v1/actor?_filter=id=="'.$UniqueId.'" and page==1');
			if (isset($ActorQuery)) {
			  $NewArr = array(
				'actor_id' => $ActorQuery->actor_id,
				'actor_name' => $ActorQuery->actor_name,
				'actor_description' => $ActorQuery->actor_description,
				'related_count' => $ActorQuery->related_count,
				'related_indicators_with_dates' => null,
				'related_indicators' => $ActorQuery->related_indicators,
			  );
			  if (isset($ActorQuery->external_references)) {
				$NewArr['external_references'] = $ActorQuery->external_references;
			  } else {
				$NewArr['external_references'] = [];
			  }
			  if (isset($ActorQuery->infoblox_references)) {
				$NewArr['infoblox_references'] = $ActorQuery->infoblox_references;
			  } else {
				$NewArr['infoblox_references'] = [];
			  }
			  array_push($Results,$NewArr);
			}
		  // End of Workaround
		  } else {
			$Ids = array();
			$Ids[] = array_keys(array_column($Actors, 'PortunusAggIPSummary.actor_id'),$UniqueId);
			$Indicators = array();
			foreach ($Ids as $Id) {
			  foreach ($Id as $Idsub) {
				$Indicators[] = $Actors[$Idsub]->{'PortunusAggIPSummary.threat_indicator'};
			  }
			}
			$ActorInfo[] = array(
			  "actor_id" => $Actors[$Id[0]]->{'PortunusAggIPSummary.actor_id'},
			  "indicators" => $Indicators
			);
		  }
		}
	  
		$ArrayChunk = array_chunk($ActorInfo, 5);
		$Requests = [];
		foreach ($ArrayChunk as $Chunk) {
		  $Query = json_encode(array(
			'actor_indicators' => $Chunk
		  ));
		  $Requests[] = $this->QueryCSPMultiRequestBuilder('post','tide-ng-threat-actor/v1/batch_actor_summary_with_indicators',$Query);
		}
	  
		$Responses = $this->QueryCSPMulti($Requests);
		foreach ($Responses as $Response) {
		  if (isset($Response['Body']->actor_responses)) {
			foreach ($Response['Body']->actor_responses as $AR) {
			  $NewArr = array(
				'actor_id' => $AR->actor_id,
				'actor_name' => $AR->actor_name,
				'actor_description' => $AR->actor_description,
				'related_count' => $AR->related_count,
				'related_indicators_with_dates' => $AR->related_indicators_with_dates,
				'related_indicators' => null,
			  );
			  if (isset($AR->external_references)) {
				$NewArr['external_references'] = $AR->external_references;
			  } else {
				$NewArr['external_references'] = [];
			  }
			  if (isset($AR->infoblox_references)) {
				$NewArr['infoblox_references'] = $AR->infoblox_references;
			  } else {
				$NewArr['infoblox_references'] = [];
			  }
			  array_push($Results,$NewArr);
			}
		  }
		}
		return $Results;
	}
}

class SecurityAssessment extends ibPortal {
	private $AssessmentReporting;
	private $TemplateConfig;
	private $ThreatActors;

	public function __construct() {
		parent::__construct();
		if (!is_dir($this->getDir()['Files'].'/reports')) {
			mkdir($this->getDir()['Files'].'/reports', 0755, true);
		}
		$this->AssessmentReporting = new AssessmentReporting();
		$this->TemplateConfig = new TemplateConfig();
	}

	public function generateSecurityReport($StartDateTime,$EndDateTime,$Realm,$UUID,$unnamed,$substring) {
		// Pass APIKey & Realm to ThreatActors Class
		$this->ThreatActors = new ThreatActors();
		$this->ThreatActors->SetCSPConfiguration($this->APIKey,$Realm);
		// Check Active Template Exists
		if (!$this->TemplateConfig->getActiveTemplate()) {
			$this->api->setAPIResponse('Error','No active template selected');
			return false;
		}
		// Check API Key is valid & get User Info
		$UserInfo = $this->GetCSPCurrentUser();
		if (is_array($UserInfo) && isset($UserInfo['Error'])) {
			$Status = $UserInfo['Status'];
			$Error = $UserInfo['Error'];
		} else {
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array(
				'result' => 'Success',
				'message' => 'Started'
			));
			fastcgi_finish_request();
	
			// Logging / Reporting
			$AccountInfo = $this->QueryCSP("get","v2/current_user/accounts");
			$CurrentAccount = $AccountInfo->results[array_search($UserInfo->result->account_id, array_column($AccountInfo->results, 'id'))];
			$this->logging->writeLog("Assessment",$UserInfo->result->name." requested a security assessment report for: ".$CurrentAccount->name,"info");
			$ReportRecordId = $this->AssessmentReporting->newReportEntry('Security Assessment',$UserInfo->result->name,$CurrentAccount->name,$Realm,$UUID,"Started");
	
			// Set Progress
			$Progress = 0;
	
			// Set Time Dimensions
			$StartDimension = str_replace('Z','',$StartDateTime);
			$EndDimension = str_replace('Z','',$EndDateTime);
	
			$HighRiskCategoryList = implode('","',[
				"Risky Activity",
				"Suspicious and Malicious Software",
				// "Uncategorized",
				"Adult",
				"Abortion",
				"Abortion Pro Choice",
				"Abortion Pro Life",
				"Child Inappropriate",
				"Gambling",
				"Gay",
				"Lingerie",
				"Nudity",
				"Pornography",
				"Profanity",
				"R-Rated",
				"Sex & Erotic",
				"Sex Education",
				"Tobacco",
				"Anonymizer",
				"Criminal Skills",
				"Self Harm",
				"Criminal Activities - Other",
				"Illegal Drugs",
				"Marijuana",
				"Child Abuse Images",
				"Hacking",
				"Hate Speech",
				"Piracy & Copyright Theft",
				"Torrent Repository",
				"Terrorism",
				"Peer-to-Peer",
				"Violence",
				"Weapons",
				"School Cheating",
				"Ad Fraud",
				"Botnet",
				"Command and Control Centers",
				"Compromised & Links To Malware",
				"Malware Call-Home",
				"Malware Distribution Point",
				"Phishing/Fraud",
				"Spam URLs",
				"Spyware & Questionable Software",
				"Cryptocurrency Mining",
				"Sexuality",
				"Parked & For Sale Domains"
			]);
			$Progress = $this->writeProgress($UUID,$Progress,"Collecting Metrics");
			$CubeJSRequests = array(
				'TopThreatFeeds' => '{"measures":["PortunusAggSecurity.feednameCount"],"dimensions":["PortunusAggSecurity.feed_name"],"timeDimensions":[{"dimension":"PortunusAggSecurity.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggSecurity.type","operator":"equals","values":["2"]},{"member":"PortunusAggSecurity.severity","operator":"equals","values":["High"]}],"limit":"10","ungrouped":false}',
				'TopDetectedProperties' => '{"measures":["PortunusDnsLogs.tpropertyCount"],"dimensions":["PortunusDnsLogs.tproperty"],"timeDimensions":[{"dimension":"PortunusDnsLogs.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusDnsLogs.type","operator":"equals","values":["2"]},{"member":"PortunusDnsLogs.feed_name","operator":"notEquals","values":["Public_DOH","public-doh","Public_DOH_IP","public-doh-ip"]},{"member":"PortunusDnsLogs.severity","operator":"notEquals","values":["Low","Info"]}],"limit":"10","ungrouped":false}',
				// Switch to using the Web Content Discovery APIs, rather than those called via the Dashboard.
				//'ContentFiltration' => '{"measures":["PortunusAggWebcontent.categoryCount"],"dimensions":["PortunusAggWebcontent.category"],"timeDimensions":[{"dimension":"PortunusAggWebcontent.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[],"limit":"10","ungrouped":false}',
				// Switch to using data from High-Risk websites instead, this is now an unneccessary API call
				//'ContentFiltration' => '{"timeDimensions":[{"dimension":"PortunusAggWebContentDiscovery.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"measures":["PortunusAggWebContentDiscovery.count"],"dimensions":["PortunusAggWebContentDiscovery.domain_category"],"filters":[{"member":"PortunusAggWebContentDiscovery.domain_category","operator":"set"},{"member":"PortunusAggWebContentDiscovery.domain_category","operator":"notEquals","values":[""]}],"order":{"PortunusAggWebContentDiscovery.count":"desc"},"limit":"10"}',
				'InsightDistribution' => '{"measures":["InsightsAggregated.count"],"dimensions":["InsightsAggregated.threatType"],"filters":[{"member":"InsightsAggregated.insightStatus","operator":"equals","values":["Active"]}]}',
				'DNSFirewallActivity' => '{"measures":["PortunusAggSecurity.severityCount"],"dimensions":["PortunusAggSecurity.severity"],"timeDimensions":[{"dimension":"PortunusAggSecurity.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggSecurity.type","operator":"equals","values":["2","3"]},{"member":"PortunusAggSecurity.severity","operator":"equals","values":["High","Medium","Low"]}],"limit":"3","ungrouped":false}',
				'DNSActivity' => '{"measures":["PortunusAggInsight.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"equals","values":["1"]}],"limit":"1","ungrouped":false}',
				'SOCInsights' => '{"measures":["InsightsAggregated.count","InsightsAggregated.mostRecentAt","InsightsAggregated.startedAtMin"],"dimensions":["InsightsAggregated.priorityText"],"filters":[{"member":"InsightsAggregated.insightStatus","operator":"equals","values":["Active"]}],"timezone":"UTC"}',
				'SecurityEvents' => '{"measures":["PortunusAggInsight.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"contains","values":["2","3"]}],"limit":"1","ungrouped":false}',
				'DataExfilEvents' => '{"measures":["PortunusAggInsight.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"equals","values":["4"]},{"member":"PortunusAggInsight.tclass","operator":"equals","values":["TI-DNST"]}],"ungrouped":false}',
				'ZeroDayDNSEvents' => '{"measures":["PortunusAggInsight.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"equals","values":["2","3"]},{"member":"PortunusAggInsight.tclass","operator":"equals","values":["Zero Day DNS"]}],"ungrouped":false}',
				'SuspiciousEvents' => '{"measures":["PortunusAggInsight.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"equals","values":["2"]},{"member":"PortunusAggInsight.tclass","operator":"equals","values":["Suspicious"]}],"ungrouped":false}',
				'HighRiskWebsites' => '{"timeDimensions":[{"dimension":"PortunusAggWebContentDiscovery.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"measures":["PortunusAggWebContentDiscovery.count","PortunusAggWebContentDiscovery.deviceCount"],"dimensions":["PortunusAggWebContentDiscovery.domain_category"],"order":{"PortunusAggWebContentDiscovery.count":"desc"},"filters":[{"member":"PortunusAggWebContentDiscovery.domain_category","operator":"equals","values":["'.$HighRiskCategoryList.'"]}]}',
				'DOHEvents' => '{"measures":["PortunusAggInsight.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"equals","values":["2"]},{"member":"PortunusAggInsight.tproperty","operator":"equals","values":["DoHService"]}],"ungrouped":false}',
				'NODEvents' => '{"measures":["PortunusAggInsight.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"equals","values":["2"]},{"member":"PortunusAggInsight.tproperty","operator":"equals","values":["NewlyObservedDomains"]}],"ungrouped":false}',
				'DGAEvents' => '{"measures":["PortunusAggInsight.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"or":[{"member":"PortunusAggInsight.tproperty","operator":"equals","values":["suspicious_rdga","suspicious_dga","DGA"]},{"member":"PortunusAggInsight.tclass","operator":"equals","values":["DGA","MalwareC2DGA"]}]},{"member":"PortunusAggInsight.type","operator":"equals","values":["2","3"]}],"ungrouped":false}',
				'UniqueApplications' => '{"measures":["PortunusAggAppDiscovery.requests"],"dimensions":["PortunusAggAppDiscovery.app_name","PortunusAggAppDiscovery.app_approval"],"timeDimensions":[{"dimension":"PortunusAggAppDiscovery.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggAppDiscovery.app_name","operator":"set"},{"member":"PortunusAggAppDiscovery.app_name","operator":"notEquals","values":[""]}],"order":{}}',
				'ThreatActivityEvents' => '{"measures":["PortunusAggInsight.threatCount"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"equals","values":["2"]},{"member":"PortunusAggInsight.severity","operator":"equals","values":["High","Medium","Low"]},{"member":"PortunusAggInsight.threat_indicator","operator":"notEquals","values":[""]}],"limit":"1","ungrouped":false}',
				'DNSFirewallEvents' => '{"measures":["PortunusAggInsight.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"and":[{"member":"PortunusAggInsight.type","operator":"equals","values":["2"]},{"or":[{"member":"PortunusAggInsight.severity","operator":"equals","values":["High","Medium","Low"]},{"and":[{"member":"PortunusAggInsight.severity","operator":"equals","values":["Info"]},{"member":"PortunusAggInsight.policy_action","operator":"equals","values":["Block","Log"]}]}]},{"member":"PortunusAggInsight.confidence","operator":"equals","values":["High","Medium","Low"]}]}],"limit":"1","ungrouped":false}',
				'WebContentEvents' => '{"measures":["PortunusAggWebcontent.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggWebcontent.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggWebcontent.type","operator":"equals","values":["3"]},{"member":"PortunusAggWebcontent.category","operator":"notEquals","values":[null]}],"limit":"1","ungrouped":false}',
				'Devices' => '{"measures":["PortunusAggInsight.deviceCount"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"contains","values":["2","3"]},{"member":"PortunusAggInsight.severity","operator":"contains","values":["High","Medium","Low"]}],"limit":"1","ungrouped":false}',
				'Users' => '{"measures":["PortunusAggInsight.userCount"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"contains","values":["2","3"]}],"limit":"1","ungrouped":false}',
				'ThreatInsight' => '{"measures":[],"dimensions":["PortunusDnsLogs.tproperty"],"timeDimensions":[{"dimension":"PortunusDnsLogs.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusDnsLogs.type","operator":"equals","values":["4"]}],"limit":"10000","ungrouped":false}',
				'ThreatView' => '{"measures":["PortunusAggInsight.tpropertyCount"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"equals","values":["2"]}],"limit":"1","ungrouped":false}',
				'Sources' => '{"measures":["PortunusAggSecurity.networkCount"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggSecurity.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggSecurity.type","operator":"contains","values":["2","3"]}],"limit":"1","ungrouped":false}',
				// Workaround for removal of batch threat actor enrichment
				//'ThreatActors' => '{"segments":[],"timeDimensions":[{"dimension":"PortunusAggIPSummary.timestamp","granularity":null,"dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"ungrouped":false,"order":{"PortunusAggIPSummary.timestampMax":"desc"},"measures":["PortunusAggIPSummary.count"],"dimensions":["PortunusAggIPSummary.threat_indicator","PortunusAggIPSummary.actor_id"],"limit":1000,"filters":[{"and":[{"operator":"set","member":"PortunusAggIPSummary.threat_indicator"},{"operator":"set","member":"PortunusAggIPSummary.actor_id"}]}]}'
				// Removed due to workaround below
				//'ThreatActors' => '{"measures":[],"segments":[],"dimensions":["ThreatActors.storageid","ThreatActors.ikbactorid","ThreatActors.domain","ThreatActors.ikbfirstsubmittedts","ThreatActors.vtfirstdetectedts","ThreatActors.firstdetectedts","ThreatActors.lastdetectedts"],"timeDimensions":[{"dimension":"ThreatActors.lastdetectedts","granularity":null,"dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"ungrouped":false}'
			);
			// Workaround for EU / US Realm Alignment
			if ($Realm == 'EU') {
				$CubeJSRequests['ThreatActors'] = '{"segments":[],"timeDimensions":[{"dimension":"PortunusAggIPSummary.timestamp","granularity":null,"dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"ungrouped":false,"order":{"PortunusAggIPSummary.timestampMax":"desc"},"measures":["PortunusAggIPSummary.count"],"dimensions":["PortunusAggIPSummary.threat_indicator","PortunusAggIPSummary.actor_id"],"limit":1000,"filters":[{"and":[{"operator":"set","member":"PortunusAggIPSummary.threat_indicator"},{"operator":"set","member":"PortunusAggIPSummary.actor_id"}]}]}';
			} elseif ($Realm == 'US') {
				$CubeJSRequests['ThreatActors'] = '{"measures":[],"segments":[],"dimensions":["ThreatActors.storageid","ThreatActors.ikbactorid","ThreatActors.domain","ThreatActors.ikbfirstsubmittedts","ThreatActors.vtfirstdetectedts","ThreatActors.firstdetectedts","ThreatActors.lastdetectedts"],"timeDimensions":[{"dimension":"ThreatActors.lastdetectedts","granularity":null,"dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"ungrouped":false}';
			}
			$CubeJSResults = $this->QueryCubeJSMulti($CubeJSRequests);
	
			// Extract Powerpoint Template Zip
			$Progress = $this->writeProgress($UUID,$Progress,"Extracting template");
			extractZip($this->getDir()['Files'].'/templates/'.$this->TemplateConfig->getActiveTemplate()['FileName'],$this->getDir()['Files'].'/reports/report-'.$UUID);
	
			//
			// Do Chart, Spreadsheet & Image Stuff Here ....
			// Top threat feeds
			$Progress = $this->writeProgress($UUID,$Progress,"Building Threat Feeds");
			//print_r($CubeJSResults);
			$TopThreatFeeds = $CubeJSResults['TopThreatFeeds']['Body'];
			if (isset($TopThreatFeeds->result->data)) {
				$TopThreatFeedsSS = IOFactory::load($this->getDir()['Files'].'/reports/report-'.$UUID.'/ppt/embeddings/Microsoft_Excel_Worksheet.xlsx');
				$RowNo = 2;
				foreach ($TopThreatFeeds->result->data as $TopThreatFeed) {
					$TopThreatFeedsS = $TopThreatFeedsSS->getActiveSheet();
					$TopThreatFeedsS->setCellValue('A'.$RowNo, $TopThreatFeed->{'PortunusAggSecurity.feed_name'});
					$TopThreatFeedsS->setCellValue('B'.$RowNo, $TopThreatFeed->{'PortunusAggSecurity.feednameCount'});
					$RowNo++;
				}
				$TopThreatFeedsW = IOFactory::createWriter($TopThreatFeedsSS, 'Xlsx');
				$TopThreatFeedsW->save($this->getDir()['Files'].'/reports/report-'.$UUID.'/ppt/embeddings/Microsoft_Excel_Worksheet.xlsx');
			}
	
			// Top detected properties
			$Progress = $this->writeProgress($UUID,$Progress,"Building Threat Properties");
			$TopDetectedProperties = $CubeJSResults['TopDetectedProperties']['Body'];
			if (isset($TopDetectedProperties->result->data)) {
				$TopDetectedPropertiesSS = IOFactory::load($this->getDir()['Files'].'/reports/report-'.$UUID.'/ppt/embeddings/Microsoft_Excel_Worksheet1.xlsx');
				$RowNo = 2;
				foreach ($TopDetectedProperties->result->data as $TopDetectedProperty) {
					$TopDetectedPropertiesS = $TopDetectedPropertiesSS->getActiveSheet();
					$TopDetectedPropertiesS->setCellValue('A'.$RowNo, $TopDetectedProperty->{'PortunusDnsLogs.tproperty'});
					$TopDetectedPropertiesS->setCellValue('B'.$RowNo, $TopDetectedProperty->{'PortunusDnsLogs.tpropertyCount'});
					$RowNo++;
				}
				$TopDetectedPropertiesW = IOFactory::createWriter($TopDetectedPropertiesSS, 'Xlsx');
				$TopDetectedPropertiesW->save($this->getDir()['Files'].'/reports/report-'.$UUID.'/ppt/embeddings/Microsoft_Excel_Worksheet1.xlsx');
			}
	
			// Content filtration
			$Progress = $this->writeProgress($UUID,$Progress,"Building Content Filters");
			// $ContentFiltration = $CubeJSResults['ContentFiltration']['Body'];
			// Re-use High-Risk Websites data
			$ContentFiltration = $CubeJSResults['HighRiskWebsites']['Body'];
			if (isset($ContentFiltration->result->data)) {
				$ContentFiltrationSS = IOFactory::load($this->getDir()['Files'].'/reports/report-'.$UUID.'/ppt/embeddings/Microsoft_Excel_Worksheet2.xlsx');
				$RowNo = 2;
				// Slice Array to limit size to 10
				$ContentFiltrationSliced = array_slice($ContentFiltration->result->data,0,10);
				foreach ($ContentFiltrationSliced as $ContentFilter) {
					$ContentFiltrationS = $ContentFiltrationSS->getActiveSheet();
					// $ContentFiltrationS->setCellValue('A'.$RowNo, $ContentFilter->{'PortunusAggWebcontent.category'});
					// $ContentFiltrationS->setCellValue('B'.$RowNo, $ContentFilter->{'PortunusAggWebcontent.categoryCount'});
					// Switch to using the Web Content Discovery APIs, rather than those called via the Dashboard.
					$ContentFiltrationS->setCellValue('A'.$RowNo, $ContentFilter->{'PortunusAggWebContentDiscovery.domain_category'});
					$ContentFiltrationS->setCellValue('B'.$RowNo, $ContentFilter->{'PortunusAggWebContentDiscovery.count'});
					$RowNo++;
				}
				$ContentFiltrationW = IOFactory::createWriter($ContentFiltrationSS, 'Xlsx');
				$ContentFiltrationW->save($this->getDir()['Files'].'/reports/report-'.$UUID.'/ppt/embeddings/Microsoft_Excel_Worksheet2.xlsx');
			}
	
			// Insight Distribution by Threat Type - Sheet 3
			$Progress = $this->writeProgress($UUID,$Progress,"Building SOC Insight Threat Types");
			$InsightDistribution = $CubeJSResults['InsightDistribution']['Body'];
			if (isset($InsightDistribution->result->data)) {
				$InsightDistributionSS = IOFactory::load($this->getDir()['Files'].'/reports/report-'.$UUID.'/ppt/embeddings/Microsoft_Excel_Worksheet3.xlsx');
				$RowNo = 2;
				foreach ($InsightDistribution->result->data as $InsightThreatType) {
					$InsightDistributionS = $InsightDistributionSS->getActiveSheet();
					$InsightDistributionS->setCellValue('A'.$RowNo, $InsightThreatType->{'InsightsAggregated.threatType'});
					$InsightDistributionS->setCellValue('B'.$RowNo, $InsightThreatType->{'InsightsAggregated.count'});
					$RowNo++;
				}
				$InsightDistributionW = IOFactory::createWriter($InsightDistributionSS, 'Xlsx');
				$InsightDistributionW->save($this->getDir()['Files'].'/reports/report-'.$UUID.'/ppt/embeddings/Microsoft_Excel_Worksheet3.xlsx');
			}
	
			// Threat Types (Lookalikes) - Sheet 4
			$Progress = $this->writeProgress($UUID,$Progress,"Getting Lookalike Threats");
			$LookalikeThreatCountUri = urlencode('/api/atclad/v1/lookalike_threat_counts?_filter=detected_at>="'.$StartDimension.'" and detected_at<="'.$EndDimension.'"');
			$LookalikeThreatCounts = $this->QueryCSP("get",$LookalikeThreatCountUri);
			if (isset($LookalikeThreatCounts->results)) {
				$LookalikeThreatCountsSS = IOFactory::load($this->getDir()['Files'].'/reports/report-'.$UUID.'/ppt/embeddings/Microsoft_Excel_Worksheet4.xlsx');
				$LookalikeThreatCountsS = $LookalikeThreatCountsSS->getActiveSheet();
				$RowNo = 2;
				if (isset($LookalikeThreatCounts->results->suspicious_count)) {
					$LookalikeThreatCountsS->setCellValue('A'.$RowNo, 'Suspicious');
					$LookalikeThreatCountsS->setCellValue('B'.$RowNo, $LookalikeThreatCounts->results->suspicious_count);
					$RowNo++;
				}
				if (isset($LookalikeThreatCounts->results->malware_count)) {
					$LookalikeThreatCountsS->setCellValue('A'.$RowNo, 'Malware');
					$LookalikeThreatCountsS->setCellValue('B'.$RowNo, $LookalikeThreatCounts->results->malware_count);
					$RowNo++;
				}
				if (isset($LookalikeThreatCounts->results->phishing_count)) {
					$LookalikeThreatCountsS->setCellValue('A'.$RowNo, 'Phishing');
					$LookalikeThreatCountsS->setCellValue('B'.$RowNo, $LookalikeThreatCounts->results->phishing_count);
					$RowNo++;
				}
				if (isset($LookalikeThreatCounts->results->others_count)) {
					$LookalikeThreatCountsS->setCellValue('A'.$RowNo, 'Others');
					$LookalikeThreatCountsS->setCellValue('B'.$RowNo, $LookalikeThreatCounts->results->others_count);
					$RowNo++;
				}
				$LookalikeThreatCountsW = IOFactory::createWriter($LookalikeThreatCountsSS, 'Xlsx');
				$LookalikeThreatCountsW->save($this->getDir()['Files'].'/reports/report-'.$UUID.'/ppt/embeddings/Microsoft_Excel_Worksheet4.xlsx');
			}
	
			// ** Reusable Metrics ** //
			// DNS Firewall Activity - Used on Slides 2, 5 & 6
			$Progress = $this->writeProgress($UUID,$Progress,"Building DNS Firewall Event Criticality");
			$DNSFirewallActivity = $CubeJSResults['DNSFirewallActivity']['Body'];
			if (isset($DNSFirewallActivity->result)) {
				$HighId = array_search('High', array_column($DNSFirewallActivity->result->data, 'PortunusAggSecurity.severity'));
				$MediumId = array_search('Medium', array_column($DNSFirewallActivity->result->data, 'PortunusAggSecurity.severity'));
				$LowId = array_search('Low', array_column($DNSFirewallActivity->result->data, 'PortunusAggSecurity.severity'));
				if ($HighId !== false) {$HighEventsCount = $DNSFirewallActivity->result->data[$HighId]->{'PortunusAggSecurity.severityCount'};} else {$HighEventsCount = 0;}
				if ($MediumId !== false) {$MediumEventsCount = $DNSFirewallActivity->result->data[$MediumId]->{'PortunusAggSecurity.severityCount'};} else {$MediumEventsCount = 0;}
				if ($LowId !== false) {$LowEventsCount = $DNSFirewallActivity->result->data[$LowId]->{'PortunusAggSecurity.severityCount'};} else {$LowEventsCount = 0;}
			} else {
				$HighEventsCount = 0;
				$MediumEventsCount = 0;
				$LowEventsCount = 0;
			}
	
			$HML = $HighEventsCount+$MediumEventsCount+$LowEventsCount;
			if ($HML > 0) {
				$HMLP = 100 / $HML;
			} else {
				$HMLP = 0;
			}
			$HighPerc = $HighEventsCount * $HMLP;
			$MediumPerc = $MediumEventsCount * $HMLP;
			$LowPerc = $LowEventsCount * $HMLP;
	
			// Total DNS Activity - Used on Slides 6 & 9
			$Progress = $this->writeProgress($UUID,$Progress,"Building DNS Activity");
			$DNSActivity = $CubeJSResults['DNSActivity']['Body'];
			if (isset($DNSActivity->result->data[0])) {
				$DNSActivityCount = $DNSActivity->result->data[0]->{'PortunusAggInsight.requests'};
			} else {
				$DNSActivityCount = 0;
			}
	
			// Lookalike Domains - Used on Slides 5, 6 & 24
			$Progress = $this->writeProgress($UUID,$Progress,"Getting Lookalike Domain Counts");
			$LookalikeDomainCounts = $this->QueryCSP("get","api/atcfw/v1/lookalike_domain_counts");
			if (isset($LookalikeDomainCounts->results->count_total)) { $LookalikeTotalCount = $LookalikeDomainCounts->results->count_total; } else { $LookalikeTotalCount = 0; }
			if (isset($LookalikeDomainCounts->results->percentage_increase_total)) { $LookalikeTotalPercentage = $LookalikeDomainCounts->results->percentage_increase_total; } else { $LookalikeTotalPercentage = 0; }
			if (isset($LookalikeDomainCounts->results->count_custom)) { $LookalikeCustomCount = $LookalikeDomainCounts->results->count_custom; } else { $LookalikeCustomCount = 0; }
			if (isset($LookalikeDomainCounts->results->percentage_increase_custom)) { $LookalikeCustomPercentage = $LookalikeDomainCounts->results->percentage_increase_custom; } else { $LookalikeCustomPercentage = 0; }
			if (isset($LookalikeDomainCounts->results->count_threats)) { $LookalikeThreatCount = $LookalikeDomainCounts->results->count_threats; } else { $LookalikeThreatCount = 0; }
			if (isset($LookalikeDomainCounts->results->percentage_increase_threats)) { $LookalikeThreatPercentage = $LookalikeDomainCounts->results->percentage_increase_threats; } else { $LookalikeThreatPercentage = 0; }
	
			// SOC Insights - Used on Slides 15 & 28
			$Progress = $this->writeProgress($UUID,$Progress,"Building SOC Insight Threat Criticality");
			$SOCInsights = $CubeJSResults['SOCInsights']['Body'];
			if (isset($SOCInsights->result)) {
				$InfoInsightsId = array_search('INFO', array_column($SOCInsights->result->data, 'InsightsAggregated.priorityText'));
				$LowInsightsId = array_search('LOW', array_column($SOCInsights->result->data, 'InsightsAggregated.priorityText'));
				$MediumInsightsId = array_search('MEDIUM', array_column($SOCInsights->result->data, 'InsightsAggregated.priorityText'));
				$HighInsightsId = array_search('HIGH', array_column($SOCInsights->result->data, 'InsightsAggregated.priorityText'));
				$CriticalInsightsId = array_search('CRITICAL', array_column($SOCInsights->result->data, 'InsightsAggregated.priorityText'));
				$TotalInsights = number_abbr(array_sum(array_column($SOCInsights->result->data, 'InsightsAggregated.count')));
			} else {
				$TotalInsights = 0;
			}
			if (isset($InfoInsightsId) AND $InfoInsightsId !== false) {$InfoInsights = $SOCInsights->result->data[$InfoInsightsId]->{'InsightsAggregated.count'};} else {$InfoInsights = 0;}
			if (isset($LowInsightsId) AND $LowInsightsId !== false) {$LowInsights = $SOCInsights->result->data[$LowInsightsId]->{'InsightsAggregated.count'};} else {$LowInsights = 0;}
			if (isset($MediumInsightsId) AND $MediumInsightsId !== false) {$MediumInsights = $SOCInsights->result->data[$MediumInsightsId]->{'InsightsAggregated.count'};} else {$MediumInsights = 0;}
			if (isset($HighInsightsId) AND $HighInsightsId !== false) {$HighInsights = $SOCInsights->result->data[$HighInsightsId]->{'InsightsAggregated.count'};} else {$HighInsights = 0;}
			if (isset($CriticalInsightsId) AND $CriticalInsightsId !== false) {$CriticalInsights = $SOCInsights->result->data[$CriticalInsightsId]->{'InsightsAggregated.count'};} else {$CriticalInsights = 0;}
	
			// Security Activity
			$Progress = $this->writeProgress($UUID,$Progress,"Building Security Activity");
			$SecurityEvents = $CubeJSResults['SecurityEvents']['Body'];
			if (isset($SecurityEvents->result->data[0])) {
				$SecurityEventsCount = $SecurityEvents->result->data[0]->{'PortunusAggInsight.requests'};
			} else {
				$SecurityEventsCount = 0;
			}
	
			// Data Exfiltration Events
			$Progress = $this->writeProgress($UUID,$Progress,"Building Data Exfiltration Events");
			$DataExfilEvents = $CubeJSResults['DataExfilEvents']['Body'];
			if (isset($DataExfilEvents->result->data[0])) {
				$DataExfilEventsCount = $DataExfilEvents->result->data[0]->{'PortunusAggInsight.requests'};
			} else {
				$DataExfilEventsCount = 0;
			}
	
			// Zero Day DNS Events
			$Progress = $this->writeProgress($UUID,$Progress,"Building Zero Day DNS Events");
			$ZeroDayDNSEvents = $CubeJSResults['ZeroDayDNSEvents']['Body'];
			if (isset($ZeroDayDNSEvents->result->data[0])) {
				$ZeroDayDNSEventsCount = $ZeroDayDNSEvents->result->data[0]->{'PortunusAggInsight.requests'};
			} else {
				$ZeroDayDNSEventsCount = 0;
			}
	
			// Suspicious Domains
			$Progress = $this->writeProgress($UUID,$Progress,"Building Suspicious Domain Events");
			$SuspiciousEvents = $CubeJSResults['SuspiciousEvents']['Body'];
			if (isset($SuspiciousEvents->result->data[0])) {
				$SuspiciousEventsCount = $SuspiciousEvents->result->data[0]->{'PortunusAggInsight.requests'};
			} else {
				$SuspiciousEventsCount = 0;
			}
	
			// High Risk Websites
			$Progress = $this->writeProgress($UUID,$Progress,"Building High Risk Website Events");
			$HighRiskWebsites = $CubeJSResults['HighRiskWebsites']['Body'];
			if (isset($HighRiskWebsites->result->data)) {
				$HighRiskWebsiteCount = array_sum(array_column($HighRiskWebsites->result->data, 'PortunusAggWebContentDiscovery.count'));
				$HighRiskWebCategoryCount = count($HighRiskWebsites->result->data);
			} else {
				$HighRiskWebsiteCount = 0;
				$HighRiskWebCategoryCount = 0;
			}
	
			// DNS over HTTPS
			$Progress = $this->writeProgress($UUID,$Progress,"Building DoH Events");
			$DOHEvents = $CubeJSResults['DOHEvents']['Body'];
			if (isset($DOHEvents->result->data[0])) {
				$DOHEventsCount = $DOHEvents->result->data[0]->{'PortunusAggInsight.requests'};
			} else {
				$DOHEventsCount = 0;
			}
	
			// Newly Observed Domains
			$Progress = $this->writeProgress($UUID,$Progress,"Building Newly Observed Domain Events");
			$NODEvents = $CubeJSResults['NODEvents']['Body'];
			if (isset($NODEvents->result->data[0])) {
				$NODEventsCount = $NODEvents->result->data[0]->{'PortunusAggInsight.requests'};
			} else {
				$NODEventsCount = 0;
			}
	
			// Domain Generation Algorithms
			$Progress = $this->writeProgress($UUID,$Progress,"Building DGA Events");
			$DGAEvents = $CubeJSResults['DGAEvents']['Body'];
			if (isset($DGAEvents->result->data[0])) {
				$DGAEventsCount = $DGAEvents->result->data[0]->{'PortunusAggInsight.requests'};
			} else {
				$DGAEventsCount = 0;
			}
	
			// Unique Applications
			$Progress = $this->writeProgress($UUID,$Progress,"Building list of Unique Applications");
			$UniqueApplications = $CubeJSResults['UniqueApplications']['Body'];
			if (isset($UniqueApplications->result->data)) {
				$UniqueApplicationsCount = count($UniqueApplications->result->data);
			} else {
				$UniqueApplicationsCount = 0;
			}
	
			// Threat Actors Metrics
			$Progress = $this->writeProgress($UUID,$Progress,"Building Threat Actor Metrics");
			$Progress = $this->writeProgress($UUID,$Progress,"Getting Threat Actor Information (This may take a moment)");
			if (isset($CubeJSResults['ThreatActors']['Body']->result)) {
				$ThreatActors = $CubeJSResults['ThreatActors']['Body']->result->data;
				// Workaround to EU / US Realm Alignment
				if ($Realm == 'EU') {
				  $ThreatActorInfo = $this->ThreatActors->GetB1ThreatActorsByIdEU($ThreatActors);
				} elseif ($Realm == 'US') {
				  $ThreatActorInfo = $this->ThreatActors->GetB1ThreatActorsById($ThreatActors,$unnamed,$substring);
				}
				$ThreatActorsCount = count($ThreatActorInfo);
				// End of workaround
	
				if (isset($ThreatActorInfo->error)) {
					$ThreatActorInfo = array();
					$ThreatActorSlideCount = 0;
				} else {
					$ThreatActorSlideCount = count($ThreatActorInfo);
				}
			} else {
				$ThreatActorsCount = 0;
			}
	
			// Threat Activity
			$Progress = $this->writeProgress($UUID,$Progress,"Building Threat Activity");
			$ThreatActivityEvents = $CubeJSResults['ThreatActivityEvents']['Body'];
			if (isset($ThreatActivityEvents->result->data[0])) {
				$ThreatActivityEventsCount = $ThreatActivityEvents->result->data[0]->{'PortunusAggInsight.threatCount'};
			} else {
				$ThreatActivityEventsCount = 0;
			}
	
			// DNS Firewall
			$Progress = $this->writeProgress($UUID,$Progress,"Building DNS Firewall Activity");
			$DNSFirewallEvents = $CubeJSResults['DNSFirewallEvents']['Body'];
			if (isset($DNSFirewallEvents->result->data[0])) {
				$DNSFirewallEventsCount = $DNSFirewallEvents->result->data[0]->{'PortunusAggInsight.requests'};
			} else {
				$DNSFirewallEventsCount = 0;
			}
	
			// Web Content
			$Progress = $this->writeProgress($UUID,$Progress,"Building Web Content Events");
			$WebContentEvents = $CubeJSResults['WebContentEvents']['Body'];
			if (isset($WebContentEvents->result->data[0])) {
				$WebContentEventsCount = $WebContentEvents->result->data[0]->{'PortunusAggWebcontent.requests'};
			} else {
				$WebContentEventsCount = 0;
			}
	
			// Device Count
			$Progress = $this->writeProgress($UUID,$Progress,"Building Device Count");
			$Devices = $CubeJSResults['Devices']['Body'];
			if (isset($Devices->result->data[0])) {
				$DeviceCount = $Devices->result->data[0]->{'PortunusAggInsight.deviceCount'};
			} else {
				$DeviceCount = 0;
			}
	
			// User Count
			$Progress = $this->writeProgress($UUID,$Progress,"Building User Count");
			$Users = $CubeJSResults['Users']['Body'];
			if (isset($Users->result->data[0])) {
				$UserCount = $Users->result->data[0]->{'PortunusAggInsight.userCount'};
			} else {
				$UserCount = 0;
			}
	
			// Threat Insight Count
			$Progress = $this->writeProgress($UUID,$Progress,"Building Threat Insight Count");
			$ThreatInsight = $CubeJSResults['ThreatInsight']['Body'];
			if (isset($ThreatInsight->result->data)) {
				$ThreatInsightCount = count($ThreatInsight->result->data);
			} else {
				$ThreatInsightCount = 0;
			}
	
			// Threat View Count
			$Progress = $this->writeProgress($UUID,$Progress,"Building Threat View Count");
			$ThreatView = $CubeJSResults['ThreatView']['Body'];
			if (isset($ThreatView->result->data[0])) {
				$ThreatViewCount = $ThreatView->result->data[0]->{'PortunusAggInsight.tpropertyCount'};
			} else {
				$ThreatViewCount = 0;
			}
	
			// Source Count
			$Progress = $this->writeProgress($UUID,$Progress,"Building Sources Count");
			$Sources = $CubeJSResults['Sources']['Body'];
			if (isset($Sources->result->data[0])) {
				$SourcesCount = $Sources->result->data[0]->{'PortunusAggSecurity.networkCount'};
			} else {
				$SourcesCount = 0;
			}
	
			// ** ** //
	
			//
			// Do Threat Actor Stuff Here ....
			//
			// Skip Threat Actor Slides if Slide Number is set to 0
			if ($this->TemplateConfig->getActiveTemplate()['ThreatActorSlide'] != 0) {
				$Progress = $this->writeProgress($UUID,$Progress,"Generating Threat Actor Slides");
				// New slides to be appended after this slide number
				$ThreatActorSlideStart = $this->TemplateConfig->getActiveTemplate()['ThreatActorSlide'];
				// Calculate the slide position based on above value
				$ThreatActorSlidePosition = $ThreatActorSlideStart-2;
	
				// Tag Numbers Start
				$TagStart = 100;
	
				// Open PPTX Presentation _rels XML
				$xml_rels = new DOMDocument('1.0', 'utf-8');
				$xml_rels->formatOutput = true;
				$xml_rels->preserveWhiteSpace = false;
				$xml_rels->load($this->getDir()['Files'].'/reports/report-'.$UUID.'/ppt/_rels/presentation.xml.rels');
				$xml_rels_f = $xml_rels->createDocumentFragment();
				$xml_rels_fstart = ($xml_rels->getElementsByTagName('Relationship')->length)+50;
				// Open PPTX Presentation XML
				$xml_pres = new DOMDocument('1.0', 'utf-8');
				$xml_pres->formatOutput = true;
				$xml_pres->preserveWhiteSpace = false;
				$xml_pres->load($this->getDir()['Files'].'/reports/report-'.$UUID.'/ppt/presentation.xml');
				$xml_pres_f = $xml_pres->createDocumentFragment();
				$xml_pres_fstart = 14700;
				// Get Slide Count
				$SlidesCount = iterator_count(new FilesystemIterator($this->getDir()['Files'].'/reports/report-'.$UUID.'/ppt/slides'));
				// Set first slide number
				$SlideNumber = $SlidesCount++;
				// Copy Blank Threat Actor Image
				copy($this->getDir()['Assets'].'/images/Threat Actors/logo-only.png',$this->getDir()['Files'].'/reports/report-'.$UUID.'/ppt/media/logo-only.png');
				// Build new Threat Actor Slides & Update PPTX Resources
				if (isset($ThreatActorInfo)) {
					foreach  ($ThreatActorInfo as $TAI) {
						$KnownActor = $this->ThreatActors->getThreatActorConfigByName($TAI['actor_name']);
						if (($ThreatActorSlideCount - 1) > 0) {
							$xml_rels_f->appendXML('<Relationship Id="rId'.$xml_rels_fstart.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slide" Target="slides/slide'.$SlideNumber.'.xml"/>');
							$xml_pres_f->appendXML('<p:sldId id="'.$xml_pres_fstart.'" r:id="rId'.$xml_rels_fstart.'"/>');
							$xml_rels_fstart++;
							$xml_pres_fstart++;
							copy($this->getDir()['Files'].'/reports/report-'.$UUID.'/ppt/slides/slide'.$ThreatActorSlideStart.'.xml',$this->getDir()['Files'].'/reports/report-'.$UUID.'/ppt/slides/slide'.$SlideNumber.'.xml');
							copy($this->getDir()['Files'].'/reports/report-'.$UUID.'/ppt/slides/_rels/slide'.$ThreatActorSlideStart.'.xml.rels',$this->getDir()['Files'].'/reports/report-'.$UUID.'/ppt/slides/_rels/slide'.$SlideNumber.'.xml.rels');
						} else {
							$SlideNumber = $ThreatActorSlideStart;
						}
						// Update Tag Numbers
						$TASFile = file_get_contents($this->getDir()['Files'].'/reports/report-'.$UUID.'/ppt/slides/slide'.$SlideNumber.'.xml');
						$TASFile = str_replace('#TATAG00', '#TATAG'.$TagStart, $TASFile);
						// Add Threat Actor Icon
						$ThreatActorIconString = '<p:pic><p:nvPicPr><p:cNvPr id="36" name="Graphic 35"><a:extLst><a:ext uri="{FF2B5EF4-FFF2-40B4-BE49-F238E27FC236}"><a16:creationId xmlns:a16="http://schemas.microsoft.com/office/drawing/2014/main" id="{898E1A10-3ABF-AED0-2C71-1F26BBB6304B}"/></a:ext></a:extLst></p:cNvPr><p:cNvPicPr><a:picLocks noChangeAspect="1"/></p:cNvPicPr><p:nvPr/></p:nvPicPr><p:blipFill><a:blip r:embed="rId115"><a:extLst><a:ext uri="{96DAC541-7B7A-43D3-8B79-37D633B846F1}"><asvg:svgBlip xmlns:asvg="http://schemas.microsoft.com/office/drawing/2016/SVG/main" r:embed="rId115"/></a:ext></a:extLst></a:blip><a:stretch><a:fillRect/></a:stretch></p:blipFill><p:spPr><a:xfrm><a:off x="5522998" y="2349624"/><a:ext cx="1246722" cy="1582377"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></p:spPr></p:pic></p:spTree>';
						$TASFile = str_replace('</p:spTree>',$ThreatActorIconString,$TASFile);
						// Append Virus Total Stuff if applicable to the slide
	
						// Workaround to EU / US Realm Alignment
						if ($Realm == 'EU') {
							if (isset($TAI['related_indicators_with_dates'])) {
								foreach ($TAI['related_indicators_with_dates'] as $TAII) {
									if (isset($TAII->vt_first_submission_date)) {
										$TASFileString = '<p:cxnSp><p:nvCxnSpPr><p:cNvPr id="6" name="Straight Connector 5"><a:extLst><a:ext uri="{FF2B5EF4-FFF2-40B4-BE49-F238E27FC236}"><a16:creationId xmlns:a16="http://schemas.microsoft.com/office/drawing/2014/main" id="{3B07D3CE-83DF-306C-1740-B15E60D50B68}"/></a:ext></a:extLst></p:cNvPr><p:cNvCxnSpPr><a:cxnSpLocks/></p:cNvCxnSpPr><p:nvPr/></p:nvCxnSpPr><p:spPr><a:xfrm><a:off x="2663429" y="6816436"/><a:ext cx="0" cy="445863"/></a:xfrm><a:prstGeom prst="line"><a:avLst/></a:prstGeom><a:ln w="9525"><a:solidFill><a:schemeClr val="accent3"><a:lumMod val="40000"/><a:lumOff val="60000"/></a:schemeClr></a:solidFill><a:prstDash val="dash"/></a:ln></p:spPr><p:style><a:lnRef idx="1"><a:schemeClr val="accent1"/></a:lnRef><a:fillRef idx="0"><a:schemeClr val="accent1"/></a:fillRef><a:effectRef idx="0"><a:schemeClr val="accent1"/></a:effectRef><a:fontRef idx="minor"><a:schemeClr val="tx1"/></a:fontRef></p:style></p:cxnSp><p:sp><p:nvSpPr><p:cNvPr id="11" name="Rectangle 10"><a:extLst><a:ext uri="{FF2B5EF4-FFF2-40B4-BE49-F238E27FC236}"><a16:creationId xmlns:a16="http://schemas.microsoft.com/office/drawing/2014/main" id="{5CF57A2B-9E16-9EF8-CC46-DEACEC1E9222}"/></a:ext></a:extLst></p:cNvPr><p:cNvSpPr/><p:nvPr/></p:nvSpPr><p:spPr><a:xfrm><a:off x="2390809" y="6646115"/><a:ext cx="546397" cy="151573"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom><a:solidFill><a:schemeClr val="bg1"/></a:solidFill><a:ln><a:noFill/></a:ln></p:spPr><p:style><a:lnRef idx="2"><a:schemeClr val="accent1"><a:shade val="50000"/></a:schemeClr></a:lnRef><a:fillRef idx="1"><a:schemeClr val="accent1"/></a:fillRef><a:effectRef idx="0"><a:schemeClr val="accent1"/></a:effectRef><a:fontRef idx="minor"><a:schemeClr val="lt1"/></a:fontRef></p:style><p:txBody><a:bodyPr rtlCol="0" anchor="ctr"/><a:lstStyle/><a:p><a:pPr algn="ctr"/><a:endParaRPr lang="en-US" dirty="0" err="1"><a:solidFill><a:srgbClr val="101820"/></a:solidFill></a:endParaRPr></a:p></p:txBody></p:sp><p:pic><p:nvPicPr><p:cNvPr id="14" name="Graphic 13"><a:extLst><a:ext uri="{FF2B5EF4-FFF2-40B4-BE49-F238E27FC236}"><a16:creationId xmlns:a16="http://schemas.microsoft.com/office/drawing/2014/main" id="{6680C076-3929-2FD5-9B2D-C8EEC6FB5791}"/></a:ext></a:extLst></p:cNvPr><p:cNvPicPr><a:picLocks noChangeAspect="1"/></p:cNvPicPr><p:nvPr/></p:nvPicPr><p:blipFill><a:blip r:embed="rId120"><a:extLst><a:ext uri="{96DAC541-7B7A-43D3-8B79-37D633B846F1}"><asvg:svgBlip xmlns:asvg="http://schemas.microsoft.com/office/drawing/2016/SVG/main" r:embed="rId121"/></a:ext></a:extLst></a:blip><a:stretch><a:fillRect/></a:stretch></p:blipFill><p:spPr><a:xfrm><a:off x="2407408" y="6670008"/><a:ext cx="499438" cy="100897"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></p:spPr></p:pic><p:sp><p:nvSpPr><p:cNvPr id="15" name="Oval 14"><a:extLst><a:ext uri="{FF2B5EF4-FFF2-40B4-BE49-F238E27FC236}"><a16:creationId xmlns:a16="http://schemas.microsoft.com/office/drawing/2014/main" id="{BF608D1F-2449-B2E7-8286-C23F058ABA75}"/></a:ext></a:extLst></p:cNvPr><p:cNvSpPr/><p:nvPr/></p:nvSpPr><p:spPr><a:xfrm><a:off x="2641147" y="7268668"/><a:ext cx="45719" cy="45719"/></a:xfrm><a:prstGeom prst="ellipse"><a:avLst/></a:prstGeom><a:solidFill><a:schemeClr val="accent3"><a:lumMod val="20000"/><a:lumOff val="80000"/></a:schemeClr></a:solidFill><a:ln><a:noFill/></a:ln></p:spPr><p:style><a:lnRef idx="2"><a:schemeClr val="accent1"><a:shade val="50000"/></a:schemeClr></a:lnRef><a:fillRef idx="1"><a:schemeClr val="accent1"/></a:fillRef><a:effectRef idx="0"><a:schemeClr val="accent1"/></a:effectRef><a:fontRef idx="minor"><a:schemeClr val="lt1"/></a:fontRef></p:style><p:txBody><a:bodyPr rtlCol="0" anchor="ctr"/><a:lstStyle/><a:p><a:pPr algn="ctr"/><a:endParaRPr lang="en-US" dirty="0" err="1"><a:solidFill><a:srgbClr val="101820"/></a:solidFill></a:endParaRPr></a:p></p:txBody></p:sp></p:spTree>';
										$TASFile = str_replace('</p:spTree>',$TASFileString,$TASFile);
										$VTIndicatorFound = true;
										break;
									} else {
										$VTIndicatorFound = false;
									}
								}
							} else {
								$VTIndicatorFound = false;
							}
						} elseif ($Realm == 'US') {
							if (isset($TAI['observed_iocs'])) {
								foreach ($TAI['observed_iocs'] as $TAII) {
									if (isset($TAII['ThreatActors.vtfirstdetectedts'])) {
										$TASFileString = '<p:cxnSp><p:nvCxnSpPr><p:cNvPr id="6" name="Straight Connector 5"><a:extLst><a:ext uri="{FF2B5EF4-FFF2-40B4-BE49-F238E27FC236}"><a16:creationId xmlns:a16="http://schemas.microsoft.com/office/drawing/2014/main" id="{3B07D3CE-83DF-306C-1740-B15E60D50B68}"/></a:ext></a:extLst></p:cNvPr><p:cNvCxnSpPr><a:cxnSpLocks/></p:cNvCxnSpPr><p:nvPr/></p:nvCxnSpPr><p:spPr><a:xfrm><a:off x="2663429" y="6816436"/><a:ext cx="0" cy="445863"/></a:xfrm><a:prstGeom prst="line"><a:avLst/></a:prstGeom><a:ln w="9525"><a:solidFill><a:schemeClr val="accent3"><a:lumMod val="40000"/><a:lumOff val="60000"/></a:schemeClr></a:solidFill><a:prstDash val="dash"/></a:ln></p:spPr><p:style><a:lnRef idx="1"><a:schemeClr val="accent1"/></a:lnRef><a:fillRef idx="0"><a:schemeClr val="accent1"/></a:fillRef><a:effectRef idx="0"><a:schemeClr val="accent1"/></a:effectRef><a:fontRef idx="minor"><a:schemeClr val="tx1"/></a:fontRef></p:style></p:cxnSp><p:sp><p:nvSpPr><p:cNvPr id="11" name="Rectangle 10"><a:extLst><a:ext uri="{FF2B5EF4-FFF2-40B4-BE49-F238E27FC236}"><a16:creationId xmlns:a16="http://schemas.microsoft.com/office/drawing/2014/main" id="{5CF57A2B-9E16-9EF8-CC46-DEACEC1E9222}"/></a:ext></a:extLst></p:cNvPr><p:cNvSpPr/><p:nvPr/></p:nvSpPr><p:spPr><a:xfrm><a:off x="2390809" y="6646115"/><a:ext cx="546397" cy="151573"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom><a:solidFill><a:schemeClr val="bg1"/></a:solidFill><a:ln><a:noFill/></a:ln></p:spPr><p:style><a:lnRef idx="2"><a:schemeClr val="accent1"><a:shade val="50000"/></a:schemeClr></a:lnRef><a:fillRef idx="1"><a:schemeClr val="accent1"/></a:fillRef><a:effectRef idx="0"><a:schemeClr val="accent1"/></a:effectRef><a:fontRef idx="minor"><a:schemeClr val="lt1"/></a:fontRef></p:style><p:txBody><a:bodyPr rtlCol="0" anchor="ctr"/><a:lstStyle/><a:p><a:pPr algn="ctr"/><a:endParaRPr lang="en-US" dirty="0" err="1"><a:solidFill><a:srgbClr val="101820"/></a:solidFill></a:endParaRPr></a:p></p:txBody></p:sp><p:pic><p:nvPicPr><p:cNvPr id="14" name="Graphic 13"><a:extLst><a:ext uri="{FF2B5EF4-FFF2-40B4-BE49-F238E27FC236}"><a16:creationId xmlns:a16="http://schemas.microsoft.com/office/drawing/2014/main" id="{6680C076-3929-2FD5-9B2D-C8EEC6FB5791}"/></a:ext></a:extLst></p:cNvPr><p:cNvPicPr><a:picLocks noChangeAspect="1"/></p:cNvPicPr><p:nvPr/></p:nvPicPr><p:blipFill><a:blip r:embed="rId120"><a:extLst><a:ext uri="{96DAC541-7B7A-43D3-8B79-37D633B846F1}"><asvg:svgBlip xmlns:asvg="http://schemas.microsoft.com/office/drawing/2016/SVG/main" r:embed="rId121"/></a:ext></a:extLst></a:blip><a:stretch><a:fillRect/></a:stretch></p:blipFill><p:spPr><a:xfrm><a:off x="2407408" y="6670008"/><a:ext cx="499438" cy="100897"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></p:spPr></p:pic><p:sp><p:nvSpPr><p:cNvPr id="15" name="Oval 14"><a:extLst><a:ext uri="{FF2B5EF4-FFF2-40B4-BE49-F238E27FC236}"><a16:creationId xmlns:a16="http://schemas.microsoft.com/office/drawing/2014/main" id="{BF608D1F-2449-B2E7-8286-C23F058ABA75}"/></a:ext></a:extLst></p:cNvPr><p:cNvSpPr/><p:nvPr/></p:nvSpPr><p:spPr><a:xfrm><a:off x="2641147" y="7268668"/><a:ext cx="45719" cy="45719"/></a:xfrm><a:prstGeom prst="ellipse"><a:avLst/></a:prstGeom><a:solidFill><a:schemeClr val="accent3"><a:lumMod val="20000"/><a:lumOff val="80000"/></a:schemeClr></a:solidFill><a:ln><a:noFill/></a:ln></p:spPr><p:style><a:lnRef idx="2"><a:schemeClr val="accent1"><a:shade val="50000"/></a:schemeClr></a:lnRef><a:fillRef idx="1"><a:schemeClr val="accent1"/></a:fillRef><a:effectRef idx="0"><a:schemeClr val="accent1"/></a:effectRef><a:fontRef idx="minor"><a:schemeClr val="lt1"/></a:fontRef></p:style><p:txBody><a:bodyPr rtlCol="0" anchor="ctr"/><a:lstStyle/><a:p><a:pPr algn="ctr"/><a:endParaRPr lang="en-US" dirty="0" err="1"><a:solidFill><a:srgbClr val="101820"/></a:solidFill></a:endParaRPr></a:p></p:txBody></p:sp></p:spTree>';
										$TASFile = str_replace('</p:spTree>',$TASFileString,$TASFile);
										$VTIndicatorFound = true;
										break;
									} else {
										$VTIndicatorFound = false;
									}
								}
							} else {
								$VTIndicatorFound = false;
							}
						}
	
						// Add Report Link
						// ** // Use the following code to link based on presence of 'infoblox_references' parameter
						// if (isset($TAI['infoblox_references'][0])) {
						// ** // Use the following code to link based on the Threat Actor config
						//$InfobloxReferenceFound = false;
						if ($KnownActor && $KnownActor['URLStub'] !== "") {
							$ThreatActorExternalLinkString = '<p:sp><p:nvSpPr><p:cNvPr id="7" name="Text Placeholder 20"><a:hlinkClick r:id="rId122"/><a:extLst><a:ext uri="{FF2B5EF4-FFF2-40B4-BE49-F238E27FC236}"><a16:creationId xmlns:a16="http://schemas.microsoft.com/office/drawing/2014/main" id="{4A652F23-47D6-59A0-1D85-972482B29234}"/></a:ext></a:extLst></p:cNvPr><p:cNvSpPr txBox="1"><a:spLocks/></p:cNvSpPr><p:nvPr/></p:nvSpPr><p:spPr><a:xfrm><a:off x="5574269" y="3869404"/><a:ext cx="1168604" cy="271567"/></a:xfrm><a:prstGeom prst="roundRect"><a:avLst><a:gd name="adj" fmla="val 20777"/></a:avLst></a:prstGeom><a:noFill/><a:ln w="19050"><a:solidFill><a:schemeClr val="accent1"/></a:solidFill></a:ln><a:effectLst><a:glow rad="63500"><a:srgbClr val="00B24C"><a:alpha val="40000"/></a:srgbClr></a:glow></a:effectLst></p:spPr><p:txBody><a:bodyPr lIns="0" tIns="0" rIns="0" bIns="0" anchor="ctr" anchorCtr="0"/><a:lstStyle><a:lvl1pPr marL="141755" indent="-141755" algn="l" defTabSz="567019" rtl="0" eaLnBrk="1" latinLnBrk="0" hangingPunct="1"><a:lnSpc><a:spcPct val="90000"/></a:lnSpc><a:spcBef><a:spcPts val="620"/></a:spcBef><a:buFont typeface="Arial" panose="020B0604020202020204" pitchFamily="34" charset="0"/><a:buChar char="•"/><a:defRPr sz="1736" kern="1200"><a:solidFill><a:schemeClr val="tx1"/></a:solidFill><a:latin typeface="+mn-lt"/><a:ea typeface="+mn-ea"/><a:cs typeface="+mn-cs"/></a:defRPr></a:lvl1pPr><a:lvl2pPr marL="425265" indent="-141755" algn="l" defTabSz="567019" rtl="0" eaLnBrk="1" latinLnBrk="0" hangingPunct="1"><a:lnSpc><a:spcPct val="90000"/></a:lnSpc><a:spcBef><a:spcPts val="310"/></a:spcBef><a:buFont typeface="Arial" panose="020B0604020202020204" pitchFamily="34" charset="0"/><a:buChar char="•"/><a:defRPr sz="1488" kern="1200"><a:solidFill><a:schemeClr val="tx1"/></a:solidFill><a:latin typeface="+mn-lt"/><a:ea typeface="+mn-ea"/><a:cs typeface="+mn-cs"/></a:defRPr></a:lvl2pPr><a:lvl3pPr marL="708774" indent="-141755" algn="l" defTabSz="567019" rtl="0" eaLnBrk="1" latinLnBrk="0" hangingPunct="1"><a:lnSpc><a:spcPct val="90000"/></a:lnSpc><a:spcBef><a:spcPts val="310"/></a:spcBef><a:buFont typeface="Arial" panose="020B0604020202020204" pitchFamily="34" charset="0"/><a:buChar char="•"/><a:defRPr sz="1240" kern="1200"><a:solidFill><a:schemeClr val="tx1"/></a:solidFill><a:latin typeface="+mn-lt"/><a:ea typeface="+mn-ea"/><a:cs typeface="+mn-cs"/></a:defRPr></a:lvl3pPr><a:lvl4pPr marL="992284" indent="-141755" algn="l" defTabSz="567019" rtl="0" eaLnBrk="1" latinLnBrk="0" hangingPunct="1"><a:lnSpc><a:spcPct val="90000"/></a:lnSpc><a:spcBef><a:spcPts val="310"/></a:spcBef><a:buFont typeface="Arial" panose="020B0604020202020204" pitchFamily="34" charset="0"/><a:buChar char="•"/><a:defRPr sz="1116" kern="1200"><a:solidFill><a:schemeClr val="tx1"/></a:solidFill><a:latin typeface="+mn-lt"/><a:ea typeface="+mn-ea"/><a:cs typeface="+mn-cs"/></a:defRPr></a:lvl4pPr><a:lvl5pPr marL="1275794" indent="-141755" algn="l" defTabSz="567019" rtl="0" eaLnBrk="1" latinLnBrk="0" hangingPunct="1"><a:lnSpc><a:spcPct val="90000"/></a:lnSpc><a:spcBef><a:spcPts val="310"/></a:spcBef><a:buFont typeface="Arial" panose="020B0604020202020204" pitchFamily="34" charset="0"/><a:buChar char="•"/><a:defRPr sz="1116" kern="1200"><a:solidFill><a:schemeClr val="tx1"/></a:solidFill><a:latin typeface="+mn-lt"/><a:ea typeface="+mn-ea"/><a:cs typeface="+mn-cs"/></a:defRPr></a:lvl5pPr><a:lvl6pPr marL="1559303" indent="-141755" algn="l" defTabSz="567019" rtl="0" eaLnBrk="1" latinLnBrk="0" hangingPunct="1"><a:lnSpc><a:spcPct val="90000"/></a:lnSpc><a:spcBef><a:spcPts val="310"/></a:spcBef><a:buFont typeface="Arial" panose="020B0604020202020204" pitchFamily="34" charset="0"/><a:buChar char="•"/><a:defRPr sz="1116" kern="1200"><a:solidFill><a:schemeClr val="tx1"/></a:solidFill><a:latin typeface="+mn-lt"/><a:ea typeface="+mn-ea"/><a:cs typeface="+mn-cs"/></a:defRPr></a:lvl6pPr><a:lvl7pPr marL="1842813" indent="-141755" algn="l" defTabSz="567019" rtl="0" eaLnBrk="1" latinLnBrk="0" hangingPunct="1"><a:lnSpc><a:spcPct val="90000"/></a:lnSpc><a:spcBef><a:spcPts val="310"/></a:spcBef><a:buFont typeface="Arial" panose="020B0604020202020204" pitchFamily="34" charset="0"/><a:buChar char="•"/><a:defRPr sz="1116" kern="1200"><a:solidFill><a:schemeClr val="tx1"/></a:solidFill><a:latin typeface="+mn-lt"/><a:ea typeface="+mn-ea"/><a:cs typeface="+mn-cs"/></a:defRPr></a:lvl7pPr><a:lvl8pPr marL="2126323" indent="-141755" algn="l" defTabSz="567019" rtl="0" eaLnBrk="1" latinLnBrk="0" hangingPunct="1"><a:lnSpc><a:spcPct val="90000"/></a:lnSpc><a:spcBef><a:spcPts val="310"/></a:spcBef><a:buFont typeface="Arial" panose="020B0604020202020204" pitchFamily="34" charset="0"/><a:buChar char="•"/><a:defRPr sz="1116" kern="1200"><a:solidFill><a:schemeClr val="tx1"/></a:solidFill><a:latin typeface="+mn-lt"/><a:ea typeface="+mn-ea"/><a:cs typeface="+mn-cs"/></a:defRPr></a:lvl8pPr><a:lvl9pPr marL="2409833" indent="-141755" algn="l" defTabSz="567019" rtl="0" eaLnBrk="1" latinLnBrk="0" hangingPunct="1"><a:lnSpc><a:spcPct val="90000"/></a:lnSpc><a:spcBef><a:spcPts val="310"/></a:spcBef><a:buFont typeface="Arial" panose="020B0604020202020204" pitchFamily="34" charset="0"/><a:buChar char="•"/><a:defRPr sz="1116" kern="1200"><a:solidFill><a:schemeClr val="tx1"/></a:solidFill><a:latin typeface="+mn-lt"/><a:ea typeface="+mn-ea"/><a:cs typeface="+mn-cs"/></a:defRPr></a:lvl9pPr></a:lstStyle><a:p><a:pPr marL="0" indent="0" algn="ctr"><a:lnSpc><a:spcPct val="100000"/></a:lnSpc><a:spcBef><a:spcPts val="300"/></a:spcBef><a:spcAft><a:spcPts val="600"/></a:spcAft><a:buClr><a:schemeClr val="tx1"/></a:buClr><a:buNone/></a:pPr><a:r><a:rPr lang="en-US" sz="600" b="1" dirty="0"><a:solidFill><a:schemeClr val="bg1"/></a:solidFill><a:latin typeface="Lato" panose="020F0502020204030203" pitchFamily="34" charset="77"/><a:ea typeface="Lato" panose="020F0502020204030203" pitchFamily="34" charset="0"/><a:cs typeface="Lato" panose="020F0502020204030203" pitchFamily="34" charset="0"/></a:rPr><a:t>THREAT ACTOR REPORT</a:t></a:r><a:endParaRPr lang="en-US" sz="600" dirty="0"><a:solidFill><a:schemeClr val="bg1"/></a:solidFill><a:latin typeface="Lato" panose="020F0502020204030203" pitchFamily="34" charset="77"/><a:ea typeface="Lato" panose="020F0502020204030203" pitchFamily="34" charset="0"/><a:cs typeface="Lato" panose="020F0502020204030203" pitchFamily="34" charset="0"/></a:endParaRPr></a:p></p:txBody></p:sp></p:spTree>';
							$TASFile = str_replace('</p:spTree>',$ThreatActorExternalLinkString,$TASFile);
							//$InfobloxReferenceFound = true;
						}
						// Update Slide XML with changes
						file_put_contents($this->getDir()['Files'].'/reports/report-'.$UUID.'/ppt/slides/slide'.$SlideNumber.'.xml', $TASFile);
						$xml_tas = new DOMDocument('1.0', 'utf-8');
						$xml_tas->formatOutput = true;
						$xml_tas->preserveWhiteSpace = false;
						$xml_tas->load($this->getDir()['Files'].'/reports/report-'.$UUID.'/ppt/slides/_rels/slide'.$SlideNumber.'.xml.rels');
						foreach ($xml_tas->getElementsByTagName('Relationship') as $element) {
							// Remove notes references to avoid having to create unneccessary notes resources
							if ($element->getAttribute('Type') == "http://schemas.openxmlformats.org/officeDocument/2006/relationships/notesSlide") {
								$element->remove();
							}
						}
						$xml_tas_f = $xml_tas->createDocumentFragment();
						if ($KnownActor && $KnownActor['PNG'] !== "" && $KnownActor['SVG'] !== "") {
							$SVG = $KnownActor['SVG'];
							$PNG = $KnownActor['PNG'];
							// Threat Actor PNG
							copy($this->getDir()['Assets'].'/images/Threat Actors/Uploads/'.$PNG,$this->getDir()['Files'].'/reports/report-'.$UUID.'/ppt/media/'.$PNG);
							copy($this->getDir()['Assets'].'/images/Threat Actors/Uploads/'.$SVG,$this->getDir()['Files'].'/reports/report-'.$UUID.'/ppt/media/'.$SVG);
							$xml_tas_f->appendXML('<Relationship Id="rId115" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/'.$PNG.'"/>');
							$xml_tas_f->appendXML('<Relationship Id="rId116" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/'.$SVG.'"/>');
						} else {
							$xml_tas_f->appendXML('<Relationship Id="rId115" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/logo-only.png"/>');
							$xml_tas_f->appendXML('<Relationship Id="rId116" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/logo-only.svg"/>');
						}
	
						// Virus Total PNG / SVG
						if ($VTIndicatorFound) {
							copy($this->getDir()['Assets'].'/images/Other/virustotal.png',$this->getDir()['Files'].'/reports/report-'.$UUID.'/ppt/media/virustotal.png');
							copy($this->getDir()['Assets'].'/images/Other/virustotal.svg',$this->getDir()['Files'].'/reports/report-'.$UUID.'/ppt/media/virustotal.svg');
							$xml_tas_f->appendXML('<Relationship Id="rId120" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/virustotal.png"/>');
							$xml_tas_f->appendXML('<Relationship Id="rId121" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/virustotal.svg"/>');
						}
	
						// Infoblox Blog URL
						// ** // Use the following code to link based on presence of 'infoblox_references' parameter
						// if ($InfobloxReferenceFound) {
						//     $xml_tas_f2 = $xml_tas->createDocumentFragment();
						//     $xml_tas_f2->appendXML('<Relationship Id="rId122" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/hyperlink" Target="'.$TAI['infoblox_references'][0].'" TargetMode="External"/>');
						//     $xml_tas->getElementsByTagName('Relationships')->item(0)->appendChild($xml_tas_f2);
						// }
						// ** // Use the following code to link based on the Threat Actor config
						if ($KnownActor && $KnownActor['URLStub'] !== "") {
							$URL = $KnownActor['URLStub'];
							$xml_tas_f->appendXML('<Relationship Id="rId122" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/hyperlink" Target="'.$URL.'" TargetMode="External"/>');
						}
	
						$xml_tas->getElementsByTagName('Relationships')->item(0)->appendChild($xml_tas_f);
						$xml_tas->save($this->getDir()['Files'].'/reports/report-'.$UUID.'/ppt/slides/_rels/slide'.$SlideNumber.'.xml.rels');
						$TagStart += 10;
						// Iterate slide number
						$SlideNumber++;
						$ThreatActorSlideCount--;
					}
	
					// Append Elements to Core XML Files
					$xml_rels->getElementsByTagName('Relationships')->item(0)->appendChild($xml_rels_f);
					// Append new slides to specific position
					$xml_pres->getElementsByTagName('sldId')->item($ThreatActorSlidePosition)->after($xml_pres_f);
	
					// Save Core XML Files
					$xml_rels->save($this->getDir()['Files'].'/reports/report-'.$UUID.'/ppt/_rels/presentation.xml.rels');
					$xml_pres->save($this->getDir()['Files'].'/reports/report-'.$UUID.'/ppt/presentation.xml');
	
					//
					// End of Threat Actors
					//
				}
			} else {
				$Progress = $this->writeProgress($UUID,$Progress,"Skipping Threat Actor Slides");
			}
	
			// Rebuild Powerpoint Template Zip
			$Progress = $this->writeProgress($UUID,$Progress,"Stitching Powerpoint Template");
			compressZip($this->getDir()['Files'].'/reports/report-'.$UUID.'.pptx',$this->getDir()['Files'].'/reports/report-'.$UUID);
	
			// Cleanup Extracted Zip
			$Progress = $this->writeProgress($UUID,$Progress,"Cleaning up");
			rmdirRecursive($this->getDir()['Files'].'/reports/report-'.$UUID);
	
			// Extract Powerpoint Template Strings
			// ** Using external library to save re-writing the string replacement functions manually. Will probably pull this in as native code at some point.
			$Progress = $this->writeProgress($UUID,$Progress,"Extract Powerpoint Strings");
			$extractor = new BasicExtractor();
			$mapping = $extractor->extractStringsAndCreateMappingFile(
				$this->getDir()['Files'].'/reports/report-'.$UUID.'.pptx',
				$this->getDir()['Files'].'/reports/report-'.$UUID.'-extracted.pptx'
			);
	
			$Progress = $this->writeProgress($UUID,$Progress,"Injecting Powerpoint Strings");
			##// Slide 2 / 45 - Title Page & Contact Page
			// Get & Inject Customer Name, Contact Name & Email
			$mapping = replaceTag($mapping,'#TAG01',$CurrentAccount->name);
			$mapping = replaceTag($mapping,'#DATE',date("jS F Y"));
			$StartDate = new DateTime($StartDimension);
			$EndDate = new DateTime($EndDimension);
			$mapping = replaceTag($mapping,'#DATESOFCOLLECTION',$StartDate->format("jS F Y").' - '.$EndDate->format("jS F Y"));
			$mapping = replaceTag($mapping,'#NAME',$UserInfo->result->name);
			$mapping = replaceTag($mapping,'#EMAIL',$UserInfo->result->email);
	
			##// Slide 5 - Executive Summary
			$mapping = replaceTag($mapping,'#TAG02',number_abbr($HighEventsCount)); // High-Risk Events
			$mapping = replaceTag($mapping,'#TAG03',number_abbr($HighRiskWebsiteCount)); // High-Risk Websites
			$mapping = replaceTag($mapping,'#TAG04',number_abbr($DataExfilEventsCount)); // Data Exfil / Tunneling
			$mapping = replaceTag($mapping,'#TAG05',number_abbr($LookalikeThreatCount)); // Lookalike Domains
			$mapping = replaceTag($mapping,'#TAG06',number_abbr($ZeroDayDNSEventsCount)); // Zero Day DNS
			$mapping = replaceTag($mapping,'#TAG07',number_abbr($SuspiciousEventsCount)); // Suspicious Domains
	
			##// Slide 6 - Security Indicator Summary
			$mapping = replaceTag($mapping,'#TAG08',number_abbr($DNSActivityCount)); // DNS Requests
			$mapping = replaceTag($mapping,'#TAG09',number_abbr($HighEventsCount)); // High-Risk Events
			$mapping = replaceTag($mapping,'#TAG10',number_abbr($MediumEventsCount)); // Medium-Risk Events
			$mapping = replaceTag($mapping,'#TAG11',number_abbr($TotalInsights)); // Insights
			$mapping = replaceTag($mapping,'#TAG12',number_abbr($LookalikeThreatCount)); // Custom Lookalike Domains
			$mapping = replaceTag($mapping,'#TAG13',number_abbr($DOHEventsCount)); // DoH
			$mapping = replaceTag($mapping,'#TAG14',number_abbr($ZeroDayDNSEventsCount)); // Zero Day DNS
			$mapping = replaceTag($mapping,'#TAG15',number_abbr($SuspiciousEventsCount)); // Suspicious Domains
	
			$mapping = replaceTag($mapping,'#TAG16',number_abbr($NODEventsCount)); // Newly Observed Domains
			$mapping = replaceTag($mapping,'#TAG17',number_abbr($DGAEventsCount)); // Domain Generated Algorithms
			$mapping = replaceTag($mapping,'#TAG18',number_abbr($DataExfilEventsCount)); // DNS Tunnelling
			$mapping = replaceTag($mapping,'#TAG19',number_abbr($UniqueApplicationsCount)); // Unique Applications
			$mapping = replaceTag($mapping,'#TAG20',number_abbr($HighRiskWebCategoryCount)); // High-Risk Web Categories
			$mapping = replaceTag($mapping,'#TAG21',number_abbr($ThreatActorsCount)); // Threat Actors
	
			##// Slide 9 - Traffic Usage Analysis
			// Total DNS Activity
			$mapping = replaceTag($mapping,'#TAG22',number_abbr($DNSActivityCount));
			// DNS Firewall Activity
			$mapping = replaceTag($mapping,'#TAG23',number_abbr($HML)); // Total
			$mapping = replaceTag($mapping,'#TAG24',number_abbr($HighEventsCount)); // High Int
			$mapping = replaceTag($mapping,'#TAG25',number_format($HighPerc,2).'%'); // High Percent
			$mapping = replaceTag($mapping,'#TAG26',number_abbr($MediumEventsCount)); // Medium Int
			$mapping = replaceTag($mapping,'#TAG27',number_format($MediumPerc,2).'%'); // Medium Percent
			$mapping = replaceTag($mapping,'#TAG28',number_abbr($LowEventsCount)); // Low Int
			$mapping = replaceTag($mapping,'#TAG29',number_format($LowPerc,2).'%'); // Low Percent
			// Threat Activity
			$mapping = replaceTag($mapping,'#TAG30',number_abbr($ThreatActivityEventsCount));
			// Data Exfiltration Incidents
			$mapping = replaceTag($mapping,'#TAG31',number_abbr($DataExfilEventsCount));
	
			##// Slide 15 - Key Insights
			// Insight Severity
			$mapping = replaceTag($mapping,'#TAG32',number_abbr($TotalInsights)); // Total Open Insights
			$mapping = replaceTag($mapping,'#TAG33',number_abbr($MediumInsights)); // Medium Priority Insights
			$mapping = replaceTag($mapping,'#TAG34',number_abbr($HighInsights)); // High Priority Insights
			$mapping = replaceTag($mapping,'#TAG35',number_abbr($CriticalInsights)); // Critical Priority Insights
			// Event To Insight Aggregation
			$mapping = replaceTag($mapping,'#TAG36',number_abbr($SecurityEventsCount)); // Events
			$mapping = replaceTag($mapping,'#TAG37',number_abbr($TotalInsights)); // Key Insights
	
			##// Slide 24 - Lookalike Domains
			$mapping = replaceTag($mapping,'#TAG38',number_abbr($LookalikeTotalCount)); // Total Lookalikes
			if ($LookalikeTotalPercentage >= 0){$arrow='↑';} else {$arrow='↓';}
			$mapping = replaceTag($mapping,'#TAG39',$arrow); // Arrow Up/Down
			$mapping = replaceTag($mapping,'#TAG40',number_abbr($LookalikeTotalPercentage)); // Total Percentage Increase
			$mapping = replaceTag($mapping,'#TAG41',number_abbr($LookalikeCustomCount)); // Total Lookalikes from Custom Watched Domains
			if ($LookalikeCustomPercentage >= 0){$arrow='↑';} else {$arrow='↓';}
			$mapping = replaceTag($mapping,'#TAG42',$arrow); // Arrow Up/Down
			$mapping = replaceTag($mapping,'#TAG43',number_abbr($LookalikeCustomPercentage)); // Custom Percentage Increase
			$mapping = replaceTag($mapping,'#TAG44',number_abbr($LookalikeThreatCount)); // Threats from Custom Watched Domains
			if ($LookalikeThreatPercentage >= 0){$arrow='↑';} else {$arrow='↓';}
			$mapping = replaceTag($mapping,'#TAG45',$arrow); // Arrow Up/Down
			$mapping = replaceTag($mapping,'#TAG46',number_abbr($LookalikeThreatPercentage)); // Threats Percentage Increase
	
			##// Slide 28 - Security Activities
			$mapping = replaceTag($mapping,'#TAG47',number_abbr($SecurityEventsCount)); // Security Events
			$mapping = replaceTag($mapping,'#TAG48',number_abbr($DNSFirewallEventsCount)); // DNS Firewall
			$mapping = replaceTag($mapping,'#TAG49',number_abbr($WebContentEventsCount)); // Web Content
			$mapping = replaceTag($mapping,'#TAG50',number_abbr($DeviceCount)); // Devices
			$mapping = replaceTag($mapping,'#TAG51',number_abbr($UserCount)); // Users
			$mapping = replaceTag($mapping,'#TAG52',number_abbr($TotalInsights)); // Insights
			$mapping = replaceTag($mapping,'#TAG53',number_abbr($ThreatInsightCount)); // Threat Insight
			$mapping = replaceTag($mapping,'#TAG54',number_abbr($ThreatViewCount)); // Threat View
			$mapping = replaceTag($mapping,'#TAG55',number_abbr($SourcesCount)); // Sources
	
			##// Slide 32 -> Onwards - Threat Actors
			// This is where the Threat Actor Tag replacement occurs
			// Set Tag Start Number
			$TagStart = 100;
			if (isset($ThreatActorInfo)) {
				foreach ($ThreatActorInfo as $TAI) {
					// Workaround for EU / US Realm Alignment
					if ($Realm == 'EU') {
						// Get sorted list of observed IOCs not found in Virus Total
						if (isset($TAI['related_indicators_with_dates'])) {
							$ObservedIndicators = $TAI['related_indicators_with_dates'];
							$IndicatorCount = count($TAI['related_indicators_with_dates']);
							$IndicatorsInVT = [];
							if ($IndicatorCount > 0) {
								foreach ($ObservedIndicators as $OI) {
									if (array_key_exists('vt_first_submission_date', json_decode(json_encode($OI), true))) {
										$IndicatorsInVT[] = $OI;
									}
								}
							}
							if (count($IndicatorsInVT) > 0) {
								// Sort the array based on the time difference
								usort($IndicatorsInVT, function($a, $b) {
									return $this->calculateVirusTotalDifference($b) <=> $this->calculateVirusTotalDifference($a);
								});
								$IndicatorsNotObserved = $TAI['related_count'] - count($ObservedIndicators);
								$ExampleDomain = $IndicatorsInVT[0]->indicator;
								$FirstSeen = new DateTime($IndicatorsInVT[0]->te_ik_submitted);
								$LastSeen = new DateTime($IndicatorsInVT[0]->te_customer_last_dns_query);
								$VTDate = new DateTime($IndicatorsInVT[0]->vt_first_submission_date);
								$ProtectedFor = $FirstSeen->diff($LastSeen)->days;
								$DaysAhead = 'Discovered '.($ProtectedFor - $LastSeen->diff($VTDate)->days).' days ahead';
							} else {
								$IndicatorsNotObserved = $TAI['related_count'] - count($ObservedIndicators);
								$ExampleDomain = $ObservedIndicators[0]->indicator;
								$FirstSeen = new DateTime($ObservedIndicators[0]->te_ik_submitted);
								$LastSeen = new DateTime($ObservedIndicators[0]->te_customer_last_dns_query);
								$DaysAhead = 'Discovered';
								$ProtectedFor = $FirstSeen->diff($LastSeen)->days;
							}
						} else {
							$IndicatorsNotObserved = 'N/A';
							$ExampleDomain = 'N/A';
							$FirstSeen = new DateTime('1901-01-01 00:00');
							$LastSeen = new DateTime('1901-01-01 00:00');
							$DaysAhead = 'Discovered';
							$ProtectedFor = 'N/A';
							$IndicatorCount = 'N/A';
						}
					} elseif ($Realm == 'US') {
						if (isset($TAI['observed_iocs'])) {
							$ObservedIndicators = $TAI['observed_iocs'];
							$IndicatorCount = $TAI['observed_count'];
							$IndicatorsInVT = [];
							if ($IndicatorCount > 0) {
								foreach ($ObservedIndicators as $OI) {
									if (array_key_exists('ThreatActors.vtfirstdetectedts', json_decode(json_encode($OI), true))) {
										$IndicatorsInVT[] = $OI;
									}
								}
							}
							if (count($IndicatorsInVT) > 0) {
								// Sort the array based on the time difference
								usort($IndicatorsInVT, function($a, $b) {
									return $this->calculateVirusTotalDifference($b) <=> $this->calculateVirusTotalDifference($a);
								});
								$IndicatorsNotObserved = $TAI['related_count'] - $IndicatorCount;
								$ExampleDomain = $IndicatorsInVT[0]['ThreatActors.domain'];
								$FirstSeen = new DateTime($IndicatorsInVT[0]['ThreatActors.ikbfirstsubmittedts']);
								$LastSeen = new DateTime($IndicatorsInVT[0]['ThreatActors.lastdetectedts']);
								$VTDate = new DateTime($IndicatorsInVT[0]['ThreatActors.vtfirstdetectedts']);
								$ProtectedFor = $FirstSeen->diff($LastSeen)->days;
								$DaysAhead = 'Discovered '.($ProtectedFor - $LastSeen->diff($VTDate)->days).' days ahead';
							} else {
								$IndicatorsNotObserved = $TAI['related_count'];
								$ExampleDomain = $ObservedIndicators[0]['ThreatActors.domain'];
								$FirstSeen = new DateTime($ObservedIndicators[0]['ThreatActors.ikbfirstsubmittedts']);
								$LastSeen = new DateTime($ObservedIndicators[0]['ThreatActors.lastdetectedts']);
								$DaysAhead = 'Discovered';
								$ProtectedFor = $FirstSeen->diff($LastSeen)->days;
							}
						} else {
							$IndicatorsNotObserved = 'N/A';
							$ExampleDomain = 'N/A';
							$FirstSeen = new DateTime('1901-01-01 00:00');
							$LastSeen = new DateTime('1901-01-01 00:00');
							$DaysAhead = 'Discovered';
							$ProtectedFor = 'N/A';
							$IndicatorCount = 'N/A';
						}
					}
					// Workaround End
	
					$mapping = replaceTag($mapping,'#TATAG'.$TagStart.'01',ucwords($TAI['actor_name'])); // Threat Actor Name
					$mapping = replaceTag($mapping,'#TATAG'.$TagStart.'02',$TAI['actor_description']); // Threat Actor Description
					$mapping = replaceTag($mapping,'#TATAG'.$TagStart.'03',$IndicatorCount); // Number of Observed IOCs
					$mapping = replaceTag($mapping,'#TATAG'.$TagStart.'04',$IndicatorsNotObserved); // Number of Observed IOCs not observed
					$mapping = replaceTag($mapping,'#TATAG'.$TagStart.'05',$TAI['related_count']); // Number of Related IOCs
					$mapping = replaceTag($mapping,'#TATAG'.$TagStart.'06',$DaysAhead); // Discovered X Days Ahead
					$mapping = replaceTag($mapping,'#TATAG'.$TagStart.'07',$FirstSeen->format('d/m/y')); // First Detection Date
					$mapping = replaceTag($mapping,'#TATAG'.$TagStart.'08',$LastSeen->format('d/m/y')); // Last Detection Date
					$mapping = replaceTag($mapping,'#TATAG'.$TagStart.'09',$ProtectedFor); // Protected X Days
					$mapping = replaceTag($mapping,'#TATAG'.$TagStart.'10',$ExampleDomain); // Example Domain
					$TagStart += 10;
	
					// if ($TAI['actor_name'] == 'Vigorish Viper') {
					//     echo 'Found!';
					//     echo ucwords($TAI['actor_name']);
					//     echo '#TATAG'.$TagStart.'01';
					//     $mapping = replaceTag($mapping,'#TATAG'.$TagStart.'01','Whee');
					//     print_r($mapping);
					//     die();
					// }
				}
			}
	
			// Rebuild Powerpoint
			// ** Using external library to save re-writing the string replacement functions manually. Will probably pull this in as native code at some point.
			$Progress = $this->writeProgress($UUID,$Progress,"Rebuilding Powerpoint Template");
			$injector = new BasicInjector();
			$injector->injectMappingAndCreateNewFile(
				$mapping,
				$this->getDir()['Files'].'/reports/report-'.$UUID.'-extracted.pptx',
				$this->getDir()['Files'].'/reports/report-'.$UUID.'.pptx'
			);
	
			// Cleanup
			$Progress = $this->writeProgress($UUID,$Progress,"Final Cleanup");
			unlink($this->getDir()['Files'].'/reports/report-'.$UUID.'-extracted.pptx');
	
			// Report Status as Done
			$Progress = $this->writeProgress($UUID,$Progress,"Done");
			$this->AssessmentReporting->updateReportEntryStatus($UUID,'Completed');
	
			$Status = 'Success';
		}
	
		## Generate Response
		$response = array(
			'Status' => $Status,
		);
		if (isset($Error)) {
			$response['Error'] = $Error;
		} else {
			$response['id'] = $UUID;
		}
		return $response;
	}
	
	public function writeProgress($id,$Count,$Action = "") {
		$Count++;
		$myfile = fopen($this->getDir()['Files'].'/reports/report-'.$id.'.progress', "w") or die("Unable to save progress file");
		$Progress = json_encode(array(
			'Count' => $Count,
			'Action' => $Action
		));
		fwrite($myfile, $Progress);
		fclose($myfile);
		return $Count;
	}
	
	public function getProgress($id,$Total) {
		$ProgressFile = $this->getDir()['Files'].'/reports/report-'.$id.'.progress';
		if (file_exists($ProgressFile)) {
			$myfile = fopen($ProgressFile, "r") or die("0");
			$Current = json_decode(fread($myfile,filesize($ProgressFile)));
			if (isset($Current) && isset($Current->Count) && isset($Current->Action)) {
				return array(
					'Progress' => (100 / $Total) * $Current->Count,
					'Action' => $Current->Action.'..'
				);
			} else {
				return array(
					'Progress' => 0,
					'Action' => 'Checking..'
				);
			}
		} else {
			return array(
				'Progress' => 0,
				'Action' => 'Starting..'
			);
		}
	}
	
	public function getReportFiles() {
		$files = array_diff(scandir($this->getDir()['Files'].'/reports/'),array('.', '..','placeholder.txt'));
		return $files;
	}
	
	private function calculateProtectedDifference($te_ik_submitted,$te_customer_last_dns_query) {
		$submitted = new DateTime($te_ik_submitted);
		$lastQuery = new DateTime($te_customer_last_dns_query);
		return $lastQuery->getTimestamp() - $submitted->getTimestamp();
	}
	
	private function calculateVirusTotalDifference($obj) {
		// Workaround for EU / US Realm Alignment
		if ($this->Realm == 'EU') {
			$submitted = new DateTime($obj->te_ik_submitted);
			$vtsubmitted = new DateTime($obj->vt_first_submission_date);
		} elseif ($this->Realm == 'US') {
			$submitted = new DateTime($obj['ThreatActors.ikbfirstsubmittedts']);
			$vtsubmitted = new DateTime($obj['ThreatActors.vtfirstdetectedts']);
		}
		return $vtsubmitted->getTimestamp() - $submitted->getTimestamp();
	}
}

class LicenseAssessment extends ibPortal {
	public function __construct() {
		parent::__construct();
	}

	public function getLicenseCount2($StartDateTime,$EndDateTime,$Realm) {
		// Set Time Dimensions
		$StartDimension = str_replace('Z','',$StartDateTime);
		$EndDimension = str_replace('Z','',$EndDateTime);
		$SpaceRequests = [
			'DNS' => '{"segments":[],"dimensions":["NstarDnsActivity.ip_space_id"],"ungrouped":false,"measures":["NstarDnsActivity.total_query_count"],"timeDimensions":[{"dateRange":["'.$StartDimension.'","'.$EndDimension.'"],"dimension":"NstarDnsActivity.timestamp","granularity":null}]}',
			'DHCP' => '{"segments":[],"dimensions":["NstarLeaseActivity.space_id"],"ungrouped":false,"measures":["NstarLeaseActivity.total_count"],"timeDimensions":[{"dateRange":["'.$StartDimension.'","'.$EndDimension.'"],"dimension":"NstarLeaseActivity.timestamp","granularity":null}]}',
		];
		$SpaceResponses = QueryCubeJSMulti($SpaceRequests);
		$CSPRequests = [];
		$CubeJSRequests = [];
		$CSPRequests[] = $this->QueryCSPMultiRequestBuilder("get",'api/infra/v1/detail_hosts?_limit=10001&_fields=id,display_name,ip_space,site_id',null,'uddi_hosts'); // Collect list of UDDI Hosts
		$CSPRequests[] = $this->QueryCSPMultiRequestBuilder("get",'/api/ddi/v1/dhcp/host?_limit=10001&_fields=id,name,ophid,ip_space',null,'dhcp_hosts'); // Collect list of UDDI Hosts
		$Responses = $this->QueryCSPMulti($CSPRequests);
		$Hosts = $Responses['uddi_hosts']['Body']->results;
		$DHCPHosts = $Responses['dhcp_hosts']['Body']->results;
		$filtered_hosts = array_values(array_filter(json_decode(json_encode($Hosts),true), function($item) {
			return array_key_exists('ip_space', $item);
		}));
		// Collect DNS Metrics
		foreach ($SpaceResponses['DNS']['Body']->result->data as $SpaceWithDNSData) {
			if ($SpaceWithDNSData->{'NstarDnsActivity.ip_space_id'} != '') {
				$Space = 'ipam/ip_space/'.$SpaceWithDNSData->{'NstarDnsActivity.ip_space_id'};
				$SiteIds = [];
				$HostsWithThisSpace = array_keys(array_column($filtered_hosts,'ip_space'),$Space);
				foreach ($HostsWithThisSpace as $HostWithThisSpace) {
					$SiteId = $filtered_hosts[$HostWithThisSpace]['site_id'];
					$SiteIds[] = $SiteId;
				}
				if (count($SiteIds) > 0) {
					// Add DNS Event Query
					$Query = json_encode(array(
						"dimensions" => [
							"NstarDnsActivity.device_ip"
						],
						"ungrouped" => false,
						"timeDimensions" => [
							array(
								"dateRange" => [
									$StartDimension,
									$EndDimension
								],
								"dimension" => "NstarDnsActivity.timestamp",
								"granularity" => null
							)
						],
						"measures" => [
							"NstarDnsActivity.total_query_count"
						],
						"filters" => [
							array(
								"member" => "NstartDnsActivity.site_id",
								"values" => $SiteIds,
								"operator" => "equals"
							)
						]
					));
					$CubeJSRequests['DNS|'.$Space] = $Query;
				}
			}
		}
		// Collect DHCP Metrics
		foreach ($SpaceResponses['DHCP']['Body']->result->data as $SpaceWithDHCPData) {
			if ($SpaceWithDHCPData->{'NstarLeaseActivity.space_id'} != '') {
				$Space = $SpaceWithDHCPData->{'NstarLeaseActivity.space_id'};
				$HostIds = [];
				$DHCPHostsWithThisSpace = array_keys(array_column($DHCPHosts,'ip_space'),$Space);
				foreach ($DHCPHostsWithThisSpace as $DHCPHostWithThisSpace) {
					$HostIds[] = $DHCPHosts[$DHCPHostWithThisSpace]->id;
				}
				
				if (count($HostIds) > 0) {
					$Query = json_encode(array(
						"dimensions" => [
							"NstarLeaseActivity.lease_ip"
						],
						"ungrouped" => false,
						"timeDimensions" => [
							array(
								"dateRange" => [
									$StartDimension,
									$EndDimension
								],
								"dimension" => "NstarLeaseActivity.timestamp",
								"granularity" => null
							)
						],
						"measures" => [
							"NstarLeaseActivity.total_count"
						],
						"filters" => [
							array(
								"member" => "NstarLeaseActivity.host_id",
								"values" => $HostIds,
								"operator" => "contains"
							)
						]
					));
					$CubeJSRequests['DHCP|'.$Space] = $Query;
				}
			}
		}
	
		$CubeJSResults = QueryCubeJSMulti($CubeJSRequests);
		$ResultsArr = array();
		foreach ($CubeJSResults as $CubeJSResultKey => $CubeJSResultVal) {
			$SpaceAndType = explode('|',$CubeJSResultKey);
			$ArrKey = array_search($SpaceAndType[1],array_column($ResultsArr,'IP_Space_ID'));
			if ($ArrKey !== false) {
				$ResultsArr[$ArrKey][$SpaceAndType[0]] = array(
					$SpaceAndType[0].'_IP_Count' => count($CubeJSResultVal['Body']->result->data),
					$SpaceAndType[0].'_IP_Data' => $CubeJSResultVal['Body']->result->data
				);
			} else {
				$ResultsArr[] = array(
					'IP_Space_ID' => $SpaceAndType[1],
					'IP_Space_Name' => 'Placeholder',
					$SpaceAndType[0] => array(
						$SpaceAndType[0].'_IP_Count' => count($CubeJSResultVal['Body']->result->data),
						$SpaceAndType[0].'_IP_Data' => $CubeJSResultVal['Body']->result->data
					)
				);
			}
		}
	
		// Get Combined Metrics
		foreach ($ResultsArr as $ResultKey => $ResultVal) {
			// Extract IP addresses from DHCP data
			if (isset($ResultVal["DHCP"])) {
				$dhcp_ips = array_map(function($entry) {
					return $entry->{'NstarLeaseActivity.lease_ip'};
				}, $ResultVal["DHCP"]["DHCP_IP_Data"]);
			} else {
				$dhcp_ips = [];
			}
	
			// Extract IP addresses from DNS data
			if (isset($ResultVal["DNS"])) {
				$dns_ips = array_map(function($entry) {
					return $entry->{'NstarDnsActivity.device_ip'};
				}, $ResultVal["DNS"]["DNS_IP_Data"]);
			} else {
				$dns_ips = [];
			}
	
			// Combine both arrays
			$all_ips = array_merge($dns_ips, $dhcp_ips);
	
			// Get unique IP addresses
			$unique_ips = array_unique($all_ips);
	
			$ResultsArr[$ResultKey]['Combined'] = array(
				'Combined_IP_Count' => count($unique_ips),
				'Combined_IP_Data' => $all_ips
			);
		}
		return $ResultsArr;
	}
	
	public function getLicenseCount($StartDateTime,$EndDateTime,$Realm) {
		// Set Time Dimensions
		$StartDimension = str_replace('Z','',$StartDateTime);
		$EndDimension = str_replace('Z','',$EndDateTime);
	
		$moreDNSIPs = true;
		$moreDHCPIPs = true;
		$moreDFPIPs = true;
		$offsetDNS = 0;
		$offsetDHCP = 0;
		$offsetDFP = 0;
		$TotalDNSIPCount = 0;
		$TotalDHCPIPCount = 0;
		$TotalDFPIPCount = 0;
	
		while ($moreDNSIPs) {
			$UniqueDNSIPs = $this->QueryCubeJS('{"measures":["NstarDnsActivity.total_query_count"],"segments":[],"dimensions":["NstarDnsActivity.device_ip","NstarDnsActivity.site_id"],"ungrouped":false,"limit":50000,"offset":'.$offsetDNS.',"timeDimensions":[{"dimension":"NstarDnsActivity.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"],"granularity":null}]}');
			if (isset($UniqueDNSIPs->result->data)) {
				$DNSIPCount = count($UniqueDNSIPs->result->data);
				if ($DNSIPCount == 50000) {
					$offsetDNS += 50000;
				} else {
					$moreDNSIPs = false;
				}
				$TotalDNSIPCount += $DNSIPCount;
			} else {
				$TotalDNSIPCount = 0;
				$moreDNSIPs = false;
			}
		}
	
		while ($moreDHCPIPs) {
			$UniqueDHCPIPs = $this->QueryCubeJS('{"measures":["NstarLeaseActivity.total_count"],"segments":[],"dimensions":["NstarLeaseActivity.lease_ip","NstarLeaseActivity.host_id"],"ungrouped":false,"limit":50000,"offset":'.$offsetDHCP.',"timeDimensions":[{"dimension":"NstarLeaseActivity.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"],"granularity":null}]}');
			if (isset($UniqueDHCPIPs->result->data)) {
				$DHCPIPCount = count($UniqueDHCPIPs->result->data);
				if ($DHCPIPCount == 50000) {
					$offsetDHCP += 50000;
				} else {
					$moreDHCPIPs = false;
				}
				$TotalDHCPIPCount += $DHCPIPCount;
			}  else {
				$TotalDHCPIPCount = 0;
				$moreDHCPIPs = false;
			}
		}
	
		while ($moreDFPIPs) {
			$UniqueDFPIPs = $this->QueryCubeJS('{"ungrouped":false,"dimensions":["PortunusAggUserDevices.device_name"],"filters":[{"operator":"equals","member":"PortunusAggUserDevices.type","values":["1"]}],"measures":["PortunusAggUserDevices.deviceCount"],"timeDimensions":[{"granularity":null,"dimension":"PortunusAggUserDevices.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"limit":"50000","offset":'.$offsetDFP.',"segments":[]}');
			if (isset($UniqueDFPIPs->result->data)) {
				$DFPIPCount = count($UniqueDFPIPs->result->data);
				if ($DFPIPCount == 50000) {
					$offsetDFP += 50000;
				} else {
					$moreDFPIPs = false;
				}
				$TotalDFPIPCount += $DFPIPCount;
			}  else {
				$TotalDFPIPCount = 0;
				$moreDFPIPs = false;
			}
		}
	
		$this->api->setAPIResponseData(array(
			'Total' => 0,
			'Unique' => array(
				'DFP' => $TotalDFPIPCount,
				'DNS' => $TotalDNSIPCount,
				'DHCP' => $TotalDHCPIPCount,
			)
		));
	}
}