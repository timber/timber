---
title: "Posts"
order: "110"
---

To get a post object in Timber, you use `Timber::get_post()` and pass the WordPress post ID as an argument.

```php
$post = Timber::get_post( $post_id );
```

This function is similar to [`get_post()`](https://developer.wordpress.org/reference/functions/get_post/) and accepts one argument: a post ID. If you don’t pass in any argument, Timber will use `get_queried_object()` to try and work with the currently queried post.

```php
$post = Timber::get_post();

// Is the same as…

$post = Timber::get_post( get_queried_object_id() );
```

What you get in return is a [`Timber\Post`](https://timber.github.io/docs/v2/reference/timber-post/) object, which is similar to `WP_Post`. This object provides you with functions and properties for pretty much everything you need for developing theme templates.

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

This is especially helpful if you only have an image ID and want to convert it to an image:

```twig
<img src="{{ Image(attachment_id).src }}">
```

It also works if you have an array of post IDs that you want to convert to `Timber\Post` objects.

```twig
{% for post in Post(post_ids) %}

{% endfor %}
```

## Invalid posts

If no valid post can be found with the post ID you provided, the `Timber::get_post()` function will return `null`. With this, you can always check for valid posts with a simple if statement.

```php
$post = Timber::get_post( $post_id );

if ( $post ) {
    // Handle post.
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
$book = Timber::get_post( $post_id );
```

You **can’t** instantiate a `Timber\Post` object or an object that extends this class with a constructor – you can’t use `$post = new Book( $post_id )`. In Timber, we’ve chosen to go a different way to prevent a lot of problems that would come with direct instantiation.

So, how does Timber know about your `Book` class? Timber will use the [Post Class Map](https://timber.github.io/docs/v2/guides/class-maps/#the-post-class-map) to sort out which class it should use.

## Querying Posts

If you want to get a collection of posts, you can use `Timber::get_posts()`.

```php
$posts = Timber::get_posts( $query );
```

You can use this function similarly to how you use [`WP_Query`](https://developer.wordpress.org/reference/classes/wp_query/). If you don’t pass in any argument, Timber will use the global query.

```php
// Use the global query.
$posts = Timber::get_posts();

// Using the WP_Query argument format.
$posts = Timber::get_posts( [
    'post_type'     => 'article',
    'category_name' => 'sports',
] );
```

The `Timber::get_posts()` function accepts a second parameter with options for the query. For example, with the `merge_default` option you can tell Timber that it should merge your query parameters with the default query parameters of the current template. You can check out `merge_default` and all the other options in the documentation for [`Timber::get_posts()`](https://timber.github.io/docs/v2/reference/timber/#get-posts).

 ```php
$posts = Timber::get_posts( $query, $options );
```

### The default query

In archive templates like **archive.php** or **category.php**, Timber will already fetch the default query when you call `Timber::context()` and make it available under the `posts` entry. Read more about this in the [Context Guide](https://timber.github.io/docs/v2/guides/context).

### Class Map

When you query for certain post types, Timber will use the [Post Class Map](https://timber.github.io/docs/v2/guides/class-maps/#the-post-class-map) to check which class it should use to instantiate your posts.

### Post Collections

The analogous `Timber` methods for getting Users, Terms, and Comments (`::get_users()`, `::get_terms()`, and `::get_comments()`) all return arrays. But to make pagination work and making Timber compatible with The Loop, we treat Posts with special care.

What you get as a **return value** when running `Timber::get_posts()` is not a pure array of posts, but a `Timber\PostCollection` object, an [`ArrayObject`](https://www.php.net/manual/en/class.arrayobject.php) that is very similar to an array as you know it. That means you can still loop over a `PostCollection` directly:

```php
$posts = Timber::get_posts(/* optional args */);
foreach ( $posts as $post ) {
    echo $post->title();
}
```

In Twig, you can also loop over the collection.

```twig
{% for post in posts %}
    {{ post.title }}
{% endfor %}
```

What **doesn’t work** with `Timber\PostCollection` objects are PHP’s [Array functions](https://www.php.net/manual/en/ref.array.php) like `array_filter()` or WordPress helper functions like [`wp_list_filter()`](https://developer.wordpress.org/reference/functions/wp_list_filter/). If you want to work with those, you can turn a `Timber\PostCollection` object into a pure array with `to_array()`. But be aware that when you do that, you lose the pagination functionality and compatibility optimizations with The Loop.

```php
$filtered = wp_list_filter( $posts->to_array(), [
    'comment_status' => 'open'
] );
```

### Differences from WP core’s `get_posts()`

It might seem like `Timber::get_posts()` is the same as [`get_posts()`](https://developer.wordpress.org/reference/functions/get_posts/) in WordPress. But it isn’t. It’s more similar to using [`WP_Query`](https://developer.wordpress.org/reference/classes/wp_query/). WP core’s `get_posts()` function applies different default parameters and performs the same database query as it would when calling `Timber::get_posts()` like this:

```php
$posts = Timber::get_posts( [
    'ignore_sticky_posts' => true,
    'suppress_filters'    => true,
    'no_found_rows'       => true,
] );
```

If you’re used to using `get_posts()` instead of `WP_Query`, you will have to set these parameters separately in your queries.

Of course, the other main difference is that instead of returning plain `WP_Post` objects, `Timber::get_posts()` returns instances of `Timber\Post`.

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
    'no_found_rows' => true,
) );
```

In the back, the query will not count the number of found rows. This can result in [better performance](https://kinsta.com/blog/wp-query/) if you have a large number of posts.

## Using posts or post collections in the context

Timber will automatically set the `post` or `posts` variable for you in the context depending on the template file you’re using. Read more about this in the [Context Guide](https://timber.github.io/docs/v2/guides/context/#template-contexts).
