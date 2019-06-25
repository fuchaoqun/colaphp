<?php

namespace Cola\Http;

use Exception;

class Client
{
    protected $_request;

    protected $_info;

    public function __construct($request)
    {
        $this->_request = array_merge_recursive(
            [
                'params' => [],
                'data' => [],
                'opts' => [
                    CURLOPT_TIMEOUT => 15,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                ],
            ],
            $request
        );
    }

    public function setHeaders($headers)
    {
        $this->_request['opts'][CURLOPT_HTTPHEADER] = $headers;
    }

    public static function genUrl($url, $params = [])
    {
        if ($params) {
            $queryStr = http_build_query($params);
            $url .= ((false === strpos($url, '?')) ? "?{$queryStr}" : "&{$queryStr}");
        }

        return $url;
    }

    /**
     * @param $url
     * @param array $params
     * @param array $opts
     * @return string
     * @throws Exception
     */
    public static function get($url, $params = [], $opts = [])
    {
        $http = new self(['url' => $url, 'params' => $params, 'opts' => $opts]);
        return $http->sendRequest();
    }

    /**
     * @param $url
     * @param array $data
     * @param array $opts
     * @return string
     * @throws Exception
     */
    public static function post($url, $data = [], $opts = [])
    {
        $http = new self(['url' => $url, 'data' => $data, 'opts' => $opts]);
        return $http->sendRequest();
    }

    /**
     * HTTP request
     *
     * @return string or throw Exception
     * @throws Exception
     */
    public function sendRequest()
    {
        if (!function_exists('curl_init')) {
            throw new Exception('Can not find curl extension');
        }

        $this->_request['opts'][CURLOPT_URL] = self::genUrl($this->_request['url'], $this->_request['params']);

        if ($this->_request['data']) {
            $this->_request['opts'][CURLOPT_POST] = true;
            if (is_array($this->_request['data'])) {
                $this->_request['opts'][CURLOPT_POSTFIELDS] = http_build_query($this->_request['data']);
            } else {
                $this->_request['opts'][CURLOPT_POSTFIELDS] = $this->_request['data'];
            }
        }

        $curl = curl_init();
        curl_setopt_array($curl, $this->_request['opts']);
        $response = curl_exec($curl);

        $this->_info = curl_getinfo($curl);

        $errno = curl_errno($curl);
        if (0 !== $errno) {
            throw new Exception(curl_error($curl), $errno);
        }

        curl_close ($curl);
        return $response;
    }

    /**
     * @return array
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * @return mixed
     */
    public function getInfo()
    {
        return $this->_info;
    }
}
