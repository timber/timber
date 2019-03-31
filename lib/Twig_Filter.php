<?php

namespace Timber;

/**
 * Handle TwigFilter among different Twig versions.
 *
 * Temporary fixes for conflicts between Twig_Filter and Twig_SimpleFilter
 * in different versions of Twig (1.* and 2.*).
 * From Twig 2.4.0, extending Twig_Filter is deprecated and will be final in 3.0.
 */
if ( version_compare( \Twig_Environment::VERSION, '2.4.0', '>=' ) ) {
	class_alias( '\Twig\TwigFilter', '\Timber\Twig_Filter' );
} elseif ( version_compare( \Twig_Environment::VERSION, '2.0.0', '>=' ) ) {
	class Twig_Filter extends \Twig_Filter {}
} else {
	class Twig_Filter extends \Twig_SimpleFilter {}
}
