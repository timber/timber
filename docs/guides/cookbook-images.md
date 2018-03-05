---
title: "Image Cookbook"
menu:
  main:
    parent: "guides"
---

Timber makes it damn easy to use an image in a tag.

Automatically, Timber will interpret images attached to a post’s thumbnail field ("Featured Image" in the admin) and treat them as instances of [Timber\Image](https://timber.github.io/docs/reference/timber-image/). Then, in your Twig templates, you can access them via `{{ post.thumbnail }}`. 

## Basic image stuff

Again, pretty damn easy:

```twig
<img src="{{ post.thumbnail.src }}" class="my-thumb-class" alt="Image for {{ post.title }}" />
```

## Use a WordPress image size

You can use WordPress image sizes (including ones you register with your theme/plugin) by passing the name of the size to `src` like so:

```twig
<img src="{{ post.thumbnail.src('medium') }}" class="my-thumb-class" alt="Image for {{ post.title }}" />
```

Note: If the WordPress size (e.g `medium`) has not been generated, it will return an empty string.

## Arbitrary resizing of images

Want to resize an image? Here we’re going to use [Twig Filters](http://twig.sensiolabs.org/doc/filters/index.html).

```twig
<img src="{{ post.thumbnail.src|resize(300, 200) }}" />
```

The first parameter is `width`, the second is `height` (optional). So if you don’t know the height of the image, but still want to scale it proportionally, you can do:

```twig
<img src="{{ post.thumbnail.src|resize(640) }}" />
```

All of these filters are written specifically to interact with WordPress’s image API. So don’t worry, no weird TimThumb stuff going on—this is all using WordPress’s internal image sizing stuff.

## Letterboxing images

Let’s say you have an image that you want to contain to a certain size without any cropping. If the proportions don’t fit you’ll letterbox the extra space. I find this is really useful when getting logos to all appear next to each other. You can do this with:

```twig
<img src="{{ post.thumbnail.src|letterbox(400, 400, '#FFFFFF') }}" />
```

Here `width` and `height` are required. The third argument is the background color in hex format (default is `#000000`).

## Converting images

Let’s say your client or editor can be a bit lazy (no!), resorting to PNGs where only JPGs are required. I’ve seen this a lot. People will just upload screenshots that are saved by default as PNGs. No problemo!

```twig
<img src="{{ post.thumbnail.src|tojpg }}" />
```

You can use this in conjunction with other filters.

```twig
<img src="{{ post.thumbnail.src|tojpg|resize(300, 300) }}" />
```

Filters are executed from left to right. You’ll probably want to convert to JPG before running the resizing, etc.

## Generating retina sizes

You can use Timber to generate @2x image sizes for retina devices. For example, using `srcset`:

```twig
<img src="{{ post.thumbnail.src }}" srcset="{{ post.thumbnail.src|retina(1) }} 1x,
    {{ post.thumbnail.src|retina(2) }} 2x,
    {{ post.thumbnail.src|retina(3) }} 3x,
    {{ post.thumbnail.src|retina(4) }} 4x">
```

This can be used in conjunction with other filters, so for example:

```twig
<img src="{{ post.thumbnail.src|resize(400, 300) }}" srcset="{{ post.thumbnail.src|resize(400, 300)|retina(1) }} 1x,
    {{ post.thumbnail.src|resize(400, 300)|retina(2) }}  2x,
    {{ post.thumbnail.src|resize(400, 300)|retina(3) }}  3x,
    {{ post.thumbnail.src|resize(400, 300)|retina(4) }}  4x">
```

## Using images in custom fields

Let’s say you're using a custom field plugin (like the amazing [Advanced Custom Fields](http://www.advancedcustomfields.com/)). You can use the resulting images in your Twig templates very easily.

When setting up your custom fields you’ll want to save the `image_id` to the field. The image object, url, etc. _will_ work but it’s not as fool-proof.

### The quick way (for most situations)

```twig
<img src="{{ TimberImage(post.hero_image).src }}" />
```

### The long way (for some special situations)

This is where we’ll start in PHP.

**single.php**

```php
<?php
$post = Timber::get_post();

if ( isset( $post->hero_image ) && strlen( $post->hero_image ) ) {
    $post->hero_image = new Timber\Image( $post->hero_image );
}

$data = Timber::get_context();
$data['post'] = $post;
Timber::render( 'single.twig', $data );
```

`TimberImage` should be initialized using a WordPress image ID. It can also take URLs and image objects, but that requires extra processing.

You can now use all the above functions to transform your custom images in the same way. The format will be:

```twig
<img src="{{ post.hero_image.src|resize(500, 300) }}" />
```
