<?php
#require_once(__DIR__.'/../functions/global-functions.php');
require_once(__DIR__.'/../../html/vendor/autoload.php');
if (!(isset($SkipCSS))) {
	
    echo '
    <head>
      <!-- Required meta tags -->
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      
      <!-- Main CSS/JS -->
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet" crossorigin="anonymous">
      <link rel="stylesheet" href="https://rawgit.com/vitalets/x-editable/master/dist/bootstrap3-editable/css/bootstrap-editable.css" crossorigin="anonymous">
      <script src="https://code.jquery.com/jquery-3.6.3.min.js" crossorigin="anonymous"></script>
      <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>



    <!-- Bootstrap-Table -->
      <script src="https://unpkg.com/bootstrap-table@1.22.0/dist/bootstrap-table.min.js"></script>
      <script type="text/javascript" src="https://unpkg.com/bootstrap-table@1.22.0/dist/extensions/filter-control/bootstrap-table-filter-control.min.js"></script>
      <script type="text/javascript" src="https://unpkg.com/bootstrap-table@1.22.0/dist/extensions/export/bootstrap-table-export.js"></script>
      <script src="https://unpkg.com/jquery-resizable-columns@0.2.3/dist/jquery.resizableColumns.min.js"></script>
      <!--<script type="text/javascript" src="https://unpkg.com/bootstrap-table@1.22.0/dist/extensions/cookie/bootstrap-table-cookie.js"></script>-->

      <link rel="stylesheet" href="https://unpkg.com/bootstrap-table@1.22.0/dist/bootstrap-table.min.css">

    <!-- FontAwesome -->
      <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" rel="stylesheet" type="text/css"/>

    </head>
    ';
    
    if(!isset($_COOKIE["theme"])) {
      echo "<body>";
    } else {
      if ($_COOKIE["theme"] == "dark") {
        echo '<body class="dark-theme">';
      } else {
        echo '<body>';
      }
    }

    echo '
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999999" id="toastContainer">
    </div>
    ';
}

?>

