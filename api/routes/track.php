<?php
$app->post('/t', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    $ib->reporting->track($ib->api->getAPIRequestData($request),$ib->auth->getAuth());
	$ib->api->setAPIResponseCode(201);

	// Return the response
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});