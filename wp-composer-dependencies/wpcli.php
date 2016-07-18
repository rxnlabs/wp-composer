<?php
/**
 * Register and use commands with WP-CLI
 */
namespace rxnlabs;
use \rxnlabs\Dependencies as Dependencies;

class WPCLI
{

	protected $composer;

	public function __construct(\rxnlabs\Dependencies $composer)
	{
		$this->composer = $composer;
	}

	/**
	 * Register commands for use with WP CLI
	 */
	public function registerCommands()
	{
		\WP_CLI::add_command('composer plugins add', array($this,'addPlugins'));
		\WP_CLI::add_command('composer themes add', array($this,'addThemes'));
	}

	/**
	 * Add installed plugins to composer.json.
	 *
	 * @return void
	 */
	public function addPlugins()
	{
		$plugin_list = array();
		$composer = json_decode(json_encode($this->composer->readComposerFile()), true);
		ob_start();
		$installed_plugins = \WP_CLI::run_command(array('plugin', 'list'), array('format'=>'json'));
		$plugins_found = json_decode(ob_get_clean(), true);
		if (is_array($plugins_found)) {
			foreach($plugins_found as $plugin) {
				if ($this->composer->isPluginAvailable($plugin['name']) === true) {
					$plugin_list[] = array('name'=>$this->composer->cleanRepoName($plugin['name']),
					                       'version'=>$plugin['version']);
				}
			}
		}

		if (!empty($composer) && !empty($plugin_list)) {
			foreach($plugin_list as $plugin) {
				$plugin_namespace = sprintf('%s/%s', $this->composer->wpackagist_namepsace['plugin'], $plugin['name']);
				$composer['require'][$plugin_namespace] = $plugin['version'];
			}
		}

	}

	/**
	 * Add installed themes to composer.json
	 *
	 * @return void
	 */
	public function addThemes()
	{
		$theme_list = array();
		$composer = json_decode(json_encode($this->composer->readComposerFile()), true);
		ob_start();
		$installed_themes = \WP_CLI::run_command(array('theme', 'list'), array('format'=>'json'));
		$themes_found = json_decode(ob_get_clean(), true);
		if (is_array($themes_found)) {
			foreach($themes_found as $theme) {
				if ($this->composer->isThemeAvailable($theme['name']) === true) {
					$plugin_list[] = array('name'=>$this->composer->cleanRepoName($theme['name']),
					                       'version'=>$theme['version']);
				}
			}
		}

		if (!empty($composer) && !empty($theme_list)) {
			foreach($theme_list as $theme) {
				$theme_namespace = sprintf('%s/%s', $this->composer->wpackagist_namepsace['theme'], $theme['name']);
				$composer['require'][$theme_namespace] = $theme['version'];
			}
		}
	}
}