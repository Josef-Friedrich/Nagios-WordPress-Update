<?php

// Include your Nagios server IP below
// It is safe to keep 127.0.0.1
$allowed_ips = array(
	'127.0.0.1',
);

// Check if the requesting server is allowed
if (! in_array($_SERVER['REMOTE_ADDR'], $allowed_ips))
{
	echo 'CRITICAL#IP Not Allowed';
	exit;
}

require_once('wp-load.php');

global $wp_version;
$core_updates = FALSE;
$plugin_updates = FALSE;

wp_version_check();
wp_update_plugins();
wp_update_themes();

if (function_exists('get_transient'))
{
	$core = get_transient('update_core');
	$plugins = get_transient('update_plugins');
	$themes = get_transient('update_themes');

	if ($core == FALSE)
	{
		$core = get_site_transient('update_core');
		$plugins = get_site_transient('update_plugins');		
		$themes = get_site_transient('update_themes');
	}
}
else
{
	$core = get_site_transient('update_core');
	$plugins = get_site_transient('update_plugins');
	$themes = get_site_transient('update_themes');
}

$core_available = FALSE;
$plugin_available = FALSE;
$theme_available = FALSE;

foreach ($core->updates as $core_update)
{
	if ($core_update->current != $wp_version)
	{
		$core_available = TRUE;
	}
}

$plugin_available = (count($plugins->response) > 0);
$theme_available = (count($themes->response) > 0);

$text = array();

if ($core_available)
	$text[] = 'Core updates available';

if ($plugin_available)
	$text[] = 'Plugin updates available';

if ($theme_available)
	$text[] = 'Theme updates available';

$status = 'OK';

if ($core_available)
{
	$status = 'CRITICAL';
}
elseif ($theme_available OR $plugin_available)
{
	$status = 'WARNING';
}

echo $status . '#' . implode($text, ';');
