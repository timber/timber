---
title: "Internationalization"
order: "1400"
---

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
<p class="entry-meta">
    <?php _e( 'Posted on', 'my-text-domain' ) ?> [...]
</p>
```

**Timber:**

```twig
<p class="entry-meta">
    {{ __('Posted on', 'my-text-domain') }} [...]
</p>
```

### sprintf notation

You can use sprintf-type placeholders, using the `format` filter:

**WordPress:**

```html
<p class="entry-meta">
    <?php printf( __('Posted on %s', 'my-text-domain'), $posted_on_date ) ?>
</p>
```

**Timber:**

```twig
<p class="entry-meta">
    {# Translators: The placeholder will be replaced with the localized post date #}
    {{ __('Posted on %s', 'my-text-domain')|format(posted_on_date) }}
</p>
```

If you want to use the `sprintf` function in Twig, you have to [add it yourself](https://timber.github.io/docs/v2/guides/functions/#make-functions-available-in-twig).

## Generating localization files

### wp-i18n-twig (recommended)

The Timber team provides a [dedicated WP-CLI package](https://github.com/timber/wp-i18n-twig) that takes care of extracting translations from Twig files.

The main benefit over other tools is that it perfectly integrates with the existing `wp i18n make-pot` command, making `.pot` file generation simple and easy.

Note that the package isn't Timber specific, it will work with any Twig implementation that also provides WordPress translation functions.

### Alternatives

#### Poedit 2

You can also generate `.pot`, `.po` and `.mo` files, with [Poedit](https://poedit.net/).

[Poedit 2](https://poedit.net/) fully supports Twig file parsing (Pro version only) with the following functions: __(), _x(), _n(), _nx().
