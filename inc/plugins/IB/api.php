<?php
// Generate Security Assessment
$app->post('/plugin/ib/assessment/security/generate', function ($request, $response, $args) {
	$ibPlugin = new SecurityAssessment();
    if ($ibPlugin->rbac->checkAccess("B1-SECURITY-ASSESSMENT")) {
        $data = $ibPlugin->api->getAPIRequestData($request);
        if ($ibPlugin->SetCSPConfiguration($data['APIKey'] ?? null,$data['Realm'] ?? null)) {
            if ((isset($data['APIKey']) OR isset($_COOKIE['crypt'])) AND isset($data['StartDateTime']) AND isset($data['EndDateTime']) AND isset($data['Realm']) AND isset($data['id']) AND isset($data['unnamed']) AND isset($data['substring'])) {
                if (isValidUuid($data['id'])) {
                    $ibPlugin->generateSecurityReport($data['StartDateTime'],$data['EndDateTime'],$data['Realm'],$data['id'],$data['unnamed'],$data['substring']);
                }
            }
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

// Get Security Assessment Progress
$app->get('/plugin/ib/assessment/security/progress', function ($request, $response, $args) {
	$ibPlugin = new SecurityAssessment();
    if ($ibPlugin->rbac->checkAccess("B1-SECURITY-ASSESSMENT")) {
        $data = $request->getQueryParams();
        if (isset($data['id']) AND isValidUuid($data['id'])) {
            $ibPlugin->api->setAPIResponseData($ibPlugin->getProgress($data['id'],38)); // Produces percentage for use on progress bar
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

// Download Security Assessment Report
$app->get('/plugin/ib/assessment/security/download', function ($request, $response, $args) {
	$ibPlugin = new SecurityAssessment();
    if ($ibPlugin->rbac->checkAccess("B1-SECURITY-ASSESSMENT")) {
        $data = $request->getQueryParams();
        if (isset($data['id']) AND isValidUuid($data['id'])) {
            $ibPlugin->logging->writeLog("Assessment","Downloaded security assessment report","info");
            $File = $ibPlugin->getDir()['Files'].'/reports/report-'.$data['id'].'.pptx';
            // Ensure the file exists and is readable
            if (file_exists($File) && is_readable($File)) {
                // Read the file content
                $fileContent = file_get_contents($File);
                // Write the file content to the response body
                $response->getBody()->write($fileContent);
                // Return the response with appropriate headers
                return $response
                    ->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.presentationml.presentation')
                    ->withHeader('Content-Disposition', 'attachment; filename="report-' . $data['id'] . '.pptx"')
                    ->withHeader('Content-Transfer-Encoding', 'binary')
                    ->withHeader('Accept-Ranges', 'bytes')
                    ->withStatus($GLOBALS['responseCode']);
            } else {
                // Handle the error if the file does not exist or is not readable
                $ibPlugin->api->setAPIResponse('Error','Invalid ID or Link Expired');
            }
        } else {
            $ibPlugin->api->setAPIResponse('Error','Invalid ID');
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

// Get Security Assessment Templates
$app->get('/plugin/ib/assessment/security/config', function ($request, $response, $args) {
	$ibPlugin = new TemplateConfig();
    if ($ibPlugin->rbac->checkAccess("ADMIN-SECASS")) {
        $data = $ibPlugin->api->getAPIRequestData($request);
        $ibPlugin->api->setAPIResponseData($ibPlugin->getTemplateConfigs());
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

// New Security Assessment Template
$app->post('/plugin/ib/assessment/security/config', function ($request, $response, $args) {
	$ibPlugin = new TemplateConfig();
    if ($ibPlugin->rbac->checkAccess("ADMIN-SECASS")) {
        $data = $ibPlugin->api->getAPIRequestData($request);
        if (isset($data['TemplateName'])) {
            $Status = $data['Status'] ?? null;
            $FileName = $data['TemplateName'] ? $data['TemplateName'] . '.pptx' : null;
            $Description = $data['Description'] ?? null;
            $ThreatActorSlide = $data['ThreatActorSlide'] ?? null;
            $TemplateName = $data['TemplateName'];
            $ibPlugin->newTemplateConfig($Status,$FileName,$TemplateName,$Description,$ThreatActorSlide);
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

// Update Security Assessment Template
$app->patch('/plugin/ib/assessment/security/config/{id}', function ($request, $response, $args) {
	$ibPlugin = new TemplateConfig();
    if ($ibPlugin->rbac->checkAccess("ADMIN-SECASS")) {
        $data = $ibPlugin->api->getAPIRequestData($request);
        $Status = $data['Status'] ?? null;
        $FileName = $data['TemplateName'] ? $data['TemplateName'] . '.pptx' : null;
        $TemplateName = $data['TemplateName'] ?? null;
        $Description = $data['Description'] ?? null;
        $ThreatActorSlide = $data['ThreatActorSlide'] ?? null;
        $ibPlugin->setTemplateConfig($args['id'],$Status,$FileName,$TemplateName,$Description,$ThreatActorSlide);
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

// Delete Security Assessment Template
$app->delete('/plugin/ib/assessment/security/config/{id}', function ($request, $response, $args) {
	$ibPlugin = new TemplateConfig();
    if ($ibPlugin->rbac->checkAccess("ADMIN-SECASS")) {
        $ibPlugin->removeTemplateConfig($args['id']);
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

// Upload New Security Assessment Template
$app->post('/plugin/ib/assessment/security/config/upload', function ($request, $response, $args) {
    $ibPlugin = new ThreatActors();
    if ($ibPlugin->rbac->checkAccess("ADMIN-SECASS")) {
        $uploadedFiles = $request->getUploadedFiles();
        $postData = $request->getParsedBody();
        $uploadDir = $ibPlugin->getDir()['Files'].'/templates/'; // Define your upload directory

        // Handle PPTX image upload
        if (isset($uploadedFiles['pptx']) && $uploadedFiles['pptx']->getError() == UPLOAD_ERR_OK) {
            if (isset($postData['TemplateName'])) {
                $pptxFileName = basename($uploadedFiles['pptx']->getClientFilename());
                $pptxFilePath = $uploadDir . urldecode($postData['TemplateName']) . '.pptx';

                if (isValidFileType($pptxFileName, ['pptx'])) {
                    // Move the uploaded file to the designated directory
                    $uploadedFiles['pptx']->moveTo($pptxFilePath);
                    $ibPlugin->api->setAPIResponseMessage("Successfully uploaded PPTX file: $pptxFileName");
                } else {
                    $ibPlugin->api->setAPIResponse("Errors","Invalid PPTX File: $pptxFileName");
                }
            } else {
                $ibPlugin->api->setAPIResponse("Errors","PPTX File Name Missing");
            }
        }
    }

	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

// Get Assessment Tracking Records
$app->get('/plugin/ib/assessment/reports/records', function ($request, $response, $args) {
	$ibPlugin = new AssessmentReporting();
    $data = $request->getQueryParams();
    if ($ibPlugin->rbac->checkAccess("REPORT-ASSESSMENTS")) {
        if (isset($data['granularity']) && isset($data['filters'])) {
            $Filters = $data['filters'];
            $Start = $data['start'] ?? null;
            $End = $data['end'] ?? null;
            $ibPlugin->logging->writeLog("Reporting","Queried Assessment Reports","info");
            $ibPlugin->api->setAPIResponseData($ibPlugin->getAssessmentReports($data['granularity'],json_decode($Filters,true),$Start,$End));
        } else {
            $ibPlugin->api->setAPIResponse('Error','Required values are missing from the request');
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

// Get Assessment Tracking Stats
$app->get('/plugin/ib/assessment/reports/stats', function ($request, $response, $args) {
	$ibPlugin = new AssessmentReporting();
    $data = $request->getQueryParams();
    if ($ibPlugin->rbac->checkAccess("REPORT-ASSESSMENTS")) {
        if (isset($data['granularity']) && isset($data['filters'])) {
            $Filters = $data['filters'];
            $Start = $data['start'] ?? null;
            $End = $data['end'] ?? null;
            $ibPlugin->logging->writeLog("Reporting","Queried Assessment Reports","info");
            $ibPlugin->api->setAPIResponseData($ibPlugin->getAssessmentReportsStats($_REQUEST['granularity'],json_decode($Filters,true),$Start,$End));
        } else {
            $ibPlugin->api->setAPIResponse('Error','Required values are missing from the request');
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

// Get Assessment Tracking Summary
$app->get('/plugin/ib/assessment/reports/summary', function ($request, $response, $args) {
	$ibPlugin = new AssessmentReporting();
    $data = $request->getQueryParams();
    if ($ibPlugin->rbac->checkAccess("REPORT-ASSESSMENTS")) {
        $ibPlugin->logging->writeLog("Reporting","Queried Assessment Reports","info");
        $ibPlugin->api->setAPIResponseData($ibPlugin->getAssessmentReportsSummary());
    }

	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

// Get Threat Actor List (IB Portal)
$app->post('/plugin/ib/threatactors', function ($request, $response, $args) {
	$ibPlugin = new ThreatActors();
    if ($ibPlugin->rbac->checkAccess("B1-THREAT-ACTORS")) {
        $data = $ibPlugin->api->getAPIRequestData($request);
        if ($ibPlugin->SetCSPConfiguration($data['APIKey'] ?? null,$data['Realm'] ?? null)) {
            $ibPlugin->getThreatActors($data);
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

// Get Threat Actor By ID (IB Portal)
$app->post('/plugin/ib/threatactor/{ActorID}', function ($request, $response, $args) {
	$ibPlugin = new ThreatActors();
    if ($ibPlugin->rbac->checkAccess("B1-THREAT-ACTORS")) {
        $data = $ibPlugin->api->getAPIRequestData($request);
        if ($ibPlugin->SetCSPConfiguration($data['APIKey'] ?? null,$data['Realm'] ?? null)) {
            $ibPlugin->GetB1ThreatActor($args['ActorID'],$data['Page'] ?? null);
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

// Get Configured Threat Actors
$app->get('/plugin/ib/threatactors/config', function ($request, $response, $args) {
	$ibPlugin = new ThreatActors();
    if ($ibPlugin->rbac->checkAccess("ADMIN-SECASS")) {
        $data = $ibPlugin->api->getAPIRequestData($request);
        $ibPlugin->api->setAPIResponseData($ibPlugin->getThreatActorConfigs());
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

// New Configured Threat Actor
$app->post('/plugin/ib/threatactors/config', function ($request, $response, $args) {
	$ibPlugin = new ThreatActors();
    if ($ibPlugin->rbac->checkAccess("ADMIN-SECASS")) {
        $data = $ibPlugin->api->getAPIRequestData($request);
        if (isset($data['name'])) {
            $SVG = $data['SVG'] ? $data['SVG'] . '.svg' : null;
            $PNG = $data['PNG'] ? $data['PNG'] . '.png' : null;
            $URLStub = $data['URLStub'] ?? null;
            $ibPlugin->newThreatActorConfig($data['name'],$SVG,$PNG,$URLStub);
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

// Update Configured Threat Actor
$app->patch('/plugin/ib/threatactors/config/{id}', function ($request, $response, $args) {
	$ibPlugin = new ThreatActors();
    if ($ibPlugin->rbac->checkAccess("ADMIN-SECASS")) {
        $data = $ibPlugin->api->getAPIRequestData($request);
        $Name = $data['name'] ?? null;
        $SVG = $data['SVG'] ? $data['SVG'] . '.svg' : null;
        $PNG = $data['PNG'] ? $data['PNG'] . '.png' : null;
        $URLStub = $data['URLStub'] ?? null;
        $ibPlugin->setThreatActorConfig($args['id'],$Name,$SVG,$PNG,$URLStub);
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

// Delete Configured Threat Actor
$app->delete('/plugin/ib/threatactors/config/{id}', function ($request, $response, $args) {
	$ibPlugin = new ThreatActors();
    if ($ibPlugin->rbac->checkAccess("ADMIN-SECASS")) {
        $ibPlugin->removeThreatActorConfig($args['id']);
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

// Upload Image(s) For Configured Threat Actor
$app->post('/plugin/ib/threatactors/config/upload', function ($request, $response, $args) {
    $ibPlugin = new ThreatActors();
    if ($ibPlugin->rbac->checkAccess("ADMIN-SECASS")) {
        $uploadedFiles = $request->getUploadedFiles();
        $postData = $request->getParsedBody();
        $uploadDir = $ibPlugin->getDir()['Assets'].'/images/Threat Actors/Uploads/'; // Define your upload directory

        $ResponseData = [
            'Items' => [],
            'Errors' => []
        ];

        // Handle SVG image upload
        if (isset($uploadedFiles['svgImage']) && $uploadedFiles['svgImage']->getError() == UPLOAD_ERR_OK) {
            if (isset($postData['svgFileName'])) {
                $svgFileName = basename($uploadedFiles['svgImage']->getClientFilename());
                $svgFilePath = $uploadDir . urldecode($postData['svgFileName']) . '.svg';

                if (isValidFileType($svgFileName, ['svg'])) {
                    // Move the uploaded file to the designated directory
                    $uploadedFiles['svgImage']->moveTo($svgFilePath);
                    $ResponseData['Items'][] = "SVG image uploaded successfully: $svgFileName";
                } else {
                    $ResponseData['Errors'][] = "Invalid SVG File: $svgFileName";
                }
            } else {
                $ResponseData['Errors'][] = "SVG File Name Missing";
            }
        }

        // Handle PNG image upload
        if (isset($uploadedFiles['pngImage']) && $uploadedFiles['pngImage']->getError() == UPLOAD_ERR_OK) {
            if (isset($postData['pngFileName'])) {
                $pngFileName = basename($uploadedFiles['pngImage']->getClientFilename());
                $pngFilePath = $uploadDir . urldecode($postData['pngFileName']) . '.png';

                if (isValidFileType($pngFileName, ['png'])) {
                    // Move the uploaded file to the designated directory
                    $uploadedFiles['pngImage']->moveTo($pngFilePath);
                    $ResponseData['Items'][] = "PNG image uploaded successfully: $pngFileName";
                } else {
                    $ResponseData['Errors'][] = "Invalid PNG File: $pngFileName";
                }
            } else {
                $ResponseData['Errors'][] = "PNG File Name Missing";
            }
        }
        $ibPlugin->api->setAPIResponseData($ResponseData);
    }

	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

// Generate License Assessment
$app->post('/plugin/ib/assessment/license/generate', function ($request, $response, $args) {
	$ibPlugin = new LicenseAssessment();
    if ($ibPlugin->rbac->checkAccess("B1-LICENSE-USAGE")) {
        $data = $ibPlugin->api->getAPIRequestData($request);
        if ($ibPlugin->SetCSPConfiguration($data['APIKey'] ?? null,$data['Realm'] ?? null)) {
            if ((isset($data['APIKey']) OR isset($_COOKIE['crypt'])) AND isset($data['StartDateTime']) AND isset($data['EndDateTime']) AND isset($data['Realm'])) {
                $ibPlugin->getLicenseCount($data['StartDateTime'],$data['EndDateTime'],$data['Realm']);
            }
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});