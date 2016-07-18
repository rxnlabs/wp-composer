<?php
/*
Plugin Name: WP Composer Dependencies
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: Manage your WordPress dependencies using Composer including themes and plugins
Version: 1.0
Author: De'Yonte W. <dev@rxnlabs.com>
Author URI: https://rxnlabs.com
License: A "Slug" license name e.g. GPL2
*/
$composer_autoload_path = __DIR__.'/vendor/autoload.php';
require $composer_autoload_path;

$GLOBALS['composer_dependencies'] = new \rxnlabs\Dependencies();

if (defined('WP_CLI') && WP_CLI) {
	$GLOBALS['composer_dependencies_wp_cli'] = new \rxnlabs\WPCLI($GLOBALS['composer_dependencies']);
	$GLOBALS['composer_dependencies_wp_cli']->registerCommands();
}