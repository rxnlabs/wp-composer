---
    title: Themes Command - WP Composer
---
##Themes Subcommands
Manage dependencies of installed themes

###add

Add currently installed themes to composer.json

---
    wp composer themes add
---

###install
Install themes listed in composer.json

---
    wp composer themes install
---

###uninstall

Deactivate and uninstall themes listed in composer.json

---
    wp composer themes uninstall
---

###activate

Activate themes listed in composer.json

---
    wp composer themes activate
---

###deactivate

Deactivate themes listed in composer.json

---
    wp composer themes deactivate
---


####Options

*[--file]*

&nbsp;&nbsp;&nbsp;&nbsp;Path to save the composer.json file

*[--all]*

&nbsp;&nbsp;&nbsp;&nbsp;Add themes found on wordpress.org and themes not found on wordpress.org. By default, only themes  available on wordpress.org will be added to the composer.json file

*[--latest]*

&nbsp;&nbsp;&nbsp;&nbsp;Add current version of themes installed or specify to always use the latest version from whatever repo the themes is coming from.

*[--installer-paths]* OR *[--ip]*

&nbsp;&nbsp;&nbsp;&nbsp;Set the WordPress plugins and themes installer path

*[--dev]*

&nbsp;&nbsp;&nbsp;&nbsp;Only apply command to themes defined as a dev requirement in composer.json

*[--deactivate]*

&nbsp;&nbsp;&nbsp;&nbsp;Deactivate the themes before uninstalling them. This only applies when using the command `wp themes uninstall --deactivate`
