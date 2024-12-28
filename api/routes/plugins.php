<?php
$app->get('/plugins/installed', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();

	$ib->api->setAPIResponseData($ib->plugins->getInstalledPlugins());

    $response->getBody()->write(jsonE($GLOBALS['api']));
	// Return the response
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/plugins/available', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();

	$ib->api->setAPIResponseData($ib->plugins->getAvailablePlugins());

    $response->getBody()->write(jsonE($GLOBALS['api']));
	// Return the response
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});