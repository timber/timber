---
title: "An intro to Composer"
---

Composer is the industry standard for managing community-built PHP packages.

With Composer, you don’t have to download, extract and move a library or a piece of functionality into your project, but you can install it with a simple command that you run in your terminal.

To install Timber, you would use `composer require`.

```bash
composer require timber/timber
```

But before you can run this command, you need to have Composer installed. Follow the [official guide for the installation](https://getcomposer.org/download/).

In addition to that, you probably want to be able to use `composer` in your terminal instead of `composer.phar`

```bash
# Install Composer as a global command.
mv composer.phar /usr/local/bin/composer
```

Now, after you’ve installed Composer, where would you run that command? As always, it depends. But generally, you would run it from your **project’s root folder**.

When you run that command, Composer will do a couple of things.

## composer.json

It will create **composer.json** file if there isn’t already one in your project. In that file, it will make a new entry for the installed package:

```json
{
    require: {
        "timber/timber": "^2.0.0"
    }
}
```

## The vendor folder

Composer will create a **vendor** folder in your project and download the Timber package as well as other packages that Timber uses into that folder.

It will create a **vendor/autoload.php** file, which is the entry point to all your packages. Here’s where Composer really shines.

## Autoloading

Maybe you’re used to require all the files you need separately?

```php
require_once 'functionality-a.php';
require_once 'class-b.php';
require_once 'class-c.php';
```

With Composer, the only thing you need to do to make all functionality available is the following.

```php
require_once 'vendor/autoload.php';
```

Usually, you would do that at the top of your **functions.php** file of your WordPress theme.

After that, you can initialize and use all Timber classes, they will all be available.
