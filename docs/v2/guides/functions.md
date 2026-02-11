---
title: "Functions"
order: "800"
---

Timber makes it easy to work with PHP functions in your Twig templates. You have three options:

1. **Use built-in Timber functions** - Timber provides a set of functions for common tasks like retrieving posts, terms, users, and rendering content.
2. **Call any PHP function with `function()`** - Need to use a custom or WordPress function that isn't pre-registered? Use the `function()` helper to call it directly from your templates.
3. **Register your own functions** - For better performance and cleaner templates, you can register your custom functions to be directly available in Twig, just like the built-in ones.

Let's explore each approach.

## Available Built-in Functions

Timber provides a wide range of built-in functions that are directly available in Twig templates without any additional setup.

### Content Retrieval Functions

These functions allow you to fetch posts, terms, users, and comments:

- `get_post()` - Retrieve a single post
- `get_posts()` - Retrieve multiple posts
- `get_image()` - Retrieve an image
- `get_external_image()` - Retrieve an external image
- `get_attachment()` - Retrieve an attachment
- `get_attachment_by()` - Retrieve attachment by specific criteria
- `get_term()` - Retrieve a single term
- `get_terms()` - Retrieve multiple terms
- `get_user()` - Retrieve a single user
- `get_users()` - Retrieve multiple users
- `get_comment()` - Retrieve a single comment
- `get_comments()` - Retrieve multiple comments

### Utility Functions

- `action()` - Execute WordPress actions, see [Twig – WordPress Actions](https://timber.github.io/docs/v2/guides/twig/#wordpress-actions)
- `shortcode()` - Process WordPress shortcodes via `do_shortcode()`
- `bloginfo()` - Get WordPress site information

### Translation Functions

Timber supports multilingual sites and provides functions to facilitate translation in Twig templates. You can read more about these functions in the [internationalization Guide](https://timber.github.io/docs/v2/guides/internationalization/):

- `__()` - Translate text
- `translate()` - Translate text
- `_e()` - Echo translated text
- `_n()` - Translate with plural support
- `_x()` - Translate with context
- `_ex()` - Echo translated text with context
- `_nx()` - Translate with plural and context support
- `_n_noop()` - Register plural strings for translation
- `_nx_noop()` - Register plural strings with context for translation
- `translate_nooped_plural()` - Translate plural strings registered with `_n_noop()` or `_nx_noop()`

## Calling Any PHP Function with `function()`

If you need to call a PHP function that isn't available as a built-in Timber function, you can use the `function()` helper. This allows you to call any PHP function directly from your Twig templates.

For example, if you need to call `wp_head()` and `wp_footer()`:

```twig
{# single.twig #}
<html>
    <head>
    <!-- Add whatever you need in the head, and then...-->
    {{ function('wp_head') }}
    </head>

    <!-- etc... -->

    <footer>
        Copyright &copy; {{ "now"|date('Y') }}
    </footer>
    {{ function('wp_footer') }}
    </body>
</html>
```

You can also use `fn('my_function')` as an alias for `function('my_function')`.

### Passing Arguments

To pass arguments to a function, add them as additional arguments after the function name:

```twig
{# single.twig #}
<div class="embed-link">
    {{ function('get_post_embed_url', post.id) }}
</div>
```

**Important note about context:** While this works in a `single.twig` file that retains the context of the current post, it may not work the same way in The Loop (like in `archive.twig` or `index.twig`). Functions like `get_post_embed_url` try to guess the post ID from the current post context. In archive or index templates, you need to explicitly pass the post ID:

```twig
{# index.twig #}
<div class="embed-link">
    {{ function('get_post_embed_url', post.ID) }}
</div>
```

## Register Your Own Functions in Twig

For better performance and cleaner template syntax, you can register your custom functions to be directly available in Twig. This is preferable to using `function()` for functions you use frequently.

Check out the [Extending Twig Guide](https://timber.github.io/docs/v2/guides/extending-twig/) to learn how to make your own functions available in Twig.

## Handling Functions That Echo Output

The concept of Timber (and templating engines like Twig in general) is to prepare all the data before you pass it to a template. Some functions in WordPress echo their output directly, which can cause issues since the output would appear before your template is rendered.

There are two ways to work around this:

1. **Using `Helper::ob_function`** - If you want to capture the output as a string to use in your context, use [`Helper::ob_function`](https://timber.github.io/docs/v2/reference/timber-helper/#ob_function).
2. **Using `FunctionWrapper`** - If a function needs to be called exactly where you use it in your template (because it depends on certain global values), use `FunctionWrapper`:

```php
$context['my_custom_function'] = new FunctionWrapper('my_custom_function', $array_of_arguments);
```
