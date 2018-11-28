---
title: "Gutenberg"
menu:
  main:
    parent: "guides"
---
## Using Gutenberg with Timber

Timber works with Gutenberg out of the box. If you use `{{ post.content }}`, Timber will render all the Gutenberg blocks.

## ACF Blocks

### What are ACF Blocks?

ACF Blocks are an alternative way to create content blocks without advanced JavaScript knowledge. If you want to learn more about them, read the article on [advancedcustomfields.com](https://www.advancedcustomfields.com/blog/acf-5-8-introducing-acf-blocks-for-gutenberg/).

### How to use ACF Blocks with Timber

Before you can start using ACF Blocks, you must install the Advanced Custom Fields 5.8.0-beta version or later.

To create a content block, you first have to register it in **functions.php** or in a separate plugin:

```php
add_action( 'acf/init', 'my_acf_init' );
function my_acf_init() {
	// check function exists
	if( function_exists('acf_register_block') ) {
		
		// Register a new block.
		acf_register_block(array(
			'name'				=> 'example_block',
			'title'				=> __( 'Example Block', 'your-text-domain' ),
			'description'		=> __( 'A custom example block.', 'your-text-domain' ),
			'render_callback'	=> 'my_acf_block_render_callback',
			'category'			=> 'formatting',
            /**
             * Use an SVG or a Dashicon.
             *
             * @link https://wordpress.org/gutenberg/handbook/block-api/#icon-optional
             */
			'icon'				=> 'admin-comments',
			'keywords'		    => array( 'example' ),
		));
	}
}
```

next you you have to create a `render_callback`:
```php
function my_acf_block_render_callback( $block ) {
    // store block values
    $vars['block'] = $block;

    // store field values
    $vars['fields'] = get_fields(); 

    // render the block
    Timber::render( '/template-parts/block/example-block.twig', $vars );
}

```
you create an extra array called `$vars` with two values:
- **block** - with all data like block title, alignment etc
- **fields** - all custom fields - also all the fields created in **ACF**

Finally, you can create the template **block/example-block.twig**:

```twig
{#
/**
 * Block Name: Example block
 *
 * This is the template that displays the example block.
 */
#}
<div id="example-{{ block.id }}" class="wrapper">
    <h1>{{ fields.title }}</h1>
    <p>{{ fields.description }}</p>
</div>
<style type="text/css">
	#testimonial-{{ block.id }} {
		background: {{ fields.background_color }};
		color: {{ fields.text_color }};
	}
</style>

```

### Using repeaters
```
{% for field in fields.repeater %}
    Title: {{ field.title }} <br/>
    Url: {{ field.url }}
{% endfor %}
```

### Using groups

```
Title: {{ fields.group.title }} <br/>
Url: {{ fields.group.url }}
```
