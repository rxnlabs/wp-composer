---
    title: Install Command - WP Composer
---
##Install Subcommands
Install the dependencies of third-party themes and plugins.
> **Note** Some commands will generate a composer.json file if one does not currently exist

###plugins

Install the dependencies of third-party plugins.

---
    wp composer install plugins
---
#####Example

Let's say that your site has the plugin bu-versions and that plugin has a composer.json file. You can install that plugin's dependencies, if those dependencies are not currently installed.

---
    # Install the dependencies of all of the currently installed plugins
    wp composer install plugins
---

> **Note** This does *not* currently install dependency plugins needed by other plugins (e.g. an running this command on an Advanced Custom Fields Add-On will not install the Advanced Custom Fields plugin onto a site. This is something I will be working on in the future).

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

> **Note** Note</strong> This does **not** currently install a parent theme of a Child-theme (e.g. an running this command on a child-theme of twentysixteen will not install the twentysixteen theme onto a site. This is something I will be working on in the future).

####Options
No options
