<?php
/**
 *
 */

class Cola_Com_Encrypt
{
    protected $_config = array(
        'hash'      => 'sha1',
        'xor'       => false,
        'mcrypt'    => false,
        'noise'     => true,
        'cipher'    => MCRYPT_RIJNDAEL_256,
        'mode'      => MCRYPT_MODE_ECB
    );

    protected $_key;

    /**
     * Constructor
     *
     * @param string $key
     * @param array $config
     */
    public function __construct($key = null, $config = array())
    {
        $this->_key = $key;

        if (function_exists('mcrypt_encrypt')) {
            $this->_config['mcrypt'] = true;
        }

        $this->_config = $config + $this->_config;
    }

    /**
     * Set or get config
     *
     * @param mixed $key
     * @param mixed $value
     * @return mixed
     */
    public function config($key = null, $value = null)
    {
        if (is_null($key)) {
            return $this->_config;
        }

        if (is_array($key)) {
            $this->_config = $key + $this->_config;
            return $this;
        }

        if (is_null($value)) {
            return $this->_config[$key];
        }

        $this->_config[$key] = $value;
    }

    /**
     * Encode
     *
     * @param string $str
     * @param string $key
     * @return string
     */
    public function encode($str, $key = null)
    {
        if (is_null($key)) {
            $key = $this->_key;
        }

        if ($this->_config['xor']) {
            $str = $this->_xorEncode($str, $key);
        }

        if ($this->_config['mcrypt']) {
            $str = $this->_mcryptEncode($str, $key);
        }

        if ($this->_config['noise']) {
            $str = $this->_noise($str, $key);
        }

		return base64_encode($str);
    }

    /**
     * Decode
     *
     * @param string $str
     * @param string $key
     * @return string
     */
    public function decode($str, $key = null)
    {
        if (is_null($key)) $key = $this->_key;

        if (preg_match('/[^a-zA-Z0-9\/\+=]/', $str)) {
            return false;
        }

        $str = base64_decode($str);

        if ($this->_config['noise']) {
            $str = $this->_denoise($str, $key);
        }

        if ($this->_config['mcrypt']) {
            $str = $this->_mcryptDecode($str, $key);
        }

        if ($this->_config['xor']) {
            $str = $this->_xorDecode($str, $key);
        }

        return $str;
    }

    /**
     * Mcrypt encode
     *
     * @param string $str
     * @param string $key
     * @return string
     */
    protected function _mcryptEncode($str, $key)
    {
        $cipher = $this->_config['cipher'];
        $mode   = $this->_config['mode'];
        $size = mcrypt_get_iv_size($cipher, $mode);
		$vect = mcrypt_create_iv($size, MCRYPT_RAND);

		return mcrypt_encrypt($cipher, $key, $str, $mode, $vect);
    }

    /**
     * Mcrypt decode
     *
     * @param string $str
     * @param string $key
     * @return string
     */
    protected function _mcryptDecode($str, $key)
    {
        $cipher = $this->_config['cipher'];
        $mode   = $this->_config['mode'];
		$size = mcrypt_get_iv_size($cipher, $mode);
		$vect = mcrypt_create_iv($size, MCRYPT_RAND);

		return rtrim(mcrypt_decrypt($cipher, $key, $str, $mode, $vect), "\0");
    }

    /**
     * XOR encode
     *
     * @param string $str
     * @param string $key
     * @return string
     */
    protected function _xorEncode($str, $key)
    {
        $rand = $this->_config['hash'](rand());
        $code = '';
		for ($i = 0; $i < strlen($str); $i++) {
		    $r = substr($rand, ($i % strlen($rand)), 1);
			$code .= $r . ($r ^ substr($str, $i, 1));
		}

		return $this->_xor($code, $key);
    }

    /**
     * XOR decode
     *
     * @param string $str
     * @param string $key
     * @return string
     */
    protected function _xorDecode($str, $key)
    {
        $str = $this->_xor($str, $key);
        $code = '';
        for ($i = 0; $i < strlen($str); $i++) {
			$code .= (substr($str, $i++, 1) ^ substr($str, $i, 1));
		}
		return $code;
    }

    /**
     * XOR
     *
     * @param string $str
     * @param string $key
     * @return string
     */
    protected function _xor($str, $key)
    {
        $hash = $this->_config['hash']($key);
        $code = '';
        for ($i = 0; $i < strlen($str); $i++) {
			$code .= substr($str, $i, 1) ^ substr($hash, ($i % strlen($hash)), 1);
		}
		return $code;
    }

    /**
     * Noise
     *
     * @see http://www.ciphersbyritter.com/GLOSSARY.HTM#IV
     * @param string $str
     * @param string $key
     * @return string
     */
    protected function _noise($str, $key)
    {
        $hash = $this->_config['hash']($key);
		$hashlen = strlen($hash);
		$strlen = strlen($str);
		$code = '';

		for ($i = 0, $j = 0; $i < $strlen; ++$i, ++$j) {
			if ($j >= $hashlen) $j = 0;
			$code .= chr((ord($str[$i]) + ord($hash[$j])) % 256);
		}

		return $code;
    }

    /**
     * Denoise
     *
     * @param string $str
     * @param string $key
     * @return string
     */
    protected function _denoise($str, $key)
    {
        $hash = $this->_config['hash']($key);
		$hashlen = strlen($hash);
		$strlen = strlen($str);
		$code = '';

		for ($i = 0, $j = 0; $i < $strlen; ++$i, ++$j) {
			if ($j >= $hashlen) $j = 0;
			$temp = ord($str[$i]) - ord($hash[$j]);
			if ($temp < 0) $temp = $temp + 256;
			$code .= chr($temp);
		}

		return $code;
    }
}