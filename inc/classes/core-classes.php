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
  private $config;
  public $configDir;

  public function __construct($conf,$api) {
    $this->configDir = dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'config';
    $this->configFile = $conf;
    $this->api = $api;
    $this->checkConfig();
    $this->cacheConfig();
  }

  private function checkConfig() {
    if (!file_exists($this->configFile)) {
      copy($this->configDir . DIRECTORY_SEPARATOR . 'config.json.example',$this->configFile);
      if ($this->cacheConfig()) {
        $salt = bin2hex(random_bytes(16));
        $this->set($config, array("Security" => array("salt" => $salt)));
      }
    }
  }

  private function cacheConfig() {
    try {
      $this->config = json_decode(file_get_contents($this->configFile),true);
      return true;
    } catch (Exception $e) {
      return false;
    }
  }

  public function get($Section = null,$Option = null) {
    if($Section && $Option) {
      return $this->config[$Section][$Option] ?? null;
    } elseif($Section) {
      return $this->config[$Section] ?? null;
    } else {
      return $this->config ?? null;
    }
  }

  public function setConfig(&$config, $data, $type = null, $name = null) {
    foreach ($data as $key => $value) {
        if (is_array($value) && isset($config[$key]) && is_array($config[$key])) {
            $this->setConfig($config[$key], $value, $type, $name);
        } else {
            if ($type && $name) {
                $config[$type][$name][$key] = $value;
            } else {
                $config[$key] = $value;
            }
        }
    }
    $this->api->setAPIResponseMessage('Successfully updated configuration');
    file_put_contents($this->configFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    $this->cacheConfig();
  }

  public function removeConfig(&$config, $section = null, $option = null) {
    if ($section && $option) {
        unset($config[$section][$option]);
        if (empty($config[$section])) {
            unset($config[$section]);
        }
    } elseif ($section) {
        unset($config[$section]);
    } else {
        return false;
    }
    file_put_contents($this->configFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    $this->api->setAPIResponseMessage('Successfully removed configuration');
    $this->cacheConfig();
    return true;
  }

  public function set(&$config, $data) {
    $config = $this->get();
    $this->setConfig($config, $data);
  }

  public function setPlugin($data, $plugin) {
    $config = $this->get();
    $this->setConfig($config, $data, 'Plugins', $plugin);
  }

  public function setWidget($data, $widget) {
    $config = $this->get();
    $this->setConfig($config, $data, 'Widgets', $widget);
  }

  public function setDashboard(&$config, $data, $dashboard) {
    // Check if Widgets key exists in the current config
    if (isset($config['Dashboards'][$dashboard]['Widgets'])) {
        // Get the current widgets
        $currentWidgets = $config['Dashboards'][$dashboard]['Widgets'];
        // Get the new widgets
        $newWidgets = isset($data['Widgets']) ? $data['Widgets'] : [];
        // Remove widgets that are not in the new data
        foreach ($currentWidgets as $key => $value) {
            if (!array_key_exists($key, $newWidgets)) {
                unset($config['Dashboards'][$dashboard]['Widgets'][$key]);
            }
        }
    }

    foreach ($data as $key => $value) {
        if ($key === 'Widgets') {
            // Ensure Widgets are placed correctly
            $config['Dashboards'][$dashboard]['Widgets'] = $value;
        } elseif ($key === 'Auth') {
            // Ensure Auth is placed correctly
            $config['Dashboards'][$dashboard]['Auth'] = $value;
        } elseif (is_array($value) && isset($config['Dashboards'][$dashboard][$key]) && is_array($config['Dashboards'][$dashboard][$key])) {
            $this->setDashboard($config['Dashboards'][$dashboard][$key], $value, $dashboard);
        } else {
            $config['Dashboards'][$dashboard][$key] = $value;
        }
    }
    $this->api->setAPIResponseMessage('Successfully updated dashboard configuration');
    file_put_contents($this->configFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    $this->cacheConfig();
  }

  public function setRepositories($list) {
    $config = $this->config;
    $config['PluginRepositories'] = $list;
    $this->api->setAPIResponseMessage('Successfully updated repository configuration');
    file_put_contents($this->configFile, json_encode($config, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
    $this->cacheConfig();
  }
}

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Logging {
  private $config;
  public $defaultLogPath;
  public $logPath;
  public $logFileName;

  public function __construct($config) {
    $this->config = $config;
    $this->defaultLogPath = dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'logs';
    $this->logPath = !empty($this->config->get('System','logging')['directory']) ? $this->config->get('System','logging')['directory'] : $this->defaultLogPath;
    $this->logFileName = !empty($this->config->get('System','logging')['filename']) ? $this->config->get('System','logging')['filename'] : "php-ef";
  }

  public function writeLog($Logger, $Message, $Level, $Context = [], $LogFile = "") {
    global $phpef;
    $now = date("d-m-Y");
    if ($LogFile == "") {
      $LogFile = $this->logPath . DIRECTORY_SEPARATOR . $this->logFileName."-".$now.".log";
    }
    $LogLevel = $this->config->get("System","logging")["level"] ?? 'Info';
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
    $files = array_diff(scandir($this->logPath),array('.', '..','php.error.log'));
    return $files;
  }

  public function getLog($date = null) {
    $this->writeLog("LOG","Queried logs","debug");
    if ($date == null) {
      $date = date("d-m-Y");
    }
    $LogFile = $this->logPath . DIRECTORY_SEPARATOR . $this->logFileName."-".$date.".log";
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
    $files = array_diff(scandir($this->logPath),array('.', '..'));
    return $matchArr;
  }
}