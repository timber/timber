---
title: "Setup"
---

## Installation
> ⚠️ **Important information about the Timber plugin**
> With the release of Timber 2.0, Composer is the only supported install method. We are unable to continue releasing or supporting Timber as a plugin on WordPress.org. We advise everyone to **[switch to the Composer based install of Timber 1 as a first step](https://timber.github.io/docs/v1/getting-started/switch-to-composer/)** as soon as possible. If you need PHP 8.2 support you will have to switch to Timber 2.0.

Underneath this text you will find an extensive list with guides and the reasons why we are not going to release Timber 2 in plugin version anymore.

* Guide: [How do I switch over from the plugin version to the Composer based version of Timber?](https://timber.github.io/docs/v1/getting-started/switch-to-composer/)
* Backstory: [Why we are dropping support for the plugin in the first place](https://github.com/timber/timber/pull/2005)
* GitHub issue: [Road to Timber 2.0](https://github.com/timber/timber/issues/2741)

### ~~Via WordPress.org (easy)~~

~~You can grab the all-things-included plugin at [WordPress.org](https://wordpress.org/plugins/timber-library/) either through the WordPress site or through the Plugins menu in the backend. Then skip ahead to [using the starter theme](#use-the-starter-theme).~~

### Via Composer (recommended)

The GitHub version of Timber requires [Composer](https://getcomposer.org/download/).

```shell
composer require timber/timber:^1.0
```

If your theme is not setup to pull in Composer’s autoload file, you will need to add the following at the top of your `functions.php` file:

**functions.php**

```php
<?php
require_once( __DIR__ . '/vendor/autoload.php' );
```

Initialize Timber with:

**functions.php**

```php
<?php
$timber = new Timber\Timber();
```

## Use the starter theme

The [starter theme](https://github.com/timber/starter-theme) is for starting a project from scratch. You can also use Timber in an existing theme.

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

Continue ahead in [part 2 about Theming](https://timber.github.io/docs/v1/getting-started/theming/).
