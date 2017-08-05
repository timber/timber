<?php

namespace Timber\Cache\Psr16;

/**
 * Adapter class to make WordPress' site transient caching available wia the PSR-16 interface.
 * This verison truncates keys longer than what Wordpress supports
 * 
 * @author Heino H. Gehlsen <heino@gehlsen.dk>
 * @copyright 2017 Heino H. Gehlsen
 * @license MIT
 */
class TimberSiteTransientAdapter
	extends WordpressSiteTransientAdapter
{
	/**
     * @var string
     */
	protected $keyPrefix;

	/**
     * @param string @group
     */
    public function __construct($prefix = null, $separator = '_')
	{
		// Verify that Wordpress is loaded.
		parent::__construct();

		//
		$this->validatePrefix($prefix);

		//
		$this->keyPrefix = $prefix == null ? '' : $prefix.$separator;
	}

	/**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
	{
		return parent::get($this->keyPrefix.$key, $default);
	}

	/**
     * {@inheritdoc}
     */
    public function set($key, $value, $ttl = null)
	{
		return parent::set($this->keyPrefix.$key, $value, $ttl);
	}

	/**
     * {@inheritdoc}
     */
    public function delete($key)
	{
		return parent::delete($this->keyPrefix.$key);
	}

	/**
     * {@inheritdoc}
     */
    public function getMultiple($keys, $default = null)
	{
		//
		$keyPrefix = $this->keyPrefix;
		
		//
		$keys = array_map(
			function($key) use ($keyPrefix) {
				return $keyPrefix.$key;
			}, 
			$keys
		);

		//
		return parent::deleteMultiple($values, $default);
	}

	/**
     * {@inheritdoc}
     */
    public function setMultiple($values, $ttl = null)
	{
		//
		$keyPrefix = $this->keyPrefix;

		//
		$values = array_combine(
			array_map(
				function($key) use ($keyPrefix) {
					return $keyPrefix.$key;
				},
				array_keys($values)
			),
			$values
		);
		
		//
		return parent::setMultiple($values, $ttl);
	}

	/**
     * {@inheritdoc}
     */
    public function deleteMultiple($keys)
	{
		//
		$keyPrefix = $this->keyPrefix;
		
		//
		$keys = array_map(
			function($key) use ($keyPrefix) {
				return $keyPrefix.$key;
			}, 
			$keys
		);

		//
		return parent::deleteMultiple($values);
	}

	/**
     * {@inheritdoc}
     */
    public function has($key)
	{
		return parent::has($this->keyPrefix.$key);
	}

    /**
     * 
     */
    protected function validatePrefix($prefix)
	{
		// TODO: Thow something on unwated stuff...
	}

	public function clear()
	{
		global $wpdb;

// TODO: This is SQL! $keyPrefix is currently unsafe!
		// Origin: Timber v1.3.4 (Timber\Loader::clearCacheTimberDatabase())
		$query = $wpdb->prepare(
			"DELETE
				FROM
					$wpdb->options
				WHERE
					option_name
				LIKE '%s'",
			'_site_transient_'.$this->keyPrefix.'%'
		);

		// Execute query
		$deleted = $wpdb->query($query);

		// Handle SQL fail
		if ($deleted === false) {

			// Reset, since nothing happened
			$this->deletedDuringLastClear = 0;

			// Return fail
			return false;
		}

		// Remember number of deleted records
		$this->deletedDuringLastClear = $deleted;

		// Return success
		return true;
	}
}
