# Debugging

### Using Twig's native functions
Twig includes a `dump` function that can output the properties of an object. To use `WP_DEBUG` must be set to true.

##### wp-config.php

```php
define('WP_DEBUG', true);
```

##### single.twig
```html
{{dump(post)}}
```

Which will give you:

<a href="http://imgur.com/5Xu53Fk"><img src="http://i.imgur.com/5Xu53Fk.png" title="Hosted by imgur.com"/></a>

You can also dump _everything_ sent to your template via:

```html
{{dump()}}
```

This will give you something like:

<a href="http://imgur.com/5ZD8VDd"><img src="http://i.imgur.com/5ZD8VDd.png" title="Hosted by imgur.com"/></a>

* * *

### Using Timber Debug Bar plugin
There's a [Timber add-on](http://wordpress.org/plugins/debug-bar-timber/) for the [WordPress debug bar](https://wordpress.org/plugins/debug-bar/). Warning: this currently requries PHP 5.4. I'm working on fixing whatever's going on for PHP 5.3

### Using (Legacy) Timber Filters
You can also use some quick filters on an object. These are legacy and will be removed in favor of using Twig's built-in functionality. However, these do not require that WP_DEBUG be turned on.
```html
	{{post|print_r}}
```
