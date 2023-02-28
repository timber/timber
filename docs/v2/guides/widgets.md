---
title: "Widgets"
order: "160"
---

Everyone loves widgets! Of course they do...

```php
$data = [
    'footer_widgets' => Timber::get_widgets('footer_widgets'),
];
```

...where `footer_widgets` is the registered name of the widgets you want to get (in twentythirteen these are called `sidebar-1` and `sidebar-2`).

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
