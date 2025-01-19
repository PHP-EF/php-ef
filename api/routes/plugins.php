<?php
$app->get('/plugins/installed', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
	if ($phpef->auth->checkAccess("ADMIN-CONFIG")) {
		$phpef->api->setAPIResponseData($phpef->plugins->getInstalledPlugins());
	}
	// Return the response
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/plugins/available', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
	if ($phpef->auth->checkAccess("ADMIN-CONFIG")) {
		$results = $phpef->plugins->getAvailablePlugins();
		if (empty($results['warnings'])) {
			$phpef->api->setAPIResponseData($results['results']);
		} else {
			$phpef->api->setAPIResponse('Warning',$results['warnings'],200,$results['results']);
		}
	}
	// Return the response
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->post('/plugins/install', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
	if ($phpef->auth->checkAccess("ADMIN-CONFIG")) {
		$data = $phpef->api->getAPIRequestData($request);
		$phpef->plugins->install($data);
	}
	// Return the response
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->post('/plugins/uninstall', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
	if ($phpef->auth->checkAccess("ADMIN-CONFIG")) {
		$data = $phpef->api->getAPIRequestData($request);
		$phpef->plugins->uninstall($data);
	}
	// Return the response
    $response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->post('/plugins/reinstall', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
	if ($phpef->auth->checkAccess("ADMIN-CONFIG")) {
		$data = $phpef->api->getAPIRequestData($request);
		$phpef->plugins->reinstall($data);
	}
	// Return the response
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/plugins/repositories', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
	if ($phpef->auth->checkAccess("ADMIN-CONFIG")) {
		$phpef->api->setAPIResponseData($phpef->plugins->getPluginRepositories());
	}
	// Return the response
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->post('/plugins/repositories', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
	if ($phpef->auth->checkAccess("ADMIN-CONFIG")) {
		$data = $phpef->api->getAPIRequestData($request);
		if (isset($data['list'])) {
			$config = $phpef->config->get();
			$phpef->api->setAPIResponseData($phpef->config->setRepositories($config,$data['list']));
		} else {
			$phpef->api->setAPIResponse('Error','List missing from request');
		}
	}

	// Return the response
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});