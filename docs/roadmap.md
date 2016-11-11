---
    title: Roadmap - WP Composer
---
#Roadmap
Upcoming list of things that would make this project event better:
* Add support for paid and non-wordpress.org repo pluginss
* Create a WordPress plugin for the WordPress dashboard to manage the package settings for better control
* Add support for child-themes, so child themes can list their parent themes in a composer.json file or in the style.css. This plugin will read that dependency and install that parent theme in the correct directory
* Add support for plugin add-ons, so add-on plugins can list the plugin they need in order to function. E.g. An Advanced Custom Fields add-on can list Adnaced Custom Fields as the plugin they need and this plugin/package will install Advanced Custom Fields in the plugin directory