<?php
$SkipCSS = true;
require_once(__DIR__.'/../../inc/inc.php');

$reportFiles = getReportFiles();
$hoursBeforeExpiry = 4;

foreach ($reportFiles as $reportFile) {
    $FullPath = __DIR__.'/../../../files/reports/'.$reportFile;
    if (is_file($FullPath)) {
        $fileAge = time() - filemtime($FullPath);
        if ($fileAge > 4 * 3600) { // 4 hours in seconds
            unlink($FullPath);
        }
    }
}
?>