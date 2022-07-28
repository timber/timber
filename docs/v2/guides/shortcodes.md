---
title: "Shortcodes"
order: "170"
---

Let’s implement a `[youtube]` shortcode which embeds a youtube video.

For the desired usage of `[youtube id=xxxx]`, we only need a few lines of code:

**functions.php**

```php
// Should be called from within an init action hook
add_shortcode('youtube', 'youtube_shortcode');

function youtube_shortcode($atts)
{
    if (isset($atts['id'])) {
        $id = sanitize_text_field($atts['id']);
    } else {
        $id = false;
    }

    // This time we use Timber::compile since shortcodes should return the code
    return Timber::compile('youtube-short.twig', [
        'id' => $id,
    ]);
}
```

In **youtube-short.twig** we have the following template:

```twig
{% if id %}
	<iframe width="560" height="315" src="//www.youtube.com/embed/{{ id }}" frameborder="0" allowfullscreen></iframe>
{% endif %}
```

Now, when the YouTube embed code changes, we only need to edit the **youtube-short.twig** template. No need to search your PHP files for this one particular line.

## Layouts with Shortcodes

Timber and Twig can process your shortcodes by using the `{% apply shortcodes %}` tag. Let’s say you're using a `[tab]` shortcode, for example:

```twig
{% apply shortcodes %}
    [tabs tab1="Tab 1 title" tab2="Tab 2 title" layout="horizontal" backgroundcolor="" inactivecolor=""]
        [tab id=1]
            Something something something
        [/tab]

        [tab id=2]
            Tab 2 content here
        [/tab]
    [/tabs]
{% endapply %}
```
