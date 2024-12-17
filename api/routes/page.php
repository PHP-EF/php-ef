<?php
$app->get('/page/{category}/{page}', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();

	$ib->api->setAPIResponseData(include_once(__DIR__."/../../pages/".$args['category']."/".$args['page'].".php"));

	// Return the response
	$response->getBody()->write($GLOBALS['api']['data']);
	return $response
		->withHeader('Content-Type', 'text/html')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/page/plugin/{plugin}/js', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
	$pluginDir = __DIR__."/../../inc/plugins/".$args['plugin'];

	// Get Custom JS
	if (file_exists($pluginDir.'/main.js')) {
		$jsContent = file_get_contents($pluginDir.'/main.js');
		$ib->api->setAPIResponseData($jsContent);
	}

	// Return the response
	$response->getBody()->write($GLOBALS['api']['data']);
	return $response
		->withHeader('Content-Type', 'text/javascript')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/page/plugin/{plugin}/css', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
	$pluginDir = __DIR__."/../../inc/plugins/".$args['plugin'];

	// Get Custom CSS
	if (file_exists($pluginDir.'/styles.css')) {
		$cssContent = file_get_contents($pluginDir.'/styles.css');
		$ib->api->setAPIResponseData($cssContent);
	}

	// Return the response
	$response->getBody()->write($GLOBALS['api']['data']);
	return $response
		->withHeader('Content-Type', 'text/css')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/page/plugin/{plugin}/{page}', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();

	$pluginDir = __DIR__."/../../inc/plugins/".$args['plugin'];
	$html = '';

	$html .= '<link href="/api/page/plugin/'.$args["plugin"].'/css" rel="stylesheet">';
	$html .= include_once($pluginDir."/pages/".$args['page'].".php");
	$html .= '<script src="/api/page/plugin/'.$args['plugin'].'/js" crossorigin="anonymous"></script>';

	$ib->api->setAPIResponseData($html);

	// Return the response
	$response->getBody()->write($GLOBALS['api']['data']);
	return $response
		->withHeader('Content-Type', 'text/html')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/pages', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    if ($ib->rbac->checkAccess("ADMIN-PAGES")) {
		$ib->api->setAPIResponseData($ib->pages->get());
	}

	// Return the response
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/pages/hierarchy', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
	if ($ib->rbac->checkAccess("ADMIN-PAGES")) {
		$config = $ib->pages->get();
		$navigation = [];
		foreach ($config as $item) {
			if ($item['Type'] === 'Link') {
				$navigation[] = $item;
			} elseif ($item['Type'] === 'Menu') {
				$navigation[$item['Name']] = $item;
				$navigation[$item['Name']]['Items'] = [];
			} elseif ($item['Type'] === 'MenuLink') {
				$navigation[$item['Menu']]['Items'][] = $item;
			} elseif ($item['Type'] === 'SubMenu') {
				$navigation[$item['Menu']]['Items'][$item['Name']] = $item;
				$navigation[$item['Menu']]['Items'][$item['Name']]['Items'] = [];
			} elseif ($item['Type'] === 'SubMenuLink') {
				$navigation[$item['Menu']]['Items'][$item['Submenu']]['Items'][] = $item;
			}
		}
	}

	$ib->api->setAPIResponseData($navigation);

	// Return the response
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});