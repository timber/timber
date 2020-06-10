---
title: "Timber Docs"
layout: "page"
---

## What is Timber?

Timber helps you create fully-customized WordPress themes faster with more sustainable code. With Timber, you write your HTML using the [Twig Template Engine](https://twig.symfony.com/). This cleans up your theme code so your PHP file can focus on providing the data/logic, while your Twig file can focus 100% on the HTML and display.

**single.php**

```php
<?php

use Timber\Timber;
$context = Timber::get_context();
$context['post'] = new Timber\Post();
Timber::render( 'single.twig', $context );
```

**single.twig**

```twig
{% extends "base.twig" %}

{% block content %}
	<article class="post">
		<h1 class="post-title">{{ post.title }}</h1>
		<img class="post-thumbnail" src="{{ post.thumbnail.src }}">
		
		<div class="post-content">
			{{ post.content }}
		</div>
	</article>
{% endblock %}
```

## Who is this for?

Timber is for both WordPress professionals and rookies. People new to WordPress will like how it reduces the WordPress-specific knowledge required to create a WordPress theme. Pros can take advantage of object-oriented patterns that adhere to DRY and MVC principles.

