---
title: "Template Locations"
menu:
  main:
    parent: "guides"
---

You can set your own locations for your twig files with...

```php
<?php
add_filter( 'timber/loader/paths', function($paths) {
	$paths[] = array('/Users/jared/Sandbox/templates');
	
	return $paths;
});
```

Use the full file path to make sure Timber knows what you're trying to draw from. You can also send an array for multiple locations:

```php
<?php
add_filter( 'timber/loader/paths', function($paths) {
	$paths[] = array(
		'/Users/jared/Sandbox/templates',
		'~/Sites/timber-templates/',
		ABSPATH.'/wp-content/templates'
	);
	
	return $paths;
});
```

## Register your own namespaces

You can also use namespaces in your locations too, just define it as the value next to a path, for example:

```php
<?php
add_filter( 'timber/loader/paths', function($paths) {
	$paths['styleguide'] = array(
		ABSPATH.'/wp-content/styleguide'
	);
	
	return $paths;
});
```

In the example above the namespace is called `styleguide`. You must prefix this with `@` when using it in templates (that's how Twig can differentiate namespaces from regular paths).
Assuming you have a template called `menu.twig` within that namespace, you would call it like so:

```twig
{% include '@styleguide/menu.twig' %}
```

You can also register multiple paths for the same namespace. Order is important as it will look top to bottom and return the first one it encounters, for example:

```php
<?php
add_filter( 'timber/loader/paths', function($paths) {
	$paths['styleguide'] = array(
		ABSPATH.'/wp-content/styleguide',
		'/Users/jared/Sandbox/styleguide'
	);
	
	return $paths;
});
```

You only need to do this once in your project (in `functions.php` of your theme). When you call one of the render or compile functions from a PHP file (say `single.php`), Timber will look for Twig files in these locations before it checks the child or parent theme.

## Changing the default folder for Twig files

By default, Timber looks in your child and parent theme’s `views` directory to pull `.twig` files. If you don't like the default `views` directory (which by default resides in your theme folder), you can change that.

Example: If I want to use `/wp-content/themes/my-theme/twigs` as my default folder, I can either configure it with a string or use an array with fallbacks.

### Configure with a string

```php
<?php
Timber::$dirname = 'twigs';
```

### Use an array with fallbacks

This is an alternative to configuring `$dirnames` with a string.

```php
<?php
Timber::$dirname = array( 'templates', 'templates/shared/mods', 'twigs', 'views' );
```

## Subdirectories

You can always reference **subdirectories** in your template folders relatively. For example:

```php
<?php
Timber::render( 'shared/headers/header-home.twig' );
```
... might correspond to a file in  
`/wp-content/themes/my-theme/views/shared/headers/header-home.twig`.
