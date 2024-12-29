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

	$results = $ib->plugins->getAvailablePlugins();
	if (empty($results['warnings'])) {
		$ib->api->setAPIResponseData($results['results']);
	} else {
		$ib->api->setAPIResponse('Warning',$results['warnings'],200,$results['results']);
	}

    $response->getBody()->write(jsonE($GLOBALS['api']));
	// Return the response
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->post('/plugins/install', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
	$data = $ib->api->getAPIRequestData($request);

	$ib->plugins->install($data);

    $response->getBody()->write(jsonE($GLOBALS['api']));
	// Return the response
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->post('/plugins/uninstall', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
	$data = $ib->api->getAPIRequestData($request);

	$ib->plugins->uninstall($data);

    $response->getBody()->write(jsonE($GLOBALS['api']));
	// Return the response
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->post('/plugins/reinstall', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
	$data = $ib->api->getAPIRequestData($request);

	$ib->plugins->reinstall($data);

    $response->getBody()->write(jsonE($GLOBALS['api']));
	// Return the response
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/plugins/repositories', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();

	$ib->api->setAPIResponseData($ib->plugins->getPluginRepositories());

    $response->getBody()->write(jsonE($GLOBALS['api']));
	// Return the response
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->post('/plugins/repositories', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
	$data = $ib->api->getAPIRequestData($request);
	if (isset($data['list'])) {
		$config = $ib->config->get();
		$ib->api->setAPIResponseData($ib->config->setRepositories($config,$data['list']));
	} else {
		$ib->api->setAPIResponse('Error','List missing from request');
	}

    $response->getBody()->write(jsonE($GLOBALS['api']));
	// Return the response
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});