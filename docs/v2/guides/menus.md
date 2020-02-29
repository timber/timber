---
title: "Menus"
order: "150"
---

In Timber, you can use `Timber\Menu` to make a standard WordPress menu available to the Twig template as an object you can loop through.

Once the menu becomes available to the context, you can get items from it in a way that is a little smoother and more versatile than WordPress’s `wp_nav_menu()` function. (And you never again need to rely on a crazy [Walker Function](https://codex.wordpress.org/Class_Reference/Walker)).

## Initializing a menu

`Timber\Menu` can be initialized with different values:

You can pass the slug of the menu you want to use:

```php
$menu = new Timber\Menu( 'site-tools' );
```

Or the ID number of the menu:

```php
$menu = new Timber\Menu( 3 );
```

Or the proper name from the admin:

```php
$menu = new Timber\Menu( 'Primary Navigation' );
```

Or the slug of the registered location:

```php
$menu = new Timber\Menu( 'primary' );
```

Or pass nothing. This is good if you have only one menu. In that case Timber will just grab what you got.

```php
$menu = new Timber\Menu();
```

## Options

Optionally, you can send additional options to `Timber\Menu`. Current only `depth` is supported (see https://developer.wordpress.org/reference/functions/wp_nav_menu/ for reference)

```php
$args = array(
    'depth' => 2,
);
$menu = new Timber\Menu( 'primary', $args );
```

## Setting up a menu globally

The first thing to do is to initialize your menu using `Timber\Menu`. This will make the menu available as an object to work with in the context. Because we need the menu on every page, we can add it to the global context through the `timber/context` filter:

**functions.php**

```php
<?php

add_filter( 'timber/context', 'add_to_context' );

function add_to_context( $context ) {
    // So here you are adding data to Timber's context object, i.e...
    $context['foo'] = 'I am some other typical value set in your functions.php file, unrelated to the menu';

    // Now, in similar fashion, you add a Timber Menu and send it along to the context.
    $context['menu'] = new \Timber\Menu( 'primary-menu' );

    return $context;
}
```

Now, when you call `Timber::context()`, your menu will already be set in the context. You don’t need to initialize the menu in all your template files.

**index.php**

```php
<?php

$context = Timber::context();

Timber::render( 'index.twig', $context );
```

## Displaying the menu items

In your Twig file, you can loop over the menu items like normal arrays. You’re in complete control of the markup.

**index.twig**

```twig
<nav>
    <ul class="nav-main">
        {% for item in menu.items %}
            <li class="nav-main-item {{ item.classes|join(' ') }}">
                <a class="nav-main-link" href="{{ item.link }}" {{ item.is_target_blank ? 'target="_blank"' }}>{{ item.title }}</a>

                {% if item.children %}
                    <ul class="nav-drop">
                        {% for child in item.children %}
                            <li class="nav-drop-item">
                                <a href="{{ child.link }}" {{ item.is_target_blank ? 'target="_blank"' }}>{{ child.title }}</a>
                            </li>
                        {% endfor %}
                    </ul>
                {% endif %}
            </li>
        {% endfor %}
    </ul>
</nav>
```

## The current menu item

When you need to check whether a menu item is the current menu item, you can use the `current` property.
Here’s an example to display child menu items only if it’s the menu item is the currently visited page:

**Twig**

```twig
{% if item.current and item.children %}
    <ul class="nav-child">
        {% for child in item.children %}
            <li class="nav-child-item">
                <a class="nav-child-link" href="{{ child.link }}">{{ child.title }}</a>
            </li>
        {% endfor %}
    </ul>
{% endif %}
```

Other properties that are available are `current_item_parent` for direct parents of a menu item and `current_item_ancestor` for when you have deeper nesting.

### Getting the current menu item outside the loop

Say you want to display sibling menu items of the current page, for example in
a sidebar. You can use the `current_item()` helper to achieve this:

**Twig**

```twig
<div class="sidebar">
  <a href="{{ menu.current_item.link }}">
    {{ menu.current_item.title }}
  </a>
  <ul>
    {% for child in menu.current_item.children %}
      <li>
        <a href="{{ child.link }}">{{ child.title }}</a>
      </li>
    {% endfor %}
  </ul>
</div>
```

### Getting a current menu ancestor

You can limit the traversal depth of the tree when looking for the current
item by passing a `$depth` parameter to `current_item`.
Going off the previous example, say you wanted the root node of your sidebar
to be the _second_ level of the main menu tree. In that case, you could
specify a depth of 2:

**Twig**

```twig
<div class="sidebar secondary-nav">
  <a href="{{ menu.current_item(2).link }}">
    {{ menu.current_item(2).title }}
  </a>
  <ul class="third-level-nav-items">
    {% for child in menu.current_item(2).children %}
      <li>
        <a href="{{ child.link }}">{{ child.title }}</a>
      </li>
    {% endfor %}
  </ul>
</div>
```

### Getting the current top-level item

For getting the top-level (that is, level-1) item corresponding to the
current post, you can call `current_top_level_item()`. This method
takes no arguments and is just an alias for `current_item(1)`.

## Menu item targets

To get the target for a menu item, you can use `item.target`:

```twig
<a href="{{ item.link }}" target="{{ item.target }}">
```

In the menu edit screen, WordPress offers a checkbox for each menu item that lets the administrator decide whether to open a menu item in a new tab. The `item.target` function will return `_blank` if that checkbox is ticked, and `_self` if it’s not ticked.

You might not need a value `_self` for the target attribute, because `_self` is the default value, which opens a link in the same tab/window. If you want to add `target="_blank"` only if it’s really needed, then you can use the conditional function `item.is_target_blank` :

```twig
<a href="{{ item.link }}" {{ item.is_target_blank ? 'target="_blank"' }}>
```

What about **external links**? If your site is `example.org`, then `google.com/whatever` is an external link. Whether it makes sense to open links in new tabs is not the topic to discuss here. If you still decide that you want that, you can check whether an item link is external with `item.is_external`:

```twig
<a href="{{ item.link }}" {{ item.is_external ? 'target="_blank"' }}">
```

You could also use it in combination with `item.is_target_blank`:

```twig
<a href="{{ item.link }}" {{ item.is_target_blank or item.is_external ? 'target="_blank"' }}>
```

## Tips

- [Add items dynamically](https://github.com/jarednova/timber/issues/200)
