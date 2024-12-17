<?php
$SkipCSS = true;
require_once(__DIR__.'/../../inc.php');
$logFiles = $ib->logging->getLogFiles();
$daysBeforeExpiry = $ib->config->get("System","logretention");
$logFileName = $ib->config->get("System","logfilename");

foreach ($logFiles as $logFile) {
  preg_match('/'.$logFileName.'-(.*).log/',$logFile, $matches);
  if (isset($matches[1])) {
    if (strtotime($matches[1]) < strtotime('-'.$daysBeforeExpiry.' days')) {
      echo $logFile." is over ".$daysBeforeExpiry." days old.";
      $fullFilePath = __DIR__.'/../../'.$ib->config->get("System","logdirectory").$logFile;
      if (!unlink($fullFilePath)) {
        $ib->logging->writeLog("LogCleanup","Error! Unable to delete: ".$logFile,"error");
      } else {
        $ib->logging->writeLog("LogCleanup","Successfully deleted: ".$logFile,"info");
      }
    }
  }
}
?>
