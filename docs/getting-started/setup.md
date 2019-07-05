---
title: "Setup"
description: "All about setting up Timber with your theme."
menu:
  main:
    parent: "getting-started"
---

## Installation

You can install Timber through [Composer](https://getcomposer.org/download/).

Run the following Composer command from within your theme's root directory:

```shell
composer require timber/timber
```

You can choose yourself where in your project you want to include Timber. Most developers prefer to have Timber installed as a theme dependency, so they would run this command from the theme root. But it’s also possible to use Timber as a WordPress dependency, which means you would run the command above from the WordPress root.

If your theme or project is not already set up to pull in Composer’s autoload file, you will need to add the following line at the top of your `functions.php` file: 

**functions.php**

```php
<?php
// Load Composer dependencies.
require_once __DIR__ . '/vendor/autoload.php';

// Initialize Timber
new Timber\Timber();
```

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
