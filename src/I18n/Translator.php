<?php

namespace Cola\I18n;

class Translator
{
    public $messages;
    public $locales;

    public $config = [
        'locales'  => ["en_US"],
        'paramId'  => '_lang',
        'cookieId' => '_lang'
    ];

    public function __construct($messages, $config = [])
    {
        if (is_string($messages) && \file_exists($messages)) {
            $messages = include($messages);
        }

        $this->config = $config + $this->config;

        $this->messages = new \Cola\Config($messages);
        $this->locales = $this->getLocalesFromHttp();
    }

    public function getLocalesFromHttp()
    {
        $paramId = $this->config['paramId'];
        if (!empty($_GET[$paramId])) {
            return explode(',', $_GET[$paramId]);
        }

        $cookieId = $this->config['cookieId'];
        if (!empty($_COOKIE[$cookieId])) {
            return explode(',', $_COOKIE[$cookieId]);
        }

        if (empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return $this->config['locales'];
        }

        preg_match_all('([a-z]+\-[A-Z]+)', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches);
        if (empty($matches[0])) {
            return $defaultLocales;
        }

        array_walk($matches[0], function(&$val, $key) {$val = str_replace('-', '_', $val);});
        return $matches[0];
    }

    public function message($key, $locales = null)
    {
        if (null == $locales) {
            $locales = $this->locales;
        }

        $locales = array_merge($locales, $this->config['locales']);

        foreach ($locales as $locale) {
            if (isset(self::$_messages[$key][$locale])) {
                return self::$_messages[$key][$locale];
            }
        }

        throw new \Exception("NO_MESSAGE_FOUND_FOR_{$key}", 404);
    }
}