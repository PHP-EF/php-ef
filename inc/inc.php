<?php
// Set error log for background tasks
ini_set('error_log',__DIR__.'/logs/php.error.log');
// Include Composer
require_once(__DIR__.'/../vendor/autoload.php');

// Include Classes
foreach (glob(__DIR__.'/classes/' . '*.php') as $class) {
  require_once $class; // Include each PHP file
}

// Instantiate Class Builder
$ib = new ib();

// Include Functions
foreach (glob(__DIR__.'/functions/' . '*.php') as $function) {
  require_once $function; // Include each PHP file
}

if (!(isset($SkipCSS))) {

    echo '
    <head>
      <!-- Required meta tags -->
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <link rel="icon" type="image/x-icon" href="/assets/images/Other/favicon.svg">

      <!-- Main CSS/JS -->
      <script src="https://code.jquery.com/jquery-3.6.3.min.js" crossorigin="anonymous"></script>
      <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
      <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.js"></script>
      <script src="/assets/js/main-0.1.2.js"></script>
      <link href="/assets/css/main-0.0.6.css" rel="stylesheet">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet" crossorigin="anonymous">
      <link rel="stylesheet" href="https://rawgit.com/vitalets/x-editable/master/dist/bootstrap3-editable/css/bootstrap-editable.css" crossorigin="anonymous">
      <link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
      <link href="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.css" rel="stylesheet">

      <!-- datetimepicker -->
      <script src="/assets/js/jquery.datetimepicker.full.min.js"></script>
      <link rel="stylesheet" href="/assets/css/jquery.datetimepicker.css">

      <!-- Bootstrap-Table -->
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.23.5/dist/bootstrap-table.min.css">
      <script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.23.5/dist/bootstrap-table.min.js"></script>
      <script type="text/javascript" src="https://unpkg.com/bootstrap-table@1.23.5/dist/extensions/filter-control/bootstrap-table-filter-control.min.js"></script>
      <script type="text/javascript" src="https://unpkg.com/bootstrap-table@1.23.5/dist/extensions/export/bootstrap-table-export.js"></script>
      <script type="text/javascript" src="/assets/js/tableExport.min.js"></script>

      <!-- FontAwesome -->
      <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" rel="stylesheet" type="text/css"/>

      <!-- Flatpickr -->
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
      <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

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