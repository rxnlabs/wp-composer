---
    title: Installation - WP Composer
---
# Installation

## Installing as a WP-CLI package (preferred installation method)
---
    wp package install rxnlabs/wp-composer-dependencies
---

## Installing as a plugin

Clone this repo into plugins/ folder of your WordPress site, run:

---
    composer install --no-dev
---

This installs the plugin dependencies. Then, activate the plugin.

## Requirements

Requires WP-CLI version 0.24.0 and up.

After installing as a WP-CLI package or as a plugin, run `wp composer --help` to see the list of commands and subcommands available.

![WP Composer Help Output](../images/wp-composer-help.png)