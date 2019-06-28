<?php

namespace Cola\Security;

class Encryptor
{
    public $_key;
    public $_method = 'aes-256-cbc';
    public $_options = 0;
    public $_iv = '';

    public function __construct($key, $iv = '')
    {
        $this->_key = $key;
        $this->_iv = $iv;
    }

    public function encode($data)
    {
        return openssl_encrypt($data, $this->_method, $this->_key, $this->_options, $this->_iv);
    }

    public function decode($data)
    {
        return openssl_decrypt($data, $this->_method, $this->_key, $this->_options, $this->_iv);
    }

    public static function genKey($length = 32, $strong = true)
    {
        return openssl_random_pseudo_bytes($length, $strong);
    }

    public static function genIV($method = 'aes-256-cbc')
    {
        return openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->_key = $key;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->_method = $method;
    }

    /**
     * @return int
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * @param int $options
     */
    public function setOptions($options)
    {
        $this->_options = $options;
    }

    /**
     * @return string
     */
    public function getIv()
    {
        return $this->_iv;
    }

    /**
     * @param string $iv
     */
    public function setIv($iv)
    {
        $this->_iv = $iv;
    }
}