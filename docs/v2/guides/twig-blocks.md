---
title: "Twig Blocks"
order: "375"
---

Timber provides powerful functionality for rendering specific [blocks](https://twig.symfony.com/doc/3.x/functions/block.html) from Twig templates, both from PHP and within Twig templates themselves. This allows you to create reusable template components and render only specific portions of a template when needed.

## What are Twig Blocks?

Twig blocks are sections of a template that can be defined with `{% block %}` tags. They're commonly used for template inheritance, but with Timber's block rendering functions, you can also use them as standalone, reusable components.

```twig
{# components/alerts.twig #}
{% block error %}
<div class="alert alert-error" role="alert">
    {{ message }}
</div>
{% endblock %}

{% block success %}
<div class="alert alert-success" role="alert">
    {{ message }}
</div>
{% endblock %}

{% block warning %}
<div class="alert alert-warning" role="alert">
    {{ message }}
</div>
{% endblock %}
```

## PHP Functions

Timber provides two PHP functions for working with Twig blocks:

### `Timber::compile_twig_block()`

Returns the rendered block as a string.

```php
/**
 * @param string              $block_name  The name of the block to render
 * @param array|string        $filenames   Template file(s) to load
 * @param string|array|null   $caller      Optional. Value from a `LocationManager` method to control lookup.
 *                                         Use `LocationManager::get_calling_script_dir()` (string) or
 *                                         `LocationManager::get_locations()` (array). When `null`, Timber uses
 *                                         `LocationManager::get_calling_script_dir(1)`.
 * @param array               $data        Data to pass to the template
 * @param bool|int|array      $expires     Cache expiration (optional)
 * @param string              $cache_mode  Cache mode (optional)
 * @return string|false                     The rendered block content
 */
Timber::compile_twig_block(
    $block_name,
    $filenames,
    $caller = null,
    $data = [],
    $expires = false,
    $cache_mode = Loader::CACHE_USE_DEFAULT
)
```

### `Timber::render_twig_block()`

Directly echoes the rendered block content.

```php
/**
 * @param string              $block_name  The name of the block to render
 * @param array|string        $filenames   Template file(s) to load
 * @param string|array|null   $caller      Optional. Value from a `LocationManager` method to control lookup.
 *                                         Use `LocationManager::get_calling_script_dir()` (string) or
 *                                         `LocationManager::get_locations()` (array). When `null`, Timber uses
 *                                         `LocationManager::get_calling_script_dir(1)`.
 * @param array               $data        Data to pass to the template
 * @param bool|int|array      $expires     Cache expiration (optional)
 * @param string              $cache_mode  Cache mode (optional)
 */
Timber::render_twig_block(
    $block_name,
    $filenames,
    $caller = null,
    $data = [],
    $expires = false,
    $cache_mode = Loader::CACHE_USE_DEFAULT
)
```

## Twig Function

Within Twig templates, you can use the `render_twig_block()` function:

```twig
{{ render_twig_block(block_name, filenames, data, expires, cache_mode) }}
```

## Basic Usage Examples

### Simple Alert Component

Create a reusable alert component template:

**views/components/alerts.twig**
```twig
{% block error %}
<div class="bg-red-500 text-white p-4 rounded" role="alert">
    <strong>Error:</strong> {{ message }}
</div>
{% endblock %}

{% block success %}
<div class="bg-green-500 text-white p-4 rounded" role="alert">
    <strong>Success:</strong> {{ message }}
</div>
{% endblock %}

{% block warning %}
<div class="bg-yellow-500 text-black p-4 rounded" role="alert">
    <strong>Warning:</strong> {{ message }}
</div>
{% endblock %}
```

**PHP Usage:**
```php
// Display a success message
$success_alert = Timber::compile_twig_block('success', 'components/alerts.twig', [
    'message' => 'Your form was submitted successfully!'
]);

echo $success_alert;

// Or render directly
Timber::render_twig_block('error', 'components/alerts.twig', [
    'message' => 'Please fix the errors below.'
]);
```

**Twig Usage:**
```twig
{# In your main template #}
<main>
    <h1>Contact Form</h1>
    
    {% if form_errors %}
        {{ render_twig_block('error', 'components/alerts.twig', {
            message: 'Please correct the errors below.'
        }) }}
    {% endif %}
    
    {% if form_success %}
        {{ render_twig_block('success', 'components/alerts.twig', {
            message: 'Thank you! Your message has been sent.'
        }) }}
    {% endif %}
    
    {# Rest of your form here #}
</main>
```

### Card Components

Create a flexible card component with multiple variations:

**views/components/cards.twig**
```twig
{% block basic_card %}
<div class="card">
    <h3>{{ title }}</h3>
    <p>{{ content }}</p>
</div>
{% endblock %}

{% block image_card %}
<div class="card card--with-image">
    <img src="{{ image.src }}" alt="{{ image.alt }}" class="card__image">
    <div class="card__content">
        <h3>{{ title }}</h3>
        <p>{{ content }}</p>
        {% if link %}
            <a href="{{ link.url }}" class="card__link">{{ link.text }}</a>
        {% endif %}
    </div>
</div>
{% endblock %}

{% block featured_card %}
<div class="card card--featured">
    <div class="card__badge">Featured</div>
    <h3>{{ title }}</h3>
    <p>{{ content }}</p>
    <div class="card__meta">
        <span>{{ date }}</span>
        <span>{{ author }}</span>
    </div>
</div>
{% endblock %}
```

**PHP Usage:**
```php
// Get some posts and render different card types
$featured_post = Timber::get_post(['meta_key' => 'featured', 'meta_value' => true]);
$recent_posts = Timber::get_posts(['posts_per_page' => 3]);

// Render featured card
if ($featured_post) {
    echo Timber::compile_twig_block('featured_card', 'components/cards.twig', [
        'title' => $featured_post->title,
        'content' => $featured_post->excerpt,
        'date' => $featured_post->date,
        'author' => $featured_post->author->name,
    ]);
}

// Render regular cards for recent posts
foreach ($recent_posts as $post) {
    echo Timber::compile_twig_block('image_card', 'components/cards.twig', [
        'title' => $post->title,
        'content' => $post->excerpt,
        'image' => $post->thumbnail,
        'link' => [
            'url' => $post->link,
            'text' => 'Read More'
        ]
    ]);
}
```

## Advanced Usage Examples

### Dynamic Component Selection

Use variables to determine which block to render:

```twig
{# views/page.twig #}
{% for component in page.components %}
    {{ render_twig_block(component.type, 'components/all.twig', component.data) }}
{% endfor %}
```

```php
// In your PHP file
$context['page'] = [
    'components' => [
        [
            'type' => 'hero',
            'data' => [
                'title' => 'Welcome to Our Site',
                'subtitle' => 'Building amazing experiences',
                'background_image' => $hero_image
            ]
        ],
        [
            'type' => 'text_block',
            'data' => [
                'content' => 'Lorem ipsum dolor sit amet...',
                'alignment' => 'center'
            ]
        ],
        [
            'type' => 'call_to_action',
            'data' => [
                'title' => 'Ready to Get Started?',
                'button_text' => 'Contact Us',
                'button_url' => '/contact'
            ]
        ]
    ]
];
```

### Layout Section Rendering

Render specific sections of a larger layout:

**views/layouts/page-sections.twig**
```twig
{% block header %}
<header class="page-header">
    <h1>{{ title }}</h1>
    {% if subtitle %}
        <p class="page-subtitle">{{ subtitle }}</p>
    {% endif %}
</header>
{% endblock %}

{% block sidebar %}
<aside class="sidebar">
    <h3>Related Links</h3>
    <ul>
        {% for link in sidebar_links %}
            <li><a href="{{ link.url }}">{{ link.title }}</a></li>
        {% endfor %}
    </ul>
</aside>
{% endblock %}

{% block footer %}
<footer class="page-footer">
    <p>&copy; {{ "now"|date('Y') }} {{ site.title }}</p>
</footer>
{% endblock %}
```

### Email Template Components

Create reusable email template blocks:

**views/email/components.twig**
```twig
{% block email_header %}
<div style="background: #f8f9fa; padding: 20px; text-align: center;">
    <img src="{{ logo_url }}" alt="{{ site_name }}" style="max-width: 200px;">
    <h1 style="color: #333; margin: 10px 0;">{{ subject }}</h1>
</div>
{% endblock %}

{% block email_button %}
<div style="text-align: center; margin: 20px 0;">
    <a href="{{ url }}" style="background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; display: inline-block;">
        {{ text }}
    </a>
</div>
{% endblock %}

{% block email_footer %}
<div style="background: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #ddd;">
    <p style="color: #666; margin: 0;">{{ footer_text }}</p>
    <p style="color: #666; margin: 5px 0 0;">
        <a href="{{ unsubscribe_url }}" style="color: #666;">Unsubscribe</a>
    </p>
</div>
{% endblock %}
```

**PHP Usage for Email Generation:**
```php
function send_welcome_email($user_email, $user_name) {
    $header = Timber::compile_twig_block('email_header', 'email/components.twig', [
        'logo_url' => get_template_directory_uri() . '/assets/logo.png',
        'site_name' => get_bloginfo('name'),
        'subject' => 'Welcome to Our Platform!'
    ]);
    
    $button = Timber::compile_twig_block('email_button', 'email/components.twig', [
        'url' => home_url('/dashboard'),
        'text' => 'Get Started'
    ]);
    
    $footer = Timber::compile_twig_block('email_footer', 'email/components.twig', [
        'footer_text' => 'Thanks for joining us!',
        'unsubscribe_url' => home_url('/unsubscribe')
    ]);
    
    $email_body = $header . '<div style="padding: 20px;"><p>Hello ' . $user_name . ',</p><p>Welcome to our platform!</p></div>' . $button . $footer;
    
    wp_mail($user_email, 'Welcome!', $email_body, ['Content-Type: text/html; charset=UTF-8']);
}
```

## Fallback Behavior

When a specified block doesn't exist, Timber will render the entire template instead:

```php
// If 'nonexistent_block' doesn't exist, this will render the whole template
$output = Timber::compile_twig_block('nonexistent_block', 'components/alerts.twig', $data);
```

This provides a graceful fallback, ensuring your site doesn't break if a block name is misspelled or removed.

## Performance and Caching

Block rendering supports the same caching options as regular template rendering:

```php
// Cache for 1 hour
$cached_block = Timber::compile_twig_block('expensive_block', 'components/complex.twig', $data, expires: 3600);

// Different cache times for logged-in vs anonymous users
$user_specific_cache = Timber::compile_twig_block('user_block', 'components/user.twig', $data, expires: [3600, 300]);
```

## Real-World Examples

### AJAX Component Loading

Use block rendering to dynamically load components via AJAX:

```php
function ajax_render_component() {
    check_ajax_referer('render_component', 'nonce');
    
    $component_type = sanitize_text_field($_POST['component_type']);
    $component_data = json_decode(stripslashes($_POST['component_data']), true);
    
    $output = Timber::compile_twig_block(
        $component_type,
        'components/ajax.twig',
        $component_data
    );
    
    wp_send_json_success(['html' => $output]);
}
add_action('wp_ajax_render_component', 'ajax_render_component');
```

### Modular Layout Building

Build complex layouts from smaller block components:

**views/components/layout-builder.twig**
```twig
{% block hero_section %}
<section class="hero" style="background-image: url({{ background_image.src }});">
    <div class="hero__content">
        <h1>{{ title }}</h1>
        <p>{{ subtitle }}</p>
        {{ render_twig_block('primary_button', 'examples/buttons.twig', {
            text: cta_text,
            url: cta_url
        }) }}
    </div>
</section>
{% endblock %}

{% block text_section %}
<section class="text-section">
    <div class="container">
        <h2>{{ title }}</h2>
        <div class="content">{{ content|raw }}</div>
    </div>
</section>
{% endblock %}

{% block gallery_section %}
<section class="gallery">
    <div class="container">
        <h2>{{ title }}</h2>
        <div class="gallery__grid">
            {% for image in images %}
                <div class="gallery__item">
                    <img src="{{ image.src('medium') }}" alt="{{ image.alt }}">
                </div>
            {% endfor %}
        </div>
    </div>
</section>
{% endblock %}
```

**PHP Usage:**
```php
$page_sections = get_field('page_sections');
foreach ($page_sections as $section) {
    echo Timber::compile_twig_block(
        $section['section_type'] . '_section',
        'components/layout-builder.twig',
        $section
    );
}
```

## Best Practices

### 1. Organize Your Block Templates

Create a clear structure for your block templates:

```
views/
├── components/
│   ├── alerts.twig
│   ├── cards.twig
│   ├── forms.twig
│   ├── buttons.twig
│   └── navigation.twig
├── email/
│   ├── components.twig
│   └── notifications.twig
└── layouts/
    ├── sections.twig
    └── layout-builder.twig
```

### 2. Use Meaningful Block Names

Choose descriptive names that clearly indicate the block's purpose:

```twig
{# Good #}
{% block primary_navigation %}
{% block error_message %}
{% block featured_product_card %}

{# Avoid #}
{% block nav %}
{% block msg %}
{% block card %}
```

Block rendering with Timber provides a powerful way to create modular, reusable template components that can be used both in PHP and Twig contexts, making your theme development more efficient and maintainable.