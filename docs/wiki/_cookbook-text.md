# Text Cookbook

There's tons of stuff you can do with Twig and Timber filters to make complex transformations easy (and fun!)

#### Dates

##### Timber does bylines like a boss:

```twig
<p class="byline">
	<span class="name">By {{ post.author.name }}</span>
	<span class="date">{{ post.post_date|date('F j, Y') }}</span>
</p>
```

###### Renders:

```html
<p class="byline"><span class="name">By Mr. WordPress</span><span class="date">September 28, 2013</span></p>
```

##### Nothing is worse than an out-of-date copyright year in the footer. Nothing.

```twig
<footer>
	<p class="copyright">&copy; {{ now|date('Y') }} by {{ bloginfo('name') }}</p>
</footer>
```

###### Renders:

```html
<footer><p class="copyright">&copy; 2015 by The Daily Orange</p></footer>
```

* * *

#### Standard transforms

##### Automatically link URLs, email addresses, twitter @s and #s

```twig
<p class="tweet">{{ post.content|twitterify }}</p>
```

##### Run WordPress' auto-paragraph filter

```twig
<p class="content">{{ post.my_custom_text|wpautop }}</p>
```

##### Run WordPress shortcodes over a block of text

```twig
<p class="content">{{ post.my_custom_text|shortcodes }}</p>
```

##### Code samples? Lord knows I've got 'em:

```twig
<div class="code-sample">{{ post.code_samples|pretags }}</div>
```

##### Functions inside of your templates, plugin calls:

Old template:

```html
<p class="entry-meta"><?php twentytwelve_entry_meta(); ?></p>
```

Timber-fied template:

```twig
<p class="entry-meta">{{ function('twentytwelve_entry_meta') }}</p>
```

##### Functions "with params" inside of your templates, plugin calls:

Old template:

```html
<p class="entry-meta"><?php get_the_title( $post->ID ); ?></p>
```

Timber-fied template:

```twig
<p class="entry-meta">{{ function('get_the_title', post.ID) }}</p>
```

* * *

### Internationalization

#### Translation functions

Timber supports the gettext i18n functions used in WordPress:

* __()
* _x()
* _n()
* _nx()
* _n_noop()
* _nx_noop()
* translate()
* translate_nooped_plural()

While `_e()` and `_ex()` are also supported, you probably won’t need them in Twig, because `{{ }}` already echoes the output.

Old template:

```html
<p class="entry-meta"><?php _e( 'Posted on', 'my-text-domain' ) ?> [...]</p>
```

Timber-fied template:

```twig
<p class="entry-meta">{{ __('Posted on', 'my-text-domain') }} [...]</p>
```

#### sprintf notation

You can use sprintf-type placeholders, using the `format` filter:

Old template:

```html
<p class="entry-meta"><?php printf( __('Posted on %s', 'my-text-domain'), $posted_on_date ) ?></p>
```

Timber-fied template:

```twig
<p class="entry-meta">{{ __('Posted on %s', 'my-text-domain')|format(posted_on_date) }}</p>
```

#### Generating .po files with Poedit 2

[Poedit 2](https://poedit.net/) fully supports parsing of Twig files (Pro version only) with the following functions: __(), _x(), _n(), _nx().

#### Generating .po files with Poedit 1.x

Internationalization functions in Twig files are not automatically parsed by gettext in Poedit 1.x. The quick and dirty workaround is to start each .twig file with `{#<?php#}`. By doing this, gettext will interpret whatever comes next as php, and start looking for `__`.

A nicer solution is to use [Twig-Gettext-Extractor](https://github.com/umpirsky/Twig-Gettext-Extractor), a special Twig parser to Poedit. The linked page contains instructions on how to set it up.

Alternatively, you can use a custom parser for Python instead. This will throw a warning or two, but your strings are extracted! To add the parser, follow these steps:

1. Create a Poedit project for your theme if you haven't already, and make sure to add `__` on the _Sources keywords_ tab.
2. Go to _Edit_->_Preferences_.
3. On the _Parsers_ tab, add a new parser with these settings:
    * Language: `Timber`
    * List of extensions: `*.twig`
    * Parser command: `xgettext --language=Python --add-comments=TRANSLATORS --force-po -o %o %C %K %F`
    * An item in keyword list: `-k%k`
    * An item in input files list: `%f`
    * Source code charset: `--from-code=%c`
4. Save and Update!

Be aware that with the Python parser, strings inside HTML attributes will not be recognized. This will not work:

```twig
<nav aria-label="{{ __('Main Menu', 'my-text-domain') }}">
```

As a workaround, you can assign the translation to a variable, which you can then use in the attribute.

```twig
{% set nav_aria_label = __('Main Menu', 'my-text-domain') %}
<nav aria-label="{{ nav_aria_label }}">
```

* * *

### Debugging

##### What properties are inside my object?

```twig
{{ dump(post) }}
```

##### What properties and _methods_ are inside my object?

Warning: Experimental!

```twig
{{ post|print_a }}
```
This outputs both the database stuff (like `{{ post.post_content }}`) and the contents of methods (like `{{ post.thumbnail }}`)

##### What type of object am I working with?

```twig
{{ post|get_class }}
```

... will output something like `TimberPost` or your custom wrapper object
