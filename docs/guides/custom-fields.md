---
title: "Custom Fields"
menu:
  main:
    parent: "guides"
---

Timber tries to make it as easy as possible for you to retrieve custom meta data for a post.

When you have a custom field that’s named `my_custom_field`, you can access it’s database value like this:

**PHP**

```php
$post->my_custom_field
```

**Twig**

```
{{ post.my_custom_field }}
```

There are plugins that make it easier for you to handle custom fields, like Advanced Custom Fields. We have [a separate guide for that](https://timber.github.io/docs/guides/acf-cookbook/).

## How does Timber get meta values?

Timber uses a technique called [Overloading](http://de.php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members) to get meta values using PHP’s [`__get` magic method](http://php.net/manual/en/language.oop5.overloading.php#object.get) on [Timber\Post](https://timber.github.io/docs/reference/timber-post/#get), Term and User objects.

This means that the value is retrieved on the fly.

### Reserved function names



## The special case of site options

You can also get site options through these magic methods. Here’s an example to retrieve the admin email address:

**Twig**

```twig
{{ site.admin_email }}
```

## Query by custom field value

This example that uses a [WP_Query](http://codex.wordpress.org/Class_Reference/WP_Query) array shows the arguments to find all posts where a custom field called `color` has a value of `red`.

```php
<?php
$args = array(
    'numberposts' => -1,
    'post_type' => 'post',
    'meta_key' => 'color',
    'meta_value' => 'red'
);

$context['posts'] = new Timber\PostQuery($args);
```
