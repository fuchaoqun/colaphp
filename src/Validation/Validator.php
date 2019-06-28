<?php

namespace Cola\Validation;

use Cola\I18n\Translator;
use Exception;


class Validator
{
    protected $_rules;

    protected $_errors;

    public $_ignoreNotExists = false;

    public function __construct($rules, $ignoreNotExists = false)
    {
        $this->_rules = $rules;
        $this->_ignoreNotExists = $ignoreNotExists;
    }

    /**
     * Check if is not empty
     *
     * @param string $str
     * @param bool $trim
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
     * @param mixed $value
     * @param number $max
     * @return boolean
     */
    public static function max($value, $max)
    {
        is_string($value) && $value = strlen($value);
        is_array($value) && $value = count($value);
        return $value <= $max;
    }

    /**
     * Min
     *
     * @param mixed $value
     * @param number $min
     * @return boolean
     */
    public static function min($value, $min)
    {
        is_string($value) && $value = strlen($value);
        is_array($value) && $value = count($value);
        return $value >= $min;
    }

    /**
     * Range
     *
     * @param mixed $value
     * @param $range
     * @return boolean
     */
    public static function range($value, $range)
    {
        is_string($value) && $value = strlen($value);
        is_array($value) && $value = count($value);
        return (($value >= $range[0]) && ($value <= $range[1]));
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
        return false !== filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Check if is url
     *
     * @param string $url
     * @return boolean
     */
    public static function url($url)
    {
        return false !== filter_var($url, FILTER_VALIDATE_URL);
    }

    /**
     * Check if is ip
     *
     * @param string $ip
     * @return boolean
     */
    public static function ip($ip)
    {
        return ((false !== ip2long($ip)) && (long2ip(ip2long($ip)) === $ip));
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
     * Check if is datetime
     *
     * @param string $datetime
     * @param string $format
     * @return boolean
     */
    public static function datetime($datetime, $format = 'Y-m-d H:i:s')
    {
        return ($time = strtotime($datetime)) && ($datetime == date($format, $time));
    }

    /**
     * Check if is number or numberic string
     *
     * @param mixed $value
     * @return boolean
     */
    public static function numberic($value)
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

    public static function digit($value)
    {
        return ctype_digit($value);
    }

    /**
     * Check if is float
     *
     * @param mixed $value
     * @return boolean
     */
    public static function float($value)
    {
        return is_float($value);
    }

    /**
     * Check if is int or float
     * @param $value
     * @return bool
     */
    public static function number($value)
    {
        return is_int($value) || is_float($value);
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
     * $rules = [
     *     'required' => true if required , false for not
     *     'type'     => var type, should be in ('email', 'url', 'ip', 'date', 'number', 'int', 'string')
     *     'regex'    => regex code to match
     *     'func'     => validate function, use the var as arg
     *     'max'      => max number or max length
     *     'min'      => min number or min length
     *     'range'    => range number or range length
     *     'message'  => error message,can be as an array
     * ]
     *
     * @param array $data
     * @param boolean $ignoreNotExists
     * @return boolean
     * @throws Exception
     */
    public function check($data, $ignoreNotExists = null)
    {
        is_null($ignoreNotExists) && $ignoreNotExists = $this->_ignoreNotExists;
        $errors = [];

        foreach ($this->_rules as $key => $rule) {
            $rule += ['required' => false, 'message' => 'failed'];

            // deal with not existed
            if ((!isset($data[$key])) && $rule['required'] && (!$ignoreNotExists)) {
                $errors[$key] = $this->getMessage($rule);
                continue;
            }

            if (!isset($data[$key])) continue;

            if (isset($rule['rules'])) {
                $validator = new self($rule['rules'], $ignoreNotExists);
                try {
                    $validator->check($data[$key]);
                } catch (ValidationException $ve) {
                    $errors[$key] = $ve->getErrors();
                }
            }

            if (!$this->_check($data[$key], $rule, $ignoreNotExists)) {
                $errors[$key] = $this->getMessage($rule);
                continue;
            }
        }

        if ($errors) {
            throw new ValidationException($errors);
        }

        return true;
    }

    /**
     * @param $rule
     * @return mixed
     * @throws Exception
     */
    public function getMessage($rule)
    {
        return empty($rule['i18n']) ? $rule['message'] : Translator::getFromContainer()->message($rule['message']);
    }

    /**
     * Check value
     *
     * @param $data
     * @param array $rule
     * @param bool $ignoreNotExists
     * @return mixed string as error, true for OK
     */
    protected function _check($data, $rule, $ignoreNotExists = false)
    {
        foreach ($rule as $key => $val) {
            switch ($key) {
                case 'required':
                    if ($val && !self::notEmpty($data)) {
                        return false;
                    }
                    break;

                case 'func':
                    if (!call_user_func($val, $data)) {
                        return false;
                    }
                    break;

                case 'regex':
                    if (!self::match($data, $val)) {
                        return false;
                    }
                    break;

                case 'type':
                    if (!self::$val($data)) {
                        return false;
                    }
                    break;

                case 'in':
                case 'max':
                case 'min':
                case 'range':
                    if (!self::$key($data, $val)) {
                        return false;
                    }
                    break;

                case 'each':
                    foreach ($data as $item) {
                        if (!$this->_check($item, $val, $ignoreNotExists)) {
                            return false;
                        }
                    }
                    break;
                case 'key':
                    foreach ($data as $k => $v) {
                        if (!$this->_check($k, $val, false)) {
                            return false;
                        }
                    }
                    break;
                default:
                    break;
            }
        }

        return true;
    }

    /**
     * @return mixed
     */
    public function getRules()
    {
        return $this->_rules;
    }

    /**
     * @param mixed $rules
     */
    public function setRules($rules)
    {
        $this->_rules = $rules;
    }

    /**
     * @return mixed
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * @return bool
     */
    public function isIgnoreNotExists()
    {
        return $this->_ignoreNotExists;
    }

    /**
     * @param bool $ignoreNotExists
     */
    public function setIgnoreNotExists($ignoreNotExists)
    {
        $this->_ignoreNotExists = $ignoreNotExists;
    }

}