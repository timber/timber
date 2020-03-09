---
title: "Theming"
---

…

WordPress wants you to interact with its API in a certain way, which sucks. Because soon you get things like:

```html
<h1 class="article-h1"><a href="<?php get_permalink(); ?>"><?php the_title(); ?></a></h1>
```

Okay, not _too_ terrible, but doesn’t this (Timber) way look so much nicer?

```twig
<h1 class="article-h1"><a href="{{ post.link }}">{{ post.title }}</a></h1>
```

---



What if you want to **add** to the block as opposed to replace? No prob, just call `{{ parent() }}` where the parent’s content should go.

## The index page

Let’s crack open **index.php** and see what’s inside:

```php
<?php
$context = Timber::context();
$context['posts'] = new Timber\PostQuery();
Timber::render( 'index.twig', $context );
```

This is where we are going to handle the logic that powers our index file. Let’s go step-by-step.


## How to use Timber\PostQuery

### Use a WP_Query array

```php
<?php
$args = array(
    'post_type' => 'post',
    'tax_query' => array(
        'relation' => 'AND',
        array(
            'taxonomy' => 'movie_genre',
            'field' => 'slug',
            'terms' => array( 'action', 'comedy' )
        ),
        array(
            'taxonomy' => 'actor',
            'field' => 'id',
            'terms' => array( 103, 115, 206 ),
            'operator' => 'NOT IN'
        )
    )
);

$context['posts'] = new Timber\PostQuery( $args );
```

You can find all available options in the documentation for [WP_Query](http://codex.wordpress.org/Class_Reference/WP_Query).

### Use a WP_Query string

```php
<?php
$args = 'post_type=movies&numberposts=8&orderby=rand';

$context['posts'] = new Timber\PostQuery( $args );
```

### Use Post ID numbers

```php
<?php

$ids = array( 14, 123, 234, 421, 811, 6 );

$context['posts'] = new Timber\PostQuery( $ids );
```

## Render

```php
<?php

Timber::render( 'index.twig', $context );
```

We’re now telling Twig to find **index.twig** and send it our data object.

