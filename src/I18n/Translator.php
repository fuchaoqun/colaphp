<?php

namespace Cola\I18n;

use Cola\App;
use Cola\Config;

class Translator
{
    public $config = [
        'addonLocales' => ["en_US"],
        'paramKey'     => '_lang',
        'cookieKey'    => '_lang'
    ];

    public function __construct($config = [])
    {
        if (is_string($config)) {
            $config = ['messages' => $config];
        }
        if (is_string($config['messages']) && \file_exists($config['messages'])) {
            $config['messages'] = include($config['messages']);
        }
        if (is_array($config['messages'])) {
            $config['messages'] = new Config($config['messages']);
        }

        $this->config = $config + $this->config + ['locales' => $this->getLocalesFromHttp()];
    }

    public static function getFromContainer($name = '_translator')
    {
        $container = App::getInstance()->container;
        if ($container->has($name)) {
            return $container->get($name);
        }

        $config = App::getInstance()->config->get($name);
        $translator = new self($config);
        App::getInstance()->container->set($name, $translator);
        return $translator;
    }

    public function getLocalesFromHttp()
    {
        $paramKey = $this->config['paramKey'];
        if (!empty($_GET[$paramKey])) {
            return explode(',', $_GET[$paramKey]);
        }

        $cookieKey = $this->config['cookieKey'];
        if (!empty($_COOKIE[$cookieKey])) {
            return explode(',', $_COOKIE[$cookieKey]);
        }

        if (empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return [];
        }

        preg_match_all('([a-z]+\-[A-Z]+)', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches);
        if (empty($matches[0])) {
            return [];
        }

        array_walk($matches[0], function(&$val, $key) {$val = str_replace('-', '_', $val);});
        return $matches[0];
    }

    public function message($key, $locales = null)
    {
        if (null == $locales) {
            $locales = $this->config['locales'];
        }

        foreach ($this->config['addonLocales'] as $locale) {
            $locales[] = $locale;
        }

        $messages = $this->config['messages'];
        foreach ($locales as $locale) {
            $fullKey = "{$key}.{$locale}";
            if ($message = $messages->get($fullKey)) {
                return $message;
            }
        }

        throw new \Exception("NO_MESSAGE_FOUND_FOR_{$key}", 404);
    }
}