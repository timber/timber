---
title: "Template inheritance and includes"
---

## Template inheritance

You might have realized that there’s no HTML skeleton around the HTML for a post yet. We’re missing a `<head>` and a `<body>` and the navigation and the footer section. Let’s get to that.

Let’s create a basic post template.

**single.twig**

```twig
{% extends "base.twig" %}

{% block content %}

    <article>

        <header>
            <h1>{{ post.title }}</h1>
        </header>

        <section>
            {{ post.content }}
        </section>

    </article>

{% endblock %}
```

The part that you should take note of here is this one:

```twig
{% extends "base.twig" %}
```

You use the [extend](https://twig.symfony.com/doc/tags/extends.html) tag to tell the template engine that this template *extends* another template. This means that **single.twig** is using **base.twig** as its parent template. Here’s what your **base.twig** could look like:

```twig
<!DOCTYPE html>
<html>
    <head>
        {% block head %}
            <title>{{ site.title }}</title>
        {% endblock %}
    </head>

    <body>
        <main>
            {% block content %}
            {% endblock %}
        </main>

        <footer>
            {% block footer %}
                <p>Brought to you with ❤ by the Timber.</p>
            {% endblock %}
        </footer>
    </body>
</html>
```

If you look at **single.twig** again, you see opening and closing block declarations that surround the main page contents.

```twig
{% extends "base.twig" %}

{% block content %}

    {# Other stuff here ... #}

{% endblock %}
```

Twig will take the content inside the `{% block %}` tag and put it where you defined your block in **base.twig**. Everything in between `{% block content %}` and `{% endblock %}` in your **single.twig** will go into your `<main>` tag in your **base.twig**.

Blocks are the single most important and powerful concept in managing your templates. The official [Twig Documentation](http://twig.sensiolabs.org/doc/templates.html#template-inheritance) has more details.

While you can define your own custom number of blocks, you can also create any number of base files to extend from (we use the name "base" as a naming convention, but it’s not required).

## Nesting Blocks and Multiple Inheritance

Here is where things get really cool. While most people use a lot of [PHP includes](https://www.php.net/manual/en/function.include.php) after each other, in Twig you can create infinite levels of nested blocks to control your page templates. For example, let’s say you occasionally want to replace the title/headline on your **single.twig** template with a custom image or typography.

For this introduction, let’s assume that the name of the page is "All about Jared" (making its slug `all-about-jared`). First, we’re going to surround the part of the template we want to control with block declarations:

**single.twig**

```twig
{% extends "base.twig" %}

{% block content %}
    <article
        class="article post-type-{{ post.post_type }}"
        id="post-{{ post.ID }}"
    >
        <section class="article-content">
            {% block headline %}
                <header>
                    <h1 class="article-title">{{ post.title }}</h1>
                    <p role="doc-subtitle">{{ post.subtitle }}</h2>
                </header>
            {% endblock %}

            <p class="article-author"><span>By</span> {{ post.author.name }} <span>&bull;</span> {{ post.post_date }}</p>

            {{ post.content }}
        </section>
    </article>
{% endblock %}
```

Compared to the earlier example, we now have the `{% block headline %}` bit surrounding the `<header>` tag of the post.

To inject a custom bit of markup, we’re going to create a file called **single-all-about-jared.twig** in the **views** directory. The logic for which template should be selected is controlled in **single.php**, but generally follows WordPress conventions on [Template Hierarchy](https://wphierarchy.com/).

Now to replace the `headline` block **single.twig**, we define it in our new file.

**single-all-about-jared.twig**

```twig
{% extends "single.twig" %}

{% block headline %}
    <h1>
        <img
            src="/wp-content/uploads/2014/05/jareds-face.jpg"
            alt="Jared’s Mug"
        />
    </h1>
{% endblock %}
```

So there are two big concepts going on here:

1. **Multiple Inheritance:** We’re extending **single.twig**, which itself extends **base.twig**. Thus we stay true to the [DRY](https://en.wikipedia.org/wiki/Don%27t_repeat_yourself) principle and don’t have very similar code between our two templates hanging around.
2. **Nested Blocks:** `{% block headline %}` is located inside `{% block content %}`. So while we’re replacing the headline, we get to keep all the other markup and variables found in the parent template.
