---
title: "Twig"
order: "200"
---

With Timber, you can use all the features that [Twig](https://twig.symfony.com/doc/) has to offer, plus some more. In this guide, we repeat some important Twig information.

## Dot notation

In Twig, you mostly use the dot notation:

```twig
{{ post.title }}
```

Twig doesn’t care whether `post` is an object or an array, or whether `title` is an array item, an object property or an object method.

(It will use what it finds first, in a specific order. This is important to understand and you can read more about this in the [Variables](https://twig.symfony.com/doc/3.x/templates.html#variables) section of the Twig documentation.)

From looking at the code, you wouldn’t know whether `title` is a variable or a function call. In this particular case, you could also add the function parenthesis.

```twig
{{ post.title() }}
```

It’s totally fine to call functions without the parenthesis. You’ll only need them if you want to pass parameters to the function.

Now, because `title` is a function, when you dump the `post` object, you won’t see what the `title()` method returns.

```twig
{{ dump(post) }}
```

Instead, you will see that `post` also contains a `post_title` property. This is what the post inherits from the `WP_Post` object. It’s the raw title that didn’t run through the `the_title` filter yet.

### Accessing array items

Consider this array with a key that has a dash in it:

```php
$item = [
    'id' => 7,
    'has-balcony' => true,
];
```

In Twig, you could access the `id` with `item.id`, but you couldn’t use `item.has-balcony` because of the `-`. Luckily, you can use `item['has-balcony']`.

```twig
{# Array item. #}
{{ item[0] }}

{# Normal associative array item. #}
{{ item.id }}

{# Array item with special characters in the key. #}
{{ item['has-balcony'] }}

{# Array item with variable as key. #}
{{ item[key] }}
```

## String concatenation

In PHP, you might be used to concatenate your strings with dots (`.`). In Twig, you’ll use a tilde (`~`).

**PHP**

```php
$string = $variable + '-suffix';
```

**Twig**

```twig
{% set string = variable ~ '-suffix' %}
```

Or, if you want to use [string interpolation](https://twig.symfony.com/doc/3.x/templates.html#string-interpolation):

```twig
{% set string = "#{variable}-suffix" }
```

You could also use the [format filter](https://twig.symfony.com/doc/3.x/filters/format.html), which works the same way as [sprintf](https://www.php.net/sprintf).

```twig
{% set string = '%s-suffix'|format(variable) %}
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

The [include **tag**](https://twig.symfony.com/doc/3.x/tags/include.html) still works, but Twig recommends to use the [include **function**](https://twig.symfony.com/doc/3.x/functions/include.html).

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

## Default values

When you want to define default values for Twig variables that you use in your templates, you can either use the [`default`](https://twig.symfony.com/doc/3.x/filters/default.html) filter or the ternary (`?:`) or null-coalescing (`??`) operator.

Consider a template that you use to display a page title.

**page-title.twig**

```twig
<h1 class="heading-1">{{ post.title }}</h1>
```

If you wanted to reuse this for archive pages or pages where you pass a specific title, then you could use a `title` variable that uses `post.title` as the default value.

**archive.twig**

```twig
{{ include('page-title.twig', {
    title: 'All posts'
}) }}
```

**page-title.twig**

```twig
<h1 class="heading-1">{{ title|default(post.title) }}</h1>

{# Or with the null-coalescing operator #}

<h1 class="heading-1">{{ title ?? post.title }}</h1>
```

Pay special attention when you use boolean default values and use `??` instead of the `default` filter.

```twig
{# 🚫 Don’t do this #}
{% if show_pagination|default(true) %}
    {{ include('pagination.twig') }}
{% endif %}

{# ✅ Do this #}
{% if show_pagination ?? true %}
    {{ include('pagination.twig') }}
{% endif %}
```

You can read more about this in the [default filter documentation](https://twig.symfony.com/doc/3.x/filters/default.html#default).

## WordPress Actions

You can call actions in your Twig templates like this:

```twig
{# Without parameters #}
{% do action('my_action') %}

{# With parameters #}
{% do action('my_action_with_args', 'foo', 'bar') %}
```

If you ask yourself why there’s no underline between `do` and `action`: The expression [`do`](https://twig.symfony.com/doc/tags/do.html) is a feature of Twig which *calls a function without printing its return value*, like `{{ }}` does. Timber only registers an `action` function, which then calls the `do_action()` function.

If you want anything from the template’s context, you'll need to pass that manually:

```twig
{% do action('my_action', 'foo', post) %}
```

**functions.php**

```php
add_action('my_action_with_args', 'my_function_with_args', 10, 2);

function my_function_with_args($foo, $post)
{
    echo 'I say ' . $foo . '!';
    echo 'For the post with title ' . $post->title();
}
```

## WordPress Filters

Timber already comes with a [set of useful filters](https://timber.github.io/docs/v2/guides/twig-filters/). If you have your own WordPress filters that you want to easily apply in Twig, you can use `apply_filters`.

**Twig**

```twig
{{ post.content|apply_filters('default_message') }}

{{ "my custom string"|apply_filters('default_message', param1, param2, ...) }}
```

Or you can use a filter with the [Twig apply tag](https://twig.symfony.com/doc/3.x/tags/apply.html).

```twig
{% apply apply_filters('default_message') %}
    {{ post.content }}
{% endapply %}

{% apply apply_filters('default_message', 'foo', 'bar, 'baz' ) %}
    I love pizza
{% endapply %}
```

In **PHP**, you can get the content of the block with the first parameter and the rest of parameters like that.

```php
add_filter('default_message', 'my_default_message', 10, 4);

function my_default_message($tag, $param1, $param2, $param3)
{
    var_dump($tag, $param1, $param2, $param3); // 'I love pizza', 'foo', 'bar, 'baz'

    echo 'I have a message: ' . $tag; // I have a message: I love pizza
}
```

### Real world example with WooCommerce

Sometimes in **WooCommerce** we find very long lines of code:

```php
echo '<li class="woocommerce-notice woocommerce-notice--info woocommerce-info">' . apply_filters(
    'woocommerce_no_available_payment_methods_message',
    WC()->customer->get_billing_country()
        ? esc_html__('Sorry, it seems that there are no available payment methods for your state. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce')
        : esc_html__('Please fill in your details above to see available payment methods.', 'woocommerce')
) . '</li>';
```

In **Twig**, you can do it like this:

```twig
<li class="woocommerce-notice woocommerce-notice--info woocommerce-info">
    {{ customer.get_billing_country()
        ? __('Sorry, it seems that there are no available payment methods for your state. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce')
        : __('Please fill in your details above to see available payment methods.', 'woocommerce')
        |apply_filters('woocommerce_no_available_payment_methods_message')
    }}
</li>
```

And with the `filter` tag, it would look like this:

```twig
<li class="woocommerce-notice woocommerce-notice--info woocommerce-info">
    {% apply apply_filters('woocommerce_no_available_payment_methods_message') %}
        {% if customer.get_billing_country() %}
            {{ __('Sorry, it seems that there are no available payment methods for your state. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce') }}
        {% else %}
            {{ __('Please fill in your details above to see available payment methods.', 'woocommerce')  }}
        {% endif %}
    {% endapply %}
</li>
```

## Using Twig vars in live type

Imagine a scenario where you have a text input for a footer message in the WordPress admin panel that your editor users can edit:

```
Copyright {{ year }} by Timber Corporation, Ltd. All Rights Reserved
```

But on the website itself, you want it to render as:

```
Copyright 2022 by Timber Corporation, Ltd. All Rights Reserved
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
* Emacs – [Web Mode](https://web-mode.org/)
* Geany – Add [Twig/Symfony2 detection and highlighting](https://wiki.geany.org/howtos/geany_and_django#twigsymfony2_support)
* PhpStorm – Built in coloring and code hinting. The Twig extension is recognized and has been for some time. [Twig Details for PhpStorm](https://blog.jetbrains.com/phpstorm/2013/06/twig-support-in-phpstorm/).
* Atom – Syntax highlighting with the [Atom Component](https://atom.io/packages/php-twig).

### WordPress tools

* [Lisa Templates](https://github.com/pierreminik/lisa-templates/) – allows you to write Twig-templates in the WordPress Admin that renders through a shortcode, widget or on the_content hook.

### Other

* [Watson-Ruby](https://nhmood.github.io/watson-ruby/) – An inline issue manager. Put tags like `[todo]` in a Twig comment and find it easily later. Watson supports Twig as of version 1.6.3.

### JavaScript

* [Twig.js](https://github.com/justjohn/twig.js) – Use those `.twig` files in the Javascript and AJAX components of your site.
* [Nunjucks](https://mozilla.github.io/nunjucks/) – Another JS template language that is also based on [Jinja2](https://jinja.pocoo.org/docs/)
