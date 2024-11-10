<?php
function getVersion() {
    return ['v0.5.0'];
}

function getConfig($Section = null,$Option = null) {
    $config_json = json_decode(file_get_contents(__DIR__.'/../config/config.json'),true); //Config file that has configurations for site.
    if($Section && $Option) {
      return $config_json[$Section][$Option];
    } elseif($Section) {
      return $config_json[$Section];
    } else {
      return $config_json;
    }
}

function setConfig($Section,$Key,$Val) {
    $config = getConfig();
    $config[$Section][$Key] = $Val;
    file_put_contents(__DIR__.'/../config/config.json', json_encode($config, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
}

function combineFilters($array) {
    $filterCount = count($array);
    $combinedFilter = null;
    foreach ($array as $arrItem) {
        if ($filterCount <= 1) {
        $combinedFilter = $combinedFilter.$arrItem;
        } else {
        $combinedFilter = $combinedFilter.$arrItem." and ";
        }
        $filterCount = $filterCount - 1;
    }
    return $combinedFilter;
}

function endsWith($string, $endString) {
  $len = strlen($endString);
  if ($len == 0) {
    return true;
  }
  return (substr($string, -$len) === $endString);
}

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

function writeLog($Logger, $Message, $Level, $Context = [], $LogFile = "") {
  $now = date("d-m-Y");
  if ($LogFile == "") {
    $LogFile = __DIR__.'/../'.getConfig("System","logdirectory").getConfig("System","logfilename")."-".$now.".log";
  }
  $LogLevel = getConfig("System","loglevel");
  $Context2 = json_decode(json_encode($Context), true);
  $log = new Logger($Logger);
  $log->pushProcessor(function ($record) {
    $Auth = GetAuth();
    if (isset($Auth['Username'])) {
      $Username = $Auth['Username'];
    } else {
      $Username = "N/A";
    }
    if (isset($Auth['DisplayName'])) {
      $DisplayName = $Auth['DisplayName'];
    } else {
      $DisplayName = "N/A";
    }
    if (isset($Auth['IPAddress'])) {
      $IPAddress = $Auth['IPAddress'];
    } else {
      $IPAddress = "N/A";
    }
    $record->extra["username"] = $Username;
    $record->extra["displayname"] = $DisplayName;
    $record->extra["ipaddress"] = $IPAddress;
    return $record;
  });
  switch ($LogLevel) {
    case "Debug":
      $log->pushHandler(new StreamHandler($LogFile, Level::Debug));
      break;
    case "Info":
      $log->pushHandler(new StreamHandler($LogFile, Level::Info));
      break;
    case "Warning":
      $log->pushHandler(new StreamHandler($LogFile, Level::Warning));
      break;
    default:
      $log->pushHandler(new StreamHandler($LogFile, Level::Info));
      break;
  };
  if ($Context2) {
    $log->$Level($Message, $Context2);
  } else {
    $log->$Level($Message);
  }
}

function getLogFiles() {
  $files = array_diff(scandir(__DIR__.'/../'.getConfig("System","logdirectory")),array('.', '..'));
  return $files;
}

function getLog($date = "") {
  writeLog("LOG","Queried logs","debug");
  if ($date == "") {
    $date = date("d-m-Y");
  }
  $LogFile = __DIR__.'/../'.getConfig("System","logdirectory").getConfig("System","logfilename")."-".$date.".log";
  $data = file_get_contents($LogFile);
  preg_match_all('/\[(?<date>.*?)\] (?<logger>\w+).(?<level>\w+): (?<message>[^\[\{]+) (?<context>[\[\{].*[\]\}]) (?<extra>[\[\{].*[\]\}])/',$data, $matches);
  $matchArr = array();
  $count = count($matches[0]);
  while ($count >= 1) {
     $count = --$count;
     if (isset(json_decode($matches[6][$count])->username)) {
       $username = json_decode($matches[6][$count])->username;
     } else {
       $username = "";
     }
     if (isset(json_decode($matches[6][$count])->ipaddress)) {
       $ipaddress = json_decode($matches[6][$count])->ipaddress;
     } else {
       $ipaddress = "";
     }
     if (isset(json_decode($matches[6][$count])->displayname)) {
       $displayname = json_decode($matches[6][$count])->displayname;
     } else {
       $displayname = "";
     }
     $matchArr[] = array(
      "date" => strtotime($matches[1][$count]),
      "logger" => $matches[2][$count],
      "level" => $matches[3][$count],
      "message" => $matches[4][$count],
      "context" => $matches[5][$count],
      "extraData" => $matches[6][$count],
      "username" => $username,
      "ipaddress" => $ipaddress,
      "displayname" => $displayname
    );
  }
  $files = array_diff(scandir(__DIR__.'/../'.getConfig("System","logdirectory")),array('.', '..'));
  return $matchArr;
}

function querySQL($Query,$Action,$FetchArr = true) {
    $serverName = getConfig("SQL","sqlserver"); //serverName\instanceName, portNumber (default is 1433)
    $connectionInfo = array( "Database"=>getConfig("SQL","sqldb"), "UID"=>getConfig("SQL","sqluser"), "PWD"=>getConfig("SQL","sqlpass"), "Encrypt"=>"no");
    $conn = sqlsrv_connect( $serverName, $connectionInfo);
    $log = array(
        "query" => $Query,
        "action" => $Action
    );
    if( $conn ) {
        writeLog("SQL","$Action action performed on the SQL Database","debug",$log);
        $resource = sqlsrv_query($conn,$Query);
        switch ($Action) {
            case "select":
                if ($FetchArr) {
                    $i = 0;
                    while( $row = sqlsrv_fetch_array( $resource, SQLSRV_FETCH_ASSOC )) {
                        $result[$i] = $row;
                        $i++;
                    }
                    if (isset($result)) {
                        return $result;
                    } else {
                        return false;
                    }
                } else {
                    return $Query;
                }
                break;
            case "update":
                $rowsaffected = sqlsrv_rows_affected( $resource );
                if ($rowsaffected <= 0) {
                    writeLog("SQL","Error. Failed to update SQL table","error",$log);
                    return false;
                } else {
                    return $rowsaffected;
                }
                break;
            case "insert":
                if ($resource) {
                    $rowsaffected = sqlsrv_rows_affected( $resource );
                    if ($rowsaffected <= 0) {
                        writeLog("SQL","Error. Failed to insert row into SQL table","error",$log);
                        return false;
                    } else {
                        return $rowsaffected;
                    }
                } else {
                    writeLog("SQL","Error inserting row into SQL table.","error",$log);
                    return false;
                }
                break;
            case "delete":
                $rowsaffected = sqlsrv_rows_affected( $resource );
                if ($rowsaffected <= 0) {
                    writeLog("SQL","Error. Failed to delete row from SQL table","error",$log);
                    return false;
                } else {
                    return $rowsaffected;
                }
                break;
        }
    } else {
        echo "Connection could not be established.<br />";
        $connectionInfo['PWD'] = "****";
        writeLog("SQL","Error. Connection could not be established","error",$connectionInfo);
        die( print_r( sqlsrv_errors(), true));
    }
}

function compareByTimestamp($time1, $time2) {
    if (strtotime($time1) < strtotime($time2))
        return 1;
    else if (strtotime($time1) > strtotime($time2))
        return -1;
    else
        return 0;
}

function isJson($str) {
    $json = json_decode($str);
    return $json && $str != $json;
}

function stripslashes_deep($value) {
    $value = is_array($value) ?
    array_map('stripslashes_deep', $value) :
        stripslashes($value);
        return $value;
}

function searchForKeyValue($InKey, $InVal, $InArray) {
    foreach ($InArray as $key => $val) {
        if ($val->$InKey == $InVal) {
            return $key;
        }
    }
    return null;
}

function generate_markdown($file) {
    $mkd = \FastVolt\Helper\Markdown::new();
    $mkd -> setFile( $file );
    return $mkd -> toHtml();
}

function number_abbr($number)
{
    if (isset($number)) {
        $abbrevs = [12 => 'T', 9 => 'B', 6 => 'M', 3 => 'K', 0 => ''];

        foreach ($abbrevs as $exponent => $abbrev) {
            if (abs($number) >= pow(10, $exponent)) {
                $display = $number / pow(10, $exponent);
                $decimals = ($exponent >= 3 && round($display) < 100) ? 1 : 0;
                $number = number_format($display, $decimals).$abbrev;
                break;
            }
        }

        return $number;
    } else {
        return 0;
    }
}

function extractZip(string $filePath, string $tempPath) {
    $zip = new ZipArchive;
    $opened = $zip->open($filePath);
    if ($opened !== TRUE) {
        throw new PptxFileException( 'Could not open zip archive ' . $filePath . '[' . $opened . ']' );
    }
    $zip->extractTo($tempPath);
    $zip->close();
}

function compressZip(string $saveLocation, string $archiveLocation) {
    //Create a pptx file again
    $zip = new ZipArchive;

    $opened = $zip->open($saveLocation, ZIPARCHIVE::CREATE | ZipArchive::OVERWRITE);
    if ($opened !== true) {
        throw new PptxFileException( 'Cannot open zip: ' . $saveLocation . ' [' . $opened . ']' );
    }

    // Create recursive directory iterator
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($archiveLocation), RecursiveIteratorIterator::LEAVES_ONLY);

    foreach($files as $name => $file) {
        $filePath = $file->getRealPath();
        if (in_array($file->getFilename(), array('.', '..'))) {
            continue;
        }
        if (!file_exists($filePath)) {
            throw new PptxFileException( 'File does not exists: ' . $file->getPathname() );
        } else {
            if (!is_readable($filePath)) {
                throw new PptxFileException( 'File is not readable: ' . $file->getPathname() );
            } else {
                if (!$zip->addFile($filePath, substr($file->getPathname(), strlen($archiveLocation) + 1))) {
                    throw new PptxFileException( 'Error adding file: ' . $file->getPathname() );
                }
            }
        }
    }
    if (!$zip->close()) {
        throw new PptxFileException( 'Could not create zip file' );
    }
}

function rmdirRecursive($dir)
{
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach($files as $file) {
        (is_dir("$dir/$file")) ? rmdirRecursive("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}

function array_search_partial($keyword,$arr) {
    foreach($arr as $index => $string) {
        if (strpos($string, $keyword) !== FALSE)
            return $index;
    }
}

function replaceTag($Mapping,$TagName,$Value) {
    $TAG = array_search_partial($TagName,$Mapping);
    if ($TAG) {
        $Mapping[$TAG] = str_replace($TagName, $Value, $Mapping[$TAG]);
    }
    return $Mapping;
}

function checkRequestMethod($Method,$ReturnInfo = false) {
    if ($_SERVER['REQUEST_METHOD'] == $Method) {
        if ($ReturnInfo) {
            return array(
                'Matches' => true,
                'MethodUsed' => $_SERVER['REQUEST_METHOD'],
                'MethodRequested' => $Method
            );
        } else {
            return true;
        }
    } else {
        if ($ReturnInfo) {
            return array(
                'Matches' => false,
                'MethodUsed' => $_SERVER['REQUEST_METHOD'],
                'MethodRequested' => $Method
            );
        } else {
            return false;
        }
    }
}

function isValidUuid(mixed $uuid): bool
{
    return is_string($uuid) && preg_match('/^[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $uuid);
}

function encrypt($data, $password){
	$iv = substr(sha1(mt_rand()), 0, 16);
	$password = sha1($password);

	$salt = sha1(mt_rand());
	$saltWithPassword = hash('sha256', $password.$salt);

	$encrypted = openssl_encrypt(
	  "$data", 'aes-256-cbc', "$saltWithPassword", null, $iv
	);
	$msg_encrypted_bundle = "$iv:$salt:$encrypted";
	return $msg_encrypted_bundle;
}


function decrypt($msg_encrypted_bundle, $password){
	$password = sha1($password);

	$components = explode( ':', $msg_encrypted_bundle );
	$iv            = $components[0];
	$salt          = hash('sha256', $password.$components[1]);
	$encrypted_msg = $components[2];

	$decrypted_msg = openssl_decrypt(
	  $encrypted_msg, 'aes-256-cbc', $salt, 0, $iv
	);

	if ( $decrypted_msg === false )
		return false;
	return $decrypted_msg;
}