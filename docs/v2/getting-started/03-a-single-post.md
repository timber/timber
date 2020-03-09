---
title: "A single post"
---

Let’s look at a singular post template in more detail, because echoing a post’s title with `{{ post.title }}` is not the only thing you can do.

**post.twig**

```twig
<article>

    <header>
        <h1>{{ post.title }}</h1>

        <p>
            Posted by {{ post.author.name }} <span>&bull;</span>
            <time
                datetime="{{ post.date('Y-m-d H:i:s') }}"
            >{{ post.date }}</time>
        <p>
    </header>

    <section>
        {{ post.content }}
    </section>

</article>
```

In this example, you see that we can also use `post.author`, `post.date` and of course, `post.content` to actually display a posts’s content.

The `{{ post }}` variable in Twig is not an array, it’s a PHP object. More specifically, it’s an instance of [`Timber\Post`](https://timber.github.io/docs/reference/timber-post/). When you look at the documentation for `Timber\Post`, you see what else you can access.

If you look for `title` in that post object in PHP, you will see that it’s actually not a property, but a function. In Twig, we don’t have to write `{{ post.title() }}` including the parentheses though, we can only use `{{ post.title }}` to call the function and echo out what it returns. If this is still all confusing to you, then don’t worry. You don’t have to completely understand this just yet.

Apart from the post title, we can also access the assigned author of a post through `{{ post.author }}`. Author is another Timber object. It’s not a post object though, but a [`Timber\User`](https://timber.github.io/docs/reference/timber-user/) object. We can get the result of the `name` method through `{{ post.author.name }}`. This displays the name of the author.

There’s also `{{ post.date }}`, but we won’t go into details about it here, because that’s a whole other chapter. You can read more about it in the [Date/Time Guide](https://timber.github.io/docs/guides/date-time/).

## Add your own data
