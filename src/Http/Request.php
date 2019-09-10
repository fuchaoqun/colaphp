<?php

namespace Cola\Http;


class Request
{
    public static function getWithDefault($data, $key, $default)
    {
        if (null === $key) {
            return $data;
        }

        return (isset($data[$key])) ? $data[$key] : $default;
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
        return self::getWithDefault($_GET, $key, $default);
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
        return self::getWithDefault($_POST, $key, $default);
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
        return self::getWithDefault($_COOKIE, $key, $default);
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
        return self::getWithDefault($_SERVER, $key, $default);
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
        return self::getWithDefault($_ENV, $key, $default);
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
        return self::getWithDefault($_SESSION, $key, $default);
    }

    /**
     * Return the value of the given HTTP header. Pass the header name as the
     * plain, HTTP-specified header name. Ex.: Ask for 'Accept' to get the
     * Accept header, 'Accept-Encoding' to get the Accept-Encoding header.
     *
     * @param string $header HTTP header name
     * @return string|false HTTP header value, or false if not found
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
     * Was the request made by POST?
     *
     * @return boolean
     */
    public static function isPost()
    {
        return 'POST' === self::server('REQUEST_METHOD');
    }

    /**
     * Was the request made by GET?
     *
     * @return boolean
     */
    public static function isGet()
    {
        return 'GET' === self::server('REQUEST_METHOD');
    }

    /**
     * Was the request made by PUT?
     *
     * @return boolean
     */
    public static function isPut()
    {
        return 'PUT' === self::server('REQUEST_METHOD');
    }

    /**
     * Was the request made by DELETE?
     *
     * @return boolean
     */
    public static function isDelete()
    {
        return 'DELETE' === self::server('REQUEST_METHOD');
    }

    /**
     * Was the request made by HEAD?
     *
     * @return boolean
     */
    public static function isHead()
    {
        return 'HEAD' === self::server('REQUEST_METHOD');
    }

    /**
     * Was the request made by OPTIONS?
     *
     * @return boolean
     */
    public static function isOptions()
    {
        return 'OPTIONS' === self::server('REQUEST_METHOD');
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
        return 'XMLHttpRequest' === self::header('X_REQUESTED_WITH');
    }

    /**
     * Is this a Flash request?
     *
     * @return bool
     */
    public static function isFlashRequest()
    {
        return 'Shockwave Flash' === self::header('USER_AGENT');
    }

    /**
     * Is https secure request
     *
     * @return boolean
     */
    public static function isSecure()
    {
        return 'https' === self::scheme();
    }

    /**
     * Check if search engine spider
     *
     * @param null $ua
     * @return boolean
     */
    public static function isSpider($ua = null)
    {
        is_null($ua) && $ua = $_SERVER['HTTP_USER_AGENT'];
        $ua = strtolower($ua);
        $spiders = array('bot', 'crawl', 'spider' ,'slurp', 'sohu-search', 'lycos', 'robozilla');
        foreach ($spiders as $spider) {
            if (false !== strpos($ua, $spider)) return true;
        }
        return false;
    }

    /**
     * Get the request URI scheme
     *
     * @return string
     */
    public static function scheme()
    {
        return ('on' === self::server('HTTPS')) ? 'https' : 'http';
    }

    /**
     * Get Client Ip
     *
     * @param string $default
     * @return string
     */
    public static function getClientIp($default = '0.0.0.0')
    {
        $keys = array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR');

        foreach ($keys as $key) {
            if (empty($_SERVER[$key])) continue;
		    $ips = explode(',', $_SERVER[$key], 1);
		    $ip = $ips[0];
		    $l  = ip2long($ip);
		    if ((false !== $l) && ($ip === long2ip($l))) return $ip;
		}

        return $default;
    }

    /**
     * Return current url
     *
     * @return string
     */
    public static function getCurrentUrl()
    {
        $url = 'http';

        if ('on' == self::server('HTTPS')) $url .= 's';

        $url .= "://" . self::server('HTTP_HOST');

        $port = self::server('SERVER_PORT');
        if (80 != $port) $url .= ":{$port}";

        return $url . self::server('REQUEST_URI');
    }

    /**
     * Format uploaded files
     */
    public static function getUploadedFiles()
    {
        $files = [];

        $keys = ['name', 'type', 'tmp_name', 'error', 'size'];

        foreach ($_FILES as $field => $data) {
            if (empty($data['name'])) continue;
            if (is_string($data['name'])) {
                $pathInfo = pathinfo($data['name']);
                $ext = isset($pathInfo['extension']) ? strtolower($pathInfo['extension']) : '';
                $files[] = $data + ['field' => $field, 'extension' => $ext];
                continue;
            }

            if (!is_array($data['name'])) continue;
            $cnt = count($data['name']);

            for ($i = 0; $i < $cnt; $i++) {
                if (empty($data['name'][$i])) continue;
                $pathInfo = pathinfo($data['name'][$i]);
                $ext = isset($pathInfo['extension']) ? strtolower($pathInfo['extension']) : '';
                $row = ['field' => $field, 'extension' => $ext];
                foreach ($keys as $key) {
                    isset($data[$key][$i]) && ($row[$key] = $data[$key][$i]);
                }
                $files[] = $row;
            }
        }

        return $files;
    }

    public static function isSafeUrl($url, $safeDomains)
    {
        $info = parse_url($url);
        if (empty($info['host'])) {
            return false;
        }
        $tmp = explode('.', $info['host']);
        $cnt = count($tmp);
        if (2 > $cnt) {
            return false;
        }
        $domain = "{$tmp[$cnt - 2]}.{$tmp[$cnt - 1]}";
        return in_array($domain, $safeDomains);
    }
}