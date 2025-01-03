<?php
$app->get('/page/{category}/{page}', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();

	$pagePath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . $args['category'] . DIRECTORY_SEPARATOR . $args['page'] . '.php';
	if (file_exists($pagePath)) {
		$ib->api->setAPIResponseData(include_once($pagePath));
		$response->getBody()->write($GLOBALS['api']['data']);
	} else {
		$ib->api->setAPIResponse('Error','Page not found',404);
		$response->getBody()->write(jsonE($GLOBALS['api']));
	}

	// Return the response
	return $response
		->withHeader('Content-Type', 'text/html')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/page/plugin/{plugin}/js', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
	$pluginDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $args['plugin'];

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
	$pluginDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $args['plugin'];

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

	
	$pluginDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $args['plugin'];
	$pagePath = $pluginDir . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . $args['page'] . '.php';
	if (file_exists($pagePath)) {
		$html = '';
		$html .= '<link href="/api/page/plugin/'.$args["plugin"].'/css" rel="stylesheet">';
		$html .= include_once($pagePath);
		$html .= '<script src="/api/page/plugin/'.$args['plugin'].'/js" crossorigin="anonymous"></script>';
		$ib->api->setAPIResponseData($html);
		$response->getBody()->write($GLOBALS['api']['data']);
	} else {
		$ib->api->setAPIResponse('Error','Page not found',404);
		$response->getBody()->write(jsonE($GLOBALS['api']));
	}

	// Return the response
	return $response
		->withHeader('Content-Type', 'text/html')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/pages', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
	$data = $request->getQueryParams();
    if ($ib->auth->checkAccess("ADMIN-PAGES")) {
		$Menu = $data['menu'] ?? null;
		$SubMenu = $data['submenu'] ?? null;
		if ($Menu || $SubMenu) {
			$ib->api->setAPIResponseData($ib->pages->getByMenu($Menu,$SubMenu));
		} else {
			$ib->api->setAPIResponseData($ib->pages->get());	
		}
	}

	// Return the response
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->post('/pages', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    if ($ib->auth->checkAccess("ADMIN-PAGES")) {
        $data = $ib->api->getAPIRequestData($request);
        $Name = $data['name'] ?? exit($ib->api->setAPIResponse('Error','Name missing from request'));
        $Title = $data['title'] ?? null;
        $Type = $data['type'] ?? null;
		$Url = $data['url'] ?? null;
        $Menu = $data['menu'] ?? null;
        $Submenu = $data['submenu'] ?? null;
		$ACL = $data['acl'] ?? null;
		$Icon = $data['icon'] ?? null;
		$ib->pages->new($Name,$Title,$Type,$Url,$Menu,$Submenu,$ACL,$Icon);
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->patch('/page/{id}', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    if ($ib->auth->checkAccess("ADMIN-PAGES")) {
        if (isset($args['id'])) {
			$data = $ib->api->getAPIRequestData($request);
			$Name = $data['name'] ?? null;
			$Title = $data['title'] ?? null;
			$Type = $data['type'] ?? null;
			$Url = $data['url'] ?? null;
			$Menu = $data['menu'] ?? null;
			$Submenu = $data['submenu'] ?? null;
			$ACL = $data['acl'] ?? null;
			$Icon = $data['icon'] ?? null;
			$ib->pages->set($args['id'],$Name,$Title,$Type,$Url,$Menu,$Submenu,$ACL,$Icon);
        } else {
            $ib->api->setAPIResponse('Error','id missing from request',400);
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

// Update CMDB Column Weight
$app->patch('/page/{id}/weight', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    if ($ib->auth->checkAccess("ADMIN-PAGES")) {
		$data = $ib->api->getAPIRequestData($request);
		if (isset($data['weight'])) {
			$ib->pages->updatePageWeight($args['id'],$data['weight']);
		} else {
			$ib->api->setAPIResponse('Error','Weight missing from request');
		}        
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});


$app->delete('/page/{id}', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    if ($ib->auth->checkAccess("ADMIN-PAGES")) {
        if (isset($args['id'])) {
            $ib->pages->delete($args['id']);
        } else {
            $ib->api->setAPIResponse('Error','id missing from request',400);
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/pages/hierarchy', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
	if ($ib->auth->checkAccess("ADMIN-PAGES")) {
		$config = $ib->pages->get();
		$navigation = [];

		// First pass: Process Menus and Submenus
		foreach ($config as $item) {
			if ($item['Type'] === 'Menu') {
				$navigation[$item['Name']] = $item;
				$navigation[$item['Name']]['Items'] = [];
			} elseif ($item['Type'] === 'SubMenu') {
				if (!isset($navigation[$item['Menu']]['Items'])) {
					$navigation[$item['Menu']]['Items'] = [];
				}
				$navigation[$item['Menu']]['Items'][$item['Name']] = $item;
				$navigation[$item['Menu']]['Items'][$item['Name']]['Items'] = [];
			}
		}
		
		// Second pass: Process Links, MenuLinks, and SubMenuLinks
		foreach ($config as $item) {
			if ($item['Type'] === 'Link') {
				$navigation[] = $item;
			} elseif ($item['Type'] === 'MenuLink') {
				if (isset($navigation[$item['Menu']]['Items'])) {
					$navigation[$item['Menu']]['Items'][] = $item;
				}
			} elseif ($item['Type'] === 'SubMenuLink') {
				if (isset($navigation[$item['Menu']]['Items'][$item['Submenu']]['Items'])) {
					$navigation[$item['Menu']]['Items'][$item['Submenu']]['Items'][] = $item;
				}
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

$app->get('/pages/list', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
	if ($ib->auth->checkAccess("ADMIN-PAGES")) {
		$ib->api->setAPIResponseData($ib->pages->getAllAvailablePages());
	}

	// Return the response
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/pages/root', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    if ($ib->auth->checkAccess("ADMIN-PAGES")) {
		$ib->api->setAPIResponseData($ib->pages->getMainLinksAndMenus());	
	}

	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/pages/menus', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    if ($ib->auth->checkAccess("ADMIN-PAGES")) {
		$ib->api->setAPIResponseData($ib->pages->getByType('Menu'));	
	}

	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/pages/submenus', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
	$data = $request->getQueryParams();
    if ($ib->auth->checkAccess("ADMIN-PAGES")) {
		$Menu = $data['menu'] ?? null;
		$ib->api->setAPIResponseData($ib->pages->getByType('SubMenu',$Menu));	
	}

	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});