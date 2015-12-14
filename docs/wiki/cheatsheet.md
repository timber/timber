# Cheatsheet

Here are some helpful conversions for functions you're probably well familiar with in WordPress and their Timber equivalents. These assume a PHP file with the `Timber::get_context();` function at the top. For example:

```php
$context = Timber::get_context();
$context['post'] = new TimberPost();
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
* `the_permalink()` => `{{ post.permalink }}`
* `the_title()` => `{{ post.title }}`
* `get_the_tags()` => `{{ post.tags }}`


## Theme
* `get_template_directory_uri()` => `{{ theme.link }}` (Parent Themes)
* `get_template_directory_uri()` => `{{ theme.parent.link }}` (Child Themes)
* `get_stylesheet_directory_uri()` => `{{ theme.link }}`
* `get_template_directory()` => `{{ theme.parent.path }}`
* `get_stylesheet_directory()` => `{{ theme.path }}`

In WordPress parlance, stylesheet_directory = child theme, template directory = parent theme. Both WP and Timber functions safely return the current theme info if there's no parent/child going on.


## wp_functions
* `wp_footer()` => `{{ wp_footer }}`
* `wp_head()` => `{{ wp_head }}`
