<?php
/**
 * Reads from and writes dependencies to composer.json.
 *
 * @package WP Composer Dependencies
 * @subpackage Read and update composer
 * @author De'Yonté W.<dw19412@gmail.com>
 */
namespace rxnlabs;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

class Dependencies
{
	/**
	 * Store the namespace for the dpendencies namespace from http://wpackagist.org/
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @var array
	 */
	public $wp_packagist_namespace = array('plugin'=>'wpackagist-plugin', 'theme'=>'wpackagist-theme');

	/**
	 * Secure URL to the WPackagist site
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @var string
	 */
	public $wp_packagist_repo = 'https://wpackagist.org';

	/**
	 * Unsecure URL to the WPackagist site. Composer repositories should ideally use HTTPS since by default composer is
	 * configured to allow download assets from HTTPS repository.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @var string
	 */
	public $wp_packagist_repo_non_https = 'http://wpackagist.org';

	/**
	 * Array of dependencies in the composer file.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @var string
	 */
	public $composer_dependencies;

	/**
	 * Path to save the composer.json file
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @var string
	 */
	public $composer_save_path;

	/**
	 * Composer file path
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @var string
	 */
	public $composer_file_location;

	/**
	 * WordPress assets install path
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @var string
	 */
	public $assets_install_path;

	/**
	 * Dependencies constructor.
	 * @param string $installer_path Set the default installer path for the WordPress plugins
	 */
	public function __construct(string $installer_path = '') {
		// set the default installer path for the wordpress assets (we can guess that this plugin is two directories below the wp-content folder or the folder that replaced wp-content)
		if (empty($installer_path) && function_exists('plugin_dir_path')) {
			$installer_path = basename(dirname(plugin_dir_path(__DIR__), 2));
		}

		$this->setInstallerPath($installer_path);
	}

	/**
	 * Execute WordPress hooks.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @return void
	 */
	public function hooks()
	{
		//register_deactivation_hook(__FILE__, array($this, 'removeThemeDependency'));
		//register_uninstall_hook(__FILE__, array($this, 'removeThemeDependency'));
	}

