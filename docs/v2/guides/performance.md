---
title: "Performance/Caching"
order: "1300"
---

Timber, especially in conjunction with WordPress and Twig, offers a variety of caching strategies to optimize performance. Here’s a quick rundown of some of the options, ranked in order of most-broad to most-focused.

## tl;dr

In my tests with Debug Bar, Timber has no measurable performance hit. Everything compiles to PHP. @fabpot has an [overview of the performance costs on his blog](https://fabien.potencier.org/article/34/templating-engines-in-php/) (scroll down to the table).

## Cache Everything

You can still use plugins like [W3 Total Cache](https://wordpress.org/plugins/w3-total-cache/) in conjunction with Timber. In most settings, this will _skip_ the Twig/Timber layer of your files and serve static pages via whatever mechanism the plugin or settings dictate.

## Cache the Entire Twig File and Data

With Timber you can cache the full Timber render/compile calls. When you do this, the whole template you render and its data will be cached. This results in faster page rendering by skipping queries and Twig compilations. But here’s the cool part: Timber hashes the fields in the view context. This means that **as soon as the data changes, the cache is automatically invalidated**. Yay!

For Timber caching to take effect on your `Timber::render()` and `Timber::compile()` calls, you need to set the `$expires` argument. If the `$expires` argument is not set, Timber will not cache that particular template, even if the (global)cache mode is set.

Example:

```php
$context['posts'] = Timber::get_posts();

Timber::render('index.twig', $context, 600);
```

In this example, Timber will cache the template for 10 minutes (600 / 60 = 10) with the default cache mode which is "transient".
You can change the cache mode for Timber globally or on a per method basis. See [Timber cache modes](#timber-cache-modes) for more information.

This caching method is very effective, but crude - the whole template is cached. So if you have any context dependent sub-views (eg. current user), this mode won’t do.

### Timber cache modes

Timber has 5 cache modes that it can use for the Timber `Timber::render()` and `Timber::compile()` methods. The following cache modes are available:

| Mode                                  | Description                                                                                                 |
| ------------------------------------- | ----------------------------------------------------------------------------------------------------------- |
| `Timber\Loader::CACHE_NONE`           | Disable caching                                                                                             |
| `Timber\Loader::CACHE_OBJECT`         | WP Object Cache                                                                                             |
| `Timber\Loader::CACHE_TRANSIENT`      | Transients                                                                                                  |
| `Timber\Loader::CACHE_SITE_TRANSIENT` | Network wide transients                                                                                     |
| `Timber\Loader::CACHE_USE_DEFAULT`    | Use whatever caching mechanism is set as the default for `Timber\Loader`, the default is `CACHE_TRANSIENT`. |

By default the cache mode is set to transients. You can change the default cache mode globally by using a filter or on a per method basis. We will go over them both.

#### Set Timber cache mode globally

The default cache mode can be changed by using the `timber/cache/mode` filter. For example:

```php
add_filter('timber/cache/mode', function () {
    return Timber\Loader::CACHE_OBJECT;
});
```

Sets the global/default cache mode to `CACHE_OBJECT`.

#### Set Timber cache mode per compile or render method

As a fourth parameter for [Timber::render()](https://timber.github.io/docs/v2/reference/timber-timber/#render) and [Timber::compile()](https://timber.github.io/docs/v2/reference/timber-timber/#compile), you can set the `$cache_mode`.

For example:

```php
Timber::render($filenames, $data, 600, Timber\Loader::CACHE_OBJECT);
```

## Cache the Twig File (but not the data)

Every time you render a `.twig` file, Twig compiles all the HTML tags and variables into a big, nasty blob of function calls and echo statements that actually gets run by PHP. In general, this is pretty efficient. However, you can cache the resulting PHP blob by turning on Twig’s cache via:

**functions.php**

```php
add_filter('timber/twig/environment/options', function ($options) {
    $options['cache'] = true;

    return $options;
});
```

You can look in your your `/vendor/timber/timber/cache` directory to see what these files look like.

If you want to change the path where Timber caches the Twig files, you can pass in an absolute path for the `cache` option:

```php
add_filter('timber/twig/environment/options', function ($options) {
    $options['cache'] = '/absolute/path/to/twig_cache';

    return $options;
});
```

This does not cache the _contents_ of the variables. But rather, the structure of the Twig files themselves (i.e. the HTML and where those variables appear in your template). Once enabled, any change you make to a `.twig` file (just tweaking the HTML for example) will not go live until the cache is flushed.

Thus, during development, you should enable the option for `auto_reload`:

```php
add_filter('timber/twig/environment/options', function ($options) {
    $options['cache']       = true;
    $options['auto_reload'] = true;

    return $options;
});
```

Enabling `Timber::$cache` works best as a last step in the production process. Once enabled, any change you make to a `.twig` file (just tweaking the HTML for example) will not go live until the cache is flushed.

Note that when `WP_DEBUG` is set to `true`, changes you make to `.twig` files will be reflected on the site regardless of the `Timber::$cache` value.

To flush the Twig cache you can do this:

```php
$loader = new Timber\Loader();
$loader->clear_cache_twig();
```

## Reuse the Twig Environment

By default, Timber creates a new `Twig\Environment` for every `Timber::compile()`, `Timber::render()` and `Timber::compile_string()` call: it registers Timber's Twig functions and filters and the cache extension, and runs every `timber/twig` hook, each time. Twig then initializes its extensions again on the first render of each new instance. Templates themselves are not recompiled, but the setup costs time on every call: on a development machine, `Timber::compile()` went from about 0.23 ms to about 0.07 ms per call once the environment was shared. A page that compiles a handful of templates will not notice. A page that compiles dozens, for example one `Timber::compile()` per block render callback, pays the setup each time.

You can tell Timber to build the environment once and reuse it for all renders in the request (or in the whole process, for WP-CLI commands and queue workers):

**functions.php**

```php
add_filter('timber/twig/environment/shared', '__return_true');
```

The template you pass to `Timber::compile()` and `Timber::render()` is still looked up in the template locations of its own calling file. What changes is that the environment is configured once and keeps the templates it has loaded. Keep this in mind when you enable it:

- Hooks that configure the environment run once, when it is built: `timber/twig`, `timber/loader/twig`, `timber/twig/environment/options`, `timber/twig/functions`, `timber/twig/filters`, `timber/twig/escapers`, `timber/cache/enable_extension` and `timber/cache/extension/lifetime`, as well as the deprecated `Timber::$autoescape`, `Timber::$twig_cache` and `Timber::$cache` properties. Register them before the first compile, for example on `after_setup_theme` or `init`. The `{% cache %}` tag keeps the `timber/cache/mode` result of the `Timber\Loader` instance that built the environment.

- Adding and removing one of those hooks around a single call has no effect once the environment exists. Security-relevant settings, like `autoescape` or removing the `function` and `fn` Twig functions through `timber/twig/functions` before compiling an untrusted string, must be registered before the first compile.
- Twig globals added in `timber/twig` are evaluated once per environment build. If a value changes during the request, call `addGlobal()` again with the same name. Twig allows updating an existing global after initialization, but not adding a new one.
- Date format, timezone and number format are read once per environment build, so `switch_to_locale()` has no effect on an existing environment.
- Loader changes made in `timber/twig` or `timber/loader/twig`, with `$twig->getLoader()->addPath()` or `$twig->setLoader()`, do not apply to templates rendered through `Timber::compile()` or `Timber::render()`. They still apply to `Timber::compile_string()` and to direct `Timber\Loader::get_twig()` callers. Use the `timber/locations` filter (it supports namespaces) or the `timber/loader/loader` filter instead.
- `{% use %}` traits are loaded the first time a template is instantiated in the shared environment and kept for later renders of that template, even from another calling file. `{% extends %}` and `{% include %}` are resolved on every full render through `Timber::compile()` or `Timber::render()`. Block renders through `Timber::compile_twig_block()` do not re-resolve the parent: they use the one left by the last full render of that template, or resolve it once and keep it.
- Code that calls `Timber\Loader::get_twig()` directly receives the shared instance. Twig throws a `LogicException` if filters, functions, extensions or new globals are added to an environment that has already loaded a template. Between renders, the environment keeps the loader it was built with, so `Timber::compile_string()` and direct callers resolve `{% include %}` against that loader. Direct callers can call `setLoader()` before loading files that need specific paths, and should restore the previous loader afterwards.
- On multisite, the environment belongs to the current blog. Each `switch_to_blog()` and `restore_current_blog()` rebuilds it on the next render.

`Timber\Loader::clear_cache_twig()` (or `Timber\Cache\Cleaner::clear_cache_twig()`) discards the shared environment; the next render builds a new one. With Twig's `cache` option enabled, it also deletes the compiled templates.

## Cache _Parts_ of the Twig File and Data

If you want to use [`cache` tag](https://twig.symfony.com/doc/3.x/tags/cache.html) in Twig, you’ll have to install the `twig/cache-extra` package:

```bash
composer require twig/cache-extra
```

And register it in Timber like this:

**functions.php**

```php
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Twig\Extra\Cache\CacheExtension;
use Twig\Extra\Cache\CacheRuntime;
use Twig\RuntimeLoader\RuntimeLoaderInterface;

add_filter('timber/twig', function ($twig) {
		$twig->addRuntimeLoader(new class implements RuntimeLoaderInterface
		{
			public function load($class)
			{
				if (CacheRuntime::class === $class) {
					return new CacheRuntime(new TagAwareAdapter(new FilesystemAdapter('', 0, TIMBER_LOC . '/cache/twig')));
				}
			}
		});
		$twig->addExtension(new CacheExtension());

    return $twig;
});
```

You can then use it like this:

```twig
{% cache 'index;content' %}
    {% for post in posts %}
        {% include ['tease-' ~ post.post_type ~ '.twig', 'tease.twig'] %}
    {% endfor %}
{% endcache %}
```

Read more about it in [Twig’s `cache` documentation](https://twig.symfony.com/doc/3.x/tags/cache.html).

If you want to use something meaningful for the cache key, you can also generate a cache key from the variables that you use. In the following example, we generate a cache key from `$posts` and then generate a key from it:

```php
$generator = new Timber\Cache\KeyGenerator();
$key = $generator->generateKey($posts);
```

### Extra: TimberKeyGeneratorInterface

Instead of hashing a whole object, you can specify the cache key in the object itself. If the object implements `TimberKeyGeneratorInterface`, it can pass a unique key through the method `get_cache_key()`. That way a class could for example pass a `'last_updated'` property as the unique key.
If arrays contain the key `_cache_key`, that one is used as cache key.

This may save yet another few processor cycles.

## Cache the PHP data

Sometimes the most expensive parts of the operations are generating the data needed to populate the twig template. You can of course use WordPress’s default [Transient API](https://codex.wordpress.org/Transients_API) to store this data.

You can also use some [syntactic sugar](https://en.wikipedia.org/wiki/Syntactic_sugar) to make the checking/saving/retrieving of transient data a bit easier:

**home.php**

```php
$context = Timber::context();

$context['main_stories'] = Timber\Helper::transient('main_stories', function () {
    $posts = Timber::get_posts();

    // As an example, do something expensive with these posts
    $extra_teases = get_field('my_extra_teases', 'options');

    foreach ($extra_teases as &$tease) {
        $tease = Timber::get_post($tease);
    }

    $main_stories = [
        'posts' => $posts,
        'extra_teases' => $extra_teases,
    ];

    return $main_stories;
}, 600);

Timber::render('home.twig', $context);
```

Here `main_stories` is a totally made-up variable. It could be called `foo`, `bar`, `elephant`, etc.

## Measuring Performance

Some tools like Debug Bar may not properly measure performance because its data (as in, the actual HTML it’s generating to tell you the timing, number of queries, etc.) is swept-up by the page’s cache.

Timber provides some quick shortcuts to measure page timing. Here’s an example of them in action:

**single.php**

```php
// This generates a starting time
$start = Timber\Helper::start_timer();

$context = Timber::context([
    'whatever' => get_my_foo(),
]);

Timber::render('single.twig', $context, 600);

// This reports the time diff by passing the $start time
echo Timber\Helper::stop_timer($start);
```

## Important notes

- Never use `{% apply spaceless %}` tags to minify your HTML output. These tags are only meant to control whitespace between HTML tags.
