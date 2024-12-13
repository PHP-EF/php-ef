<?php
// Define the method (get/post/put/patch/delete/etc.)
$app->get('/helloworld', function ($request, $response, $args) {
	// Instantiate ib Class
	$ib = ($request->getAttribute('ib')) ?? new ib();

	// API Endpoint Code
	$result = 'Success';
	$message = 'Hello World';
	$responseCode = '201';
	$data = array(
		'An example list'
	);

	// Set the response
	$ib->api->setAPIResponse($result,$message,$responseCode,$data);

	// Return the response
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});