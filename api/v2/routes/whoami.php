<?php
$app->get('/whoami', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();

    if (null !== $ib->auth->getAuth()) {
        $AuthContent = $ib->auth->getAuth();
        $AuthContent['headers'] = getallheaders();
        $UnsetHeaders = array(
            "Remote-Email",
            "Remote-Groups",
            "Remote-Name",
            "Remote-User"
        );
        foreach ($UnsetHeaders as $UnsetHeader) {
            unset($AuthContent['headers'][$UnsetHeader]);
        }
        $ib->api->setAPIData($AuthContent);
    }

	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});