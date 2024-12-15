<?php
// **
// USED TO DEFINE PLUGIN INFORMATION & CLASS
// **

// PLUGIN INFORMATION
$GLOBALS['plugins']['example'] = [ // Plugin Name
	'name' => 'example', // Plugin Name
	'author' => 'TehMuffinMoo', // Who wrote the plugin
	'category' => 'Testing', // One to Two Word Description
	'link' => 'https://github.com/TehMuffinMoo', // Link to plugin info
	'version' => '1.0.0', // SemVer of plugin
	'image' => 'logo.png', // 1:1 non transparent image for plugin
	'settings' => true, // does plugin need a settings modal?
	'api' => '/api/plugins/example/settings', // api route for settings page (All Lowercase)
];

class examplePlugin extends ib
{
	public function _pluginGetSettings()
	{
        return include_once(__DIR__ . DIRECTORY_SEPARATOR . 'config.php');
	}
}