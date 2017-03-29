# Text Cookbook

There's tons of stuff you can do with Twig and Timber filters to make complex transformations easy (and fun!)

#### Dates

##### Timber does bylines like a boss:

```twig
<p class="byline">
	<span class="name">By {{ post.author.name }}</span>
	<span class="date">{{ post.post_date|date('F j, Y') }}</span>
</p>
```

###### Renders:

```html
<p class="byline"><span class="name">By Mr. WordPress</span><span class="date">September 28, 2013</span></p>
```

##### Nothing is worse than an out-of-date copyright year in the footer. Nothing.

```twig
<footer>
	<p class="copyright">&copy; {{ now|date('Y') }} by {{ bloginfo('name') }}</p>
</footer>
```

###### Renders:

```html
<footer><p class="copyright">&copy; 2015 by The Daily Orange</p></footer>
```

* * *

#### Standard transforms

##### Automatically link URLs, email addresses, twitter @s and #s

```twig
<p class="tweet">{{ post.content|twitterify }}</p>
```

##### Run WordPress' auto-paragraph filter

```twig
<p class="content">{{ post.my_custom_text|wpautop }}</p>
```

##### Run WordPress shortcodes over a block of text

```twig
<p class="content">{{ post.my_custom_text|shortcodes }}</p>
```

##### Code samples? Lord knows I've got 'em:

```twig
<div class="code-sample">{{ post.code_samples|pretags }}</div>
```

##### Functions inside of your templates, plugin calls:

Old template:

```html
<p class="entry-meta"><?php twentytwelve_entry_meta(); ?></p>
```

Timber-fied template:

```twig
<p class="entry-meta">{{ function('twentytwelve_entry_meta') }}</p>
```

##### Functions "with params" inside of your templates, plugin calls:

Old template:

```html
<p class="entry-meta"><?php get_the_title( $post->ID ); ?></p>
```

Timber-fied template:

```twig
<p class="entry-meta">{{ function('get_the_title', post.ID) }}</p>
```

* * *

### Debugging

##### What properties are inside my object?

```twig
{{ dump(post) }}
```

##### What properties and _methods_ are inside my object?

Warning: Experimental!

```twig
{{ post|print_a }}
```
This outputs both the database stuff (like `{{ post.post_content }}`) and the contents of methods (like `{{ post.thumbnail }}`)

##### What type of object am I working with?

```twig
{{ post|get_class }}
```

... will output something like `TimberPost` or your custom wrapper object