	/**
	 * Write dependencies to composer.json file
	 *
	 * Main command that adds and tracks dependencies.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	public function saveComposer(string $composer_file_name = 'composer.json', string $save_path = '')
	{
		// attempt to extract save path from the passed $composer_file_name path
		if (empty($save_path) && !empty($composer_file_name)) {
			$save_path = pathinfo($composer_file_name)['dirname'];
		}

		if (empty($save_path)) {
			if (empty($this->composer_save_path)) {
				// read the default composer.json file to set the default save path
				$this->readComposerFile();
			}
			$save_path = $this->composer_save_path;
		}

		$file_extension = new \SplFileInfo($composer_file_name);
		if ($file_extension !== 'json') {
			$composer_file_name = 'composer.json';
		}

		$this->composer_file_location = sprintf('%s/%s', $save_path, $composer_file_name);

		try {
			/*
			if we're writing to a local file, Flysystem uses relative paths instead of absolute paths
			 to write files, so the composer.json file would be saved to the plugin folder instead of
			the passed save path. So set the $root to '/' to fix this issue.
			@link https://github.com/thephpleague/flysystem/issues/462
			*/
			$adapter = new Local('/');
			$filesystem = new Filesystem($adapter);
		} catch (\LogicException $e) {
			error_log($e->getMessage(), 0);
			return false;
		}

		if (!empty($this->composer_dependencies)) {
			$wp_asset = array();
			$other = array();

			$requires = array('require', 'require-dev');
			$wordpress_types_of_deps = array('plugins', 'themes');
			// loop through composer array and convert each type of dependency to the kind stored in composer.json file
			foreach ($requires as $type_of_require) {
				if (!empty($this->composer_dependencies[$type_of_require])) {
					$composer_dependencies = array();
					foreach ( $wordpress_types_of_deps as $dep ) {
						if ( empty( $this->composer_dependencies[ $type_of_require ][ $dep ] ) ) {
							continue;
							unset( $this->composer_dependencies[ $type_of_require ][ $dep ] );
						} elseif ( isset( $this->composer_dependencies[ $type_of_require ][ $dep ] ) && is_array( $this->composer_dependencies[ $type_of_require ][ $dep ] ) ) {
							foreach ( $this->composer_dependencies[ $type_of_require ][ $dep ] as $plugin_or_theme => $version ) {
								switch ( $dep ) {
									case 'plugins':
										$type = 'plugin';
										break;
									case 'themes':
										$type = 'theme';
										break;
									default:
										$type = 'plugin';
								}
								$plugin_or_theme_namespace = $this->addNameSpace( $plugin_or_theme, $type );
								$wp_asset[ $plugin_or_theme_namespace ] = $version;
							}
							$composer_dependencies = array_merge( $composer_dependencies, $wp_asset );
							unset( $this->composer_dependencies[ $type_of_require ][ $dep ] );
						}
					}

					$this->composer_dependencies[ $type_of_require ] = array_merge( $this->composer_dependencies[ $type_of_require ], $composer_dependencies );

				} else {
					unset($this->composer_dependencies[ $type_of_require ]);
				}
			}

			$json_pretty = new \Camspiers\JsonPretty\JsonPretty;
			$composer_dependencies = stripslashes($json_pretty->prettify($this->composer_dependencies));
			$composer_file = sprintf('%s/%s', $save_path, $composer_file_name);
			$get_real_path = realpath($composer_file);
			// if the $composer_file_name doesn't exist
			if ($get_real_path === false) {
				$composer_file = $this->getAbsolutePathFromRelative($composer_file);
			} else {
				$composer_file = $get_real_path;
			}
			$is_success = $filesystem->put($composer_file, $composer_dependencies);

			return $is_success;
		}


	}

	/**
	 * Read the composer.json file installed.
	 *
	 * Read the composer.json file to check for other dependencies.
	 *
	 * @since 1.0.0
	 * @verion 1.0.0
	 *
	 * @param string $composer_file File path of composer.json file
	 */
	public function readComposerFile($composer_file = '')
	{
		$options = get_option('wp-composer-dependencies');

		$adapter = new Local('/');
		$filesystem = new Filesystem($adapter);

		// if a specific composer.json file wasn't passed in as parameter, check the current directory for a
		if (empty($composer_file)) {
			$composer_file = getcwd().'/composer.json';
		}

		$add_packagist_repo = function(array $dependencies){
			if (isset($dependencies['repositories'])) {
				$is_object_repo = key($dependencies['repositories']);
				if ($is_object_repo !== 0) {
					$dependencies['repositories']['wp-composer'] = ['type'=>'composer','url'=>$this->wp_packagist_repo];
				} else {
					$dependencies['repositories'] = [['type'=>'composer','url'=>$this->wp_packagist_repo]];
				}
			} else {
				$dependencies['repositories'] = [['type'=>'composer','url'=>$this->wp_packagist_repo]];
			}

			return $dependencies;
		};

		$add_installer_paths = function(array $dependencies) {
			$wordpress_path = $this->getInstallerPath();
			$theme_path = $wordpress_path.'/themes/{$name}/';
			$plugin_path = $wordpress_path.'/plugins/{$name}/';
			$mu_plugin_path = $wordpress_path.'/mu-plugins/{$name}/';

			$wordpress_install_paths = [
				$theme_path => ["type:wordpress-theme"],
				$plugin_path => ["type:wordpress-plugin"],
				$mu_plugin_path => ["type:wordpress-muplugin"]
			];

			if (isset($dependencies['extra']['installer-paths'])) {
				$installer_path = $dependencies['extra']['installer-paths'];
			} else {
				$installer_path = $dependencies['extra']['installer-paths'] = array();
			}

			$dependencies['extra']['installer-paths'] = array_merge( $installer_path, $wordpress_install_paths);
			return $dependencies;
		};

		try {
		   // $parser = new \JsonCollectionParser\Parser();
			$get_real_path = realpath($composer_file);
			// if the $composer_file_name doesn't exist
			if ($get_real_path === false) {
				$composer_file = $this->getAbsolutePathFromRelative($composer_file);
			} else {
				$composer_file = $get_real_path;
			}

			if (!$filesystem->has($composer_file)) {
				$composer_file = $this->composer_file_location = '';
				$this->composer_save_path = getcwd();
				$dependencies = [
					'name'=>'wp-composer-dependencies',
					'description' => sprintf('Theme and plugin dependencies for the site %s', get_bloginfo('url')),
					'require'=>[],
					'require-dev'=>[]
				];
				$dependencies = $add_installer_paths($add_packagist_repo($dependencies));
			} else {
				$dependencies = json_decode($filesystem->read($composer_file), true);

				if (isset($dependencies['repositories'])) {
					$repo_added_already = false;
					foreach ($dependencies['repositories'] as &$repo) {
						$is_object_repo = key($dependencies['repositories']);
						if ($is_object_repo !== 0) {
							if ($repo['type'] === 'composer' && $repo['url'] === $this->wp_packagist_repo_non_https) {
								$repo['url'] = $this->wp_packagist_repo;
								$repo_added_already = true;
								break;
							}

							if ($repo['type'] === 'composer' && $repo['url'] === $this->wp_packagist_repo) {
								$repo_added_already = true;
								break;
							}
						} else {
							if ($repo['type'] === 'composer' && $repo['url'] === $this->wp_packagist_repo_non_https) {
								$repo['url'] = $this->wp_packagist_repo;
								$repo_added_already = true;
								break;
							}

							if ($repo['type'] === 'composer' && $repo['url'] === $this->wp_packagist_repo) {
								$repo_added_already = true;
								break;
							}
						}
					}

					if ($repo_added_already === false) {
						$dependencies = $add_installer_paths($add_packagist_repo($dependencies));
					}
				} else {
					$dependencies = $add_installer_paths($add_packagist_repo($dependencies));
				}

				$this->composer_save_path = pathinfo($composer_file)['dirname'];
			}

			$this->composer_dependencies = $dependencies;
			$this->composer_file_location = sprintf('%s/%s', $this->composer_save_path, 'composer.json');

			return $dependencies;
		} catch (Exception $e) {
			return new WP_Error(sprintf('composer.json file does not exist at the path %s', $composer_file));
		}
	}

	/**
	 * Read composer dependencies and extract the ones that WordPress plugins and themes into array
	 *
	 * Since wpackagist plugins and themes need to have the package namespaced in a defined format, we need to extract WordPress plugins and themes into their own array
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @param array $dependencies Composer dependencies (i.e. either the "require" key or the "require-dev" key of the composer file)
	 */
	private function formatComposerDependencies(array $dependencies)
	{
		foreach($dependencies as $dep) {
			$is_wordpress_dependency = false;
		}
	}

	/**
	 * Determine of the plugin is available on wordpress.org
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @param string $plugin_slug WordPress plugin slug
	 * @param string $repo_hosting_service The hosting service where the plugin can be downloaded from (e.g. WordPress.org)
	 */
	public function isPluginAvailable($plugin_slug, $repo_hosting_service = 'wordpress') {
		$object_name = $this->cleanRepoName( $plugin_slug );

		if ($repo_hosting_service === 'wordpress') {
			$url = sprintf('https://api.wordpress.org/plugins/info/1.0/%s.json', $object_name);

			if (isset($url)) {
				$plugin_info = wp_remote_post($url);

				if (!is_wp_error($plugin_info) && wp_remote_retrieve_response_code($plugin_info) === 200) {
					$response = json_decode( wp_remote_retrieve_body( $plugin_info ) );
					if (empty($response)) {
						return false;
					} else {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Determine of the theme is available on wordpress.org
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @param string $theme_slug WordPress theme slug
	 * @param string $repo_hosting_service The hosting service where the theme can be downloaded from (e.g. WordPress.org)
	 *
	 * @return bool True if the theme is available, false if not available
	 */
	public function isThemeAvailable($theme_slug, $repo_hosting_service = 'wordpress')
	{
		$object_name = $this->cleanRepoName($theme_slug);

		if ($repo_hosting_service === 'wordpress') {
			$url = 'https://api.wordpress.org/themes/info/1.1/';

			$request = array(
				'slug' => $theme_slug,
				'fields' => 'screenshot_url'
			);

			$body = array(
				'action'=>'theme_information',
				'request'=>$request
			);

			$theme_info = wp_remote_post($url,array(
				'body'=> $body
			));

			if (!is_wp_error($theme_info) && wp_remote_retrieve_response_code($theme_info) === 200) {
				$response = json_decode(wp_remote_retrieve_body($theme_info));
				if (empty($response)) {
					return false;
				} else {
					return true;
				}

			}
		}

		return false;
	}

	/**
	 * Get current plugins.
	 *
	 * Get the current active and inactive plugins.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @param string $status Get active, inactive, or all installed plugins.
	 */
	public function getPlugins($status = 'active')
	{
		$plugins  = get_plugins();
		if ($status === 'active') {

		} elseif ($status === 'inactive') {

		} elseif ($status === 'all') {

		}
	}

	/**
	 * Get the current themes.
	 *
	 * Get the current active and inactive themes.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @param strng $status Get active, inactive, or all installed themes.
	 */
	public function getThemes($status = 'active')
	{
		$themes = wp_get_themes();
		if ($status === 'active') {

		} elseif ($status === 'inactive') {

		} elseif ($status === 'all') {

		}
	}

	/**
	 * Add the WordPress plugin as a dependency
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @param string $plugin_name Name of the plugin to add as a dependency (e.g. slug, folder name)
	 * @param string $version The plugin version number
	 */
	public function addPluginDependency($plugin_name, $version = '*')
	{
		$this->composer_dependencies['require']['plugins'][$plugin_name] = $version;
	}

	/**
	 * Add the WordPress plugin as a dev dependency
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @param string $plugin_name Name of the plugin to add as a dependency (e.g. slug, folder name)
	 * @param string $version The plugin version number
	 */
	public function addDevPluginDependency($plugin_name, $version = '*')
	{
		$this->composer_dependencies['require-dev']['plugins'][$plugin_name] = $version;
	}

	/**
	 * Remove the WordPress plugin as a dependency
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @param string $plugin_name Name of the plugin to remove as a dependency (e.g. slug, folder name)
	 */
	public function removePluginDependency($plugin_name)
	{
		$namespaced_plugin_name = $this->addNameSpace( $plugin_name, 'plugin' );
		unset($this->composer_dependencies['require'][$namespaced_plugin_name]);
	}

	/**
	 * Remove the WordPress plugin as a dev dependency
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @param string $plugin_name Name of the plugin to remove as a dependency (e.g. slug, folder name)
	 */
	public function removeDevPluginDependency($plugin_name)
	{
		$namespaced_plugin_name = $this->addNameSpace( $plugin_name, 'plugin' );
		unset($this->composer_dependencies['require-dev'][$namespaced_plugin_name]);
	}

	/**
	 * Add specified theme as a dependency
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @param string $theme_name Name of the theme to add as a dependency (e.g. slug, folder name)
	 */
	public function addThemeDependency($theme_name, $version =  "*")
	{
		$this->composer_dependencies['require']['themes'][$theme_name] = $version;
	}

	/**
	 * Add specified theme as a dev dependency
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @param string $theme_name Name of the theme to add as a dependency (e.g. slug, folder name)
	 */
	public function addDevThemeDependency($theme_name, $version =  "*")
	{
		$this->composer_dependencies['require-dev']['themes'][$theme_name] = $version;
	}

	/**
	 * Remove the WordPress theme  as a dependency
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @param string $theme_name Name of the theme  to remove as a dependency
	 */
	public function removeThemeDependency($theme_name)
	{
		$namespaced_theme_name = $this->addNameSpace( $theme_name, 'theme' );
		unset($this->composer_dependencies['require'][$namespaced_theme_name]);

		return $this->composer_dependencies;
	}

	/**
	 * Remove the WordPress theme  as a dev dependency
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @param string $theme_name Name of the theme  to remove as a dependency
	 */
	public function removeDevThemeDependency($theme_name)
	{
		$namespaced_theme_name = $this->addNameSpace( $theme_name, 'theme' );
		unset($this->composer_dependencies['require-dev'][$namespaced_theme_name]);

		return $this->composer_dependencies;
	}

	/**
	 * Add other dependencies to composer.json that aren't WordPress plugins or themes.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @param string $other Composer package or vendor.
	 */
	public function addOtherDependency($other, $version)
	{
		$this->composer_dependencies['other'][$other] = $version;
	}

	/**
	 * Load WordPress core as a dependency.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @param string $version WordPress version
	 */
	public function addWordPressDependency($version = 'latest')
	{

	}

	/**
	 * Add inactive plugins as dependencies
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	public function addInactivePlugins()
	{

	}

	/**
	 *  Add inactive themes  as dependencies
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	public function addInactiveThemes()
	{

	}

	/**
	 * Sort dependencies in alphabetical order.
	 *
	 * Sort dependencies either by ascending alphabetical order or descending alphabetical order.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @param boolean Sort Ascending If true, sort by ascending order. If false, sort by descending alphabetical order.
	 */
	public function sortDependencies($ascending = true)
	{

	}

	/**
	 * Remove forward slashes and backslashes from the theme or plugin slug.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @param string $object_name Slug of the theme or plugin
	 * @return string Plugin or theme slug with slashes stripped from the name.
	 */
	public function cleanRepoName($object_name)
	{
		if (strpos($object_name, '/') !== false) {
			list($vendor, $object_name) = explode('/', $object_name);
		}

		return stripcslashes($object_name);
	}

	/**
	 * Add the plugin and theme wpackagist namespace to the plugin and theme name
	 *
	 * @param string $plugin_or_theme_name tTeme or plugin slug name/folder
	 * @param string $theme_or_plugin Is this a plugin or theme. Valid values are "plugin", and  "theme"
	 * @return string Plugin with wpackagist namespace or theme with wpackagist namespace
	 */
	public function addNameSpace($plugin_or_theme_name, $theme_or_plugin = 'plugin')
	{
		switch($theme_or_plugin) {
			case 'plugin': $namespace = sprintf('%s/%s', $this->wp_packagist_namespace['plugin'], $plugin_or_theme_name);
				break;
			case 'theme': $namespace = sprintf('%s/%s', $this->wp_packagist_namespace['theme'], $plugin_or_theme_name);
				break;
			default: $namespace = $plugin_or_theme_name;
		}

		return $namespace;
	}

	/**
	 * Remove the plugin and theme wpackagist namespace from the plugin and theme name
	 *
	 * @param string $plugin_or_theme_name Theme or plugin slug name/folder
	 * @param string $theme_or_plugin Is this a plugin or theme. Valid values are "plugin", and  "theme"
	 *
	 * @return string Theme or plugin name without the wpackagist namespace
	 */
	public function removeNamespace($plugin_or_theme_name, $theme_or_plugin = 'plugin')
	{
		$count_limit = 1;
		switch($theme_or_plugin) {
			case 'plugin': $namespace = str_replace($this->wp_packagist_namespace['plugin'].'/', '', $plugin_or_theme_name, $count_limit);
				break;
			case 'theme': $namespace = str_replace( $this->wp_packagist_namespace['theme'].'/', '', $plugin_or_theme_name, $count_limit);
				break;
			default: $namespace = $plugin_or_theme_name;
		}
		return $namespace;
	}

	/**
	 * Check if a package listed in composer.json is a WordPress theme or plugin
	 *
	 * @param string $plugin_or_theme_name Theme or plugin slug name/folder
	 *
	 * @return bool True if package is a WordPress plugin or theme. False, if not.
	 */
	public function isWordPressPackage($plugin_or_theme_name)
	{
		foreach ($this->wp_packagist_namespace as $asset) {
			if (strpos($plugin_or_theme_name, $asset) !== false) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if a package listed in composer.json is a WordPress plugin based on it's key
	 *
	 * @param string $plugin_slug Plugin slug name/folder
	 *
	 * @return bool True if package is a WordPress plugin. False, if not.
	 */
	public function isWordPressPlugin($plugin_slug)
	{
		if (strpos($plugin_slug, $this->wp_packagist_namespace['plugin']) !== false) {

			return true;
		}

		return false;
	}

	/**
	 * Check if a package listed in composer.json is a WordPress theme based on it's key
	 *
	 * @param string $theme_slug Theme slug name/folder
	 *
	 * @return bool True if package is a WordPress theme. False, if not.
	 */
	public function isWordPressTheme($theme_slug)
	{
		if (strpos($theme_slug, $this->wp_packagist_namespace['theme']) !== false) {
			return true;
		}

		return false;
	}

	/**
	 * Convert an absolute path to a relative path.
	 *
	 * Convert an absolute path to a relative path so we can save the composer.json file in the correct
	 * directory instead of getting saved to the plugin folder.
	 *
	 * @link http://stackoverflow.com/questions/2637945/getting-relative-path-from-absolute-path-in-php#answer-2638272
	 * @param $from_path
	 * @param $to_path
	 * @return string
	 */
	protected function getRelativePath($from_path, $to_path)
	{
		// some compatibility fixes for Windows paths
		$from_path = is_dir($from_path) ? rtrim($from_path, '\/') . '/' : $from_path;
		$to_path   = is_dir($to_path)   ? rtrim($to_path, '\/') . '/'   : $to_path;
		$from_path = str_replace('\\', '/', $from_path);
		$to_path   = str_replace('\\', '/', $to_path);

		$from_path     = explode('/', $from_path);
		$to_path       = explode('/', $to_path);
		$relPath  = $to_path;

		foreach($from_path as $depth => $dir) {
			// find first non-matching dir
			if($dir === $to_path[$depth]) {
				// ignore this directory
				array_shift($relPath);
			} else {
				// get number of remaining dirs to $from
				$remaining = count($from_path) - $depth;
				if($remaining > 1) {
					// add traversals up to first matching dir
					$padLength = (count($relPath) + $remaining - 1) * -1;
					$relPath = array_pad($relPath, $padLength, '..');
					break;
				} else {
					$relPath[0] = './' . $relPath[0];
				}
			}
		}
		return implode('/', $relPath);
	}

	/**
	 * Convert relative directory path to absolute path.
	 *
	 * Needed since the Flysystem library doesn't create directories when we pass it relative paths.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 * @link  http://proger.i-forge.net/%D0%9C%D0%BE%D0%B8%20%D0%BF%D1%80%D0%BE%D0%B3%D0%B8/%D0%92%D0%B5%D0%B1/Real%20realpath.html
	 * @param string $path Relative path to use
	 * @param string|null $cwd Current working directory
	 * @return string Absolute path to file
	 */
	public function getAbsolutePathFromRelative($path, $cwd = null)
	{
		$path = self::expandLeaveLinks($path, $cwd);

		if (function_exists('readlink')) {  // prior to PHP 5.3 it only works for *nix.
			while (is_link($path) and ($target = readlink($path)) !== false) { $path = $target; }
		}

		return $path;
	}


	public function expandLeaveLinks($path, $cwd = null)
	{
		if (!is_scalar($path) and $path !== null) { return; }

		$cwd === null and $cwd = getcwd();
		$cwd = static::pathize($cwd);

		$path = strtr($path, DIRECTORY_SEPARATOR === '\\' ? '/' : '\\', DIRECTORY_SEPARATOR);
		$firstIsSlash = (isset($path[0]) and strpbrk($path[0], '\\/'));

		if ($path === '' or (!$firstIsSlash and isset($path[1]) and $path[1] !== ':')) {
			$path = $cwd.DIRECTORY_SEPARATOR.$path;
		} elseif ($firstIsSlash and isset($cwd[1]) and $cwd[1] === ':') {
			// when a drive is specified in CWD then \ or / refers to that drive's root.
			$path = substr($cwd, 0, 2).$path;
		}

		if ($path !== '' and ($path[0] === DIRECTORY_SEPARATOR or (isset($path[1]) and $path[1] === ':'))) {
			list($prefix, $path) = explode(DIRECTORY_SEPARATOR, $path, 2);
			$prefix .= DIRECTORY_SEPARATOR;
		} else {
			$prefix = '';
		}

		$expanded = array();
		foreach (explode(DIRECTORY_SEPARATOR, $path) as $dir) {
			if ($dir === '..') {
				array_pop($expanded);
			} elseif ($dir !== '' and $dir !== '.') {
				$expanded[] = $dir;
			}
		}

		return $prefix.join(DIRECTORY_SEPARATOR, $expanded);
	}

	/**
	 * @link https://github.com/ProgerXP/HTMLki/blob/master/start.php#L82
	 * @param $path
	 * @return string
	 */
	static function pathize($path) {
		return rtrim(str_replace('\\', '/', $path), '/').'/';
	}

	/**
	 * Set the path to install WordPress plugins and themes into
	 *
	 * Not all WordPress websites use the default WordPress directory structure. Some sites don't use wp-content to store their assets (e.g. Bedrock, https://github.com/roots/bedrock), some use subdirectories for their wordpress installs but manage composer assets in an ancestor directory. Allow thiose sites to set their own path to install their WordPress assets.
	 * @param string $path Path to install the WordPres composer packages (i.e. themes and plugins)
	 *
	 * @return void
	 */
	public function setInstallerPath($path = 'wp-content')
	{
		if (empty($path)) {
			$path = 'wp-content';
		}

		$this->assets_install_path = $path;
	}

	/**
	 * Get the path to install WordPress plugins and themes into
	 *
	 * @see \rxnlabs\Dependencies\setInstallPath For why we need this
	 */
	public function getInstallerPath()
	{
		return $this->assets_install_path;
	}
}
