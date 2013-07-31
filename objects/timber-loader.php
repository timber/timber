<?php

class TimberLoader
{

  var $locations;

  function __construct($caller = false)
  {
    $this->locations = $this->get_locations($caller);
  }

  function render($file, $data = null)
  {
    $twig = $this->get_twig();
    return $twig->render($file, $data);
  }

  function choose_template($filenames)
  {
    if (is_array($filenames)) {
      /* its an array so we have to figure out which one the dev wants */
      foreach ($filenames as $filename) {
        if ($this->template_exists($filename)) {
          return $filename;
        }
      }
      return false;
    }
    return $filenames;
  }

  function template_exists($file)
  {
    foreach ($this->locations as $dir) {
      $look_for = trailingslashit($dir) . $file;
      if (file_exists($look_for)) {
        return true;
      }
    }
    return false;
  }

  function get_locations_theme()
  {
    $theme_locs = array();
    $child_loc = get_stylesheet_directory();
    $parent_loc = get_template_directory();
    $theme_locs[] = $child_loc;
    if ($child_loc != $parent_loc) {
      $theme_locs[] = $parent_loc;
    }
    //add the template directory to each path
    foreach ($theme_locs as $tl) {
      $theme_locs[] = trailingslashit($tl) . trailingslashit(Timber::$dirname);
    }
    //now make sure theres a trailing slash on everything
    foreach ($theme_locs as &$tl) {
      $tl = trailingslashit($tl);
    }
    return $theme_locs;
  }

  function get_locations_user()
  {
    $locs = array();
    if (isset(Timber::$locations)) {
      if (is_string(Timber::$locations)) {
        Timber::$locations = array(Timber::$locations);
      }
      foreach (Timber::$locations as $tloc) {
        $tloc = realpath($tloc);
        if (is_dir($tloc)) {
          $locs[] = $tloc;
        }
      }
    }
    return $locs;
  }

  function get_locations_caller($caller = false)
  {
    $locs = array();
    if ($caller && is_string($caller)) {
      $caller = trailingslashit($caller);
      if (is_dir($caller)) {
        $locs[] = $caller;
      }
      $caller_sub = $caller . trailingslashit(Timber::$dirname);
      if (is_dir($caller_sub)) {
        $locs[] = $caller_sub;
      }
    }
    return $locs;
  }

  function get_locations($caller = false)
  {
    //prioirty: user locations, caller, theme
    $locs = array();
    $locs = array_merge($locs, $this->get_locations_user());
    $locs = array_merge($locs, $this->get_locations_caller($caller));
    $locs = array_merge($locs, $this->get_locations_theme());
    $locs = array_unique($locs);
    return $locs;
  }

  function get_loader()
  {
    $loaders = array();
    foreach ($this->locations as $loc) {
      $loc = realpath($loc);
      if (is_dir($loc)) {
        $loc = realpath($loc);
        $loaders[] = new Twig_Loader_Filesystem($loc);
      } else {
        //error_log($loc.' is not a directory');
      }
    }
    $loader = new Twig_Loader_Chain($loaders);
    return $loader;
  }

  function get_twig()
  {
    $loader_loc = trailingslashit(TIMBER_LOC) . 'Twig/lib/Twig/Autoloader.php';
    require_once($loader_loc);
    Twig_Autoloader::register();

    $loader = $this->get_loader();
    $params = array('debug' => WP_DEBUG, 'autoescape' => false);
    if (Timber::$cache) {
      $params['cache'] = TIMBER_LOC . '/twig-cache';
    }
    $twig = new Twig_Environment($loader, $params);
    $twig->addExtension(new Twig_Extension_Debug());
    $twig = apply_filters('twig_apply_filters', $twig);
    return $twig;
  }

  function get_file($filenames)
  {

  }
}