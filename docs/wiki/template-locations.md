# Template Locations

You can set arbitrary locations for your twig files with...

```php
/* functions.php */
Timber::$locations = '/Users/jared/Sandbox/templates';
```

Use the full file path to make sure Timber knows what you're trying to draw from. You can also send an array for multiple locations..

```php
/* functions.php */
Timber::$locations = array(	'/Users/jared/Sandbox/templates',
							'~/Sites/timber-templates/',
							ABSPATH.'/wp-content/templates'
						);
```

You only need to do this once in your project (like in `functions.php`) then when you call from a PHP file (say `single.php`) Timber will look for twig files in these locations before the child/parent theme.

* * *

### Changing the default folder for .twig files

By default, Timber looks in your child and parent theme's `views` directory to pull `.twig` files. If you don't like the default `views` directory (which by default resides in your theme folder) you can change that to. Example: I want to use `/wp-content/themes/my-theme/twigs`...

###### Configure with a string:

```php
/* functions.php */
Timber::$dirname = 'twigs';
```

###### You can also send an array with fallbacks:

```php
/* functions.php */
Timber::$dirname = array('templates', 'templates/shared/mods', 'twigs', 'views');
```

A quick note on **subdirectories**: you can always reference these relatively. For example:

```php
Timber::render('shared/headers/header-home.twig');
```
... might correspond to a file in `/wp-content/themes/my-theme/views/shared/headers/header-home.twig`.
