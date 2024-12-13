<?php
$app->post('/login', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    $ib->auth->login($ib->api->getApiData($request));
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});