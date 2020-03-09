---
title: "A post archive"
---

Here’s how a PHP template file for a WordPress archive looks like.

**index.php**

```php
<?php

$context = Timber::context();

Timber::render( 'index.twig', $context );
```

For basic archives, that’s all you need.

**index.twig**

```twig
{% extends "base.twig" %}

{% block content %}
	{% for post in posts %}
		{{ include('teaser.twig') }}
	{% endfor %}

	{{ include('partials/pagination.twig') }}
{% endblock %}
```

Behind the scenes, Timber already prepared a `posts` array for you that holds all the posts that you would normally find in [The Loop]().

```php
$context['posts'] = Timber::get_posts();
```
