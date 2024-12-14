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
    }
}