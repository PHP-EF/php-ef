<?php
// Handle refresh request
if (isset($_GET['refresh'])) {
    unset($_SESSION['dependency_checks']);
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

function checkConfiguration() {
    // Include Composer
    require_once(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

    function dependencyCheck($dependencies, $type = 'installation') {
        $detector = new PlatformDetector();
        $_SESSION['Environment'] = $detector->detectPlatform();
        $IgnoreList = $_SESSION['Environment']['IgnoredChecks'];

        $output = '';
        $allPassed = true;
    
        foreach ($dependencies as $dep => $check) {
            if (!in_array($dep,$IgnoreList)) {
                $output .= "<tr><td>$dep</td>";
                $result = $check();
                $status = $result ? ($type === 'connectivity' ? 'Connected' : 'Installed') : ($type === 'connectivity' ? 'Not Connected' : 'Not Installed');
                $class = $result ? 'installed' : 'not-installed';
        
                if (!is_bool($result)) {
                    if (isset($result)) {
                        if (strpos($result, "not found") === false && strpos($result, "failed") === false && strpos($result, "error") === false) {
                            $status = $type === 'connectivity' ? 'Connected' : 'Installed';
                            $class = 'installed';
                        } else {
                            $status = $type === 'connectivity' ? 'Not Connected' : 'Not Installed';
                            $class = 'not-installed';
                        }
                    } else {
                        $status = $type === 'connectivity' ? 'Not Connected' : 'Not Installed';
                        $class = 'not-installed';
                    }
                    $message = $result;
                } else {
                    $message = $result ? 'Success' : 'Failed';
                }
                $output .= "<td><span class='status $class'>$status</span></td><td>$message</td></tr>";
                if (!$result) {
                    $allPassed = false;
                }
            }
        }
        return [$output, $allPassed];
    }

    function redisConnectivityTest() {
        try {
            $redis = new Predis\Client;
            return $redis->ping() == "PONG" ? 'PONG' : false;
        } catch (Exception $e) {
            return false;
        }
    }

    function dbConnectivityTest() {
        try {
            $appDb = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'app.db';
            $db = new PDO("sqlite:$appDb");
            return true; // Return true if connection is successful
        } catch (Exception $e) {
            return $e->getMessage(); // Return the error message if an exception is raised
        }
    }

    // Define dependencies
    $systemDependencies = [
        // 'supervisor' => function() { return shell_exec("supervisord -v 2>&1"); },
        'redis' => function() { return shell_exec("redis-cli -v"); },
        'git' => function() { return shell_exec("git --version"); },
        'curl' => function() { return function_exists('curl_version'); },
        'composer' => function() { return shell_exec("composer --version"); },
        'nginx' => function() { return shell_exec("nginx -v 2>&1"); }
    ];

    $redisConnection = [
        'Database Connectivity' => 'dbConnectivityTest',
        'Redis Cache Connectivity' => 'redisConnectivityTest'
    ];

    $phpDependencies = [
        'php' => function() { return phpversion(); },
        'php-ldap' => function() { return extension_loaded("ldap"); },
        'php-ctype' => function() { return extension_loaded("ctype"); },
        'php-sqlite3' => function() { return extension_loaded("sqlite3"); },
        'php-pdo' => function() { return extension_loaded("pdo"); },
        'php-pdo_sqlite' => function() { return extension_loaded("pdo_sqlite"); },
        'php-curl' => function() { return extension_loaded("curl"); },
        'php-dom' => function() { return extension_loaded("dom"); },
        'php-fileinfo' => function() { return extension_loaded("fileinfo"); },
        'php-gd' => function() { return extension_loaded("gd"); },
        'php-intl' => function() { return extension_loaded("intl"); },
        'php-mbstring' => function() { return extension_loaded("mbstring"); },
        'php-opcache' => function() { return extension_loaded("Zend OPcache"); },
        'php-openssl' => function() { return extension_loaded("openssl"); },
        'php-phar' => function() { return extension_loaded("phar"); },
        'php-session' => function() { return extension_loaded("session"); },
        'php-tokenizer' => function() { return extension_loaded("tokenizer"); },
        'php-xml' => function() { return extension_loaded("xml"); },
        'php-xmlreader' => function() { return extension_loaded("xmlreader"); },
        'php-xmlwriter' => function() { return extension_loaded("xmlwriter"); },
        'php-simplexml' => function() { return extension_loaded("simplexml"); },
        'php-posix' => function() { return extension_loaded("posix"); }
    ];

    // Check if dependency results are already stored in the session
    if (!isset($_SESSION['dependency_checks'])) {
        // Run checks
        list($systemOutput, $systemPassed) = dependencyCheck($systemDependencies);
        list($connectivityOutput, $connectivityPassed) = dependencyCheck($redisConnection, 'connectivity');
        list($phpOutput, $phpPassed) = dependencyCheck($phpDependencies);

        // Store results in session
        $_SESSION['dependency_checks'] = [
            'systemOutput' => $systemOutput,
            'systemPassed' => $systemPassed,
            'connectivityOutput' => $connectivityOutput,
            'connectivityPassed' => $connectivityPassed,
            'phpOutput' => $phpOutput,
            'phpPassed' => $phpPassed
        ];
    }
    // Retrieve results from session
    return array(
        "systemOutput" => $_SESSION['dependency_checks']['systemOutput'],
        "systemPassed" => $_SESSION['dependency_checks']['systemPassed'],
        "connectivityOutput" => $_SESSION['dependency_checks']['connectivityOutput'],
        "connectivityPassed" => $_SESSION['dependency_checks']['connectivityPassed'],
        "phpOutput" => $_SESSION['dependency_checks']['phpOutput'],
        "phpPassed" => $_SESSION['dependency_checks']['phpPassed'],
    );
}

class PlatformDetector {
    private $environment;

    public function __construct() {
        $this->environment = [
            'Platform' => 'Unknown',
            'IgnoredChecks' => []
        ];
    }

    public function detectPlatform() {
        if ($this->checkLinuxDistro() || $this->checkIfLinux() || $this->checkIfDocker() || $this->checkIfAzureWebsites() || $this->checkIfWindows() || $this->checkIfMac() || $this->checkIfHeroku() || $this->checkIfAWS() ) {
            return $this->environment;
        }
        return null;
    }

    private function checkIfAzureWebsites() {
        if (getenv('WEBSITE_SKU') && getenv('WEBSITE_SCM_SEPARATE_STATUS')) {
            $this->environment['Platform'] = 'Azure Websites';
            $this->environment['IgnoredChecks'][] = 'composer';
            return true;
        }
        return false;
    }

    private function checkIfHeroku() {
        if (getenv('DYNO')) {
            $this->environment['Platform'] = 'Heroku';
            return true;
        }
        return false;
    }

    private function checkIfAWS() {
        if (getenv('AWS_EXECUTION_ENV')) {
            $this->environment['Platform'] = 'AWS';
            return true;
        }
        return false;
    }

    private function checkIfWindows() {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->environment['Platform'] = 'Windows';
            return true;
        }
        return false;
    }

    private function checkIfMac() {
        if (strtoupper(substr(PHP_OS, 0, 6)) === 'DARWIN') {
            $this->environment['Platform'] = 'Mac';
            return true;
        }
        return false;
    }

    private function checkIfLinux() {
        if (strtoupper(substr(PHP_OS, 0, 5)) === 'LINUX') {
            $this->environment['Platform'] = 'Linux';
            return true;
        }
        return false;
    }

    private function checkIfDocker() {
        if (file_exists('/.dockerenv')) {
            $this->environment['Platform'] = 'Docker';
            return true;
        }
        return false;
    }

    private function checkLinuxDistro() {
        if (file_exists('/etc/os-release')) {
            $osRelease = parse_ini_file('/etc/os-release');
            if (isset($osRelease['NAME'])) {
                $this->environment['Platform'] = $osRelease['NAME'];
                return true;
            }
        } elseif (file_exists('/etc/lsb-release')) {
            $lsbRelease = parse_ini_file('/etc/lsb-release');
            if (isset($lsbRelease['DISTRIB_ID'])) {
                $this->environment['Platform'] = $lsbRelease['DISTRIB_ID'];
                return true;
            }
        }
        return false;
    }
}


$cc = checkConfiguration();

// If any check fails, show the dependency check page
if (!$cc['systemPassed'] || !$cc['connectivityPassed'] || !$cc['phpPassed']) {
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
                        <div class='card-body col-md-6'>
                            <h4>System Information</h4>
                            <table class='table table-bordered'>
                                <tbody>
                                    <tr><td><span>Platform</span></td><td><span>Windows</span></td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div class='card-body'>
                            <h4>System Dependencies</h4>
                            <table class='table table-bordered'>
                                <thead>
                                    <tr><th>Dependency</th><th>Status</th><th>Message</th></tr>
                                </thead>
                                <tbody>".$cc['systemOutput']."</tbody>
                            </table>
                            <hr>
                            <h4>Connectivity Tests</h4>
                            <table class='table table-bordered'>
                                <thead>
                                    <tr><th>Dependency</th><th>Status</th><th>Message</th></tr>
                                </thead>
                                <tbody>".$cc['connectivityOutput']."</tbody>
                            </table>
                            <hr>
                            <h4>PHP Dependencies</h4>
                            <table class='table table-bordered'>
                                <thead>
                                    <tr><th>Dependency</th><th>Status</th><th>Message</th></tr>
                                </thead>
                                <tbody>".$cc['phpOutput']."</tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>";
    exit;
}

// Continue with normal flow if all checks pass
?>