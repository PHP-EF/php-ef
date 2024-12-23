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

function isValidFileType($fileName, $validExtensions) {
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    return in_array($fileExtension, $validExtensions);
}




function settingsOption($type, $name = null, $extras = null)
{
    $type = strtolower(str_replace('-', '', $type));
    $setting = [
        'name' => $name,
        'value' => ''
    ];
    switch ($type) {
        case 'enable':
            $settingMerge = [
                'type' => 'switch',
                'label' => 'Enable',
            ];
            break;
        case 'test':
            $settingMerge = [
                'type' => 'button',
                'label' => 'Test Connection',
                'icon' => 'fa fa-flask',
                'class' => 'pull-right',
                'text' => 'Test Connection',
                'attr' => 'onclick="testAPIConnection(\'' . $name . '\')"',
                'help' => 'Remember! Please save before using the test button!'
            ];
            break;
        case 'url':
            $settingMerge = [
                'type' => 'input',
                'label' => 'URL',
                'help' => 'Please make sure to use local IP address and port - You also may use local dns name too.',
                'placeholder' => 'http(s)://hostname:port'
            ];
            break;
        case 'cron':
            $settingMerge = [
                'type' => 'cron',
                'label' => 'Cron Schedule',
                'help' => 'You may use either Cron format or - @hourly, @daily, @monthly',
                'placeholder' => '* * * * *'
            ];
            break;
        case 'folder':
            $settingMerge = [
                'type' => 'folder',
                'label' => 'Save Path',
                'help' => 'Folder path',
                'placeholder' => '/path/to/folder'
            ];
            break;
        case 'username':
            $settingMerge = [
                'type' => 'input',
                'label' => 'Username',
            ];
            break;
        case 'password':
            $settingMerge = [
                'type' => 'password',
                'label' => 'Password',
            ];
            break;
        case 'passwordalt':
            $settingMerge = [
                'type' => 'password-alt',
                'label' => 'Password',
            ];
            break;
        case 'passwordaltcopy':
            $settingMerge = [
                'type' => 'password-alt-copy',
                'label' => 'Password',
            ];
            break;
        case 'apikey':
        case 'token':
            $settingMerge = [
                'type' => 'password-alt',
                'label' => 'API Key/Token',
            ];
            break;
        case 'notice':
            $settingMerge = [
                'type' => 'html',
                'override' => 12,
                'label' => '',
                'html' => '
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="panel panel-' . ($extras['notice'] ?? 'info') . '">
                                <div class="panel-heading">
                                    <span lang="en">' . ($extras['title'] ?? 'Attention') . '</span>
                                </div>
                                <div class="panel-wrapper" aria-expanded="true">
                                    <div class="panel-body">
                                        <span lang="en">' . ($extras['body'] ?? '') . '</span>
                                        <span>' . ($extras['bodyHTML'] ?? '') . '</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    '
            ];
            break;
        case 'about':
            $settingMerge = [
                'type' => 'html',
                'override' => 12,
                'label' => '',
                'html' => '
                    <div class="panel panel-default">
                        <div class="panel-wrapper collapse in">
                            <div class="panel-body">
                                <h3 lang="en">' . ucwords($name) . ' Homepage Item</h3>
                                <p lang="en">' . $extras["about"] . '</p>
                            </div>
                        </div>
                    </div>'
            ];
            break;
        case 'title':
            $settingMerge = [
                'type' => 'input',
                'label' => 'Title',
                'help' => 'Sets the title of this homepage module',
            ];
            break;
        case 'limit':
            $settingMerge = [
                'type' => 'number',
                'label' => 'Item Limit',
            ];
            break;
        case 'blank':
            $settingMerge = [
                'type' => 'blank',
                'label' => '',
            ];
            break;
        case 'precodeeditor':
            $settingMerge = [
                'type' => 'textbox',
                'class' => 'hidden ' . $name . 'Textarea',
                'label' => '',
            ];
            break;
        default:
            $settingMerge = [
                'type' => strtolower($type),
                'label' => ''
            ];
            break;
    }
    $setting = array_merge($settingMerge, $setting);
    if ($extras) {
        if (gettype($extras) == 'array') {
            $setting = array_merge($setting, $extras);
        }
    }
    return $setting;
}