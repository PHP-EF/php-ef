<?php
$app->get('/plugins/installed', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();

	$phpef->api->setAPIResponseData($phpef->plugins->getInstalledPlugins());

    $response->getBody()->write(jsonE($GLOBALS['api']));
	// Return the response
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/plugins/available', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();

	$results = $phpef->plugins->getAvailablePlugins();
	if (empty($results['warnings'])) {
		$phpef->api->setAPIResponseData($results['results']);
	} else {
		$phpef->api->setAPIResponse('Warning',$results['warnings'],200,$results['results']);
	}

    $response->getBody()->write(jsonE($GLOBALS['api']));
	// Return the response
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->post('/plugins/install', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
	$data = $phpef->api->getAPIRequestData($request);

	$phpef->plugins->install($data);

    $response->getBody()->write(jsonE($GLOBALS['api']));
	// Return the response
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->post('/plugins/uninstall', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
	$data = $phpef->api->getAPIRequestData($request);

	$phpef->plugins->uninstall($data);

    $response->getBody()->write(jsonE($GLOBALS['api']));
	// Return the response
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->post('/plugins/reinstall', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
	$data = $phpef->api->getAPIRequestData($request);

	$phpef->plugins->reinstall($data);

    $response->getBody()->write(jsonE($GLOBALS['api']));
	// Return the response
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/plugins/repositories', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();

	$phpef->api->setAPIResponseData($phpef->plugins->getPluginRepositories());

    $response->getBody()->write(jsonE($GLOBALS['api']));
	// Return the response
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->post('/plugins/repositories', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
	$data = $phpef->api->getAPIRequestData($request);
	if (isset($data['list'])) {
		$config = $phpef->config->get();
		$phpef->api->setAPIResponseData($phpef->config->setRepositories($config,$data['list']));
	} else {
		$phpef->api->setAPIResponse('Error','List missing from request');
	}

    $response->getBody()->write(jsonE($GLOBALS['api']));
	// Return the response
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});