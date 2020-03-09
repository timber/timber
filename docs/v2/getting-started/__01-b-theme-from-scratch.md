---
title: "Introduction"
---




For this guide, we assume that you already a local development environment to develop your WordPress projects with a WordPress website that you’ve set up.

## Tutorial theme

If you want to start from scratch, you can use the following command. Run it from the **wp-content/themes** folder of your WordPress installation.

```bash
composer create-project timber/getting-started getting-started
```

## A view

The goal of a templating engine is to separate your logic from your HTML templates. When working with Timber, we call the Twig templates "views".

To render a template, you can use `Timber::render()`.

**index.php**

```php
<?php

Timber::render( 'index.twig' );
```

This will look for an `index.twig` file in the **views** folder of your theme.

**index.twig**

```twig
<h1>A Timber Tutorial</h1>
```

We don’t have any data yet. Let’s create an array with data that we then pass to our view with the second parameter for `Timber::render()`.

```php
<?php

$data = [
    'title' => 'A Timber Tutorial',
];

Timber::render( 'index.twig', $data );
```

**index.twig**

```twig
<h1>{{ title }}</h1>
```

Our associative `$data` array was turned into variables that we can use directly in our Twig template.

The `{{ }}` delimiters are more or less the same as the `echo` command in PHP. Whenever you want to output a variable or an expression in Twig, use the double curly braces. You can also see that when we reference variables in Twig, we don’t need a `$` like we do in PHP.

## Make it dynamic

The `title` variable is still a static string. Let’s switch it with the title of your post.

**index.php**

```php
<?php

$data = [
    'title' => get_the_title(),
];

Timber::render( 'index.twig', $data );
```

We used the `get_the_title()` function instead of a static title to get the title of your WordPress post. See how we fetch the data we need in PHP and output it in Twig? This is what we call *separation of concerns*.


## Using partials

You can start replacing different parts of your theme with Timber. Instead of using [`get_template_part()`](https://developer.wordpress.org/reference/functions/get_template_part/) or [`get_extended_template_part()`](https://github.com/johnbillion/extended-template-parts), you can use `Timber::render()`. Pass in the name of a partial that you want to load.



---

## A basic WordPress theme

Let’s create a very basic WordPress theme based on Timber together. Create a new file **style.css** and put the following contents in it.

```css

```

Next, create an **index.php** file. Let’s fill it with an example template. This is what most of the usual WordPress theme templates look like, with a call to `get_header()` and `get_footer()`.

**index.php**

```php
<?php get_header(); ?>

    <h1>A Timber Tutorial</h1>

<?php get_footer(); ?>
```

Congratulations, you now just built one of the most basic themes you can have in WordPress. Now, go to your WordPress Admin and activate the newly created theme under **Design** &rarr; **Themes**.

## Turn your theme into a Timber theme

We are going to turn this template into a Timber theme step by step. First, let’s move the `<h1>` into a separate template.

Create a new folder named **views** in the root of your theme and then create a Twig file named **index.twig** with the following contents in there.

```twig
<h1>A Timber Tutorial</h1>
```

The **views** folder is where we will put all our Twig template files.

We can now load that template from our **index.php**.

```php
<?php

get_header();

Timber::render( 'heading-1.twig' );

get_footer();
```

The `Timber::render()` function renders (echoes) a Twig template. It accepts the name of the Twig template we want to render.

You might notice that we’ve removed the HTML part from our PHP and moved it into a separate template. This way, we don’t need to think about wrapping all our PHP code snippets within our template with `<?php` tags. It’s enough to have the PHP opening tag at the beginning of our file. That feels much cleaner, right?

## Make your data dynamic

The template above is still a bit static. Let’s create a variable that we can load in our template.

```php
<?php

get_header();

$data = [
    'title'  => 'A Timber Tutorial',
];

Timber::render( 'heading-1.twig', $data );

get_footer();
```

We declare an associative array named `$data`. We then pass the `$data` array to the `Timber::render()` function as the second argument.

Let’s go back to our **views/heading-1.twig** file and replace the string we had in there with a variable.

```twig
<h1>{{ title }}</h1>
```

Our `$data` array was turned into variables that we can use directly in our Twig template.

The `{{ }}` delimiters are more or less the same as the `echo` command in PHP. Whenever you want to output a variable or an expression in Twig, use the double curly braces. You can also see that when we use variables in Twig, we don’t need a `$` like we do in PHP.

The `title` variable is still a static string. Let’s switch it with the title of your post.

**index.php**

```php
$data = [
    'title' => get_the_title(),
];

Timber::render( 'index.twig', $data );
```

We used the `get_the_title()` function instead of a static title to get the title of your WordPress post. See how we fetch the data we need in PHP and output it in Twig? This is what we call separation of concerns.

## The context

In Timber, we prepare a lot for you in the background, so you don’t have to remember function names like `get_the_title()`.

Most of Timber’s templates look like this:

**index.php**

```php
<?php

$context = Timber::context();

Timber::render( 'index.twig', $context );
```

What happens here? In Timber, you can use the `Timber::context()` function to get an array of data that you need in most of your templates. It can be things like the site name, the description or the navigation menu that you probably need in every template. Timber always prepares some of the data you will need behind the scenes.

…

This is still not all we want to do. Let’s keep improving. Read about [Template Inheritance and Includes](@todo) in the next section.
