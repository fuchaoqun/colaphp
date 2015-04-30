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

class Cola_Ext_Validate
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
            $rule += array('required' => false, 'msg' => 'Unvalidated');

            // deal with not existed
            if (!isset($data[$key])) {
                if ($rule['required'] && !$ignorNotExists) {
                    $this->errors[$key] = $rule['msg'];
                }
                continue;
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
        $flag = true;
        foreach ($rule as $key => $val) {
            switch ($key) {
            	case 'required':
            		if ($val) $flag = self::notEmpty($data);
            		break;

                case 'func':
                    $flag = call_user_func($val, $data);
                    break;

                case 'regex':
                    $flag = self::match($data, $val);
                    break;

                case 'type':
                    $flag = self::$val($data);
                    break;

                case 'in':
                case 'max':
                case 'min':
                case 'max':
                case 'range':
                    $flag = self::$key($data, $val);
                    break;

                case 'each':
                    $val += array('required' => false);
                    foreach ($data as $item) {
                        if (!$flag = self::_check($item, $val)) break;
                    }
                    break;
                case 'rules':
                    $flag = $this->check($data, $val, $ignorNotExists);
                    break;
            	default:
            		break;
            }
            if (!$flag) {
                return false;
            }
        }

        return true;
    }
}