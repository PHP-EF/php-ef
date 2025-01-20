<?php
$app->get('/logs', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-LOGS")) {
        $data = $request->getQueryParams();
        $Date = $data['date'] ?? null;
        $phpef->api->setAPIResponseData($phpef->logging->getLog($Date));
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});