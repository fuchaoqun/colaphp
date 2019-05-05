<?php

namespace Cola\OAuth2\Client\Provider;

use Cola\Http\Client;

abstract class AbstractProvider
{
    public $config = [];

    public function __construct($config)
    {
        $this->config = $config + $this->config;
    }

    public function genAuthorizeUrl($params = [])
    {
        $default = [
            'client_id' => $this->config['clientId'],
            'redirect_uri' => $this->config['redirectUri'],
            'scope' => $this->config['scope']
        ];

        $params += $default;

        $url = Client::genUrl($this->config['authorizeUrl'], $params);
        return $url;
    }

    public function getAccessToken($code, $data = [])
    {
        $default = [
            'client_id' => $this->config['clientId'],
            'client_secret' => $this->config['clientSecret'],
            'code' => $code,
            'redirect_uri' => $this->config['redirectUri']
        ];

        $data = $default + $data;

        $rps = Client::post($this->config['accessTokenUrl'], $data);
        parse_str($rps, $ret);
        return $ret;
    }
}