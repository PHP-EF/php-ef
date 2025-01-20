<?php
$app->get('/settings/{setting}', function ($request, $response, $args) {
    $phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-CONFIG")) {
        $method = 'settings' . ucfirst($args['setting']);
        if (method_exists($phpef, $method)) {
            $phpef->api->setAPIResponseData($phpef->$method());
        } else {
            $phpef->api->setAPIResponse('Error','Invalid setting',404);
        }
    }
    $response->getBody()->write(jsonE($GLOBALS['api']));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus($GLOBALS['responseCode']);
});

$app->get('/settings/widgets/{widget}', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
	if ($phpef->auth->checkAccess("ADMIN-CONFIG")) {
		$phpef->api->setAPIResponseData($phpef->dashboard->getWidgetSettings($args["widget"]));
	}
    $response->getBody()->write(jsonE($GLOBALS['api']));
	// Return the response
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});