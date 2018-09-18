---
title: "Debugging"
menu:
  main:
    parent: "guides"
---

## Enable debugging

To use debugging, the constant `WP_DEBUG` needs to be set to `true`.

**wp-config.php**

```php
<?php
define( 'WP_DEBUG', true );
```

## Using Twig’s native functions

Twig includes a `dump` function that can output the properties of an object. 

**Twig**

```twig
{{ dump(post) }}
```

Which will give you:

![](http://i.imgur.com/5Xu53Fk.png)

You can also dump _everything_ sent to your template (contents of `$context` passed to the Twig file) via:

```twig
{{ dump() }}
```

This will give you something like:

![](http://i.imgur.com/5ZD8VDd.png)

## Formatted output

For a highlighted output like you see it above, you need to have [xDebug](https://xdebug.org/) enabled in your local development environment. With some environments like MAMP, enabling it is as easy as ticking a checkbox and restarting the server. Other times, it might be more complex.

An easier solution is to use the [Timber Dump Extension](https://github.com/nlemoine/timber-dump-extension), which will make use of the Symfony VarDumper component to generate output like this when using `{{ dump() }}` in Twig:

![](https://user-images.githubusercontent.com/2084481/31230351-116569a8-a9e4-11e7-8310-48b7f679892b.png)

It also works in PHP. Instead of using `var_dump` or `print_r`, you will use `dump()` as well:

```php
dump( $post );
```

## Using Timber Debug Bar plugin

There’s a [Timber add-on](http://wordpress.org/plugins/debug-bar-timber/) for the [WordPress debug bar](https://wordpress.org/plugins/debug-bar/).  
**Warning:** this currently requires PHP 5.4.

## Using (Legacy) Timber Filters

You can also use some quick filters on an object. These are legacy and will be removed in favor of using Twig's built-in functionality. However, these do not require that `WP_DEBUG` be turned on.

### print_r

Passes the variable to PHP’s `print_r` function.

```twig
{{ post|print_r }}
```

### get_class

This filter answers the question: What type of object am I working with? It passes a variable to PHP’s `get_class` function.

```twig
{{ post|get_class }}
```

It will output something like `TimberPost` or your custom wrapper object.
