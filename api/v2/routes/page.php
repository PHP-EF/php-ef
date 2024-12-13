<?php
$app->get('/page/{category}/{page}', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();

	$ib->api->setAPIResponseData(include_once(__DIR__."/../../../pages/".$args['category']."/".$args['page'].".php"));

	// Return the response
	$response->getBody()->write($GLOBALS['api']['data']);
	return $response
		->withHeader('Content-Type', 'text/html')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/page/plugin/{plugin}/{page}', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();

	$ib->api->setAPIResponseData(include_once(__DIR__."/../../../inc/plugins/".$args['plugin']."/pages/".$args['page'].".php"));

	// Return the response
	$response->getBody()->write($GLOBALS['api']['data']);
	return $response
		->withHeader('Content-Type', 'text/html')
		->withStatus($GLOBALS['responseCode']);
});