<?php
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

function rmdirRecursive($dir) {
    if (!is_dir($dir)) {
        return false;
    }

    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        $path = "$dir/$file";
        if (is_dir($path)) {
            if (!rmdirRecursive($path)) {
                return false;
            }
        } else {
            if (!unlink($path)) {
                return false;
            }
        }
    }

    return rmdir($dir);
}

function array_search_partial($keyword,$arr) {
    foreach($arr as $index => $string) {
        if (preg_match('/'.$keyword.'\b/', $string)) {
            return $index;
        }
    }
}

function replaceTag($Mapping,$TagName,$Value) {
    $TAG = array_search_partial($TagName,$Mapping);
    if ($TAG) {
        $pattern = '/'.$TagName.'\b/';
        $Mapping[$TAG] = preg_replace($pattern, $Value, $Mapping[$TAG]);
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


function decrypt($msg_encrypted_bundle, $password = null){
    global $ib;

    if (!isset($password)) {
        $password = $ib->config->get('Security','salt');
    }

	$password = sha1($password);

	$components = explode( ':', $msg_encrypted_bundle );
	$iv            = $components[0] ?? null;
    $saltToUse = $components[1] ?? null;
	$salt          = hash('sha256', $password . $saltToUse);
	$encrypted_msg = $components[2] ?? null;

    if (isset($iv) && isset($saltToUse) && isset($salt) && isset($encrypted_msg)) {
        $decrypted_msg = openssl_decrypt(
            $encrypted_msg, 'aes-256-cbc', $salt, 0, $iv
        );
      
        if ( $decrypted_msg === false )
            return false;
        return $decrypted_msg;
    } else {
        throw new Exception('Unable to decrypt');
    }
}

function isValidFileType($fileName, $validExtensions) {
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    return in_array($fileExtension, $validExtensions);
}

function sanitizeInput($input) {
    // Replace spaces with underscores
    $input = str_replace(' ', '_', $input);
    
    // Remove special characters
    $input = preg_replace('/[^A-Za-z0-9_]/', '', $input);
    
    return $input;
}

function sanitizePage($input) {
    return htmlspecialchars(strip_tags($input));
}

function getSecureHeaders() {
    global $ib;

    // X-Frame-Options - Add any iFrame Pages to this to ensure they are permitted
    $XFrameOptions = $ib->config->get('Security', 'Headers')['X-Frame-Options'] ?? 'SAMEORIGIN';
    $iFrameLinks = $ib->pages->getiFrameLinks();
    $AllowList = [];
    if (!empty($iFrameLinks)) {
        $AllowList = array_column($iFrameLinks,'Name');
    }
    header('X-Frame-Options: ' . $XFrameOptions);

    // ** Content Security Policy ** //
    
    // Script Sources
    $ScriptSources = implode(' ',[
        "https://code.jquery.com",
        "https://cdn.jsdelivr.net",
        "https://cdnjs.cloudflare.com",
        "https://unpkg.com"
    ]);

    // Style Sources
    $StyleSources = implode(' ',[
        "https://fonts.googleapis.com",
        "https://cdn.jsdelivr.net",
        "https://cdnjs.cloudflare.com",
        "https://rawgit.com",
        "https://code.jquery.com",
        "https://unpkg.com"
    ]);

    // Font Sources
    $FontSources = implode(' ',[
        "https://cdn.jsdelivr.net",
        "https://cdnjs.cloudflare.com",
        "https://unpkg.com",
        "https://fonts.googleapis.com",
        "https://fonts.gstatic.com"
    ]);

    $FrameSource = $ib->config->get('Security', 'Headers')['CSP']['Frame-Source'] ?? implode(' ',$AllowList);
    
    $ConnectSourceFromConfig = $ib->config->get('Security', 'Headers')['CSP']['Connect-Source'] ?? '';

    if (isset($GLOBALS['Headers']['CSP']['Connect-Source'])) {
        $ConnectSource = implode(' ',$GLOBALS['Headers']['CSP']['Connect-Source']) . ' ' . $ConnectSourceFromConfig;
    } else {
        $ConnectSource = $ConnectSourceFromConfig;
    };

    header('X-Frame-Options: ' . $XFrameOptions);
    header("Content-Security-Policy:  default-src 'self'; script-src 'self' $ScriptSources 'unsafe-inline' 'unsafe-eval'; style-src 'self' $StyleSources 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' $FontSources; connect-src 'self' $ConnectSource; object-src 'none'; frame-ancestors 'self'; frame-src 'self' $FrameSource; base-uri 'self'; form-action 'self';");
}