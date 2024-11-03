<?php
$SkipCSS = true;
require_once(__DIR__.'/../inc/inc.php');
use GO\Scheduler;
// Create a new scheduler
$scheduler = new Scheduler();

$scheduler->php(__DIR__.'/jobs/report-cleanup.php')->at('*/30 * * * *'); ## Every 30 minutes

// Let the scheduler execute jobs which are due.
$scheduler->run();
?>