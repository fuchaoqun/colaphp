<?php
/**
 *
 */

class Cola_Ext_Encrypt
{
    public $key;
    public $method = 'aes-256-cbc';
    public $options = 0;
    public $iv = "";

    public function __construct($key, $iv = "")
    {
        $this->key = $key;
        $this->iv = $iv;
    }

    public function encode($data)
    {
        return openssl_encrypt($data, $this->method, $this->key, $this->options, $this->iv);
    }

    public function decode($data)
    {
        return openssl_decrypt($data, $this->method, $this->key, $this->options, $this->iv);
    }

    public static function genKey($length = 32, $strong = true)
    {
        return openssl_random_pseudo_bytes($length, $strong);
    }

    public static function genIV($method = 'aes-256-cbc')
    {
        return openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
    }
}