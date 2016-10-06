---
    title: Add Command - WP Composer
---
##Add Command
Add installed plugins and themes to composer.json
> **Note** Some commands will generate a composer.json file if one does not currently exist

###add

Add all plugins and themes to the composer.json file

---
    wp composer add
---
#####Example

Let's say that your site has the themes twentyeleven, and twentysixteeen installed. It also has the plugins bbpress, and buddypress installed.

---
    # Add all installed plugins and themes as dependencies to a composer.json file
    wp composer add --file=web/assets
---

#####Result
A composer.json file will be generated and saved to web/assets/composer.json with twentysixteen added as a dependency and twentyfourteen added as a dev dependency
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
        "wpackagist-theme/twentyeleven": "*",
        "wpackagist-plugin/bbpress": "*",
        "wpackagist-plugin/buddypress": "*"
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
*[--file]*

&nbsp;&nbsp;&nbsp;&nbsp;Path to save the composer.json file

*[--latest]*

&nbsp;&nbsp;&nbsp;&nbsp;Always use the latest version from whatever repo the theme is coming from. **default**.

> **Note** Unlike plugins, themes on wordpress.org don't have specific versions (e.g. v1.0, v2.0, etc...), so the latest version of a theme will always be downloaded

*[--installer-paths]* OR *[--ip]*

&nbsp;&nbsp;&nbsp;&nbsp;Set the WordPress plugins and themes installer path

*[--dev]*

&nbsp;&nbsp;&nbsp;&nbsp;Add theme as a  dev requirement in composer.json
