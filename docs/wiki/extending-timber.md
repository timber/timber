# Extending Timber

Myth: Timber is for making simple themes. Fact: It's for making incredibly complex themes _look_ easy. But yes, you can also make simple sites from it.

## Extending Objects

The beauty of Timber is that the object-oriented nature lets you extend it to match the exact requirements of your theme.

Timber's objects like `TimberPost`, `TimberTerm`, etc. are a great starting point to build your own subclass from. For example, on this project each post was a part of an "issue" of a magazine. I wanted an easy way to reference the issue in the twig file:


```html
<h1>{{ post.title }}</h1>
<h3>From the {{ post.issue.title }} issue</h3>
```

Of course, `TimberPost` has no built-in concept of an issue (which I've built as a custom taxonomy called "issues"). So we're going to extend TimberPost to give it one...

```php
class MySitePost extends TimberPost {

	var $_issue;

	public function issue() {
		$issues = $this->get_terms('issues');
		if (is_array($issues) && count($issues)) {
			return $issues[0];
		}
	}
}
```

So now I've got an easy way to refer to the {{ post.issue }} in our twig templates. If you want to make this production-ready I recommend a bit of internal caching so that you don't re-query every time you need to get the
issue data:

```php
class MySitePost extends TimberPost {

	var $_issue;

	public function issue() {
		if (!$this->_issue) {
			$issues = $this->get_terms('issues');
			if (is_array($issues) && count($issues)) {
				$this->_issue = $issues[0];
			}
		}
		return $this->_issue;
	}
}
```

Right now I'm in the midst of building a complex site for a hybrid foundation and publication. The posts on the site have some very specific requirements that requires a fair amount of logic. I can take the simple `TimberPost` object and extend it to make it work perfectly for this theme.

For example, I have a plugin that let's people insert manually related posts, but if they don't, WordPress will pull some automatically based on how the post is tagged.

```php

	class MySitePost extends TimberPost {

		function get_related_auto() {
			$tags = $this->tags();
			if (is_array($tags) && count($tags)) {
				$search_tag = $tags[0];
				$related = Timber::get_posts('tag_id='.$search_tag->ID);
				return $related;
			} else {
				//not tagged, cant do related on it
				return false;
			}
		}

		function get_related_manual() {
			if (isset($this->related_manual) && is_array($this->related_manual)){
				foreach($this->related_manual as &$related){
					$related = new MySitePost($related);
				}
				return $this->related_manual;
			}
			return false;
		}

		function related($limit = 3) {
			$related = $this->get_related_manual();
			if (!$related){
				$related = $this->get_related_auto();
			}
			if (is_array($related)) {
				array_splice($related, 0, $limit);
			}
			return $related;
		}
	}
```

These can get pretty complex. And that's the beauty. The complexity lives inside the context of the object, but very simple when it comes to your templates.


## Adding to Twig

This is the correct formation for when you need to add custom functions, filters to twig:

```php

/* functions.php */

add_filter('get_twig', 'add_to_twig');

function add_to_twig($twig) {
	/* this is where you can add your own fuctions to twig */
	$twig->addExtension(new Twig_Extension_StringLoader());
  /**
   * Deprecated: Twig_Filter_Function, use Twig_SimpleFilter
   * http://twig.sensiolabs.org/doc/deprecated.html#filters
   * $twig->addFilter('whatever', new Twig_Filter_Function('my_whatever'));
   */
  $twig->addFilter('myfoo', new Twig_SimpleFilter('myfoo', 'my_whatever'));
  return $twig;
}

function my_whatever($text) {
	$text .= ' or whatever';
	return $text;
}
```

Following is shortened version of [Timber Starter Theme](https://github.com/upstatement/timber-starter-theme) class definition using methods to add filters.

```php

/* functions.php */

class StarterSite extends TimberSite {

  function __construct() {
    add_filter( 'timber_context', array( $this, 'add_to_context' ) );
    add_filter( 'get_twig', array( $this, 'add_to_twig' ) );
    parent::__construct();
  }

  function add_to_context( $context ) {
    $context['menu'] = new TimberMenu();
    $context['site'] = $this;
    return $context;
  }

  function my_whatever( $text ) {
    $text .= ' or whatever';
    return $text;
  }

  function add_to_twig( $twig ) {
    /* this is where you can add your own functions to twig */
    $twig->addExtension( new Twig_Extension_StringLoader() );
    /**
     * Deprecated: Twig_Filter_Function, use Twig_SimpleFilter
     * http://twig.sensiolabs.org/doc/deprecated.html#filters
     * $twig->addFilter( 'whatever', new Twig_Filter_Function( 'whatever' ) );
     */
    $twig->addFilter('whatever', new Twig_SimpleFilter('whatever', array($this, 'my_whatever')));
    return $twig;
  }

}

new StarterSite();

```

This can now be called in your twig files with:

```html
<h2>{{ post.title|whatever }}</h2>
```

Which will output:

```html
<h2>Hello World! or whatever</h2>
```
