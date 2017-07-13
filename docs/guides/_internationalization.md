# Internationalization

Internationalization of a Timber theme works pretty much the same way as it does for default WordPress themes. Follow the guide in the [WordPress Theme Handbook](https://developer.wordpress.org/themes/functionality/internationalization/) to setup i18n for your theme.

Twig has its own i18n extension that gives you `{% trans %}` tags to define translatable blocks, but there’s no need to use it, because with Timber, you have all you need.

## Translation functions

Timber supports all the translation functions used in WordPress:

* __()
* _x()
* _n()
* _nx()
* _n_noop()
* _nx_noop()
* translate()
* translate_nooped_plural()

The functions `_e()` and `_ex()` are also supported, but you probably won’t need them in Twig, because `{{ }}` already echoes the output.

**WordPress:**

```html
<p class="entry-meta"><?php _e( 'Posted on', 'my-text-domain' ) ?> [...]</p>
```

**Timber:**

```twig
<p class="entry-meta">{{ __('Posted on', 'my-text-domain') }} [...]</p>
```

### sprintf notation

You can use sprintf-type placeholders, using the `format` filter:

**WordPress:**

```html
<p class="entry-meta"><?php printf( __('Posted on %s', 'my-text-domain'), $posted_on_date ) ?></p>
```

**Timber:**

```twig
<p class="entry-meta">{{ __('Posted on %s', 'my-text-domain')|format(posted_on_date) }}</p>
```

If you want to use the `sprintf` function in Twig, you have to [add it yourself](http://timber.github.io/timber/#make-functions-available-in-twig).

## Generating localization files

To generate `.pot`, `.po` and `.mo` files, you need a tool that supports parsing Twig files to detect all your translations. While there are a lot of tools that can parse PHP files, the solution that works best for Twig files is [Poedit](https://poedit.net/).

### Generating l10n files with Poedit 2

[Poedit 2](https://poedit.net/) fully supports Twig file parsing (Pro version only) with the following functions: __(), _x(), _n(), _nx().

### Generating l10n files with Poedit 1.x

Internationalization functions in Twig files are not automatically parsed by gettext in Poedit 1.x. The quick and dirty workaround is to start each .twig file with `{#<?php#}`. By doing this, gettext will interpret whatever comes next as PHP, and start looking for `__`.

* * *

Another solution is [Twig-Gettext-Extractor](https://github.com/umpirsky/Twig-Gettext-Extractor), a special Twig parser for Poedit. The linked page contains instructions on how to set it up.

* * *

Alternatively, you can use a custom parser for Python instead. This will throw a warning or two, but your strings are extracted! To add the parser, follow these steps:

1. Create a Poedit project for your theme if you haven't already, and make sure to add `__` on the _Sources keywords_ tab.
2. Go to _Edit_ > _Preferences_.
3. On the _Parsers_ tab, add a new parser with these settings:
    * Language: `Timber`
    * List of extensions: `*.twig`
    * Parser command: `xgettext --language=Python --add-comments=TRANSLATORS --force-po -o %o %C %K %F`
    * An item in keyword list: `-k%k`
    * An item in input files list: `%f`
    * Source code charset: `--from-code=%c`
4. Save and Update!

Be aware that with the Python parser, strings **inside HTML attributes** will not be recognized. This will not work:

```twig
<nav aria-label="{{ __('Main Menu', 'my-text-domain') }}">
```

As a workaround, you can assign the translation to a variable, which you can then use in the attribute.

```twig
{% set nav_aria_label = __('Main Menu', 'my-text-domain') %}
<nav aria-label="{{ nav_aria_label }}">
```
