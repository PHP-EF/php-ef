<?php
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class ib {
  private $core;
  public $api;
  public $auth;
  public $rbac;
  public $config;
  public $pages;
  public $logging;
  public $db;
  public $reporting;

  public function __construct() {
      $this->api = new api();
      $this->db = (new db(__DIR__.'/../config/app.db'))->db;
      $this->core = new core(__DIR__.'/../config/config.json');
      $this->auth = new Auth($this->core,$this->db,$this->api);
      $this->rbac = new RBAC($this->core,$this->db,$this->auth,$this->api);
      $this->config = $this->core->config;
      $this->pages = new Pages($this->db,$this->api,$this->core);
      $this->logging = $this->core->logging;
      $this->reporting = new Reporting($this->core,$this->db);
  }

  public function getVersion() {
    return ['v0.6.8'];
  }
}

class core {
  public $config;
  public $logging;

  public function __construct($configFile) {
    $this->config = new Config($configFile);
    $this->logging = new Logging($this->config);
  }
}

class db {
  public $db;

  public function __construct($dbFile) {
    // Create or open the SQLite database
    $this->db = new PDO("sqlite:$dbFile");
    $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
}

class Config {
  private $configFile;

  public function __construct($conf) {
    $this->configFile = $conf;
  }

  public function get($Section = null,$Option = null) {
    $config_json = json_decode(file_get_contents($this->configFile),true); //Config file that has configurations for site.
    if($Section && $Option) {
      return $config_json[$Section][$Option];
    } elseif($Section) {
      return $config_json[$Section];
    } else {
      return $config_json;
    }
  }

