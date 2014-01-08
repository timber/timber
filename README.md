<div style="text-align:center">
<a href="http://jarednova.github.com/timber"><img src="https://github.com/jarednova/timber/blob/master/admin/images/logo/timber-badge-large.jpg?raw=true" style="display:block; margin:auto;"/></a>
<div>
By Jared Novack (<a href="http://twitter.com/jarednova">@JaredNova</a>) and <a href="http://upstatement.com">Upstatement</a> (<a href="http://twitter.com/upstatement">@Upstatement</a>)</div>  
</div>

[![Build Status](https://travis-ci.org/jarednova/timber.png)](https://travis-ci.org/jarednova/timber)

### Because WordPress is awesome, but the_loop isn't
Timber helps you create fully-customized WordPress themes faster with more sustainable code. With Timber, you write your HTML using the [Twig Template Engine](http://twig.sensiolabs.org/) separate from your PHP files.

This cleans-up your theme code so, for example, your php file can focus on being the data/logic, while your twig file can focus 100% on the HTML and display.

This is what Timber's `.twig` files look like:

```html
{% extends "base.twig" %}
{% block content %}
<h1 class="big-title">{{foo}}</h1>
<h2>{{post.title}}</h2>
<img src="{{post.thumbnail.src}}" />
<div class="body">
	{{post.content}}
</div>
{% endblock %}
```
Once Timber is installed and activated in your plugin directory, it gives any WordPress theme the ability to take advantage of the power of Twig and other Timber features.

### Looking for docs?
* [Timber Documentation](https://github.com/jarednova/timber/wiki/)
* [Twig Reference](http://twig.sensiolabs.org/doc/templates.html)
* [Video Tutorials](https://github.com/jarednova/timber/wiki/Video-Tutorials)
* [Overview / Getting Started Guide](https://github.com/jarednova/timber/wiki/getting-started)

* * *

### Installation
Just drag the `timber` folder into `wp-content/plugins` -- then activate in your WordPress admin. If you're looking for a 'blank' theme to start developing with, drag the `timber-starter-theme` from the timber directory into your themes directory.

* * *

### Mission Statement
Timber is a tool for developers who want to translate their HTML into high-quality WordPress themes through an intuitive, consistent and fully-accessible interface.
* **Intuitive**: The API is written to be user-centric around a programmer's expectations.
* **Consistent**: All WordPress objects can be accessed through polymorphic properties like slug, ID and name.
* **Accessible**: No black boxes. Every effort is made so the developer has access to 100% of their HTML.

#### What does it look like?
Nothing. Timber is meant for you to build a theme on. Like the [Starkers](https://github.com/viewportindustries/starkers) or [Boilerplate theme](https://github.com/zencoder/html5-boilerplate-for-wordpress) it comes style-free, because you're the style expert. Instead, Timber handles the logic you need to make a kick-ass looking site.

#### Who is it good for?
Timber is great for any WordPress developer who cares about writing good, maintainable code. It helps teams of designers and developers working together. At [Upstatement](http://upstatement.com) we made Timber because not everyone knows the ins-and-outs of the_loop(), WordPress codex and PHP (nor should they). With Timber your best WordPress dev can focus on building the .php files with requests from WordPress and pass the data into .twig files. Once there, designers can easily mark-up data and build out a site's look-and-feel.

#### Related Projects
* [**Timber Debug Bar**](https://github.com/upstatement/debug-bar-timber) Adds a debug bar panel that will show you want template is in-use and the data sent to your twig file.
* [**Twig**](https://github.com/fabpot/Twig) The template language used by Timber.

#### Should I use it?
Well, it's **free**! And it's GPL-licensed, so use in personal or commercial work. Just don't re-sell it. Timber is still in development. While much has been stabalized since the first major push back in June 2013, you should expect some breaking changes as development progresses towards a version 1.0

#### Upgrade Notes
Twig is no longer a submodule, just a part of the repo. If you have trouble pulling, just delete the `Twig` folder. Then you should be good.
In May 2013 there was a major rewrite of Timber. Trust me, it's worth it. But if you're looking for the old [Parent Theme Timber](https://github.com/jarednova/timber/tree/theme) you can still find it on this [branch](https://github.com/jarednova/timber/tree/theme).


[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/jarednova/timber/trend.png)](https://bitdeli.com/free "Bitdeli Badge")



