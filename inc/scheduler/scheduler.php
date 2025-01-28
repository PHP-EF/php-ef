<?php
$SkipCSS = true;
$NoLogin = true;
require_once(__DIR__.'/../inc.php');
use GO\Scheduler;
// Create a new scheduler
$scheduler = new Scheduler();

// Log Cleanup
$LogCleanupSchedule = !empty($phpef->config->get('System','logging')['cleanupSchedule']) ? $phpef->config->get('System','logging')['cleanupSchedule'] : '0 4 * * *';
$scheduler->php(__DIR__.'/jobs/log-cleanup.php')->at($LogCleanupSchedule);

// Scheduled Backups
$BackupSchedule = !empty($phpef->config->get('System','backup')['schedule']) ? $phpef->config->get('System','backup')['schedule'] : '0 2 * * *';
$scheduler->call(function() {
    global $phpef;
    $phpef->backup();
})->at($BackupSchedule);

/*
 * Include Plugin Cron Jobs
 */
if (file_exists(dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'plugins')) {
    $folder = dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'plugins';
    $directoryIterator = new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS);
    $iteratorIterator = new RecursiveIteratorIterator($directoryIterator);
    foreach ($iteratorIterator as $info) {
        if ($info->getFilename() == 'cron.php') {
            require_once $info->getPathname();
        }
    }
}

// Let the scheduler execute jobs which are due.
$scheduler->run();
?>