<?php
/*
Plugin Name: WP Composer Dependencies
Plugin URI: https://rxnlabs.com
Description: Manage your WordPress dependencies using Composer including themes and plugins
Version: 1.0.0
Author: De'Yonte W. <dev@rxnlabs.com>
Author URI: https://rxnlabs.com
License: A "Slug" license name e.g. GPL2
*/
require __DIR__.'/vendor/autoload.php';
$composer_dependencies = new \rxnlabs\Dependencies();
if (defined('WP_CLI') && WP_CLI && php_sapi_name() === 'cli') {
	$composer_dependencies_wp_cli = new \rxnlabs\WPCLI($composer_dependencies);
	$composer_dependencies_wp_cli->registerCommands();
	$composer_dependencies_wp_cli->hooks();
} else {
	$composer_dependencies->hooks();
}