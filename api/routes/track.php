<?php
$app->post('/t', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    $phpef->reporting->track($phpef->api->getAPIRequestData($request),$phpef->auth->getAuth());
	$phpef->api->setAPIResponseCode(201);

	// Return the response
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});