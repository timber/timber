---
title: "Switch to Composer"
description: "How to switch your plugin based Timber theme over to the Composer based version."
---

With the release of Timber 2.0, Composer is the only supported install method. We are unable to continue releasing or supporting Timber as a plugin on WordPress.org. We advise everyone to switch to the Composer based install of Timber 1 as soon as possible as a first step towards upgrading to Timber 2.

## Recommended upgrade path

1. Get a local development version of your website up and running.
2. Check if you are running the latest version of the Timber plugin. If not, update it and check if your website still runs as expected.
3. Disable the Timber plugin
4. Install the latest 1.x version of Timber via Composer
5. Load the Composer autoloader and initialize Timber
6. Check if your website still runs as expected
7. Deploy your changes to your live website


### 1. Get a local development version of your website up and running
We highly recommend doing these steps on a local development version of your website. If you don’t have one yet, you could use [Local by Flywheel](https://localbyflywheel.com/) to get one up and running in a few minutes.

#### 1.1 How to install Composer
If you don’t have Composer installed yet, you can follow the [official installation guide](https://getcomposer.org/doc/00-intro.md).


### 2. Check if you are running the latest version of the Timber plugin
We want to make sure that you are running the latest version of the Timber plugin before we start the upgrade process. This way we can be sure that any issues that might occur are not caused by an outdated version of the plugin.

### 3. Disable the Timber plugin
Once you are sure that you are running the latest version of the Timber plugin, you can disable it. This will make sure that the plugin is not loaded anymore and does not interfere with the Composer based version of Timber.

Please note that you website will throw errors at this point, as there is no Timber available. This will be fixed in the next two steps.

### 4. Install the latest 1.x version of Timber via Composer
Now that the plugin is disabled, you can install the latest 1.x version of Timber via Composer. You can do this by navigating to your site's active theme folder inside your terminal and then running the following command:

```shell
composer require timber/timber:^1.0
```

If Composer gives you the following notice:

```shell
composer/installers contains a Composer plugin [...] (writes "allow-plugins" to composer.json)
```
it is safe to answer `y` to this question and press enter.


### 5. Load the Composer autoloader and initialize Timber
Now that we have Timber installed via Composer, we need to load the Composer autoloader and initialize Timber. You can do this by adding the following code at the top of your **functions.php** file:

```php
// Load Composer dependencies.
require_once __DIR__ . '/vendor/autoload.php';

$timber = new Timber\Timber();
```

### 6. Check if your website still runs as expected
Now that you have Timber installed via Composer, you can check if your website still runs as expected. If you run into any issues, please try to solve them yourself by activating [WordPress debugging](https://wordpress.org/documentation/article/debugging-in-wordpress/) and checking the error logs.

### 7. Deploy your changes to your live website
When you are 100% sure that you have successfully upgraded your theme in the development website to the Composer based version of Timber, you can deploy your theme changes to your live website.

Make sure that the new **vendor** folder inside your theme gets deployed as well. If you are using a deployment pipeline, you can commit your changes and push to your live website.

If you are not using a version control system, you can use FTP or SCP to upload your changes to your theme.

As a last step, you should disable and then remove the Timber v1 plugin from your live website.

Congrats - you successfully moved to the Composer-based install and are prepared to later [upgrade to Timber v2](https://timber.github.io/docs/v2/upgrade-guides/2.0/).
