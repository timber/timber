<?php

namespace Timber;

/**
  * Temporary fix for conflicts between Twig_Function and Twig_SimpleFunction
  * in different versions of Twig (1.* and 2.*)
  */
if ( version_compare(\Twig_Environment::VERSION, '2.0.0', '>=') ) {

  class Twig_Function extends \Twig_Function { }

} else {

  class Twig_Function extends \Twig_SimpleFunction { }
  
}