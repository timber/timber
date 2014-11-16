=== Timber ===
Contributors: jarednova
Tags: template engine, templates, twig
Requires at least: 3.7
Stable tag: 0.20.8
Tested up to: 4.0
PHP version: 5.3.0 or greater
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Helps you create themes faster with sustainable code. With Timber, you write HTML using Mustache-like Templates http://timber.upstatement.com

== Description ==
Timber cleans-up your theme code so, for example, your php file can focus on being the data, while your twig/html file can focus 100% on the HTML and display.

Once Timber is installed and activated in your plugin directory, it gives any WordPress theme the ability to take advantage of the power of Twig and other Timber features.

### Looking for docs?
* **[Project Page](http://upstatement.com/timber)**
* [Timber Documentation](https://github.com/jarednova/timber/wiki/)
* [Twig Reference (from SensioLabs)](http://twig.sensiolabs.org/doc/templates.html)
_Twig is the template language powering Timber; if you need a little background on what a template language is, [Twig's homepage has an overview](http://twig.sensiolabs.org/)_
* **[Video Tutorials](https://github.com/jarednova/timber/wiki/Video-Tutorials)**
* [Overview / Getting Started Guide](https://github.com/jarednova/timber/wiki/getting-started)

#### Related Projects
* [**Timber Debug Bar**](http://wordpress.org/plugins/debug-bar-timber/) Adds a debug bar panel that will show you want template is in-use and the data sent to your twig file.

#### What does it look like?
Nothing. Timber is meant for you to build a theme on. Like the [Starkers](https://github.com/viewportindustries/starkers) or [_s theme](https://github.com/Automattic/_s) it comes style-free, because you're the style expert. Instead, Timber handles the logic you need to make a kick-ass looking site.

#### Who is it good for?
Timber is great for any WordPress developer who cares about writing good, maintainable code. It helps teams of designers and developers working together. At [Upstatement](http://upstatement.com) we made Timber because not everyone knows the ins-and-outs of the_loop(), WordPress codex and PHP (nor should they). With Timber your best WordPress dev can focus on building the .php files with requests from WordPress and pass the data into .twig files. Once there, designers can easily mark-up data and build out a site's look-and-feel.

#### Want to read more?
* [Timber on GitHub](http://github.com/jarednova/timber/)
* [Timber Overview on Tidy Repo](http://www.wpmayor.com/articles/timber-templating-language-wordpress/)
* ["What is WordPress Missing? A Template Language" on Torque](http://torquemag.io/what-is-wordpress-lacking-a-template-language/)



== Changelog ==

= 0.20.8 =
* Fixed some Twig deprecation (thanks @alexlrobertson)
* Support for {{img.src|retina}} filter (@jarednova)

= 0.20.7 =
* Cleaned-up logic for {{post.next}} and {{post.prev}} (thanks @alexlrobertson)
* Simplifiying internals of TimberCore, TimberPost (thanks @alexlrobertson)
* Initialization of variables from stolen WP functions (thanks @alexlrobertson)
* Fixed Twitter API call (thanks @asecondwill)
* Matched TimberMenu fallback behavior to grab pages_men (@jaredNova)
* Fixed a bug with the_title filter (thanks @kuus)
* Fixed weird conflicts when ACF names match methods (@jaredNova)
* Added a filter for timber_output (thanks @aristath)
* Fix for galleries showing only 5 images (thanks @IJMacD)

= 0.20.6 =
* Fixed some resulting bugs from numberposts vs. posts_per_page change as relates to galleries (thanks @IJMacD)
* Fixed issue with author.php in starter theme (thanks @dmtroyer)
* Added some sanity checks when menus are missing (thanks @jaredNova)
* New tests, yay!

= 0.20.5 =
* Fixed issue with sticky posts being included when just feeding an array of ids
* Fix for pagination links with search terms (thanks @matthewsoares)

= 0.20.4 =
* Fixed issue with Timber::get_posts and Timber::query_posts using numberposts in args

= 0.20.3 = 
* Fixed some issues with linking to menu items with a hash

= 0.20.2 =
* Change default response code on load_view to be 200
* Fixed error with relpath and subdomains (thanks @jnweaver)
* Various bug fixes (thanks @andyford, @discern)

= 0.20.1 =
* Hotfix to ensure non-exitent properties don't throw exception (thanks @kylehotchkiss)

= 0.20.0 =
* Iterators! You can now get data using query_posts which hooks into WP loop. Methods like get_the_title() now work (big thanks to @mgmartel)
* Fixed img_to_jpg issue with alternate WP setups (@thetmkay)
* Fixed issue with links in TimberMenuItem
* post.date now supports a DateTime object (@aduth)
* removal of long-since deprecated functions
* Massive code clean-up and bug fixes (@jaredNova, @mgmartel)

= 0.19.2 =
* Fixed issue with {{post.author.name}}
* Bug fixes and code organization (@hsz, @jaredNova)

= 0.19.1 =
* Removed .git folder hiding in php-router
* Added support for WooCommerce and other plugins in starter theme (thanks @jamesagreenleaf)
* Starter theme now based on OO-style TimberSite convention
* You can not get the modified_author (thanks @jamesagreenleaf)
* ...oh and the modified date (thanks @jamesagreenleaf)
* Code clean-up like mad (thanks @hsz)
* Fixed bug when calling Timber::get_posts in loop (thanks @jamesagreenleaf)

= 0.19.0 =
* Reorganized dependencies into /vendor directories based on composer (thanks @mgmartel, @rarst, @bryanaka)
* Fixed issues with image library deletion (thanks @thetmkay)
* Fixed issues with sidebar output

= 0.18.1 =
* Dates now use date_i18n filter (thanks @jamesagreenleaf)
* The twig |date filter now defaults to your WP Admin settings (thanks @jamesagreenleaf)
* You can send Timber::$dirname an array to specify multiple locations of twig files within a theme
* Load views from anywhere on the server (thanks @th3fallen)
* Load twig files from anywhere on the server using an absolute path
* Use another version of Twig if you have it loaded (thanks @ButlerPCnet)
* more tests!

= 0.18.0 =
* BREAKING CHANGE ALERT wp_title no longer appends bloginfo('name') to end of string (thanks @aduth)
* BREAKING CHANGE ALERT get_preview now respects <!-- more --> tag (thanks @jnweaver)
* TimberHelper::transient is more reliable (thanks @mgmartel)
* Secure urls in TimberImage if current page is served over SSL (thanks @mgmartel)
* Re-wrote most of letterboxing functionality
* Re-organized Helper functions

= 0.17.2 =
* TimberPost::children() now sorts by menu_order, title as WordPress core does (thanks @aduth)
* Fixed an occaisonal warning (thanks @matthewsoares)
* TimberImage::alt() now returns your image's alt tag info from WordPress (thanks @jnweaver)
* In the router, non-404 headers are forced asap (thanks @mgmartel)
* Router now accepts + signs in paths

= 0.17.1 =
* Hotfix on timber-admin error

= 0.17.0 =
* Now you can resize/crop images with positional preferences, thanks @mmikkel. Here are the docs: https://github.com/jarednova/timber/wiki/TimberImage#resize
* Removed the Timber Starter Guide from the admin, a link to the GitHub wiki suffices.

= 0.16.8 =
* You can now retrieve prev/next posts within the same category (post.next('category').title, etc.). (thanks @slimndap)
* Fixed issue with letterboxing images when WP is installed in a subdirectory ( @wesrice)
* Fix for images stored inside custom content path (@mmikkel)
* Cleaned-up some things in Timber Starter theme (@southernfriedbb, @jarednova)


= 0.16.7 =
* Fixed issue with image heights on external images (thanks @AndrewDuthie)
* Added new filter for timber_compile_result (thanks @parisholley)
* Other minor fixes (@jarednova)

= 0.16.6 =
* Router plays nice with installs in subdirectories (thanks @TerminalPixel)
* ACF Timber now initializes on Init (thanks @Zerek)
* Composer is updated (thanks @Rarst)
* $autoescape bug fixed (thanks @ParisHolley)
* You can now select a term ID from a specific taxonomy (thanks @mgmartel)
* added stripshortcodes filter
* TimberMenuItems now have is_external method
* Other misc bugs

= 0.16.5 =
* print_a lives! added methods for TimberPost
* quick fix on TimberPost::content which was generating warning

= 0.16.4 =
* Fixed a few things on image handling
* Updated to Twig 1.15 (thanks @fabpot)
* Added wp_link_pages as TimberPost::pagination
* New filter to help with template selection (thanks @zlove)

= 0.16.3 =
* Added width, height and aspect methods for TimberImages
* Timber::pagination can now accept a single integer as the overall "size" argument (for the total number of pages that get shown)
* TimberPost->class (usage: `<article class="{{post.class}}"`>) will now show you the products of post_class
* Sanity checks for ACF (thanks @parisholley)
* Fixed bug in TimberPost::prev and TimberPost::next that could return draft posts (thanks @slimndap)
* Fixed bug with extra ellipsis in some previews (thanks @parisholley)

= 0.16.2 =
* Added has_term to TimberPost
* Extra checks to make sure redirected links don't get 404 body class
* Misc bugs

= 0.16.1 =
* Bug fix on ugly permalinks for pagination
* Fixed issue where posts retrieved via an array of IDs was truncated at the default post count
* Fixed issue where loading terms from multi taxonomies (thanks @WL-hohoho)
* Added support for post_class on TimberPost (thanks @slimndap)
* new `array` filter to convert single-values into array in twig
* Cleaned-up and added translation support to `time_ago` filter (thanks @WL-hohoho)

= 0.16.0 =
* TimberTheme is now available in default context as .theme
* Post meta now respects arrays (watch out for some possible compatiblity issues here)
* Template loads now work for parent/child themes in Windows (thanks @matthewsoares)
* Better method for removing 404 body class on manual redirects (thanks @mgmartel)

= 0.15.5 =
* Post formats: {{post.format}} !

= 0.15.4 =
* More improvements to filters to support external integration with Pods and other WP frameworks
* Fixed bug on date internationalization (thanks @slimndap)
* Fixed bug on using existing image sizes (thanks @matthewsoares)
* Fixed bug on homeurl vs siteurl (thanks @ciarand)
* Added a cache lock to the TimberHelper::transient method
* Added an in-development version of a TimberArchives object

= 0.15.3 =
* Upgrayedd to Twig 1.14.2
* Added composer integration
* Bunch of new tests
* Comments now support gravatrs (thanks @asecondwill)
* Moved ACF integration into its own file. It now interacts via hooks instead of in-line
* A few misc. bugs and extra sanity checks

= 0.15.2 =
* TimberImages now support alternate sizes

= 0.15.1 =
* Fix on revered prev/next post links

= 0.15.0 =
* Cacheing!!!
* Cacheing!!
* Cacheing!!!! Timber::render('mytemplate.twig', $data, $expires_time_in_secs);
* Timber::render now automatically echos. Don't want it to? See below...
* New Timber::compile method which _doesn't_ automatically echo. (Same args as Timber::render)
* Added post.get_next / post.get_prev for TimberPosts
* Fixed a thing to make get_preview easier when you want to omit the 'Read More' link
* Read the [Full Release Notes](https://github.com/jarednova/timber/releases/tag/0.15.0)

= 0.14.1 =
* Added hooks to play nicely with Timber Debug Bar
* Fixed-up Timber Term aliases, link, path, etc.
* Add DB queries now get properly prepared
* Supports custom author permalinks
* Simplified TimberPost processing; shaved some processing time off

= 0.14.0 =
* More flexiblity for custom routes (thanks @mgmartel)
* Added filters for core objects (TimberPost and TimberTerm). This greatly helps when you need to have retrived custom fields or repeaters interprted as posts or terms
* Renamed "WPHelper" to more namespace-friendly "TimberHelper"
* Added function_wrapper helper to execute functions where they are placed in the template as opposed to when they are generated (@mgmartel)
* You can now have custom fields processed via post.get_field('my_custom_field'). This is a huge help for using things like Advanced Custom Fields' repeater.
* Performance improvements

= 0.13.5 =
* Added comprehensive support for actions and filters (thanks @mgmartel)
* Rewrote routing to template to be 100% harmonious with WordPress (thanks again @mgmartel)
* Fix to some pagination errors when using a custom rewrite on a taxonomy (thanks to @kylehotchkiss)
* Fixed issue with stripping the ellipses on a preview (thanks to @bryanscode)
* Functions now work more logically, example: {{function('my_special_function', 'arg1')}}

= 0.13.0 =
* TimberMenuItems now get the WP classes you've come to know and love (.current-menu-item, etc.)
* More test coverage for images
* Resizing external images converts the URL into a md5 hash
* Removed a dangerous backtrace that could overload errorlog
* Some object caching on TimberPost->get_terms to improve performance

= 0.12.2 =
* TimberMenus now contain metadata 'bout the menu (thanks @bryanaka)
* Fixed issue with Windows servers (thanks @kzykhys)
* Resizing external images now incl. the full URL to avoid conflicts
* Fixed pagination oddity
* Some code cleanup stuff.

= 0.12.1 =
* A few fixes that catch issues with absolute vs. relative URLs in resize

= 0.12.0 =
* Pagination is re-factored to be more intuitive, and well, better.
* Resize is also re-factored to respect absolute vs. relative URLs
* Got rid of lots of old, bogus code.

= 0.11.0 =
* fixed load order of views so files inside of the child theme have priority over the parent theme.
* comment ordering respects the default set in WordPress
* added getting started screen
* misc bug fixes
* removed lots of old garbage, simplified file organization
* contributors for this release: @ysurian, @thisislawatts, @punkshui and @paulwilde

= 0.10.7 =
* more normalization of menus, users
* fixed bug in post.get_content (thanks @paulwilde)
* fixed bug in way menu items with children got their children (thanks @EloB)

= 0.10.6 =
* more normalization of comments
* Lots of cleanup of starter theme

= 0.10.5 =
* added theme URI to universal context

= 0.10.4 =
* Lots of code cleanup thanks to [Jakub](http://github.com/hsz)
* Added new function for bloginfo
* You can now hook into timber_context to filter the $context object
* Added Timber::get_terms to retrieve lists of your blog's terms
* Added better support for translation
* Added filter for executing a function, ie {{'my_theme_function'|filter}}

= 0.10.3 =
* Corrected error with sidebar retrieval
* language_attributes are now available as part of Timber::get_context(); payload.
* Upgraded to Twig 1.13.1

= 0.10.2 =
* added more aliases for easier coding (post.thumbnail instead of post.get_thumbnail, etc.)
* Garbage removal

= 0.10.1 =
* load_template for routing can now accept a query argument
* load_template will wait to load a template so that 'init' actions can fire.
* way more inline documentation
* print_a now includes the output of (most) methods in addition to properties.
* added lots of aliases so that things like .author will work the same as .get_author

== Screenshots ==

1. This what a normal WordPres PHP file looks like
2. With Timber, you write Twig files that are super-clear and HTML-centric.

== Installation ==

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

== Support ==

Please use the [GitHub repo](https://github.com/jarednova/timber/issues?state=open) to file bugs or questions.

== Frequently Asked Questions ==

= Can it be used in an existing theme? =
You bet! Watch these **[video tutorials](https://github.com/jarednova/timber/wiki/Video-Tutorials)** to see how.

= Is it used in production? =
At Upstatement we've now used it on more than a dozen client sites. Hundreds of other sites use it too. You can check some of them out in the **[showcase](http://upstatement.com/timber/#showcase)**.

= Doesn't this all make WordPress harder since there's more to learn? =
Does jQuery make JavaScript harder? Yes, it's an extra piece to learn -- but it super-charges your ability to write unencumbered JavaScript (and prevents you from having to learn lots of the messy internals). If your answer is "jQuery sucks and everyone should learn how to write vanilla JS or they're rotten stupid people," this tool isn't for you.

= Oh, Timber is simple code so it's for making simple themes =
Whatever. It simplifies the silly stuff so that you can focus on building more complicated sites and apps. jQuery simplifies Javascript, but you can still use the full range of JS's abilities.

= Will you support it? =
As stated above, we're using it in dozens of sites (and dozens more planned) -- dozens of other developers are using it too. This isn't going anywhere. Twig is the chosen language for other PHP platforms like Symfony, Drupal 8 and Craft. WordPress will eventually adopt Twig too, I promise you that.

= Support? =
Leave a [GitHub issue](https://github.com/jarednova/timber/issues?state=open) and I'll holler back.
