<?php
$SkipCSS = true;
require_once(__DIR__.'/../../inc.php');

$logFiles = getLogFiles();
$daysBeforeExpiry = getConfig("System","logretention");

foreach ($logFiles as $logFile) {
  preg_match('/ib-sa-tools-(.*).log/',$logFile, $matches);
  if (isset($matches[1])) {
    if (strtotime($matches[1]) < strtotime('-'.$daysBeforeExpiry.' days')) {
      echo $logFile." is over ".$daysBeforeExpiry." days old.";
      $fullFilePath = getConfig("System","logdirectory").$logFile;
      if (!unlink($fullFilePath)) {
        writeLog("LogCleanup","Error! Unable to delete: ".$logFile,"error");
      } else {
        writeLog("LogCleanup","Successfully deleted: ".$logFile,"info");
      }
    }
  }
}
?>
