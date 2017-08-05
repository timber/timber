<?php

namespace Timber\TwigLoader;

use Timber\LocationManager;

/**
 * @author Heino H. Gehlsen <heino@gehlsen.dk>
 * @copyright 2017 Heino H. Gehlsen
 * @license MIT
 */
class LegacyLoader
	implements \Twig_LoaderInterface, CallerCompatibleInterface
{
	protected $set = false;
	protected $filesystemLoader;

	/**
	 * 
	 */
	public function __construct() {
		$open_basedir = ini_get('open_basedir');
		$rootPath = $open_basedir ? null : '/';

		$this->filesystemLoader = new \Twig_Loader_Filesystem(array(), $rootPath);
	}

	public function setCaller($caller = false)
	{
		$locations = LocationManager::get_locations($caller);

		$open_basedir = ini_get('open_basedir');

		$paths = array_merge($locations, array($open_basedir ? ABSPATH : '/'));
		$paths = apply_filters('timber/loader/paths', $paths);

		$this->filesystemLoader->setPaths($paths);
		$this->set = true;
	}

	public function resetCaller()
	{
		$this->filesystemLoader->setPaths(array());
		$this->set = false;
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
		if ($this->set === false) {
			$this->setCaller();
		}
		return $this->filesystemLoader->getSourceContext($name);
	}

    /**
     * Gets the source code of a template, given its name.
     *
     * @param string $name The name of the template to load
     * @return string The template source code
     * @throws Twig_Error_Loader When $name is not found
     * @deprecated since 1.27 (to be removed in 2.0), implement Twig_SourceContextLoaderInterface
     */
    public function getSource($name)
	{
		return $this->getSourceContext($name)->getCode();
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
		if ($this->set === false) {
			$this->setCaller();
		}
		return $this->filesystemLoader->getCacheKey($name);
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
		if ($this->set === false) {
			$this->setCaller();
		}
		return $this->filesystemLoader->isFresh($name, $time);
	}

    /**
     * Check if we have the source code of a template, given its name.
     *
     * @param string $name The name of the template to check if we can load
     * @return bool If the template source code is handled by this loader or not
     */
    public function exists($name)
	{
		if ($this->set === false) {
			$this->setCaller();
		}
		return $this->filesystemLoader->exists($name);
	}
}
