<?php
$app->get('/notifications/news', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-CONFIG")) {
        $data = $request->getQueryParams();
        $phpef->api->setAPIResponseData($phpef->notifications->getNews());
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->patch('/notifications/news/{id}', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    $data = $phpef->api->getAPIRequestData($request);
    if ($phpef->auth->checkAccess("ADMIN-CONFIG")) {
        if (isset($args['id'])) {
            $phpef->notifications->updateNews($args['id'],$data);
        } else {
            $phpef->api->setAPIResponse('Error','id missing from request',400);
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->post('/notifications/news', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    $data = $phpef->api->getAPIRequestData($request);
    if ($phpef->auth->checkAccess("ADMIN-CONFIG")) {
        $phpef->notifications->newNews($data);
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->delete('/notifications/news/{id}', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-CONFIG")) {
        $phpef->notifications->removeNews($args['id']);
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});