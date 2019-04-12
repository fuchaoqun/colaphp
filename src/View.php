<?php
/**
 *
 */

class Cola_View
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
     * @param string $tpl
     * @param string $dir
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
     * @param string $tpl
     * @param string $dir
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
     * @param string $regex
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

    /**
     * Dynamic get vars
     *
     * @param string $key
     */
    public function __get($key)
    {
        switch ($key) {
            case 'config':
                $this->config = Cola::getInstance()->config;
                return $this->config;

            default:
                return null;
        }
    }
}