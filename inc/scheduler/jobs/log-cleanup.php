<?php
$SkipCSS = true;
require_once(__DIR__.'/../../inc.php');
$logFiles = $phpef->logging->getLogFiles();
$daysBeforeExpiry = $phpef->config->get("System","logretention");
$logFileName = $phpef->config->get("System","logfilename");

foreach ($logFiles as $logFile) {
  preg_match('/'.$logFileName.'-(.*).log/',$logFile, $matches);
  if (isset($matches[1])) {
    if (strtotime($matches[1]) < strtotime('-'.$daysBeforeExpiry.' days')) {
      echo $logFile." is over ".$daysBeforeExpiry." days old.";
      $fullFilePath = __DIR__.'/../../'.$phpef->config->get("System","logdirectory").$logFile;
      if (!unlink($fullFilePath)) {
        $phpef->logging->writeLog("LogCleanup","Error! Unable to delete: ".$logFile,"error");
      } else {
        $phpef->logging->writeLog("LogCleanup","Successfully deleted: ".$logFile,"info");
      }
    }
  }
}
?>
