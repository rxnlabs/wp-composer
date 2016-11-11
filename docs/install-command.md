---
    title: Install Command - WP Composer
---
##Install Command
Install the dependencies of third-party themes and plugins.
> **Note** Some commands will generate a composer.json file if one does not currently exist

###plugins

Install the dependencies of third-party plugins.

---
    wp composer install plugins
---
#####Example

Let's say that your site has the plugin example-plugin and that plugin has a composer.json file. You can install that plugin's dependencies, if those dependencies are not currently installed.

---
    # Install the dependencies of all of the currently installed plugins
    wp composer install plugins
---

#####Result
Will run the composer install command for the plugin.

###themes

Install the dependencies of third-party themes.

---
    wp composer install themes
---
#####Example

Let's say that your site has the theme example-theme and that plugin has a composer.json file. You can install that plugin's dependencies, if those dependencies are not currently installed.

---
    # Install the dependencies of all of the currently installed themes
    wp composer install themes
---

#####Result
Will run the composer install command for the theme.

####Options
No options
