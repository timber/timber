<?php

namespace Timber;

if ( version_compare(\Twig_Environment::VERSION, '2.0.0', '>=') ) {

  class Timber_Function extends \Twig_Function { }

} else {

  class Timber_Function extends \Twig_SimpleFunction { }
  
}