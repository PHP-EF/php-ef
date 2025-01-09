<?php
$app->get('/reports/tracking/records', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    $data = $request->getQueryParams();
    if ($phpef->auth->checkAccess("REPORT-TRACKING")) {
        if (isset($data['granularity']) && isset($data['filters'])) {
            $Filters = $data['filters'];
            $Start = $data['start'] ?? null;
            $End = $data['end'] ?? null;
            $phpef->logging->writeLog("Reporting","Queried Web Tracking","info");
            $phpef->api->setAPIResponseData($phpef->reporting->getTrackingRecords($data['granularity'],json_decode($Filters,true),$Start,$End));
        } else {
            $phpef->api->setAPIResponse('Error','Required values are missing from the request');
        }
    }

	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/reports/tracking/stats', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    $data = $request->getQueryParams();
    if ($phpef->auth->checkAccess("REPORT-TRACKING")) {
        if (isset($data['granularity']) && isset($data['filters'])) {
            $Filters = $data['filters'];
            if (isset($data['start'])) { $Start = $data['start']; } else { $Start = null; }
            if (isset($data['end'])) { $End = $data['end']; } else { $End = null; }
            $phpef->logging->writeLog("Reporting","Queried Web Tracking Stats","debug");
            $phpef->api->setAPIResponseData($phpef->reporting->getTrackingStats($data['granularity'],json_decode($Filters,true),$Start,$End));
        } else {
            $phpef->api->setAPIResponse('Error','Required values are missing from the request');
        }
    }

	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/reports/tracking/summary', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    $data = $request->getQueryParams();
    if ($phpef->auth->checkAccess("REPORT-TRACKING")) {
        $phpef->logging->writeLog("Reporting","Queried Web Tracking Summary","debug");
        $phpef->api->setAPIResponseData($phpef->reporting->getTrackingSummary());
    }

	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});