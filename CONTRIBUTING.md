## Here are ways to get involved:

1. [Star](https://github.com/timber/timber/stargazers) the project!
2. Answer questions that come through [GitHub issues](https://github.com/timber/timber/issues?state=open)
3. [Report a bug](https://github.com/timber/timber/issues/new) that you find.
4. Share a theme you’ve built with Timber. This helps transfer knowledge about best practices, etc. _Add it to the [Showcase list](https://github.com/timber/timber/wiki/Showcase)_.
5. Tweet and [blog](http://www.oomphinc.com/blog/2013-10/php-templating-wordpress/#post-content) about the advantages (and criticisms) of the project and Twig.
6. Browse [contrib opportunities](https://github.com/timber/timber/issues?labels=contrib-opportunity&page=1&state=open) for areas of WordPress/PHP/code you know well to consider, build or document.
7. Answer questions on [Stack Overflow posted under the «Timber» tag](https://stackoverflow.com/questions/tagged/timber). You can also [subscribe to a tag](https://stackoverflow.blog/2010/12/20/subscribe-to-tags-via-emai/) via email to get notified when someone needs help.
8. Answer question in the support channel on [Gitter](https://gitter.im/timber/timber).

### Pull Requests

Pull requests are highly appreciated. More than fifty people have written parts of Timber (so far). Here are some guidelines to help:

1. **Solve a problem** – Features are great, but even better is cleaning-up and fixing issues in the code that you discover.
2. **Write tests** – This helps preserve functionality as the codebase grows and demonstrates how your change affects the code.
3. **Small > big** – Better to have a few small pull requests that address specific parts of the code, than one big pull request that jumps all over.
4. **Comply with Coding Standards** – See next section.

## Coding Standards

We try to follow the [WordPress Coding Standards](https://make.wordpress.org/core/handbook/coding-standards/php/) as close as we can, with the following exceptions:

- Class and file names are defined in `StudlyCaps`. We follow the [PSR-0 standard](http://www.php-fig.org/psr/psr-1/#namespace-and-class-names), because we use autoloading via Composer.
- We use hook names namespaced by `/` instead of underscores (e.g. `timber/context` instead of `timber_context`).

### Inline Documentation

We follow the [WordPress Inline Documentation Standards](https://make.wordpress.org/core/handbook/best-practices/inline-documentation-standards/php/). The [Reference section of the documentation](https://timber.github.io/docs/reference/) is automatically generated from the inline documentation of the Timber code base. That’s why we allow Markdown in the PHPDoc blocks.

### Use PHP_CodeSniffer to detect coding standard violations

To check where the code deviates from the standards, you can use [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer). Timber comes with a `phpcs.xml` in the root folder of the repository, so that the coding standards will automatically be applied for the Timber code base.

- Install PHP_CodeSniffer globally by following this guide: <https://github.com/squizlabs/PHP_CodeSniffer#installation>.
- Install WPCS by following this guide: <https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards#installation>.

#### Command Line Usage

To run PHP_CodeSniffer with the default settings on all relevant Timber files, use the following command from the root folder of the Timber library: 

```bash
phpcs
```

You could check a single file like this:

```bash
phpcs ./lib/Menu.php
```

Use `phpcs --help` for a list of available settings.

#### Use it in your IDE

Please refer to <https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards#how-to-use> for different ways to use PHP_CodeSniffer directly in your IDE. In some IDEs like PHPStorm, you may have to select the `phpcs.xml` explicitly to apply the proper standards.

#### Whitelisting

If it’s not possible to adapt to certain rules, code could be whitelisted. However, this should be used sparingly.

- <https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards/wiki/Whitelisting-code-which-flags-errors>
- <https://github.com/squizlabs/PHP_CodeSniffer/wiki/Advanced-Usage#ignoring-parts-of-a-file>
