<?php
$app->get('/launch[/]', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
	$GLOBALS['api']['response']['data']['status'] = $phpef->launch();
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/checkAccess[/]', function ($request, $response, $args) {
    $phpef = ($request->getAttribute('phpef')) ?? new phpef();
    $data = $request->getQueryParams();
    $currentUser = $phpef->auth->getAuth();
    if (isset($data['node'])) {
        $Result = array(
            "node" => $data['node']
        );
        if ($phpef->auth->checkAccess($data['node'])) {
            $Result['permitted'] = true;
            header("X-PHPEF-User: " . $currentUser['Username']);
            header("X-PHPEF-Email: " . $currentUser['Email']);
            header("X-PHPEF-Firstname: " . $currentUser['Firstname']);
            header("X-PHPEF-Surname: " . $currentUser['Surname']);
            header("X-PHPEF-DisplayName: " . $currentUser['DisplayName']);
            header("X-PHPEF-Groups: " . implode(',', $currentUser['Groups']));
            header("X-PHPEF-IP: " . $currentUser['IPAddress']);
            $phpef->api->setAPIResponse('Success', null, 200, $Result);
        } else {
            $Result['permitted'] = false;
            $phpef->api->setAPIResponse('Error', 'Unauthorized', 401, $Result);
        }
        $phpef->api->setAPIResponseData($Result);
    } else {
		$phpef->api->setAPIResponse('Error', 'Node empty', 422);
	}

    $response->getBody()->write(json_encode($GLOBALS['api']));
    return $response
        ->withHeader('Content-Type', 'application/json;charset=UTF-8')
        ->withStatus($GLOBALS['responseCode']);
});