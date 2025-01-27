<?php
$SkipCSS = true;
require_once(__DIR__.'/../inc.php');
use GO\Scheduler;
// Create a new scheduler
$scheduler = new Scheduler();
$scheduler->php(__DIR__.'/jobs/log-cleanup.php')->at('*/30 * * * *');

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