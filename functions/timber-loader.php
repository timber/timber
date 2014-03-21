<?php

class TimberLoader {

    const CACHEGROUP = 'timberloader';

    const TRANS_KEY_LEN		   = 50;

    const CACHE_NONE           = 'none';
    const CACHE_OBJECT         = 'cache';
    const CACHE_TRANSIENT      = 'transient';
    const CACHE_SITE_TRANSIENT = 'site-transient';
    const CACHE_USE_DEFAULT    = 'default';

    public static $cache_modes = array(
        self::CACHE_NONE,
        self::CACHE_OBJECT,
        self::CACHE_TRANSIENT,
        self::CACHE_SITE_TRANSIENT
    );

    protected $cache_mode = self::CACHE_TRANSIENT;

    var $locations;

	function __construct($caller = false) {
		$this->locations = $this->get_locations($caller);
        $this->cache_mode = apply_filters( 'timber_cache_mode', $this->cache_mode );
	}

	function render( $file, $data = null, $expires = false, $cache_mode = self::CACHE_USE_DEFAULT ) {
        // Different $expires if user is anonymous or logged in
        if ( is_array( $expires ) ) {
            if ( is_user_logged_in() && isset( $expires[1] ) )
                $expires = $expires[1];
            else
                $expires = $expires[0];
        }

        $key = null;

        $output = false;
        if ( false !== $expires ){
            ksort( $data );
            $key = md5( $file . json_encode( $data ) );
            $output = $this->get_cache( $key, self::CACHEGROUP, $cache_mode );
        }

        if ( false === $output || null === $output ) {
            $twig = $this->get_twig();
            if (strlen($file)){
				$loader = $this->get_loader();
				$result = $loader->getCacheKey($file);
				do_action('timber_loader_render_file', $result);
			}
			$data = apply_filters('timber_loader_render_data', $data);
            $output = $twig->render($file, $data);
        }

        if ( false !== $output && false !== $expires && null !== $key )
            $this->set_cache( $key, $output, self::CACHEGROUP, $expires, $cache_mode );

        return $output;
	}

	function choose_template($filenames) {
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

	function template_exists($file) {
		foreach ($this->locations as $dir) {
			$look_for = trailingslashit($dir) . $file;
			if (file_exists($look_for)) {
				return true;
			}
		}
		return false;
	}

	function get_locations_theme() {
		$theme_locs = array();
		$child_loc = get_stylesheet_directory();
		$parent_loc = get_template_directory();
		if (DIRECTORY_SEPARATOR == '\\') {
			$child_loc = str_replace('/', '\\', $child_loc);
			$parent_loc = str_replace('/', '\\', $parent_loc);
		}
		$theme_locs[] = $child_loc;
		$theme_locs[] = trailingslashit($child_loc) . trailingslashit(Timber::$dirname);
		if ($child_loc != $parent_loc) {
			$theme_locs[] = $parent_loc;
			$theme_locs[] = trailingslashit($parent_loc) . trailingslashit(Timber::$dirname);
		}
		//now make sure theres a trailing slash on everything
		foreach ($theme_locs as &$tl) {
			$tl = trailingslashit($tl);
		}
		return $theme_locs;
	}

	function get_locations_user() {
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

	function get_locations_caller($caller = false) {
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

	function get_locations($caller = false) {
		//prioirty: user locations, caller (but not theme), child theme, parent theme, caller
		$locs = array();
		$locs = array_merge($locs, $this->get_locations_user());
		$locs = array_merge($locs, $this->get_locations_caller($caller));
		//remove themes from caller
		$locs = array_diff($locs, $this->get_locations_theme());
		$locs = array_merge($locs, $this->get_locations_theme());
		$locs = array_merge($locs, $this->get_locations_caller($caller));
		$locs = array_unique($locs);
		$locs = apply_filters('timber_locations', $locs);
		return $locs;
	}

	function get_loader() {
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

	function get_twig() {
		$loader_loc = trailingslashit(TIMBER_LOC) . 'Twig/lib/Twig/Autoloader.php';
		require_once($loader_loc);
		Twig_Autoloader::register();

		$loader = $this->get_loader();
		$params = array('debug' => WP_DEBUG, 'autoescape' => false);
		if (isset(Timber::$autoescape)){
			$params['autoescape'] = Timber::$autoescape;
		}
		if (Timber::$cache) {
			$params['cache'] = TIMBER_LOC . '/twig-cache';
		}
		$twig = new Twig_Environment($loader, $params);
		$twig->addExtension(new Twig_Extension_Debug());
        $twig->addExtension($this->_get_cache_extension());

		$twig = apply_filters('twig_apply_filters', $twig);
		return $twig;
	}

        private function _get_cache_extension() {
            $loader_loc = trailingslashit(TIMBER_LOC) . 'functions/cache/loader.php';
            require_once($loader_loc);
            TimberCache_Loader::register();

            $key_generator   = new \Timber\Cache\KeyGenerator();
            $cache_provider  = new \Timber\Cache\WPObjectCacheAdapter( $this );
            $cache_strategy  = new \Asm89\Twig\CacheExtension\CacheStrategy\GenerationalCacheStrategy( $cache_provider, $key_generator );
            $cache_extension = new \Asm89\Twig\CacheExtension\Extension($cache_strategy);

            return $cache_extension;
        }

    public function get_cache( $key, $group = self::CACHEGROUP, $cache_mode = self::CACHE_USE_DEFAULT ) {
        $object_cache = false;

        if ( isset( $GLOBALS[ 'wp_object_cache' ] ) && is_object( $GLOBALS[ 'wp_object_cache' ] ) )
            $object_cache = true;

        $cache_mode = $this->_get_cache_mode( $cache_mode );

        $value = false;

        $trans_key = substr($group . '_' . $key, 0, self::TRANS_KEY_LEN);
        if ( self::CACHE_TRANSIENT === $cache_mode )
            $value = get_transient( $trans_key );

		elseif ( self::CACHE_SITE_TRANSIENT === $cache_mode )
			$value = get_site_transient( $trans_key );

        elseif ( self::CACHE_OBJECT === $cache_mode && $object_cache )
			$value = wp_cache_get( $key, $group );

        return $value;
    }

    public function set_cache( $key, $value, $group = self::CACHEGROUP, $expires = 0, $cache_mode = self::CACHE_USE_DEFAULT ) {
        $object_cache = false;

        if ( isset( $GLOBALS[ 'wp_object_cache' ] ) && is_object( $GLOBALS[ 'wp_object_cache' ] ) )
            $object_cache = true;

        if ( (int) $expires < 1 )
            $expires = 0;

        $cache_mode = self::_get_cache_mode( $cache_mode );
        $trans_key = substr($group . '_' . $key, 0, self::TRANS_KEY_LEN);

        if ( self::CACHE_TRANSIENT === $cache_mode )
            set_transient( $trans_key, $value, $expires );

        elseif ( self::CACHE_SITE_TRANSIENT === $cache_mode )
            set_site_transient( $trans_key, $value, $expires );

        elseif ( self::CACHE_OBJECT === $cache_mode && $object_cache )
            wp_cache_set( $key, $value, $group, $expires );

        return $value;
    }

        private function _get_cache_mode( $cache_mode ) {
            if ( empty( $cache_mode ) || self::CACHE_USE_DEFAULT === $cache_mode )
                $cache_mode = $this->cache_mode;

            // Fallback if self::$cache_mode did not get a valid value
            if ( !in_array( $cache_mode, self::$cache_modes ) )
                $cache_mode = self::CACHE_OBJECT;

            return $cache_mode;
        }
}
