<?php
// Safely encode JSON response
function safe_json_encode($value, $options = 0, $depth = 512)
{
	$encoded = json_encode($value, $options, $depth);
	if ($encoded === false && $value && json_last_error() == JSON_ERROR_UTF8) {
		$encoded = json_encode(utf8ize($value), $options, $depth);
	}
	return $encoded;
}

// Function to return JSON data
function jsonE($json)
{
	return safe_json_encode($json, JSON_HEX_QUOT | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

// Sets the base API path
function getBasePath()
{
	$uri = $_SERVER['REQUEST_URI'];
	$uriUse = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
	if (stripos($uri, 'api/v2/') !== false) {
		return $uriUse;
	} else {
		return '';
	}
}