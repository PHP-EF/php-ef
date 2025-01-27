<?php
session_start(); // Start a PHP session

// Set error log for background tasks
ini_set('error_log',__DIR__.'/logs/php.error.log');

// Set Global Plugins Var
$GLOBALS['plugins'] = [];

// Include Composer
require_once(__DIR__.'/../vendor/autoload.php');

// Include Functions
foreach (glob(__DIR__.'/functions/*.php') as $function) {
  require_once $function; // Include each PHP file
}

// Include Classes
foreach (glob(__DIR__.'/classes/*.php') as $class) {
  require_once $class; // Include each PHP file
}

// Instantiate Class Builder
$phpef = new phpef();

// Include all Plugin Classes & Widgets
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'plugins')) {
	$folder = __DIR__ . DIRECTORY_SEPARATOR . 'plugins';
	$directoryIterator = new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS);
	$iteratorIterator = new RecursiveIteratorIterator($directoryIterator);
	foreach ($iteratorIterator as $info) {
		if ($info->getFilename() == 'plugin.php' || $info->getFilename() == 'widgets.php' ) {
			require_once $info->getPathname();
		}
	}
}

session_write_close(); // Save PHP Session

// Include Widgets
foreach (glob(__DIR__.'/widgets/*.php') as $widget) {
  require_once $widget; // Include each PHP file
}

// ** Set CSP / Frame Headers ** //
getSecureHeaders();

// Force login if setting is enabled
if ($phpef->config->get('Security', 'alwaysRequireLogin')) {
  if (!$phpef->auth->getAuth()['Authenticated']) {
    if (basename($_SERVER['PHP_SELF']) !== 'login.php' && $_SERVER['PHP_SELF'] !== '/api/index.php') {
      header('Location: /login.php');
      exit;
    }
  }
}

if (!(isset($SkipCSS))) {
  $Styling = $phpef->config->get('Styling');
  $faviconPath = $Styling['favicon']['Image'];
  $faviconPath = $faviconPath ? $faviconPath : '/assets/images/favicon.ico';
  $customCSS = $Styling['css']['custom'] ?? '';
    echo '
    <head>
      <!-- Required meta tags -->
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

      <link rel="icon" type="image/x-icon" href="' . (file_exists(dirname(__DIR__,1) . $faviconPath) ? $faviconPath : '/assets/images/php-ef-icon.png') . '">

      <!-- Bootstrap / jquery -->
      <script src="https://code.jquery.com/jquery-3.6.3.min.js" crossorigin="anonymous"></script>
      <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/masonry-layout@4.2.2/dist/masonry.pkgd.min.js" integrity="sha384-GNFwBvfVxBkLMJpYMOABq3c+d3KnQxudP/mGPkzpZSTYykLBNsZEnG2D9G/X/+7D" crossorigin="anonymous" async></script>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet" crossorigin="anonymous">
      <link rel="stylesheet" href="https://rawgit.com/vitalets/x-editable/master/dist/bootstrap3-editable/css/bootstrap-editable.css" crossorigin="anonymous">
      <link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
      <link href="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.css" rel="stylesheet">

      <!-- LazyLoad -->
      <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery.lazy/1.7.10/jquery.lazy.min.js"></script>

      <!-- datetimepicker -->
      <script src="/assets/js/jquery.datetimepicker.full.min.js"></script>
      <link rel="stylesheet" href="/assets/css/jquery.datetimepicker.css">

      <!-- Dropzone -->
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.css">
      <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.js"></script>

      <!-- Dynamic Select -->
      <link rel="stylesheet" href="/assets/css/dynamic-select.css">
      <script src="/assets/js/dynamic-select.js"></script>

      <!-- Bootstrap-Table -->
      <script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.24.0/dist/bootstrap-table.min.js"></script>
      <script type="text/javascript" src="https://unpkg.com/bootstrap-table@1.24.0/dist/extensions/filter-control/bootstrap-table-filter-control.min.js"></script>
      <script type="text/javascript" src="https://unpkg.com/bootstrap-table@1.24.0/dist/extensions/export/bootstrap-table-export.js"></script>
      <script type="text/javascript" src="/assets/js/tableExport.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/tablednd@1.0.5/dist/jquery.tablednd.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.24.0/dist/extensions/reorder-rows/bootstrap-table-reorder-rows.min.js"></script>
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.24.0/dist/bootstrap-table.min.css">
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.24.0/dist/extensions/reorder-rows/bootstrap-table-reorder-rows.css">

      <!-- FontAwesome -->
      <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" rel="stylesheet" type="text/css"/>

      <!-- Flatpickr -->
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
      <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

      <!-- Boxiocns CDN Link -->
      <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">

      <!-- Charts -->
      <script src="/assets/js/apexcharts.min.js"></script>

      <!-- ACE -->
      <script src="https://cdn.jsdelivr.net/npm/ace-builds@1.37.5/src-noconflict/ace.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/ace-builds@1.37.5/src-noconflict/snippets/python.min.js"></script>
      <link href="https://cdn.jsdelivr.net/npm/ace-builds@1.37.5/css/ace.min.css" rel="stylesheet">

      <!-- Main -->
      <script src="/assets/js/main.js?v'.$phpef->getVersion()[0].'"></script>
      <link href="/assets/css/main.css?v'.$phpef->getVersion()[0].'" rel="stylesheet">

    </head>

    <style>
    '.$phpef->buildCustomStyles() . $customCSS.'
    </style>
    ';

    if(!isset($_COOKIE["theme"])) {
      $defaultTheme = $phpef->config->get('Styling','theme')['default'] ?? 'dark';
      echo '<body class="'.$defaultTheme.'-theme">';
    } else {
      if ($_COOKIE["theme"] == "dark") {
        echo '<body class="dark-theme">';
      } else {
        echo '<body class="light-theme">';
      }
    }

    echo '
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999999" id="toastContainer">
    </div>
    ';
}