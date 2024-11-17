<?php
$SkipCSS = true;
require_once(__DIR__.'/../../inc.php');

$reportFiles = getReportFiles();
$hoursBeforeExpiry = 4;

$filesCleaned = array(
    'filesCleaned' => [],
    'directoriesCleaned' => []
);

foreach ($reportFiles as $reportFile) {
    $FullPath = __DIR__.'/../../../files/reports/'.$reportFile;
    $fileAge = time() - filemtime($FullPath);
    if ($fileAge > 4 * 3600) { // 4 hours in seconds
        if (is_file($FullPath)) {
            if (!unlink($FullPath)) {
                $ib->logging->writeLog("ReportCleanup","Error! Unable to delete report: ".$reportFile,"error");
            } else {
                $filesCleaned['filesCleaned'][] = $reportFile;
            }
        } else {
            if (!rmdirRecursive($FullPath)) {
                $ib->logging->writeLog("ReportCleanup","Error! Unable to delete directory: ".$reportFile,"error");
            } else {
                $filesCleaned['directoriesCleaned'][] = $reportFile;
            }
        }
    }
}

if (isset($filesCleaned['filesCleaned']) && count($filesCleaned['filesCleaned']) > 0 || isset($filesCleaned['directoriesCleaned']) && count($filesCleaned['directoriesCleaned']) > 0) {
    $ib->logging->writeLog("ReportCleanup","Successfully cleaned up old reports.","info",$filesCleaned);
}
?>