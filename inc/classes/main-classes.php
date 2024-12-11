<?php
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class ib {
  private $core;
  public $auth;
  public $rbac;
  public $config;
  public $logging;
  public $db;
  public $reporting;
  public $templates;
  public $threatactors;

  public function __construct() {
      $this->db = (new db(__DIR__.'/../config/app.db'))->db;
      $this->core = new core(__DIR__.'/../config/config.json');
      $this->auth = new Auth($this->core,$this->db);
      $this->rbac = new RBAC($this->core,$this->db,$this->auth);
      $this->config = $this->core->config;
      $this->logging = $this->core->logging;
      $this->reporting = new Reporting($this->core,$this->db);
      $this->templates = new TemplateConfig($this->core,$this->db);
      $this->threatactors = new ThreatActorConfig($this->core,$this->db);
  }

  public function getVersion() {
    return ['v0.6.2'];
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

  public function getConfig($Section = null,$Option = null) {
    $config_json = json_decode(file_get_contents($this->configFile),true); //Config file that has configurations for site.
    if($Section && $Option) {
      return $config_json[$Section][$Option];
    } elseif($Section) {
      return $config_json[$Section];
    } else {
      return $config_json;
    }
  }

  public function setConfig($Section,$Key,$Val) {
    $config = $this->getConfig();
    $config[$Section][$Key] = $Val;
    file_put_contents($this->configFile, json_encode($config, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
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
      $LogFile = __DIR__.'/../'.$this->config->getConfig("System","logdirectory").$this->config->getConfig("System","logfilename")."-".$now.".log";
    }
    $LogLevel = $this->config->getConfig("System","loglevel");
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
    $files = array_diff(scandir(__DIR__.'/../'.$this->config->getConfig("System","logdirectory")),array('.', '..','php.error.log'));
    return $files;
  }

  public function getLog($date = "") {
    $this->writeLog("LOG","Queried logs","debug");
    if ($date == "") {
      $date = date("d-m-Y");
    }
    $LogFile = __DIR__.'/../'.$this->config->getConfig("System","logdirectory").$this->config->getConfig("System","logfilename")."-".$date.".log";
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
    $files = array_diff(scandir(__DIR__.'/../'.$this->config->getConfig("System","logdirectory")),array('.', '..'));
    return $matchArr;
  }
}