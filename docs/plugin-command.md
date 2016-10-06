---
    title: Plugin Command - WP Composer
---
##Plugin Subcommands
Manage dependencies of a specific plugin
> **Note** Some commands will generate a composer.json file if one does not currently exist

###add

Add the specified plugin to the composer.json file

---
    wp composer plugin add
---

#####Example
---
    # Add bbpress plugin to the composer.json file.
    wp composer plugin add bbpress
    # Add debug-bar as dev dependency
    wp composer plugin add debug-bar --dev
---
#####Result
A composer.json file will be generated with bbpress added as a dependency and denug-bar added as a dev dependency
```json
{
    "name": "wp-composer-dependencies",
    "description": "Theme and plugin dependencies for the site http://example.com",
    "repositories": [
        {
            "type": "composer",
            "url": "https://wpackagist.org"
        }
    ],
    "require": {
        "wpackagist-plugin/bbpress": "*"
    },
    "require-dev": {
        "wpackagist-plugin/debug-bar": "*"
    },
    "extra": {
        "installer-paths": {
            "wp-content/themes/{$name}": [
                "type:wordpress-theme"
            ],
            "wp-content/plugins/{$name}": [
                "type:wordpress-plugin"
            ],
            "wp-content/mu-plugins/{$name}": [
                "type:wordpress-muplugin"
            ]
        }
    }
}
```
###remove
Remove the specified plugin from the composer.json file

#####Example
---
    # Remove debug-bar as dev dependency
    wp composer plugin remove debug-bar --dev
---
#####Result
A composer.json file will be saved with debug-bar removed as a dev dependency
```json
{
    "name": "wp-composer-dependencies",
    "description": "Theme and plugin dependencies for the site http://example.com",
    "repositories": [
        {
            "type": "composer",
            "url": "https://wpackagist.org"
        }
    ],
        "require": {
            "wpackagist-plugin/bbpress": "*"
    },
    "extra": {
        "installer-paths": {
            "wp-content/themes/{$name}": [
                "type:wordpress-theme"
            ],
            "wp-content/plugins/{$name}": [
                "type:wordpress-plugin"
            ],
            "wp-content/mu-plugins/{$name}": [
                "type:wordpress-muplugin"
            ]
        }
    }
}
```

####Options

*[<plugin\>...]*

&nbsp;&nbsp;&nbsp;&nbsp;One or more plugins

*[--file]*

&nbsp;&nbsp;&nbsp;&nbsp;Path to save the composer.json file

*[--version]*

&nbsp;&nbsp;&nbsp;&nbsp;Add specified version of plugin

*[--latest]*

&nbsp;&nbsp;&nbsp;&nbsp;Always use the latest version from whatever repo the plugin is coming from

*[--installer-paths]* OR *[--ip]*

&nbsp;&nbsp;&nbsp;&nbsp;Set the WordPress plugins and themes installer path

*[--dev]*

&nbsp;&nbsp;&nbsp;&nbsp;Add plugin as a  dev requirement in composer.json

*[--deactivate]*

&nbsp;&nbsp;&nbsp;&nbsp;Deactivate the plugin before uninstalling it
