<?php

$SkipCSS = true;
require_once(__DIR__.'/../inc/inc.php');
header('Content-Type: application/json; charset=utf-8');
if (!($_REQUEST['f'])) {
    echo json_encode(array(
        'Error' => 'Function not specified.',
        'Request' => $_REQUEST
    ),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
    die();
} else {
    switch ($_REQUEST['f']) {
        // case 't':
        //     $ib->reporting->track(json_decode(file_get_contents('php://input'), true),$ib->auth->getAuth());
        //     http_response_code(201);
        //     break;
        // case 'login':
        //     echo json_encode($ib->auth->login($_POST['un'],$_POST['pw']),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
        //     break;
        // case 'logout':
        //     echo json_encode($ib->auth->logout(),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
        //     break;
        // case 'sso':
        //     if ($ib->config->getConfig('SAML','enabled')) {
        //         $ib->auth->sso();
        //     } else {
        //         echo json_encode(array(
        //             'Status' => 'Error',
        //             'Message' => 'SSO is not enabled.'
        //         ),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
        //     }
        //     break;
        // case 'slo':
        //     if ($ib->config->getConfig('SAML','enabled')) {
        //         $ib->auth->slo();
        //     } else {
        //         echo json_encode(array(
        //             'Status' => 'Error',
        //             'Message' => 'SSO is not enabled.'
        //         ),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
        //     }
        //     break;
        // case 'acs':
        //     if ($ib->config->getConfig('SAML','enabled')) {
        //         if (checkRequestMethod('POST') && isset($_POST['SAMLResponse'])) {
        //             echo json_encode($ib->auth->acs(),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
        //         }
        //     } else {
        //         echo json_encode(array(
        //             'Status' => 'Error',
        //             'Message' => 'SSO is not enabled.'
        //         ),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
        //     }
        //     break;
        // case 'samlMetadata':
        //     if ($ib->config->getConfig('SAML','enabled')) {
        //         echo $ib->auth->getSamlMetadata();
        //     } else {
        //         echo json_encode(array(
        //             'Status' => 'Error',
        //             'Message' => 'SSO is not enabled.'
        //         ),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
        //     }
        //     break;
        // case 'getUsers':
        //     if (checkRequestMethod('GET')) {
        //         if ($ib->rbac->checkAccess("ADMIN-USERS")) {
        //             $users = $ib->auth->getAllUsers();
        //             echo json_encode($users,JSON_PRETTY_PRINT);
        //         }
        //     }
        //     break;
        case 'newUser':
            if (checkRequestMethod('POST')) {
                if ($ib->rbac->checkAccess("ADMIN-USERS")) {
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
                    if (isset($_POST['expire'])) {
                        $Expire = $_POST['expire'];
                    } else {
                        $Expire = 'false';
                    }
                    $new = $ib->auth->newUser($UN,$PW,$FN,$SN,$EM,$Groups,'Local',$Expire);
                    echo json_encode($new,JSON_PRETTY_PRINT);
                }
            }
            break;
        // case 'setUser':
        //     if (checkRequestMethod('POST')) {
        //         if ($ib->rbac->checkAccess("ADMIN-USERS")) {
        //             if (isset($_POST['id'])) {
        //                 $ID = $_POST['id'];
        //             } else {
        //                 echo json_encode(array(
        //                     'Status' => 'Error',
        //                     'Message' => 'Invalid User ID'
        //                 ));
        //                 break;
        //             }
        //             if (isset($_POST['fn'])) {
        //                 $FN = $_POST['fn'];
        //             } else {
        //                 $FN = null;
        //             }
        //             if (isset($_POST['sn'])) {
        //                 $SN = $_POST['sn'];
        //             } else {
        //                 $SN = null;
        //             }
        //             if (isset($_POST['em'])) {
        //                 $EM = $_POST['em'];
        //             } else {
        //                 $EM = null;
        //             }
        //             if (isset($_POST['un'])) {
        //                 $UN = $_POST['un'];
        //             } else {
        //                 $UN = null;
        //             }
        //             if (isset($_POST['pw'])) {
        //                 $PW = $_POST['pw'];
        //             } else {
        //                 $PW = null;
        //             }
        //             if (isset($_POST['groups'])) {
        //                 $Groups = $_POST['groups'];
        //             } else {
        //                 $Groups = null;
        //             }
        //             $update = $ib->auth->updateUser($ID,$UN,$PW,$FN,$SN,$EM,$Groups);
        //             echo json_encode($update,JSON_PRETTY_PRINT);
        //         }
        //     }
        //     break;
        // case 'removeUser':
        //     if (checkRequestMethod('POST')) {
        //         if ($ib->rbac->checkAccess("ADMIN-USERS")) {
        //             if (isset($_POST['id'])) {
        //                 $remove = $ib->auth->removeUser($_POST['id']);
        //                 echo json_encode($remove,JSON_PRETTY_PRINT);
        //             }
        //         }
        //     }
        //     break;
        // case 'heartbeat':
        //     if ($ib->auth->getAuth()['Authenticated'] == true) {
        //         http_response_code(200);
        //     } else {
        //         http_response_code(301);
        //         echo "Session timed out.";
        //         die();
        //     }
        //     break;
        // case 'whoami':
        //     if (isset($ib->auth->getAuth()['Authenticated'])) {
        //         $AuthContent = $ib->auth->getAuth();
        //         $AuthContent['headers'] = getallheaders();
        //         $UnsetHeaders = array(
        //             "Remote-Email",
        //             "Remote-Groups",
        //             "Remote-Name",
        //             "Remote-User"
        //         );
        //         foreach ($UnsetHeaders as $UnsetHeader) {
        //             unset($AuthContent['headers'][$UnsetHeader]);
        //         }
        //         echo json_encode($AuthContent,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
        //     }
        //     break;
        case 'resetPassword':
            if (checkRequestMethod('POST')) {
                if (isset($_POST['pw'])) {
                    echo json_encode($ib->auth->resetPassword($_POST['pw']),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
                } else {
                    echo json_encode(array(
                        'Status' => 'Error',
                        'Message' => 'New password missing from request'
                    ));
                }
            }
            break;
        case 'resetExpiredPassword':
            if (checkRequestMethod('POST')) {
                if (isset($_POST['un']) && isset($_POST['cpw']) && isset($_POST['pw'])) {
                    echo json_encode($ib->auth->resetExpiredPassword($_REQUEST['un'],$_POST['cpw'],$_REQUEST['pw']),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
                } else {
                    echo json_encode(array(
                        'Status' => 'Error',
                        'Message' => 'Required values missing from the request'
                    ));
                }
            }
            break;
        case 'CheckAccess':
            if (isset($_REQUEST['node'])) {
                $Result = array(
                    "node" => $_REQUEST['node']
                );
                if ($ib->rbac->checkAccess($_REQUEST['node'])) {
                    $Result['permitted'] = true;
                } else {
                    $Result['permitted'] = false;
                }
                echo json_encode($Result,JSON_PRETTY_PRINT);
            }
            break;
        case 'GetLog':
            if ($ib->rbac->checkAccess("ADMIN-LOGS")) {
                if (isset($_REQUEST['date'])) {
                    $Date = $_REQUEST['date'];
                } else {
                    $Date = "";
                }
                echo json_encode($ib->logging->getLog($Date), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
            }
            break;
        // case 'GetRBACGroups':
        //     if ($ib->rbac->checkAccess("ADMIN-RBAC")) {
        //         if (isset($_REQUEST['type'])) {
        //             $Type = $_REQUEST['type'];
        //         } else {
        //             $Type = null;
        //         }
        //         echo json_encode($ib->rbac->getRBACGroups($Type), JSON_PRETTY_PRINT);
        //     }
        //     break;
        // case 'GetRBACGroup':
        //     if ($ib->rbac->checkAccess("ADMIN-RBAC")) {
        //         if (isset($_REQUEST['id'])) {
        //             echo json_encode($ib->rbac->getRBACGroupByID($_REQUEST['id']), JSON_PRETTY_PRINT);
        //         }
        //     }
        //     break;
        // case 'SetRBACGroup':
        //     if ($ib->rbac->checkAccess("ADMIN-RBAC")) {
        //         if (isset($_REQUEST['id'])) {
        //             if (isset($_REQUEST['name'])) {
        //                 $GroupName = $_REQUEST['name'];
        //             } else {
        //                 $GroupName = null;
        //             }
        //             if (isset($_REQUEST['description'])) {
        //                 $Description = $_REQUEST['description'];
        //             } else {
        //                 $Description = null;
        //             }
        //             if (isset($_REQUEST['key'])) {
        //                 $Key = $_REQUEST['key'];
        //             } else {
        //                 $Key = null;
        //             }
        //             if (isset($_REQUEST['value'])) {
        //                 $Value = $_REQUEST['value'];
        //             } else {
        //                 $Value = null;
        //             }
        //             echo json_encode($ib->rbac->updateRBACGroup($_REQUEST['id'],$GroupName,$Description,$Key,$Value), JSON_PRETTY_PRINT);
        //         }
        //     }
        //     break;
        case 'NewRBACGroup':
            if ($ib->rbac->checkAccess("ADMIN-RBAC")) {
                if (isset($_REQUEST['name'])) {
                    if (isset($_REQUEST['description'])) {
                        $Description = $_REQUEST['description'];
                    } else {
                        $Description = null;
                    }
                    echo json_encode($ib->rbac->newRBACGroup($_REQUEST['name'],$Description), JSON_PRETTY_PRINT);
                }
            }
            break;
        case 'DeleteRBACGroup':
            if ($ib->rbac->checkAccess("ADMIN-RBAC")) {
                if (isset($_REQUEST['id'])) {
                    echo json_encode($ib->rbac->deleteRBACGroup($_REQUEST['id']), JSON_PRETTY_PRINT);
                } else {
                    return array(
                        'Status' => 'Error',
                        'Message' => 'Group ID Missing'
                    );
                }
            }
            break;
        case 'GetRBACRoles':
            if ($ib->rbac->checkAccess("ADMIN-RBAC")) {
                echo json_encode($ib->rbac->getRBACRoles(), JSON_PRETTY_PRINT);
            }
            break;
        case 'NewRBACRole':
            if ($ib->rbac->checkAccess("ADMIN-RBAC")) {
                if (isset($_REQUEST['name'])) {
                    if (isset($_REQUEST['description'])) {
                        $Description = $_REQUEST['description'];
                    } else {
                        $Description = null;
                    }
                    echo json_encode($ib->rbac->newRBACRole($_REQUEST['name'],$Description), JSON_PRETTY_PRINT);
                }
            }
            break;
        case 'SetRBACRole':
            if ($ib->rbac->checkAccess("ADMIN-RBAC")) {
                if (isset($_REQUEST['id'])) {
                    if (isset($_REQUEST['name'])) {
                        $RoleName = $_REQUEST['name'];
                    } else {
                        $RoleName = null;
                    }
                    if (isset($_REQUEST['description'])) {
                        $RoleDescription = $_REQUEST['description'];
                    } else {
                        $RoleDescription = null;
                    }
                    echo json_encode($ib->rbac->updateRBACRole($_REQUEST['id'],$RoleName,$RoleDescription), JSON_PRETTY_PRINT);
                }
            }
            break;
        case 'DeleteRBACRole':
            if ($ib->rbac->checkAccess("ADMIN-RBAC")) {
                if (isset($_REQUEST['id'])) {
                    echo json_encode($ib->rbac->deleteRBACRole($_REQUEST['id']), JSON_PRETTY_PRINT);
                } else {
                    return array(
                        'Status' => 'Error',
                        'Message' => 'Role ID Missing'
                    );
                }
            }
            break;
        case 'GetConfig':
            if ($ib->rbac->checkAccess("ADMIN-CONFIG")) {
                $config = $ib->config->getConfig();
                $config['Security']['salt'] = "********";
                if ($config['SAML']['sp']['privateKey'] != "") {
                    $config['SAML']['sp']['privateKey'] = "********";
                }
                $config['SAML']['idp']['x509cert'] = substr($config['SAML']['idp']['x509cert'],0,24).'...';
                echo json_encode($config,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
                $ib->logging->writeLog("Config","Queried Configuration","info",$_REQUEST);
            }
            break;
        case 'SetConfig':
            if (checkRequestMethod('POST')) {
                if ($ib->rbac->checkAccess("ADMIN-CONFIG")) {
                    $config = $ib->config->getConfig();
                    $config['Security']['salt'] = "********";
                    if ($config['SAML']['sp']['privateKey'] != "") {
                        $config['SAML']['sp']['privateKey'] = "********";
                    }
                    $config['SAML']['idp']['x509cert'] = substr($config['SAML']['idp']['x509cert'],0,24).'...';
                    if (isset($_POST['systemLogFileName'])) { $ib->config->setConfig("System","logfilename",$_POST['systemLogFileName']); }
                    if (isset($_POST['systemLogDirectory'])) { $ib->config->setConfig("System","logdirectory",$_POST['systemLogDirectory']); }
                    if (isset($_POST['systemLogLevel'])) { $ib->config->setConfig("System","loglevel",$_POST['systemLogLevel']); }
                    if (isset($_POST['systemLogRetention'])) { $ib->config->setConfig("System","logretention",$_POST['systemLogRetention']); }
                    if (isset($_POST['systemCURLTimeout'])) { $ib->config->setConfig("System","CURL-Timeout",$_POST['systemCURLTimeout']); }
                    if (isset($_POST['systemCURLTimeoutConnect'])) { $ib->config->setConfig("System","CURL-ConnectTimeout",$_POST['systemCURLTimeoutConnect']); }
                    if (isset($_POST['securitySalt'])) { $ib->config->setConfig("Security","salt",$_POST['securitySalt']); }

                    // SAML Stuff //
                    if (isset($_POST['samlEnabled'])) { $ib->config->setConfig("SAML","enabled",filter_var($_POST['samlEnabled'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)); }
                    if (isset($_POST['samlAutoCreateUsers'])) { $ib->config->setConfig("SAML","AutoCreateUsers",filter_var($_POST['samlAutoCreateUsers'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)); }
                    if (isset($_POST['samlStrict'])) { $ib->config->setConfig("SAML","strict",filter_var($_POST['samlStrict'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)); }
                    if (isset($_POST['samlDebug'])) { $ib->config->setConfig("SAML","debug",filter_var($_POST['samlDebug'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)); }

                    // SP
                    if (isset($_POST['spEntityId']) || isset($_POST['spAcsUrl']) || isset($_POST['spSloUrl']) || isset($_POST['spX509Cert']) || isset($_POST['spPrivateKey'])) {
                        $spconfig = $config['SAML']['sp'];
                        if (isset($_POST['spEntityId'])) {
                            $spconfig['entityId'] = urldecode($_POST['spEntityId']);
                        }
                        if (isset($_POST['spAcsUrl'])) {
                            $spconfig['assertionConsumerService']['url'] = urldecode($_POST['spAcsUrl']);
                        }
                        if (isset($_POST['spSloUrl'])) {
                            $spconfig['singleLogoutService']['url'] = urldecode($_POST['spSloUrl']);
                        }
                        if (isset($_POST['spX509Cert'])) {
                            $spconfig['x509cert'] = urldecode($_POST['spX509Cert']);
                        }
                        if (isset($_POST['spPrivateKey'])) {
                            $spconfig['privateKey'] = urldecode($_POST['spPrivateKey']);
                        }
                        $ib->config->setConfig("SAML","sp",$spconfig);
                    }

                    // IdP
                    if (isset($_POST['idpEntityId']) || isset($_POST['idpSsoUrl']) || isset($_POST['idpSloUrl']) || isset($_POST['idpX509Cert'])) {
                        $idpconfig = $config['SAML']['idp'];
                        if (isset($_POST['idpEntityId'])) {
                            $idpconfig['entityId'] = urldecode($_POST['idpEntityId']);
                        }
                        if (isset($_POST['idpSsoUrl'])) {
                            $idpconfig['singleSignOnService']['url'] = urldecode($_POST['idpSsoUrl']);
                        }
                        if (isset($_POST['idpSloUrl'])) {
                            $idpconfig['singleLogoutService']['url'] = urldecode($_POST['idpSloUrl']);
                        }
                        if (isset($_POST['idpX509Cert'])) {
                            $idpconfig['x509cert'] = urldecode($_POST['idpX509Cert']);
                        }
                        $ib->config->setConfig("SAML","idp",$idpconfig);
                    }

                    // User Attributes
                    if (isset($_POST['attributeUsername']) || isset($_POST['attributeFirstName']) || isset($_POST['attributeLastName']) || isset($_POST['attributeEmail']) || isset($_POST['attributeGroups'])) {
                        $attributeconfig = $config['SAML']['attributes'];
                        if (isset($_POST['attributeUsername'])) {
                            $attributeconfig['Username'] = urldecode($_POST['attributeUsername']);
                        }
                        if (isset($_POST['attributeFirstName'])) {
                            $attributeconfig['FirstName'] = urldecode($_POST['attributeFirstName']);
                        }
                        if (isset($_POST['attributeLastName'])) {
                            $attributeconfig['LastName'] = urldecode($_POST['attributeLastName']);
                        }
                        if (isset($_POST['attributeEmail'])) {
                            $attributeconfig['Email'] = urldecode($_POST['attributeEmail']);
                        }
                        if (isset($_POST['attributeGroups'])) {
                            $attributeconfig['Groups'] = urldecode($_POST['attributeGroups']);
                        }
                        $ib->config->setConfig("SAML","attributes",$attributeconfig);
                    }
                    // End of SAML stuff //

                    $newConfig = $ib->config->getConfig();
                    $newConfig['Security']['salt'] = "********";
                    if ($newConfig['SAML']['sp']['privateKey'] != "") {
                        $newConfig['SAML']['sp']['privateKey'] = "********";
                    }
                    $newConfig['SAML']['idp']['x509cert'] = substr($newConfig['SAML']['idp']['x509cert'],0,24).'...';
                    echo json_encode($newConfig,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
                    $logArr = array(
                        "Old Configuration" => $config,
                        "New Configuration" => $newConfig
                    );
                    $ib->logging->writeLog("Config","Updated configuration","warning",$logArr);
                }
            }
            break;
        case 'getChangelog':
            if (checkRequestMethod('GET')) {
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
            if ($ib->rbac->checkAccess("B1-SECURITY-ASSESSMENT")) {
                if (checkRequestMethod('POST')) {
                    if ((isset($_POST['APIKey']) OR isset($_COOKIE['crypt'])) AND isset($_POST['StartDateTime']) AND isset($_POST['EndDateTime']) AND isset($_POST['Realm']) AND isset($_POST['id']) AND isset($_POST['unnamed']) AND isset($_POST['substring'])) {
                        if (isValidUuid($_POST['id'])) {
                            $response = generateSecurityReport($_POST['StartDateTime'],$_POST['EndDateTime'],$_POST['Realm'],$_POST['id'],$_POST['unnamed'],$_POST['substring']);
                            echo json_encode($response,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
                        }
                    }
                }
            }
            break;
        case 'downloadSecurityReport':
            if ($ib->rbac->checkAccess("B1-SECURITY-ASSESSMENT")) {
                if (checkRequestMethod('GET')) {
                    $ib->logging->writeLog("Assessment","Downloaded security assessment report","info");
                    if (isset($_REQUEST['id']) AND isValidUuid($_REQUEST['id'])) {
                        $id = $_REQUEST['id'];
                        $File = __DIR__.'/../files/reports/report-'.$id.'.pptx';
                        if (file_exists($File)) {
                            $ib->reporting->updateReportEntryStatus($id,'Downloaded');
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
            if ($ib->rbac->checkAccess("B1-SECURITY-ASSESSMENT")) {
                if (checkRequestMethod('GET')) {
                    if (isset($_REQUEST['id']) AND isValidUuid($_REQUEST['id'])) {
                        $id = $_REQUEST['id'];
                        echo json_encode(getProgress($id,38)); // Produces percentage for use on progress bar
                    }
                }
            }
            break;
        case 'createLicenseReport':
            if ($ib->rbac->checkAccess("B1-LICENSE-USAGE")) {
                if (checkRequestMethod('POST')) {
                    if ((isset($_POST['APIKey']) OR isset($_COOKIE['crypt'])) AND isset($_POST['StartDateTime']) AND isset($_POST['EndDateTime']) AND isset($_POST['Realm'])) {
                        $response = getLicenseCount($_POST['StartDateTime'],$_POST['EndDateTime'],$_POST['Realm']);
                        echo json_encode($response,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
                    }
                }
            }
            break;
        case 'createLicenseReport2':
            // if ($ib->rbac->checkAccess("B1-LICENSE-USAGE")) {
                if (checkRequestMethod('POST')) {
                    if ((isset($_POST['APIKey']) OR isset($_COOKIE['crypt'])) AND isset($_POST['StartDateTime']) AND isset($_POST['EndDateTime']) AND isset($_POST['Realm'])) {
                        $response = getLicenseCount2($_POST['StartDateTime'],$_POST['EndDateTime'],$_POST['Realm']);
                        echo json_encode($response,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
                    }
                }
            // }
            break;
        case 'crypt':
            if (checkRequestMethod('POST')) {
                if (isset($_POST['key'])) {
                    echo json_encode(array(encrypt($_POST['key'],$ib->config->getConfig("Security","salt"))));
                }
            }
            break;
        case 'getSecurityAssessmentTemplates':
            if ($ib->rbac->checkAccess("ADMIN-SECASS")) {
                if (checkRequestMethod('GET')) {
                    echo json_encode($ib->templates->getTemplateConfigs(),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
                }
            }
            break;
        case 'newSecurityAssessmentTemplate':
            if ($ib->rbac->checkAccess("ADMIN-SECASS")) {
                if (checkRequestMethod('POST')) {
                    if (isset($_POST['TemplateName'])) {
                        $TemplateName = $_POST['TemplateName'];
                        if (isset($_POST['Status'])) { $Status = $_POST['Status']; } else { $Status = null; }
                        if (isset($_POST['FileName'])) { $FileName = $_POST['FileName'] . '.pptx'; } else { $FileName = null; }
                        if (isset($_POST['Description'])) { $Description = $_POST['Description']; } else { $Description = null; }
                        if (isset($_POST['ThreatActorSlide'])) { $ThreatActorSlide = $_POST['ThreatActorSlide']; } else { $ThreatActorSlide = null; }
                        echo json_encode($ib->templates->newTemplateConfig($Status,$FileName,$TemplateName,$Description,$ThreatActorSlide),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
                    }
                }
            }
            break;
        case 'setSecurityAssessmentTemplate':
            if ($ib->rbac->checkAccess("ADMIN-SECASS")) {
                if (checkRequestMethod('POST')) {
                    if (isset($_POST['id'])) {
                        $ID = $_POST['id'];
                        if (isset($_POST['TemplateName'])) { $TemplateName = $_POST['TemplateName']; } else { $TemplateName = null; }
                        if (isset($_POST['Status'])) { $Status = $_POST['Status']; } else { $Status = null; }
                        if (isset($_POST['FileName'])) { $FileName = $_POST['FileName'] . '.pptx'; } else { $FileName = null; }
                        if (isset($_POST['Description'])) { $Description = $_POST['Description']; } else { $Description = null; }
                        if (isset($_POST['ThreatActorSlide'])) { $ThreatActorSlide = $_POST['ThreatActorSlide']; } else { $ThreatActorSlide = null; }
                        echo json_encode($ib->templates->setTemplateConfig($ID,$Status,$FileName,$TemplateName,$Description,$ThreatActorSlide),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
                    }
                }
            }
            break;
        case 'removeSecurityAssessmentTemplate':
            if ($ib->rbac->checkAccess("ADMIN-SECASS")) {
                if (checkRequestMethod('POST')) {
                    if (isset($_POST['id'])) {
                        echo json_encode($ib->templates->removeTemplateConfig($_POST['id']),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
                    }
                }
            }
            break;
        case 'uploadSecurityAssessmentTemplate':
            if ($ib->rbac->checkAccess("ADMIN-SECASS")) {
                if (checkRequestMethod('POST')) {
                    $uploadDir = __DIR__.'/../files/templates/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    if (isset($_FILES['pptx']) && $_FILES['pptx']['error'] == UPLOAD_ERR_OK) {
                        if (isset($_POST['TemplateName'])) {
                            if (isValidFileType($_FILES['pptx']['name'],['pptx'])) {
                                $pptxFileName = basename($_FILES['pptx']['name']);
                                $pptxFilePath = $uploadDir . urldecode($_POST['TemplateName']) . '.pptx';

                                // Move the uploaded file to the designated directory
                                if (move_uploaded_file($_FILES['pptx']['tmp_name'], $pptxFilePath)) {
                                    echo json_encode(array(
                                        'Status' => 'Success',
                                        'Message' => "Successfully uploaded PPTX file: $pptxFileName"
                                    ));
                                } else {
                                    echo json_encode(array(
                                        'Status' => 'Error',
                                        'Message' => "Error uploading PPTX file."
                                    ));
                                }
                            } else {
                                echo json_encode(array(
                                    'Status' => 'Error',
                                    'Message' => "Invalid PPTX file: $pptxFileName"
                                ));
                            }
                        } else {
                            echo json_encode(array(
                                'Status' => 'Error',
                                'Message' => "Template Name is missing."
                            ));
                        }
                    } else {
                        echo json_encode(array(
                            'Status' => 'Error',
                            'Message' => "Error uploading PPTX file."
                        ));
                    }
                }
            }
            break;
        case 'getThreatActorConfig':
            if ($ib->rbac->checkAccess("ADMIN-SECASS")) {
                if (checkRequestMethod('GET')) {
                    echo json_encode($ib->threatactors->getThreatActorConfigs(),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
                }
            }
            break;
        case 'newThreatActorConfig':
            if ($ib->rbac->checkAccess("ADMIN-SECASS")) {
                if (checkRequestMethod('POST')) {
                    if (isset($_POST['name'])) {
                        if (isset($_POST['SVG'])) { $SVG = $_POST['SVG'] . '.svg'; } else { $SVG = null; }
                        if (isset($_POST['PNG'])) { $PNG = $_POST['PNG'] . '.png'; } else { $PNG = null; }
                        if (isset($_POST['URLStub'])) { $URLStub = $_POST['URLStub']; } else { $URLStub = null; }
                        echo json_encode($ib->threatactors->newThreatActorConfig($_POST['name'],$SVG,$PNG,$URLStub),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
                    }
                }
            }
            break;
        case 'setThreatActorConfig':
            if ($ib->rbac->checkAccess("ADMIN-SECASS")) {
                if (checkRequestMethod('POST')) {
                    if (isset($_POST['id'])) {
                        if (isset($_POST['name'])) { $Name = $_POST['name']; } else { $Name = null; }
                        if (isset($_POST['SVG'])) { $SVG = $_POST['SVG'] . '.svg'; } else { $SVG = null; }
                        if (isset($_POST['PNG'])) { $PNG = $_POST['PNG'] . '.png'; } else { $PNG = null; }
                        if (isset($_POST['URLStub'])) { $URLStub = $_POST['URLStub']; } else { $URLStub = null; }
                        echo json_encode($ib->threatactors->setThreatActorConfig($_POST['id'],$Name,$SVG,$PNG,$URLStub),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
                    }
                }
            }
            break;
        case 'removeThreatActorConfig':
            if ($ib->rbac->checkAccess("ADMIN-SECASS")) {
                if (checkRequestMethod('POST')) {
                    if (isset($_POST['id'])) {
                        echo json_encode($ib->threatactors->removeThreatActorConfig($_POST['id']),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
                    }
                }
            }
            break;
        case 'getThreatActors':
            if ($ib->rbac->checkAccess("B1-THREAT-ACTORS")) {
                if (checkRequestMethod('POST')) {
                    if ((isset($_POST['APIKey']) OR isset($_COOKIE['crypt'])) AND isset($_POST['StartDateTime']) AND isset($_POST['EndDateTime']) AND isset($_POST['Realm'])) {
                        $UserInfo = GetCSPCurrentUser();
                        if (isset($UserInfo->result->name)) {
                            $ib->logging->writeLog("ThreatActors",$UserInfo->result->name." queried list of Threat Actors","info");
                            $Actors = GetB1ThreatActors($_POST['StartDateTime'],$_POST['EndDateTime']);
                            if (!isset($Actors->Error)) {
                                echo json_encode(GetB1ThreatActorsById3($Actors,$_POST['unnamed'],$_POST['substring']),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
                            } else {
                                echo json_encode($Actors);
                            };
                        } else {
                            echo json_encode(array(
                                'Status' => 'Error',
                                'Message' => 'Invalid API Key'
                            ));
                        }
                    }
                }
            }
            break;
        case 'getThreatActor':
            if ($ib->rbac->checkAccess("B1-THREAT-ACTORS")) {
                if (checkRequestMethod('POST')) {
                    if ((isset($_POST['APIKey']) OR isset($_COOKIE['crypt'])) AND isset($_POST['Realm']) AND isset($_POST['ActorID']) AND isset($_POST['Page'])) {
                        $UserInfo = GetCSPCurrentUser();
                        if (isset($UserInfo)) {
                            $ib->logging->writeLog("ThreatActors",$UserInfo->result->name." queried list of Threat Actor IOCs","info");
                        }
                        echo json_encode(GetB1ThreatActor($_POST['ActorID'],$_POST['Page']),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
                    }
                }
            }
            break;
        case 'uploadThreatActorImage':
            if ($ib->rbac->checkAccess("ADMIN-SECASS")) {
                if (checkRequestMethod('POST')) {
                    $uploadDir = __DIR__.'/../assets/images/Threat Actors/Uploads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    if (isset($_FILES['svgImage']) && $_FILES['svgImage']['error'] == UPLOAD_ERR_OK) {
                        if (isset($_POST['svgFileName'])) {
                            if (isValidFileType($_FILES['svgImage']['name'],['svg'])) {
                                $svgFileName = basename($_FILES['svgImage']['name']);
                                $svgFilePath = $uploadDir . urldecode($_POST['svgFileName']) . '.svg';

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
                        } else {
                            echo json_encode(array(
                                'Status' => 'Error',
                                'Message' => "SVG File Name Missing"
                            ));
                            break;
                        }
                    }

                    // Handle PNG image upload
                    if (isset($_FILES['pngImage']) && $_FILES['pngImage']['error'] == UPLOAD_ERR_OK) {
                        if (isset($_POST['pngFileName'])) {
                            $pngFileName = basename($_FILES['pngImage']['name']);
                            $pngFilePath = $uploadDir . urldecode($_POST['svgFileName']) . '.png';
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
                        } else {
                            echo json_encode(array(
                                'Status' => 'Error',
                                'Message' => "PNG File Name Missing"
                            ));
                            break;
                        }
                    }
                }
            }
            break;
        case 'DNSToolbox':
            if ($ib->rbac->checkAccess("DNS-TOOLBOX")) {
                if (checkRequestMethod('GET')) {
                    if (isset($_GET['request']) && isset($_GET['domain'])) {

                        $domain = $_GET['domain'];
                        if ($domain == '.') {
                            echo json_encode(array(
                                'Status' => 'Error',
                                'Message' => 'Domain Name or IP missing from request'
                            ));
                            break;
                        }
                        if ($_GET['request'] != 'port') {
                            if (!isset($_GET['source']) || $_GET['source'] == 'null') {
                                echo json_encode(array(
                                    'Status' => 'Error',
                                    'Message' => 'DNS Server missing from request'
                                ));
                                break;
                            } else {
                                $source = $_GET['source'];
                                switch ($source) {
                                    case 'google':
                                        $sourceserver = 'dns.google';
                                        break;
                                    case 'cloudflare':
                                        $sourceserver = 'one.one.one.one';
                                        break;
                                }
                            }
                        } else {
                            if ($_GET['port'] == "") {
                                $port = [];
                            } else {
                                $port = explode(',',$_GET['port']);
                            }
                        }

                        $DNSToolbox = new DNSToolbox();

                        $ib->logging->writeLog("DNSToolbox","A query was performed using type: ".$_GET['request'],"debug",$_GET);
                        switch ($_GET['request']) {
                            case 'a':
                                echo json_encode($DNSToolbox->a($domain,$sourceserver));
                                break;
                            case 'aaaa':
                                echo json_encode($DNSToolbox->aaaa($domain,$sourceserver));
                                break;
                            case 'cname':
                                echo json_encode($DNSToolbox->cname($domain,$sourceserver));
                                break;
                            case 'all':
                                echo json_encode($DNSToolbox->all($domain,$sourceserver));
                                break;
                            case 'mx':
                                echo json_encode($DNSToolbox->mx($domain,$sourceserver));
                                break;
                            case 'port':
                                echo json_encode($DNSToolbox->port($domain,$port));
                                break;
                            case 'txt':
                                echo json_encode($DNSToolbox->txt($domain,$sourceserver));
                                break;
                            case 'dmarc':
                                echo json_encode($DNSToolbox->dmarc($domain,$sourceserver));
                                break;
                            case 'nameserver':
                                echo json_encode($DNSToolbox->ns($domain,$sourceserver));
                                break;
                            case 'soa':
                                echo json_encode($DNSToolbox->soa($domain,$sourceserver));
                                break;
                            case 'reverse':
                                echo json_encode($DNSToolbox->reverse($domain,$sourceserver));
                                break;
                            default:
                                echo json_encode(array(
                                    'Status' => 'Error',
                                    'Message' => 'Invalid Request Type'
                                ));
                                break;
                        }
                    }
                }
            break;
            }
        case 'getAssessmentReports':
            if ($ib->rbac->checkAccess("REPORT-ASSESSMENTS")) {
                if (checkRequestMethod('GET')) {
                    if (isset($_REQUEST['granularity']) && isset($_REQUEST['filters'])) {
                        $Filters = $_REQUEST['filters'];
                        if (isset($_REQUEST['start'])) { $Start = $_REQUEST['start']; } else { $Start = null; }
                        if (isset($_REQUEST['end'])) { $End = $_REQUEST['end']; } else { $End = null; }
                        $ib->logging->writeLog("Reporting","Queried Assessment Reports","info");
                        echo json_encode($ib->reporting->getAssessmentReports($_REQUEST['granularity'],json_decode($Filters,true),$Start,$End),JSON_PRETTY_PRINT);
                    }
                }
            }
            break;
        case 'getAssessmentReportsStats':
            if ($ib->rbac->checkAccess("REPORT-ASSESSMENTS")) {
                if (checkRequestMethod('GET')) {
                    if (isset($_REQUEST['granularity']) && isset($_REQUEST['filters'])) {
                        $Filters = $_REQUEST['filters'];
                        if (isset($_REQUEST['start'])) { $Start = $_REQUEST['start']; } else { $Start = null; }
                        if (isset($_REQUEST['end'])) { $End = $_REQUEST['end']; } else { $End = null; }
                        $ib->logging->writeLog("Reporting","Queried Assessment Report Stats","debug");
                        echo json_encode($ib->reporting->getAssessmentReportsStats($_REQUEST['granularity'],json_decode($Filters,true),$Start,$End),JSON_PRETTY_PRINT);
                    }
                }
            }
            break;
        case 'getAssessmentReportsSummary':
            if ($ib->rbac->checkAccess("REPORT-ASSESSMENTS")) {
                if (checkRequestMethod('GET')) {
                    $ib->logging->writeLog("Reporting","Queried Assessment Report Summary","debug");
                    echo json_encode($ib->reporting->getAssessmentReportsSummary(),JSON_PRETTY_PRINT);
                }
            }
            break;
        case 'getTrackingRecords':
            if ($ib->rbac->checkAccess("REPORT-TRACKING")) {
                if (checkRequestMethod('GET')) {
                    if (isset($_REQUEST['granularity']) && isset($_REQUEST['filters'])) {
                        $Filters = $_REQUEST['filters'];
                        if (isset($_REQUEST['start'])) { $Start = $_REQUEST['start']; } else { $Start = null; }
                        if (isset($_REQUEST['end'])) { $End = $_REQUEST['end']; } else { $End = null; }
                        $ib->logging->writeLog("Reporting","Queried Web Tracking","info");
                        echo json_encode($ib->reporting->getTrackingRecords($_REQUEST['granularity'],json_decode($Filters,true),$Start,$End),JSON_PRETTY_PRINT);
                    }
                }
            }
            break;
        case 'getTrackingStats':
            if ($ib->rbac->checkAccess("REPORT-TRACKING")) {
                if (checkRequestMethod('GET')) {
                    if (isset($_REQUEST['granularity']) && isset($_REQUEST['filters'])) {
                        $Filters = $_REQUEST['filters'];
                        if (isset($_REQUEST['start'])) { $Start = $_REQUEST['start']; } else { $Start = null; }
                        if (isset($_REQUEST['end'])) { $End = $_REQUEST['end']; } else { $End = null; }
                        $ib->logging->writeLog("Reporting","Queried Web Tracking Summary","debug");
                        echo json_encode($ib->reporting->getTrackingStats($_REQUEST['granularity'],json_decode($Filters,true),$Start,$End),JSON_PRETTY_PRINT);
                    }
                }
            }
            break;
        case 'getTrackingSummary':
            if ($ib->rbac->checkAccess("REPORT-TRACKING")) {
                if (checkRequestMethod('GET')) {
                    $ib->logging->writeLog("Reporting","Queried Web Tracking Summary","debug");
                    echo json_encode($ib->reporting->getTrackingSummary(),JSON_PRETTY_PRINT);
                }
            }
            break;
    }
}