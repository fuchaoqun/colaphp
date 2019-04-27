<?php

namespace Cola\Validation;

/**
 * usage
$data = [
    'id'     => 8,
    'sex'    => 'F',
    'tags'   => ['foo' => 3, 'bar' => 7],
    'age'    => 8,
    'email'  => 'foo@bar.com',
    'date'   => '2012-12-10',
    'body'   => 'foobarbarfoo',
];

$rules = [
    'id'     => ['required' => true, 'type' => 'int'],
    'sex'    => ['in' => ['F', 'M']],
    'tags'   => ['required' => true, 'each' => ['type' => 'int']],
    'age'    => ['type' => 'int', 'range' => [38, 130], 'message' => 'age must be 18~130'],
    'email'  => ['type' => 'email'),
    'date'   => ['type' => 'date'),
    'body'   => ['required' => true, 'range' => [1, 500]]
];

var_dump(Validator::check($data, $rules));
**/

class Validator
{
    public $rules;

    public $ignorNotExists = false;

    public $translatorId = 'translator';

    public function __construct($rules, $ignorNotExists = false)
    {
        $this->rules = $rules;
        $this->ignorNotExists = $ignorNotExists;
    }

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
        is_string($value) && $value = strlen($value);
        is_array($value) && $value = count($value);
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
        is_string($value) && $value = strlen($value);
        is_array($value) && $value = count($value);
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
     * @param boolean $ignorNotExists
     * @return boolean
     */
    public function check($data, $ignorNotExists = null)
    {
        is_null($ignorNotExists) && $ignorNotExists = $this->ignorNotExists;
        $errors = [];

        foreach ($this->rules as $key => $rule) {
            $rule += array('required' => false, 'message' => 'failed');

            // deal with not existed
            if ((!isset($data[$key])) && $rule['required'] && (!$ignorNotExists)) {
                $errors[$key] = $this->_getMessage($rule['message']);
                continue;
            }

            if (!isset($data[$key])) continue;

            if (isset($rule['rules'])) {
                $validator = new self($rule['rules'], $ignorNotExists);
                try {
                    $validator->check($data[$key]);
                } catch (ValidationException $ve) {
                    $errors[$key] = $ve->errors;
                }
            }

            if (!$this->_check($data[$key], $rule, $ignorNotExists)) {
                $errors[$key] = $this->_getMessage($rule['message']);
                continue;
            }
        }

        if ($errors) {
            throw new ValidationException($errors);
        }

        return true;
    }

    /**
     * Check value
     *
     * @param mixed $value
     * @param array $rule
     * @return mixed string as error, true for OK
     */
    protected function _check($data, $rule, $ignorNotExists = false)
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
                case 'max':
                case 'range':
                    if (!self::$key($data, $val)) {
                        return false;
                    }
                    break;

                case 'each':
                    foreach ($data as $item) {
                        if (!$this->_check($item, $val, $ignorNotExists)) {
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

    protected function _getMessage($message)
    {
        if ('{{' !== \substr($message, 0, 2)) {
            return $message;
        }

        $translator = \Cola\App::getInstance()->container->get($translatorId);
        return $translator->message(\substr($message, 2, -2));
    }
}