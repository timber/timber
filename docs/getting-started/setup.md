# Getting Started: Setup

## Installation

#### Via WordPress.org (easy)
You can just grab the all-things-included plugin at [WordPress.org](http://wordpress.org/plugins/timber-library/) either through the WP site or your Plugins->Add New in wp-admin. Then skip ahead to [using the starter theme](#use-the-starter-theme).

#### Via GitHub (for developers)

The GitHub version of Timber requires [Composer](https://getcomposer.org/download/). If you'd prefer one-click installation, you should use the [WordPress.org](https://wordpress.org/plugins/timber-library/) version.

```shell
composer require timber/timber
```

If your theme is not setup to pull in Composer's autoload file, you will need to:

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');
```

at the top of your `functions.php` file.

Initialize Timber with:

```php
<?php
$timber = new \Timber\Timber();
```

* * *

## [Use the starter theme](https://github.com/Upstatement/timber-starter-theme)
This is for starting a project from scratch. You can also use Timber in an existing theme.

##### Navigate to your WordPress themes directory
Like where twentyeleven and twentytwelve live. The Timber Starter will live at the same level.

	/wp-content/themes	/twentyeleven
						/twentytwelve
						/timber-starter-theme

You should now have:

	/wp-content/themes/timber-starter-theme

You should probably **rename** this to something better.

### 1. Activate Timber
It will be in wp-admin/plugins.php

### 2. Select your theme in WordPress
Make sure you select the Timber-enabled theme **after** you activate the plugin. The theme will crash unless Timber is activated. Use the **timber-starter-theme** theme from the step above (or whatever you renamed it).

### 3. Let's write our theme!
