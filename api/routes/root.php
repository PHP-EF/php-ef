<?php
$app->get('/launch[/]', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
	$GLOBALS['api']['response']['data']['status'] = $ib->launch();
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/checkAccess[/]', function ($request, $response, $args) {
    $ib = ($request->getAttribute('ib')) ?? new ib();
    $data = $request->getQueryParams();
    $currentUser = $ib->auth->getAuth();
    if (isset($data['node'])) {
        $Result = array(
            "node" => $data['node']
        );
        if ($ib->auth->checkAccess($data['node'])) {
            $Result['permitted'] = true;
            header("X-PHPEF-User: " . $currentUser['Username']);
            header("X-PHPEF-Email: " . $currentUser['Email']);
            header("X-PHPEF-Firstname: " . $currentUser['Firstname']);
            header("X-PHPEF-Surname: " . $currentUser['Surname']);
            header("X-PHPEF-DisplayName: " . $currentUser['DisplayName']);
            header("X-PHPEF-Groups: " . implode(',', $currentUser['Groups']));
            header("X-PHPEF-IP: " . $currentUser['IPAddress']);
            $ib->api->setAPIResponse('Success', null, 200, $Result);
        } else {
            $Result['permitted'] = false;
            $ib->api->setAPIResponse('Error', 'Unauthorized', 401, $Result);
        }
        $ib->api->setAPIResponseData($Result);
    } else {
		$ib->api->setAPIResponse('Error', 'Node empty', 422);
	}

    $response->getBody()->write(json_encode($GLOBALS['api']));
    return $response
        ->withHeader('Content-Type', 'application/json;charset=UTF-8')
        ->withStatus($GLOBALS['responseCode']);
});