<?php

namespace Timber;

use Timber\Core;
use Timber\CoreInterface;

use Timber\Theme;
use Timber\Helper;

/**
 * TimberSite gives you access to information you need about your site. In Multisite setups, you can get info on other sites in your network.
 * @example
 * ```php
 * $context = Timber::get_context();
 * $other_site_id = 2;
 * $context['other_site'] = new TimberSite($other_site_id);
 * Timber::render('index.twig', $context);
 * ```
 * ```twig
 * My site is called {{site.name}}, another site on my network is {{other_site.name}}
 * ```
 * ```html
 * My site is called Jared's blog, another site on my network is Upstatement.com
 * ```
 */
class Site extends Core implements CoreInterface {

	/**
	 * @api
	 * @var string the admin email address set in the WP admin panel
	 */
	public $admin_email;
	public $blogname;
	/**
	 * @api
	 * @var string
	 */
	public $charset;

	/**
	 * @api
	 * @var string
	 */
	public $description;
	/**
	 * @api
	 * @var int the ID of a site in multisite
	 */
	public $id;
	/**
	 * @api
	 * @var string the language setting ex: en-US
	 */
	public $language;
	/**
	 * @api
	 * @var bool true if multisite, false if plain ole' WordPress
	 */
	public $multisite;

	/**
	 * @api
	 * @var string
	 */
	public $name;

	/** @api
	 * @var string for people who like trackback spam
	 */
	public $pingback_url;
	public $siteurl;
	/**
	 * @api
	 * @var [TimberTheme](#TimberTheme)
	 */
	public $theme;
	/**
	 * @api
	 * @var string
	 */
	public $title;
	public $url;
	public $home_url;
	public $site_url;

	/**
	 * @api
	 * @var string
	 */

	public $rdf;
	public $rss;
	public $rss2;
	public $atom;

	/**
	 * Constructs a TimberSite object
	 * @example
	 * ```php
	 * //multisite setup
	 * $site = new TimberSite(1);
	 * $site_two = new TimberSite("My Cool Site");
	 * //non-multisite
	 * $site = new TimberSite();
	 * ```
	 * @param string|int $site_name_or_id
	 */
	public function __construct( $site_name_or_id = null ) {
		if ( is_multisite() ) {
			$blog_id = self::switch_to_blog($site_name_or_id);
			$this->init();
			$this->init_as_multisite($blog_id);
			restore_current_blog();
		} else {
			$this->init();
			$this->init_as_singlesite();
		}
	}

	/**
	 * Switches to the blog requested in the request
	 * @param string|integer|null $site_name_or_id
	 * @return integer with the ID of the new blog
	 */
	protected static function switch_to_blog( $site_name_or_id ) {
		if ( $site_name_or_id === null ) {
			$site_name_or_id = get_current_blog_id();
		}
		$info = get_blog_details($site_name_or_id);
		switch_to_blog($info->blog_id);
		return $info->blog_id;
	}

	/**
	 * @internal
	 * @param integer $site_id
	 */
	protected function init_as_multisite( $site_id ) {
		$info = get_blog_details($site_id);
		$this->import($info);
		$this->ID = $info->blog_id;
		$this->id = $this->ID;
		$this->name = $this->blogname;
		$this->title = $this->blogname;
		$theme_slug = get_blog_option($info->blog_id, 'stylesheet');
		$this->theme = new Theme($theme_slug);
		$this->description = get_blog_option($info->blog_id, 'blogdescription');
		$this->admin_email = get_blog_option($info->blog_id, 'admin_email');
		$this->multisite = true;
	}

	/**
	 * Executed for single-blog sites
	 * @internal
	 */
	protected function init_as_singlesite() {
		$this->admin_email = get_bloginfo('admin_email');
		$this->name = get_bloginfo('name');
		$this->title = $this->name;
		$this->description = get_bloginfo('description');
		$this->theme = new Theme();
		$this->multisite = false;
	}

	/**
	 * Executed for all types of sites: both multisite and "regular"
	 * @internal
	 */
	protected function init() {
		$this->url = home_url();
		$this->home_url = $this->url;
		$this->site_url = site_url();
		$this->rdf = get_bloginfo('rdf_url');
		$this->rss = get_bloginfo('rss_url');
		$this->rss2 = get_bloginfo('rss2_url');
		$this->atom = get_bloginfo('atom_url');
		$this->language = get_bloginfo('language');
		$this->charset = get_bloginfo('charset');
		$this->pingback = $this->pingback_url = get_bloginfo('pingback_url');
	}


	/**
	 * Returns the language attributes that you're looking for
	 * @return string
	 */
	public function language_attributes() {
		return get_language_attributes();
	}

	/**
	 *
	 *
	 * @param string  $field
	 * @return mixed
	 */
	public function __get( $field ) {
		if ( !isset($this->$field) ) {
			if ( is_multisite() ) {
				$this->$field = get_blog_option($this->ID, $field);
			} else {
				$this->$field = get_option($field);
			}
		}
		return $this->$field;
	}

	public function icon() {
		if ( is_multisite() ) {
			return $this->icon_multisite($this->ID);
		}
		$iid = get_option('site_icon');
		if ( $iid ) {
			return new Image($iid);
		}
	}

	protected function icon_multisite( $site_id ) {
		$image = null;
		$blog_id = self::switch_to_blog($site_id);
		$iid = get_blog_option($blog_id, 'site_icon');
		if ( $iid ) {
			$image = new Image($iid);
		}
		restore_current_blog();
		return $image;
	}

	/**
	 * Returns the link to the site's home.
	 * @example
	 * ```twig
	 * <a href="{{ site.link }}" title="Home">
	 * 	  <img src="/wp-content/uploads/logo.png" alt="Logo for some stupid thing" />
	 * </a>
	 * ```
	 * ```html
	 * <a href="http://example.org" title="Home">
	 * 	  <img src="/wp-content/uploads/logo.png" alt="Logo for some stupid thing" />
	 * </a>
	 * ```
	 * @api
	 * @return string
	 */
	public function link() {
		return $this->url;
	}

	/**
	 * @deprecated 0.21.9
	 * @internal
	 * @return string
	 */
	public function get_link() {
		Helper::warn('{{site.get_link}} is deprecated, use {{site.link}}');
		return $this->link();
	}


	/**
	 * @ignore
	 */
	public function meta( $field ) {
		return $this->__get($field);
	}

	/**
	 *
	 * @ignore
	 * @param string  $key
	 * @param mixed   $value
	 */
	public function update( $key, $value ) {
		$value = apply_filters('timber_site_set_meta', $value, $key, $this->ID, $this);
		if ( is_multisite() ) {
			update_blog_option($this->ID, $key, $value);
		} else {
			update_option($key, $value);
		}
		$this->$key = $value;
	}

	/**
	 * @deprecated 1.0.4
	 * @see TimberSite::link
	 * @return string
	 */
	public function url() {
		return $this->link();
	}

	/**
	 * @deprecated 0.21.9
	 * @internal
	 * @return string
	 */
	public function get_url() {
		Helper::warn('{{site.get_url}} is deprecated, use {{site.link}} instead');
		return $this->link();
	}

}
