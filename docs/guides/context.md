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

Of course you don’t have to figure all the variables you need for yourself. Timber will provide with a set of useful variables when you call `Timber::context()`.

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

The `user` variable will be a `Timber\User` object if a user/visitor is currently logged in and otherwise it will just be `false`.

#### http_host

…

#### wp_title

…

#### body_class

…

### Hook into the global context

In your theme, you probably have elements that you use on every page, like a navigation menu, an address or phone number. You don’t want to set these variables whenever you call `Timber::render()` or `Timber::compile()`. And you don’t have to! You can use the `timber/context` filter to add your own data.

Here’s an example for how you could add a menu to your context, so that it becomes available in every template you use:

```php
add_filter( 'timber/context', function( $context ) {
    // Example: Add a menu to the global context.
    $context['menu'] = new Timber\Menu( 'primary-menu' );

    return $context;
} );
```

For menus to work, you’d first need to [register them](https://codex.wordpress.org/Navigation_Menus).

## Template contexts

The context can change based on what template is displayed. Timber will always set `post` or `posts` in the context, if it can. But sometimes this is not what you want. Sometimes you need to change some arguments when fetching posts, or write your own posts query. In that case, it’s important to tell Timber of your change. Otherwise it will perform a separate query in the back, which might affect performance. Since version 2.0 of Timber, you have more control over this process.

### post

The `post` variable will be available in singular templates ([is_singular()](https://developer.wordpress.org/reference/functions/is_singular/)) like posts or pages. It will contain a `Timber\Post` object of the currently displayed post.

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

Of course you can pass in a `Timber\Post` object, too.

**single.php**

```php
<?php
$post = new Timber\Post( 85 );

$context = Timber::context( array(
    'post' => $post,
) );

Timber::render( 'single.twig', $context );
```

This is also the way to go if you want to use [your own post class](https://timber.github.io/docs/guides/extending-timber/). In that case, you could pass a post object directly.

### posts

The `posts` variable will be available in archive templates ([is_archive()](https://developer.wordpress.org/reference/functions/is_archive/)), like your posts index page, category or tag archives, date based or author archives. It will contain the posts that are fetched with `Timber\PostQuery` with the arguments of the default query.

#### Change arguments for default query

You can change arguments for the default query by passing a `posts` argument to the `context()` function or by using a filter.

For example, if you’d want to change the default query to only show pages that have no parents, you can set the `post_parent` argument.

```php
$context = Timber::context( array(
    'posts' => array(
        'post_parent' => 0,
    ),
) );

Timber::render( 'archive.twig', $context );
```

This way, `$context` will contain a `posts` array.

#### Overwrite default query

To overwrite the default query, there’s a parameter `cancel_default_query` that you can use with the arguments array that you pass to `context()`:

```php
// Cancel default query for a small performance improvement
$context = Timber::context( array(
    'cancel_default_query' => true,
) );

$context['posts'] = new Timber\PostQuery( array(
    'post_type'      => 'book',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
) );

Timber::render( 'archive.twig', $context );
```

Now, to make it easier to write, you can also pass in the arguments directly through the `posts` parameter.

```php
$context = Timber::context( array(
    'cancel_default_query' => true,
    'posts'                => array(
        'post_type'      => 'book',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ),
) );

Timber::render( 'archive.twig', $context );
```

If `cancel_default_query` is set to `true`, the arguments that you pass in `posts` will be used to perform a query that will then be present in `$context['posts']`.

### Disable template contexts

Automatic template contexts might not be what you want. In that case, you can disable them by using the `timber/context/args` filter:

```php
// Disable `post` and `posts` in context
add_filter( 'timber/context/args', function( $args ) {
	$args['post'] = false;
	$args['posts'] = false;

	return $args;
} );
```

You can also directly pass `false` to either the `post` and/or the `posts` parameter when you call `context()`:

```php
$context = Timber::context( array(
    'post'  => false,
    'posts' => false,
) );
```

## Context cache

Sometimes you also need your context in other places, for example when you compile the template for a shortcode:

```php
/**
 * Shortcode for address inside a WYSIWG field
 */
add_shortcode( 'address', function() {
    return Timber::compile( 'shortcode/address.twig', Timber::context() );
} );
```

Inside `shortcode/address.twig`, you will then have access to all the variables that you can use in your normal template files as well. You can call the `context()` function multiple times without losing performance. Timber will cache the context for your first call and will return the cache when you call it again.

- The global context will always be cached. That’s why it’s important that you add your `timber/context` filters before you call `context()` for the first time.
- The template contexts will be cached, except when you pass different parameters than in a previous call.

## timber/context/args filter
