---
title: "Block Editor (Gutenberg)"
order: "1550"
---

## Using the Block Editor with Timber

Timber works with the Block Editor (also called Gutenberg) out of the box. If you use `{{ post.content }}`, Timber will render all the Gutenberg blocks.

## ACF Blocks

### What are ACF Blocks?

ACF Blocks are an alternative way to create content blocks without advanced JavaScript knowledge. If you want to learn more about them, read the article on [advancedcustomfields.com](https://www.advancedcustomfields.com/resources/blocks/). 

### How to use ACF Blocks with Timber

Before you can start using ACF Blocks, you must install the Advanced Custom Fields Pro 6.0 or later.

#### Add Blocks Directory Structure

First, we will create a blocks directory in our theme folder. This directory will house the individual block.json files that will provide the settings for each respective block.

1. Create a **blocks** directory in the root of your theme: 
2. Add a directory with the block name of your choice. Example: **my-block**
3. Within your custom block directory, add the block.json file. 
4. Also within your custom block directory, add a css file to reference in the settings. 
    * Note: this is optional. You can reference CSS from any directory or rely on sitewide styling to get applied to your block on production. For purposes of this tutorial

Your blocks directory shoud look like this: 

```
.
|--...
|-- wp-content
|   |-- themes 
|       |-- your-theme # your theme directory
|           |-- blocks # your blocks directory
|               |-- my-block # your block
|                   |-- block.json # your block settings 
|                   |-- my-block.css # styles for your block
```

#### Write the Block Settings file: block.json

[Wordpress has a full example](https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/) of the values you can add to your block.json. For our example, we'll follow [ACF's example](https://www.advancedcustomfields.com/resources/acf-blocks-key-concepts/#acf-blocks-and-blockjson) with the minimal fields plus the acf property. We will use renderCallback instead of renderTemplate so we can set a formula to render our blocks instead of rednering them one by one in our code. 

```json
//block.json
{
    "name": "acf/my-block", // https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#name
    "title": "My Block", // https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#title
    "description": "Description for my block", // https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#description
    "style": [ "file:./my-block.css" ], // https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#category,
    
    "category": "formatting", // https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#category
    "icon": "format-aside", // https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#icon
    "keywords": ["my", "block"], // https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#keywords
    "acf": { 
        "mode": "preview", // https://www.advancedcustomfields.com/resources/acf-blocks-key-concepts/#acf-blocks-and-blockjson
        "renderCallback": "my_acf_block_render_callback" // the function that will render the block we'll add later on
    } 
}
```
Important note on the name property: You are not required to use acf as the namespace in the naming convention `namespace/block-name`, but you still need a value. The block-name will need to match your template name for when we render the block later on. 

#### Register the Block

Your site will need to know the block exists on init so we will employ the [register_block_type](https://developer.wordpress.org/reference/functions/register_block_type/) function witin our functions.php file and trigger the function on init. 

The straight forward way to do this is posted below: 

```php
function register_acf_blocks() {
    register_block_type( __DIR__ . '/blocks/my-block' ); //register_block_type will look in the current directory and register the block you specify
    //add additional register_block_type functions for each of your custom blocks
}
add_action( 'init', 'register_acf_blocks' ); //trigger the register function on init
```

Alternatively, you can dynamically register each block. This will keep our code DRY and prevent you from having to register each inidivual block every time you create a new one. 

```php
function register_acf_blocks() {
	foreach ( $blocks = new DirectoryIterator( __DIR__ . '/blocks' ) as $item ) {
        if ( $item -> isDir() && !$item -> isDot() ) //check for the directory
		if ( file_exists( $item -> getPathname() . '/block.json' ) ) //check if the block.json exists
		register_block_type( $item -> getPathname() );  //register the block given the directory name within the blocks directory
	}
}
add_action( 'init', 'register_acf_blocks' ); //trigger the register function on init
```

#### Create fields in ACF

[ACF has precise guidance](https://www.advancedcustomfields.com/resources/create-your-first-acf-block/#create-the-testimonial-field-group) on how to create the fields for your block. The important thing is to make sure the field group is enabled for your specific block: 
    Show field group if: Block  
    Operator: is equal to  
    Selector: My Block  
    

#### Block Template
Now that we have our block directory and settings, we need the block template that our site will render. We will create a blocks directory within our view directory to keep things organized and add the template file to that blocks directory: 

```
.
|--...
|-- wp-content
|   |-- themes 
|       |-- your-theme # your theme directory
|           |-- blocks # your blocks directory
|               |-- my-block # your block
|                   |-- block.json # your block settings 
|                   |-- my-block.css # styles for your block
|           |-- views # your views directory
|               |-- blocks # your block templates directory
|                   |-- my-block.twig # your block template 
```

Within our new template, we will call each of the fields we created in ACF for our block. Each field we define for this block in ACF will be prepended with the fields key. Here is a simple example: 

```twig
{# my-block.twig #}
<div>
    <h2>{{ fields.title }}</h2>
    <p>{{ fields.text }}</p>
</div>
```

##### Using repeaters

```
{% for field in fields.repeater %}
    Title: {{ field.title }} <br/>
    Url: {{ field.url }}
{% endfor %}
```

##### Using groups

```
Title: {{ fields.group.title }} <br/>
Url: {{ fields.group.url }}
```

#### Render Block

Finally, Add the following function to your template to your functions.php file:  

```php
//functions.php 
function my_acf_block_render_callback( $block ) {
    // Create the slug of the block using the name property in the block.json. 
	$slug = str_replace( 'acf/', '', $block['name'] );

	$context = Timber::get_context();

	// Store block values. 
	$context['block'] = $block;

	// Store field values. These are the fields from your ACF field group for the block. 
	$context['fields'] = get_fields(); 

	// Render the block.
	Timber::render(
			'blocks/' . $slug . '.twig',
			$context
	);
}
```
We call this function in the renderCallback object in block.json file of our block. This function will work for all blocks so long as we follow the nameing convertion of acf/[block name] for the name property in the block.json and name the template [block name].twig in the blocks folder of the views directory. 



