<?php
$app->get('/images', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('ib')) ?? new phpef();
    $phpef->api->setAPIResponseData($phpef->getImages());

	// Return the response
    $response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});