<?php

$SkipCSS = true;
require_once(__DIR__.'/../scripts/inc/inc.php');
header('Content-Type: application/json; charset=utf-8');

if (!($_REQUEST['function'])) {
    echo json_encode(array(
        'Error' => 'Function not specified.',
        'Request' => $_REQUEST
    ),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
    die();
} else {
    switch ($_REQUEST['function']) {
        case 'getChangelog':
            if ($method = checkRequestMethod('GET')) {
                $MD = generate_markdown(__DIR__.'/../CHANGELOG.md');
                header('Content-Type: text/html; charset=utf-8');
                echo '<link href="/css/changelog.css" rel="stylesheet">';
                echo '<h1><center>Change Log</center></h1>';
                print_r($MD);
            }
        break;
        case 'createReport':
            if ($method = checkRequestMethod('POST')) {
                if (isset($_POST['APIKey']) AND isset($_POST['StartDateTime']) AND isset($_POST['EndDateTime']) AND isset($_POST['Realm'])) {
                    $response = generateSecurityReport($_POST['APIKey'],$_POST['StartDateTime'],$_POST['EndDateTime'],$_POST['Realm']);
                    echo json_encode($response,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
                }
            }
        break;
        case 'downloadReport':
            if ($method = checkRequestMethod('GET')) {
                if (isset($_REQUEST['id']) AND is_numeric($_REQUEST['id'])) {
                    $id = $_REQUEST['id'];
                    $File = __DIR__.'/../files/reports/report-'.$id.'.pptx';
                    header('Content-type: application/pptx');
                    header('Content-Disposition: inline; filename="report-'.$id.'.pptx"');
                    header('Content-Transfer-Encoding: binary');
                    header('Accept-Ranges: bytes');
                    readfile($File);   
                }
            }
        break;
    }
}