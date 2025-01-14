<?php
// Set error log for background tasks
ini_set('error_log',__DIR__.'/logs/php.error.log');

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
$ib = new ib();

// Include all Plugin Classes
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'plugins')) {
	$folder = __DIR__ . DIRECTORY_SEPARATOR . 'plugins';
	$directoryIterator = new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS);
	$iteratorIterator = new RecursiveIteratorIterator($directoryIterator);
	foreach ($iteratorIterator as $info) {
		if ($info->getFilename() == 'plugin.php') {
			require_once $info->getPathname();
		}
	}
}

// ** Set Headers ** //
// X-Frame-Options
$XFrameOptions = $ib->config->get('Security', 'Headers')['X-Frame-Options'] ?? 'SAMEORIGIN';
$iFrameLinks = $ib->pages->getiFrameLinks();
$AllowList = [];
if (!empty($iFrameLinks)) {
  $AllowList = array_column($iFrameLinks,'Name');
}
$FrameSource = $ib->config->get('Security', 'Headers')['Frame-Source'] ?? implode(' ',$AllowList);
header('X-Frame-Options: ' . $XFrameOptions);
header("Content-Security-Policy:  default-src 'self'; script-src 'self' https://code.jquery.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com 'unsafe-inline' 'unsafe-eval'; style-src 'self' https://fonts.googleapis.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://rawgit.com https://code.jquery.com https://unpkg.com 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com https://fonts.googleapis.com https://fonts.gstatic.com; connect-src 'self' https://plex.tv; object-src 'none'; frame-ancestors 'self'; frame-src 'self' $FrameSource; base-uri 'self'; form-action 'self';");

if (!(isset($SkipCSS))) {
  $faviconPath = $ib->config->get('Styling', 'favicon')['Image'];
  $faviconPath = $faviconPath ? $faviconPath : '/assets/images/favicon.ico';
    echo '
    <head>
      <!-- Required meta tags -->
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

      <link rel="icon" type="image/x-icon" href="' . (file_exists(dirname(__DIR__,1) . $faviconPath) ? $faviconPath : '/assets/images/php-ef-icon.png') . '">

      <!-- Bootstrap / jquery -->
      <script src="https://code.jquery.com/jquery-3.6.3.min.js" crossorigin="anonymous"></script>
      <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
      <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.js"></script>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet" crossorigin="anonymous">
      <link rel="stylesheet" href="https://rawgit.com/vitalets/x-editable/master/dist/bootstrap3-editable/css/bootstrap-editable.css" crossorigin="anonymous">
      <link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
      <link href="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.css" rel="stylesheet">

      <!-- datetimepicker -->
      <script src="/assets/js/jquery.datetimepicker.full.min.js"></script>
      <link rel="stylesheet" href="/assets/css/jquery.datetimepicker.css">

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

      <!-- Main -->
      <script src="/assets/js/main.js?v'.$ib->getVersion()[0].'"></script>
      <link href="/assets/css/main.css?v'.$ib->getVersion()[0].'" rel="stylesheet">

    </head>
    ';

    if(!isset($_COOKIE["theme"])) {
      echo "<body>";
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