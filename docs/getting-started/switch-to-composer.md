---
title: "Switch to Composer"
description: "How to switch your plugin based Timber theme over to the composer based version."
menu:
  main:
    parent: "getting-started"
---

With the upcoming release of Timber 2, we will stop releasing Timber as a plugin. We advice everyone to switch to the composer based install as soon as possible.

## Recommended upgrade path

1. Get a local development version of your website up and running.
2. Check if you are running the latest version of the Timber plugin, if not, update it and check if your website still runs as expected
3. Disable the Timber plugin
4. Install the latest 1.x version of Timber via composer
5. Load the composer autoloader and initialize Timber 
6. Check if your website still runs as expected
7. Deploy your changes to your live website

### 1. Get a local development version of your website up and running
We highly recommend doing these steps on a local development version of your website. If you don't have one yet, you can use [Local by Flywheel](https://localbyflywheel.com/) to get one up and running in a few minutes.

#### 1.1 How to install Composer
If you don't have Composer installed yet, you can follow the [official installation guide](https://getcomposer.org/doc/00-intro.md).


### 2. Check if you are running the latest version of the Timber plugin
We want to make sure that you are running the latest version of the Timber plugin before we start the upgrade process. This way we can be sure that any issues that might occur are not caused by an outdated version of the plugin.

### 3. Disable the Timber plugin
Once you are sure that you are running the latest version of the Timber plugin, you can disable it. This will make sure that the plugin is not loaded anymore and does not interfere with the composer based version of Timber.

### 4. Install the latest 1.x version of Timber via composer
Now that the plugin is disabled, you can install the latest 1.x version of Timber via composer. You can do this by navigating to your site's active theme folder inside your terminal and then running the following commands:

```shell
composer init
```
This will start the composer initialization process. You can just press enter on all the questions that are asked. This will create a `composer.json` file in your theme folder. After that we can require the latest 1.x version of Timber by running the following command:

```shell
composer require timber/timber:^1.0
```

### 5. Load the composer autoloader and initialize Timber
Now that we have Timber installed via composer, we need to load the composer autoloader and initialize Timber. You can do this by adding the following code at the top of your `functions.php` file:

```php
/**
 * If you are installing Timber as a Composer dependency in your theme, you'll need this block
 * to load your dependencies and initialize Timber.
 */
$composer_autoload = __DIR__ . '/vendor/autoload.php';
if ( file_exists( $composer_autoload ) ) {
	require_once $composer_autoload;
	$timber = new Timber\Timber();
}
```

### 6. Check if your website still runs as expected
Now that you have Timber installed via composer, you can check if your website still runs as expected. If you run into any issues, please try to solve them yourself by activating WordPress debugging and checking the error logs.

### 7. Deploy your changes to your live website
When you are 100% sure that you have succesfully upgraded your website to the composer based version of Timber, you can disable the Timber plugin, deploy your theme changes to your live website. If you are using a deployment pipeline, you can commit your changes and push them to your live website and make sure that the new `vendor` folder inside your theme gets deployed as well. If you are not using a version control system, you can use FTP to upload your changes to your theme. As a last step you can remove the Timber plugin from your website.