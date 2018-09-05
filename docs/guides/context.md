---
title: "Context"
menu:
  main:
    parent: "guides"
---

The context is one of the most important concepts to understand in Timber. Think of the context as the set of variables that are passed to your Twig template.

In the following example, `$data` is an associative array of values. Each of the values will be available in the Twig template with the key as a variable name.

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

Of course you don’t have to figure out all the variables you need for yourself. Timber will provide you with a set of useful variables when you call `Timber::context()`. 

**single.php**

```php
$context = Timber::context();

Timber::render( 'single.twig', $context );
```

Follow this guide to get an overview of what’s in the context, or use `var_dump( $context );` in PHP or `{{ dump() }}` in Twig to display the contents in your browser.

## Global context

The global context is the context that is always set when you load it through `Timber::context()`.

### Global context variables

Among others, the following variables will be available:

- **site** – The `site` variable is a [Timber\Site](/docs/reference/timber-site/) object which will make it easier for you to retrieve site info. If you’re used to using `blog_info( 'sitename' )` in PHP, you can use `{{ site.name }}` in Twig instead.
- **request** - The `request` variable is a `Timber\Request` object, which will make it easier for you to access `$_GET` and `$_POST` variables in your context. Please be aware that you should always be very careful about using `$_GET` and `$_POST` variables in your templates directly. Read more about this in the Escaping Guide.
- **theme** - The `theme` variable is [Timber\Theme](/docs/reference/timber-theme/) object and contains info about your theme.
- **user** - The `user` variable will be a [`Timber\User`](/docs/reference/timber-user/) object if a user/visitor is currently logged in and otherwise it will just be `false`.

For a full list of variables, go have a look at the reference for [`Timber::context()`](/docs/reference/timber-timber/#context).

### Hook into the global context

In your theme, you probably have elements that you use on every page, like a navigation menu, or a postal address or phone number. You don’t want to add these variables to the context every time you call `Timber::render()` or `Timber::compile()`. And you don’t have to! You can use the `timber/context` filter to add your own data that will always be available.

Here’s an example for how you could **add a navigation menu** to your context, so that it becomes available in every template you use:

**functions.php**

```php
// Example: Add a menu to the global context.
add_filter( 'timber/context', function( $context ) {
    $context['menu'] = new Timber\Menu( 'primary-menu' );

    return $context;
} );
```

For menus to work, you will first need to [register them](https://codex.wordpress.org/Navigation_Menus).

## Context cache

The global context will be cached. That’s why you need to define your `timber/context` filter before using `Timber::context()` for the first time. Otherwise, the cache will be set before you could add your own data. Having a cached global context can be useful if you need the context in other places. For example if you compile the template for a shortcode:

```php
/**
 * Shortcode for address inside a WYSIWG field
 */
add_shortcode( 'company_address', function() {
    return Timber::compile(
        'shortcode/company-address.twig',
        Timber::context()
    );
} );
```

Inside `shortcode/company-address.twig`, you will have access to all the global variables that you can use in your normal template files as well. You can call that function multiple times without losing performance.

## Singular templates

For singular templates, it’s common to have a `post` variable in your context that contains the currently displayed post.

**single.php**

```php
$context = Timber::context();

$post = new Timber\Post();
$post->setup();

$context['post'] = $post;

Timber::render( 'single.twig', $context );
```

By calling `new Timber\Post()` without any arguments, Timber will fetch the post for the current singular template. 

**To setup a post in a singular template, you should use `$post->setup()`**. This function improves compatibility with third party plugins.

## Archive templates

For archive templates, it’s common to have a `posts` variable that contains a collection of posts for the current archive, like your posts index page, category or tag archives, date based or author archives.

### Use the default query

```php
$context          = Timber::context();
$context['posts'] = new Timber\PostQuery();

Timber::render( 'archive.twig', $context );
```

### Write your own query

When you don’t need the default query, you can pass in your own arguments to `Timber\PostQuery()`.

```php
$context          = Timber::context();
$context['posts'] = new Timber\PostQuery( array(
    'post_type'      => 'book',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
) );
```

### Change arguments for default query

Sometimes you don’t want to use the default query, but only change a little thing. You can change arguments for the default query that WordPress will use to fetch posts by using the `Timber\PostQuery::merge_default()` function. For example, if you’d want to change the default query to *only show pages that have no parents*, you could pass in a `post_parent` argument:

**archive.php**

```php
$context          = Timber::context();
$context['posts'] = Timber\PostQuery::merge_default( array(
    'post_parent' => 0,
) );

Timber::render( 'archive.twig', $context );
```

The function `Timber\PostQuery::merge_default()` will return a `Timber\PostQuery()` object that contains a posts collection fetched by the default query. This is practically the same as using [`pre_get_posts` filter](https://developer.wordpress.org/reference/hooks/pre_get_posts/) in a default WordPress project, but maybe a little more convenient.
