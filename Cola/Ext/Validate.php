<?php
/**
 *
 */

class Cola_Com_Validate
{
    protected $_error = array();

    protected static $_message = array(
        'email'    => 'invalid_email',
        'required' => 'empty',
        'max'      => 'above_max',
        'min'      => 'below_min',
        'range'    => 'not_in_rang',
        'ip'       => 'invalid_ip',
        'number'   => 'not_all_numbers',
        'int'      => 'not_int',
        'digit'    => 'not_digit',
        'string'   => 'not_string'
    );
    /**
     * Check if is not empty
     *
     * @param string $str
     * @return boolean
     */
    public static function notEmpty($str, $trim = true)
    {
        if (is_array($str)) {
            return 0 < count($str);
        }

        return strlen($trim ? trim($str) : $str) ? true : false;
    }

    /**
     * Match regex
     *
     * @param string $value
     * @param string $regex
     * @return boolean
     */
    public static function match($value, $regex)
    {
        return preg_match($regex, $value) ? true : false;
    }

    /**
     * Max
     *
     * @param mixed $value numbernic|string
     * @param number $max
     * @return boolean
     */
    public static function max($value, $max)
    {
        if (is_string($value)) $value = strlen($value);
        return $value <= $max;
    }

    /**
     * Min
     *
     * @param mixed $value numbernic|string
     * @param number $min
     * @return boolean
     */
    public static function min($value, $min)
    {
        if (is_string($value)) $value = strlen($value);
        return $value >= $min;
    }

    /**
     * Range
     *
     * @param mixed $value numbernic|string
     * @param array $max
     * @return boolean
     */
    public static function range($value, $range)
    {
        if (is_string($value)) $value = strlen($value);
        return $value >= $range[0] && $value <= $range[1];
    }

    /**
     * Check if in array
     *
     * @param mixed $value
     * @param array $list
     * @return boolean
     */
    public static function in($value, $list)
    {
        return in_array($value, $list);
    }

    /**
     * Check if is email
     *
     * @param string $email
     * @return boolean
     */
    public static function email($email)
    {
        return preg_match('/^[a-z0-9_\-]+(\.[_a-z0-9\-]+)*@([_a-z0-9\-]+\.)+([a-z]{2}|aero|arpa|biz|com|coop|edu|gov|info|int|jobs|mil|museum|name|nato|net|org|pro|travel)$/', $email) ? true : false;
    }

    /**
     * Check if is url
     *
     * @param string $url
     * @return boolean
     */
    public static function url($url)
    {
        return preg_match('/^((https?|ftp|news):\/\/)?([a-z]([a-z0-9\-]*\.)+([a-z]{2}|aero|arpa|biz|com|coop|edu|gov|info|int|jobs|mil|museum|name|nato|net|org|pro|travel)|(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]))(\/[a-z0-9_\-\.~]+)*(\/([a-z0-9_\-\.]*)(\?[a-z0-9+_\-\.%=&amp;]*)?)?(#[a-z][a-z0-9_]*)?$/i', $url) ? true : false;
    }

    /**
     * Check if is ip
     *
     * @param string $ip
     * @return boolean
     */
    public static function ip($ip)
    {
        return ((false === ip2long($ip)) || (long2ip(ip2long($ip)) !== $ip)) ? false : true;
    }

    /**
     * Check if is date
     *
     * @param string $date
     * @return boolean
     */
    public static function date($date)
    {
        return preg_match('/^\d{4}[\/-]\d{1,2}[\/-]\d{1,2}$/', $date) ? true : false;
    }

    /**
     * Check if is numbers
     *
     * @param mixed $value
     * @return boolean
     */
    public static function number($value)
    {
        return is_numeric($value);
    }

    /**
     * Check if is int
     *
     * @param mixed $value
     * @return boolean
     */
    public static function int($value)
    {
        return is_int($value);
    }

    /**
     * Check if is digit
     *
     * @param mixed $value
     * @return boolean
     */
    public static function digit($value)
    {
        return is_int($value) || ctype_digit($value);
    }

    /**
     * Check if is string
     *
     * @param mixed $value
     * @return boolean
     */
    public static function string($value)
    {
        return is_string($value);
    }

    /**
     * Check
     *
     * $rules = array(
     *     'required' => true if required , false for not
     *     'type'     => var type, should be in ('email', 'url', 'ip', 'date', 'number', 'int', 'string')
     *     'regex'    => regex code to match
     *     'func'     => validate function, use the var as arg
     *     'max'      => max number or max length
     *     'min'      => min number or min length
     *     'range'    => range number or range length
     *     'msg'      => error message,can be as an array
     * )
     *
     * @param array $data
     * @param array $rules
     * @param boolean $ignorNotExists
     * @return boolean
     */
    public function check($data, $rules, $ignorNotExists = false)
    {
        foreach ($rules as $key => $rule) {
            $rule += array('required' => false, 'msg' => self::$_message);

            // deal with not existed
            if (!isset($data[$key])) {
                if (!$rule['required']) continue;
                if ($ignorNotExists) continue;
                $this->_error[$key] = $this->_msg($rule, 'required');
                continue;
            }

            $value = $data[$key];

            $result = self::_check($value, $rule);

            if (0 !== $result['code']) $this->_error[$key] = $result['msg'];

            if (isset($rule['rules'])) {
                $this->check($value, $rule['rules'], $ignorNotExists);
            }
        }

        return $this->_error ? false : true;
    }

    /**
     * Check value
     *
     * @param mixed $value
     * @param array $rule
     * @return mixed string as error, true for OK
     */
    protected function _check($value, $rule)
    {
        if ($rule['required'] && !self::notEmpty($value)) {
            return array('code' => -1, 'msg' => $this->_msg($rule, 'required'));
        }

        if (isset($rule['func']) && !call_user_func($rule['func'], $value)) {
            return array('code' => -1, 'msg' => $this->_msg($rule, 'func'));
        }

        if (isset($rule['regex']) && !self::match($value, $rule['regex'])) {
            return array('code' => -1, 'msg' => $this->_msg($rule, 'regex'));
        }

        if (isset($rule['type']) && !self::$rule['type']($value)) {
            return array('code' => -1, 'msg' => $this->_msg($rule, $rule['type']));
        }

        $acts = array('max', 'min', 'range', 'in');
        foreach ($acts as $act) {
            if (isset($rule[$act]) && !self::$act($value, $rule[$act])) {
                return array('code' => -1, 'msg' => $this->_msg($rule, $act));
            }
        }

        if (isset($rule['each'])) {
            $rule['each'] += array('required' => false, 'msg' => self::$_message);
            if (isset($rule['msg'])) {
                $rule['each'] += array('msg' => $rule['msg']);
            }
            foreach ($value as $item) {
                $result = $this->_check($item, $rule['each']);
                if (0 !== $result['code']) {
                    return $result;
                }
            }
        }

        return array('code' => 0);
    }

    /**
     * Get error message
     *
     * @param array $rule
     * @param string $name
     * @return string
     */
    protected function _msg($rule, $name)
    {
        if (empty($rule['msg'])) return 'INVALID';

        if (is_string($rule['msg'])) return $rule['msg'];

        return isset($rule['msg'][$name]) ? $rule['msg'][$name] : 'INVALID';
    }

    /**
     * Get error
     *
     * @return array
     */
    public function error()
    {
        return $this->_error;
    }
}