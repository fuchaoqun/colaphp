<?php

namespace Cola;

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
    public $file;

    /**
     * Constructor
     *
     */
    public function __construct($file)
    {
        $this->file = $file;
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
        include $this->file;
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
        $app = App::getInstance();
        foreach ($args as $arg) {
            $tmp = (array) $app->config->get($arg, $arg);
            foreach ($tmp as $row) {
                echo "<script type=\"text/javascript\" src=\"{$row}\"></script>";
            }
        }
    }

    public function css()
    {
        $args = func_get_args();
        $app = App::getInstance();
        foreach ($args as $arg) {
            $tmp = (array) $app->config->get($arg, $arg);
            foreach ($tmp as $row) {
                echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$row}\" />";
            }
        }
    }

    /**
     * Dynamic get vars
     *
     * @param string $key
     * @return Config|null
     */
    public function __get($key)
    {
        switch ($key) {
            case 'config':
                $this->config = App::getInstance()->config;
                return $this->config;

            default:
                return null;
        }
    }
}