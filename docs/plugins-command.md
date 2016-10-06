---
    title: Plugins Command - WP Composer
---
##Plugins Subcommands
Manage dependencies of installed plugins

###add

Add currently installed plugins to composer.json

---
    wp composer plugins add
---

###install
Install plugins listed in composer.json

---
    wp composer plugins install
---

###uninstall

Deactivate and uninstall plugins listed in composer.json

---
    wp composer plugins uninstall
---

###activate

Activate plugins listed in composer.json

---
    wp composer plugins activate
---

###deactivate

Deactivate plugins listed in composer.json

---
    wp composer plugins deactivate
---


####Options
*[--file]*

&nbsp;&nbsp;&nbsp;&nbsp;Path to save the composer.json file

*[--all]*

&nbsp;&nbsp;&nbsp;&nbsp;Add plugins found on wordpress.org and plugins not found on wordpress.org. By default, only plugins  available on wordpress.org will be added to the composer.json file

*[--latest]*

&nbsp;&nbsp;&nbsp;&nbsp;Add current version of plugin installed or specify to always use the latest version from whatever repo the plugin is coming from.

*[--installer-paths]* OR *[--ip]*

&nbsp;&nbsp;&nbsp;&nbsp;Set the WordPress plugins and themes installer path

*[--dev]*

&nbsp;&nbsp;&nbsp;&nbsp;Only apply command to plugins defined as a dev requirement in composer.json

*[--deactivate]*

&nbsp;&nbsp;&nbsp;&nbsp;Deactivate the plugins before uninstalling them. This only applies when using the command `wp plugins uninstall --deactivate`
