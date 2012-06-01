<?php
/**
 *
 */
class Cola_Request
{
    /**
     * Retrieve a member of the pathinfo params
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function param($key = null, $default = array())
    {
        $params = (array)Cola::reg('_params');

        if (null === $key) return $params;

        return (isset($params[$key]) ? $params[$key] : $default);
    }

    /**
     * Retrieve a member of the $_GET superglobal
     *
     * If no $key is passed, returns the entire $_GET array.
     *
     * @param string $key
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public static function get($key = null, $default = null)
    {
        if (null === $key) {
            return $_GET;
        }

        return (isset($_GET[$key])) ? $_GET[$key] : $default;
    }

    /**
     * Retrieve a member of the $_POST superglobal
     *
     * If no $key is passed, returns the entire $_POST array.
     *
     * @param string $key
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public static function post($key = null, $default = null)
    {
        if (null === $key) {
            return $_POST;
        }

        return (isset($_POST[$key])) ? $_POST[$key] : $default;
    }

    /**
     * Retrieve a member of the $_COOKIE superglobal
     *
     * If no $key is passed, returns the entire $_COOKIE array.
     *
     * @param string $key
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public static function cookie($key = null, $default = null)
    {
        if (null === $key) {
            return $_COOKIE;
        }

        return (isset($_COOKIE[$key])) ? $_COOKIE[$key] : $default;
    }

    /**
     * Retrieve a member of the $_SERVER superglobal
     *
     * If no $key is passed, returns the entire $_SERVER array.
     *
     * @param string $key
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public static function server($key = null, $default = null)
    {
        if (null === $key) {
            return $_SERVER;
        }

        return (isset($_SERVER[$key])) ? $_SERVER[$key] : $default;
    }

    /**
     * Retrieve a member of the $_ENV superglobal
     *
     * If no $key is passed, returns the entire $_ENV array.
     *
     * @param string $key
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public static function env($key = null, $default = null)
    {
        if (null === $key) {
            return $_ENV;
        }

        return (isset($_ENV[$key])) ? $_ENV[$key] : $default;
    }

    /**
     * Get session
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function session($key = null, $default = null)
    {
        isset($_SESSION) || session_start();
        if (null === $key) {
            return $_SESSION;
        }

        return (isset($_SESSION[$key])) ? $_SESSION[$key] : $default;
    }

    /**
     * Return the value of the given HTTP header. Pass the header name as the
     * plain, HTTP-specified header name. Ex.: Ask for 'Accept' to get the
     * Accept header, 'Accept-Encoding' to get the Accept-Encoding header.
     *
     * @param string $header HTTP header name
     * @return string|false HTTP header value, or false if not found
     * @throws Exception
     */
    public static function header($header)
    {
        if (empty($header)) {
            return null;
        }

        // Try to get it from the $_SERVER array first
        $temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        if (!empty($_SERVER[$temp])) {
            return $_SERVER[$temp];
        }

        // This seems to be the only way to get the Authorization header on
        // Apache
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (!empty($headers[$header])) {
                return $headers[$header];
            }
        }
        return false;
    }

    /**
     * Return current url
     *
     * @return string
     */
    public static function currentUrl()
    {
        $url = 'http';

        if ('on' == self::server('HTTPS')) $url .= 's';

        $url .= "://" . self::server('SERVER_NAME');

        $port = self::server('SERVER_PORT');
        if (80 != $port) $url .= ":{$port}";

        return $url . self::server('REQUEST_URI');
    }
    /**
     * Was the request made by POST?
     *
     * @return boolean
     */
    public static function isPost()
    {
        if ('POST' == self::server('REQUEST_METHOD')) {
            return true;
        }

        return false;
    }

    /**
     * Was the request made by GET?
     *
     * @return boolean
     */
    public static function isGet()
    {
        if ('GET' == self::server('REQUEST_METHOD')) {
            return true;
        }

        return false;
    }

    /**
     * Was the request made by PUT?
     *
     * @return boolean
     */
    public static function isPut()
    {
        if ('PUT' == self::server('REQUEST_METHOD')) {
            return true;
        }

        return false;
    }

    /**
     * Was the request made by DELETE?
     *
     * @return boolean
     */
    public static function isDelete()
    {
        if ('DELETE' == self::server('REQUEST_METHOD')) {
            return true;
        }

        return false;
    }

    /**
     * Was the request made by HEAD?
     *
     * @return boolean
     */
    public static function isHead()
    {
        if ('HEAD' == self::server('REQUEST_METHOD')) {
            return true;
        }

        return false;
    }

    /**
     * Was the request made by OPTIONS?
     *
     * @return boolean
     */
    public static function isOptions()
    {
        if ('OPTIONS' == self::server('REQUEST_METHOD')) {
            return true;
        }

        return false;
    }

    /**
     * Is the request a Javascript XMLHttpRequest?
     *
     * Should work with Prototype/Script.aculo.us, possibly others.
     *
     * @return boolean
     */
    public static function isAjax()
    {
        return ('XMLHttpRequest' == self::header('X_REQUESTED_WITH'));
    }

    /**
     * Is this a Flash request?
     *
     * @return bool
     */
    public static function isFlashRequest()
    {
        return ('Shockwave Flash' == self::header('USER_AGENT'));
    }

    /**
     * Is https secure request
     *
     * @return boolean
     */
    public static function isSecure()
    {
        return ('https' === self::scheme());
    }

    /**
     * Get the request URI scheme
     *
     * @return string
     */
    public static function scheme()
    {
        return ('on' == self::server('HTTPS')) ? 'https' : 'http';
    }

    /**
     * Get Client Ip
     *
     * @param string $default
     * @return string
     */
    public static function clientIp($default = '0.0.0.0')
    {
        $keys = array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR');

        foreach ($keys as $key) {
            if (empty($_SERVER[$key])) continue;
		    $ips = explode(',', $_SERVER[$key], 1);
		    $ip = $ips[0];
		    if (false != ip2long($ip) && long2ip(ip2long($ip) === $ip)) return $ips[0];
		}

        return $default;
    }
}