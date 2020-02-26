---
title: "Posts"
---

To get a post object in Timber, you use `Timber::get_post()` and pass the post ID as an argument.

```php
$post = Timber::get_post( $post_id );
```

This function is similar to [`get_post()`](https://developer.wordpress.org/reference/functions/get_post/) and accepts one argument: a post ID. If you don’t pass in any argument, Timber will use `get_queried_object()` to try an work with the currently queried post.

```php
$post = Timber::get_post( get_queried_object_id() );
```

What you get in return is a [`Timber\Post`](https://timber.github.io/docs/reference/timber-post/) object, which is similar to `WP_Post`. This object provides you with functions and properties for pretty much everything you need for developing theme templates.

Here’s a Twig template that received the post above in a `$post` variable.

```twig
<article class="article post-type-{{ post.type }}" id="post-{{ post.ID }}">
    <section class="article-content">
        <h1 class="article-h1">{{ post.title }}</h1>
        <h2 class="article-h2">{{ post.meta('subtitle') }}</h2>

        <p class="article-author">
            <span>By</span> {{ post.author.name }} <span>&bull;</span> {{ post.date }}
        </p>

        {{ post.content }}
    </section>
</article>
```

## Twig

You can convert post IDs to post objects in Twig using the `Post()` function.

```twig
{% set post = Post(post_id) %}
```

This is especially helpful if you only have the image ID and want to convert it to an image:

```twig
<img src="{{ get_image(attachment_id).src }}">
```

It also works if you have an array of post IDs that you want to convert to `Timber\Post` objects.

```twig
{% for post in Post(post_ids) %}
```

## Invalid posts

If no valid post can be found with the post ID you provided, the `Timber::get_post()` function will return `null`. With this, you can always check for valid posts with a simple if statement.

```php
$post = Timber::get_post( $post_id );

if ( $post ) {

}
```

Or in Twig:

```twig
{% if post %}
    {{ post.title }}
{% endif %}
```

## Extending `Timber\Post`

If you need additional functionality that the `Timber\Post` class doesn’t provide or if you want to have cleaner Twig templates, you can extend the `Timber\Post` class with your own classes:

```php
class Book extends Timber\Post {

}
```

To initiate your new `Book` post, you also use `Timber::get_post()`.

```php
$post = Timber::get_post( $post_id );
```

You **can’t** instantiate a `Timber\Post` object or an object that extends this class with a constructor – you can’t use `$post = new Book( $post_id )`. In Timber, we’ve chosen to go a different way to prevent a lot of problems that would come with direct instantiation.

So, how does Timber know about your `Book` class? Timber will use the [Post Class Map](https://timber.github.io/docs/guides/class-maps/#the-post-class-map) to sort out which class it should use.

## Querying Posts

If you want to get a collection of posts, you can use `Timber::get_posts()`.

```php
$posts = Timber::get_posts( [
    'query' => $query,
] );
```

You can use this function in a similar way to how you use [`WP_Query`](https://developer.wordpress.org/reference/classes/wp_query/). If you don’t pass in any argument, Timber will use the global query.

```php
// Using the WP_Query argument format.
$posts_query = new Timber::get_posts( [
    'query' => [
        'post_type'     => 'article',
        'category_name' => 'sports',
    ],
 ] );
```

Be aware that you need pass an array to `Timber::get_posts()` with a `query` parameter. This array can have additional arguments that you can check out in the documentation for [`Timber::get_posts()`](https://timber.github.io/docs/reference/timber/#get-posts).

What you get as a return value is not a pure array of posts, but a `Timber\PostCollection` object, which is an `ArrayObject` that is very similar to an array like you know it. To loop over the posts collection in PHP, you first need to convert it to an array with `$posts->get_posts()`.

```php
foreach ( $posts_query->get_posts() as $post ) {
    echo $post->title();
}
```

In Twig, you can directly loop over it.

```twig
{% for post in posts %}
    {{ post.title }}    
{% endfor %}
```

When you query for certain post types, Timber will use the [Post Class Map](https://timber.github.io/docs/guides/class-maps/#the-post-class-map) to check which class it should use to instantiate your posts.

### The difference to `get_post()`

It might seem like `Timber::get_posts()` is the same as [`get_posts()`](https://developer.wordpress.org/reference/functions/get_posts/) in WordPress. But it isn’t. It’s the same as using [`WP_Query`](https://developer.wordpress.org/reference/classes/wp_query/). The difference in the `get_posts()` function is that it uses different parameters and looks like this when using `Timber::get_posts()`.

```php
$posts = Timber::get_posts( array(
    'query' => array(
        'ignore_sticky_posts' => true,
        'suppress_filters'    => true,
        'no_found_rows'       => true,
    ),
) );
```

If you’re used to using `get_posts()` instead of `WP_Query`, you will have to set these parameters separately in your queries.

## Performance

### Consider using the `pre_get_posts` action

Data will always be fetched from the database if you

- Use `Timber::get_posts()` with a `query` argument
- Use `Timber::get_post()` with an argument.

These two cases are always a performance hit. As long as you don’t change any query parameters, Timber will use the default query that is already set up and no additional database query will be run.

If you care about performance and want to change the query that WordPress runs before deciding which template file it will use, you need to use the [`pre_get_posts`](https://developer.wordpress.org/reference/hooks/pre_get_posts/) action.

### Count rows only if needed

Whenever you query for a collection of posts, but you don’t need pagination for them, you should set `no_found_rows` to `true`.

```php
$posts = Timber::get_posts( array(
    'query' => array(
        'no_found_rows' => true,
    ),
) );
```

In the back, the query will not count the number of found rows. This can result in [better performance](https://kinsta.com/blog/wp-query/) if you have a large number of posts.

## Using posts or post collections in the context

Timber will automatically set the `post` or `posts` variable for you in the context depending on the template file you’re using. Read more about this in the [Context Guide](https://timber.github.io/docs/guides/context/#template-contexts).
