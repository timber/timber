---
title: "Twig filters"
order: "220"
---

## General Filters

Twig offers a variety of [filters](https://twig.symfony.com/doc/filters/index.html) to transform text and other information into the desired output. In addition, Timber has added some extra filters to filter data the WordPress way. Below is a comprehensive list of available filters, categorized by their functionality. Click on any filter name to jump to its detailed documentation and examples.

### Image Filters

- [`resize`](#resize) - Resize an image
- [`retina`](#retina) - Create a retina-ready image
- [`letterbox`](#letterbox) - Letterbox an image
- [`tojpg`](#tojpg) - Convert image to JPG format
- [`towebp`](#towebp) - Convert image to WebP format

### Text Filters

- [`excerpt`](#excerpt) - Trim text to a word count
- [`excerpt_chars`](#excerpt_chars) - Trim text to a character count
- [`truncate`](#truncate) - Trim text to a word count
- [`wpautop`](#wpautop) - Add paragraph tags automatically
- [`stripshortcodes`](#stripshortcodes) - Remove WordPress shortcodes
- [`shortcodes`](#shortcodes) - Process WordPress shortcodes
- [`pretags`](#pretags) - Convert entities in `<pre>` tags
- [`sanitize`](#sanitize) - Sanitize title for URLs

### Array & Collection Filters

- [`array`](#array) - Convert value to array
- [`list`](#list) - Format array as list with separators
- [`pluck`](#pluck) - Extract specific field from array of objects
- [`wp_list_filter`](#wp_list_filter) - Filter array of objects

### Date & Time Filters

- [`date`](#date) - Format dates (WordPress-compatible)
- [`time_ago`](#time_ago) - Display relative time ("5 minutes ago")

### URL Filters

- [`relative`](#relative) - Convert absolute URL to relative URL

### Utility Filters

- [`function`](#function) - Execute a PHP function
- [`apply_filters`](#apply_filters) - Apply WordPress filters
- [`size_format`](#size_format) - Format file size for display

### Security & Escaping Filters

These filters help protect against security vulnerabilities:

- [`esc_url`](#esc_url) - Escape URLs for safe use in HTML attributes
- [`esc_attr`](#esc_attr) - Escape HTML attributes
- [`esc_html`](#esc_html) - Escape HTML content
- [`esc_js`](#esc_js) - Escape JavaScript strings
- [`wp_kses`](#wp_kses) - Strip unwanted HTML tags with allowed list
- [`wp_kses_post`](#wp_kses_post) - Strip unwanted HTML tags (allows post content tags)

## Available Twig Filters

## `array`

Makes sure a variable is an array to safely loop over it without running into an error.

**PHP**

```php
$things = 'thing';

// Or
$things = ['thing', 'thang'];
```

**Twig**

```twig
{% for thing in things|array %}
    {{ thing }}
{% endfor %}
```

## `excerpt`

Trims text to a certain number of words.

**Twig**

```twig
<p class="intro">{{ post.post_content|excerpt(30) }}...</p>
```

**Output**

```html
<p class="intro">
  Steve-O was born in London, England. His mother, Donna Gay (née Wauthier), was
  Canadian, and his father, Richard Glover, was American. His paternal
  grandfather was English and his maternal step-grandfather ...
</p>
```

## `excerpt_chars`

Trims text to a certain number of characters.

**Twig**

```twig
<p class="intro">{{ post.post_content|excerpt_chars(124) }}...</p>
```

**Output**

```html
<p class="intro">
  Steve-O was born in London, England. His mother, Donna Gay (née Wauthier), was
  Canadian, and his father, Richard Glover, was ...
</p>
```

## `function`

Runs a function where you need. Really valuable for integrating plugins or existing themes.

The `|function` filter is deprecated. Use `function()` instead. You can read more about this in the [Functions Guide](https://timber.github.io/docs/v2/guides/functions/).

**Twig**

```twig
{# 🚫 Don’t do this #}
<div class="entry-meta">{{ 'twenty_ten_entry_meta'|function }}</div>

{# ✅ Do this instead #}
<div class="entry-meta">{{ function('twenty_ten_entry_meta') }}</div>
```

**Output**

```html
<div class="entry-meta">Posted on September 6, 2013</div>
```

## `relative`

Converts an absolute URL into a relative one, for example:

```twig
My custom link is <a href="{{ 'https://example.org/2015/08/my-blog-post'|relative }}">here!</a>
```

```html
My custom link is <a href="/2015/08/my-blog-post">here!</a>
```

## `pretags`

Converts tags like `<span>` into `&lt;span&gt;`, but only inside of `<pre>` tags. Great for code samples when you need to preserve other formatting in the non-code sample content.

## `sanitize`

Converts `Titles like these` into `titles-like-these`. This is great for converting titles to use them in `id` attributes.

**Twig**

```twig
<h1 id="{{ post.title|sanitize }}">{{ post.title }}</h1>
```

**Output**

```html
<h1 id="my-awesome-post">My awesome post</h1>
```

## `shortcodes`

Runs text through WordPress's shortcodes filter. In this example imagine that you've added a shortcode to a custom field like `[gallery id="123" size="medium"]`

**Twig**

```twig
<section class="gallery">
    {{ post.custom_shortcode_field|shortcodes }}
</section>
```

**Output**

```html
<section class="gallery">
  Here is my gallery
  <div class="gallery" id="gallery-123"><img src="...." />...</div>
</section>
```

## `stripshortcodes`

Removes all shortcode tags from the given content using [`strip_shortcodes()`](https://developer.wordpress.org/reference/functions/strip_shortcodes/).

**Twig**

```twig
{{ post.content|stripshortcodes }}
```

## `time_ago`

Displays a date in timeago format:

**Twig**

```twig
<p class="entry-meta">Posted: <time>{{ post.post_date_gmt|time_ago }}</time></p>
```

**Output**

```html
<p class="entry-meta">Posted: <time>3 days ago</time></p>
```

## `truncate`

**Twig**

```twig
<p class="entry-meta">{{ post.character.origin_story|truncate(8) }} ...</p>
```

**Output**

```html
<p class="entry-meta">Bruce Wayne’s parents were shot outside the opera ...</p>
```

## `wpautop`

Adds paragraph breaks to new lines.

**Twig**

```twig
<div class="body">
	{{ post.meta('custom_text_area')|wpautop }}
</div>
```

**Output**

```html
<div class="body">
  <p>Sinatra said, "What do you do?"</p>
  <p>"I'm a plumber," Ellison said.</p>
  <p>
    "No, no, he's not," another young man quickly yelled from across the table.
    "He wrote The Oscar."
  </p>
  <p>
    "Oh, yeah," Sinatra said, "well I've seen it, and it's a piece of crap."
  </p>
</div>
```

## `wp_list_filter`

Uses `wp_list_filter()`.

<!-- @todo -->

## `list`

Converts an array of strings into a comma-separated list.

**PHP**

```php
$context['contributors'] = [
    'Blake Allen',
    'Rachel White',
    'Maddy May',
];
```

**Twig**

```twig
Contributions made by {{ contributors|list(',', '&') }}
```

**Output**

```html
Contributions made by Blake Allen, Rachel White & Maddy May
```

## `pluck`

Extracts a specific attribute or key from each item in an array, useful for getting all values of the same property.

**PHP**

```php
$context['posts'] = [
    ['title' => 'First Post', 'author' => 'John'],
    ['title' => 'Second Post', 'author' => 'Jane'],
];
```

**Twig**

```twig
{% set titles = posts|pluck('title') %}
Titles: {{ titles|list(', ') }}
```

**Output**

```
Titles: First Post, Second Post
```

## `apply_filters`

Applies a [WordPress filter hook](https://developer.wordpress.org/plugins/hooks/filters/) to the given content. You can read more about this filter in the [Functions Guide](https://timber.github.io/docs/v2/guides/twig/#wordpress-filters).

**Twig**

```twig
{{ content|apply_filters('my_custom_filter') }}
```

## `size_format`

Formats a number of bytes into a human-readable file size (e.g., "1 MB", "2.5 GB").

**Twig**

```twig
File size: {{ 1048576|size_format }} {# 1 MB #}
Large file: {{ 5368709120|size_format }} {# 5 GB #}
```

## Image Filters

You can read more about the image filters in the [Image Cookbook](https://timber.github.io/docs/v2/guides/cookbook-images/).

### `resize`

Resizes an image to specific dimensions.

**Twig**

```twig
<img src="{{ image|resize(400, 300) }}" alt="{{ image.alt }}">
```

### `retina`

Creates a retina-ready version of an image (doubled dimensions and URL handling).

**Twig**

```twig
<img src="{{ image|retina }}" alt="{{ image.alt }}">
```

### `letterbox`

Adds letterboxing to an image to fit specific dimensions while maintaining aspect ratio.

**Twig**

```twig
<img src="{{ image|letterbox(400, 300) }}" alt="{{ image.alt }}">
```

### `tojpg`

Converts an image to JPG format.

**Twig**

```twig
<img src="{{ image|tojpg }}" alt="{{ image.alt }}">
```

### `towebp`

Converts an image to modern WebP format.

**Twig**

```twig
<picture>
    <source srcset="{{ image|towebp }}" type="image/webp">
    <img src="{{ image }}" alt="{{ image.alt }}">
</picture>
```

## `date`

Formats a date using WordPress date formats, while considering the timezone and date format settings you set in your WordPress settings. For more details, see [Twig's date filter documentation](https://twig.symfony.com/doc/3.x/filters/date.html).

**Twig**

```twig
Posted on {{ post.post_date|date('Y-m-d') }}
{{ post.post_date|date('F j, Y') }}
```

**Output**

```
Posted on 2023-09-15
September 15, 2023
```

## Security & Escaping Filters

You can read more about escaping and security filters in the [Escaping Guide](https://timber.github.io/docs/v2/guides/escaping/).

### `esc_url`

Escapes URLs for safe use in HTML attributes. Prevents XSS attacks in URL contexts.

**Twig**

```twig
<a href="{{ post.link|esc_url }}">{{ post.title }}</a>
```

### `esc_attr`

Escapes strings for safe use in HTML attributes.

**Twig**

```twig
<div class="{{ custom_class|esc_attr }}" data-value="{{ custom_data|esc_attr }}">
    Content
</div>
```

### `esc_html`

Escapes HTML content to display as plain text, preventing scripts from executing.

**Twig**

```twig
<p>{{ user_comment|esc_html }}</p>
```

### `esc_js`

Escapes strings for safe use in JavaScript contexts.

**Twig**

```twig
<script>
var title = "{{ post.title|esc_js }}";
</script>
```

### `wp_kses`

Strips unwanted HTML tags while preserving an allowed list of tags. Requires an allowed tags parameter.

**Twig**

```twig
{# Only allow <b>, <i>, and <em> tags #}
{{ user_content|wp_kses('<b><i><em>') }}
```

### `wp_kses_post`

Strips unwanted HTML tags while allowing standard post formatting tags (paragraphs, lists, links, etc.).

**Twig**

```twig
<div class="entry-content">
    {{ post.post_content|wp_kses_post }}
</div>
```
