<?php
$app->get('/logs', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    if ($ib->rbac->checkAccess("ADMIN-LOGS")) {
        $data = $request->getQueryParams();
        $Date = $data['date'] ?? null;
        $ib->api->setAPIResponseData($ib->logging->getLog($Date));
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});