<?php
// **
// USED TO DEFINE PLUGIN INFORMATION & CLASS
// **

// PLUGIN INFORMATION
$GLOBALS['plugins']['ib'] = [ // Plugin Name
	'name' => 'ib', // Plugin Name
	'author' => 'TehMuffinMoo', // Who wrote the plugin
	'category' => 'Infoblox', // One to Two Word Description
	'link' => 'https://github.com/TehMuffinMoo/ib-sa-report', // Link to plugin info
	'version' => '1.0.0', // SemVer of plugin
	'image' => 'logo.png', // 1:1 non transparent image for plugin
	'settings' => true, // does plugin need a settings modal?
	'api' => '/api/v2/plugins/ib/settings', // api route for settings page (All Lowercase)
];

class ibPlugin extends ib
{
	public function _pluginGetSettings()
	{
        return include_once(__DIR__ . DIRECTORY_SEPARATOR . 'config.php');
	}
}