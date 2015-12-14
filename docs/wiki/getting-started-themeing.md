# Getting Started: Themeing


## Your first Timber project
### Let's start with your single post
Find this file: (btw if you have no idea what I'm talking about you should go to the [setup article](Getting-Started%3A-Setup)
```html
	wp-content/themes/{timber-starter-theme}/views/single.twig
```

Brilliant! Open it up.

```html
{% extends "base.twig" %}
{% block content %}
	<div class="content-wrapper">
		<article class="post-type-{{post.post_type}}" id="post-{{post.ID}}">
			<section class="article-content">
				<h1 class="article-h1">{{post.title}}</h1>
				<h2 class="article-h2">{{post.subtitle}}</h2>
				<p class="blog-author"><span>By</span> {{ post.author.name }} <span>&bull;</span> {{ post.post_date|date }}</p>
				{{post.content}}
			</section>
		</article>
	</div> <!-- /content-wrapper -->
{% endblock %}
```

#### This is the fun part.

```html
<h1 class="article-h1">{{post.title}}</h1>
```

This is now how we now call stuff from the WordPress API. Instead of this familiar face:

```html
<h1 class="article-h1"><?php the_title(); ?></h1>
```
This is how WordPress wants you to interact with its API. Which sucks. Because soon you get things like:

```html
<h1 class="article-h1"><a href="<?php get_permalink(); ?>"><?php the_title(); ?></a></h1>
```
Okay, not _too_ terrible, but doesn't this (Timber) way look so much nicer:
```html
<h1 class="article-h1"><a href="{{post.permalink}}">{{post.title}}</a></h1>
```

It gets better. Let's explain some other concepts.
```html
{% extends "base.twig" %}
```

This means that `single.twig` is using `base.twig` as its parent template. That's why you don't see any `<head>`, `<header>`, or `<footer>` tags, those site-wide (usually) things are all controlled in `base.twig`. You can create any number of base files to extend from (the "base" naming convention is recommended, but not requrired).

What if you want modify `<head>`, etc? Read on to learn all about blocks.

### Blocks
Blocks are the single most important and powerful concept in managing your templates. The [official Twig Documentationn](http://twig.sensiolabs.org/doc/templates.html#template-inheritance) has more details. Let's cover the basics.

In `single.twig` you see opening and closing block declarations that surround the main page contents.

```html
{% block content %} {# other stuff here ... #} {% endblock %}
```
If you were to peek into **base.twig** you would see a matching set of `{% block content %} / {% endblock %}` tags. **single.twig** is replacing the content of base's `{% block content %}` with its own.

##### Nesting Blocks, Multiple Inheritance
This is when things get really cool. Whereas most people use PHP includes in a linear fashion, you can create infinite levels of nested blocks to particularly control your page templates. For example, let's say you occasionally want to replace the title/headline on your `single.twig` template with a custom image or typography.

For this demo let's assume that the name of the page is "All about Jared" (making its slug `all-about-jared`). First, I'm going to surround the part of the template I want to control with block declarations:

```html
{# single.twig #}
{% extends "base.twig" %}
{% block content %}
	<div class="content-wrapper">
		<article class="post-type-{{post.post_type}}" id="post-{{post.ID}}">
			<section class="article-content">
				{% block headline %}
					<h1 class="article-h1">{{post.title}}</h1>
					<h2 class="article-h2">{{post.subtitle}}</h2>
				{% endblock %}
				<p class="blog-author"><span>By</span> {{ post.author.name }} <span>&bull;</span> {{ post.post_date|date }}</p>
				{{post.content}}
			</section>
		</article>
	</div> <!-- /content-wrapper -->
{% endblock %}
```

Compared to the earlier example of this page, we now have the `{% block headline %}` bit surrounding the `<h1>` and `<h2>`.

To inject my custom bit of markup, I'm going to create a file called `single-all-about-jared.twig` in the `views` directory. The logic for which template should be selected is controlled in `single.php` but generally follows WordPress conventions on Template Hierarchy. Inside that file, all I need is...

```php
{# single-all-about-jared.twig #}
{% extends "single.twig" %}
{% block headline %}
	<h1><img src="/wp-content/uploads/2014/05/jareds-face.jpg" alt="Jared's Mug"/></h1>
{% endblock %}
```

So two big concepts going on here:

1. **Multiple Inheritance** I'm extending `{% single.twig %}`, which itself extends `{% base.twig %}`. Thus we stay true to DRY and don't have very similar code between my two templates hanging around.
2. **Nested Blocks** `{% block headline %}` is located inside `{% block content %}`. So while I'm replacing the headline, I get to keep all the other markup and variables found in the parent template.

What if you want to **add** to the block as opposed to replace? No prob, just call `{{ parent() }}` where the parent's content should go.

### Loop / Index page

Let's crack open **index.php** and see what's inside:

```php
$context = Timber::get_context();
$context['posts'] = Timber::get_posts();
Timber::render('index.twig', $context);
```
This is where we are going to handle the logic that powers our index file. Let's go step-by-step

#### Get the starter
```php
$context = Timber::get_context();
```
This is going to return an object with a lot of the common things we need across the site. Things like your nav, wp_head and wp_footer you'll want to start with each time (even if you over-write them later). You can do a ```print_r($context);``` to see what's inside or open-up **timber.php** to inspect for yourself

#### Grab your posts
```php
$context['posts'] = Timber::get_posts();
```
We're now going to grab the posts that are inside the loop and stick them inside our data object under the **posts** key.

##### Timber::get_posts() usage

###### Use a [WP_Query](http://codex.wordpress.org/Class_Reference/WP_Query) array
```php
	$args = array(
		'post_type' => 'post',
		'tax_query' => array(
			'relation' => 'AND',
			array(
				'taxonomy' => 'movie_genre',
				'field' => 'slug',
				'terms' => array( 'action', 'comedy' )
			),
			array(
				'taxonomy' => 'actor',
				'field' => 'id',
				'terms' => array( 103, 115, 206 ),
				'operator' => 'NOT IN'
			)
		)
	);
	$context['posts'] = Timber::get_posts($args);
```

##### Use a [WP_Query](http://codex.wordpress.org/Class_Reference/WP_Query) string
```php
	$args = 'post_type=movies&numberposts=8&orderby=rand';
	$context['posts'] = Timber::get_posts($args);
```

##### Use Post ID numbers
```php
	$ids = array(14, 123, 234, 421, 811, 6);
	$context['posts'] = Timber::get_posts($ids);
```

#### Render

```php
Timber::render('index.twig', $context);
```
We're now telling Twig to find **index.twig** and send it our data object.

Timber will look first in the child theme and then falls back to the parent theme (same as WordPress logic). The official load order is...

1. User-defined locations
2. Directory of calling PHP script (but not theme)
3. Child theme
4. Parent theme
5. Directory of calling PHP script (including the theme)

... item 2 is inserted above others so that if you're using Timber in a plugin it will use the twig files in the plugin's directory.