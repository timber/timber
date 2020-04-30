---
title: "Installation"
---

You can install Timber through [Composer](https://getcomposer.org/download/). If you’re not familiar with Composer yet, read [our short introduction](@todo) and then come back here.

In earlier versions of Timber, you could install Timber as a WordPress plugin. We dropped support for that, because Timber is not really a plugin. It’s something in between of a framework or a library to develop WordPress themes.

## Install Timber into an existing project

To install Timber, you can use the following command:

```bash
composer require timber/timber
```

Now in which folder do you run that command?

You can choose yourself where in your project you want to include Timber.

- Some developers prefer to have Timber installed as a theme dependency, so they would run this command from the **theme root**.
- Others want to use Timber as a WordPress dependency, which means you would run the command above from the **WordPress root**.

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
composer create-project upstatement/timber-starter-theme --no-dev
```

This command will install the Starter Theme with Timber included as a Composer dependency.
