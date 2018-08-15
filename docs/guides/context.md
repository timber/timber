---
title: "Context"
menu:
  main:
    parent: "guides"
---

The context is one of the most important concepts to understand in Timber. Think of the context as the set of variables that are passed to your Twig template.

In the following example, `$data` is an array of values. Each of the values will be available in the Twig template with the key of the value as a variable name.

**single.php**

```php
<?php

$data = array(
    'message' => 'This can be any variable you want',
    'author'  => 'Tom',
);

Timber::render( 'single.twig', $data );
```

**single.twig**

```twig
<h3>Message by {{ author }}</h3>
<p>{{ message }}</p>
```

Of course you don’t have to figure all the variables you need for yourself. Timber will provide you with a set of useful variables when you call `Timber::context()`. 

**single.php**

```php
<?php

$context = Timber::context();

Timber::render( 'single.twig', $context );
```

Follow this guide to get an overview of what’s in the context, or use `var_dump( $context );` in PHP or `{{ dump() }}` in Twig to display the contents right on your site.

## Global context

The global context is the context that is always set when you load it through `Timber::context()`.

### Global context variables

#### site

The `site` variable is a `Timber\Site` object which will make it easier for you to retrieve site info. If you’re used to using `blog_info( 'sitename' )` in PHP, you can use `{{ site.name }}` in Twig instead.

#### request

The `request` variable is a `Timber\Request` object, which will make it easier for you to access `$_GET` and `$_POST` variables in your context. Please be aware that you should always be very careful about using `$_GET` and `$_POST` variables in your templates directly. Read more about this in the [Escaping Guide]().

#### theme

The `theme` variable contains useful info about your theme.

#### user

The `user` variable will be a `Timber\User` object if a user/visitor is currently logged in and otherwise it will just be `false`. This will make it possible for you to check if a user is logged by checking for `user` instead of calling `is_user_logged_in()` in your Twig templates:

```twig
{% if user %}
	Hello {{ user.name }}
{% endif %}
```

#### http_host

…

#### wp_title

…

#### body_class

…

### Hook into the global context

In your theme, you probably have elements that you use on every page, like a navigation menu, or a postal address or phone number. You don’t want to set these variables every time you call `Timber::render()` or `Timber::compile()`. And you don’t have to! You can use the `timber/context` filter to add your own data that will always be available.

Here’s an example for how you could **add a navigation menu** to your context, so that it becomes available in every template you use:

```php
// Example: Add a menu to the global context.
add_filter( 'timber/context', function( $context ) {
    $context['menu'] = new Timber\Menu( 'primary-menu' );

    return $context;
} );
```

For menus to work, you will first need to [register them](https://codex.wordpress.org/Navigation_Menus).

## Template contexts

The context can change based on what template is displayed. Timber will always set `post` or `posts` in the context, if it can. But sometimes this is not what you want. Sometimes you need to change some arguments when fetching posts, or write your own posts query. In that case, it’s important to tell Timber about your change. Otherwise it will perform a separate query in the back, which might affect the performance of your page load. Since version 2.0 of Timber, you have more control over this process.

### post

The `post` variable will be available in singular templates ([is_singular()](https://developer.wordpress.org/reference/functions/is_singular/)), like posts or pages. It will contain a `Timber\Post` object of the currently displayed post.

This means that the most basic singular template might look like this:

**single.php**

```php
<?php

Timber::render( 'single.twig', Timber::context() );
```

If you want to use a different post than the default post, you can do that with the `post` parameter. In the following example, we’re passing a post ID directly.

**single.php**

```php
<?php

$context = Timber::context( array(
    'post' => 85,
) );

Timber::render( 'single.twig', $context );
```

Of course you can pass in a `Timber\Post` object instead of just an ID, too.

**single.php**

```php
<?php
$context = Timber::context( array(
    'post' => new Timber\Post( 85 ),
) );

Timber::render( 'single.twig', $context );
```

This is also the way to go if you want to use [your own post class](https://timber.github.io/docs/guides/extending-timber/). In that case, you could pass a post object directly.

**single.php**

```php
<?php
$context = Timber::context( array(
    'post' => new Extended_Post(),
) );

Timber::render( 'single.twig', $context );
```

### posts

The `posts` variable will be available in archive templates ([is_archive()](https://developer.wordpress.org/reference/functions/is_archive/)), like your posts index page, category or tag archives, date based or author archives. It will contain the posts that are fetched with `Timber\PostQuery` with the arguments of WordPress’ default query.

#### Change arguments for default query

You can change arguments for the default query that WordPress will use to fetch posts by passing a `posts` argument array to the `context()` function. You might be used to using the [`pre_get_posts` filter](https://developer.wordpress.org/reference/hooks/pre_get_posts/), but here’s a way that might be more convenient for you.

For example, if you’d want to change the default query to only show pages that have no parents, you can set the `post_parent` argument:

**archive.php**

```php
$context = Timber::context( array(
    'posts' => array(
        'post_parent' => 0,
    ),
) );

Timber::render( 'archive.twig', $context );
```

You `$context` will then contain a `posts` entry with a posts collection fetched by the default query.

#### Overwrite default query

To **overwrite the default query** that WordPress uses, there’s a parameter `cancel_default_query` that you can use in combination with the arguments array that you pass to `context()`:

**archive.php**

```php
// Cancel default query for a small performance improvement
$context = Timber::context( array(
    'cancel_default_query' => true,
) );

// Write your own query
$context['posts'] = new Timber\PostQuery( array(
    'post_type'      => 'book',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
) );

Timber::render( 'archive.twig', $context );
```

Now, to make it easier to write, you can also pass in the arguments directly through the `posts` parameter.

**archive.php**

```php
Timber::render( 'archive.twig', Timber::context( array(
    'cancel_default_query' => true,
    'posts'                => array(
        'post_type'      => 'book',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ),
) ) );
```

If `cancel_default_query` is set to `true`, the arguments that you pass in `posts` will be used to perform a query that will then be present in `$context['posts']`.

### Disable template contexts

Automatic template contexts might not be what you want. In that case, you can disable them by using the `timber/context/args` filter:

**functions.php**

```php
// Disable `post` and `posts` in context
add_filter( 'timber/context/args', function( $args ) {
	$args['post'] = false;
	$args['posts'] = false;

	return $args;
} );
```

You can also directly pass `false` to either the `post` and/or the `posts` parameter when you call `context()`:

**single.php**

```php
$context = Timber::context( array(
    'post'  => false,
) );
```

**archive.php**

```php
$context = Timber::context( array(
    'posts' => false,
) );
```

## Context cache

Sometimes you also need your context in other places, for example when you compile the template for a shortcode:

```php
/**
 * Shortcode for address inside a WYSIWG field
 */
add_shortcode( 'company_address', function() {
    return Timber::compile( 'shortcode/company-address.twig', Timber::context_global() );
} );
```

Inside `shortcode/company-address.twig`, you will then have access to all the global variables that you can use in your normal template files as well. When you only need the global context, you can use the `Timber::context_global()` function. You can call that function multiple times without losing performance.

Timber will cache the global context for you, but it won’t cache template contexts. If you’re using the `timber/context` filter, make sure you run it before you call `Timber::context()` for the first time. Otherwise, the cache will be set before you could add your own data.
