<div style="text-align:center">
<a href="https://upstatement.com/timber/"><img src="http://i.imgur.com/PbEwvZ9.png" style="display:block; margin:auto; width:100%; max-width:100%"/></a>
</div>

By
[Jared Novack](https://github.com/jarednova) ([@jarednova](https://twitter.com/jarednova)),
[Lukas Gächter](https://github.com/gchtr) ([@lgaechter](https://twitter.com/lgaechter)),
[Nicolas Lemoine](https://github.com/nlemoine) ([@nlemoine](https://niconico.fr/)),
[Erik van der Bas](https://github.com/Levdbas) ([website](https://basedonline.nl/)),
[Coby Tamayo](https://github.com/acobster) ([@cobytamayo](https://keybase.io/acobster)),
[Upstatement](https://twitter.com/upstatement) and [hundreds of other GitHub contributors](https://github.com/timber/timber/graphs/contributors):

[![](https://opencollective.com/timber/contributors.svg?width=900&button=false)](https://github.com/timber/timber/graphs/contributors)

[![Build Status](https://img.shields.io/travis/timber/timber/master?style=flat-square)](https://app.travis-ci.com/github/timber/timber/branches)
[![Coverage Status](https://img.shields.io/coveralls/timber/timber.svg?style=flat-square)](https://coveralls.io/github/timber/timber)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/timber/timber.svg?style=flat-square)](https://scrutinizer-ci.com/g/timber/timber/?branch=master)
[![Latest Stable Version](https://img.shields.io/packagist/v/timber/timber.svg?style=flat-square)](https://packagist.org/packages/timber/timber)
[![WordPress Rating](https://img.shields.io/wordpress/plugin/r/timber-library.svg?style=flat-square)](https://wordpress.org/support/plugin/timber-library/reviews/)
[![!Financial Contributors](https://opencollective.com/timber/tiers/badge.svg)](https://opencollective.com/timber/)

### ⚠️ Important information about the Timber plugin ⚠️ 
> With the release of Timber 2.0, Composer is the only supported install method. We are unable to continue releasing or supporting Timber as a plugin on WordPress.org. We advise everyone to **[switch to the Composer based install of Timber 1 as a first step](https://timber.github.io/docs/v1/getting-started/switch-to-composer/)** as soon as possible. If you need PHP 8.2 support you will have to switch to Timber 2.0.

For more information and a list of additional resources, please visit this [discussion](https://github.com/timber/timber/discussions/2804).

### Timber 2

Timber 2 is out now and is the recommended version.

- GitHub: [Timber 2](https://github.com/timber/timber)
- Documentation: [https://timber.github.io/docs/v2/](https://timber.github.io/docs/v2/)


### Because WordPress is awesome, but the_loop isn't
Timber helps you create fully-customized WordPress themes faster with more sustainable code. With Timber, you write your HTML using the [Twig Template Engine](https://twig.symfony.com/) separate from your PHP files.

This cleans up your theme code so, for example, your PHP file can focus on being the data/logic, while your Twig file can focus 100% on the HTML and display.

This is what Timber's `.twig` files look like (from this [Hello World example](https://gist.github.com/jarednova/dc75030fd2c7dd6fe52a6fef459c450e))

```twig
{% extends "base.twig" %}
{% block content %}
  <h1 class="big-title">{{ foo }}</h1>
  <h2 class="post-title">{{ post.title }}</h2>
  <img src="{{ post.thumbnail.src }}" />
  <div class="body">
	{{ post.content }}
  </div>
{% endblock %}
```
Once Timber is installed and activated in your `plugins` directory, it gives any WordPress theme the ability to take advantage of the power of Twig and other Timber features.

### Looking for docs?
* [Timber Documentation](https://timber.github.io/docs/v1/)
* [Twig Reference](https://twig.symfony.com/)
* [Overview / Getting Started Guide](https://timber.github.io/docs/v1/getting-started/)
* [Video Tutorials](https://timber.github.io/docs/v1/getting-started/video-tutorials/)

* * *

### Installation

The GitHub version of Timber requires [Composer](https://getcomposer.org/download/) and is setup for inclusion _within_ a theme or plugin.

```shell
cd ~/wp-content/themes/my-theme
composer require timber/timber
```

If your theme/plugin is not setup to pull in Composer's autoload file, you will need to

```php
/* functions.php */
require_once(__DIR__ . '/vendor/autoload.php');
```

After this line, initialize Timber with
```php
$timber = new \Timber\Timber();
```

### What Now?

Setup the [Timber Starter Theme](https://timber.github.io/docs/v1/getting-started/setup/#use-the-starter-theme). Once you have that installed in your WordPress setup, continue reading the [Getting Started guide to Themeing](https://timber.github.io/docs/v1/getting-started/theming/).

* * *

### Mission Statement

Timber is a tool for developers who want to translate their HTML into high-quality WordPress themes through an intuitive, consistent and fully-accessible interface.
* **Intuitive**: The API is written to be user-centric around a programmer's expectations.
* **Consistent**: WordPress objects can be accessed through common polymorphic properties like slug, ID and name.
* **Accessible**: No black boxes. Every effort is made so the developer has access to 100% of their HTML.

#### What does it look like?
Nothing. Timber is meant for you to build a theme on. Like [_s](https://github.com/Automattic/_s) it comes style-free, because you're the style expert. Instead, Timber handles the logic you need to make a kick-ass looking site.

#### Who is it good for?
Timber is great for any WordPress developer who cares about writing good, maintainable code. It helps teams of designers and developers working together. At [Upstatement](http://upstatement.com) we made Timber because while our entire team needs to participate in building WordPress sites, not everyone knows the ins-and-outs of the_loop(),  codex and PHP (nor should they). With Timber your best WordPress engineer can focus on building the `.php` files with requests from WordPress and pass the data into `.twig` files. Once there, designers can easily mark-up data and build out a site's look-and-feel.

#### Related & Official Projects
* [**Twig**](https://github.com/twigphp/Twig) The template language used by Timber.
* [**Timber Starter Theme**](https://github.com/timber/starter-theme) The "_s" of Timber to give you an easy start to the most basic theme you can build upon and customize.
* [**Timber Debug Bar**](https://github.com/timber/debug-bar-timber) Adds a debug bar panel that will show you which template is in-use and the data sent to your twig file.

#### Related Timber Projects
* [**Editor for Timber**](https://wordpress.org/plugins/editor-for-timber/) Edit your Twig files from the WordPress admin
* [**Pine**](https://github.com/azeemhassni/pine) A CLI _installer_ for Timber
* [**Query Monitor Twig Profile**](https://github.com/NielsdeBlaauw/query-monitor-twig-profile) An add-on for [Query Monitor](https://wordpress.org/plugins/query-monitor/) to see what's going on inside your Twig files (automatically works with Timber!)
* [**Timber ACF WP Blocks**](https://github.com/palmiak/timber-acf-wp-blocks) Easy ACF Gutenberg blocks creation
* [**Timber CLI**](https://github.com/nclud/wp-timber-cli) A CLI for Timber
* [**Timber Commented Include**](https://github.com/djboris88/timber-commented-include) Debug output via HTML comments before and after each include statement in Twig
* [**Timber Debugger**](https://github.com/djboris88/timber-debugger) Package that provides extra debugging options for Timber
* [**Timber Dump Extension**](https://github.com/nlemoine/timber-dump-extension) Debug output with nice formatting
* [**Timber Photon**](https://github.com/slimndap/TimberPhoton) Plug-in to use JetPack's free Photon image manipulation and CDN with Timber
* [**Timber VS Code Extension**](https://github.com/JDevx97/Timber-Snippets) Snippets for Timber in Visual Studio Code
* [**Timber Sugar**](https://github.com/timber/sugar) A catch-all for goodies to use w Timber
* [**Timber WebLink Extension**](https://github.com/nlemoine/timber-weblink-extension) Provides Twig functions to manage the Link HTTP header needed for Web Linking when using HTTP/2 Server Push as well as Resource Hints
* [**Timmy**](https://github.com/MINDKomm/Timmy) Advanced image manipulation for Timber


#### Projects that use Timber
* [**Branch**](https://github.com/JeyKeu/branch/) Bootstrap 3 + Timber = Branch starter theme!
* [**Flynt**](https://flyntwp.com/) a component based WordPress starter theme built on Timber and ACF Pro
* [**Gantry5**](https://wordpress.org/plugins/gantry5/) a framework for theme development
* [**Hozokit**](https://github.com/csalmeida/hozokit) a component based starter theme
* [**Juniper**](https://www.osomstudio.com/blog/meet-the-juniper-starter-pack-your-wordpress-development-new-best-friend/) Starter pack that incorporates Timber and Bedrock
* [**Seedling**](https://github.com/maxdmyers/seedling) a starter theme using Bootstrap 4

#### Helpful Links
* [**CSS Tricks**](https://css-tricks.com/timber-and-twig-reignited-my-love-for-wordpress/) introduction to Timber by [@tjFogarty](https://github.com/tjFogarty)
* [**Twig for Timber Cheatsheet**](http://notlaura.com/the-twig-for-timber-cheatsheet/) by [@laras126](https://github.com/laras126)
* [**Timber Cheatsheet**](https://gist.github.com/taotiwordpress/266fd95513f97f3c17748288579c56b9) by [@taotiwordpress](https://github.com/taotiwordpress)
* [**TutsPlus**](http://code.tutsplus.com/articles/kick-start-wordpress-development-with-twig-introduction--cms-24781) A guide to getting started by [@ahmadawais](https://github.com/ahmadawais)

#### Support
Please post on [StackOverflow under the "Timber" tag](http://stackoverflow.com/questions/tagged/timber). Please use GitHub issues _only_ for specific bugs, feature requests and other types of issues.

#### Should I use it?
Timber is MIT-licensed, so please use in personal or commercial work. Just don't re-sell it. Timber is used on [tens of thousands of sites](https://www.upstatement.com/timber/#showcase) (and tons more we don't know about)

#### Contributing & Community
We love PRs! Read the [Contributor Guidelines](https://github.com/timber/timber/blob/2.x/CONTRIBUTING.md) for more info. Say hello, share your tips/work, and spread the love on Twitter at [@TimberWP](https://twitter.com/TimberWP).

## Sponsor us

Since 2013 our goal at Timber is to create a library to that helps you create fully-customized WordPress themes _faster_ with more _sustainable code_.

Through the collaborative efforts of both our dedicated team and countless contributors, we have invested numerous hours in maintaining and enhancing Timber. To keep doing that, we rely on the invaluable support of our sponsors.

Are you a WordPress pro or part of an agency who relies on Timber? Keep the magic alive by [becoming a sponsor](https://opencollective.com/timber)! By becoming a sponsor, you contribute to the continuous maintenance and enhancement of Timber, ultimately benefiting developers worldwide.

![](https://opencollective.com/timber/tiers/bronze-sponsor.svg?avatarHeight=36&limit=0&button=true)

### Gold Sponsors

![](https://opencollective.com/timber/tiers/gold-sponsor.svg?avatarHeight=36&button=false)

<!-- Enable when available.
### Silver Sponsors

![](https://opencollective.com/timber/tiers/sponsors.svg?avatarHeight=36?avatarHeight=36&button=false)
-->

<!-- Enable when available.
### Basic Sponsors

![](https://opencollective.com/timber/tiers/bronze-sponsor.svg?avatarHeight=36&button=false)
-->

### Our backers

![](https://opencollective.com/timber/tiers/backers.svg?avatarHeight=36&button=false)

## Documentation

The Official [Documentation for Timber 1](https://timber.github.io/docs/v1/) is generated from the contents of this repository:

* Documentation for classes and functions is [auto generated](https://github.com/timber/docs). Any changes to the [Reference section](https://timber.github.io/docs/v1/reference/) of the docs should be made by editing the function’s DocBlock. For inline documentation, we follow the [WordPress PHP Documentation Standards](https://make.wordpress.org/core/handbook/best-practices/inline-documentation-standards/php/).
* To make a change to one of the guides, edit the relevant file in the [`docs` directory](https://github.com/timber/timber/tree/1.x/docs).
