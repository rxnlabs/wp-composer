<?php
/**
 * Register and use commands with WP-CLI
 */
namespace rxnlabs;
use \rxnlabs\Dependencies as Dependencies;

class WPCLI
{

	protected $composer;

	/**
	 * WPCLI constructor.
	 * @param \rxnlabs\Dependencies $composer
	 */
	public function __construct(\rxnlabs\Dependencies $composer)
	{
		$this->composer = $composer;
	}

	/**
	 * Register commands for use with WP CLI
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	public function registerCommands()
	{
		\WP_CLI::add_command('composer plugins', array($this,'plugins'));
		\WP_CLI::add_command('composer themes', array($this,'themes'));
		\WP_CLI::add_command('composer add', array($this,'addAllDependencies'));
	}

	/**
	 * Perform actions on installed plugins.
	 *
	 * ## OPTIONS
	 *
	 * <action>
	 * : The action to perform. Available actions are:
	 * - "add" action will add installed plugins to composer.json
	 * - "delete" action will remove plugins from composer.json
	 *
	 * [--file]
	 * : Path to save the composer.json file
	 *
	 * [--all]
	 * : Add plugins found on wordpress.org and plugins not found on wordpress.org. By default only plugins  available on wordpress.org will be added
	 *
	 * [--latest]
	 * : Add current version of plugin installed or specify to always use the latest version from whatever repo the plugin is coming from.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @param array $args Positional argumenets passed to command
	 * @param array $assoc_args Key based arguments passed to command
	 * @return true|false True if able to write to a composer.json file, false if unable to write to the file for some reason
	 */
	public function plugins($args, $assoc_args = array())
	{
		if (isset($args[0])) {
			switch($args[0]) {
				case "add":
					$this->addPlugins($args, $assoc_args);
					break;
				default:
					$message = sprintf('%s is not a valid action', $args[0]);
					\WP_CLI::warning($message);
			}
		}
	}

	/**
	 * Perform actions on installed themes.
	 *
	 * ## OPTIONS
	 *
	 * <action>
	 * : The action to perform. Available actions are:
	 * - "add" action will add installed themes to composer.json
	 * - "delete" action will remove themes from composer.json
	 *
	 * [--file]
	 * : Path to save the composer.json file
	 *
	 * [--all]
	 * : Add themes found on wordpress.org and themes not found on wordpress.org. By default only themes available on wordpress.org will be added
	 *
	 * [--latest]
	 * : Add current version of theme installed or specify to always use the latest version from whatever repo the theme is coming from.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @param array $args Positional argumenets passed to command
	 * @param array $assoc_args Key based arguments passed to command
	 * @return true|false True if able to write to a composer.json file, false if unable to write to the file for some reason
	 */
	public function themes($args, $assoc_args = array())
	{
		if (isset($args[0])) {
			switch($args[0]) {
				case "add":
					$this->addThemes($args, $assoc_args);
					break;
				default:
					$message = sprintf('%s is not a valid action', $args[0]);
					\WP_CLI::warning($message);
			}
		}
	}

	/**
	 * Add installed plugins and themes to composer.json
	 *
	 * ## OPTIONS
	 *
	 * [--file]
	 * : Path to save the composer.json file
	 *
	 * [--all]
	 * : Add themes and plugins found on wordpress.org and themes and plugins not found on wordpress.org. By default only themes and plugins available on wordpress.org will be added
	 *
	 * [--latest]
	 * : Add current version of theme or plugin installed or specify to always use the latest version from whatever repo the theme or plugn is coming from.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @param array $args Positional argumenets passed to command
	 * @param array $assoc_args Key based arguments passed to command
	 * @return true|false True if able to write to a composer.json file, false if unable to write to the file for some reason
	 */
	public function addAllDependencies($args, array $assoc_args = array())
	{
		\WP_CLI::run_command(array('composer', 'plugins', 'add'), $assoc_args);
		\WP_CLI::run_command(array('composer', 'themes', 'add'), $assoc_args);
	}

