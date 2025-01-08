<?php
class core {
  public $config;
  public $logging;

  public function __construct($configFile,$api) {
    $this->config = new Config($configFile,$api);
    $this->logging = new Logging($this->config);
  }
}

class Config {
  private $configFile;
  private $api;

  public function __construct($conf,$api) {
    $this->configFile = $conf;
    $this->api = $api;
  }

  public function get($Section = null,$Option = null) {
    $config_json = json_decode(file_get_contents($this->configFile),true); //Config file that has configurations for site.
    if($Section && $Option) {
      return $config_json[$Section][$Option] ?? null;
    } elseif($Section) {
      return $config_json[$Section] ?? null;
    } else {
      return $config_json ?? null;
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
    $this->api->setAPIResponseMessage('Successfully updated configuration');
    file_put_contents($this->configFile, json_encode($config, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
  }

  public function setPlugin(&$config, $data, $plugin) {
    foreach ($data as $key => $value) {
      if (is_array($value) && isset($config[$key]) && is_array($config[$key])) {
          $this->setPlugin($config[$key], $value);
      } else {
          $config['Plugins'][$plugin][$key] = $value;
      }
    }
    $this->api->setAPIResponseMessage('Successfully updated configuration');
    file_put_contents($this->configFile, json_encode($config, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
  }

  public function setRepositories(&$config,$list) {
    $config['PluginRepositories'] = $list;
    $this->api->setAPIResponseMessage('Successfully updated repository configuration');
    file_put_contents($this->configFile, json_encode($config, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
  }
}

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Logging {
  private $config;

  public function __construct($config) {
    $this->config = $config;
  }

  public function writeLog($Logger, $Message, $Level, $Context = [], $LogFile = "") {
    global $phpef;
    $now = date("d-m-Y");
    if ($LogFile == "") {
      $LogFile = __DIR__.'/../'.$this->config->get("System","logdirectory").$this->config->get("System","logfilename")."-".$now.".log";
    }
    $LogLevel = $this->config->get("System","loglevel");
    $Context2 = json_decode(json_encode($Context), true);
    $log = new Logger($Logger);
    $log->pushProcessor(function ($record) {
      global $phpef;
      $AuthObj = $phpef->auth->getAuth();
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
    global $phpef;
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