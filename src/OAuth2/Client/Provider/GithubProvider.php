<?php

namespace Cola\OAuth2\Client\Provider;

class GithubProvider extends AbstractProvider
{
    public $_config = [
        'authorizeUrl' => 'https://github.com/login/oauth/authorize',
        'accessTokenUrl' => 'https://github.com/login/oauth/access_token',
    ];
}