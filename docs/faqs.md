---
    title: Frequently Asked Questions - WP Composer
---
#Frequently Asked Questions
that no one has asked yet.

* Why did I create this?
    * I needed to migrate a bunch of individual WordPress sites into a multisite installation. I created a script to get all of the WordPress plugins installed on each site and add them all to a composer.json file to not have to have all of the third-party plugin code in the repo.
* Isn't there already a [Composer plugin](https://wordpress.org/plugins/composer/) for WordPress?
    * Yes, there is but it hasn't been updated in three  years and does not work without changing the name of the main plugin file. Also, it doesn't have as many options as this one.
* What is a good use case for this?
    * When you need to manage the plugin and theme dependencies of your WordPress site but you don't want a bunch of third-party code in your repo, which increases its size. Also, managing the dependencies of a Multisite.
* Does this plugin manage premium plugins?
    * This plugin currently does **not** support premium plugins and themes, as well as plugins and themes not hosted on WordPress.org. This is a limitation I would like to fix at some point. In the meantime, you can manage your premium plugins in other ways such as submodules, subtrees, and plenty of other ways.

##Frequently Asked Questions that someone has asked
* I see this is a wrapper for WPackagist. I don't mind but I don't see the huge benefit either other than saving you from having to type "wpackagist-plugin/slug", or am I missing something?
    *   In essence, this is very similar to just using composer and running the command. The biggest benefit is just using WP-CLI commands and using the command of the package to automatically add your plugins to the composer.json file.  
        Also it [hooks into the command](https://rxnlabs.github.io/wp-composer/hooks.html "Hooks for WP-Composer") wp plugin install plugin-name and will automatically add that plugin to the composer.json file. It also hooks into wp plugin uninstall plugin-name command to remove the plugin you uninstalled from your composer.json file. This also applies to the wp theme commands. It also just works well for bulk installing your plugins using wp-cli, though you could do this WP-CLI as well.