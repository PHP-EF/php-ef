<?php

// ** LEGACY API ** //

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
        // Security Assessments (Translate into Plugin(s))
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

        // License Usage Reports (Translate Into Plugin(s))
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
    }
}