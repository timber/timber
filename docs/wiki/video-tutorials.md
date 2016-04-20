# Video Tutorials

I'm in the midst of an install and walk-through on Timber, here are the screencasts thus far:

### 1. Install Timber

#### Option 1: Via GitHub (for developers)

##### 1) Navigate to your WordPress plugins directory
	$ cd ~/Sites/mywordpress/wp-content/plugins

##### 2) Use git to grab the repo
	$ git clone git@github.com:timber/timber.git

##### 3) Use [Composer](https://getcomposer.org/doc/00-intro.md) to download the dependencies (Twig, etc.)
	$ cd timber
	$ composer install

#### Option 2: Via Composer (for developers)

##### 1) Navigate to your WordPress plugins directory
    $ cd ~/Sites/mywordpress/wp-content/plugins

##### 2) Use [Composer](https://getcomposer.org/doc/00-intro.md) to create project and download the dependencies (Twig, etc.)
    $ composer create-project --no-dev timber/timber ./timber

#### Option 3: Via WordPress plugins directory (for non-developers)

##### If you'd prefer one-click installation, you should use the [WordPress.org](http://wordpress.org/plugins/timber-library/) version.

* * *

Now just activate in your WordPress admin screen. Inside of the timber directory there's a timber-starter-theme. To use this move it into your `themes` directory (probably want to rename it too) and select it.

* * *

### 2. Including a Twig template and sending data
[![Installing Timber](http://img.youtube.com/vi/SlMonnwVi5M/0.jpg)](http://www.youtube.com/watch?v=SlMonnwVi5M)

In which we use an existing WordPress template and implement a very simple Timber usage.

Here's the relevant code:

```php
/* index.php */
$context = array();
$context['headline'] = 'Welcome to my new Timber Blog!';
Timber::render('welcome.twig', $context);
```

```handlebars
{# welcome.twig #}
<section class="welcome-block">
	<div class="inner">
		<h3>{{headline}}</h3>
		<p>This will be a superb blog, I will inform you every day</p>
	</div>
</section>
```
* * *
### 3. Connecting Twig to your WordPress Admin
[![Connecting Timber](http://img.youtube.com/vi/C7HtYkaG2DQ/0.jpg)](http://www.youtube.com/watch?v=C7HtYkaG2DQ)

```php
$context = array();
$context['welcome'] = Timber::get_post(56);
Timber::render('welcome.twig', $context);
```

```handlebars
<section class="welcome-block">
	<div class="inner">
		<h3>{{welcome.post_title}}</h3>
		<p>{{welcome.get_content}}</p>
		<p>Follow me on <a href="https://twitter.com/{{welcome.twitter_handle}}" target="_blank">Twitter!</a></p>
	</div>
</section>
```
* * *
### 4. Converting HTML to Twig Templates
[![Connecting HTML Templates](http://img.youtube.com/vi/BxazrNBLK-0/0.jpg)](http://www.youtube.com/watch?v=BxazrNBLK-0)

```php
$context['posts'] = Timber::get_posts();
Timber::render('home-main.twig', $context);
```

```handlebars
{# home-main.twig #}
{% for post in posts %}
    {% include "tz-post.twig" %}
{% endfor %}
```

* * *

### 5. Using Custom Post Types with Timber + Twig

[![Using Custom Post Types with Timber](http://img.youtube.com/vi/19T0MStDLSQ/0.jpg)](http://www.youtube.com/watch?v=19T0MStDLSQ)

```handlebars
{# home-main.twig #}
{% for post in posts %}
	{# you can send includes an array, in order of precedence #}
	{% include ["tz-"~post.post_type~".twig", "tz-post.twig"] %}
{% endfor %}
```

```handlebars
{# tz-recipe.twig #}
<article id="post-{{post.ID}}" class="post-{{post.ID}} {{post.post_type}} type-{{post.post_type}} status-publish hentry">
	{% if post.get_thumbnail %}
		<img src="{{post.thumbnail.src|resize(600, 300)}}" />
	{% endif %}
	<h2>{{post.post_title}}</h2>
	<div class="post-body">
		{{post.get_content}}
	</div>
</article>
```
* * *
### 6. Extending Templates
_Todo: Record Screencast showing this_

This is a **really** important concept for DRY. I'll show how to create a base template that can power your site:

##### Create a `base.twig` file:

```handlebars
{# base.twig #}
{% include "html-header.twig" %}
{% block head %}
	<!-- This is where you'll put template-specific stuff that needs to go in the head tags like custom meta tags, etc. -->
{% endblock %}
</head>
<body class="{{body_class}}">
{% block content %}
	<!-- The template's main content will go here. -->
{% endblock %}
{% include "footer.twig" %}
{{wp_footer}}
</body>
</html>
```

##### You can use this in a custom `single.twig` file:

```handlebars
{# single.twig #}
{% extends "base.twig" %}
{% block head %}
	<meta property="og:title" value="{{post.title}}" />
{% endblock %}
{% block content %}
	<div class="main">
		<h1>{{post.title}}</h1>
		<p>{{post.get_content}}</p>
	</div>
{% endblock %}
```
