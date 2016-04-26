<?php

namespace Timber;

use Timber\Core;
use Timber\Theme;
use Timber\URLHelper;

/**
 * Need to display info about your theme? Well you've come to the right place. By default info on the current theme comes for free with what's fetched by `Timber::get_context()` in which case you can access it your theme like so:
 * @example
 * ```php
 * <?php
 * $context = Timber::get_context();
 * Timber::render('index.twig', $context);
 * ?>
 * ```
 * ```twig
 * <script src="{{theme.link}}/static/js/all.js"></script>
 * ```
 * ```html
 * <script src="http://example.org/wp-content/themes/my-theme/static/js/all.js"></script>
 * ```
 * @package Timber
 */
class Theme extends Core {

	/**
	 * @api
	 * @var string the human-friendly name of the theme (ex: `My Timber Starter Theme`)
	 */
	public $name;

	/**
	 * @api
	 * @var TimberTheme|bool the TimberTheme object for the parent theme (if it exists), false otherwise
	 */
	public $parent = false;

	/**
	 * @api
	 * @var string the slug of the parent theme (ex: `_s`)
	 */
	public $parent_slug;

	/**
	 * @api
	 * @var string the slug of the theme (ex: `my-super-theme`)
	 */
	public $slug;
	public $uri;

	/**
	 * Constructs a new TimberTheme object. NOTE the TimberTheme object of the current theme comes in the default `Timber::get_context()` call. You can access this in your twig template via `{{site.theme}}.
	 * @param string $slug
	 * @example
	 * ```php
	 * <?php
	 *     $theme = new TimberTheme("my-theme");
	 *     $context['theme_stuff'] = $theme;
	 *     Timber::render('single.')
	 * ?>
	 * ```
	 * ```twig
	 * We are currently using the {{ theme_stuff.name }} theme.
	 * ```
	 * ```html
	 * We are currently using the My Theme theme.
	 * ```
	 */
	function __construct( $slug = null ) {
		$this->init($slug);
	}

	/**
	 * @internal
	 * @param string $slug
	 */
	protected function init( $slug = null ) {
		$data = wp_get_theme($slug);
		$this->name = $data->get('Name');
		$ss = $data->get_stylesheet();
		$this->slug = $ss;

		if ( !function_exists('get_home_path') ) {
			require_once(ABSPATH.'wp-admin/includes/file.php');
		}

		$this->uri = get_stylesheet_directory_uri();
		$this->parent_slug = $data->get('Template');
		if ( !$this->parent_slug ) {
			$this->uri = get_template_directory_uri();
		}
		if ( $this->parent_slug && $this->parent_slug != $this->slug ) {
			$this->parent = new Theme($this->parent_slug);
		}
	}

	/**
	 * @api
	 * @return string the absolute path to the theme (ex: `http://example.org/wp-content/themes/my-timber-theme`)
	 */
	public function link() {
		return $this->uri;
	}

	/**
	 * @api
	 * @return  string the relative path to the theme (ex: `/wp-content/themes/my-timber-theme`)
	 */
	public function path() {
		return URLHelper::get_rel_url($this->link());
	}

	/**
	 * @param string $name
	 * @param bool $default
	 * @return string
	 */
	public function theme_mod( $name, $default = false ) {
		return get_theme_mod($name, $default);
	}

	/**
	 * @return array
	 */
	public function theme_mods() {
		return get_theme_mods();
	}

}
