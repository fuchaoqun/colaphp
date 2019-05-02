<?php

namespace Cola\OAuth2\Client\Provider;

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

        $params = $default + $params;

        $url = \Cola\Http\Client::genUrl($this->config['authorizeUrl'], $params);
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

        $rps = \Cola\Http\Client::post($this->config['accessTokenUrl'], $data);
        parse_str($rps, $ret);
        return $ret;
    }
}