---
title: "Block Editor (Gutenberg)"
order: "1550"
---

## Using the Block Editor with Timber

Timber works with the Block Editor (also called Gutenberg) out of the box. If you use `{{ post.content }}`, Timber will render all the Gutenberg blocks.

## ACF Blocks

[ACF Blocks](https://www.advancedcustomfields.com/resources/blocks/) are an alternative way to create content blocks without advanced JavaScript knowledge.

### Concept

A (ACF) block is an individual element that can be added to a page or post. It can be a simple text block, a gallery, a call to action, or a complex layout. Blocks are the building blocks of your website. In this tutorial, we will show you how to create a simple block with ACF and Timber.

### Prerequisites

Before you can start using ACF Blocks, you must install Advanced Custom Fields Pro 6.0 or later.

#### Add Blocks Directory Structure

First, we will create a `blocks` directory in our theme folder. This directory will house folders for each of our custom blocks. Inside each block directory, we will have a block.json file, a CSS file and a Twig template.

1. Create a `blocks` directory in the root of your theme.
2. Add a directory with the block name of your choice. Example: **my-block**.
3. Within your custom block directory, create a block.json file.
4. Also within your custom block directory, add a CSS file to reference in the settings.
    * Note: this is optional. You can reference CSS from any directory or rely on sitewide styling to get applied to your block on production.

Your theme structure should look like this:

```
|--...
|-- wp-content
|   |-- themes
|       |-- your-theme                  # Your theme directory
|           |-- blocks                  # Your blocks directory
|               |-- my-block            # Your block
|                   |-- block.json      # Your block settings
|                   |-- my-block.css    # Styles for your block
|                   |-- my-block.twig   # Your block template
```

#### The block settings file: block.json

The block.json file is a configuration file that contains the settings for your block. It is used to define the block’s name, title, description, category among many other settings.

[WordPress has a full example](https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/) of the values you can add to your block.json. For our example, we’ll follow [ACF's example](https://www.advancedcustomfields.com/resources/acf-blocks-key-concepts/#acf-blocks-and-blockjson) with the minimal settings plus the specific ACF property. We will use `renderCallback` instead of `renderTemplate` so we can set a function callback to render all our blocks instead of creating a function for each individual block.

Example contents of the block.json

```json
{
    "name": "acf/my-block",
    "title": "My Block",
    "description": "Description for my block",
    "style": [ "file:./my-block.css" ],
    "category": "formatting",
    "icon": "format-aside",
    "keywords": ["my", "block"],
    "acf": {
        "mode": "preview",
        "renderCallback": "my_acf_block_render_callback"
    }
}
```

**Notes:**

- Learn more about the different entries in the block.json file in the [WordPress documentation](https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata).
- For the entries under `acf`, learn more in the [ACF documentation](https://www.advancedcustomfields.com/resources/acf-blocks-key-concepts/#acf-blocks-and-blockjson).
- For the `name` property, you are not required to use `acf` as the namespace in the naming convention `namespace/block-name`, but you do have to provide one. For example: you could also name them `my-project/block-name`. We recommend using the same namespace throughout your blocks and that, in the case you use another namespace then `acf`, you need to change the render callback as well to omit your chosen namespace.

#### Register the Block

Your theme needs to know the block exists on `init` so we will use the [register_block_type()](https://developer.wordpress.org/reference/functions/register_block_type/) function and trigger the function on the `init` action.

Here’s the straight-forward way to do this.

**functions.php**

```php
function register_acf_blocks() {
    // The register_block_type() function will look in the current directory and
    // register the block you specify. Add additional register_block_type()
    // functions for each of your custom blocks.
    register_block_type( __DIR__ . '/blocks/my-block' );
}

add_action( 'init', 'register_acf_blocks' );
```

Alternatively, you can dynamically register each block. This will keep our code DRY and prevent you from having to register each individual block every time you create a new one.

```php
function register_acf_blocks() {
    foreach ($blocks = new DirectoryIterator( __DIR__ . '/blocks' ) as $item) {
        // Check if block.json file exists in each subfolder.
        if ($item->isDir() && !$item->isDot()
            && file_exists($item->getPathname() . '/block.json')
        ) {
            // Register the block given the directory name within the blocks
            // directory.
            register_block_type($item -> getPathname());
        }
    }
}

add_action('init', 'register_acf_blocks');
```

#### Render block

Now that we have our block registered, we need to create a function to render our blocks. We will create a function called `my_acf_block_render_callback()` that will be used to render all our blocks. In this function, we will prepare the context for our block and render the block using Timber.

```php
/**
 * Render callback to prepare and display a registered block using Timber.
 *
 * @param    array    $attributes The block attributes.
 * @param    string   $content The block content.
 * @param    bool     $is_preview Whether or not the block is being rendered for editing preview.
 * @param    int      $post_id The current post being edited or viewed.
 * @param    WP_Block $wp_block The block instance (since WP 5.5).
 * @return   void
 */
function my_acf_block_render_callback($attributes, $content = '', $is_preview = false, $post_id = 0, $wp_block = null) {
    // Create the slug of the block using the name property in the block.json.
    $slug = str_replace( 'acf/', '', $attributes['name'] );

    $context = Timber::context();

    // Store block attributes.
    $context['attributes'] = $attributes;

    // Store field values. These are the fields from your ACF field group for the block.
    $context['fields'] = get_fields();

    // Store whether the block is being rendered in the editor or on the frontend.
    $context['is_preview'] = $is_preview;

    // Render the block.
    Timber::render(
        'blocks/' . $slug . '/' . $slug . '.twig',
        $context
    );
}
```
We call this function in the `renderCallback` object in the block.json file of our block. This function will work for all blocks as long as we follow the naming convention of `acf/your-block-name` for the name property in the block.json and name the template `your-block-name.twig` in the `blocks/your-block-name` folder inside the root of your theme.

#### Create fields in ACF

[ACF has precise guidance](https://www.advancedcustomfields.com/resources/create-your-first-acf-block/#create-the-testimonial-field-group) on how to create the fields for your block. The important thing is to make sure the field group is enabled for your specific block:

- **Show field group if:** Block
- **Operator:** is equal to
- **Selector:** My Block

#### Block Template

Now that we have our block directory and settings, we need the a Twig template for our block that will be used to display our block.

Within our new Twig template, we will call each of the fields we created in ACF for our block. Each field we define for this block in ACF will be prepended with the fields key. Here is a simple example:

**my-block.twig**

```twig
<div>
    <h2>{{ fields.title }}</h2>
    <p>{{ fields.text }}</p>
</div>
```

##### Using repeaters

```twig
{% for field in fields.repeater %}
    Title: {{ field.title }} <br/>
    Url: {{ field.url }}
{% endfor %}
```

##### Using groups

```twig
Title: {{ fields.group.title }} <br/>
Url: {{ fields.group.url }}
```
