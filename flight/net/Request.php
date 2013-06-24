<?php
/**
 * Flight: An extensible micro-framework.
 *
 * @copyright   Copyright (c) 2011, Mike Cao <mike@mikecao.com>
 * @license     http://www.opensource.org/licenses/mit-license.php
 */

namespace flight\net;

use flight\util\Collection;

/**
 * The Request class represents an HTTP request. Data from
 * all the super globals $_GET, $_POST, $_COOKIE, and $_FILES
 * are stored and accessible via the Request object.
 *
 * The default request properties are:
 *   url - The URL being requested
 *   base - The parent subdirectory of the URL
 *   method - The request method (GET, POST, PUT, DELETE)
 *   referrer - The referrer URL
 *   ip - IP address of the client
 *   ajax - Whether the request is an AJAX request
 *   scheme - The server protocol (http, https)
 *   user_agent - Browser information
 *   body - Raw data from the request body
 *   type - The content type
 *   length - The content length
 *   query - Query string parameters
 *   data - Post parameters 
 *   cookies - Cookie parameters
 *   files - Uploaded files
 */
class Request {
    /**
     * Constructor.
     *
     * @param array $config Request configuration
     */
    public function __construct($config = array()) {
        // Default properties
        if (empty($config)) {
            $config = array(
                'url' => getenv('REQUEST_URI') ?: '/',
                'base' => str_replace('\\', '/', dirname(getenv('SCRIPT_NAME'))),
                'method' => getenv('REQUEST_METHOD') ?: 'GET',
                'referrer' => getenv('HTTP_REFERER') ?: '',
                'ip' => getenv('REMOTE_ADDR') ?: '',
                'ajax' => getenv('HTTP_X_REQUESTED_WITH') == 'XMLHttpRequest',
                'scheme' => getenv('SERVER_PROTOCOL') ?: 'HTTP/1.1',
                'user_agent' => getenv('HTTP_USER_AGENT') ?: '',
                'body' => file_get_contents('php://input'),
                'type' => getenv('CONTENT_TYPE') ?: '',
                'length' => getenv('CONTENT_LENGTH') ?: 0,
                'query' => new Collection($_GET),
                'data' => new Collection($_POST),
                'cookies' => new Collection($_COOKIE),
                'files' => new Collection($_FILES),
                'secure' => getenv('HTTPS') && getenv('HTTPS') != 'off',
                'accept' => getenv('HTTP_ACCEPT') ?: '',
                'proxy_ip' => $this->getProxyIpAddress()
            );
        }

        $this->init($config);
    }

    /**
     * Initialize request properties.
     *
     * @param array $properties Array of request properties
     */
    public function init($properties) {
        foreach ($properties as $name => $value) {
            $this->$name = $value;
        }

        if ($this->base != '/' && strlen($this->base) > 0 && strpos($this->url, $this->base) === 0) {
            $this->url = substr($this->url, strlen($this->base));
        }

        if (empty($this->url)) {
            $this->url = '/';
        }
        else {
            $_GET += self::parseQuery($this->url);

            $this->query->setData($_GET);
        }
    }

    /**
     * Parse query parameters from a URL.
     *
     * @param string $url URL string
     * @return array Query parameters
     */
    public static function parseQuery($url) {
        $params = array();

        $args = parse_url($url);
        if (isset($args['query'])) {
            parse_str($args['query'], $params);
        }

        return $params;
    }

    /**
     * Gets the real remote IP address.
     *
     * @return string IP address
     */
    private function getProxyIpAddress() {
        static $forwarded = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED'
        );

        $flags = \FILTER_FLAG_NO_PRIV_RANGE | \FILTER_FLAG_NO_RES_RANGE;

        foreach ($forwarded as $key) {
            if (array_key_exists($key, $_SERVER)) {
                sscanf($_SERVER[$key], '%[^,]', $ip);
                if (filter_var($ip, \FILTER_VALIDATE_IP, $flags) !== false) {
                    return $ip;
                }
            }
        }

        return '';
    }
}
?>
