---
title: "WP-CLI"
order: "1670"
---

Timber includes its own `wp timber` command for [WP-CLI](https://make.wordpress.org/cli/handbook/), the WordPress Command Line Interface.

## Preparations

Before you can use the CLI, you need to [install WP-CLI](https://make.wordpress.org/cli/handbook/guides/installing/) separately.

## Commands

You can run the following CLI commands from the root directory of your WordPress installation.

If you need help with a command, use `wp help timber` or `wp help timber <command>`, e.g. `wp help timber clear-cache`.

### `wp timber clear-cache`

Clears Timber and Twig caches. Runs `Timber\Cache\Cleaner::clear_cache()` in the background.

```bash
# Clear all caches.
wp timber clear-cache

# Clear Timber caches.
wp timber clear-cache timber

# Clear Twig caches.
wp timber clear-cache twig
```

## Contributing commands

If you want to contribute more commands, we’re happy to receive [pull requests](https://github.com/timber/timber/pulls) to the Timber repository.
