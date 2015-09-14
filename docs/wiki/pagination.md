# Pagination

Do you like pagination? Stupid question, of course you do. Well, here's how you do it.

This will only work in a php file with an active query (like `archive.php` or `home.php`):

```php
	$context = Timber::get_context();
	$context['posts'] = Timber::get_posts();
	$context['pagination'] = Timber::get_pagination();
	Timber::render('archive.twig', $context);
```

You can then markup the output like so  (of course, the exact markup is up to YOU):

```twig
<div class="tool-pagination">
	{% if pagination.prev %}
		<a href="{{pagination.prev.link}}" class="prev {{pagination.prev.link|length ? '' : 'invisible'}}">Prev</a>
	{% endif %}
	<ul class="pages">
		{% for page in pagination.pages %}
			<li>
				{% if page.link %}
					<a href="{{page.link}}" class="{{page.class}}">{{page.title}}</a>
				{% else %}
					<span class="{{page.class}}">{{page.title}}</span>
				{% endif %}
			</li>
		{% endfor %}
	</ul>
	{% if pagination.next %}
		<a href="{{pagination.next.link}}" class="next {{pagination.next.link|length ? '' : 'invisible'}}">Next</a>
	{% endif %}
</div>
```

### What if I'm not using the default query?
So let's say you want to paginate things on `page-events.php` where you list items from a custom post type of `event`. Because the default query is just the request for the page's info **pagination will not work**. You need to _make_ it the default query by using the dreaded `query_posts` like so:

```php
	global $paged;
	if (!isset($paged) || !$paged){
		$paged = 1;
	}
	$context = Timber::get_context();
	$args = array(
		'post_type' => 'event',
		'posts_per_page' => 5,
		'paged' => $paged
	);
	/* THIS LINE IS CRUCIAL */
	/* in order for WordPress to know what to paginate */
	/* your args have to be the defualt query */
		query_posts($args);
	/* make sure you've got query_posts in your .php file */
	$context['posts'] = Timber::get_posts();
	$context['pagination'] = Timber::get_pagination();
	Timber::render('page-events.twig', $context);
```

### The pre_get_posts Way
Custom `query_posts` sometimes shows 404 on example.com/page/2
In that case you can also use `pre_get_posts` in your functions.php file
```php
	function my_home_query( $query ) {
	  if ( $query->is_main_query() && !is_admin() ) {
		$query->set( 'post_type', array( 'movie', 'post' ));
	  }
	}
	add_action( 'pre_get_posts', 'my_home_query' );
```
In your archive.php or home.php template
```php
	$context = Timber::get_context();
	$context['posts'] = Timber::get_posts();
	$context['pagination'] = Timber::get_pagination();
	Timber::render('archive.twig', $context);
```
