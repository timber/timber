<?php

namespace Timber;

use Timber\Cache\Cleaner;

class Twig_Environment 
	extends \Twig_Environment
{
	/**
     * @param Twig_LoaderInterface $loader
     * @param array                $options An array of options
	 */
	public function __construct(\Twig_LoaderInterface $loader, $options = array()) {
		
		$loader = apply_filters('timber/loader/loader', $loader);
// TODO: Consider this new filter as a future replacement for 'timber/loader/loader'
//		$loader = apply_filters('timber/twig/loader', $loader, $this);
		if ( !$loader instanceof \Twig_LoaderInterface ) {
			throw new \UnexpectedValueException('Loader must implement \Twig_LoaderInterface');
		}

		if ($loader instanceof LegacyLoader) {
			// It's a legacy loader...
		}

		$options = array('debug' => WP_DEBUG, 'autoescape' => false);
		if ( isset(Timber::$autoescape) ) {
			$options['autoescape'] = Timber::$autoescape;
		}

// TODO: Consider this new (experimental) filter!
//		$options = apply_filters('timber/twig/options', $options, $this);

		if ( Timber::$cache === true ) {
			Timber::$twig_cache = true;
		}
		if ( Timber::$twig_cache ) {
			$twig_cache_loc = apply_filters('timber/cache/location', TIMBER_LOC.'/cache/twig');
			if ( !file_exists($twig_cache_loc) ) {
				mkdir($twig_cache_loc, 0777, true);
			}
			$options['cache'] = $twig_cache_loc;
		}

		parent::__construct($loader, $options);

		if ( WP_DEBUG ) {
			$this->addExtension(new \Twig_Extension_Debug());
		}

		do_action('timber/twig', $this);
		/**
		 * get_twig is deprecated, use timber/twig
		 */
		do_action('get_twig', $this);
	}
}


/**
 * @param \Twig_Environment $twig
 * @return \Twig_Environment
 * @internal
 */
function do_legacy_twig_environment_filters_pre_timber_twig(\Twig_Environment $twig) {
	do_action('twig_apply_filters', $twig);
	do_action('timber/twig/filters', $twig);
}
// Attach action with lower than default priority to simulate the filters prior location before 'timber/twig' was fired at the bottom of Twig::add_timber_filters()
add_action('timber/twig', __NAMESPACE__.'\do_legacy_twig_environment_filters_pre_timber_twig', 5);

/**
 * @param \Twig_Environment $twig
 * @return \Twig_Environment
 * @internal
 */
function do_legacy_twig_environment_filters_post_timber_twig(\Twig_Environment $twig) {
	do_action('timber/twig/functions', $twig);
	do_action('timber/twig/escapers', $twig);
	do_action('timber/loader/twig', $twig);
}
// Attach action with higher than default priority to simulate the filters prior location after 'timber/twig' was fired at the bottom of Twig::add_timber_filters()
add_action('timber/twig', __NAMESPACE__.'\do_legacy_twig_environment_filters_post_timber_twig', 15);
