<?php
$app->get('/launch[/]', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
	// $tabInfo = $Organizr->getUserTabsAndCategories();
	// $GLOBALS['api']['response']['data']['categories'] = ($tabInfo['categories']) ?? false;
	// $GLOBALS['api']['response']['data']['tabs'] = ($tabInfo['tabs']) ?? false;
	// $GLOBALS['api']['response']['data']['user'] = $Organizr->user;
	// $GLOBALS['api']['response']['data']['branch'] = $Organizr->config['branch'];
	// $GLOBALS['api']['response']['data']['theme'] = $Organizr->config['theme'];
	// $GLOBALS['api']['response']['data']['style'] = $Organizr->config['style'];
	// $GLOBALS['api']['response']['data']['version'] = $Organizr->version;
	// $GLOBALS['api']['response']['data']['settings'] = $Organizr->organizrSpecialSettings();
	// $GLOBALS['api']['response']['data']['plugins'] = $Organizr->pluginGlobalList();
	// $GLOBALS['api']['response']['data']['appearance'] = $Organizr->loadAppearance();
	$GLOBALS['api']['response']['data']['status'] = $ib->launch();
	// $GLOBALS['api']['response']['data']['sso'] = $Organizr->ssoCookies();
	// $GLOBALS['api']['response']['data']['warnings'] = $Organizr->warnings;
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});