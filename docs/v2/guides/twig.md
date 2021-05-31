---
title: "Twig"
order: "200"
---

With Timber, you can use all the features that [Twig](https://twig.symfony.com/doc/) has to offer, plus some more. Here, we repeat some important information.

## Dot notation

In Twig, you mostly use the dot notation:

```twig
{{ post.title }}
```

Now, Twig doesn’t care whether post is an object or an array, or whether title is an array item, an object property or an object method. This is important to understand.

From looking at this code, you wouldn’t know whether `title` is a variable or a function call. In this particular case, you could also add the function parenthesis.

```twig
{{ post.title() }}
```

It’s totally fine to call functions without the parenthesis. You’ll only need them if you want to pass parameters to the function.

Now, because `title` is a function, when you dump the `post` object, you won’t see what the `title()` method returns.

```twig
{{ dump(post) }}
```

Instead, you will see that `post` also contains a `post_title` property. This is what the post inherits from the `WP_Post` object. It’s the raw title that didn’t run through the `the_title` filter.

...

Consider this array with a key that has a dash in it:

```php
$item = [
    'id'          => 7,
    'has-balcony' => true,
];
```

In Twig, you could access the `id` with `item.id`, but you couldn’t do…

```twig
# Array item.
{{ item[0] }}

# Normal associative array item.
{{ item.id }}

# Array item with special characters in the key.
{{ item['has-balcony'] }}

# Array item with variable as key.
{{ item[key] }}
```

## String concatentation

In PHP, you might be used to concatenate your strings with dots (`.`). In Twig, you’ll use a tilde (`~`).

**PHP**

```php
$string = $variable + '-suffix';
```

**Twig**

```twig
{% set string = variable ~ '-suffix' %}
```

## Includes

### Simple include

```twig
{{ include('footer.twig') }}
```

In earlier versions of Twig you would also see includes that looked like this:

```twig
{% include 'footer.twig' %}
```

The [include tag](https://twig.symfony.com/doc/3.x/tags/include.html) still works, but Twig recommends to use the [include function](https://twig.symfony.com/doc/3.x/functions/include.html).

Be sure to read through that documentation, because it provides helpful information. For example, it will tell you how to deal with missing files using `ignore_missing`.

When using includes, Timber will use the same [Template Locations](https://timber.github.io/docs/v2/guides/template-locations/) it uses for `Timber::render()` and `Timber::compile()`.

### Dynamic includes

If you want to build the name of your Twig template dynamically using a variable, you can use a tilde (`~`) to concatenate your strings:

```twig
{{ include(
    'blocks/block-' ~ block.slug ~ '.twig',
    ignore_missing = true
) }}
```

### Template arrays

You can pass an array of template to Twig includes. Twig will then use the first template it finds. In combination with dynamic includes, this would mean that `block/block.twig` would act as a fallback template.

```twig
{{ include(
    ['blocks/block-' ~ block.slug ~ '.twig', 'blocks/blog.twig'],
    ignore_missing = true
) }}
```

### Escapers

…

## Using Twig vars in live type

Imagine a scenario where you have a text input for a footer message in the WordPress admin panel that your editor users can edit:

```
Copyright {{ year }} by Upstatement, LLC. All Rights Reserved
```

But on the website itself, you want it to render as:

```
Copyright 2020 by Upstatement, LLC. All Rights Reserved
```

Ready? There are a bunch of ways to do this, but here’s one helpful example. First, we’re preparing the data in PHP.

**PHP**

```php
$data = [
    'year' => wp_date( 'Y' ),
    // "Copyright {{ year }} by Upstatement, LLC. All Rights Reserved"
    'copyright' => get_option( 'footer_message' );
];

Timber::render( 'footer.twig', $data );
```

And then, we pass it to Twig, where we use Twig’s own [`template_to_string()`](https://twig.symfony.com/doc/3.x/functions/template_from_string.html) function.

**footer.twig**

```twig
{% include template_from_string(copyright) %}
```

## Twig tools

### Text editor add-ons

* Text Mate & Sublime text bundle – [Anomareh's PHP-Twig](https://github.com/Anomareh/PHP-Twig.tmbundle)
* Emacs – [Web Mode](http://web-mode.org/)
* Geany – Add [Twig/Symfony2 detection and highlighting](https://wiki.geany.org/howtos/geany_and_django#twigsymfony2_support)
* PhpStorm – Built in coloring and code hinting. The Twig extension is recognized and has been for some time. [Twig Details for PhpStorm](http://blog.jetbrains.com/phpstorm/2013/06/twig-support-in-phpstorm/).
* Atom – Syntax highlighting with the [Atom Component](https://atom.io/packages/php-twig).

### WordPress tools

* [Lisa Templates](https://github.com/pierreminik/lisa-templates/) – allows you to write Twig-templates in the WordPress Admin that renders through a shortcode, widget or on the_content hook.

### Other

* [Watson-Ruby](http://nhmood.github.io/watson-ruby/) – An inline issue manager. Put tags like `[todo]` in a Twig comment and find it easily later. Watson supports Twig as of version 1.6.3.

### JavaScript

* [Twig.js](https://github.com/justjohn/twig.js) – Use those `.twig` files in the Javascript and AJAX components of your site.
* [Nunjucks](http://mozilla.github.io/nunjucks/) – Another JS template language that is also based on [Jinja2](http://jinja.pocoo.org/docs/)
