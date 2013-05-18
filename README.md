<div style="text-align:center">
<a href="http://jarednova.github.com/timber"><img src="https://github.com/jarednova/timber/blob/master/images/logo/timber-badge-large.jpg?raw=true" style="display:block; margin:auto;"/></a>
<div>
By Jared Novack (<a href="http://twitter.com/jarednova">@JaredNova</a>) and <a href="http://upstatement.com">Upstatement</a> (<a href="http://twitter.com/upstatement">@Upstatement</a>)</div>  
</div>
## Upgrade Notes
This is a major rewrite of Timber. Trust me, it's worth it. But if you're looking for the old [Parent Theme Timber](https://github.com/jarednova/timber/tree/theme) you can still find it on this [branch](https://github.com/jarednova/timber/tree/theme).

## Because WordPress is awesome, but the_loop isn't
Timber is a WordPress plugin that allows you to use the [Twig Templating Engine](http://twig.sensiolabs.org/) in your theme. This helps clean-up your theme code so your single.php file can focus on being the controller for your WordPress model, while your single.html file can focus 100% on the HTML and display.

### What does it look like?
Nothing. Timber is meant for you to build a theme on. Like the [Starkers](https://github.com/viewportindustries/starkers) or [Boilerplate theme](https://github.com/zencoder/html5-boilerplate-for-wordpress) it comes style-free, because you're the style expert. Instead, Timber handles the logic you need to make a kick-ass looking site.

### Who is it good for?
Timber is great for teams of designers and developers working together. At [Upstatement](http://upstatement.com) not everyone knows the ins-and-outs of the_loop(), WordPress codex and PHP (nor should they). With Timber your best WordPress dev can focus on building the .php files with requests from WordPress and pass the data into .html files. Once there, designers can easily mark-up data and build out a site's look-and-feel.

### Should I use it?
Well, it's **free**! And it's GPL-licensed, so use in personal or commerical work. Just don't re-sell it.

# Get started

## Setup

### Download Timber + Twig

#### 1) Navigate to your WordPress plugins directory
	cd ~/Sites/mywordpress/wp-content/plugins

#### 2) Use git to grab the repo
	git clone --recursive git@github.com:jarednova/timber.git timber-framework

This is important! **--recursive** is needed so that the **Twig** submodule is also downloaded. Having trouble with the recursive stuff? Skip to step #4 to download Twig. Also some dude took the name 'timber' in the WP Plugin Repo so you should change the name of the directory or you'll get a misleading upgrade notice.

#### 3) Don't know git?
That's cool, you should, but developer lectures are lame. Grab the zip and stick it in the plugins directory (so **timber** lives in the same folder as other plugins you may have). You should also rename it to "timber-framework" or you'll get an incorrect upgrade warning from WordPress.

#### 4) Don't know git? (part 2)
We'll also need to grab [Twig](https://github.com/fabpot/Twig). Download the zip and replace the Twig folder inside of timber (please note: cAsE sEnSeTiVe). Confirm this file structure:
	
	/wp-content/plugins/timber-framework/Twig/composer.json
	/wp-content/plugins/timber-framework/Twig/lib

### Use the starter theme

#### Navigate to your WordPress themes directory
Like where twentyeleven and twentytwelve live. Timber will live at the same level.

	/wp-content/themes	/twentyeleven
						/twentytwelve
						/timber-starter-theme

You should now have

	/wp-content/themes/timber-starter-theme
	
Feel free to rename this to something ... cool

### Activate Timber
It will be in wp-admin/plugins.php

### Select your theme in WordPress
Use the **timber-starter-theme** theme from the step above.

## Your first Timber project
### Let's start with your single post
Find this file:
	
	wp-content/themes/[timber-starter-theme]/views/single.html

Brilliant! Open it up.

```html
{% extends "base.html" %}
{% block content %}
	<div class="content-wrapper">
		<article class="post-type-{{post.post_type}}" id="post-{{ID}}">
			<section class="article-content">
				<h1 class="article-h1">{{post.post_title}}</h1>
				<h2 class="article-h2">{{post.subtitle}}</h2>
				<p class="blog-author"><span>By</span> {{ post.author.name }} <span>&bull;</span> {{ post.display_date }}</p>
				{{post.post_content|wpautop|raw}}
			</section>
		</article>
	</div> <!-- /content-wrapper -->
{% endblock %}
```	

#### This is the fun part. 
```php	
<h1 class="article-h1">{{post.post_title}}</h1>
```
This is now how we now call stuff from the WordPress API. Instaed of this familiar face:
```php	
<h1 class="article-h1"><?php the_title(); ?></h1>
```
This is how WordPress wants you to interact with its API. Which sucks. Because soon you get things like:
```php
<h1 class="article-h1"><a href="<?php get_permalink(); ?>"><?php the_title(); ?></a></h1>
```
Okay, not _too_ terrible, but doesn't this (Timber) way look so much nicer:
```php	
<h1 class="article-h1"><a href="{{post.permalink}}">{{post.post_title}}</a></h1>
```	
It gets better. Let's explain some other concepts.
```php
{% extends "base.html" %}
```
This means that **single.html** is using **base.html** as its parent template. That's why you don't see any ```<head>```, ```<header>```, or ```<footer>``` tags, those site-wide (usually) things are all controlled in base.html. If they're not? no prob, you can make single extend **base-single.html** or just include _all_ the markup inside of single.html.
```php
{% block content %} / {% endblock %}
```
If you were to peak into **base.html** you would see a matching set of ```{% block content %} / {% endblock %}``` tags. **single.html** is replacing the content of base's ```{% block content %}``` with its own.

Yeah baby!

### Loop / Index page

Let's crack open **index.php** and see what's inside:

```php
$context = Timber::get_context();
$context['posts'] = Timber::get_posts();
render_twig('index.html', $context);
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

#### 
```php
render_twig('index.html', $context);
```
We're now telling Twig to grab **index.html** and send it our data object. 

# Reference

### TimberCore

#### title

#### slug

#### content

#### path



### Timber
#### get_posts($query, $PostClass = 'TimberPost')
Send WordPress an arbitrary [WordPress Query](http://codex.wordpress.org/Class_Reference/WP_Query) or an array of IDs and it will send you back an array of Post Objects. By default it will use `TimberPost` but you can supply your own subclass of `TimberPost`.

If you send **false** to the $query, Timber takes the WordPress loop and translates into an array of Post Objects. By default it will use `TimberPost` but you can supply your own subclass of `TimberPost`.

##### returns
(array) of TimberPosts

#### get_context()
Returns a basic context object with:
* ['http_host'] = 'http://mywordpresssite.com';
* ['wp_title'] = "Jared's Site";
* ['wp_head'] = the output from wp_head();
* ['wp_footer'] = the output of wp_footer();
* ['wp_nav_menu'] = <ul><li>Whatever HTML is rendered from your nav menu '</li></ul>';

##### returns
(array) an associative array of different types

#### get_wp_footer
##### returns
(string)

#### get_wp_head
##### returns
(string)

### TimberCore
#### import();
#### url_to_path()

### TimberPost extends TimberCore
init()
update()


### TimberComment extends TimberCore
### TimberImage extends TimberCore
#### get_url()
#### get_path()
#### url()
#### can_edit();
#### init_with_url()
### TimberTerm extends TimberCore
### TimberUser extends TimberCore



