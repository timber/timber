<div style="text-align:center">
<img src="https://github.com/jarednova/timber/blob/master/images/logo/timber-badge-large.jpg?raw=true" style="display:block; margin:auto;"/>

<div>By Jared Novack (@JaredNova) and Upstatement (@Upstatement)</div>  
</div>
## Because WordPress is awesome, but the_loop isn't
Timber is a WordPress theme that uses the [Twig Templating Engine](http://twig.sensiolabs.org/). This helps clean-up your theme code so your single.php file can focus on your WordPress model, while your single.html file can focus 100% on the HTML and display.

### What does it look like?
Nothing. Timber is meant for you to build a child theme on. Like the [Starkers](https://github.com/viewportindustries/starkers) or [Boilerplate theme](https://github.com/zencoder/html5-boilerplate-for-wordpress) it comes style-free, because you're the style expert. Instead, Timber handles the logic you need to make a kick-ass looking site.

### Who is it good for?
Timber is great for teams of designers and developers working together. At Upstatement not everyone knows the ins-and-outs of the_loop(), WordPress codex and PHP (nor should they). With Timber your best WordPress dev can focus on building the .php files with requests from WordPress and pass the data into .html files. Once there, designers can easily mark-up data and build out a site's look-and-feel.

### Should I use it?
Well, it's **free**! And it's GPL-licensed, so use in personal or commerical work. Just don't re-sell it.

# Get started

## Setup

### Download Timber + Twig

#### 1) Navigate to your WordPress themes directory
Like where twentyeleven and twentytwelve live. Timber will live at the same level.

	/wp-content/themes	/twentyeleven
						/twentytwelve
						/timber

#### 2) Use git to grab the repo
	git clone --recursive git@github.com:jarednova/timber.git
This is important! **--recursive** is needed so that the **Twig** submodule is also downloaded. Having trouble with the recursive stuff? Skip to step #4

#### 3) Don't know git?
That's cool, you should, but developer lectures are lame. Grab the zip and stick it in the themes directory (so timber lives in the same folder as twentyeleven and other thems you may have)

#### 4) Don't know git? (part 2)
We'll also need to grab [Twig](https://github.com/fabpot/Twig). Download the zip and replace the Twig folder inside of timber (please note: cAsE sEnSeTiVe). Confirm this file structure:
	
	/wp-content/themes/timber/Twig/composer.json
	/wp-content/themes/timber/Twig/lib

### Use the child theme
Optional but _strongly_ recommended

Pull the ```child-theme``` folder from ```timber``` into your main ```themes``` directory
![Drag child-theme into the themes directory](http://i.imgur.com/SyfoYRh.png)
You should now have
	/wp-content/themes/child-theme
	
Feel free to rename this to something ... cool

### Select your theme in WordPress
Use the **child** theme from the step above.

## Your first Timber project
### Let's start with your single post
You'll want to **copy** **single.html** from timber to your child theme's views folder. You should now have:
	
	wp-content/themes/my-child-theme/views/single.html

Brilliant! Open it up.

```html
{% extends "base.html" %}
{% block content %}
	{% if post.banner_image %}
		<img src="{{post.banner_image}}" class="blog-banner-image" alt="{{post.post_title}}" />
	{% endif %}
	<div class="content-wrapper">
		<article class="post-type-{{post.post_type}}" id="post-{{ID}}">
			<section class="article-content">
				<h1 class="article-h1">{{post.post_title|editable(post.ID, 'post_title')}}</h1>
				<h2 class="article-h2">{{post.subtitle}}</h2>
				<p class="blog-author"><span>By</span> {{ post.author_data.display_name }} <span>&bull;</span> {{ post.display_date }}</p>
				{{post.post_content|wpautop|raw}}
			</section>
			<section class="article-comments">
				{{comments}}
			</section>
		</article>
	</div> <!-- /content-wrapper -->
{% endblock %}
```	

#### This is the fun part. 

	<h1 class="article-h1">{{post.post_title}}</h1>
	
This is how we now call stuff from the WordPress API. Does this look familiar?
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
```html
{% extends "base.html" %}
```	
This means that **single.html** is using **base.html** as its parent template. That's why you don't see any ```<head>```, ```<header>```, or ```<footer>``` tags, those site-wide (usually) things are all controlled in base.html. If they're not? no prob, you can make single extend **base-single.html** or just include _all_ the markup inside of single.html.

	{% block content %} / {% endblock %}
	
If you were to peak into **base.html** you would see a matching set of ```{% block content %} / {% endblock %}``` tags. **single.html** is replacing the content of base's ```{% block content %}``` with its own.

Yeah baby!

### Loop / Index page

Let's crack open **index.php** and see what's inside:

```php
$posts = PostMaster::loop_to_array();
$data['page_title'] = wp_title('|', false);
$data['posts'] = $posts;
$data['wp_title'] = WPHelper::get_wp_title();
render_twig('index.html', $data);
```


