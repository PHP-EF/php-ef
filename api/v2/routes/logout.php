<?php
$app->get('/logout', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    $ib->auth->logout();
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});