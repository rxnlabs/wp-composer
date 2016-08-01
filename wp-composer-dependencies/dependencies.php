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
	 * @var array
	 */
	public $wp_packagist_namespace = array('plugin'=>'wpackagist-plugin', 'theme'=>'wpackagist-theme');

	/**
	 * URL to the WPackagist site
	 * @var string
	 */
	public $wp_packagist_repo = 'https://wpackagist.org';

	/**
	 * Array of dependencies in the composer file.
	 * @var string
	 */
	public $composer_dependencies;

	/**
	 * Path to save the composer.json file
	 * @var string
	 */
	public $composer_save_path;

	/**
	 * Composer file path
	 * @var string
	 */
	public $composer_file_location;

	/**
	 * Execute WordPress hooks.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
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

		if (empty($this->composer_dependencies)) {

		}

		if (!empty($this->composer_dependencies)) {
			$plugins = array();
			$themes = array();
			$other = array();
			$composer_dependencies = array();

			if (isset($this->composer_dependencies['require']['plugins']) && is_array($this->composer_dependencies['require']['plugins'])) {
				foreach ($this->composer_dependencies['require']['plugins'] as $plugin => $version) {
					$plugin_namespace = $this->addNameSpace($plugin, 'plugin');
					$plugins[$plugin_namespace] = $version;
				}
				$composer_dependencies = array_merge($composer_dependencies, $plugins);
			}

			unset($this->composer_dependencies['require']['plugins']);

			if (isset($this->composer_dependencies['require']['themes']) && is_array($this->composer_dependencies['require']['themes'])) {
				foreach ($this->composer_dependencies['require']['themes'] as $theme => $version) {
					$theme_namespace = $this->addNameSpace($theme, 'theme');
					$themes[$theme_namespace] = $version;
				}
				$composer_dependencies = array_merge($composer_dependencies, $themes);
			}

			unset($this->composer_dependencies['require']['themes']);
			$this->composer_dependencies['require'] = array_merge($this->composer_dependencies['require'], $composer_dependencies);

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

		if (empty($composer_file)) {
			$composer_file = $_SERVER['DOCUMENT_ROOT']. '/composer.json';
		}

		// check to see if composer.json file  exists directly above the current root (e.g. site is using the Bedrock directory structure)
		if (!file_exists($composer_file)) {
			$composer_file = $_SERVER['DOCUMENT_ROOT'].'/../composer.json';

			if (!file_exists($composer_file)) {
				$composer_file = getcwd().'/composer.json';
			}
		}

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
					'repositories'=>['type'=>'composer','url'=>$this->wp_packagist_repo],
					'require'=>[]
				];
			} else {
				$dependencies = json_decode($filesystem->read($composer_file), true);
				$this->composer_save_path = pathinfo($composer_file)['dirname'];
			}

			$this->composer_dependencies = $dependencies;

			return $dependencies;
		} catch (Exception $e) {
			return new WP_Error(sprintf('composer.json file does not exist at the path %s', $composer_file));
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
						return;
					} else {
						return true;
					}
				}
			}
		}

		return;
	}

	/**
	 * Determine of the theme is available on wordpress.org
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @param string $theme_slug WordPress theme slug
	 * @param string $repo_hosting_service The hosting service where the theme can be downloaded from (e.g. WordPress.org)
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
					return;
				} else {
					return true;
				}

			}
		}

		return;
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
	 * Remove the WordPress plugin as a dependency
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @param string $plugin_name Name of the plugin to remove as a dependency (e.g. slug, folder name)
	 */
	public function removePluginDependency($plugin_name)
	{
		unset($this->composer_dependencies['require']['plugins'][$plugin_name]);
	}

	/**
	 * Add specified theme as a dependency
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * string $theme_name Name of the theme to add as a dependency (e.g. slug, folder name)
	 */
	public function addThemeDependency($theme_name, $version =  "*")
	{
		$this->composer_dependencies['require']['themes'][$theme_name] = $version;
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
		unset($this->composer_dependencies['require']['themes'][$theme_name]);
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
	 * Add the plugin wpackagist namespace to the plugin
	 *
	 * @param $plugin_or_theme_name
	 * @param string $theme_or_plugin
	 * @return string
	 */
	public function addNameSpace($plugin_or_theme_name, $theme_or_plugin = 'plugin')
	{
		switch($theme_or_plugin) {
			case 'plugin': $namespace = sprintf('%s/%s', $this->wp_packagist_namespace['plugin'], $plugin_or_theme_name);
				break;
			case 'theme': $namespace = sprintf('%s/%s', $this->wp_packagist_namespace['theme'], $plugin_or_theme_name);
				break;
			default: $namespace = false;
		}

		return $namespace;
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
}
