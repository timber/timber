=== Timber ===
Contributors: jarednova, connorjburton, lggorman
Tags: template engine, templates, twig
Requires at least: 3.7
Stable tag: 1.1.12
Tested up to: 4.6
PHP version: 5.3.0 or greater
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Helps you create themes faster with sustainable code. With Timber, you write HTML using Mustache-like Templates http://timber.upstatement.com

== Description ==
Timber cleans-up your theme code so, for example, your php file can focus on being the data, while your twig/html file can focus 100% on the HTML and display.

Once Timber is installed and activated in your plugin directory, it gives any WordPress theme the ability to take advantage of the power of Twig and other Timber features.

### Looking for docs?
* **[Project Page](http://upstatement.com/timber)**
* [Timber Documentation](https://github.com/timber/timber/wiki/)
* [Twig Reference (from SensioLabs)](http://twig.sensiolabs.org/doc/templates.html)
_Twig is the template language powering Timber; if you need a little background on what a template language is, [Twig's homepage has an overview](http://twig.sensiolabs.org/)_
* **[Video Tutorials](https://github.com/timber/timber/wiki/Video-Tutorials)**
* [Overview / Getting Started Guide](https://github.com/timber/timber/wiki/getting-started)

#### Related Projects
* [**Timber Debug Bar**](http://wordpress.org/plugins/debug-bar-timber/) Adds a debug bar panel that will show you want template is in-use and the data sent to your twig file.

#### What does it look like?
Nothing. Timber is meant for you to build a theme on. Like the [Starkers](https://github.com/viewportindustries/starkers) or [_s theme](https://github.com/Automattic/_s) it comes style-free, because you're the style expert. Instead, Timber handles the logic you need to make a kick-ass looking site.

#### Who is it good for?
Timber is great for any WordPress developer who cares about writing good, maintainable code. It helps teams of designers and developers working together. At [Upstatement](http://upstatement.com) we made Timber because not everyone knows the ins-and-outs of the_loop(), WordPress codex and PHP (nor should they). With Timber your best WordPress dev can focus on building the .php files with requests from WordPress and pass the data into .twig files. Once there, designers can easily mark-up data and build out a site's look-and-feel.

#### Want to read more?
* [Timber on GitHub](http://github.com/timber/timber/)
* [Timber Overview on Tidy Repo](http://tidyrepo.com/timber/)
* ["Timber and Twig Reignited My Love for WordPress" on CSS-Tricks](https://css-tricks.com/timber-and-twig-reignited-my-love-for-wordpress/)



== Changelog ==

= 1.1.12 =
* Fixed Twig issue with deprecation #1265 (thanks @codesman)!
* Cleaned-up the warnings for WP.org users and disabled easy updates for major/milestone versions 331314d9aaf90a52ff1c5a213656b8c02a27c60e

= 1.1.11 =
* Improved flexibility for public query_vars #1250 (thanks @xavivars)
* Children should inehrit publish state #1255 (thanks @motia)
* Pages are sorted by their menu order instead of publish order #1251 (thanks @forgandenny)
* Fixes to object caching #1259

= 1.1.10 =
* Added support for Co-Authors Plus Guest Authors #1239 (thanks @motia)
* Fix for Yoast SEO with multisite #1244 (thanks @alexandernanberg)
* Fixes issues with basedir restrictions that arose in Timber 1.1.9 #1245

= 1.1.9 =
* Timber now retrieves native term meta info #824
* Added site icon support in Theme #1210
* Fixes to menu getting by slug #1237 (thanks @motia)
* Fix to off-site image URLs! #1234 (thanks @njbarrett)
* Fix inconsistency with Post::get_terms #1222 (thanks @haroldangenent)

= 1.1.8 =
* Fixed image generation when images are updated/deleted by WordPress (thanks @dudewithamood)

= 1.1.7.1 =
* Quick fix for backwards compatibility in some situations

= 1.1.7 =
* A new PostQuery object that comes _with_ pagination (thanks @lggorman).
* You can pass an array of post types to `post.children()` (thanks @njbarrett)

= 1.1.6 = 
* Kill those transients! Timber now wipes expired ones away 9a5851bf36110dcb399e277d51230f1addb0c53c
* Fixed a warning that was annoying and nobody liked and didn't have any friends c53b4c832cfced01157f8196688468ad3318d3fb

= 1.1.5 =
* Removed change for custom loaders due to incompatability with Gantry

= 1.1.4 =
* Native support for Co-Authors Plus! just use `{{ post.authors }}` 939331e282fd54bf3e210645964504304f2b071b
* New filter to enable PW propmpt for PW protected posts (`timber/post/content/show_password_form_for_protected`) 0f9b20ec90b34059634c25bc27671875c18f8fcb
* New filter for custom loaders (`timber/loader/custom`) (thanks @tnottu!) 9097984a7c3df23068056d7835465e0690338567
* Fixed some updating bugs with 4.6 (thanks @daronspence) 16b8bd71571be71b298e6306abe2cd4b95d8c9e8
* You can now count Query results (thanks Evan Mattson) 141624a0ac18d9dcce62a2a681134009a2b79814

= 1.1.3 =
* New escapers! (thanks @matgargano) c7e8ed34da6fcd13bdc9005c04045f3a6b33595b
* Fix to how categories work in Timber::get_posts 49f6007db3f829097f82ed41d389dd39053fb84a
* Fix to usage of class maps in Timber::get_posts (thanks @vilpersson) b1387e443850aa021a0a70203bc20d238d4b21cb
* Added Post::password_required method (thanks @marclarr) 2e685ce3d05c50e879817e51256202e032e77122
* You can filter the link markup for Post::get_preview (thanks @LiljebergXYZ) b8100d7f2601b4da40bcc0a873c071b6ecf267f1

= 1.1.2 =
* Fix to how post IDs are retrieved (thanks @lggorman) 798acd90ee603de2d009828127bdeaab503beb10
* Fixes to pagination in search (@jarednova) 1d1ab67f124b02d8c60646f7b133abdf68cedc38
* Fixes to hooks for Timber Debug Bar (@jarednova) 82a914ec0be5be1011a15c1584c2c8e2999f1c1c

= 1.1.1 =
* Fixed 301 redirects for pagination (thanks @xavivars)
* Added new escaping filter options for `|e('wp_kses_post')` and `|e('esc_url')`(thanks @matgargano)
* Fixed pagination warning (thanks @nikola3244)
* More test coverage
* Fixed issue with archive limits (@jarednova)

= 1.1.0 =
* Fixed how Timber loads with Composer (thanks @connorjburton and @mrgrain)
* Updated docs! (thanks @lggorman and @kateboudreau)
* Fixed ImageHelper paths (thanks @TuureKaunisto)
* Added new filters for render (thanks @johnbillion)
* Fixed issue with timestamp conversion (thanks @thedamon)
* Fixed localization bugs (thanks @FlyingDR)

= 1.0.5 =
* Restored prior `{{ post.type }}` behavior for existing custom fields (@jarednova) 6c9574912e526b8589eb134b79820c7e239a1dda
* Fixed errors in PHP 7 (@FlyingDR) 48ba0fc125c2d19eeb0de0a895a83a9d3bb5a398
* Misc bug fixes and upkeep (@connorjburton + @jarednova)

= 1.0.4 =
* New method for `{{ post.type }}` this makes it easy to access things like `{{post.type.labels.name}}` right in Twig https://github.com/timber/timber/pull/1003
* New method for `{{ post.preview }}` which makes it easy to customize like `{{post.preview.length(50).read_more("Keep Reading").end('........')}}` https://github.com/timber/timber/pull/1015
* Added `Timber::get_term` (thanks @connorjburton!) 58fe671757b30a8eb9de2589bbb817448662e121
* Fix for revision issue (thanks @dknoben!) 70de6640c68a1321394aaa95202dea70e0755664
* Fix for issue with uppercase file extensions (thanks @connorjburton) 5632359329894d1b95cd643470950d319628f4c6
* Better handling for gifs (thanks @connorjburton) 91c40b852c056e0f096345d976767f2e5e993ce9
* Fix on some old class names in there (thanks @mrgrain) 63fe60ba18c6fce5d545983334af3f752c7c2755
* Pagination with post counts (thanks @lggorman) 2bcacbe50c90c7936da61d29238e3b52910a3ff9
* Remove `Timber::get_pids` (@jarednova) 4278d11d25aaca0d60cbde32c32783dc0effac6b
* Fixed deprecation in Twig (thanks @simonmilz) 6c80f1d5fd48b8fcbd335f6c8e9c6fed1b008e26
* Handle ACF image arrays (thanks @connorjburton) 039be5d880fa7f9c9763f4ebd6c40863f4820e0a

= 1.0.3 =
* Hot fix for PHP 5.3 error

= 1.0.2 =
* Fixed possible infinite loop with Timber::get_context (thanks @connorjburton) 376928d59dd5f2dd2f389c61217530ba54e40b24
* Removed bug in Term (thanks @Jmayhak) a5e3c30b9eb12acea06bc914cd6b3673ead06012
* {{ user.avatar }} now returns an Image object (thanks @connorjburton) 51dd7329aee6212490daee5742280286e221f2e8
* Attention Comment Form fans! {{ post.comment_form }} now gives you a friggin' comment form 9009ac12536a0199a1bb071ac41b2e91152bef4d
* Helper\comment_form also gives you a comment form. 9009ac12536a0199a1bb071ac41b2e91152bef4d

= 1.0.1 =
* {{ user.avatar }} property is now available (thanks @connorjburton) d21eb85
* #947: Fix to pagination base (thanks @matsrietdijk) 270d7c2
* Fix to some namespacing issues (thanks @connorjburton) 0a8346a
* #958: Call the_post action to help other plugins (thanks @felthy) 4442703
* #976: Fixed problem with static declaration (@jarednova) c888606
* #978: Bug with arrays for post types (thanks @connorjburton) 571f6f8

= 1.0.0 =
* Added `{{ user }}` object to context
* Exposed translation functions
* Added better error reporting/warnings
* Fixed some things with function wrapper
* Timber is now namespaced, big big thanks to @connorjburton
* Cleanup of spacing
* Removed deprecated functions, added warning for key functions
* Updated version numbers and build script (@jarednova) 81a281e
* Corrected Routes -> /Routes which threw a fatal error (@jarednova) 26b6585

= 0.22.6 = 
* New {{request}} object for post/get variables (thanks @connorjburton) #856
* New crop positions (thanks @salaros) #861
* Bug Fixes

* Fix to "next" in pagination (thanks @connorjburton) #900
* Fix to issue with tojpg filter's images not being deleted (thanks @connorjburton) #897
* `{{post.parent.children}}` used to return unpublished posts due to underlying behavior in WordPress, it now only returns published posts (thanks @connorjburton) #883

= 0.22.5 =
* Fixed errors in tests (thanks @lggorman)
* Fixed error in comments_link (thanks @tehlivi)

= 0.22.4 =
* Fixed [bug](https://github.com/timber/timber/issues/785) in get_calling_script file (thanks @gwagroves)
* Added tons of new tests and docs (thanks @lggorman and @jarednova)

= 0.22.3 =
* Fix to comment threadding (thanks @josephbergdoll)
* Fixed-up conditional for when comments are being moderated (thanks @lggorman)
* Fixed hooks for when attachments are deleted (thanks @lgaechter)
* Added a new filter for `list` (thanks @lggorman)

= 0.22.2 =
* New .time method for TimberPost and TimberComment (thanks @lggorman)
* Added support for WordPress's quality filter when resizing (thanks @t-wright)
* Added support for animated gifs

= 0.22.1 =
* Added better support for [post.get_terms](https://github.com/timber/timber/pull/737) (thanks @aaemnnosttv)
* Fix for issue with ACF date field (thanks @rpkoller)
* Fix for resizing jpEgs (thanks @eaton)

= 0.22.0 =
* Added fetch method to Timber (thanks @xavivars and @erik-landvall)
* Added a total to the pagination data array (thanks @lggorman)
* Threaded comments get some love! (thanks @josephbergdoll)
* A fix to date parsing when handling numeric timestamps (thanks @xavivars)

= 0.21.10 =
* Removed deprecated twitterify function
* Much more docs (and deprecation docs)
* Fixed issues with using constants (thanks @xavivars)

= 0.21.9 =
* Much much much more inline docs
* Fix to TimberComment::approved()
* HHVM support confirmed (it always worked, but now the tests prove it)
* Fixes to multisite handling of themes
* Fix to comments pagination (thanks @newkind)

= 0.21.8 =
* Fixes to things in docs
* Added ID to timber/image/src filter (thanks @aaronhippie)
* Fixed edgecase with HTTP_HOST vs SERVER_NAME (thanks @maketimetodesign)

= 0.21.7 =
* Fix for Image src in some situtations

= 0.21.6 =
* Fix for TimberMenu visiblility
* Fix for TimberComment visibility

= 0.21.5 =
* Patch for method property visibility in TimberPost

= 0.21.4 =
* Fixed issue with multisite variables
* Fixed issue with string conversion on function output

= 0.21.3 =
* Fixed issues with static post pages
* Fixed issues with front pages with static pages

= 0.21.2 =
* Fixed GIF handling (thanks @josephbergdoll and @jarednova)
* Improved handling of diff't image sizes
* Timber Archives are now tested and much improved (thanks @KLVTZ)
* Image fixing (thanks @marciojcoelho)
* More tests and improving coverage to 77%

= 0.21.1 =
* Fixed capitalization problem for WP.org version

= 0.21.0 =
* Routes is now its own independent repo
* Timber Starter Theme is now its own independent repo
* Improved loading of files (thanks @mgmartel)
* Fixed some errors with TimberImages (thanks @imranismail)

= 0.20.10 =
* Resolved lingering composer issues (thanks @austinpray, @lucasmichot)
* You can now access `{{comment.status}}` when working with comments (thanks @simonmilz)
* Better support for alternate setups with uploads directory (thanks @xavierpriour)
* Major clean-up of image-handling classes (thanks @xavierpriour)
* Starter theme now follows WP coding standards (thanks @kuus)
* A slew of other bugs and clean-up (thanks @JeyKeu, @quinn and @jaredNova)

= 0.20.9 =
* Twig goes from 1.6.2 to 1.6.3 (thanks @fabpot)
* Some clean-up items on Menus (thanks @oskarrough)
* Simplified composer installation (thanks @lucasmichot)

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
* Now you can resize/crop images with positional preferences, thanks @mmikkel. Here are the docs: https://github.com/timber/timber/wiki/TimberImage#resize
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
* Read the [Full Release Notes](https://github.com/timber/timber/releases/tag/0.15.0)

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

Please post on [StackOverflow under the "Timber" tag](http://stackoverflow.com/questions/tagged/timber). Please use GitHub issues only for specific bugs, feature requests and other types of issues.

== Frequently Asked Questions ==

= Can it be used in an existing theme? =
You bet! Watch these **[video tutorials](https://github.com/timber/timber/wiki/Video-Tutorials)** to see how.

= Is it used in production? =
At Upstatement we've now used it on more than a dozen client sites. Hundreds of other sites use it too. You can check some of them out in the **[showcase](http://upstatement.com/timber/#showcase)**.

= Doesn't this all make WordPress harder since there's more to learn? =
Does jQuery make JavaScript harder? Yes, it's an extra piece to learn -- but it super-charges your ability to write unencumbered JavaScript (and prevents you from having to learn lots of the messy internals). If your answer is "jQuery sucks and everyone should learn how to write vanilla JS or they're rotten stupid people," this tool isn't for you.

= Oh, Timber is simple code so it's for making simple themes =
Whatever. It simplifies the silly stuff so that you can focus on building more complicated sites and apps. jQuery simplifies Javascript, but you can still use the full range of JS's abilities.

= Will you support it? =
As stated above, we're using it in dozens of sites (and dozens more planned) -- dozens of other developers are using it too. This isn't going anywhere. Twig is the chosen language for other PHP platforms like Symfony, Drupal 8 and Craft. WordPress will eventually adopt Twig too, I promise you that.

= Support? =
Leave a [GitHub issue](https://github.com/timber/timber/issues?state=open) and I'll holler back.
