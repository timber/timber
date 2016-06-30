# Text Cookbook

There's tons of stuff you can do with Twig and Timber filters to make complex transformations easy (and fun!)

#### Dates

##### Timber does bylines like a boss:

```twig
<p class="byline">
	<span class="name">By {{post.author.name}}</span>
	<span class="date">{{post.post_date|date('F j, Y')}}</span>
</p>
```

###### Renders:

```html
<p class="byline"><span class="name">By Mr. WordPress</span><span class="date">September 28, 2013</span></p>
```

##### Nothing is worse than an out-of-date copyright year in the footer. Nothing.

```twig
<footer>
	<p class="copyright">&copy; {{now|date('Y')}} by {{bloginfo('name')}}</p>
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
<p class="tweet">{{post.content|twitterify}}</p>
```

##### Run WordPress' auto-paragraph filter

```twig
<p class="content">{{post.my_custom_text|wpautop}}</p>
```

##### Run WordPress shortcodes over a block of text

```twig
<p class="content">{{post.my_custom_text|shortcodes}}</p>
```

##### Code samples? Lord knows I've got 'em:

```twig
<div class="code-sample">{{post.code_samples|pretags}}</div>
```

##### Functions inside of your templates, plugin calls:
Old template:

```html
<p class="entry-meta"><?php twentytwelve_entry_meta(); ?></p>
```

Timber-fied template:

```twig
<p class="entry-meta">{{function('twentytwelve_entry_meta')}}</p>
```

##### Functions "with params" inside of your templates, plugin calls:
Old template:

```html
<p class="entry-meta"><?php get_the_title($post->ID); ?></p>
```

Timber-fied template:

```twig
<p class="entry-meta">{{function('get_the_title', post.ID)}}</p>
```
* * *

### Internationalization

#### __()

Timber comes built-in with your ordinary gettext function __() for l10n.

Old template:

```html
<p class="entry-meta"><?php _e('Posted on', 'my-text-domain') ?> [...]</p>
```

Timber-fied template:

```twig
<p class="entry-meta">{{__('Posted on', 'my-text-domain')}} [...]</p>
```

#### sprintf notation

You can even use sprintf-type placeholders, using the `format` filter:

Old template:

```html
<p class="entry-meta"><?php printf( __('Posted on %s', 'my-text-domain'), $posted_on_date ) ?></p>
```

Timber-fied template:

```twig
<p class="entry-meta">{{__('Posted on %s', 'my-text-domain')|format(posted_on_date)}}</p>
```

#### Generating .po files using Poedit

Unfortunately, Twig files with the above functions are not automatically parsed by gettext in Poedit. The quick and dirty workaround is to start each .twig file with `{#<?php#}` (by doing this, gettext will interpret whatever comes next as php, and start looking for `__`).

A nicer solution is to use [Twig-Gettext-Extractor](https://github.com/umpirsky/Twig-Gettext-Extractor), a special Twig parser to Poedit. The linked page contains instructions on how to set it up.

Alternatively, you can use the parser for Python instead. This will throw a warning or two, but your strings are extracted! To add the parser, follow these steps:

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

* * *

### Debugging

##### What properties are inside my object?

```twig
{{dump(post)}}
```

##### What properties and _methods_ are inside my object?

Warning: Experimental!

```twig
{{post|print_a}}
```
This outputs both the database stuff (like {{post.post_content}}) and the contents of methods (like {{post.thumbnail}})

##### What type of object am I working with?

```twig
{{post|get_class}}
```

... will output something like `TimberPost` or your custom wrapper object
