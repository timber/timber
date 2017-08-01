<?php

namespace Timber;

/**
 *
 */
class ChainLoader
	implements \Twig_LoaderInterface, CallerCompatibleLoaderInterface
{
	private $chain;
	private $temporaryLoader;
	private $locationsLoader;
	private $themeLoader;
	private $basedirLoader;
	private $callerLoader;
	private $caller2Loader;

	/**
	 * 
	 */
	public function __construct() {
		$open_basedir = ini_get('open_basedir');
		$rootPath = $open_basedir ? null : '/';

		$this->chain = new \Twig_Loader_Chain();
		
		$this->chain->addLoader($this->temporaryLoader = new \Twig_Loader_Filesystem(array(), $rootPath));
		$this->chain->addLoader($this->locationsLoader = new \Twig_Loader_Filesystem(array(), $rootPath));
		$this->chain->addLoader($this->callerLoader = new \Twig_Loader_Filesystem(array(), $rootPath));
		$this->chain->addLoader($this->themeLoader = new \Twig_Loader_Filesystem(array(), $rootPath));
		$this->chain->addLoader($this->caller2Loader = new \Twig_Loader_Filesystem(array(), $rootPath));
		$this->chain->addLoader($this->basedirLoader = new \Twig_Loader_Filesystem(array(), $rootPath));

		$this->updateLoaders();
	}

	/**
     * Returns the source context for a given template logical name.
     *
     * @param string $name The template logical name
     * @return Twig_Source
     * @throws Twig_Error_Loader When $name is not found
     */
    public function getSourceContext($name)
	{
		return $this->chain->getSourceContext($name);
	}

    /**
     * Gets the cache key to use for the cache for a given template name.
     *
     * @param string $name The name of the template to load
     * @return string The cache key
     * @throws Twig_Error_Loader When $name is not found
     */
    public function getCacheKey($name)
	{
		return $this->chain->getCacheKey($name);
	}

    /**
     * Returns true if the template is still fresh.
     *
     * @param string $name The template name
     * @param int    $time Timestamp of the last modification time of the
     *                     cached template
     * @return bool true if the template is fresh, false otherwise
     * @throws Twig_Error_Loader When $name is not found
     */
    public function isFresh($name, $time)
	{
		return $this->chain->isFresh($name, $time);
	}

    /**
     * Check if we have the source code of a template, given its name.
     *
     * @param string $name The name of the template to check if we can load
     * @return bool If the template source code is handled by this loader or not
     */
    public function exists($name)
	{
		return $this->chain->exists($name);
	}

	/**
	 *  
	 */
	public function updateLoaders() {
		$open_basedir = ini_get('open_basedir');

		$theme = LocationManager::get_locations_theme();
		$theme = apply_filters('timber/loader/paths', $theme);
// TODO: Consider this new filter as a future replacement
//		$theme = apply_filters('timber/twig/loader/theme', $theme);

		$locations = LocationManager::get_locations_user();
		$locations = array_diff($locations, $theme);
		$locations = apply_filters('timber_locations', $locations);
		$locations = apply_filters('timber/locations', $locations);
// TODO: Consider this new filter as a future replacement
//		$locations = apply_filters('timber/twig/loader/locations', $theme);

		$basedir = array($open_basedir ? ABSPATH : '/');
// TODO: Consider this new filter
//		$basedir = apply_filters('timber/twig/loader/basedir', $theme);

		$this->locationsLoader->setPaths($locations);
		$this->themeLoader->setPaths($theme);
		$this->basedirLoader->setPaths($basedir);

		$this->resetCaller();
	} 
	
	/**
	 *  
	 * @param string|false $caller
	 */
	public function setCaller($caller = false)
	{
		if ($caller === false) {
			$this->resetCaller();
			return;
		}

		$locations = $this->locationsLoader->getPaths();
		$theme = $this->themeLoader->getPaths();
		
		$caller1 = LocationManager::get_locations_caller($caller);
		$caller1 = array_diff($caller1, $locations, $theme);
		$this->callerLoader->setPaths($caller1);
		
		$caller2 = LocationManager::get_locations_caller($caller);
		$caller2 = array_diff($caller2, $locations, $theme, $caller1);
		$this->caller2Loader->setPaths($caller2);
	} 
	
	/**
	 *  
	 * @param string $CALLER
	 */
	public function resetCaller()
	{
		$this->callerLoader->setPaths(array());
		$this->caller2Loader->setPaths(array());
	} 

	/**
	 * @return \Twig_Loader_Filesystem
	 */
	public function getTemporaryLoader() {
		return $this->temporaryLoader;
	}

	/**
	 * @return \Twig_Loader_Filesystem
	 */
	public function getLocationsLoader() {
		return $this->locationsLoader;
	}

	/**
	 * @return \Twig_Loader_Filesystem
	 */
	public function getThemeLoader() {
		return $this->themeLoader;
	}

	/**
	 * @return \Twig_Loader_Filesystem
	 */
	public function getBasedirLoader() {
		return $this->basedirLoader;
	}
}
