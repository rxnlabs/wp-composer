---
    title: Theme Command - WP Composer
---
##Theme Subcommands
Manage dependencies of a specific theme
> **Note** Some commands will generate a composer.json file if one does not currently exist

###add

Add the specified theme to the composer.json file

---
    wp composer theme add
---

#####Example
---
    # Add twentysixteen theme to the composer.json file.
    wp composer theme add twentysixteen
    # Add twentyfourteen as dev dependency
    wp composer theme add twentyfourteen --dev
    # Install zerif lite and sydney themes
    wp composer theme add zerif-lite,sydney
---
#####Result
A composer.json file will be generated with twentysixteen added as a dependency and twentyfourteen added as a dev dependency
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
        "wpackagist-theme/twentysixteen": "*",
        "wpackagist-theme/zerif-lite": "*",
        "wpackagist-theme/sydney": "*"
    },
    "require-dev": {
        "wpackagist-theme/twentyfourteen": "*"
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
Remove the specified theme from the composer.json file

#####Example
---
    # Remove twentyfourteen as dev dependency
    wp composer theme remove twentyfourteen --dev
---
#####Result
A composer.json file will be saved with twentyfourteen removed as a dev dependency
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
        "wpackagist-theme/twentysixteen": "*"
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

*[<theme\>...]*

&nbsp;&nbsp;&nbsp;&nbsp;One or more themes

*[--file]*

&nbsp;&nbsp;&nbsp;&nbsp;Path to save the composer.json file

*[--latest]*

&nbsp;&nbsp;&nbsp;&nbsp;Always use the latest version from whatever repo the theme is coming from. **default**.

> **Note** Unlike plugins, themes on wordpress.org don't have specific versions (e.g. v1.0, v2.0, etc...), so the latest version of a theme will always be downloaded

*[--installer-paths]* OR *[--ip]*

&nbsp;&nbsp;&nbsp;&nbsp;Set the WordPress plugins and themes installer path

*[--dev]*

&nbsp;&nbsp;&nbsp;&nbsp;Add theme as a  dev requirement in composer.json

*[--deactivate]*

&nbsp;&nbsp;&nbsp;&nbsp;Deactivate the theme before uninstalling it