  public function set(&$config, $data) {
    foreach ($data as $key => $value) {
      if (is_array($value) && isset($config[$key]) && is_array($config[$key])) {
          $this->set($config[$key], $value);
      } else {
          $config[$key] = $value;
      }
    }
    file_put_contents($this->configFile, json_encode($config, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
  }
}

class Pages {
  private $db;
  private $api;
  private $logging;

  public function __construct($db,$api,$core) {
    $this->db = $db;
    $this->api = $api;
    $this->logging = $core->logging;
    $this->createPagesTable();
  }

  private function createPagesTable() {
    // Create users table if it doesn't exist
    $this->db->exec("CREATE TABLE IF NOT EXISTS pages (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      Name TEXT,
      Title TEXT,
      ACL TEXT,
      Type TEXT,
      Menu TEXT,
      Submenu TEXT,
      Url TEXT,
      Icon TEXT
    )");

    // Insert default nav links if they don't exist
    $navLinks = [
      ['Home','Home',null,'Link',null,null,'#page=core/default','fa fa-house'],
      ['Admin','Admin',null,'Menu',null,null,null,'fas fa-user-shield'],
      ['Settings','Settings',null,'SubMenu','Admin',null,null,'fa fa-cog'],
      ['Users','Users','ADMIN-USERS','SubMenuLink','Admin','Settings','#page=core/users',null],
      ['Pages','Pages','ADMIN-PAGES','SubMenuLink','Admin','Settings','#page=core/pages',null],
      ['Configuration','Configuration','ADMIN-CONFIG','SubMenuLink','Admin','Settings','#page=core/configuration',null],
      ['Role Based Access','Role Based Access','ADMIN-RBAC','SubMenuLink','Admin','Settings','#page=core/rbac',null],
      ['Reports','Reports',null,'Menu',null,null,null,'fa-solid fa-chart-simple'],
      ['Web Tracking','Web Tracking',"REPORT-TRACKING",'MenuLink',"Reports",null,"#page=reports/tracking",'fa-solid fa-bullseye']
    ];

    foreach ($navLinks as $link) {
      if (!$this->pageExists($link[0])) {
        $stmt = $this->db->prepare("INSERT INTO pages (Name, Title, ACL, Type, Menu, Submenu, Url, Icon) VALUES (:Name, :Title, :ACL, :Type, :Menu, :Submenu, :Url, :Icon)");
        $stmt->execute([':Name' => $link[0],':Title' => $link[1], ':ACL' => $link[2], ':Type' => $link[3], ':Menu' => $link[4], ':Submenu' => $link[5], ':Url' => $link[6], ':Icon' => $link[7]]);
      }
    }
  }

  // Function to check if a page exists in DB
  private function pageExists($pageName) {
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM pages WHERE Name = :name");
    $stmt->execute([':name' => $pageName]);
    return $stmt->fetchColumn() > 0;
  }
  private function getPageById($pageId) {
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM pages WHERE id = :id");
    $stmt->execute([':id' => $pageId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function get() {
    $stmt = $this->db->prepare("SELECT * FROM pages");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $users; 
  }

  public function new($Name,$Title,$Type,$Url,$Menu,$Submenu,$ACL,$Icon) {
    $prepare = [];
    $execute = [];
    if (!empty($Name)) {
      $prepare[] = 'Name';
      $execute[':Name'] = $Name;
    }
    if (!empty($Title)) {
      $prepare[] = 'Title';
      $execute[':Title'] = $Title;
    }
    if (!empty($Type)) {
      $prepare[] = 'Type';
      $execute[':Type'] = $Type;
    }
    if (!empty($Url)) {
      $prepare[] = 'Url';
      $execute[':Url'] = $Url;
    }
    if (!empty($Menu)) {
      $prepare[] = 'Menu';
      $execute[':Menu'] = $Menu;
    }
    if (!empty($Submenu)) {
      $prepare[] = 'Submenu';
      $execute[':Submenu'] = $Submenu;
    }
    if (!empty($ACL)) {
      $prepare[] = 'ACL';
      $execute[':ACL'] = $ACL;
    }
    if (!empty($Icon)) {
      $prepare[] = 'Icon';
      $execute[':Icon'] = $Icon;
    }
    $valueArray = array_map(function($value) {
      return ':' . $value;
    }, $prepare);
    $stmt = $this->db->prepare("INSERT INTO pages (".implode(", ",$prepare).") VALUES (".implode(', ', $valueArray).")");
    $stmt->execute($execute);
    $this->api->setAPIResponseMessage('Created new page successfully.');
  }

  public function set($ID,$Name,$Title,$Type,$Url,$Menu,$Submenu,$ACL,$Icon) {
    if ($this->getPageById($ID)) {
      $prepare = [];
      $execute = [];
      $stmt = $this->db->prepare('UPDATE pages SET Name = :Name, Title = :Title, Type = :Type, Url = :Url, Menu = :Menu, Submenu = :Submenu, ACL = :ACL, Icon = :Icon WHERE id = :id');
      $stmt->execute([':id' => $ID,':Name' => $Name,':Title' => $Title,':Type' => $Type,':Url' => $Url,':Menu' => $Menu,':Submenu' => $Submenu,':ACL' => $ACL,':Icon' => $Icon]);
      $this->api->setAPIResponseMessage('Page updated successfully');
    } else {
      $this->api->setAPIResponse('Error','Page does not exist');
    }
  }

  public function delete($PageID) {
    if ($this->getPageById($PageID)) {
      $stmt = $this->db->prepare("DELETE FROM pages WHERE id = :id");
      $stmt->execute([':id' => $PageID]);
      $this->logging->writeLog("Pages","Deleted Page: $PageID","debug",$_REQUEST);
      $this->api->setAPIResponseMessage('Page deleted successfully');  
    } else {
      $this->logging->writeLog("Pages","Unable to delete Page. The Page does not exist.","error",$_REQUEST);
      $this->api->setAPIResponse('Error','Unable to delete Page. The Page does not exist.');
    }
  }

  public function getByType($Type,$Menu = null) {
    $Prepare = "SELECT * FROM pages WHERE Type = :Type";
    $Execute = [':Type' => $Type];
    if ($Menu) {
      $Prepare .= " AND Menu = :Menu";
      $Execute[':Menu'] = $Menu;
    }
    $stmt = $this->db->prepare($Prepare);
    $stmt->execute($Execute);
    $Pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $Pages; 
  }
}

class Logging {
  private $config;

  public function __construct($config) {
    $this->config = $config;
  }

  public function writeLog($Logger, $Message, $Level, $Context = [], $LogFile = "") {
    global $ib;
    $now = date("d-m-Y");
    if ($LogFile == "") {
      $LogFile = __DIR__.'/../'.$this->config->get("System","logdirectory").$this->config->get("System","logfilename")."-".$now.".log";
    }
    $LogLevel = $this->config->get("System","loglevel");
    $Context2 = json_decode(json_encode($Context), true);
    $log = new Logger($Logger);
    $log->pushProcessor(function ($record) {
      global $ib;
      $AuthObj = $ib->auth->getAuth();
      if (isset($AuthObj['Username'])) {
        $Username = $AuthObj['Username'];
      } else {
        $Username = "N/A";
      }
      if (isset($AuthObj['DisplayName'])) {
        $DisplayName = $AuthObj['DisplayName'];
      } else {
        $DisplayName = "N/A";
      }
      if (isset($AuthObj['IPAddress'])) {
        $IPAddress = $AuthObj['IPAddress'];
      } else {
        $IPAddress = "N/A";
      }
      $record->extra["username"] = $Username;
      $record->extra["displayname"] = $DisplayName;
      $record->extra["ipaddress"] = $IPAddress;
      return $record;
    });
    switch ($LogLevel) {
      case "Debug":
        $log->pushHandler(new StreamHandler($LogFile, Level::Debug));
        break;
      case "Info":
        $log->pushHandler(new StreamHandler($LogFile, Level::Info));
        break;
      case "Warning":
        $log->pushHandler(new StreamHandler($LogFile, Level::Warning));
        break;
      default:
        $log->pushHandler(new StreamHandler($LogFile, Level::Info));
        break;
    };
    if ($Context2) {
      $log->$Level($Message, $Context2);
    } else {
      $log->$Level($Message);
    }
  }

  public function getLogFiles() {
    global $ib;
    $files = array_diff(scandir(__DIR__.'/../'.$this->config->get("System","logdirectory")),array('.', '..','php.error.log'));
    return $files;
  }

  public function getLog($date = null) {
    $this->writeLog("LOG","Queried logs","debug");
    if ($date == null) {
      $date = date("d-m-Y");
    }
    $LogFile = __DIR__.'/../'.$this->config->get("System","logdirectory").$this->config->get("System","logfilename")."-".$date.".log";
    $data = file_get_contents($LogFile);
    preg_match_all('/\[(?<date>.*?)\] (?<logger>\w+).(?<level>\w+): (?<message>[^\[\{]+) (?<context>[\[\{].*[\]\}]) (?<extra>[\[\{].*[\]\}])/',$data, $matches);
    $matchArr = array();
    $count = count($matches[0]);
    while ($count >= 1) {
       $count = --$count;
       if (isset(json_decode($matches[6][$count])->username)) {
         $username = json_decode($matches[6][$count])->username;
       } else {
         $username = "";
       }
       if (isset(json_decode($matches[6][$count])->ipaddress)) {
         $ipaddress = json_decode($matches[6][$count])->ipaddress;
       } else {
         $ipaddress = "";
       }
       if (isset(json_decode($matches[6][$count])->displayname)) {
         $displayname = json_decode($matches[6][$count])->displayname;
       } else {
         $displayname = "";
       }
       $matchArr[] = array(
        "date" => strtotime($matches[1][$count]),
        "logger" => $matches[2][$count],
        "level" => $matches[3][$count],
        "message" => $matches[4][$count],
        "context" => $matches[5][$count],
        "extraData" => $matches[6][$count],
        "username" => $username,
        "ipaddress" => $ipaddress,
        "displayname" => $displayname
      );
    }
    $files = array_diff(scandir(__DIR__.'/../'.$this->config->get("System","logdirectory")),array('.', '..'));
    return $matchArr;
  }
}