---
title: "Cheatsheet"
order: "1500"
---

Here are some helpful conversions for functions you’re probably well familiar with in WordPress and their Timber equivalents. These assume a PHP file with the `Timber::context();` function at the top. For example:

```php
$context = Timber::context();

Timber::render('single.twig', $context);
```

## Blog Info
* `blog_info('charset')` => `{{ site.charset }}`
* `blog_info('description')` => `{{ site.description }}`
* `blog_info('sitename')` => `{{ site.name }}`
* `blog_info('url')` => `{{ site.url }}`


## Body Class
* `implode(' ', get_body_class())` => `<body class="{{ body_class }}">`


## Post
* `the_content()` => `{{ post.content }}`
* `the_permalink()` => `{{ post.link }}`
* `the_title()` => `{{ post.title }}`
* `get_the_tags()` => `{{ post.tags }}`


## Theme
* `get_template_directory_uri()` => `{{ theme.uri }}` Template directory URI for the active (parent) theme (ex: `http://example.org/wp-content/themes/my-timber-theme`)
* `get_template_directory_uri()` => `{{ theme.parent.link }}` Explicitly return directory uri of parent theme (ex: `http://example.org/wp-content/themes/my-timber-parent-theme`)
* `get_stylesheet_directory_uri()` => `{{ theme.link }}` Template directory URI for the active (child) theme (ex: `http://example.org/wp-content/themes/my-timber-theme`)
* `get_template_directory()` => `{{ theme.parent.path }}` Explicitly return relative directory path of parent theme  (ex: `/wp-content/themes/my-timber-parent-theme`)
* `get_stylesheet_directory()` => `{{ theme.path }}` Relative directory path for the active (child) theme (ex: `/wp-content/themes/my-timber-theme`)

In WordPress parlance, stylesheet_directory = child theme, template directory = parent theme. Both WP and Timber functions safely return the current theme info if there's no parent/child going on.
