<?php
session_start(); // Start the session

// Handle refresh request
if (isset($_GET['refresh'])) {
    unset($_SESSION['dependency_checks']);
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

// Set error log for background tasks
ini_set('error_log', dirname(__DIR__, 2) . '/inc/logs/php.error.log');



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
                            <h3 class='card-title mt-1'>Dependency Checks<button onclick=window.location.href='?refresh=true' class='btn btn-success float-end'>Refresh Checks</button></h3>
                        </div>
                        <div class='card-body'>
                            <h4>System Dependencies</h4>
                            <table class='table table-bordered'>
                                <thead>
                                    <tr><th>Dependency</th><th>Status</th></tr>
                                </thead>
                                <tbody>".$cc['systemOutput']."</tbody>
                            </table>
                            <hr>
                            <h4>Connectivity Tests</h4>
                            <table class='table table-bordered'>
                                <thead>
                                    <tr><th>Dependency</th><th>Status</th></tr>
                                </thead>
                                <tbody>".$cc['connectivityOutput']."</tbody>
                            </table>
                            <hr>
                            <h4>PHP Dependencies</h4>
                            <table class='table table-bordered'>
                                <thead>
                                    <tr><th>Dependency</th><th>Status</th></tr>
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
// Your normal application code here
?>