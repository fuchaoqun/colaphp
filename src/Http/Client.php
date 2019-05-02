<?php

namespace Cola\Http;

class Client
{
    public $request;

    public $response;

    public $info;

    public $defaultOpts = [
        CURLOPT_TIMEOUT => 15,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
    ];

    public function __construct($request)
    {
        $this->request = array_merge_recursive(
            [
                'params' => [],
                'data' => [],
                'opts' => $this->defaultOpts,
            ],
            $request
        );
    }

    public function setHeaders($headers)
    {
        $this->request['opts'][CURLOPT_HTTPHEADER] = $headers;
    }

    public static function genUrl($url, $params = [])
    {
        if ($params) {
            $queryStr = http_build_query($params);
            $url .= ((false === strpos($url, '?')) ? "?{$queryStr}" : "&{$queryStr}");
        }

        return $url;
    }

    public static function get($url, $params = [], $opts = [])
    {
        $http = new self(['url' => $url, 'params' => $params, 'opts' => $opts]);
        return $http->sendRequest();
    }

    public static function post($url, $data = [], $opts = [])
    {
        $http = new self(['url' => $url, 'data' => $data, 'opts' => $opts]);
        return $http->sendRequest();
    }

    /**
     * HTTP request
     *
     * @return string or throw Exception
     * @throws \Exception
     */
    public function sendRequest()
    {
        if (!function_exists('curl_init')) {
            throw new \Exception('Can not find curl extension');
        }

        $this->request['opts'][CURLOPT_URL] = self::genUrl($this->request['url'], $this->request['params']);

        if ($this->request['data']) {
            $this->request['opts'][CURLOPT_POST] = true;
            if (is_array($this->request['data'])) {
                $this->request['opts'][CURLOPT_POSTFIELDS] = http_build_query($this->request['data']);
            } else {
                $this->request['opts'][CURLOPT_POSTFIELDS] = $this->request['data'];
            }
        }

        $curl = curl_init();
        curl_setopt_array($curl, $this->request['opts']);
        $this->response = curl_exec($curl);

        $this->info = curl_getinfo($curl);

        $errno = curl_errno($curl);
        if (0 !== $errno) {
            var_dump($errno);
            throw new \Exception(curl_error($curl), $errno);
        }

        curl_close ($curl);
        return $this->response;
    }
}
