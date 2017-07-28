<?php

namespace Timber;

/**
 * This is leftovers after Timber's extensions to Twig was moved into an independent Twig extension class, TwigExtension.
 * 
 * This class was temporarily preserved to avoid messing with the filter order. The two filters, 'timber/twig' and 'get_twig',
 * is defined here, and added via Twig::init(), which is called from Timber::init().
 * 
 * @todo The filters should possibly be moved safely to either Loader::get_twig() or temporarily to Timber::init().
 * @codeCoverageIgnore
 */
class Twig {

	/**
	 * This property was not used internally prior moving class methods to TwigExtension...
	 * 
	 * @codeCoverageIgnore
	 */
	public static $dir_name;

	/**
	 * @codeCoverageIgnore
	 */
	public static function init() {
		// This is preserved due to the existence of then $dir_name property
		new self();
		
		add_action('timber/twig/filters', array(__CLASS__, 'add_timber_filters'));
	}

	/**
	 *
	 *
	 * @codeCoverageIgnore
	 *
	 * @param Twig_Environment $twig
	 * @return Twig_Environment
	 */
	public static function add_timber_filters( $twig ) {
		
		$twig = apply_filters('timber/twig', $twig);
		/**
		 * get_twig is deprecated, use timber/twig
		 */
		$twig = apply_filters('get_twig', $twig);
		return $twig;
	}
}
