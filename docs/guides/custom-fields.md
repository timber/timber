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
3. Directly access it through the field’s name.
4. Define your own method.

Each of these methods behaves a little differently. Let’s look at each one in more detail.

### The `meta()` method

This method is the recommended way to access meta values. You’ll get values that are **filtered** by third-party plugins (e.g. Advanced Custom Fields).

**Twig**

```twig
{{ post.meta('my_custom_field') }}
```

**PHP**

```php
$my_custom_field = $post->meta( 'my_custom_field' );
```

### Direct access through field name

We *recommend* to use this method when you’ve defined a custom method that **modifies the value of the custom field before it is returned**.

```php
class CustomPost extends Timber\Post {
    /**
     * Gets formatted price.
     */
    public function price() {
        $price = $this->meta( 'price' );
        
        // Remove decimal digits.
        return number_format( $price, 0, '', '' );
    }
}
```

```twig
{{ post.price }}
```

This way, you’ll know from looking at the code that you call a method. If that method doesn’t exist, Timber will fall back to the equivalent `{{ post.meta('post') }}` call. This means that you can always write `{{ post.price }}`, even if you don’t have method. We don’t recommend to use this method, because you might run into conflicts with existing methods on an object.

#### Caveat: Conflicts with Timber methods

This method might not work in all cases. For example, when you use a custom field that you name `date` and try to get its value through `{{ post.date }}`, it won’t work. That’s because [`date`](https://timber.github.io/docs/reference/timber-post/#date) is a method of the `Timber\Post` object that returns the date a post was published.

In PHP, you’d access the property through `$post->date` and call the `date` method through `$post->date()`. But in Twig, you can call methods without using brackets. And methods take precedence over properties. Timber uses a technique called [Overloading](http://de.php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members) to get meta values using PHP’s [`__get` magic method](http://php.net/manual/en/language.oop5.overloading.php#object.get) on `Timber\Post`, `Timber\Term`, `Timber\User` and `Timber\Comment` objects. This means that when you use `{{ post.date}}`, it will

- Check if method method `date` exists. If it does, it will return what the method produces.
- Otherwise, it will check if a property `date` exists. If it does, it will return its value.
- Otherwise, it will hit the `__get()` method, which handles undefined or inaccessible properties. Timber’s `__get()` method will look for existing custom values and return these.

Now, if you run into this problem, you should probably use the [`meta()`](#the-meta-method) method.

### The `custom` property

The `custom` property that’s always set on an object is an array that hold the values of all the meta values from the `postmeta` table in the database. With this method, values are **raw** (directly from the database) and are **not filtered** by third-party plugins (e.g. Advanced Custom Fields).

**Twig**

```twig
{{ post.raw_meta('my_custom_field') }}
```

**PHP**

```php
$my_custom_field = $post->raw_meta( 'my_custom_field' );
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
