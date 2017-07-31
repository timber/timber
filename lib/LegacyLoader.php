<?php

namespace Timber;

class LegacyLoader
	extends \Twig_Loader_Filesystem
	implements \Twig_LoaderInterface
{
	public static function create($caller)
	{
		$locations = LocationManager::get_locations($caller);

		$open_basedir = ini_get('open_basedir');

		$paths = array_merge($locations, array($open_basedir ? ABSPATH : '/'));
		$paths = apply_filters('timber/loader/paths', $paths);

		$rootPath = '/';
		if ( $open_basedir ) {
			$rootPath = null;
		}

		return new static($paths, $rootPath);
	}
}
