---
    title: Install Command - WP Composer
---
##Install Command
Install the dependencies of third-party themes and plugins.
> **Note** Some commands will generate a composer.json file if one does not currently exist

###install

Add all plugins and themes to the composer.json file

---
    wp composer install
---
#####Example

Let's say that your site has the plugin example-plugin and that plugin has a composer.json file. You can install that plugin's dependencies, if those dependencies are not currently installed.

---
    # Install the dependencies of all of the currently installed plugins and themes
    wp composer install
---

#####Result
Will run the composer install command for the plugin.

####Options
No options
