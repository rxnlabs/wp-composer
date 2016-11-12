# WP Composer

WP Composer is a [WP-CLI package](http://wp-cli.org/package-index/) for managing your WordPress theme and plugin dependencies. By using the power of WP-CLI and [Composer](https://getcomposer.org/doc/00-intro.md), you can declare the themes and plugins your WordPress site depends on.

## How it works
Your themes and plugins will be stored in a [composer.json](https://getcomposer.org/doc/01-basic-usage.md) file along, the same way you would manage other PHP dependencies.

## Installation

### Installing as a WP-CLI package (preferred installation method)
---
    wp package install rxnlabs/wp-composer-dependencies
---

### Installing as a plugin (you need to have Composer installed)

Clone this repo into plugins/ folder of your WordPress site, run:

---
    composer install --no-dev --prefer-dist
---

This installs the plugin dependencies. Then, activate the plugin.

## Requirements

* PHP 5.4 and up
* Requires [WP-CLI](http://wp-cli.org/) version 0.24.0 and up.

### Optional Requirements

> **Note:** Only needed if you're using as a plugin and not as a WP-CLI package. omposer is useful in a lot of other scenarios. Learn more about [Composer](https://getcomposer.org/doc/00-intro.md).

* [Composer](https://getcomposer.org/)

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