=== Timber ===
Contributors: jarednova
Tags: template engine, templates, twig
Requires at least: 3.5
Stable tag: trunk
Tested up to: 3.5.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Helps you create themes faster with more sustainable code. With Timber, you write your HTML using Twig Templates separate from PHP. 

== Description ==
Timber cleans-up your theme code so, for example, your php file can focus on being the data, while your twig/html file can focus 100% on the HTML and display.

Once Timber is installed and activated in your plugin directory, it gives any WordPress theme the ability to take advantage of the power of Twig and other Timber features.

### Looking for docs?
* [Timber Documentation](https://github.com/jarednova/timber/wiki/)
* [Twig Reference](http://twig.sensiolabs.org/documentation)
* **[Video Tutorials](https://github.com/jarednova/timber/wiki/Video-Tutorials)**
* [Overview / Getting Started Guide](https://github.com/jarednova/timber/wiki/getting-started)

#### What does it look like?
Nothing. Timber is meant for you to build a theme on. Like the [Starkers](https://github.com/viewportindustries/starkers) or [Boilerplate theme](https://github.com/zencoder/html5-boilerplate-for-wordpress) it comes style-free, because you're the style expert. Instead, Timber handles the logic you need to make a kick-ass looking site.

#### Who is it good for?
Timber is great for any WordPress developer who cares about writing good, maintainable code. It helps teams of designers and developers working together. At [Upstatement](http://upstatement.com) we made Timber because not everyone knows the ins-and-outs of the_loop(), WordPress codex and PHP (nor should they). With Timber your best WordPress dev can focus on building the .php files with requests from WordPress and pass the data into .twig files. Once there, designers can easily mark-up data and build out a site's look-and-feel.

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Activate the plugin through the 'Plugins' menu in WordPress
2. For an example, try modifying your home.php or index.php with something like this:

`
$context = array();
$context['message'] = 'Hello Timber!';
Timber::render('welcome.twig', $context);
`

Then create a subdirectory called `views` in your theme folder. The make this file: `views/welcome.twig`
`
{# welcome.twig #}
<div class="welcome">
	<h3>{{message}}</h3>
</div>
`

That's Timber!


== Frequently Asked Questions ==

= Can it be used in an existing theme? =

You bet! Watch these **[video tutorials](https://github.com/jarednova/timber/wiki/Video-Tutorials)** to see how.