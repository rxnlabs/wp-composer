<?php
/*
Plugin Name: WP Composer
Plugin URI: https://rxnlabs.com
Description: Manage your WordPress dependencies using Composer including themes and plugins
Version: 1.0.0
Author: De'Yonté W.<dev@rxnlabs.com>
Author URI: https://rxnlabs.com
License: GPL2+
*/

if (defined('WP_CLI') && WP_CLI && php_sapi_name() === 'cli') {
	// Check if installed as WordPress plugin. If so, make sure the composer dependencies have been installed
	if (defined('ABSPATH')) {
		if (file_exists( __DIR__ . '/vendor/autoload.php')) {
			require_once  __DIR__ . '/vendor/autoload.php';
		} else {
			die( "Please, run composer install --no-dev first" );
		}
	}

	$composer_dependencies = new \rxnlabs\Dependencies();
	$composer_dependencies_wp_cli = new \rxnlabs\WPCLI($composer_dependencies);
	$composer_dependencies_wp_cli->registerCommands();
	$composer_dependencies_wp_cli->hooks();
}