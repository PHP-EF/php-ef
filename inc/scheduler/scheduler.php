<?php
$SkipCSS = true;
require_once(__DIR__.'/../inc.php');
use GO\Scheduler;
// Create a new scheduler
$scheduler = new Scheduler();

$scheduler->php(__DIR__.'/jobs/report-cleanup.php')->at('*/30 * * * *'); ## Every 30 minutes
$scheduler->php(__DIR__.'/jobs/log-cleanup.php')->at('0 10 * * *'); ## Every day at 10AM (UTC)

// Let the scheduler execute jobs which are due.
$scheduler->run();
?>