---
title: "WP-CLI"
order: "1670"
---

Timber includes its own `wp timber` command for [WP-CLI](https://make.wordpress.org/cli/handbook/), the WordPress Command Line Interface.

## Preparations

Before you can use the CLI, you need to [install WP-CLI](https://make.wordpress.org/cli/handbook/guides/installing/) separately.

## Commands

You can run the following commands from the root directory of your WordPress installation.

### `wp timber`

The `wp timber` command doesn’t do anything. But if you run it, it tells you which commands are available.

```bash
wp timber
```

### `wp timber clear_cache`

Clears Timber and Twig’s cache. Runs `Timber\Cache\Cleaner::clear_cache()`.

```bash
wp timber clear_cache
```

### `wp timber clear_cache_twig`

Clears Twig’s cache only. Runs `Timber\Cache\Cleaner::clear_cache_twig()`.

```bash
wp timber clear_cache_twig
```

### `wp timber clear_cache_timber`

Clears Timber’s cache only. Runs `Timber\Cache\Cleaner::clear_cache_timber()`.

```bash
wp timber clear_cache_timber
```

## Contributing commands

If you want to contribute more commands, we’re happy to receive [pull requests](https://github.com/timber/timber/pulls) to the Timber repository.
