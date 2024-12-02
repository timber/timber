---
title: "Widgets"
order: "160"
---

In a world where the block editor is used more and more, widgets are getting less important. However, they are still a part of WordPress and you might need to use them. Timber can help you with that.

First we will have to register the widget area in our theme. This is done in the **functions.php** file for example.

```php
function site_widgets_init() {
    register_sidebar([
        'name'          => 'Footer widgets',
        'id'            => 'footer_widgets'
        'description'   => 'Add widgets here to appear in your footer.',
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget__title">',
        'after_title'   => '</h3>',
    ]);
}

add_action('widgets_init', 'site_widgets_init');
```

Then you can add the widgets to the global context.

```php
function add_to_context($context) {
    $context['footer_widgets'] = Timber::get_widgets('footer_widgets');

    return $context;
}

add_filter('timber/context', 'add_to_context');
```

...where `footer_widgets` is the registered name of the widgets you want to get.

Then use it in your template:

**base.twig**

```twig
<footer>
    {{ footer_widgets }}
</footer>
```

## Using Timber inside your own widgets

You can also use twig templates for your widgets! Let’s imagine we want a widget that shows a random number each time it is rendered.

Inside the widget class, the widget function is used to show the widget:

```php
class My_Widget extends WP_Widget
{
    public function widget($args, $instance)
    {
        $number = rand();

        Timber::render('random-widget.twig', [
            'args' => $args,
            'instance' => $instance,
            'number' => $number,
        ]);
    }
}
```

The corresponding template file **random-widget.twig** looks like this:

```twig
{{ args.before_widget|raw }}
{{ args.before_title|raw }}{{ instance.title|apply_filters('widget_title') }}{{ args.after_title|raw }}

<p>Your magic number is: <strong>{{ number }}</strong></p>

{{ args.after_widget|raw }}
```

The raw filter is needed here to embed the widget properly.

You may also want to check if the Timber plugin was loaded before using it:

```php
class My_Widget extends WP_Widget
{
    public function widget($args, $instance)
    {
        if (!class_exists('Timber')) {
            // if you want to show some error message, this is the right place
            return;
        }

        $number = rand();

        Timber::render('random-widget.twig', [
            'args' => $args,
            'instance' => $instance,
            'number' => $number,
        ]);
    }
}
```
