# Performance

Timber, especially in conjunction with WordPress and Twig, offers a variety of caching strategies to optimize performance. Here's a quick rundown of some of the options, ranked in order of most-broad to most-focused.

### tl;dr
In my tests with Debug Bar, Timber has no measurable performance hit. Everything compiles to PHP. @fabpot has an [overview of the performance costs on his blog](http://fabien.potencier.org/article/34/templating-engines-in-php) (scroll down to the table)



## Cache Everything
You can still use plugins like [W3 Total Cache](https://wordpress.org/plugins/w3-total-cache/) in conjunction with Timber. In most settings, this will _skip_ the Twig/Timber layer of your files and serve static pages via whatever mechanism the plugin or settings dictate



## Cache the Entire Twig File and Data

When rendering, use the `$expires` argument in `Timber::render`. For example:

```php
$data['posts'] = Timber::get_posts();
Timber::render('index.twig', $data, 600);
```

Timber will cache the template for 10 minutes (600 / 60 = 10). But here's the cool part. Timber hashes the fields in the view context. This means that as soon as the data changes, the cache is automatically invalidated (yay!).

Full Parameters:

```php
Timber::render(
    $filenames,
    $data,
    $expires, /** Default: false. False disables cache altogether. When passed an array, the first value is used for non-logged in visitors, the second for users **/
    $cache_mode /** Any of the cache mode constants defined in TimberLoader **/
);
```

The cache modes are:

```php
TimberLoader::CACHE_NONE /** Disable caching **/
TimberLoader::CACHE_OBJECT /** WP Object Cache **/
TimberLoader::CACHE_TRANSIENT  /** Transients **/
TimberLoader::CACHE_SITE_TRANSIENT /** Network wide transients **/
TimberLoader::CACHE_USE_DEFAULT /** Use whatever caching mechanism is set as the default for TimberLoader, the default is transient **/
```

This method is very effective, but crude - the whole template is cached. So if you have any context dependent sub-views (eg. current user), this mode won't do.



## Cache _Parts_ of the Twig File and Data

This method implements the Twig Cache Extension. It adds the cache tag, for use in templates. Best shown by example:

```
{% cache 'index/content' posts %}
    {% for post in posts %}
        {% include ['tease-'~post.post_type~'.twig', 'tease.twig'] %}
    {% endfor %}
{% endcache %}
```

`'index/content'` will be used as annotation (ie. label) for the cache, while `posts` will be encoded with all its public fields. You can use anything for the label ("foo", "elephant", "single-posts", whatever).

The mechanism behind it is the same as with render - the cache key is determined based on a hash of the object/array passed in (in the above example posts).

The cache method used is always the default mode, set using the bespoke filter (by default, transient cache).

This method allows for very fine-grained control over what parts of templates are being cached and which are not. When applied on resource-intensive sections, the performance difference is huge.

In your cache, the eventual key will be:
```php
$annotation . '__GCS__' . $key
```
that is in this scenario
```php
'index/content__GCS__' . md5( json_encode( $context['post'] ) )
```

###### Extra: TimberKeyGeneratorInterface

Instead of hashing a whole object, you can specify the cache key in the object itself. If the object implements TimberKeyGeneratorInterface, it can pass a unique key through the method get_cache_key. That way a class can for example simply pass last_updated as the unique key.
If arrays contain the key _cache_key, that one is used as cache key.

This may save yet another few processor cycles.



## Cache the Twig File (but not the data)
Every time you render a `.twig` file, Twig compiles all the html tags and variables into a big, nasty blob of function calls and echo statements that actually gets run by PHP. In general, this is pretty efficient. However, you can cache the resulting PHP blob by turning on Twig's cache via:

```php
/* functions.php */
if (class_exists('Timber')){
	Timber::$cache = true;
}
```
You can look in your your `/wp-content/plugins/timber/twig-cache` directory to see what these files look like.

This does not cache the _contents_ of the variables. This is recommended as a last-step in the production process. Once enabled, any change you make to a `.twig` file (just tweaking the html for example) will not go live until the cache is flushed.


## Cache the PHP data
Sometimes the most expensive parts of the operations are generating the data needed to populate the twig template. You can of course use WordPress's default [Transient API](http://codex.wordpress.org/Transients_API) to store this data.

You can also use some [syntactic sugar](http://en.wikipedia.org/wiki/Syntactic_sugar) to make the checking/saving/retrieving of transient data a bit easier:

```php
/* home.php */

$context = Timber::get_context();
$context['main_stories'] = TimberHelper::transient('main_stories', function(){
	$posts = Timber::get_posts();
	//do something expensive with these posts
	$extra_teases = get_field('my_extra_teases', 'options');
	foreach($extra_teases as &$tease){
		$tease = new TimberPost($tease);
	}
	$main_stories = array();
	$main_stories['posts'] = $posts;
	$main_stories['extra_teases'] = $extra_teases;
	return $main_stories;
}, 600);
Timber::render('home.twig', $context);
```
Here `main_stories` is a totally made-up variable. It could be called `foo`, `bar`, `elephant`, etc.



## Measuring Performance
Some tools like Debug Bar may not properly measure performance because its data (as in, the actual HTML it's generating to tell you the timing, number of queries, etc.) is swept-up by the page's cache.

Timber provides some quick shortcuts to measure page timing. Here's an example of them in action...

```php
/* single.php */
$start = TimberHelper::start_timer(); //this generates a starting time
$context = Timber::get_context();
$context['post'] = new TimberPost();
$context['whatever'] = get_my_foo();
Timber::render('single.twig', $context, 600);
echo TimberHelper::stop_timer($start); //this reports the time diff by passing the $start time
```

