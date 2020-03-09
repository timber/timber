

One of these *things* that are prepared for you in the context, is the `site` variable.

**views/index.twig**

```twig
<h1>{{ site.title }}</h1>
<p role="doc-subtitle">{{ site.description }}</p>
```

The `site` variable contains data about your WordPress website. If you want to learn more, read more about [Timber\Site](https://timber.github.io/docs/reference/timber-site/) later.

To access an item in an associative array, a property or a method of a object in Twig, you can use the `.` notation.


## A note about namespaces

What you’ll see in a couple of examples with Timber are PHP namespaces. If you’re not familiar with those yet, we recommend you to get familiar with them.
