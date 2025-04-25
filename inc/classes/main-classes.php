<?php
class phpef {
    // Traits //
    Use Common,
    Images,
    Settings,
    Cron,
    Style,
    Backups;

    private $configFilePath;
    private $dbPath;
    private $logPath;
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
    public $dashboard;
    public $notifications;

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
        $this->plugins = new Plugins($this->api,$this->core,$this->db,$this->getVersion()[0]);
        $this->dashboard = new Dashboard($this->core);
        $this->notifications = new Notifications($this->core,$this->db,$this->api);
        $this->checkDB();
        $this->checkUUID();
        $this->logPath = $this->core->logging->logPath;
    }

    public function getVersion() {
        return ['0.8.5'];
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

	public function launch() {
        $ConfigCheck = new configurationCheck();

		$status = array();

		if (!file_exists($this->configFilePath)) {
			$status['status'] = 'wizard';
		}
		if (!$ConfigCheck->checkAllPassed()) {
			$status['status'] = 'dependencies';
		}
		$status['status'] = ($status['status']) ?? 'OK';
        $status['version'] = $this->getVersion();
		$status['configWritable'] = is_writable($this->configFilePath) ? true : false;
        $status['dbWritable'] = is_writable($this->dbPath) ? true : false;
        $status['logsWritable'] = is_writable($this->logPath) ? true : false;
		$status['checks'] = $ConfigCheck->configChecks;
        $status['environment'] = [
            "OS" => $_SESSION['Environment']
        ];
		$status['configFile'] = $this->configFilePath;
		return $status;
	}
}