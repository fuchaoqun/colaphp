<?php
/**
 *
 */

class Cola_Ext_Http
{
    public $url;

    /**
     * Default Options
     *
     * @var array
     */
    public $opts = array(
        CURLOPT_TIMEOUT => 15,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
    );

    protected $_maps = array(
        'timeout' => CURLOPT_TIMEOUT,
        'ssl'     => CURLOPT_SSL_VERIFYPEER,
        'headers' => CURLOPT_HTTPHEADER
    );

    public $rps;
    public $info = array();

    public function __construct($url, $opts = array())
    {
        $this->url = $url;
        foreach ($opts as $key => $val) {
            if (isset($this->_maps[$key])) {
                $this->opts[$this->_maps[$key]] = $val;
            } else {
                $this->opts[$key] = $val;
            }
        }
    }

    public static function getUrl($url, $params = array(), $opts = array())
    {
        $http = new self($url, $opts);
        return $http->get($params);
    }

    public static function postUrl($url, $data = array(), $opts = array())
    {
        $http = new self($url, $opts);
        return $http->post($data);
    }

    /**
     * HTTP GET
     *
     * @param string $url
     * @param array $data
     * @param array $params
     * @return string
     */
    public function get($params = array())
    {
        $url = $this->url;

        if ($params) {
            $queryStr = http_build_query($params);
            $url .= ((false === strpos($url, '?')) ? "?{$queryStr}" : "&{$queryStr}");
        }

        return $this->request($url, $this->opts);
    }

    /**
     * HTTP POST
     *
     * @param string $url
     * @param array $data
     * @param array $params
     * @return string
     */
    public function post($data)
    {
        $opts = $this->opts;
        $opts[CURLOPT_POST] = true;

        if ($data && is_array($data)) {
            $data = http_build_query($data);
        }

        $opts[CURLOPT_POSTFIELDS] = $data;

        return $this->request($this->url, $opts);
    }

    /**
     * HTTP request
     *
     * @param string $uri
     * @param array $opts
     * @return string or throw Exception
     */
    public function request($url, $opts)
    {
        if (!function_exists('curl_init')) {
            throw new Cola_Exception('Can not find curl extension');
        }

        $curl = curl_init();
        $opts[CURLOPT_URL] = $url;
        curl_setopt_array($curl, $opts);
        $this->response = curl_exec($curl);

        $errno = curl_errno($curl);
        $error = curl_error($curl);

        $this->info = curl_getinfo($curl) + array('errno' => $errno, 'error' => $error);

        if (0 !== $errno) {
            throw new Cola_Exception($error, $errno);
        }

        curl_close ($curl);
        return $this->response;
    }
}
