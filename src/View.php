<?php

namespace Cola;

use Cola\I18n\Translator;

/**
 * @property Config config
 */
class View
{
    /**
     * view file
     *
     * @var string
     */
    protected $_file;

    /**
     * Constructor
     * @param $file
     */
    public function __construct($file)
    {
        $this->_file = $file;
    }

    /**
     * Fetch
     *
     * @return string
     */
    public function fetch()
    {
        ob_start();
        ob_implicit_flush(0);
        $this->display();
        return ob_get_clean();
    }

    /**
     * Display
     *
     */
    public function display()
    {
        include $this->_file;
    }

    /**
     * Escape
     *
     * @param string $str
     * @param string $type
     * @param string $encoding
     * @return string
     */
    public static function escape($str, $type = 'html', $encoding = 'UTF-8')
    {
        switch ($type) {
            case 'html':
                return htmlspecialchars($str, ENT_QUOTES, $encoding);

            case 'htmlall':
                return htmlentities($str, ENT_QUOTES, $encoding);

            case 'javascript':
                return strtr($str, array('\\'=>'\\\\',"'"=>"\\'",'"'=>'\\"',"\r"=>'\\r',"\n"=>'\\n','</'=>'<\/'));

            case 'mail':
                return str_replace(array('@', '.'),array(' [AT] ', ' [DOT] '), $str);

            default:
                return $str;
        }
    }

    /**
     * Truncate
     *
     * @param string $str
     * @param int $limit
     * @param string $encoding
     * @param string $suffix
     * @return string
     */
    public static function truncate($str, $limit, $encoding = 'UTF-8', $suffix = '...')
    {
        if (mb_strwidth($str, $encoding) <= $limit) return $str;

        $limit -= mb_strwidth($suffix, $encoding);
        $tmp = mb_strimwidth($str, 0, $limit, '', $encoding);
        return $tmp . $suffix;
    }

    public static function desensitizeEmail($email)
    {
        $max = 3;

        $info = explode('@', $email, 2);
        if ($max < strlen($info[0])) {
            $prefix = substr($info[0], 0, $max);
        } else {
            $prefix = substr($info[0], 0, 1) ;
        }

        return $prefix . '***@' . $info[1];
    }

    public static function desensitizeName($name, $encoding = 'UTF-8', $suffix = '**')
    {
        $limit = 2;
        return mb_strimwidth($name, 0, $limit, '', $encoding) . $suffix;
    }

    public static function desensitizePhoneNumber($phoneNumber)
    {
        $l = strlen($phoneNumber);
        if ($l >= 8) {
            return substr_replace($phoneNumber, '****', $l - 8, 4);
        } else {
            $m = intval(ceil($l/2));
            return str_repeat('*', $m) . substr($phoneNumber, $m);
        }
    }

    /**
     * Dynamic set vars
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value = null)
    {
        $this->$key = $value;
    }

    public function js()
    {
        $args = func_get_args();
        foreach ($args as $arg) {
            $tmp = (array) $this->config->get($arg, $arg);
            foreach ($tmp as $row) {
                echo "<script type=\"text/javascript\" src=\"{$row}\"></script>";
            }
        }
    }

    public function css()
    {
        $args = func_get_args();
        foreach ($args as $arg) {
            $tmp = (array) $this->config->get($arg, $arg);
            foreach ($tmp as $row) {
                echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$row}\" />";
            }
        }
    }

    public function message($key, $locales = null)
    {
        $translator = Translator::getFromContainer();
        return $translator->message($key, $locales);
    }

    /**
     * Dynamic get vars
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        switch ($key) {
            case 'config':
                $this->config = App::getInstance()->getConfig();
                return $this->config;

            default:
                return null;
        }
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->_file;
    }

    /**
     * @param string $file
     */
    public function setFile($file)
    {
        $this->_file = $file;
    }
}