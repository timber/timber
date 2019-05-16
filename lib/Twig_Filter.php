<?php

namespace Timber;

/**
 * Handle TwigFilter among different Twig versions.
 *
 * Temporary fixes for conflicts between Twig_Filter and Twig_SimpleFilter in different versions of
 * Twig (1.*, 2.* and 2.4+). From Twig 2.4.0, extending Twig_Filter is deprecated and the class will
 * be final in 3.0.
 */
if ( class_exists( '\Twig\TwigFilter' ) ) {
	// Twig version >= 2.4 with namespaced classes.
	class_alias( '\Twig\TwigFilter', '\Timber\Twig_Filter' );
} elseif ( class_exists( '\Twig_Filter' ) ) {
	// Twig version >= 2.0.0
	class Twig_Filter extends \Twig_Filter {}
} else {
	// Twig 1.x
	class Twig_Filter extends \Twig_SimpleFilter {}
}
