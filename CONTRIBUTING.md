## Here are ways to get involved:

1. [Star](https://github.com/timber/timber/stargazers) the project!
2. Answer questions that come through [GitHub issues](https://github.com/timber/timber/issues?state=open)
3. [Report a bug](https://github.com/timber/timber/issues/new) that you find.
4. Share a theme you’ve built with Timber. This helps transfer knowledge about best practices, etc. _Add it to the [Showcase list](https://github.com/timber/timber/wiki/Showcase)_.
5. Tweet and [blog](http://www.oomphinc.com/blog/2013-10/php-templating-wordpress/#post-content) about the advantages (and criticisms) of the project and Twig.
6. Browse ["help wanted"](https://github.com/timber/timber/labels/help%20wanted) and ["good first issue"](https://github.com/timber/timber/labels/good%20first%20issue) labels for areas of WordPress/PHP/code you know well to consider, build or document.
7. Answer questions on [Stack Overflow posted under the «Timber» tag](https://stackoverflow.com/questions/tagged/timber). You can also [subscribe to a tag](https://stackoverflow.blog/2010/12/20/subscribe-to-tags-via-emai/) via email to get notified when someone needs help.
8. Answer question in the support channel on [Gitter](https://gitter.im/timber/timber).

### Pull Requests

Pull requests are highly appreciated. Almost 150 people have written parts of Timber (so far). Here are some guidelines to help:

1. **Solve a problem** – Features are great, but even better is cleaning-up and fixing issues in the code that you discover.
2. **Write tests** – This helps preserve functionality as the codebase grows and demonstrates how your change affects the code.
3. **Small > big** – Better to have a few small pull requests that address specific parts of the code, than one big pull request that jumps all over.
4. **Comply with Coding Standards** – See next section.

## Coding Standards

We try to follow the [WordPress Coding Standards](https://make.wordpress.org/core/handbook/coding-standards/php/) as close as we can, with the following exceptions:

- Class and file names are defined in `StudlyCaps`. We follow the [PSR-0 standard](http://www.php-fig.org/psr/psr-1/#namespace-and-class-names), because we use autoloading via Composer.
- We use hook names namespaced by `/` instead of underscores (e.g. `timber/context` instead of `timber_context`).

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

## Inline Documentation

The [Reference section](https://timber.github.io/docs/reference/) of the documentation is automatically generated from the inline documentation of the Timber code base. To document Timber, we follow the official [PHP Documentation Standards](https://make.wordpress.org/core/handbook/best-practices/inline-documentation-standards/) of WordPress.

There are minor differences to the official standards:

- For class headers, we don’t use `@package` or `@subpackage` tags.
- We don’t use the `@access` tag.

### Ignoring Structural Elements

An element (class, method, property) is **ignored when one of the following conditions** applies:

- No DocBlock is provided
- No `@api` tag is present
- An `@ignore` tag is present
- An `@internal` tag is present
- The visibility is `private` (applies to methods only)

This means that for Markdown files to be generated for a class at all, you’ll need at least a DocBlock with an `@api` tag.


### Referencing class names

When referencing a namespaced class name in a type (for example in a `@param` or `@return` tag), then use the fully qualified name. Example: `Timber\Post` instead of just `Post`.

### Code examples

The `@example` tag allows you add code examples to your DocBlocks, including fenced code blocks:

```php
/**
 * Function summary.
 * 
 * Function description.
 *
 * @api
 * @example
 *
 * Optional text to describe the example.
 * 
 * ```php
 * my_method( 'example', false );
 * ```
 *
 * @param string $param1 Description. Default 'value'.
 * @param bool   $param2 Optional. Description. Default true.
 */
function my_method( $param1, $param2 = true ) {}
```

### Reference linking with @see tag

When you use the `@see` tag, the Reference Generator will automatically convert it to a link to the [reference](http://timber-docs.test/docs/reference/).

- Use this tag only when the referenced method has an `@api` tag, which means that it is public.
- Beware, you’ll always use the notation with `::`, which you normally know from static methods. But even if the method that you link is not static, you’ll have to use the double colon.

An example:

```
@see \Timber\Image::src()
```

will turn into this:

```html
<strong>see</strong>
<a href="/docs/reference/timber-image/#src">Timber\Image::src()</a>
```

### Documenting Hooks

Follow the official [Inline Documentation Standards for Actions and Filters](https://make.wordpress.org/core/handbook/best-practices/inline-documentation-standards/php/#4-hooks-actions-and-filters) to document hooks.

#### Keywords for filters and actions

A DocBlock that documents a hook should always start with the keyword

- `Filters` for filter hooks
- `Fires` for action hooks

This is needed so that the [Hook Reference Generator](https://github.com/timber/teak/blob/master/lib/Compiler/HookReference.php) can detect the comments associated with filters and actions.

#### Syntax

```php
/**
 * Filters … / Fires … (Summary)
 *
 * Description.
 * `$var` Optional description of variables that might be used in the filter name.
 * 
 * @see \Timber\Classname::function()
 * @link https://github.com/timber/timber/pull/1254
 * @since x.x.x
 * @deprecated x.x.x
 * @example
 * Optional text to describe the example.
 * 
 * ```php
 * // A PHP example
 *
 * /**
 *  * Multiline comments are possible as well.
 *  * You’ll need to escape the closing tag with a "\".
 *  *\/
 * ```
 *
 * ```twig
 * {# A Twig example #}
 * ```
 *
 * @param type  $var Description. Default 'value'.
 * @param array $args {
 *     Short description about this hash.
 *
 *     @type type $var Optional. Description. Default value.
 *     @type type $var Optional. Description. Default value.
 * }
 * @param type  $var Optional. Description. Default value.
 */
```

#### Dynamic filters

When a filter contains a variable, it should be marked up with double quotes `"` and the variable name inside curly braces:

```php
$force = apply_filters( "timber/transient/force_transient_{$slug}", $force );
```

Additionally to this, document what the variable is by adding it to the description. Add it on a newline with the variable wrapped in backticks, so that they appear as code in Markdown:

```php
/**
 * Filters …
 *
 * Here is a description about the filter.
 * `$slug` The transient slug.
 *
 * @param bool $force Param description.
 */
$force = apply_filters( "timber/transient/force_transient_{$slug}", $force );
```

#### Multiline declaration

Formatting a filter into multiple lines when the line should be too long is allowed:

```php
/**
 * Filters …
 */
$force = apply_filters_deprecated(
    'timber_force_transients',
    array( $force ),
    '2.0.0',
    'timber/transient/force_transients'
);
```

#### Unfinished filters

If a filter description is not finished yet, mark it up with the `@todo` tag. It’s okay if you don’t know what a filter is doing exactly or if you’re unsure about what a parameter does. Describe what needs to be done in the `@todo` tag.

```php
/**
 * Filters …
 *
 * @todo Add summary, add description.
 *
 * @param bool $force Param description.
 */
```

As soon as the todo is resolved, the `@todo` tag can be removed.