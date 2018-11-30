---
title: "Custom Fields"
weight: "600"
menu:
  main:
    parent: "guides"
---

Timber tries to make it as easy as possible for you to retrieve custom meta data for post, term and user objects. And it works with a range of plugins that make it easier for you to create custom fields, like Advanced Custom Fields. While most of this guide applies to everything you do with custom fields, we have separate guides in the [Integrations sections](https://timber.github.io/docs/integrations/).

## Accessing custom values

When you have a custom field that’s named `my_custom_field`, you can access it in 3 different ways:

1. Directly access it through the field’s name.
2. Access it through the `custom` property.
3. Access it through the `meta()` method.

Each of these methods behaves a little differently. Let’s look at each one in more detail.

### Direct access through field name

You can access a custom field value directly through it’s name. With this method, values **are filtered** by third-party plugins (e.g. Advanced Custom Fields).

**Twig**

```
{{ post.my_custom_field }}
```

**PHP**

```php
$my_custom_field = $post->my_custom_field;
```

#### Conflicts with Timber methods

This method might not work in all cases. When you for example use a custom field that you name `date` and try to get its value through `{{ post.date }}`, it won’t work. That’s because [`date`](https://timber.github.io/docs/reference/timber-post/#date) is a method of the `Timber\Post` object that returns the date a post was published.

In PHP, you’d access the property through `$post->date` and call the `date` method through `$post->date()`. But in Twig, you can call methods without using brackets. And methods take precedence over properties. Timber uses a technique called [Overloading](http://de.php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members) to get meta values using PHP’s [`__get` magic method](http://php.net/manual/en/language.oop5.overloading.php#object.get) on `Timber\Post`, `Timber\Term` and `Timber\User` objects. This means that when you use `{{ post.date}}`, it will

- Check if method method `date` exists. If it does, it will return what the method produces.
- Otherwise, it will check if a property `date` does exist. If it does, it will return its value.
- Otherwise, it will hit the `__get()` method, which handles undefined or inaccessible properties. Timber’s `__get()` method will look for existing custom values and return these.

Now, there are several workarounds for that:

1. Use a different field name. For a date, you could e.g. use `date_from`.
2. Access the value through the [`meta()`](#the-meta-method) method.

### The `custom` property

The `custom` property that’s always set on an object is an array that hold the values of all the meta values from the `postmeta` table in the database. With this method, values are **raw** (directly from the database) and are **not filtered** by third-party plugins (e.g. Advanced Custom Fields).

**Twig**

```
{{ post.custom.my_custom_field }}
```

**PHP**

```php
$my_custom_field = $post->custom['my_custom_field'];
```

### The `meta()` method

This method is practically the same as accessing value directly through its field name. You’ll also get values that are **filtered** by third-party plugins (e.g. Advanced Custom Fields), without having the risk of running into the problem of conflicts with Timber methods.

**Twig**

```twig
{{ post.meta('my_custom_field') }}
```

**PHP**

```php
$my_custom_field = $post->meta( 'my_custom_field' );
```

## Site options

You can also get site options directly through their name. Here’s an example to retrieve the admin email address:

```twig
{{ site.admin_email }}
```

Please be aware that using this might also [conflict with existing Timber methods](#conflicts-with-timber-methods). As a workaround, you can also use the `custom` property or the `meta()` method.

## Query by custom field value

This example that uses a [WP_Query](http://codex.wordpress.org/Class_Reference/WP_Query) array shows the arguments to find all posts where a custom field called `color` has a value of `red`.

```php
$args = array(
    'numberposts' => -1,
    'post_type' => 'post',
    'meta_key' => 'color',
    'meta_value' => 'red'
);

$context['posts'] = new Timber\PostQuery($args);
```
