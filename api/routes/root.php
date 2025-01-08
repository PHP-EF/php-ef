<?php
$app->get('/launch[/]', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
	$GLOBALS['api']['response']['data']['status'] = $ib->launch();
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});