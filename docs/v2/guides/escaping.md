---
title: "Escaping"
order: "400"
---

Escaping describes the practice of securing output before rendering it for the end user of your website. Data in WordPress comes from all sorts of places and a general mindset for development is: **Don’t trust any data.**

## Escaping in Timber

While Twig has escaping enabled by default, **Timber’s Twig does not escape** the output of standard tags (i.e. `{{ post.field }}`).

If you want to enable Twig’s `autoescape` behavior, you can enable it with the `timber/twig/environment/options` filter:

**functions.php**

```php
add_filter('timber/twig/environment/options', function ($options) {
    $options['autoescape'] = 'html';

    return $options;
});
```

## Why should I escape?

In terms of security, developing a Timber theme is no different than developing a normal WordPress theme. It’s important that you develop a security mindset:

- Don’t trust any data.
- Never assume anything.
- Escape as late as possible.
- Sanitation is okay, but validation/rejection is better.

You can read more about the basics of [theme security](https://developer.wordpress.org/themes/advanced-topics/security/) and [how to escape your output in WordPress](https://developer.wordpress.org/apis/security/escaping/) in the WordPress Developer Resources.


## Escapers

Twig offers a variety of [escaping functions](https://twig.symfony.com/doc/filters/escape.html) out of the box. They are intended to escape a string for safe insertion into the final output.

In addition to these standard escaping functions, Timber comes with some valuable custom escapers for your WordPress theme. To use the escaper (see documentation link above), you pipe your content through a function `e` and if you want to use a custom escaper, you would supply an argument to the function, e.g. `e('wp_kses_post')`.

## wp_kses_post

KSES is a recursive acronym for `KSES Kills Evil Scripts`. It’s goal is to ensure only "allowed" HTML element names, attribute names and attribute values plus only sane HTML entities in the string. Allowed means based on a configuration.

The `wp_kses_post` escaper uses the internal WordPress function [`wp_kses_post()`](https://developer.wordpress.org/reference/functions/wp_kses_post/) that sanitizes content for allowed HTML tags for the post content. The configuration used can be found by running ` wp_kses_allowed_html( 'post' );`.

**Twig**

```twig
<p class="intro">{{ post.post_content|wp_kses_post }}</p>
```

In this example, `post.post_content` contains the following string:

```
<div foo="bar" src="bum">Foo</div><script>DoEvilThing();</script>
```

**Output**

```html
<div>Foo</div>DoEvilThing();
```

## esc_url

Uses WordPress’ internal [`esc_url`](https://developer.wordpress.org/reference/functions/esc_url/) function on a text. This should be used to sanitize URLs.

**Twig**

```twig
<a href="{{ post.meta('custom_link')|esc_url }}"></a>
```

**Output**

```html
<a href="https://google.com"></a>
```

## esc_html

Escaping for HTML blocks. Converts any potentially conflicting HTML entities to their encoded equivalent to prevent them from being rendered as markup by the browser, e.g. converts `<` to `&lt;` and double quotes `"` to `$quot;`.

This is for plain old text. If your content has HTML markup, you should not use `esc_html`, which will render the HTML as it looks in your code editor. To preserve the HTML you will want to use `wp_kses_post`.

**Twig**

```twig
<div class="equation">{{ post.meta('equation')|esc_html }}</div>
```

**Output**

```html
<div class="equation">is x &lt; y?</div>
```

## esc_attr

Escaping for HTML attributes. Encodes the <, >, &, ” and ‘ (less than, greater than, ampersand, double quote and single quote) characters. Will never double encode entities.
Always use when escaping HTML attributes (especially form values) such as alt, value, title, etc.

**Twig**

```twig
<input type="text" name="name" value="{{ user.name|esc_attr }}">
```

**Output**

```html
<input type="text" name="name" value="Han Solo">
```

## esc_js

Escapes text strings for echoing in JavaScript. It is intended to be used for inline JavaScript (in a tag attribute, for example `onclick="…"`). Note that the strings have to be in single quotes. The WordPress filter `js_escape` will also be applied here.

**Twig**

```twig
<script>var bar = '{{ post.meta('name')|esc_js }}';</script>
```

**Output**

```html
<script>var bar = 'Gabrielle';</script>
```
