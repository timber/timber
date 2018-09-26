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

### Context cache

The global context will be cached. That’s why you need to define your `timber/context` filter before using `Timber::context()` for the first time. Otherwise, the cache will be set before you could add your own data. 

Having a cached global context can be useful if you need the context in other places. For example if you compile the template for a shortcode:

```php
/**
 * Shortcode for address inside a WYSIWG field
 */
add_shortcode( 'company_address', function() {
    return Timber::compile(
        'shortcode/company-address.twig',
        Timber::context_global()
    );
} );
```

In this example, we've provided all the global context variables to `shortcode/company-address.twig` via `Timber::context_global()`. Whenever you only need the global context, you should use the `Timber::context_global()` function. You can call that function multiple times without losing performance.

Timber will not cache template contexts.

## Template contexts

When WordPress decides [which PHP template file](https://wphierarchy.com/) it will display, it has already run database queries to fetch posts for archive templates or to set up the `$post` global for singular templates.

When you call `Timber::context()`, Timber will automatically populate your context with a `post` or `posts` variable, depending on which type of template file you’re in.

### Singular templates

The `post` variable will be available in singular templates ([is_singular()](https://developer.wordpress.org/reference/functions/is_singular/)), like posts or pages. It will contain a `Timber\Post` object of the currently displayed post.

**single.php**

```php
$context = Timber::context();

Timber::render( 'single.twig', $context );
```

By calling `new Timber\Post()` without any arguments, Timber will use the `$post` global for the current singular template.

#### Using a custom post class

If you want to use [your own post class](/docs/guides/extending-timber/), you can create an instance of your own class.

```php
$context = Timber::context();

// Using an custom post class
$post = new Extended_Post();
$post->setup();

$context['post'] = $post;

// Or very short
$context['post'] = ( new Extended_Post() )->setup();
```

Whenever you set up **a post in a singular template (instead of relying on `Timber::context()` to do it for you), you need set up your post through `$post->setup()`**. This function improves compatibility with third party plugins.

### Archive templates

The `posts` variable will be available in archive templates ([is_archive()](https://developer.wordpress.org/reference/functions/is_archive/)), like your posts index page, category or tag archives, date based or author archives. It will contain a `Timber\PostQuery` with the posts that WordPress already fetched for your archive page.

#### Use the default query

```php
$context          = Timber::context();

Timber::render( 'archive.twig', $context );
```

#### Write your own query

When you don’t need the default query, you can pass in your own arguments to `Timber\PostQuery()`.

```php
$context          = Timber::context();
$context['posts'] = new Timber\PostQuery( array(
    'query' => array(
        'post_type'      => 'book',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ),
) );
```

#### Change arguments for default query

Sometimes you don’t want to use the default query, but only change a little thing. You can change arguments for the default query that WordPress will use to fetch posts by using the `merge_default` argument. For example, if you’d want to change the default query to *only show posts written by a specific group of authors*, you could pass in a `author__in` argument:

**archive.php**

```php
$context          = Timber::context();
$context['posts'] = Timber\PostQuery( array(
    'query' => array(
        'author__in' => array(1, 6, 14),
    ),
    'merge_default' => true,
) );

Timber::render( 'archive.twig', $context );
```

Timber will accept the parameters that can be found in WordPress’s [WP_Query class](https://codex.wordpress.org/Class_Reference/WP_Query).

### Performance

In any case where you instantiate a new `Timber\PostQuery` with a `query` argument or create a new `Timber\Post()` with an argument, data will be fetched from the database. It’s always a performance hit. As long as you don’t change any query parameters, no additional database query will be run.

If you care about performance and want to change the query that WordPress runs before deciding which template file it will use, you need to use the [`pre_get_posts`](https://developer.wordpress.org/reference/hooks/pre_get_posts/) action.
