# WP Composer

WP Composer is a WP-CLI package for managing your WordPress theme and plugin dependencies. By using the power of WP-CLI and [composer](https://getcomposer.org/doc/00-intro.html), you can declare the themes and plugins your WordPress site depends on.

## Install

### Installing as a plugin

Clone this repo into the plugins/ folder of your WordPress site (i.e. plugins/wp-composer), run `composer install --no-dev` to fetch the plugin dependencies. Then, activate the plugin.

Run `wp composer --help` to get the list of commands and subcommands available.
## Requirements

* Requires [WP-CLI](https://github.com/wp-cli/wp-cli) version 0.24.0 and up
* [Composer]((https://getcomposer.org)

## Commands

| Command          | Description                |
| ---------------- | -------------------------- |
| `composer plugins` | Manage dependencies of installed plugins |
| `composer themes`  | Manage dependencies of installed themes |
| `composer plugin`  | Manage dependencies of a specific plugin |
| `composer theme`  | Manage dependencies of a specific theme |
| `composer add`  | Add installed plugins and themes to composer.json |
| `composer install`  | Install the dependencies of third-party themes and plugins |


## Documentation

Learn more about the plugin and the commands available by visiting [https://rxnlabs.github.io/wp-composer/](https://rxnlabs.github.io/wp-composer/)

> **Note** This plugin currently does **not** support premium plugins and themes, as well as plugins and themes not hosted on WordPress.org. This is a limitation I would like to fix at some point. In the meantime, you can manage your premium plugins in other ways such as submodules, subtrees, and plenty of other ways.

