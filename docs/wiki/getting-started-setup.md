# Getting Started: Setup

## Installation

#### Via WordPress.org (easy)
You can just grab the all-things-included plugin at [WordPress.org](http://wordpress.org/plugins/timber-library/) either through the WP site or your Plugins->Add New in wp-admin. Then skip ahead to [using the starter theme](#use-the-starter-theme).

#### Via GitHub (for developers)

##### 1) Navigate to your WordPress plugins directory
	$ cd ~/Sites/mywordpress/wp-content/plugins

##### 2) Use git to grab the repo
	$ git clone git@github.com:timber/timber.git

##### 3) Use [Composer](https://getcomposer.org/doc/00-intro.md) to download the dependencies (Twig, etc.)
	$ cd timber
	$ composer install

* * *

## Use the starter theme
This is for starting a project from scratch. You can also use Timber in an existing theme.

##### Navigate to your WordPress themes directory
Like where twentyeleven and twentytwelve live. The Timber Starter will live at the same level.

	/wp-content/themes	/twentyeleven
						/twentytwelve
						/timber-starter-theme

You should now have

	/wp-content/themes/timber-starter-theme

You should probably **rename** this to something better

### 1. Activate Timber
It will be in wp-admin/plugins.php

### 2. Select your theme in WordPress
Make sure you select the Timber-enabled theme **after** you activate the plugin. The theme will crash unless Timber is activated. Use the **timber-starter-theme** theme from the step above (or whatever you renamed it).

### 3. Let's write our theme!
Continue ahead in [Part 2](#getting-started-themeing)
