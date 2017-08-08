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
