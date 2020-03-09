---
title: "Installation"
---

You can install Timber through [Composer](https://getcomposer.org/download/). If you’re not familiar with Composer yet, read [our short introduction](…) and then come back here.

In earlier versions of Timber, you could install Timber as a WordPress plugin. We dropped support for that, because Timber is not really a plugin. It’s something in between of a framework or a library to develop WordPress themes and plugins.

## Install Timber into an existing project

To install Timber, you can use the following command:

```bash
composer require timber/timber
```

Now in which folder do you run that command?

### Install Timber as a theme dependency

Run the following Composer command from within your theme’s root directory:



This command will install the Timber package into the `vendor` folder of your project.

### Install Timber as a project dependency

You can choose yourself where in your project you want to include Timber. Most developers prefer to have Timber installed as a theme dependency, so they would run this command from the **theme root**. But it’s also possible to use Timber as a WordPress dependency, which means you would run the command above from the **WordPress root**.

If your theme or project is not already set up to pull in Composer’s autoload file, you will need to add the following line at the top of your **functions.php** file:

```php
<?php
// Load Composer dependencies.
require_once __DIR__ . '/vendor/autoload.php';

// Initialize Timber.
new Timber\Timber();
```

## Use the Starter Theme

If you want to use the Timber’s Starter Theme, you can use `composer create-project`. Run the following command from the **wp-content/themes** folder of your WordPress installation.

```bash
composer create-project timber/starter-theme --no-dev
```
