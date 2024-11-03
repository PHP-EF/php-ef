<?php
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
	  $encrypted_msg, 'aes-256-cbc', $salt, null, $iv
	);

	if ( $decrypted_msg === false )
		return false;
	return $decrypted_msg;
}
