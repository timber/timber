<?php

namespace Timber;

/**
 * Class URLHelper
 *
 * @api
 */
class URLHelper
{
    /**
     * Get the current URL of the page
     *
     * @api
     * @return string
     */
    public static function get_current_url()
    {
        $page_url = self::get_scheme() . '://';
        if (isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] && $_SERVER["SERVER_PORT"] != "80" && $_SERVER["SERVER_PORT"] != "443") {
            $page_url .= self::get_host() . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        } else {
            $page_url .= self::get_host() . $_SERVER['REQUEST_URI'];
        }
        return $page_url;
    }

    /**
     * Get url scheme
     *
     * @api
     * @return string
     */
    public static function get_scheme()
    {
        return \is_ssl() ? 'https' : 'http';
    }

    /**
     * Check to see if the URL begins with the string in question
     * Because it's a URL we don't care about protocol (HTTP vs HTTPS)
     * Or case (so it's cAsE iNsEnSiTiVe)
     *
     * @api
     * @return boolean
     */
    public static function starts_with($haystack, $starts_with)
    {
        $haystack = \str_replace('https', 'http', \strtolower($haystack));
        $starts_with = \str_replace('https', 'http', \strtolower($starts_with));
        if (0 === \strpos($haystack, $starts_with)) {
            return true;
        }
        return false;
    }

    /**
     * @api
     * @param string $url
     * @return bool
     */
    public static function is_url($url)
    {
        if (!\is_string($url)) {
            return false;
        }
        $url = \strtolower($url);
        if (\strstr($url, '://')) {
            return true;
        }
        return false;
    }

    /**
     * @api
     * @return string
     */
    public static function get_path_base()
    {
        $struc = \get_option('permalink_structure');
        $struc = \explode('/', $struc);
        $p = '/';
        foreach ($struc as $s) {
            if (!\strstr($s, '%') && \strlen($s)) {
                $p .= $s . '/';
            }
        }
        return $p;
    }

    /**
     * @api
     * @param string $url
     * @param bool   $force
     * @return string
     */
    public static function get_rel_url($url, $force = false)
    {
        $url_info = \parse_url($url);
        if (isset($url_info['host']) && $url_info['host'] != self::get_host() && !$force) {
            return $url;
        }
        $link = '';
        if (isset($url_info['path'])) {
            $link = $url_info['path'];
        }
        if (isset($url_info['query']) && \strlen($url_info['query'])) {
            $link .= '?' . $url_info['query'];
        }
        if (isset($url_info['fragment']) && \strlen($url_info['fragment'])) {
            $link .= '#' . $url_info['fragment'];
        }
        $link = self::remove_double_slashes($link);
        return $link;
    }

    /**
     * Some setups like HTTP_HOST, some like SERVER_NAME, it's complicated
     *
     * @api
     * @link http://stackoverflow.com/questions/2297403/http-host-vs-server-name
     *
     * @return string the HTTP_HOST or SERVER_NAME
     */
    public static function get_host()
    {
        if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST']) {
            return $_SERVER['HTTP_HOST'];
        }
        if (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME']) {
            return $_SERVER['SERVER_NAME'];
        }
        return '';
    }

    /**
     * Checks whether a URL or domain is local.
     *
     * True if `$url` has a host name matching the server’s host name. False if
     * a relative URL or if it’s a subdomain.
     *
     * @api
     *
     * @param string $url URL to check.
     * @return bool
     */
    public static function is_local(string $url): bool
    {
        $host = \wp_parse_url($url, \PHP_URL_HOST);
        if (null === $host || false === $host) {
            $host = $url;
        }

        $wp_host = self::get_host();

        return $wp_host && $wp_host === $host;
    }

    /**
     * @api
     *
     * @param string $src
     * @return string
     */
    public static function get_full_path($src)
    {
        $root = ABSPATH;
        $old_root_path = $root . $src;
        $old_root_path = \str_replace('//', '/', $old_root_path);
        return $old_root_path;
    }

    /**
     * Translates a URL to a filesystem path
     *
     * Takes a url and figures out its filesystem location.
     *
     * NOTE: Not fool-proof, makes a lot of assumptions about the file path
     * matching the URL path
     *
     * @api
     *
     * @param string $url The URL to translate to a filesystem path
     * @return string The filesystem path derived from the URL
     */
    public static function url_to_file_system($url)
    {
        $url_parts = \parse_url($url);

        /**
         * Filters the path of a parsed URL.
         *
         * You can use this filter to alter the returned file system path.
         * This filter is used by the WPML integration.

         *
         * @see \Timber\URLHelper::url_to_file_system()
         * @since 1.3.2
         *
         * @param string $path The current translated path
         */
        $url_parts['path'] = \apply_filters('timber/url_helper/url_to_file_system/path', $url_parts['path']);

        /**
         * Filters the path of a parsed URL.
         *
         * @deprecated 2.0.0, use `timber/url_helper/url_to_file_system/path`
         */
        $url_parts['path'] = \apply_filters_deprecated(
            'timber/URLHelper/url_to_file_system/path',
            [$url_parts['path']],
            '2.0.0',
            'timber/url_helper/url_to_file_system/path'
        );

        $path = ABSPATH . $url_parts['path'];
        $path = \str_replace('//', '/', $path);
        return $path;
    }

    /**
     * Translates a filesystem path to a URL
     *
     * Takes a filesystem path and figures out its URL location.
     *
     * @api
     * @param string $fs The filesystem path to translate to a URL
     * @return string    The URL derived from the filesystem path
     */
    public static function file_system_to_url($fs)
    {
        $relative_path = self::get_rel_path($fs);
        $url = \home_url('/' . $relative_path);

        /**
         * Filters the URL in URLHelper::file_system_to_url
         *
         * You can use this filter to alter the returned URL.
         * This filter is used by the WPML integration.
         *
         * @see \Timber\URLHelper::file_system_to_url()
         * @since 1.3.2
         *
         * @param string $url The current translated url
         */
        $url = \apply_filters('timber/url_helper/file_system_to_url', $url);

        /**
         * Filters the URL in URLHelper::file_system_to_url
         *
         * You can use this filter to alter the returned URL.
         * This filter is used by the WPML integration.
         *
         * @param string $url The current url
         * @deprecated 2.0.0, use `timber/url_helper/file_system_to_url`
         */
        $url = \apply_filters_deprecated(
            'timber/URLHelper/file_system_to_url',
            [$url],
            '2.0.0',
            'timber/url_helper/file_system_to_url'
        );
        return $url;
    }

    /**
     * Get the path to the content directory relative to the site.
     * This replaces the WP_CONTENT_SUBDIR constant
     *
     * @api
     *
     * @return string (ex: /wp-content or /content)
     */
    public static function get_content_subdir()
    {
        $home_url = \get_home_url();

        /**
         * Filters the home URL that is used to get the path relative to the content directory.
         *
         * @since 1.3.2
         *
         * @param string $home_url The URL to use as the base for getting the content subdirectory.
         *                         Default value of `home_url()`.
         */
        $home_url = \apply_filters('timber/url_helper/get_content_subdir/home_url', $home_url);

        /**
         * Filters the home URL that is used to get the path relative to the content directory.
         *
         * @deprecated 2.0.0, use `timber/url_helper/get_content_subdir/home_url`
         */
        $home_url = \apply_filters_deprecated(
            'timber/URLHelper/get_content_subdir/home_url',
            [$home_url],
            '2.0.0',
            'timber/url_helper/get_content_subdir/home_url'
        );

        return \str_replace($home_url, '', WP_CONTENT_URL);
    }

    /**
     * @api
     * @param string $src
     * @return string
     */
    public static function get_rel_path($src)
    {
        if (\str_contains($src, ABSPATH)) {
            return \str_replace(ABSPATH, '', $src);
        }
        // its outside the WordPress directory, alternate setups:
        $src = \str_replace(WP_CONTENT_DIR, '', $src);
        return self::get_content_subdir() . $src;
    }

    /**
     * Look for accidental slashes in a URL and remove them
     *
     * @api
     * @param  string $url to process (ex: http://nytimes.com//news/article.html)
     * @return string the result (ex: http://nytimes.com/news/article.html)
     */
    public static function remove_double_slashes($url)
    {
        $url = \str_replace('//', '/', $url);

        /**
         * Filters the schemes that are excluded for double slash removal.
         *
         * If an url start with one of the schemes in the whitelist,
         * that scheme will be excluded from the double slash removal.
         *
         * @since 1.16.0
         *
         * @param array $schemes_whitelist the schemes that are excluded for double slash removal.
         */
        $schemes_whitelist = \apply_filters('timber/url/schemes-whitelist', ['http', 'https', 's3', 'gs']);
        foreach ($schemes_whitelist as $scheme) {
            if (\strstr($url, $scheme . ':') && !\strstr($url, $scheme . '://')) {
                $url = \str_replace($scheme . ':/', $scheme . '://', $url);
            }
        }
        return $url;
    }

    /**
     * Add something to the start of the path in an URL
     *
     * @api
     * @param  string $url a URL that you want to manipulate (ex: 'https://nytimes.com/news/article.html').
     * @param  string $path the path you want to insert ('/2017').
     * @return string the result (ex 'https://nytimes.com/2017/news/article.html')
     */
    public static function prepend_to_url($url, $path)
    {
        if (\strstr(\strtolower($url), 'http')) {
            $url_parts = \wp_parse_url($url);
            $url = $url_parts['scheme'] . '://' . $url_parts['host'];

            if (isset($url_parts['port'])) {
                $url .= ':' . $url_parts['port'];
            }

            $url .= $path;

            if (isset($url_parts['path'])) {
                $url .= $url_parts['path'];
            }
            if (isset($url_parts['query'])) {
                $url .= '?' . $url_parts['query'];
            }
            if (isset($url_parts['fragment'])) {
                $url .= '#' . $url_parts['fragment'];
            }
        } else {
            $url = $url . $path;
        }
        return self::remove_double_slashes($url);
    }

    /**
     * Add slash (if not already present) to a path
     *
     * @api
     * @param  string $path to process.
     * @return string
     */
    public static function preslashit($path)
    {
        if (\strpos($path, '/') !== 0) {
            $path = '/' . $path;
        }
        return $path;
    }

    /**
     * Remove slashes (if found) from a path
     *
     * @api
     * @param  string $path to process.
     * @return string
     */
    public static function unpreslashit($path)
    {
        return \ltrim($path, '/');
    }

    /**
     * This will evaluate wheter a URL is at an aboslute location (like http://example.org/whatever)
     *
     * @param string $path
     * @return boolean true if $path is an absolute url, false if relative.
     */
    public static function is_absolute($path)
    {
        return (bool) (\strstr($path, 'http'));
    }

    /**
     * This function is slightly different from the one below in the case of:
     * an image hosted on the same domain BUT on a different site than the
     * WordPress install will be reported as external content.
     *
     * @api
     * @param  string $url a URL to evaluate against
     * @return boolean if $url points to an external location returns true
     */
    public static function is_external_content($url)
    {
        $is_external = self::is_absolute($url) && !self::is_internal_content($url);

        return $is_external;
    }

    /**
     * @param string $url
     */
    private static function is_internal_content($url)
    {
        // using content_url() instead of site_url or home_url is IMPORTANT
        // otherwise you run into errors with sites that:
        // 1. use WPML plugin
        // 2. or redefine content directory.
        $is_content_url = \strstr($url, \content_url());

        // this case covers when the upload directory has been redefined.
        $upload_dir = \wp_upload_dir();
        $is_upload_url = \strstr($url, $upload_dir['baseurl']);

        return $is_content_url || $is_upload_url;
    }

    /**
     * Checks whether a URL or domain is external.
     *
     * True if the `$url` host name does not match the server’s host name.
     * Otherwise, false.
     *
     * @api
     * @param  string $url URL to evalute.
     * @return bool
     */
    public static function is_external(string $url): bool
    {
        $has_scheme = \str_starts_with($url, '//') || \wp_parse_url($url, \PHP_URL_SCHEME);

        if ($has_scheme) {
            return !self::is_local($url);
        }

        // Check with added scheme.
        return !self::is_local('//' . $url);
    }

    /**
     * Pass links through untrailingslashit unless they are a single /
     *
     * @api
     * @param  string $link the URL to process.
     * @return string
     */
    public static function remove_trailing_slash($link)
    {
        if ($link != '/') {
            $link = \untrailingslashit($link);
        }
        return $link;
    }

    /**
     * Removes the subcomponent of a URL regardless of protocol
     *
     * @api
     * @since  1.3.3
     * @author jarednova
     * @param string $haystack ex: http://example.org/wp-content/uploads/dog.jpg
     * @param string $needle ex: http://example.org/wp-content
     * @return string
     */
    public static function remove_url_component($haystack, $needle)
    {
        $haystack = \str_replace($needle, '', $haystack);
        $needle = self::swap_protocol($needle);
        return \str_replace($needle, '', $haystack);
    }

    /**
     * Swaps whatever protocol of a URL is sent. http becomes https and vice versa
     *
     * @api
     * @since  1.3.3
     * @author jarednova
     *
     * @param  string $url ex: http://example.org/wp-content/uploads/dog.jpg.
     * @return string ex: https://example.org/wp-content/uploads/dog.jpg
     */
    public static function swap_protocol($url)
    {
        if (\stristr($url, 'http:')) {
            return \str_replace('http:', 'https:', $url);
        }
        if (\stristr($url, 'https:')) {
            return \str_replace('https:', 'http:', $url);
        }
        return $url;
    }

    /**
     * Pass links through user_trailingslashit handling query strings properly
     *
     * @api
     * @param  string $link the URL to process.
     * @return string
     */
    public static function user_trailingslashit($link)
    {
        $link_parts = \wp_parse_url($link);

        if (!$link_parts) {
            return $link;
        }

        if (isset($link_parts['path']) && '/' !== $link_parts['path']) {
            $new_path = \user_trailingslashit($link_parts['path']);
            if ($new_path !== $link_parts['path']) {
                $link = \str_replace($link_parts['path'], $new_path, $link);
            }
        }
        return $link;
    }

    /**
     * Returns the url path parameters, or a single parameter if given an index.
     * Returns false if given a non-existent index.
     *
     * @example
     * ```php
     * // Given a $_SERVER["REQUEST_URI"] of:
     * // http://example.org/blog/post/news/2014/whatever
     *
     * $params = URLHelper::get_params();
     * // => ["blog", "post", "news", "2014", "whatever"]
     *
     * $third = URLHelper::get_params(2);
     * // => "news"
     *
     * // get_params() supports negative indices:
     * $last = URLHelper::get_params(-1);
     * // => "whatever"
     *
     * $nada = URLHelper::get_params(99);
     * // => false
     * ```
     *
     * @api
     * @param boolean|int $i the position of the parameter to grab.
     * @return array|string|false
     */
    public static function get_params($i = false)
    {
        $uri = \trim($_SERVER['REQUEST_URI']);
        $params = \array_values(\array_filter(\explode('/', $uri)));

        if (false === $i) {
            return $params;
        }

        // Support negative indices.
        if ($i < 0) {
            $i = \count($params) + $i;
        }

        return $params[$i] ?? false;
    }

    /**
     * Secures an URL based on the current environment.
     *
     * @param  string $url The URL to evaluate.
     *
     * @return string An URL with or without http/https, depending on what’s appropriate for server.
     */
    public static function maybe_secure_url($url)
    {
        if (\is_ssl() && \strpos($url, 'https') !== 0 && \strpos($url, 'http') === 0) {
            $url = 'https' . \substr($url, \strlen('http'));
        }

        return $url;
    }
}
