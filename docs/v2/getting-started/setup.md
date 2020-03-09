---
title: "Setup"
description: "All about setting up Timber with your theme."
---

## Installation


## Use the starter theme

The [starter theme](https://github.com/timber/starter-theme) is for starting a project from scratch (you can also use Timber in an existing theme).

A `composer.json` file is already included with the starter theme, so you can run the following command to install Timber:

```shell
composer install
```

### Navigate to your WordPress themes directory

Like where twentyeleven and twentytwelve live. The Timber Starter Theme will live at the same level.

	/wp-content/themes	/twentyeleven
						/twentytwelve
						/timber-starter-theme

You should now have:

	/wp-content/themes/timber-starter-theme

You should probably **rename** this to something better.

### 1. Activate Timber

It will be in `wp-admin/plugins.php`.

### 2. Select your theme in WordPress

Make sure you select the Timber-enabled theme **after** you activate the plugin. The theme will crash unless Timber is activated. Use the **timber-starter-theme** theme from the step above (or whatever you renamed it to).

### 3. Let’s write our theme!

Continue ahead in [part 2 about Theming](https://timber.github.io/docs/getting-started/theming/).
