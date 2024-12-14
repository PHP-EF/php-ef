<?php
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