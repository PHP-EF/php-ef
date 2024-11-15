<?php

$SkipCSS = true;
require_once(__DIR__.'/../inc/inc.php');
header('Content-Type: application/json; charset=utf-8');

if (!($_REQUEST['function'])) {
    echo json_encode(array(
        'Error' => 'Function not specified.',
        'Request' => $_REQUEST
    ),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
    die();
} else {
    switch ($_REQUEST['function']) {
        case 'login':
            echo json_encode($auth->login($_POST['un'],$_POST['pw']),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
            break;
        case 'logout':
            echo json_encode($auth->logout(),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
            break;
        case 'getUsers':
            if ($method = checkRequestMethod('GET')) {
                if ($auth->checkAccess(null,"ADMIN-USERS")) {
                    $users = $auth->getAllUsers();
                    echo json_encode($users,JSON_PRETTY_PRINT);
                }
            }
            break;
        case 'newUser':
            if ($method = checkRequestMethod('POST')) {
                if ($auth->checkAccess(null,"ADMIN-USERS")) {
                    if (isset($_POST['un'])) {
                        $UN = $_POST['un'];
                    } else {
                        echo json_encode(array(
                            'Status' => 'Error',
                            'Message' => 'Username missing from request'
                        ));
                        break;
                    }
                    if (isset($_POST['pw'])) {
                        $PW = $_POST['pw'];
                    } else {
                        echo json_encode(array(
                            'Status' => 'Error',
                            'Message' => 'Password missing from request'
                        ));
                        break;
                    }
                    if (isset($_POST['fn'])) {
                        $FN = $_POST['fn'];
                    } else {
                        echo json_encode(array(
                            'Status' => 'Error',
                            'Message' => 'First name missing from request'
                        ));
                        break;
                    }
                    if (isset($_POST['sn'])) {
                        $SN = $_POST['sn'];
                    } else {
                        echo json_encode(array(
                            'Status' => 'Error',
                            'Message' => 'Surname missing from request'
                        ));
                        break;
                    }
                    if (isset($_POST['em'])) {
                        $EM = $_POST['em'];
                    } else {
                        echo json_encode(array(
                            'Status' => 'Error',
                            'Message' => 'Email address missing from request'
                        ));
                        break;
                    }
                    if (isset($_POST['groups'])) {
                        $Groups = $_POST['groups'];
                    } else {
                        $Groups = null;
                    }
                    $new = $auth->newUser($UN,$PW,$FN,$SN,$EM,$Groups);
                    echo json_encode($new,JSON_PRETTY_PRINT);
                }
            }
            break;
        case 'setUser':
            if ($method = checkRequestMethod('POST')) {
                if ($auth->checkAccess(null,"ADMIN-USERS")) {
                    if (isset($_POST['id'])) {
                        $ID = $_POST['id'];
                    } else {
                        echo json_encode(array(
                            'Status' => 'Error',
                            'Message' => 'Invalid User ID'
                        ));
                        break;
                    }
                    if (isset($_POST['fn'])) {
                        $FN = $_POST['fn'];
                    } else {
                        echo json_encode(array(
                            'Status' => 'Error',
                            'Message' => 'First name missing from request'
                        ));
                        break;
                    }
                    if (isset($_POST['sn'])) {
                        $SN = $_POST['sn'];
                    } else {
                        echo json_encode(array(
                            'Status' => 'Error',
                            'Message' => 'Surname missing from request'
                        ));
                        break;
                    }
                    if (isset($_POST['em'])) {
                        $EM = $_POST['em'];
                    } else {
                        echo json_encode(array(
                            'Status' => 'Error',
                            'Message' => 'Email address missing from request'
                        ));
                        break;
                    }
                    if (isset($_POST['un'])) {
                        $UN = $_POST['un'];
                    } else {
                        $UN = null;
                    }
                    if (isset($_POST['pw'])) {
                        $PW = $_POST['pw'];
                    } else {
                        $PW = null;
                    }
                    if (isset($_POST['groups'])) {
                        $Groups = $_POST['groups'];
                    } else {
                        $Groups = null;
                    }
                    $update = $auth->updateUser($ID,$UN,$PW,$FN,$SN,$EM,$Groups);
                    echo json_encode($update,JSON_PRETTY_PRINT);
                }
            }
            break;
        case 'removeUser':
            if ($method = checkRequestMethod('POST')) {
                if ($auth->checkAccess(null,"ADMIN-USERS")) {
                    if (isset($_POST['id'])) {
                        $remove = $auth->removeUser($_POST['id']);
                        echo json_encode($remove,JSON_PRETTY_PRINT);
                    }
                }
            }
            break;
        case 'heartbeat':
            if ($auth->getAuth()['Authenticated'] == true) {
                http_response_code(200);
            } else {
                http_response_code(301);
                echo "Timed out.";
                die();
            }
            break;
        case 'whoami':
            if (isset($auth->getAuth()['Authenticated'])) {
                $AuthContent = $auth->getAuth();
                $AuthContent['headers'] = getallheaders();
                $UnsetHeaders = array(
                    "Remote-Email",
                    "Remote-Groups",
                    "Remote-Name",
                    "Remote-User"
                );
                foreach ($UnsetHeaders as $UnsetHeader) {
                    unset($AuthContent['headers'][$UnsetHeader]);
                }
                echo json_encode($AuthContent,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
            }
            break;
        case 'CheckAccess':
            if (isset($_REQUEST['node']) && $auth->getAuth()['Authenticated'] == true) {
                $Result = array(
                    "node" => $_REQUEST['node']
                );
                if ($auth->checkAccess(null,$_REQUEST['node'])) {
                    $Result['permitted'] = true;
                } else {
                    $Result['permitted'] = false;
                }
                echo json_encode($Result,JSON_PRETTY_PRINT);
            }
            break;
        case 'GetLog':
            if ($auth->checkAccess(null,"ADMIN-LOGS")) {
                if (isset($_REQUEST['date'])) {
                    $Date = $_REQUEST['date'];
                } else {
                    $Date = "";
                }
                echo json_encode(getLog($Date), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
            } else {
                return false;
            }
            break;
        case 'GetRBAC':
            if ($auth->checkAccess(null,"ADMIN-RBAC")) {
                if (isset($_REQUEST['group'])) {
                    $Group = $_REQUEST['group'];
                } else {
                    $Group = null;
                }
                if (isset($_REQUEST['action'])) {
                    $Action = $_REQUEST['action'];
                } else {
                    $Action = null;
                }
                echo json_encode($rbac->getRBAC($Group,$Action), JSON_PRETTY_PRINT);
            } else {
                return false;
            }
            break;
        case 'SetRBAC':
            if ($auth->checkAccess(null,"ADMIN-RBAC")) {
                if (isset($_REQUEST['id'])) {
                    $GroupID = $_REQUEST['id'];
                } else {
                    $GroupID = null;
                }
                if (isset($_REQUEST['name'])) {
                    $GroupName = $_REQUEST['name'];
                } else {
                    $GroupName = null;
                }
                if (isset($_REQUEST['description'])) {
                    $Description = $_REQUEST['description'];
                } else {
                    $Description = null;
                }
                if (isset($_REQUEST['key'])) {
                    $Key = $_REQUEST['key'];
                } else {
                    $Key = null;
                }
                if (isset($_REQUEST['value'])) {
                    $Value = $_REQUEST['value'];
                } else {
                    $Value = null;
                }
                 echo json_encode($rbac->setRBAC($GroupID,$GroupName,$Description,$Key,$Value), JSON_PRETTY_PRINT);
            }
            break;
        case 'DeleteRBAC':
            if ($auth->checkAccess(null,"ADMIN-RBAC")) {
                if (isset($_REQUEST['group'])) {
                    $Group = $_REQUEST['group'];
                } else {
                    $Group = null;
                }
                echo json_encode($rbac->deleteRBAC($Group), JSON_PRETTY_PRINT);
            }
            break;
        case 'GetConfig':
            if ($auth->checkAccess(null,"ADMIN-CONFIG")) {
                $config = getConfig();
                $config['Security']['salt'] = "********";
                echo json_encode($config,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
                writeLog("Config","Queried Configuration","info",$_REQUEST);
            }
            break;
        case 'SetConfig':
            if ($method = checkRequestMethod('POST')) {
                if ($auth->checkAccess(null,"ADMIN-CONFIG")) {
                    $config = getConfig();
                    $config['Security']['salt'] = "********";
                    if (isset($_POST['systemLogFileName'])) { setConfig("System","logfilename",$_POST['systemLogFileName']); }
                    if (isset($_POST['systemLogDirectory'])) { setConfig("System","logdirectory",$_POST['systemLogDirectory']); }
                    if (isset($_POST['systemLogLevel'])) { setConfig("System","loglevel",$_POST['systemLogLevel']); }
                    if (isset($_POST['systemLogRetention'])) { setConfig("System","logretention",$_POST['systemLogRetention']); }
                    if (isset($_POST['systemCURLTimeout'])) { setConfig("System","CURL-Timeout",$_POST['systemCURLTimeout']); }
                    if (isset($_POST['systemCURLTimeoutConnect'])) { setConfig("System","CURL-ConnectTimeout",$_POST['systemCURLTimeoutConnect']); }
                    if (isset($_POST['systemRBACFile'])) { setConfig("System","rbacjson",$_POST['systemRBACFile']); }
                    if (isset($_POST['systemRBACInfoFile'])) { setConfig("System","rbacinfo",$_POST['systemRBACInfoFile']); }
                    if (isset($_POST['securitySalt'])) { setConfig("Security","salt",$_POST['securitySalt']); }
                    if (isset($_POST['securityAssessmentThreatActorSlide'])) { setConfig("SecurityAssessment","ThreatActorSlide",$_POST['securityAssessmentThreatActorSlide']); }
                    if (isset($_POST['securityAssessmentTemplateName'])) { setConfig("SecurityAssessment","TemplateName",$_POST['securityAssessmentTemplateName']); }
                    $newConfig = getConfig();
                    $newConfig['Security']['salt'] = "********";
                    echo json_encode($newConfig,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
                    $logArr = array(
                        "Old Configuration" => $config,
                        "New Configuration" => $newConfig
                    );
                    writeLog("Config","Updated configuration","warning",$logArr);
                }
            }
            break;
        case 'getChangelog':
            if ($method = checkRequestMethod('GET')) {
                $MD = generate_markdown(__DIR__.'/../CHANGELOG.md');
                header('Content-Type: text/html; charset=utf-8');
                echo '<link href="/assets/css/changelog.css" rel="stylesheet">';
                echo '<h1><center>Change Log</center></h1>';
                print_r($MD);
            }
            break;
        case 'getUUID':
            header('Content-type: text/plain');
            echo \Ramsey\Uuid\Uuid::uuid4();
            break;
        case 'createSecurityReport':
            if ($auth->checkAccess(null,"B1-SECURITY-ASSESSMENT")) {
                if ($method = checkRequestMethod('POST')) {
                    if ((isset($_POST['APIKey']) OR isset($_COOKIE['crypt'])) AND isset($_POST['StartDateTime']) AND isset($_POST['EndDateTime']) AND isset($_POST['Realm']) AND isset($_POST['id'])) {
                        if (isValidUuid($_POST['id'])) {
                            $response = generateSecurityReport($_POST['StartDateTime'],$_POST['EndDateTime'],$_POST['Realm'],$_POST['id']);
                            echo json_encode($response,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
                        }
                    }
                }
            }
            break;
        case 'downloadSecurityReport':
            if ($auth->checkAccess(null,"B1-SECURITY-ASSESSMENT")) {
                if ($method = checkRequestMethod('GET')) {
                    writeLog("SecurityAssessment","Downloaded security report","info");
                    if (isset($_REQUEST['id']) AND isValidUuid($_REQUEST['id'])) {
                        $id = $_REQUEST['id'];
                        $File = __DIR__.'/../files/reports/report-'.$id.'.pptx';
                        if (file_exists($File)) {
                            header('Content-type: application/pptx');
                            header('Content-Disposition: inline; filename="report-'.$id.'.pptx"');
                            header('Content-Transfer-Encoding: binary');
                            header('Accept-Ranges: bytes');
                            readfile($File);
                        } else {
                            echo 'Invalid ID';
                        }
                    }
                }
            }
            break;
        case 'getSecurityReportProgress':
            if ($auth->checkAccess(null,"B1-SECURITY-ASSESSMENT")) {
                if ($method = checkRequestMethod('GET')) {
                    if (isset($_REQUEST['id']) AND isValidUuid($_REQUEST['id'])) {
                        $id = $_REQUEST['id'];
                        echo json_encode(getProgress($id,38)); // Produces percentage for use on progress bar
                    }
                }
            }
            break;
        case 'createLicenseReport':
            if ($auth->checkAccess(null,"B1-LICENSE-USAGE")) {
                if ($method = checkRequestMethod('POST')) {
                    if ((isset($_POST['APIKey']) OR isset($_COOKIE['crypt'])) AND isset($_POST['StartDateTime']) AND isset($_POST['EndDateTime']) AND isset($_POST['Realm'])) {
                        $response = getLicenseCount($_POST['StartDateTime'],$_POST['EndDateTime'],$_POST['Realm']);
                        echo json_encode($response,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
                    }
                }
            }
            break;
        case 'crypt':
            if ($method = checkRequestMethod('POST')) {
                if (isset($_POST['key'])) {
                    echo json_encode(array(encrypt($_POST['key'],getConfig("Security","salt"))));
                }
            }
            break;
        case 'getThreatActorConfig':
            if ($auth->checkAccess(null,"ADMIN-SECASS")) {
                if ($method = checkRequestMethod('GET')) {
                    $ThreatActorConfig = [];
                    foreach (getThreatActorConfig() as $Key => $Val) {
                        $ThreatActorConfig[] = array(
                            'Name' => $Key,
                            'SVG' => $Val['SVG'],
                            'PNG' => $Val['PNG'],
                            'URLStub' => $Val['URLStub']
                        );
                    }
                    echo json_encode($ThreatActorConfig,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
                }
            }
            break;
        case 'newThreatActorConfig':
            if ($auth->checkAccess(null,"ADMIN-SECASS")) {
                if ($method = checkRequestMethod('POST')) {
                    if (isset($_POST['name']) AND isset($_POST['URLStub'])) {
                        if (isset($_POST['SVG'])) {
                            $SVG = $_POST['SVG'];
                        } else {
                            $SVG = null;
                        }
                        if (isset($_POST['PNG'])) {
                            $SVG = $_POST['PNG'];
                        } else {
                            $SVG = null;
                        }
                        echo json_encode(newThreatActorConfig($_POST['name'],$SVG,$PNG,$_POST['URLStub']),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
                    }
                }
            }
            break;
        case 'setThreatActorConfig':
            if ($auth->checkAccess(null,"ADMIN-SECASS")) {
                if ($method = checkRequestMethod('POST')) {
                    if (isset($_POST['name']) AND isset($_POST['URLStub'])) {
                        if (isset($_POST['SVG'])) {
                            $SVG = $_POST['SVG'];
                        } else {
                            $SVG = null;
                        }
                        if (isset($_POST['PNG'])) {
                            $SVG = $_POST['PNG'];
                        } else {
                            $SVG = null;
                        }
                        echo json_encode(setThreatActorConfig($_POST['name'],$_POST['SVG'],$_POST['PNG'],$_POST['URLStub']),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
                    }
                }
            }
            break;
        case 'removeThreatActorConfig':
            if ($auth->checkAccess(null,"ADMIN-SECASS")) {
                if ($method = checkRequestMethod('POST')) {
                    if (isset($_POST['name'])) {
                        echo json_encode(removeThreatActorConfig($_POST['name']),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
                    }
                }
            }
            break;
        case 'getThreatActors':
            if ($auth->checkAccess(null,"B1-THREAT-ACTORS")) {
                if ($method = checkRequestMethod('POST')) {
                    if ((isset($_POST['APIKey']) OR isset($_COOKIE['crypt'])) AND isset($_POST['StartDateTime']) AND isset($_POST['EndDateTime']) AND isset($_POST['Realm'])) {
                        $UserInfo = GetCSPCurrentUser();
                        if (isset($UserInfo)) {
                            writeLog("ThreatActors",$UserInfo->result->name." queried list of Threat Actors","info");
                        }
                        $Actors = GetB1ThreatActors($_POST['StartDateTime'],$_POST['EndDateTime']);
                        if (!isset($Actors['Error'])) {
                            echo json_encode(GetB1ThreatActorsById($Actors),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
                        } else {
                            echo json_encode($Actors);
                        };
                    }
                }
            }
            break;
        case 'uploadThreatActorImage':
            if ($auth->checkAccess(null,"B1-THREAT-ACTORS")) {
                if ($method = checkRequestMethod('POST')) {
                    $uploadDir = __DIR__.'/../assets/images/Threat Actors/Uploads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    if (isset($_FILES['svgImage']) && $_FILES['svgImage']['error'] == UPLOAD_ERR_OK) {
                        if (isValidFileType($_FILES['svgImage']['name'],['svg'])) {
                            $svgFileName = basename($_FILES['svgImage']['name']);
                            $svgFilePath = $uploadDir . $svgFileName;
                        
                            // Move the uploaded file to the designated directory
                            if (move_uploaded_file($_FILES['svgImage']['tmp_name'], $svgFilePath)) {
                                $response['svg'] = "SVG image uploaded successfully: $svgFileName";
                            } else {
                                $response['svg'] = "Error uploading SVG image.";
                            }
                        } else {
                            echo json_encode(array(
                                'Status' => 'Error',
                                'Message' => "Invalid SVG File: $svgFileName"
                            ));
                            break;
                        }
                    }
                    
                    // Handle PNG image upload
                    if (isset($_FILES['pngImage']) && $_FILES['pngImage']['error'] == UPLOAD_ERR_OK) {
                            $pngFileName = basename($_FILES['pngImage']['name']);
                            $pngFilePath = $uploadDir . $pngFileName;
                            if (isValidFileType($_FILES['pngImage']['name'],['png'])) {
                            // Move the uploaded file to the designated directory
                            if (move_uploaded_file($_FILES['pngImage']['tmp_name'], $pngFilePath)) {
                                echo json_encode(array(
                                    'Status' => 'Success',
                                    'Message' => "PNG image uploaded successfully: $pngFileName"
                                ));
                            } else {
                                echo json_encode(array(
                                    'Status' => 'Error',
                                    'Message' => "Error uploading PNG image"
                                ));
                            }
                        } else {
                            echo json_encode(array(
                                'Status' => 'Error',
                                'Message' => "Invalid PNG File: $pngFileName"
                            ));
                            break;
                        }
                    }
                }
            }
            break;
    }
}