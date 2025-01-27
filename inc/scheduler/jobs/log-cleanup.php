<?php
$SkipCSS = true;
require_once(__DIR__.'/../../inc.php');

try {
  $cleaned = false;
  $logFiles = $phpef->logging->getLogFiles();
  $daysBeforeExpiry = $phpef->config->get("System","logging")["retention"] ?? 30;
  $logFileName = $phpef->logging->logFileName;
  foreach ($logFiles as $logFile) {
    preg_match('/'.$logFileName.'-(.*).log/',$logFile, $matches);
    if (isset($matches[1])) {
      if (strtotime($matches[1]) < strtotime('-'.$daysBeforeExpiry.' days')) {
        $cleaned = true;
        echo $logFile." is over ".$daysBeforeExpiry." days old.";
        $fullFilePath = $phpef->logging->logPath . DIRECTORY_SEPARATOR . $logFile;
        if (!unlink($fullFilePath)) {
          $phpef->logging->writeLog("LogCleanup","Error! Unable to delete: ".$logFile,"error");
          $phpef->updateCronStatus('System','Log Cleanup', 'error', 'Error! Unable to delete: '.$logFile);
          break;
        } else {
          $phpef->logging->writeLog("LogCleanup","Successfully deleted: ".$logFile,"info");
          $phpef->updateCronStatus('System','Log Cleanup', 'success', "Successfully deleted: ".$logFile);
        }
      }
    }
  }
  if (!$cleaned) {
    $phpef->updateCronStatus('System','Log Cleanup', 'success', "No logs to clean up");
  }
} catch (Exception $e) {
  $phpef->updateCronStatus('System','Log Cleanup', 'error', $e->getMessage());
}

?>
