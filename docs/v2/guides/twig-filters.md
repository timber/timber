---
title: "Twig filters"
order: "220"
---

## General Filters

Twig offers a variety of [filters](https://twig.symfony.com/doc/filters/index.html) to transform text and other information into the desired output. In addition, Timber has added some valuable custom filters for your WordPress theme:

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
<p class="intro">Steve-O was born in London, England. His mother,
Donna Gay (née Wauthier), was Canadian, and his father, Richard
Glover, was American. His paternal grandfather was English
and his maternal step-grandfather ...</p>
```

## `excerpt_chars`

Trims text to a certain number of characters.

**Twig**

```twig
<p class="intro">{{ post.post_content|excerpt_chars(124) }}...</p>
```

**Output**

```html
<p class="intro">Steve-O was born in London, England. His mother,
Donna Gay (née Wauthier), was Canadian, and his father, Richard
Glover, was ...</p>
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
Here is my gallery <div class="gallery" id="gallery-123"><img src="...." />...</div>
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
	<p>"No, no, he's not," another young man quickly yelled from across the table. "He wrote The Oscar."</p>
	<p>"Oh, yeah," Sinatra said, "well I've seen it, and it's a piece of crap."</p>
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
