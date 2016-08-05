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
		\WP_CLI::add_command('composer plugins', array($this,'plugins'), [
			'after_wp_config_load' => $this->setInstallerPath()
		]);
		\WP_CLI::add_command('composer themes', array($this,'themes'), [
			'after_wp_config_load' =>$this->setInstallerPath()
		]);
		\WP_CLI::add_command('composer add', array($this,'addAllDependencies'), [
			'after_wp_config_load' => $this->setInstallerPath()
		]);
		\WP_CLI::add_command('composer plugin', array($this,'plugin'), [
				'after_wp_config_load' => $this->setInstallerPath()
		]);
		\WP_CLI::add_command('composer theme', array($this,'theme'), [
				'after_wp_config_load' => $this->setInstallerPath()
		]);
	}

	/**
	 * Perform actions on installed plugins.
	 *
	 * ## OPTIONS
	 *
	 * <action>
	 * : The action to perform. Available actions are:
	 * - "add" action will add installed plugins to composer.json
	 * - "remove" action will remove plugins from composer.json
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
	 * [--installer-paths]
	 * : Set the WordPress plugins and themes installer path
	 *
	 * [--ip]
	 * : Set the WordPress plugins and themes installer path (shorter alias for "installer-paths")
	 *
	 * [--dev]
	 * : Only apply command to plugins defined as a dev requirement (only works with "uninstall" command at this moment)
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @param array $args Positional arguments passed to command
	 * @param array $assoc_args Key based arguments passed to command
	 * @return true|false True if able to write to a composer.json file, false if unable to write to the file for some reason
	 */
	public function plugins($args, $assoc_args = array())
	{
		if (isset($args[0])) {

			if (isset($assoc_args['installer-path']) || isset($assoc_args['ip'])) {
				if (isset($assoc_args['installer-path'])) {
					$installer_path = $assoc_args['installer-path'];
				} else {
					$installer_path = $assoc_args['ip'];
				}
				$this->composer->setInstallerPath( $installer_path );
			}

			switch($args[0]) {
				case "add":
					$this->addPlugins($args, $assoc_args);
					break;
				case "install":
					$this->installPlugins($args, $assoc_args);
					break;
				case "uninstall":
					$this->uninstallPlugins($args, $assoc_args);
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
	 * - "remove" action will remove themes from composer.json
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
	 * [--installer-paths]
	 * : Set the WordPress plugins and themes installer path
	 *
	 * [--ip]
	 * : Set the WordPress plugins and themes installer path (shorter alias for "installer-paths")
	 *
	 * [--dev]
	 * : Only apply command to plugins defined as a dev requirement (only works with "uninstall" command at this moment)
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @param array $args Positional arguments passed to command
	 * @param array $assoc_args Key based arguments passed to command
	 * @return true|false True if able to write to a composer.json file, false if unable to write to the file for some reason
	 */
	public function themes($args, $assoc_args = array())
	{
		if (isset($args[0])) {

			if (isset($assoc_args['installer-path']) || isset($assoc_args['ip'])) {
				if (isset($assoc_args['installer-path'])) {
					$installer_path = $assoc_args['installer-path'];
				} else {
					$installer_path = $assoc_args['ip'];
				}
				$this->composer->setInstallerPath( $installer_path );
			}

			switch($args[0]) {
				case "add":
					$this->addThemes($args, $assoc_args);
					break;
				case "install":
					$this->installThemes( $args, $assoc_args );
					break;
				case "uninstall":
					$this->uninstallThemes( $args, $assoc_args );
					break;
				default:
					$message = sprintf('%s is not a valid action', $args[0]);
					\WP_CLI::warning($message);
			}
		}
	}

	/**
	 * Perform actions on a particular plugin by specifying the plugin slug.
	 *
	 * ## OPTIONS
	 *
	 * <action>
	 * : The action to perform. Available actions are:
	 * - "add" will add the specified plugin slug to the composer.json file
	 * - "remove" will remove the specified plugin from the composer.json file
	 *
	 * <plugin|zip|url>
	 * : Plugin slug. When using the plugin slug to specify the plugin, by default only a plugin available on wordpress.org will be added
	 *
	 * [--file]
	 * : Path to save the composer.json file
	 *
	 * [--latest]
	 * : Add current version of plugin installed or specify to always use the latest version from whatever repo the plugin is coming from.
	 *
	 * [--version]
	 * : Use the specified the version. This only applies when using the "add" action.
	 *
	 * [--dev]
	 * : Add or remove the plugin as a dev dependency
	 *
	 * [--installer-paths]
	 * : Set the WordPress plugins and themes installer path
	 *
	 * [--ip]
	 * : Set the WordPress plugins and themes installer path (shorter alias for "installer-paths")
	 *
	 * @param array $args Positional arguments passed to the command
	 * @param array $assoc_args Key based arguments passed to the command
	 */
	public function plugin($args, $assoc_args = array())
	{
		if ( isset( $args[1] ) ) {
			$plugin_slug = $args[1];

			if (isset($assoc_args['installer-path']) || isset($assoc_args['ip'])) {
				if (isset($assoc_args['installer-path'])) {
					$installer_path = $assoc_args['installer-path'];
				} else {
					$installer_path = $assoc_args['ip'];
				}
				$this->composer->setInstallerPath( $installer_path );
			}

			if ( isset( $args[0] ) ) {
				switch ( $args[0] ) {
					case "add":
						$this->addPlugin( $plugin_slug, $args, $assoc_args );
						break;
					case "remove":
						$this->removePlugin( $plugin_slug, $args, $assoc_args );
						break;
					default:
						$message = sprintf( '%s is not a valid action', $args[0] );
						\WP_CLI::warning( $message );
				}
			}
		}
	}

	/**
	 * Perform actions on a particular theme by specifying the theme slug.
	 *
	 * ## OPTIONS
	 *
	 * <action>
	 * : The action to perform. Available actions are:
	 * - "add" will add the specified theme slug to the composer.json file
	 * - "remove" will remove the specified theme from the composer.json file
	 *
	 * <theme|zip|url>
	 * : Theme slug. When using the theme slug to specify the theme, by default only a theme available on wordpress.org will be added
	 *
	 * [--file]
	 * : Path to save the composer.json file
	 *
	 * [--latest]
	 * : Add current version of theme installed or specify to always use the latest version from whatever repo the plugin is coming from.
	 *
	 * [--version]
	 * : Use the specified the version. This only applies when using the "add" action.
	 *
	 * [--dev]
	 * : Add or remove the theme as a dev dependency
	 *
	 * [--installer-paths]
	 * : Set the WordPress plugins and themes installer path
	 *
	 * [--ip]
	 * : Set the WordPress plugins and themes installer path (shorter alias for "installer-paths")
	 *
	 * [--activate]
	 * : Activate the plugins defined in composer.json (only applies to the "add" command)
	 *
	 * @param array $args Positional arguments passed to the command
	 * @param array $assoc_args Key based arguments passed to the command
	 */
	public function theme($args, $assoc_args = array())
	{
		if ( isset( $args[1] ) ) {
			$theme_slug = $args[1];

			if (isset($assoc_args['installer-path']) || isset($assoc_args['ip'])) {
				if (isset($assoc_args['installer-path'])) {
					$installer_path = $assoc_args['installer-path'];
				} else {
					$installer_path = $assoc_args['ip'];
				}
				$this->composer->setInstallerPath( $installer_path );
			}

			if ( isset( $args[0] ) ) {
				switch ( $args[0] ) {
					case "add":
						$this->addTheme( $theme_slug, $args, $assoc_args );
						break;
					case "remove":
						$this->removeTheme( $theme_slug, $args, $assoc_args );
						break;
					default:
						$message = sprintf( '%s is not a valid action', $args[0] );
						\WP_CLI::warning( $message );
				}
			}
		}
	}

	/**
	 * Add installed plugins and themes to composer.json.
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
	 * [--installer-paths]
	 * : Set the WordPress plugins and themes installer path
	 *
	 * [--ip]
	 * : Set the WordPress plugins and themes installer path (shorter alias for "installer-paths")
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @param array $args Positional arguments passed to command
	 * @param array $assoc_args Key based arguments passed to command
	 * @return true|false True if able to write to a composer.json file, false if unable to write to the file for some reason
	 */
	public function addAllDependencies($args, array $assoc_args = array())
	{
		if (isset($assoc_args['installer-path']) || isset($assoc_args['ip'])) {
			if (isset($assoc_args['installer-path'])) {
				$installer_path = $assoc_args['installer-path'];
			} else {
				$installer_path = $assoc_args['ip'];
			}
			$this->composer->setInstallerPath( $installer_path );
		}
		
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
		$plugins_added = array();

		if (!empty($composer) && is_array($plugins_found)) {
			foreach ($plugins_found as $plugin) {
				if ($all === false) {
					if ($this->composer->isPluginAvailable($plugin['name'])) {
						$plugins_added[] = $plugin['name'];
						if (isset($latest_version)) {
							$plugin_version = $latest_version;
						} else {
							$plugin_version = $plugin['version'];
						}
						\WP_CLI::line(sprintf('Adding plugin %s. Using version %s',$plugin['name'], $plugin_version));
						$this->composer->addPluginDependency($plugin['name'], $plugin_version);
					}
				} else {
					$plugins_added[] = $plugin['name'];
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

				$plugins_added = implode(', ', $plugins_added);

				if ($success === true) {
					\WP_CLI::success(sprintf('Saved %s plugin dependencies to %s', $plugins_added, $saved_file));
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
	 * @param array $args Positional arguments passed to command
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
		\WP_CLI::run_command(array('theme', 'list'), array('format'=>'json'));
		$themes_found = json_decode(ob_get_clean(), true);
		$themes_added = array();

		if (!empty($composer) && is_array($themes_found)) {
			foreach ($themes_found as $theme) {
				if ($all === false) {
					if ($this->composer->isThemeAvailable($theme['name'])) {
						$themes_added[] = $theme['name'];
						if (isset($latest_version)) {
							$theme_version = $latest_version;
						} else {
							$theme_version = $theme['version'];
						}
						\WP_CLI::line(sprintf('Adding theme %s. Using version %s',$theme['name'], $theme_version));
						$this->composer->addThemeDependency($theme['name'], $theme_version);
					}
				} else {
					$themes_added[] = $theme['name'];
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

				$themes_added = implode(', ', $themes_added);

				if ($success === true) {
					\WP_CLI::success(sprintf('Saved %s theme dependencies to %s', $themes_added, $saved_file));
					return true;
				}
			} catch (\Exception $e) {
				\WP_CLI::warning($e->getMessage());
				return false;
			}
		}
	}

	/**
	 * Add the specified plugin to the composer.json file.
	 *
	 * @param string $plugin_slug Plugin slug or path to download the plugin from.
	 * @param array $args Positional arguments passed to the command
	 * @param array $assoc_args Key based arguments passed to command
	 *
	 * @return bool
	 */
	private function addPlugin(string $plugin_slug, $args, $assoc_args)
	{
		$file = '';
		$plugin_version = '*';
		$dev = false;

		if (isset($assoc_args['file'])) {
			$file = $assoc_args['file'];
		}

		if (isset($assoc_args['latest'])) {
			$plugin_version = '*';
		}

		if (isset($assoc_args['version'])) {
			$plugin_version = $assoc_args['version'];
		}

		if (isset($assoc_args['dev'])) {
			$dev = true;
		}

		$composer = json_decode(json_encode($this->composer->readComposerFile($file)), true);

		if ( !empty( $composer ) ) {
			\WP_CLI::line( sprintf( 'Adding plugin %s. Using version %s', $plugin_slug, $plugin_version ) );

			if ($dev) {
				$this->composer->addDevPluginDependency( $plugin_slug, $plugin_version );
			} else {
				$this->composer->addPluginDependency( $plugin_slug, $plugin_version );
			}

			try {
				$success = $this->composer->saveComposer( $file );

				$saved_file = $file;
				if ( empty( $saved_file ) ) {
					$saved_file = $this->composer->composer_file_location;
				}

				if ($dev) {
					$message = sprintf( 'Saved %s plugin a dev dependency to %s', $plugin_slug, $saved_file );
				} else {
					$message = sprintf( 'Saved %s plugin a dependency to %s', $plugin_slug, $saved_file );
				}

				if ( $success === true ) {
					\WP_CLI::success( $message );

					return true;
				}
			} catch ( \Exception $e ) {
				\WP_CLI::warning( $e->getMessage() );

				return false;
			}
		}
	}

	/**
	 * Add the specified theme to the composer.json file.
	 *
	 * @param string $theme_slug Theme slug or path to download the theme from.
	 * @param array $args Positional arguments passed to the command
	 * @param array $assoc_args Key based arguments passed to command
	 *
	 * @return bool
	 */
	private function addTheme(string $theme_slug, $args, $assoc_args)
	{
		$file = '';
		$theme_version = '*';
		$dev = false;

		if (isset($assoc_args['file'])) {
			$file = $assoc_args['file'];
		}

		if (isset($assoc_args['latest'])) {
			$theme_version = '*';
		}

		if (isset($assoc_args['version'])) {
			$theme_version = $assoc_args['version'];
		}

		if (isset($assoc_args['dev'])) {
			$dev = true;
		}

		$composer = json_decode(json_encode($this->composer->readComposerFile($file)), true);

		if ( !empty( $composer ) ) {
			\WP_CLI::line( sprintf( 'Adding theme %s. Using version %s', $theme_slug, $theme_version ) );
			if ($dev) {
				$this->composer->addDevThemeDependency( $theme_slug, $theme_version );
			} else {
				$this->composer->addThemeDependency( $theme_slug, $theme_version );
			}

			try {
				$success = $this->composer->saveComposer( $file );

				$saved_file = $file;
				if ( empty( $saved_file ) ) {
					$saved_file = $this->composer->composer_file_location;
				}

				if ( $success === true ) {
					if ($dev) {
						$message = sprintf( 'Saved %s theme a dev dependency to %s', $theme_slug, $saved_file );
					} else {
						$message = sprintf( 'Saved %s theme a dependency to %s', $theme_slug, $saved_file );
					}

					\WP_CLI::success( $message );

					return true;
				}
			} catch ( \Exception $e ) {
				\WP_CLI::warning( $e->getMessage() );

				return false;
			}
		}
	}

	/**
	 * Remove the specified plugin from the composer.json file.
	 *
	 * @param string $plugin_slug Plugin slug
	 * @param array $args Positional arguments passed to the command
	 * @param array $assoc_args Key based arguments passed to command
	 *
	 * @return bool
	 */
	private function removePlugin(string $plugin_slug, $args, $assoc_args)
	{
		$file = '';

		if (isset($assoc_args['file'])) {
			$file = $assoc_args['file'];
		}

		$composer = json_decode(json_encode($this->composer->readComposerFile($file)), true);

		if ( !empty( $composer ) ) {
			\WP_CLI::line( sprintf( 'Removing plugin %s', $plugin_slug) );
			$this->composer->removePluginDependency( $plugin_slug );

			try {
				$success = $this->composer->saveComposer( $file );
				$saved_file = $file;
				if ( empty( $saved_file ) ) {
					$saved_file = $this->composer->composer_file_location;
				}

				if ( $success === true ) {
					\WP_CLI::success( sprintf( 'Removed %s plugin dependency from %s', $plugin_slug, $saved_file ) );

					return true;
				}
			} catch ( \Exception $e ) {
				\WP_CLI::warning( $e->getMessage() );

				return false;
			}
		}
	}

	/**
	 * Remove the specified theme from the composer.json file.
	 *
	 * @param string $plugin_slug Theme slug
	 * @param array $args Positional arguments passed to the command
	 * @param array $assoc_args Key based arguments passed to command
	 *
	 * @return bool
	 */
	private function removeTheme(string $theme_slug, $args, $assoc_args)
	{
		$file = '';

		if (isset($assoc_args['file'])) {
			$file = $assoc_args['file'];
		}

		$composer = json_decode(json_encode($this->composer->readComposerFile($file)), true);

		if ( !empty( $composer ) ) {
			\WP_CLI::line( sprintf( 'Removing theme %s', $theme_slug) );
			$this->composer->removeThemeDependency( $theme_slug );

			try {
				$success = $this->composer->saveComposer( $file );
				$saved_file = $file;
				if ( empty( $saved_file ) ) {
					$saved_file = $this->composer->composer_file_location;
				}

				if ( $success === true ) {
					\WP_CLI::success( sprintf( 'Removed %s theme dependency from %s', $theme_slug, $saved_file ) );

					return true;
				}
			} catch ( \Exception $e ) {
				\WP_CLI::warning( $e->getMessage() );

				return false;
			}
		}
	}

	/**
	 * Bulk uninstall plugins
	 *
	 * @param array $args Positional arguments passed to the command
	 * @param array $assoc_args Key based arguments passed to command
	 *
	 */
	private function uninstallPlugins($args, $assoc_args)
	{
		$file = '';
		$type_of_plugin = 'require';
		$dev = false;

		if (isset($assoc_args['file'])) {
			$file = $assoc_args['file'];
		}

		if (isset($assoc_args['dev'])) {
			$type_of_plugin = 'require-dev';
			$dev = true;
		}

		$composer = json_decode(json_encode($this->composer->readComposerFile($file)), true);
		if (isset($composer[$type_of_plugin])) {
			$found_plugins = array();
			foreach ($composer[$type_of_plugin] as $plugin_name=>$version) {
				if ($this->composer->isWordPressPlugin($plugin_name)) {
					$plugin_slug = $this->composer->removeNamespace($plugin_name, 'plugin');
					$found_plugins[] = $plugin_slug;
					\WP_CLI::run_command(array('plugin', 'deactivate', $plugin_slug));
					\WP_CLI::run_command(array('plugin', 'uninstall', $plugin_slug));

					if ($dev) {
						$this->composer->removeDevPluginDependency( $plugin_slug );
					} else {
						$this->composer->removePluginDependency( $plugin_slug );
					}
				}
			}

			if (!empty($found_plugins)) {
				try {
					$success = $this->composer->saveComposer( $file );
					$saved_file = $file;
					if ( empty( $saved_file ) ) {
						$saved_file = $this->composer->composer_file_location;
					}

					$uninstalled_plugins = implode(', ', $found_plugins);

					if ( $success === true ) {
						if ($dev) {
							$message = sprintf( 'Uninstalled plugin(s) %s and removed dev plugin dependency from %s', $uninstalled_plugins, $saved_file );
						} else {
							$message = sprintf( 'Uninstalled plugin(s) %s and removed plugin dependency from %s', $uninstalled_plugins, $saved_file );
						}

						\WP_CLI::success( $message );

						return true;
					}
				} catch ( \Exception $e ) {
					\WP_CLI::warning( $e->getMessage() );

					return false;
				}
			}
		}
	}

	/**
	 * Bulk uninstall themes
	 *
	 * @param array $args Positional arguments passed to the command
	 * @param array $assoc_args Key based arguments passed to command
	 *
	 */
	private function uninstallThemes($args, $assoc_args)
	{
		$file = '';
		$type_of_theme = 'require';
		$dev = false;

		if (isset($assoc_args['file'])) {
			$file = $assoc_args['file'];
		}

		if (isset($assoc_args['dev'])) {
			$type_of_theme = 'require-dev';
			$dev = true;
		}

		$composer = json_decode(json_encode($this->composer->readComposerFile($file)), true);
		if (isset($composer[$type_of_theme])) {
			$found_themes = array();
			foreach ($composer[$type_of_theme] as $theme_name=>$version) {
				if ($this->composer->isWordPressTheme($theme_name)) {
					$theme_slug = $this->composer->removeNamespace($theme_name, 'theme');
					$found_themes[] = $theme_slug;
					\WP_CLI::run_command(array('theme', 'uninstall', $theme_slug));

					if ($dev) {
						$this->composer->removeDevThemeDependency( $theme_slug );
					} else {
						$this->composer->removeThemeDependency( $theme_slug );
					}
				}
			}

			if (!empty($found_themes)) {
				try {
					$success = $this->composer->saveComposer( $file );
					$saved_file = $file;
					if ( empty( $saved_file ) ) {
						$saved_file = $this->composer->composer_file_location;
					}

					$uninstalled_themes = implode(', ', $found_themes);

					if ( $success === true ) {
						if ($dev) {
							$message = sprintf( 'Uninstalled theme %s and removed dev theme dependency from %s', $uninstalled_themes, $saved_file );
						} else {
							$message = sprintf( 'Uninstalled theme %s and removed theme dependency from %s', $uninstalled_themes, $saved_file );
						}

						\WP_CLI::success( $message );

						return true;
					}
				} catch ( \Exception $e ) {
					\WP_CLI::warning( $e->getMessage() );

					return false;
				}
			}

		}
	}

	/**
	 * Bulk install plugins
	 *
	 * @param array $args Positional arguments passed to the command
	 * @param array $assoc_args Key based arguments passed to command
	 *
	 */
	private function installPlugins($args, $assoc_args)
	{
		$file = '';
		$type_of_plugin = 'require';
		$dev = false;

		if (isset($assoc_args['file'])) {
			$file = $assoc_args['file'];
		}

		if (isset($assoc_args['dev'])) {
			$type_of_plugin = 'require-dev';
			$dev = true;
		}

		$composer = json_decode(json_encode($this->composer->readComposerFile($file)), true);
		if (isset($composer[$type_of_plugin])) {
			$found_plugins = array();
			foreach ($composer[$type_of_plugin] as $plugin_name=>$version) {
				if ($this->composer->isWordPressPlugin($plugin_name)) {
					$plugin_slug = $this->composer->removeNamespace($plugin_name, 'plugin');
					$found_plugins[] = $plugin_slug;

					// since wordpress.org doesn't label plugins by these words, these are all alias to download the latest version
					$bad_versions_of_plugins = ['*','dev-trunk','dev-master','master','dev'];
					if (in_array($version, $bad_versions_of_plugins)) {
						\WP_CLI::run_command(array('plugin', 'install', $plugin_slug));
					} else {
						\WP_CLI::run_command(array('plugin', 'install', $plugin_slug), array('version'=>$version));
					}

				}
			}

			if (!empty($found_plugins)) {

				$saved_file = $file;
				if ( empty( $saved_file ) ) {
					$saved_file = $this->composer->composer_file_location;
				}

				$installed_plugins = implode(', ', $found_plugins);

				if ($dev) {
					$message = sprintf( 'Successfully installed required dev plugins %s found in %s', $installed_plugins, $saved_file );
				} else {
					$message = sprintf( 'Successfully installed required plugins %s found in %s', $installed_plugins, $saved_file );
				}

				\WP_CLI::success( $message );
			}

		}
	}

	/**
	 * Bulk install themes
	 *
	 * @param array $args Positional arguments passed to the command
	 * @param array $assoc_args Key based arguments passed to command
	 *
	 */
	private function installThemes($args, $assoc_args)
	{
		$file = '';
		$type_of_theme = 'require';
		$dev = false;

		if (isset($assoc_args['file'])) {
			$file = $assoc_args['file'];
		}

		if (isset($assoc_args['dev'])) {
			$type_of_theme = 'require-dev';
			$dev = true;
		}

		$composer = json_decode(json_encode($this->composer->readComposerFile($file)), true);
		if (isset($composer[$type_of_theme])) {
			$found_themes = array();
			foreach ($composer[$type_of_theme] as $theme_name=>$version) {
				if ($this->composer->isWordPressTheme($theme_name)) {
					$theme_slug = $this->composer->removeNamespace($theme_name, 'theme');
					$found_themes[] = $theme_slug;

					// since wordpress.org doesn't label themes by these words, these are all alias to download the latest version
					$bad_versions_of_themes = ['*','dev-trunk','dev-master','master','dev'];
					if (in_array($version, $bad_versions_of_themes)) {
						\WP_CLI::run_command(array('theme', 'install', $theme_slug));
					} else {
						\WP_CLI::run_command(array('theme', 'install', $theme_slug), array('version'=>$version));
					}

				}
			}

			if (!empty($found_themes)) {

				$saved_file = $file;
				if ( empty( $saved_file ) ) {
					$saved_file = $this->composer->composer_file_location;
				}
				$installed_themes = implode(', ', $found_themes);
				if ($dev) {
					$message = sprintf( 'Successfully installed required dev themes %s found in %s', $installed_themes, $saved_file );
				} else {
					$message = sprintf( 'Successfully installed required themes %s found in %s', $installed_themes, $saved_file );
				}

				\WP_CLI::success( $message );
			}

		}
	}

	/**
	 * Set the WordPress plugin and theme installer paths
	 */
	private function setInstallerPath()
	{
		ob_start();
		\WP_CLI::run_command(array('plugin', 'path'));
		$installer_path = basename(dirname(ob_get_clean()), 2);
		$this->composer->setInstallerPath($installer_path);
	}

}