<?php
/**
 * usage
$data = array(
    'id'     => 8,
    'sex'    => 'F',
    'tags'   => array('foo' => 3, 'bar' => 7),
    'age'    => 8,
    'email'  => 'foo@bar.com',
    'date'   => '2012-12-10',
    'body'   => 'foobarbarfoo',
);

$rules = array(
    'id'     => array('required' => true, 'type' => 'int'),
    'sex'    => array('in' => array('F', 'M')),
    'tags'   => array('required' => true, 'each' => array('type' => 'int')),
    'age'    => array('type' => 'int', 'range' => array(38, 130), 'msg' => 'age must be 18~130'),
    'email'  => array('type' => 'email'),
    'date'   => array('type' => 'date'),
    'body'   => array('required' => true, 'range' => array(1, 500))
);

var_dump(Cola_Ext_Validate::check($data, $rules));
**/

class Cola_Ext_Validator
{
    /**
     * Validate Errors
     *
     * @var array
     */
    public $errors = array();

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
            $rule += array('required' => false, 'msg' => 'failed');

            // deal with not existed
            if ((!isset($data[$key])) && $rule['required'] && (!$ignorNotExists)) {
                $this->errors[$key] = $rule['msg'];
                continue;
            }

            if (!isset($data[$key])) continue;

            if (isset($rule['rules'])) {
                $validator = new self();
                if (!$validator->check($data[$key], $rule['rules'], $ignorNotExists)) {
                    $this->errors[$key] = $validator->errors;
                    continue;
                }
            }

            if (!$this->_check($data[$key], $rule, $ignorNotExists)) {
                $this->errors[$key] = $rule['msg'];
                continue;
            }
        }

        return $this->errors ? false : true;
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
}