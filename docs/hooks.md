# Hooks

## Hooks used by this plugin

This plugin hooks into the command `wp plugin install plugin-name` and will automatically add that plugin to the composer.json file.

### Example

---
	wp plugin install buddypress
---

### Result

![WP Composer Plugin Install Hook](images/wp-composer-plugin-install-hook.gif)

It also hooks into the command `wp plugin uninstall plugin-name` by automatically removing plugin-name from the composer.json file

### Example

---
	wp plugin uninstall buddypress --deactivate
---

### Result

![WP Composer Plugin Uninstall Hook](images/wp-composer-plugin-uninstall-hook.gif)

This also applies to the `wp theme install theme-name`

---
	wp theme install twentysixteen
---

This also applies to the `wp theme uninstall theme-name`

---
	wp theme uninstall twentysixteen  --deactivate
---

## Hooks available for this plugin

None at this time. If you're writing a WP-CLI command, you can use the hooks provided by [WP-CLI add_hook function](https://wp-cli.org/docs/internal-api/wp-cli-add-hook/)