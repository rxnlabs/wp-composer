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