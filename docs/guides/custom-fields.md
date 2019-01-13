---
title: "Custom Fields"
weight: "600"
menu:
  main:
    parent: "guides"
---

Timber tries to make it as easy as possible for you to retrieve custom meta data for post, term, user and comment objects. And it works with a range of plugins that make it easier for you to create custom fields, like Advanced Custom Fields. While most of this guide applies to everything you do with custom fields, we have separate guides in the [Integrations section](https://timber.github.io/docs/integrations/).

## Accessing custom values

When you have a custom field that’s named `my_custom_field`, you can access it in 3 different ways:

1. Access it through the `meta()` method.
2. Access it through the `raw()` property.
3. Define your own method.

Each of these methods behaves a little differently. Let’s look at each one in more detail.

### The `meta()` method

This is the **recommended** method to get custom values for most use cases.
You’ll get values that are **filtered** by third-party plugins (e.g. Advanced Custom Fields).

**Twig**

```twig
{{ post.meta('my_custom_field') }}
```

**PHP**

```php
$my_custom_field = $post->meta( 'my_custom_field' );
```

### The `raw_meta()` method

With this method, values are **raw** (directly from the database) and are **not filtered** by third-party plugins (e.g. Advanced Custom Fields).

**Twig**

```twig
{{ post.raw_meta('my_custom_field') }}
```

**PHP**

```php
$my_custom_field = $post->raw_meta( 'my_custom_field' );
```

### Define your own method

When you directly access the field name of an object, then it’s considered that you defined your own method for handling that value.

**Twig**

```twig
{{ post.my_custom_field }}
```

**PHP**

```php
class ExtendedPost extends Timber\Post {
    public function my_custom_field() {
        $value = $this->meta( 'my_custom_field' );

        // Do something else with the value

        return $value;
    }
}
```

Be aware that through this method, you might overwrite a method that already exists on for a `Timber\Post` object, like [`date`](https://timber.github.io/docs/reference/timber-post/#date).

### The `custom` property

The `custom` property that’s always set on an object is an array that holds the values of all the meta values from the meta database table (`wp_postmeta`, `wp_termmeta`, `wp_usermeta`or  `wp_commentmeta`, depending on the object you have at hand).

This property only acts **as a reference** for all the existing meta values. You **can’t access the values through that property**, because it’s protected. You need to use the `meta()` or `raw_meta()` method instead. So instead of doing:

```twig
{{ post.my_custom_field }}
```

You would use one of the following:

```twig
{{ post.meta('my_custom_field') }}
{{ post.raw_meta('my_custom_field') }}
```

## Site options

You can also get site options through a similar method. Instead of `meta()`, it’s called `option()`. Here’s an example to retrieve the admin email address:

```twig
{{ site.option('admin_email') }}
```

For site options, it’s also possible to access it directly through its name:

```twig
{{ site.admin_email }}
```

Please be aware that using this might conflict with existing Timber methods on the `Timber\Site` object. That’s why the `option()` method is the preferred way to retrieve site options.

## Query by custom field value

This example that uses a [WP_Query](http://codex.wordpress.org/Class_Reference/WP_Query) array shows the arguments to find all posts where a custom field called `color` has a value of `red`.

```php
$args = array(
    'numberposts' => -1,
    'post_type'   => 'post',
    'meta_key'    => 'color',
    'meta_value'  => 'red',
);

$context['posts'] = new Timber\PostQuery($args);
```
