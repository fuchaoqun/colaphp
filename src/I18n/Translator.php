<?php

namespace Cola\I18n;

use Cola\App;
use Cola\Config;
use Exception;
use function file_exists;

class Translator
{
    protected $_config = [
        'addonLocales' => ["en_US"],
        'queryName'    => '_lang',
        'cookieName'   => '_lang'
    ];

    public function __construct($config = [])
    {
        if (is_string($config)) {
            $config = ['messages' => $config];
        }
        if (is_string($config['messages']) && file_exists($config['messages'])) {
            $config['messages'] = include($config['messages']);
        }
        if (is_array($config['messages'])) {
            $config['messages'] = new Config($config['messages']);
        }

        $this->_config = $config + $this->_config + [
            'locales' => $this->getLocalesFromRequest($this->_config['queryName'], $this->_config['cookieName'])
        ];
    }

    public static function getFromContainer($name = '_translator')
    {
        $container = App::getInstance()->getContainer();
        if ($container->has($name)) {
            return $container->get($name);
        }

        $config = App::config($name);
        $translator = new self($config);
        $container->set($name, $translator);
        return $translator;
    }

    public static function getLocalesFromRequest($queryName = '_lang', $cookieName = '_lang')
    {
        if (!empty($_GET[$queryName])) {
            return explode(',', $_GET[$queryName]);
        }

        if (!empty($_COOKIE[$cookieName])) {
            return explode(',', $_COOKIE[$cookieName]);
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

    public function message($key, $vars = [], $locales = null)
    {
        if (null == $locales) {
            $locales = $this->_config['locales'];
        }

        foreach ($this->_config['addonLocales'] as $locale) {
            $locales[] = $locale;
        }

        $messages = $this->_config['messages'];
        foreach ($locales as $locale) {
            $fullKey = "{$key}.{$locale}";
            if ($message = $messages->get($fullKey)) {
                return $vars ? str_replace(array_keys($vars), array_values($vars), $message) : $message;
            }
        }

        throw new Exception("NO_MESSAGE_FOUND_FOR_{$key}", 404);
    }
}