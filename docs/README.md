# WP Composer

WP Composer is a WP-CLI package for managing your WordPress theme and plugin dependencies. By using the power of WP-CLI and [composer](https://getcomposer.org/doc/00-intro.md), you can declare the themes and plugins your WordPress site depends on.

## How it works
Your themes and plugins will be stored in a [composer.json](https://getcomposer.org/doc/01-basic-usage.md) file along, the same way you would manage other PHP dependencies.

## Installation

### Installing as a WP-CLI package (preferred installation method)
---
    wp package install rxnlabs/wp-composer-dependencies
---

### Installing as a plugin

Clone this repo into plugins/ folder of your WordPress site, run:

---
    composer install --no-dev
---

This installs the plugin dependencies. Then, activate the plugin.

### Requirements

Requires WP-CLI version 0.24.0 and up.

After installing as a WP-CLI package or as a plugin, run `wp composer --help` to see the list of commands and subcommands available.
![WP Composer Help Output](images/wp-composer-help.png)

## Commands
| Command          | Description                |
| ---------------- | -------------------------- |
| [`composer plugins`](plugins-command.md) | Manage dependencies of installed pluginsss |
| [`composer themes`](themes-command.md)  | Manage dependencies of installed themes |
| [`composer plugin`](plugin-command.md)  | Manage dependencies of a specific plugin |
| [`composer theme`](theme-command.md)  | Manage dependencies of a specific theme |
| [`composer add`](add-command.md)  | Add installed plugins and themes to composer.json |
| [`composer install`](install-command.md)  | Install the dependencies of third-party themes and plugins |