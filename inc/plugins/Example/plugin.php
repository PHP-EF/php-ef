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
	'api' => '/api/plugin/example/settings', // api route for settings page, or null if no settings page
];

class examplePlugin extends ib
{
	public function _pluginGetSettings()
	{
		return array(
			'About' => array (
				settingsOption('notice', '', ['title' => 'Information', 'body' => '
				<p>This is an example plugin.</p>
				<br/>']),
			),
			'Plugin Settings' => array(
				settingsOption('password', 'Password', ['label' => 'Some Password']),
				settingsOption('input', 'Config1', ['label' => 'Some option 1']),
				settingsOption('input', 'Config2', ['label' => 'Some option 2']),
				settingsOption('blank'),
				settingsOption('input', 'Config3', ['label' => 'Some option 3']),
				settingsOption('button', '', ['label' => 'Undo', 'icon' => 'fa fa-undo', 'text' => 'Retrieve', 'attr' => 'onclick="doSomething();"']),
			),
			'Connection Settings' => array(
				settingsOption('url', 'URL'),
				settingsOption('password-alt', 'Token'),
				settingsOption('select', 'Server', ['label' => 'Preferred Server', 'options' => array(array("name" => 'Option 1', "value" => 'opt1'),array("name" => 'Option 2', "value" => 'opt2'))]),
			),
		);
	}
}