<?php
$app->get('/reports/tracking/records', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    $data = $request->getQueryParams();
    if ($ib->rbac->checkAccess("REPORT-TRACKING")) {
        if (isset($data['granularity']) && isset($data['filters'])) {
            $Filters = $data['filters'];
            $Start = $data['start'] ?? null;
            $End = $data['end'] ?? null;
            $ib->logging->writeLog("Reporting","Queried Web Tracking","info");
            $ib->api->setAPIResponseData($ib->reporting->getTrackingRecords($data['granularity'],json_decode($Filters,true),$Start,$End));
        } else {
            $ib->api->setAPIResponse('Error','Required values are missing from the request');
        }
    }

	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/reports/tracking/stats', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    $data = $request->getQueryParams();
    if ($ib->rbac->checkAccess("REPORT-TRACKING")) {
        if (isset($data['granularity']) && isset($data['filters'])) {
            $Filters = $data['filters'];
            if (isset($data['start'])) { $Start = $data['start']; } else { $Start = null; }
            if (isset($data['end'])) { $End = $data['end']; } else { $End = null; }
            $ib->logging->writeLog("Reporting","Queried Web Tracking Summary","debug");
            $ib->api->setAPIResponseData($ib->reporting->getTrackingStats($data['granularity'],json_decode($Filters,true),$Start,$End));
        } else {
            $ib->api->setAPIResponse('Error','Required values are missing from the request');
        }
    }

	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/reports/tracking/summary', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    $data = $request->getQueryParams();
    if ($ib->rbac->checkAccess("REPORT-TRACKING")) {
        $ib->logging->writeLog("Reporting","Queried Web Tracking Summary","debug");
        $ib->api->setAPIResponseData($ib->reporting->getTrackingSummary());
    }

	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});