<?php
// Load Composer
require_once(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

// Handle refresh request

if (isset($_GET['refresh'])) {
    unset($_SESSION['dependency_checks']);
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

class configurationCheck {
    private $configChecks;

    public function __construct() {
        $this->configChecks = $this->getConfiguration();
    }

    function checkAllPassed() {
        foreach ($this->configChecks as $key => $value) {
            if (is_array($value)) {
                if (isset($value['allPassed']) && !$value['allPassed']) {
                    echo "Error on check: $key => $value";
                    return false;
                }
                if (!$this->checkAllPassed($value)) {
                    return false;
                }
            }
        }
        return true;
    }

    public function getConfiguration() {
        if (isset($_SESSION['configChecks'])) {
            return $_SESSION['configChecks'];
        } else {
            $_SESSION['configChecks'] = $this->checkConfiguration();
            return $_SESSION['configChecks'];
        }
    }

    public function checkConfiguration() {
        return array(
            'PHP' => array(
                'Extensions' => $this->checkPHPExtensions(),
                'Functions' => $this->checkPHPFunctions()
            ),
            'System' => $this->checkSystemDependencies()
        );
    }

    public function buildChecks() {
        $Config = $this->checkConfiguration();
        $output = '';
    
        // Function to generate table rows
        function generateRows($checks) {
            $rows = '';
            foreach ($checks as $Check) {
                $Class = $Check['status'] ? 'installed' : 'not-installed';
                $Status = $Check['status'] ? 'Active' : 'Failed';
                $Message = $Check['message'] ?? '';
                $rows .= "<tr><td>{$Check['name']}</td><td><span class='status $Class'>$Status</span></td><td>$Message</td></tr>";
            }
            return $rows;
        }
    
        // Function to generate tables
        function generateTable($category, $checks) {
            return "
            <div class='card-body col-md-12'>
                <h4>$category</h4>
                <table class='table table-bordered'>
                    <tbody>
                        <th>Dependency</th><th>Status</th><th>Message</th>
                        " . generateRows($checks) . "
                    </tbody>
                </table>
            </div>";
        }
    
        // System Checks
        foreach ($Config['System'] as $SystemCheckCategory => $SystemCheck) {
            $output .= generateTable($SystemCheckCategory, $SystemCheck['checks']);
        }
    
        // PHP Checks
        foreach ($Config['PHP'] as $PHPCheckCategory => $PHPChecks) {
            $output .= generateTable($PHPCheckCategory, $PHPChecks['checks']);
        }
    
        return $output;
    }

    private function dependencyCheck($dependencies, $type = 'installation') {
        $Checks = [];
        $allPassed = true;
        $IgnoreList = $_SESSION['Environment']['IgnoredChecks'] ?? [];
        foreach ($dependencies as $dep => $check) {
            if (!in_array($dep,$IgnoreList)) {
                $result = $check();
                $status = $result ? true : false;

                if (!is_bool($result)) {
                    if (isset($result)) {
                        if (strpos($result, "not found") === false && strpos($result, "failed") === false && strpos($result, "error") === false) {
                            $status = true;
                        } else {
                            $status = false;
                        }
                        $message = $result;
                    }
                }
    
                $Checks[] = array(
                    'name' => $dep,
                    'status' => $status,
                    'message' => $message
                );
                
                if (!$status) {
                    $allPassed = false;
                }
            }
        }
        return [$Checks, $allPassed];
    }
    
    private function checkPHPExtensions() {
        $checks = [];
        $allPassed = true;
        $extensions = array("pdo","pdo_sqlite","sqlite3","curl","dom","fileinfo","gd","intl","mbstring","Zend OPcache","openssl","phar","session","tokenizer","xml","xmlreader","xmlwriter","simplexml","posix","ldap","ctype");
        foreach ($extensions as $check) {
            if (extension_loaded($check)) {
                $checks[] = array(
                    'name' => $check,
                    'status' => true,
                    'message' => 'Installed'
                );
            } else {
                $checks[] = array(
                    'name' => $check,
                    'status' => false,
                    'message' => 'Not Installed'
                );
                $allPassed = false;
            }
        }
        return array(
            'allPassed' => $allPassed,
            'checks' => $checks
        );
    }
    
    private function checkPHPFunctions() {
        $checks = [];
        $allPassed = true;
        $functions = array('hash', 'fopen', 'fsockopen', 'fwrite', 'fclose', 'readfile', 'curl_version');
        foreach ($functions as $check) {
            if (function_exists($check)) {
                $checks[] = array(
                    'name' => $check,
                    'status' => true,
                    'message' => 'Exists'
                );
            } else {
                $checks[] = array(
                    'name' => $check,
                    'status' => false,
                    'message' => 'Does Not Exist'
                );
                $allPassed = false;
            }
        }
        return array(
            'allPassed' => $allPassed,
            'checks' => $checks
        );
    }
    
    private function checkSystemDependencies() {
        // Define dependencies
        $systemDependencies = [
            // 'supervisor' => function() { return shell_exec("supervisord -v 2>&1"); },
            'redis' => function() { return shell_exec("redi1s-cli -v"); },
            'git' => function() { return shell_exec("git --version"); },
            'curl' => function() { return function_exists('curl_version'); },
            'composer' => function() { return shell_exec("composer --version"); },
            'nginx' => function() { return shell_exec("nginx -v 2>&1"); }
        ];
        $Dependencies = $this->dependencyCheck($systemDependencies);
        $Connectivity = $this->checkConnectivity();
        return array(
            'System' => [
                'allPassed' => $Dependencies[1],
                'checks' => $Dependencies[0]
            ],
            'Connectivity' => [
                'allPassed' => $Connectivity[1],
                'checks' => $Connectivity[0]
            ]
        );
    }
    
    private function checkConnectivity() {
        $allPassed = false;
        $Checks = [];
        $redis = $this->checkRedisConnectivity();
        $db = $this->checkDBConnectivity();
        if ($redis && $db) {
            $allPassed = true;
        }
        $Checks[] = array(
            "name" => 'Redis',
            "status" => $redis ? true : false,
            "message" => $redis
        );
        $Checks[] = array(
            "name" => 'Database',
            "status" => $db ? true : false,
            "message" => $db
        );
        return [$Checks,$allPassed];
    }

    private function checkRedisConnectivity() {
        try {
            $redis = new Predis\Client;
            return $redis->ping() == "PONG" ? 'PONG' : false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function checkDBConnectivity() {
        try {
            $appDb = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'app.db';
            $db = new PDO("sqlite:$appDb");
            return true; // Return true if connection is successful
        } catch (Exception $e) {
            return $e->getMessage(); // Return the error message if an exception is raised
        }
    }

    private function checkPlatform() {
        $detector = new PlatformDetector();
        return $detector->detectPlatform();
    }
}

class PlatformDetector {
    private $environment;

    public function __construct() {
        $this->environment = [
            'OS' => 'Unknown'
        ];
    }

    public function detectPlatform() {
        if ($this->checkIfAzureWebsites() || $this->checkIfDocker() || $this->checkIfWindows() || $this->checkIfMac() || $this->checkLinuxDistro() || $this->checkIfLinux() || $this->checkIfHeroku() || $this->checkIfAWS()) {
            return $this->environment;
        }
        return null;
    }

    private function checkIfAzureWebsites() {
        if (getenv("WEBSITE_SKU") && getenv("WEBSITE_STACK")) {
            $this->environment['OS'] = 'Azure Websites';
            return true;
        }
        return false;
    }

    private function checkIfHeroku() {
        if (getenv('DYNO')) {
            $this->environment['OS'] = 'Heroku';
            return true;
        }
        return false;
    }

    private function checkIfAWS() {
        if (getenv('AWS_EXECUTION_ENV')) {
            $this->environment['OS'] = 'AWS';
            return true;
        }
        return false;
    }

    private function checkIfWindows() {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->environment['OS'] = 'Windows';
            return true;
        }
        return false;
    }

    private function checkIfMac() {
        if (strtoupper(substr(PHP_OS, 0, 6)) === 'DARWIN') {
            $this->environment['OS'] = 'Mac';
            return true;
        }
        return false;
    }

    private function checkIfLinux() {
        if (strtoupper(substr(PHP_OS, 0, 5)) === 'LINUX') {
            $this->environment['OS'] = 'Linux';
            return true;
        }
        return false;
    }

    private function checkIfDocker() {
        if (file_exists('/.dockerenv')) {
            $this->environment['OS'] = 'Docker';
            return true;
        }
        return false;
    }

    private function checkLinuxDistro() {
        if (file_exists('/etc/os-release')) {
            $osRelease = parse_ini_file('/etc/os-release');
            if (isset($osRelease['NAME'])) {
                $this->environment['OS'] = $osRelease['NAME'];
                return true;
            }
        } elseif (file_exists('/etc/lsb-release')) {
            $lsbRelease = parse_ini_file('/etc/lsb-release');
            if (isset($lsbRelease['DISTRIB_ID'])) {
                $this->environment['OS'] = $lsbRelease['DISTRIB_ID'];
                return true;
            }
        }
        return false;
    }
}

$configChecker = new configurationCheck();

// If any check fails, show the dependency check page

if ($configChecker->checkAllPassed()) {
    echo "<head>
        <meta charset='utf-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>
        <script src='https://code.jquery.com/jquery-3.6.3.min.js' crossorigin='anonymous'></script>
        <script src='https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js' integrity='sha384-9/reFTGAW83EW2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN' crossorigin='anonymous'></script>
        <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js' integrity='sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM' crossorigin='anonymous'></script>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet' integrity='sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3' crossorigin='anonymous'>
        <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css' rel='stylesheet' type='text/css'/>
        <script src='/assets/js/main.js'></script>
        <link href='/assets/css/main.css' rel='stylesheet'>
        <style>
            .status { font-weight: bold; }
            .installed { color: green; }
            .not-installed { color: red; }
            .card-header { background-color: #007bff; color: white; }
            .card-title { margin-bottom: 0; }
            table { width: 100%; }
            th, td { padding: 10px; text-align: left; }
            .card { width: 100%; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='row justify-content-center'>
                <div class='col-md-6'>
                    <div class='card mt-5'>
                        <div class='card-header'>
                            <div class='alert alert-info'>You must use the Refresh Checks button to re-check, as results are cached in your session.</div>
                            <h3 class='card-title mt-1'>Dependency Checks<button onclick=window.location.href='?refresh=true' class='btn btn-success float-end'>Refresh Checks</button></h3>
                        </div>
                        ".$configChecker->buildChecks()."
                    </div>
                </div>
            </div>
        </div>
    </body>";
    exit;
}

// Continue with normal flow if all checks pass
?>