---
title: "Post Types"
order: "126"
---

While [`Timber::get_post()`](https://timber.github.io/docs/v2/guides/posts/) gives you a single post, sometimes you need the post type itself – for example to display the labels you defined in `register_post_type()`.

## Get a post type

```php
$post_type = Timber::get_post_type('recipe');
```

What you get in return is a `Timber\PostType` object, which is similar to `WP_Post_Type`. Everything you passed to [`register_post_type()`](https://developer.wordpress.org/reference/functions/register_post_type/) is available on it.

```twig
<h1>{{ post_type.labels.all_items }}</h1>

{% if post_type.hierarchical %}
    {# … #}
{% endif %}
```

Note that this is different from WordPress’ `get_post_type()`, which returns the name of the post type of a post. `Timber::get_post_type()` returns the post type itself.

Because `name` holds the name the post type was registered with (ex: `post`), use `title` when you want the human-readable label (ex: `Posts`):

```twig
<h1>{{ post_type.title }}</h1>
```

And to link editors to the post list in the admin:

```twig
{% if post_type.can_edit %}
    <a href="{{ post_type.edit_link }}">Manage {{ post_type.title }}</a>
{% endif %}
```

If you don’t pass in any argument, Timber will use the post type of the currently queried object, which is the post type on single posts and on post type archives.

```php
$post_type = Timber::get_post_type();
```

If no post type with that name is registered, `Timber::get_post_type()` returns `null`. The post type of a post is also available through `{{ post.type }}`.

## Get the taxonomies of a post type

```twig
{% for taxonomy in post_type.taxonomies %}
    <a href="{{ taxonomy.edit_link }}">{{ taxonomy.title }}</a>
{% endfor %}
```

You get an array of [`Timber\Taxonomy`](https://timber.github.io/docs/v2/guides/taxonomies/) objects, keyed by taxonomy name.

## Querying post types

To get more than one post type, use `Timber::get_post_types()`. Without any arguments, you get all public post types.

```php
$post_types = Timber::get_post_types();
```

You can pass a post type name, a list of post type names, or the same arguments you would pass to [`get_post_types()`](https://developer.wordpress.org/reference/functions/get_post_types/).

```php
// A list of post type names.
$post_types = Timber::get_post_types(['post', 'recipe']);

// All custom hierarchical post types.
$post_types = Timber::get_post_types([
    'hierarchical' => true,
    '_builtin' => false,
]);
```

The result is an array of `Timber\PostType` objects, keyed by post type name.

```twig
{% for post_type in get_post_types({ _builtin: false }) %}
    <a href="{{ post_type.edit_link }}">{{ post_type.title }}</a>
{% endfor %}
```

## Twig

Both functions are available in Twig as well.

```twig
{% set post_type = get_post_type('recipe') %}
{% set post_types = get_post_types({ _builtin: false }) %}
```

## Extending `Timber\PostType`

If you need additional functionality that the `Timber\PostType` class doesn’t provide, you can extend it with your own class and tell Timber to use it through the [Post Type Class Map](https://timber.github.io/docs/v2/guides/class-maps/#the-post-type-class-map):

**functions.php**

```php
add_filter('timber/post_type/classmap', function ($classmap) {
    return array_merge($classmap, [
        'recipe' => RecipePostType::class,
    ]);
});
```

You still use `Timber::get_post_type()` – and `Timber::get_post_types()` – to get your object.

## Creating a `Timber\PostType` yourself

Creating a post type with `new Timber\PostType('recipe')` still works, but we recommend to use `Timber::get_post_type()` instead, because it respects the Post Type Class Map. Creating post types with `new` will be deprecated in a future version.
