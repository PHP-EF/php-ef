<?php
$app->get('/changelog', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();

    $MD = '<link href="/assets/css/changelog.css" rel="stylesheet">';
    $MD .= '<h1><center>Change Log</center></h1>';
    $MD .= generate_markdown(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'CHANGELOG.md');
	$ib->api->setAPIResponseData($MD);

	$response->getBody()->write($GLOBALS['api']['data']);
	return $response
		->withHeader('Content-Type', 'text/html; charset=utf-8')
		->withStatus($GLOBALS['responseCode']);
});