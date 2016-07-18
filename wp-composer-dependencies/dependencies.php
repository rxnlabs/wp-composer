<?php
/**
 * Reads from and writes dependencies to composer.json.
 *
 * @package WP Composer Dependencies
 * @subpackage Read and update composer
 * @author De'Yonté W.<dw19412@gmail.com>
 */
namespace rxnlabs;

class Dependencies
{
	/**
	 * Store the namespace for the dpendencies namespace from http://wpackagist.org/
	 * @var array
	 */
	public $wpackagist_namepsace = array('plugin'=>'wpackagist-plugin', 'theme'=>'wpackagist-theme');

	/**
	 * Execute WordPress hooks.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	public function hooks()
	{

	}

	/**
	 * Add dependencies to composer.json file
	 *
	 * Main command that adds and tracks dependencies.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	public function addDependencies()
	{

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

	    if (empty($composer_file)) {
		    $composer_file = $_SERVER['DOCUMENT_ROOT']. '/composer.json';
	    }

	    // check to see if composer.json file  exists directly above the current root (e.g. site is using the Bedrock directory structure)
	    if (!file_exists($composer_file)) {
			$composer_file = $_SERVER['DOCUMENT_ROOT']. '/../composer.json';
	    }

	    try {
		   // $parser = new \JsonCollectionParser\Parser();
		    $dependencies = json_decode(file_get_contents($composer_file));

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
     */
    public function addPluginDependency($plugin_name)
    {
		$plugin_namespace = sprintf('%s/%s', $this->wpackagist_namepsace['plugin'], $plugin_name);
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

    }

    /**
     * Add specified theme as a dependency
     *
     * @since 1.0.0
     * @version 1.0.0
     *
     * string $theme_name Name of the theme to add as a dependency (e.g. slug, folder name)
     */
    public function addThemeDependency($theme_name)
    {
	    $theme_namespace = sprintf('%s/%s', $this->wpackagist_namepsace['plugin'], $theme_name);
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

    }

	/**
	 * Add other dependencies to composer.json that aren't WordPress plugins or themes.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @param string $other Composer package or vendor.
	 */
    public function addOtherDependency($other)
    {

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
	 * @param boolean ascending If true, sort by ascending order. If false, sort by descending alphabetical order.
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
}
