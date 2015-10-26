<?php
/**
 *
 */

class Cola_Ext_Http
{
    public $url;
    public $data;

    /**
     * Default Options
     *
     * @var array
     */
    public $opts = array(
        CURLOPT_TIMEOUT => 15,
        CURLOPT_RETURNTRANSFER => true,
    );

    protected $maps = array(
        'timeout' => CURLOPT_TIMEOUT,
        'ssl'     => CURLOPT_SSL_VERIFYPEER,
        'headers' => CURLOPT_HTTPHEADER
    );

    public $response;
    public $info = array();



    public function __construct($url, $data = array(), $opts = array())
    {
        $this->url = $url;
        $this->data = $data;
        foreach ($opts as $key => $val) {
            if (isset($this->maps[$key])) {
                $this->opts[$this->maps[$key]] = $val;
            } else {
                $this->opts[$key] = $val;
            }
        }
    }

    /**
     * HTTP GET
     *
     * @param string $url
     * @param array $data
     * @param array $params
     * @return string
     */
    public function get()
    {
        $url = $this->url;

        if ($this->data) {
            $queryStr = http_build_query($this->data);
            $url .= "?{$queryStr}";
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
    public function post()
    {
        $opts = $this->opts;
        $opts[CURLOPT_POST] = true;

        if (!empty($this->data)) {
            $opts[CURLOPT_POSTFIELDS] = http_build_query($this->data);
        }

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