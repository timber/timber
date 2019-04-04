<?php

namespace Timber;

/**
 * Handle TwigFunction among different Twig versions.
 *
 * Temporary fixes for conflicts between Twig_Function and Twig_SimpleFunction in different versions
 * of Twig (1.*, 2.* and 2.4+). From Twig 2.4.0, extending Twig_Filter is deprecated and the class
 * will be final in 3.0.
 *
 * @ticket #1641
 */

if ( class_exists( '\Twig\TwigFunction' ) ) {
	// Twig version >= 2.4 with namespaced classes.
	class_alias('\Twig\TwigFunction', '\Timber\Twig_Function');

} elseif ( class_exists( '\Twig_Function' ) ) {
	// Twig version >= 2.0.0
	class Twig_Function extends \Twig_Function { }

} else {
	// Twig 1.x
	class Twig_Function extends \Twig_SimpleFunction { }

}
