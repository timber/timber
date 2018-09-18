<?php

namespace Timber;

/**
 * Handle TwigFunction among Twig versions
 *
 * From Twig 2.4.0, extending Twig_Function is deprecated, will be final in 3.0
 * @ticket #1641
 * Temporary fix for conflicts between Twig_Function and Twig_SimpleFunction
 * in different versions of Twig (1.* and 2.*)
 */

if ( version_compare(\Twig_Environment::VERSION, '2.4.0', '>=') ) {

	class_alias('\Twig\TwigFunction', '\Timber\Twig_Function');

} elseif ( version_compare(\Twig_Environment::VERSION, '2.0.0', '>=') ) {

	class Twig_Function extends \Twig_Function { }

} else {

	class Twig_Function extends \Twig_SimpleFunction { }

}
