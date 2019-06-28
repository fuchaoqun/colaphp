<?php

namespace Cola\OAuth2\Client\Provider;

use Cola\Http\Client;
use Exception;

abstract class AbstractProvider
{
    protected $_config = [];

    public function __construct($config)
    {
        $this->_config = $config + $this->_config;
    }

    public function genAuthorizeUrl($params = [])
    {
        $default = [
            'client_id' => $this->_config['clientId'],
            'redirect_uri' => $this->_config['redirectUri'],
            'scope' => $this->_config['scope']
        ];

        $params += $default;

        $url = Client::genUrl($this->_config['authorizeUrl'], $params);
        return $url;
    }

    /**
     * @param $code
     * @param array $data
     * @return mixed
     * @throws Exception
     */
    public function getAccessTokenInfo($code, $data = [])
    {
        $default = [
            'client_id' => $this->_config['clientId'],
            'client_secret' => $this->_config['clientSecret'],
            'code' => $code,
            'redirect_uri' => $this->_config['redirectUri']
        ];

        $data = $default + $data;

        $rps = Client::post($this->_config['accessTokenUrl'], $data);
        parse_str($rps, $ret);
        return $ret;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->_config = $config;
    }
}