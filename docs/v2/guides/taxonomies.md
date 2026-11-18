---
title: "Taxonomies"
order: "125"
---

While [`Timber::get_term()`](https://timber.github.io/docs/v2/guides/terms/) gives you a single term, sometimes you need the taxonomy itself – for example to display the labels you defined in `register_taxonomy()`.

## Get a taxonomy

```php
$taxonomy = Timber::get_taxonomy('genre');
```

What you get in return is a `Timber\Taxonomy` object, which is similar to `WP_Taxonomy`. Everything you passed to [`register_taxonomy()`](https://developer.wordpress.org/reference/functions/register_taxonomy/) is available on it.

```twig
<h1>{{ taxonomy.labels.all_items }}</h1>

{% if taxonomy.hierarchical %}
    {# … #}
{% endif %}
```

Because `name` holds the name the taxonomy was registered with (ex: `post_tag`), use `title` when you want the human-readable label (ex: `Tags`):

```twig
<h1>{{ taxonomy.title }}</h1>
```

If you defined a `default_term` when registering the taxonomy, you can get it as a `Timber\Term`:

```twig
{% if taxonomy.default_term %}
    <a href="{{ taxonomy.default_term.link }}">{{ taxonomy.default_term.title }}</a>
{% endif %}
```

And to link editors to the term overview in the admin:

```twig
{% if taxonomy.can_edit %}
    <a href="{{ taxonomy.edit_link }}">Manage {{ taxonomy.title }}</a>
{% endif %}
```

If you don’t pass in any argument, Timber will use the taxonomy of the currently queried term. This is handy on `taxonomy.php`, `category.php` or `tag.php`.

```php
$taxonomy = Timber::get_taxonomy();
```

If no taxonomy with that name is registered, `Timber::get_taxonomy()` returns `null`.

## Get the terms of a taxonomy

The terms of a taxonomy are only queried when you access them, so getting a taxonomy stays cheap when you never display its terms.

```twig
{% for term in taxonomy.terms %}
    <a href="{{ term.link }}">{{ term.title }}</a>
{% endfor %}
```

You can pass [`WP_Term_Query`](https://developer.wordpress.org/reference/classes/wp_term_query/) arguments to refine the result. The `taxonomy` argument is always set for you.

```twig
{% for term in taxonomy.terms({ hide_empty: false, orderby: 'count', order: 'DESC' }) %}
    {{ term.title }} ({{ term.count }})
{% endfor %}
```

The terms you get back are `Timber\Term` objects, which means the [Term Class Map](https://timber.github.io/docs/v2/guides/class-maps/#the-term-class-map) applies here as well.

## Querying taxonomies

To get more than one taxonomy, use `Timber::get_taxonomies()`. Without any arguments, you get all public taxonomies.

```php
$taxonomies = Timber::get_taxonomies();
```

You can pass a taxonomy name, a list of taxonomy names, or the same arguments you would pass to [`get_taxonomies()`](https://developer.wordpress.org/reference/functions/get_taxonomies/).

```php
// A list of taxonomy names.
$taxonomies = Timber::get_taxonomies(['category', 'genre']);

// All hierarchical taxonomies.
$taxonomies = Timber::get_taxonomies([
    'hierarchical' => true,
]);
```

In addition to the arguments supported by `get_taxonomies()`, you can use `post_type` to get all taxonomies that are registered for a post type. It can be combined with any of the other arguments.

```php
$taxonomies = Timber::get_taxonomies([
    'post_type' => 'recipe',
]);

// Only the hierarchical ones.
$taxonomies = Timber::get_taxonomies([
    'post_type' => 'recipe',
    'hierarchical' => true,
]);
```

The result is an array of `Timber\Taxonomy` objects, keyed by taxonomy name. This makes it straightforward to build filter navigations:

```twig
{% for taxonomy in get_taxonomies({ post_type: 'recipe' }) %}
    <fieldset>
        <legend>{{ taxonomy.labels.all_items }}</legend>

        {% for term in taxonomy.terms %}
            <a href="{{ term.link }}">{{ term.title }}</a>
        {% endfor %}
    </fieldset>
{% endfor %}
```

## Get the post types of a taxonomy

```twig
{% for post_type in taxonomy.post_types %}
    {{ post_type.labels.name }}
{% endfor %}
```

You get an array of `Timber\PostType` objects – the same objects you get through `{{ post.type }}`.

## Twig

Both functions are available in Twig as well.

```twig
{% set taxonomy = get_taxonomy('genre') %}
{% set taxonomies = get_taxonomies({ post_type: 'recipe' }) %}
```

## Extending `Timber\Taxonomy`

If you need additional functionality that the `Timber\Taxonomy` class doesn’t provide, you can extend it with your own class:

```php
class Genre extends Timber\Taxonomy
{
    public function top_level_terms(): iterable
    {
        return $this->terms([
            'parent' => 0,
            'hide_empty' => false,
            'orderby' => 'name',
        ]);
    }

    public function term_count(): int
    {
        return (int) wp_count_terms([
            'taxonomy' => $this->name,
            'hide_empty' => false,
        ]);
    }
}
```

Tell Timber to use your class for the `genre` taxonomy through the [Taxonomy Class Map](https://timber.github.io/docs/v2/guides/class-maps/#the-taxonomy-class-map):

**functions.php**

```php
add_filter('timber/taxonomy/classmap', function ($classmap) {
    return array_merge($classmap, [
        'genre' => Genre::class,
    ]);
});
```

You still use `Timber::get_taxonomy()` – and `Timber::get_taxonomies()` – to get your object, and your methods are available in Twig:

```twig
{% set genre = get_taxonomy('genre') %}

<h2>{{ genre.title }} ({{ genre.term_count }})</h2>

<ul>
    {% for term in genre.top_level_terms %}
        <li><a href="{{ term.link }}">{{ term.title }}</a></li>
    {% endfor %}
</ul>
```

Taxonomies you don’t list in the Taxonomy Class Map keep using the default `Timber\Taxonomy` class.
