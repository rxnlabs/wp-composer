# WP Composer

WP Composer is a WP-CLI package for managing your WordPress theme and plugin dependencies. By using the power of WP-CLI and [composer](https://getcomposer.org/doc/00-intro.html), you can declare the themes and plugins your WordPress site depends on.

## Install

### Installing as a WP-CLI package (preferred installation method)
---
    wp package install rxnlabs/wp-composer-dependencies
---

### Installing as a plugin

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

![WP Composer Help Output](docs/images/wp-composer-help.png)

## Commands

| Command          | Description                |
| ---------------- | -------------------------- |
| `composer plugins` | Manage dependencies of installed plugins |
| `composer themes`  | Manage dependencies of installed themes |
| `composer plugin`  | Manage dependencies of a specific plugin |
| `composer theme`  | Manage dependencies of a specific theme |
| `composer add`  | Add installed plugins and themes to composer.json |
| `composer install`  | Install the dependencies of third-party themes and plugins |


## Documentation and Examples

Learn more about the plugin and the commands available by visiting [https://rxnlabs.github.io/wp-composer/](https://rxnlabs.github.io/wp-composer/)

> **Note** This plugin currently does **not** support premium plugins and themes, as well as plugins and themes not hosted on WordPress.org. This is a limitation I would like to fix at some point. In the meantime, you can manage your premium plugins in other ways such as submodules, subtrees, and plenty of other ways.

## Bugs and Issues

When you find issues, please report theme:
* web: https://github.com/rxnlabs/wp-composer/issues

Be sure to include any relevant details including PHP version, plugins installed on the site, error messages, themes install on the site, WP-CLI version.

