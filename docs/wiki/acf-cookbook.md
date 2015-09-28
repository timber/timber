# ACF Cookbook

Timber is designed to play nicely with (the amazing) [Advanced Custom Fields](http://www.advancedcustomfields.com/). It's not a requirement, of course.

While data saved by ACF is available via `{{post.my_acf_field}}` you will often need to do some additional work to get back the _kind_ of data you want. For example, images are stored as image ID#s which you might want to translate into a specific image object. Read on to learn more about those specific exceptions.

### WYSIWYG field (and other requiring text):
```twig
<h3>{{post.title}}</h3>
<div class="intro-text">
     {{post.get_field('my_wysiwyg_field')}}
</div>
```

This will apply your expected paragraph breaks and other pre-processing to the text.

### Image field type:
You can retrieve an image from a custom field, then use it in a Twig template. The most reliable approach is this: When setting up your custom fields you'll want to save the `image_id` to the field. The image object, url, etc. _will_ work but it's not as fool-proof.

##### The quick way (for most situations)

```twig
<img src="{{TimberImage(post.get_field('hero_image')).src}}" />
```

##### The long way (for some special situations)

This is where we'll start in PHP.

```php
/* single.php */
$post = new TimberPost();
if (isset($post->hero_image) && strlen($post->hero_image)){
	$post->hero_image = new TimberImage($post->hero_image);
}
$data = Timber::get_context();
$data['post'] = $post;
Timber::render('single.twig', $data);
```

`TimberImage` should be initialized using a WordPress image ID#. It can also take URLs and image objects, but that requires extra processing.

You can now use all the above functions to transform your custom images in the same way, the format will be:

```twig
<img src="{{post.hero_image.src|resize(500, 300)}}" />
```

* * *

### Repeater field

You can access repeater fields within in twig files:
```twig
{# single.twig #}
<h2>{{post.title}}</h2>
<div class="my-list">
	{% for item in post.get_field('my_repeater') %}
		<div class="item">
			<h4>{{item.name}}</h4>
			<h6>{{item.info}}</h6>
			<img src="{{TimberImage(item.picture).src}}" />
		</div>
	{% endfor %}
</div>
```

##### Nested?

When you run `get_field` on an outer ACF field, everything inside is ready to be traversed. You can refer to nested fields via outer_item.inner_repeater

```twig
{% for item_outer in post.get_field('outer') %}
     {{item_outer.title}}

     {% for item_inner in item_outer.inner_repeater %}
          {{item_inner.title}}
     {% endfor %}

{% endfor %}
```

##### Troubleshooting

A common problem in working with repeaters is that you should only call the `get_field` method **once** on an item. In other words if you have a field inside a field (for example, a relationship inside a repeater or a repeater inside a repeater, **do not** call `get_field` on the inner field). More:

###### DON'T DO THIS (Bad)

```twig
{% for gear in post.get_field('gear_items') %}
    <h3> {{ gear.brand_name }} </h3>
    {% for gear_feature in gear.get_field('features') %}
        <li> {{gear_feature}} </li>
    {% endfor %}
{% endfor %}
```

###### Do THIS: (Good)

```twig
{% for gear in post.get_field('gear_items') %}
    <h3> {{ gear.brand_name }} </h3>
    {% for gear_feature in gear.features %}
        <li> {{gear_feature}} </li>
    {% endfor %}
{% endfor %}
```

* * *

### Flexible content field

Similar to repeaters, get the field by the name of the flexible content field:

```twig
{% for media_item in post.get_field('media_set') %}
	{% if media_item.acf_fc_layout == 'image_set' %}
		<img src="{{TimberImage(media_item.image).src}}" />
		<p class="caption">{{TimberImage(media_item.image).caption}}</p>
		<aside class="notes">{{media_item.notes}}</aside>
	{% elseif media_item.acf_fc_layout == 'video_set' %}
		<iframe width="560" height="315" src="http://www.youtube.com/embed/{{media_item.youtube_id}}" frameborder="0" allowfullscreen></iframe>
		<p class="caption">{{media_item.caption}}</p>
	{% endif %}
{% endfor %}
```

* * *

### Options Page

```php
	$context['site_copyright_info'] = get_field('copyright_info', 'options');
	Timber::render('index.twig', $context);
```

```twig
	<footer>{{site_copyright_info}}</footer>
```

###### Get all info from your options page

```php
	$context['options'] = get_fields('options');
	Timber::render('index.twig', $context);
```

ACF Pro has a built in options page, and changes the `get_fields('options')` to `get_fields('option')`.

```twig
	<footer>{{options.copyright_info}}</footer>
```

###### Use options info site wide

To use any options fields site wide, add the `option` context to your functions.php file

```php
/* functions.php */
add_filter( 'timber_context', 'mytheme_timber_context'  );

function mytheme_timber_context( $context ) {
    $context['option'] = get_fields('option');
    return $context;
}
```

Now, you can use any of the option fields across the site instead of per template.

```twig
/* footer.twig */
<footer>{{options.copyright_info}}</footer>
```

* * *

### Getting ACF info:
You can grab specific field label data like so:

```php
/* single.php */
$context["acf"] = get_field_objects($data["post"]->ID);
```

```twig
{{ acf.your_field_name_here.label }}
```

* * *

### Query by custom field value:
###### Use a [WP_Query](http://codex.wordpress.org/Class_Reference/WP_Query) array

####Basic Example
This example shows the arguments to find all posts where a custom field called ‘color’ has a value of ‘red’.

```php
$args = array(
    'numberposts' => -1,
    'post_type' => 'post',
    'meta_key' => 'color',
    'meta_value' => 'red'
));
$context['posts'] = Timber::get_posts($args);
```
* * *
