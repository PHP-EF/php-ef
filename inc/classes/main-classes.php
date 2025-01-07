<?php
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class ib {
  private $configFilePath;
  private $dbPath;
  public $hooks;
  private $core;
  public $api;
  public $auth;
  public $config;
  public $pages;
  public $logging;
  public $db;
  public $dbHelper;
  public $reporting;
  public $plugins;

  public function __construct() {
    $this->configFilePath = dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.json';
    $this->dbPath = dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'app.db';
    $this->hooks = new hooks();
    $this->core = new core($this->configFilePath,(new api()));
    $this->db = (new db($this->dbPath,$this->core,$this->getVersion()[0]))->db;
    $this->dbHelper = new dbHelper($this->db);
    $this->api = new api($this->core);
    $this->auth = new Auth($this->core,$this->db,$this->api,$this->hooks);
    $this->config = $this->core->config;
    $this->pages = new Pages($this->db,$this->api,$this->core);
    $this->logging = $this->core->logging;
    $this->reporting = new Reporting($this->core,$this->db);
    $this->plugins = new Plugins($this->api,$this->core,$this->db);
    $this->checkDB();
    $this->checkUUID();
  }

  public function getVersion() {
    return ['0.7.4'];
  }

  // Initiate Database Migration if required
  private function checkDB() {
    $currentVersion = $this->dbHelper->getDatabaseVersion();
    $newVersion = $this->getVersion()[0];
    if ($currentVersion < $newVersion) {
        $this->dbHelper->updateDatabaseSchema($currentVersion, $newVersion, $this->dbHelper->migrationScripts());
    }
  }

  private function checkUUID() {
    if (!$this->config->get('System','uuid')) {
      $config = $this->config->get();
      $uuid = array(
        'System' => array(
          'uuid' => \Ramsey\Uuid\Uuid::uuid4()
        )
      );
      $this->config->set($config,$uuid);
    }
  }

	public function checkConfiguration() {
		$status = array();

    $PHPExtensions = checkPHPExtensions();
    $PHPFunctions = checkPHPFunctions();

		if (!file_exists($this->configFilePath)) {
			$status['status'] = 'wizard';
		}
		if (count($PHPExtensions['inactive']) > 0 || !is_writable(dirname(__DIR__, 2))) {
			$status['status'] = 'dependencies';
		}
		$status['status'] = ($status['status']) ?? 'OK';
    $status['version'] = $this->getVersion();
		$status['configWritable'] = is_writable($this->configFilePath) ? true : false;
    $status['dbWritable'] = is_writable($this->dbPath) ? true : false;
		$status['PHP'] = [
      "Version" => phpversion(),
      "User" => get_current_user(),
      "Extensions" => [
        "Active" => $PHPExtensions['active'],
        "Inactive" => $PHPExtensions['inactive']
      ],
      "Functions" => [
        "Active" => $PHPFunctions['active'],
        "Inactive" => $PHPFunctions['inactive']
      ]
    ];
    $status['environment'] = [
      "OS" => $_SESSION['Environment']
    ];
		$status['configFile'] = $this->configFilePath;
		return $status;
	}

  public function settingsOption($type, $name = null, $extras = null) {
    $type = strtolower(str_replace('-', '', $type));
    $setting = [
        'name' => $name,
        'value' => ''
    ];
    switch ($type) {
        case 'auth':
            $settingMerge = [
                'type' => 'select',
                'options' => $this->auth->getRBACRolesForMenu()
            ];
            break;
        case 'enable':
            $settingMerge = [
                'type' => 'switch',
                'label' => 'Enable',
            ];
            break;
        case 'test':
            $Method = $extras['Method'] ?? 'GET';
            $settingMerge = [
                'type' => 'button',
                'label' => 'Test',
                'icon' => 'fa fa-flask',
                'class' => 'pull-right',
                'text' => 'Test',
                'attr' => 'onclick="testAPI(\'' . $Method . '\',\'' . $name . '\')"',
                'help' => 'Remember! Please save before using the test button!'
            ];
            break;
        case 'url':
            $settingMerge = [
                'type' => 'input',
                'label' => 'URL',
                'help' => 'Please make sure to use local IP address and port - You also may use local dns name too.',
                'placeholder' => 'http(s)://hostname:port'
            ];
            break;
        case 'cron':
            $settingMerge = [
                'type' => 'input',
                'label' => 'Cron Schedule',
                // 'help' => 'You may use either Cron format or - @hourly, @daily, @monthly',
                'placeholder' => '* * * * *'
            ];
            break;
        case 'folder':
            $settingMerge = [
                'type' => 'folder',
                'label' => 'Save Path',
                'help' => 'Folder path',
                'placeholder' => '/path/to/folder'
            ];
            break;
        case 'username':
            $settingMerge = [
                'type' => 'input',
                'label' => 'Username',
            ];
            break;
        case 'password':
            $settingMerge = [
                'type' => 'password',
                'label' => 'Password',
                'class' => 'encrypted'
            ];
            break;
        case 'passwordalt':
            $settingMerge = [
                'type' => 'password-alt',
                'label' => 'Password',
            ];
            break;
        case 'passwordaltcopy':
          $settingMerge = [
              'type' => 'password-alt-copy',
              'label' => 'Password',
          ];
          break;
        case 'apikey':
        case 'token':
            $settingMerge = [
                'type' => 'password',
                'label' => 'API Key/Token',
                'class' => 'encrypted'
            ];
            break;
        case 'notice':
            $settingMerge = [
                'type' => 'html',
                'override' => 12,
                'label' => '',
                'html' => '
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="panel panel-' . ($extras['notice'] ?? 'info') . '">
                                <div class="panel-heading">
                                    <span lang="en">' . ($extras['title'] ?? 'Attention') . '</span>
                                </div>
                                <div class="panel-wrapper" aria-expanded="true">
                                    <div class="panel-body">
                                        <span lang="en">' . ($extras['body'] ?? '') . '</span>
                                        <span>' . ($extras['bodyHTML'] ?? '') . '</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    '
            ];
            break;
        case 'about':
            $settingMerge = [
                'type' => 'html',
                'override' => 12,
                'label' => '',
                'html' => '
                    <div class="panel panel-default">
                        <div class="panel-wrapper collapse in">
                            <div class="panel-body">
                                <h3 lang="en">' . ucwords($name) . ' Homepage Item</h3>
                                <p lang="en">' . $extras["about"] . '</p>
                            </div>
                        </div>
                    </div>'
            ];
            break;
        case 'limit':
            $settingMerge = [
                'type' => 'number',
                'label' => 'Item Limit',
            ];
            break;
        case 'blank':
            $settingMerge = [
                'type' => 'blank',
                'label' => '',
            ];
            break;
        case 'precodeeditor':
            $settingMerge = [
                'type' => 'textbox',
                'class' => 'hidden ' . $name . 'Textarea',
                'label' => '',
            ];
            break;
        default:
            $settingMerge = [
                'type' => strtolower($type),
                'label' => ''
            ];
            break;
    }
    $setting = array_merge($settingMerge, $setting);
    if ($extras) {
        if (gettype($extras) == 'array') {
            $setting = array_merge($setting, $extras);
        }
    }
    return $setting;
  }
}

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
    $dbHelper = new dbHelper($this->db);
    if (!$dbHelper->tableExists("pages")) {
      // Create pages table if it doesn't exist
      $this->db->exec("CREATE TABLE IF NOT EXISTS pages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        Name TEXT,
        Title TEXT,
        ACL TEXT,
        Type TEXT,
        Menu TEXT,
        Submenu TEXT,
        Url TEXT,
        LinkType TEXT,
        Icon TEXT,
        Weight INTEGER
      )");

      // Insert default nav links if they don't exist
      $navLinks = [
        ['Home','Home',null,'Link',null,null,'#page=core/default','Native','fa fa-house',1],
        ['Admin','Admin',null,'Menu',null,null,null,null,'fas fa-user-shield',2],
        ['Reports','Reports',null,'Menu',null,null,null,null,'fa-solid fa-chart-simple',3],
        ['Settings','Settings',null,'SubMenu','Admin',null,null,null,'fa fa-cog',1],
        ['Logs','Logs',null,'SubMenu','Admin',null,null,null,'fa-regular fa-file',2],
        ['Users','Users','ADMIN-USERS','SubMenuLink','Admin','Settings','#page=core/users','Native',null,1],
        ['Pages','Pages','ADMIN-PAGES','SubMenuLink','Admin','Settings','#page=core/pages','Native',null,2],
        ['Configuration','Configuration','ADMIN-CONFIG','SubMenuLink','Admin','Settings','#page=core/configuration','Native',null,3],
        ['Role Based Access','Role Based Access','ADMIN-RBAC','SubMenuLink','Admin','Settings','#page=core/rbac','Native',null,4],
        ['Portal Logs','Portal Logs','ADMIN-LOGS','SubMenuLink','Admin','Logs','#page=core/logs','Native',null,1],
        ['Web Tracking','Web Tracking',"REPORT-TRACKING",'MenuLink',"Reports",null,"#page=reports/tracking",'Native','fa-solid fa-bullseye',1]
      ];

      foreach ($navLinks as $link) {
        if (!$this->pageExists($link[0])) {
          $stmt = $this->db->prepare("INSERT INTO pages (Name, Title, ACL, Type, Menu, Submenu, Url, LinkType, Icon, Weight) VALUES (:Name, :Title, :ACL, :Type, :Menu, :Submenu, :Url, :LinkType, :Icon, :Weight)");
          $stmt->execute([':Name' => $link[0],':Title' => $link[1], ':ACL' => $link[2], ':Type' => $link[3], ':Menu' => $link[4], ':Submenu' => $link[5], ':Url' => $link[6], ':LinkType' => $link[7], ':Icon' => $link[8], ':Weight' => $link[9]]);
        }
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
    $stmt = $this->db->prepare("SELECT * FROM pages WHERE id = :id");
    $stmt->execute([':id' => $pageId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function getiFrameLinks() {
    $stmt = $this->db->prepare("SELECT * FROM pages WHERE LinkType = 'iFrame'");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function get() {
    $prepare = "SELECT * FROM pages ORDER BY Weight";
    
    $stmt = $this->db->prepare($prepare);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $users; 
  }

  public function new($Name,$Title,$Type,$Url,$Menu,$Submenu,$ACL,$Icon,$LinkType) {
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
    if (!empty($LinkType)) {
      $prepare[] = 'LinkType';
      $execute[':LinkType'] = $LinkType;
    }
    $valueArray = array_map(function($value) {
      return ':' . $value;
    }, $prepare);
    $stmt = $this->db->prepare("INSERT INTO pages (".implode(", ",$prepare).") VALUES (".implode(', ', $valueArray).")");
    $stmt->execute($execute);
    $this->api->setAPIResponseMessage('Created new page successfully.');
  }

  public function set($ID,$Name,$Title,$Type,$Url,$Menu,$Submenu,$ACL,$Icon,$LinkType) {
    if ($this->getPageById($ID)) {
      $prepare = [];
      $execute = [];
      $stmt = $this->db->prepare('UPDATE pages SET Name = :Name, Title = :Title, Type = :Type, Url = :Url, Menu = :Menu, Submenu = :Submenu, ACL = :ACL, Icon = :Icon, LinkType = :LinkType WHERE id = :id');
      $stmt->execute([':id' => $ID,':Name' => $Name,':Title' => $Title,':Type' => $Type,':Url' => $Url,':Menu' => $Menu,':Submenu' => $Submenu,':ACL' => $ACL,':Icon' => $Icon, ':LinkType' => $LinkType]);
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

  public function getByMenu($Menu = null,$SubMenu = null) {
    $Filters = [];
    $Prepare = "SELECT * FROM pages";
    $Execute = [];
    $Types = [];
    if ($Menu) {
      $Filters[] = 'Menu = :Menu';
      $Types[] = "MenuLink";
      $Types[] = "SubMenu";
      $Execute[':Menu'] = $Menu;
    }
    if ($SubMenu) {
      $Filters[] = 'SubMenu = :SubMenu';
      $Types[] = "SubMenuLink";
      $Execute[':SubMenu'] = $SubMenu;
    }
    if ($Filters) {
      $Prepare .= " WHERE ".implode(" AND ",$Filters);
      if ($Types) {
        $typesArr = array_map(function($value) {
          return "'" . $value . "'";
        }, $Types);
        $Prepare .= ' AND Type IN ('.implode(',',$typesArr).')';        
      }
    }
    $Prepare .= " ORDER BY Weight";

    $stmt = $this->db->prepare($Prepare);
    $stmt->execute($Execute);
    $Pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $Pages; 
  }

  public function getMainLinksAndMenus() {
    $stmt = $this->db->prepare('SELECT * FROM pages WHERE Type IN (\'Menu\',\'Link\') ORDER BY Weight');
    $stmt->execute();
    $Pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $Pages; 
  }

  public function getByType($Type,$Menu = null) {
    $Prepare = "SELECT * FROM pages WHERE Type = :Type";
    $Execute = [':Type' => $Type];
    if ($Menu) {
      $Prepare .= " AND Menu = :Menu";
      $Execute[':Menu'] = $Menu;
    }
    $Prepare .= " ORDER BY Weight";
    $stmt = $this->db->prepare($Prepare);
    $stmt->execute($Execute);
    $Pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $Pages; 
  }

  private function getPagesRecursively($directory,$pluginName = null) {
    $result = [];

    $files = scandir($directory);
    foreach ($files as $file) {
      if ($file === '.' || $file === '..') {
          continue;
      }

      $filePath = $directory . DIRECTORY_SEPARATOR . $file;
      if (is_dir($filePath)) {
        $result = array_merge($result, $this->getPagesRecursively($filePath,$pluginName));
      } else {
        $result[] = [
            'plugin' => $pluginName,
            'directory' => basename($directory),
            'filename' => pathinfo($filePath, PATHINFO_FILENAME)
        ];
      }
    }
    return $result;
  }

  private function getAllPluginPagesRecursively() {
    $pluginPages = [];

    $pluginsDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'plugins';
    if (file_exists($pluginsDir)) {
        $directoryIterator = new DirectoryIterator($pluginsDir);
        foreach ($directoryIterator as $pluginDir) {
            if ($pluginDir->isDir() && !$pluginDir->isDot()) {
                $pagesDir = $pluginDir->getPathname() . DIRECTORY_SEPARATOR . 'pages';
                if (file_exists($pagesDir) && is_dir($pagesDir)) {
                    $pluginPages = array_merge($pluginPages, $this->getPagesRecursively($pagesDir,$pluginDir->getFilename()));
                }
            }
        }
    }
    return $pluginPages;
  }

  public function getAllAvailablePages() {
    $result = array();
    // Get Built In Pages
    $result = array_merge($result, $this->getPagesRecursively(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'pages'));
    // Get Plugin Pages
    $result = array_merge($result, $this->getAllPluginPagesRecursively());
    return $result;
  }

	// Function to manage updating weights of Pages/Menus/Submenus
  public function updatePageWeight($id, $Weight) {
    $Page = $this->getPageById($id);

    if ($Page) {
        $originalWeight = $Page['Weight'];

        // Update the weight of the specific row
        $updateRow = $this->db->prepare("UPDATE pages SET Weight = :Weight WHERE id = :id;");
        $execute = [":id" => $id, ":Weight" => $Weight];

        if ($updateRow->execute($execute)) {
            // Prepare to shift the weights of other rows
            if ($Weight > $originalWeight) {
                $Prepare = 'UPDATE pages SET Weight = Weight - 1 WHERE id != :id AND Weight > :originalWeight AND Weight <= :Weight';
            } else {
                $Prepare = 'UPDATE pages SET Weight = Weight + 1 WHERE id != :id AND Weight < :originalWeight AND Weight >= :Weight';
            }

            $StrictWeights = '';

            if ($Page['Type'] == "Menu" || $Page['Type'] == "Link") {
                $Prepare .= ' AND Type IN (\'Menu\',\'Link\')';
                $StrictWeights = ' WHERE Type IN (\'Menu\',\'Link\')';
            } elseif ($Page['Type'] == "SubMenu" || $Page['Type'] == "MenuLink") {
                $Prepare .= ' AND Type IN (\'SubMenu\',\'MenuLink\') AND Menu = :Menu';
                $StrictWeights = ' WHERE Type IN (\'SubMenu\',\'MenuLink\')';
                $execute[':Menu'] = $Page['Menu'];
            } elseif ($Page['Type'] == "SubMenuLink") {
                $Prepare .= ' AND Type = "SubMenuLink" AND Submenu = :SubMenu';
                $StrictWeights = ' WHERE Type IN (\'SubMenuLink\')';
                $execute[':SubMenu'] = $Page['Submenu'];
            }

            $execute[':originalWeight'] = $originalWeight;
            $updateOtherRows = $this->db->prepare($Prepare);

            if ($updateOtherRows->execute($execute)) {
                // Enforce strict weight assignment
                $enforceConsecutiveWeights = $this->db->prepare('
                    WITH NumberedRows AS (
                        SELECT 
                            id, 
                            ROW_NUMBER() OVER (ORDER BY Weight) AS row_number
                        FROM 
                            pages
                        ' . $StrictWeights . '
                    )
                    UPDATE pages
                    SET Weight = (SELECT row_number FROM NumberedRows WHERE pages.id = NumberedRows.id)
                    ' . $StrictWeights . ';
                ');

                if ($enforceConsecutiveWeights->execute()) {
                    $this->api->setAPIResponseMessage('Successfully updated position');
                    return true;
                }
            }
        }
    } else {
        $this->api->setAPIResponse('Error', 'Column not found');
        return false;
    }
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