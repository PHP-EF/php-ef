<?php
$app->get('/uuid/generate', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('ib')) ?? new phpef(); 
    $phpef->api->setAPIResponseData(\Ramsey\Uuid\Uuid::uuid4());
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});