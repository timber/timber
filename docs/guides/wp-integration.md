---
title: "WordPress Integration"
menu:
  main:
    parent: "guides"
---

Timber plays nicely with your existing WordPress setup. You can still use other plugins, etc.

## the_content

You’re probably used to call `the_content()` in your theme file. This is good. Before outputting, WordPress will run all the filters and actions that your plugins and themes are using. If you want to get this into your new Timber theme (and you probably do), call it like this:

```twig
<div class="my-article">
    {{ post.content }}
</div>
```

This differs from `{{ post.post_content }}`, which will display the raw text stored in the database.

## Hooks

Timber hooks to interact with WordPress use `this/style/of_hooks` instead of `this_style_of_hooks`. This matches the same methodology as [Advanced Custom Fields](http://www.advancedcustomfields.com/resources/#actions).

Full documentation to come.

## Actions

You can all actions in your Twig templates like this:

```twig
{% do action('my_action') %}
{% do action('my_action_with_args', 'foo', 'bar') %}
```

If you ask yourself why there’s no underline between `do` and `action`: The expression [`do`](https://twig.symfony.com/doc/2.x/tags/do.html) is a feature of Twig which *calls a function without printing its return value*, like `{{ }}` does. Timber only register an `action` function, which then calls the `do_action()` function.

## Filters

Timber already comes with a [set of useful filters](https://timber.github.io/docs/guides/filters/). If you have your own filters that you want to apply, you can use `apply_filters`.

```twig
{{ post.content|apply_filters('my_filter') }}
{{ "my custom string"|apply_filters('my_filter', param1, param2, ...) }}
```

## Widgets

Everyone loves widgets! Of course they do...

```php
<?php
$data['footer_widgets'] = Timber::get_widgets( 'footer_widgets' );
```

...where `footer_widgets` is the registered name of the widgets you want to get (in twentythirteen these are called `sidebar-1` and `sidebar-2`).

Then use it in your template:

**base.twig**

```twig
<footer>
    {{ footer_widgets }}
</footer>
```

### Using Timber inside your own widgets

You can also use twig templates for your widgets! Let’s imagine we want a widget that shows a random number each time it is rendered.

Inside the widget class, the widget function is used to show the widget:

```php
<?php
public function widget( $args, $instance ) {
    $number = rand();

    Timber::render( 'random-widget.twig', array(
        'args' => $args,
        'instance' => $instance,
        'number' => $number
    ) );
}
```

The corresponding template file `random-widget.twig` looks like this:

```twig
{{ args.before_widget|raw }}
{{ args.before_title|raw }}{{ instance.title|apply_filters('widget_title') }}{{ args.after_title|raw }}

<p>Your magic number is: <strong>{{ number }}</strong></p>

{{ args.after_widget|raw }}
```
The raw filter is needed here to embed the widget properly.

You may also want to check if the Timber plugin was loaded before using it:

```php
<?php
public function widget( $args, $instance ) {
    if ( ! class_exists( 'Timber' ) ) {
        // if you want to show some error message, this is the right place
        return;
    }

    $number = rand();

    Timber::render( 'random-widget.twig', array(
        'args' => $args,
        'instance' => $instance,
        'number' => $number
    ) );
}
```

## Shortcodes

Well, if it works for widgets, why shouldn't it work for shortcodes? Of course it does!

Let’s implement a `[youtube]` shortcode which embeds a youtube video.
For the desired usage of `[youtube id=xxxx]`, we only need a few lines of code:

```php
<?php
// Should be called from within an init action hook
add_shortcode( 'youtube', 'youtube_shortcode' );

function youtube_shortcode( $atts ) {
    if( isset( $atts['id'] ) ) {
        $id = sanitize_text_field( $atts['id'] );
    } else {
        $id = false;
    }

    // This time we use Timber::compile since shortcodes should return the code
    return Timber::compile( 'youtube-short.twig', array( 'id' => $id ) );
}
```

In `youtube-short.twig` we have the following template:

```twig
{% if id %}
	<iframe width="560" height="315" src="//www.youtube.com/embed/{{ id }}" frameborder="0" allowfullscreen></iframe>
{% endif %}
```

Now, when the YouTube embed code changes, we only need to edit the `youtube-short.twig` template. No need to search your PHP files for this one particular line.

### Layouts with Shortcodes

Timber and Twig can process your shortcodes by using the `{% filter shortcodes %}` tag. Let’s say you're using a `[tab]` shortcode, for example:

```twig
{% filter shortcodes %}
    [tabs tab1="Tab 1 title" tab2="Tab 2 title" layout="horizontal" backgroundcolor="" inactivecolor=""]
        [tab id=1]
            Something something something
        [/tab]

        [tab id=2]
            Tab 2 content here
        [/tab]
    [/tabs]
{% endfilter %}
```

