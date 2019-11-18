<?php

namespace Cola\I18n;

use Cola\App;
use Cola\Config;
use Exception;
use function file_exists;

class Translator
{
    protected $_config = [
        'availableLocales' => null,
        'addonLocales' => ["en_US"],
        'queryName'    => 'lang',
        'cookieName'   => 'lang'
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

        $this->_config = $config + $this->_config;

        $locales = $this->getLocalesFromRequest($this->_config['queryName'], $this->_config['cookieName']);
        foreach ($this->_config['addonLocales'] as $addonLocale) {
            if (!in_array($addonLocale, $locales)) {
                $locales[] = $addonLocale;
            }
        }

        if (!empty($this->_config['availableLocales'])) {
            $niceLocales = [];
            foreach ($locales as $locale) {
                if (in_array($locale, $this->_config['availableLocales'])) {
                    $niceLocales[] = $locale;
                }
            }
            $locales = $niceLocales;
        }

        $this->_config = $config + $this->_config + [
            'locales' => $locales
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

    public static function getLocalesFromRequest($queryName = 'lang', $cookieName = 'lang')
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

    public function getLocales()
    {
        return $this->_config['locales'];
    }

    public function message($key, $vars = [], $locales = null)
    {
        if (null == $locales) {
            $locales = $this->_config['locales'];
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