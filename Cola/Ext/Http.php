<?php
/**
 *
 */

class Cola_Com_Http
{
    protected static $_http_context_option_keys = array('method', 'header', 'user_agent', 'content','proxy', 'request_fulluri',
                                                        'max_redirects', 'protocol_version', 'timeout', 'ignore_errors');

    protected static $_responseHeader;
    /**
     * Http get request
     *
     * @param string $uri
     * @param array $data
     * @param string $timeOut
     * @param string $host
     * @return mixed string if ok or false when error
     */
    public static function get($uri, $data = null, $config = null)
    {
        $options = array('http' => array('method' => 'GET'));

        if ($config) $options['http'] += self::_buildOptions($config);

        $context = stream_context_create($options);

        if (!is_null($data))  $uri .= '?' . http_build_query($data);

        return self::request($uri, $context);
    }

    /**
     * Http post request
     *
     * @param string $uri
     * @param array $data
     * @param float $timeOut
     * @param string $host
     * @return mixed string if ok or false when error
     */
    public static function post($uri, $data, $config = null)
    {
        $options = array('http' => array('method' => 'POST'));

        if ($config) $options['http'] += self::_buildOptions($config);

        $options['http']['content'] = http_build_query($data);

        $context = stream_context_create($options);

        return self::request($uri, $context);
    }

    /**
     * Http request
     *
     * @param string $uri
     * @param array $opts @see http://cn2.php.net/manual/en/context.http.php
     * @return mixed string if ok or false when error
     */
    public static function request($uri, $context)
    {
        $data = @file_get_contents($uri, null, $context);

        self::$_responseHeader = $http_response_header;

        return $data;
    }

    /**
     * Get response header
     *
     * @return array
     */
    public static function responseHeader()
    {
        return self::$_responseHeader;
    }

    /**
     * Build options
     *
     * @param array $config
     */
    protected static function _buildOptions($config)
    {
        $options = array('timeout' => 3, 'header' => '');
        foreach ($config as $key => $value) {
            if (in_array($key, self::$_http_context_option_keys)) {
                $options[$key] = $value;
                continue;
            }
            // http headers
            if ('cookie' == strtolower($key) && is_array($value)) {
                $cookie = '';
                foreach ($value as $k => $v) {
                    $cookie .= "$k=$v;";
                }
                $options['header'] .= "Cookie: $cookie\r\n";
            } else {
                $options['header'] .= ucfirst($key) . ": $value\r\n";
            }
        }
        if ('' == $options['header']) unset($options['header']);
        return $options;
    }
}