	/**
	 * Add installed plugins to composer.json.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @param array $args Positional arguments passed to command
	 * @param array $assoc_args Key based arguments passed to command
	 * @return true|false True if able to write to a composer.json file, false if unable to write to the file for some reason
	 */
	private function addPlugins($args, array $assoc_args = array())
	{
		$file = '';
		$all = false;
		if (isset($assoc_args['file'])) {
			$file = $assoc_args['file'];
		}

		if (isset($assoc_args['all'])) {
			$all = true;
		}

		if (isset($assoc_args['latest'])) {
			$latest_version = '*';
		}

		$composer = json_decode(json_encode($this->composer->readComposerFile($file)), true);
		ob_start();
		$installed_plugins = \WP_CLI::run_command(array('plugin', 'list'), array('format'=>'json'));
		$plugins_found = json_decode(ob_get_clean(), true);

		if (!empty($composer) && is_array($plugins_found)) {
			foreach ($plugins_found as $plugin) {
				if ($all === false) {
					if ($this->composer->isPluginAvailable($plugin['name'])) {
						if (isset($latest_version)) {
							$plugin_version = $latest_version;
						} else {
							$plugin_version = $plugin['version'];
						}
						\WP_CLI::line(sprintf('Adding plugin %s. Using version %s',$plugin['name'], $plugin_version));
						$this->composer->addPluginDependency($plugin['name'], $plugin_version);
					}
				} else {
					if (isset($latest_version)) {
						$plugin_version = $latest_version;
					} else {
						$plugin_version = $plugin['version'];
					}
					\WP_CLI::line(sprintf('Adding plugin %s. Using version %s',$plugin['name'], $plugin_version));
					$this->composer->addPluginDependency($plugin['name'], $plugin_version);
				}
			}

			try {
				$success = $this->composer->saveComposer($file);
				$saved_file = $file;
				if (empty($saved_file)) {
					$saved_file = $this->composer->composer_file_location;
				}

				if ($success === true) {
					\WP_CLI::success(sprintf('Saved plugin dependencies to %s', $saved_file));
					return true;
				}
			} catch (\Exception $e) {
				\WP_CLI::warning($e->getMessage());
				return false;
			}
		}

	}

	/**
	 * Add installed themes to composer.json
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @param array $args Positional argumenets passed to command
	 * @param array $assoc_args Key based arguments passed to command
	 * @return true|false True if able to write to a composer.json file, false if unable to write to the file for some reason
	 */
	private function addThemes($args, $assoc_args)
	{
		$file = '';
		$all = false;
		if (isset($assoc_args['file'])) {
			$file = $assoc_args['file'];
		}

		if (isset($assoc_args['all'])) {
			$all = true;
		}

		if (isset($assoc_args['latest'])) {
			$latest_version = '*';
		}

		$composer = json_decode(json_encode($this->composer->readComposerFile($file)), true);
		ob_start();
		$installed_themes = \WP_CLI::run_command(array('theme', 'list'), array('format'=>'json'));
		$themes_found = json_decode(ob_get_clean(), true);

		if (!empty($composer) && is_array($themes_found)) {
			foreach ($themes_found as $theme) {
				if ($all === false) {
					if ($this->composer->isThemeAvailable($theme['name'])) {
						if (isset($latest_version)) {
							$theme_version = $latest_version;
						} else {
							$theme_version = $theme['version'];
						}
						\WP_CLI::line(sprintf('Adding theme %s. Using version %s',$theme['name'], $theme_version));
						$this->composer->addThemeDependency($theme['name'], $theme_version);
					}
				} else {
					if (isset($latest_version)) {
						$theme_version = $latest_version;
					} else {
						$theme_version = $theme['version'];
					}
					\WP_CLI::line(sprintf('Adding theme %s. Using version %s',$theme['name'], $theme_version));
					$this->composer->addThemeDependency($theme['name'], $theme_version);
				}
			}

			try {
				$success = $this->composer->saveComposer($file);
				$saved_file = $file;
				if (empty($saved_file)) {
					$saved_file = $this->composer->composer_file_location;
				}

				if ($success === true) {
					\WP_CLI::success(sprintf('Saved theme dependencies to %s', $saved_file));
					return true;
				}
			} catch (\Exception $e) {
				\WP_CLI::warning($e->getMessage());
				return false;
			}
		}
	}
}