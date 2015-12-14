# Custom Page Templates

There are a few ways to manage custom pages in WordPress and Timber, in order from simple-to-complex:

### Custom Twig File
Say you've created a page called "About Us" and WordPress has given it the slug `about-us`. If you're using the [Timber Starter Theme](https://github.com/Upstatement/timber-starter-theme) you can simply...
- Create a file called `page-about-us.twig` inside your `views` and go crazy.
- I recommend copying-and-pasting the contents of [`page.twig`](https://github.com/Upstatement/timber-starter-theme/blob/master/views/page.twig) into here so you have something to work from.

##### How does this work?
In the `page.php` file you'll see this code...
```php
Timber::render(array('page-' . $post->post_name . '.twig', 'page.twig'), $context);
```
Which is telling PHP to first look for a twig file named `page-{{slug}}.twig` and falling back to [`page.twig`](https://github.com/Upstatement/timber-starter-theme/blob/master/views/page.twig) if that doesn't exist.

* * *

### Custom PHP File
If you need to do something special for this page in PHP, you can use standard WordPress [template hierarchy](http://codex.wordpress.org/Template_Hierarchy) to gather and manipulate data for this page. In the above example, you would create a file called `/wp-content/themes/my-theme/page-about-us.php` and populate it with the necessary PHP. Again, you can use the contents of the starter theme's [`page.php`](https://github.com/Upstatement/timber-starter-theme/blob/master/page.php) file as a guide.

* * *

### Custom Page Template
```php
<?php
/*
 * Template Name: My Custom Page
 * Description: A Page Template with a darker design.
 */

// Code to display Page goes here...
```

In the WordPress admin, this will now display in your page's list of available templates like so:

![wordpress custom page template chooser](http://codex.wordpress.org/images/thumb/a/a3/page-templates-pulldown-screenshot.png/180px-page-templates-pulldown-screenshot.png)

I recommend naming it something like `/wp-content/themes/my-theme/template-my-custom-page.php`. Do NOT name it something beginning with `page-` or WP will get very confused. Here's [an example of what the PHP in this file](https://github.com/Upstatement/blades/blob/master/template-person.php) looks like.
