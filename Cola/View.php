<?php
/**
 *
 */

class Cola_View
{
    /**
     * Base path of views
     *
     * @var string
     */
    protected $_basePath = '';

    /**
     * Widgets Home
     *
     * @var string
     */
    protected $_widgetsHome = '';

    /**
     * Constructor
     *
     */
    public function __construct($params = array())
    {
        if (isset($params['basePath'])) {
            $this->_basePath = $params['basePath'];
        }
    }

    /**
     * Set base path of views
     *
     * @param string $path
     */
    public function setBasePath($path)
    {
        $this->_basePath = $path;
    }

    /**
     * Get base path of views
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->_basePath;
    }

    /**
     * Render view
     *
     */
    protected function _render($tpl, $dir = null)
    {
        if (null === $dir) $dir = $this->_basePath;
        if ($dir) $dir = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR;
        ob_start();
        ob_implicit_flush(0);
        $file = $dir . $tpl;
        include $file;
        return ob_get_clean();
    }

    /**
     * Fetch
     *
     * @param string $tpl
     * @param string $dir
     * @return string
     */
    public function fetch($tpl, $dir = null)
    {
        return $this->_render($tpl, $dir);
    }

    /**
     * Display
     *
     * @param string $tpl
     * @param string $dir
     */
    public function display($tpl, $dir = null)
    {
        echo $this->_render($tpl, $dir);
    }

    /**
     * Slot
     *
     * @param string $tpl
     * @param mixed $data
     * @return string
     */
    public function slot($file, $data = null)
    {
        ob_start();
        ob_implicit_flush(0);
        include $file;
        return ob_get_clean();
    }

    /**
     * Set widgets home dir
     *
     * @param strong $dir
     * @return Cola_View
     */
    public function setWidgetsHome($dir)
    {
        $this->_widgetsHome = $dir;
        return $this;
    }

    /**
     * Get widgets Home
     *
     * @return string
     */
    public function getWidgetsHome()
    {
        return $this->_widgetsHome;
    }

    /**
     * Widget
     *
     * @param string $name
     * @param array $data
     * @return Cola_Com_Widget
     */
    public function widget($name, $data = null)
    {
        if (empty($this->_widgetsHome) && $widgetsHome = $this->config->get('_widgetsHome')) {
            $this->_widgetsHome = $widgetsHome;
        }

        $class = ucfirst($name) . 'Widget';

        if (!Cola::loadClass($class, $this->_widgetsHome)) {
            throw new Cola_Exception("Can not find widget:{$class}");
        }

        $widget = new $class($data);

        return $widget;
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
    public static function truncate($str, $limit, $encoding = 'UTF-8', $suffix = '...', $regex = null)
    {
        if (function_exists('mb_strwidth')) {
            return  self::mbTruncate($str, $limit, $encoding, $suffix);
        }
        return self::regexTruncate($str, $limit, $encoding, $suffix, $regex = null);
    }

    /**
     * Truncate with mbstring
     *
     * @param string $str
     * @param int $limit
     * @param string $encoding
     * @param string $suffix
     * @return string
     */
    public static function mbTruncate($str, $limit, $encoding = 'UTF-8', $suffix = '...')
    {
        if (mb_strwidth($str, $encoding) <= $limit) return $str;

        $limit -= mb_strwidth($suffix, $encoding);
        $tmp = mb_strimwidth($str, 0, $limit, '', $encoding);
        return $tmp . $suffix;
    }

    /**
     * Truncate with regex
     *
     * @param string $str
     * @param int $limit
     * @param string $encoding
     * @param string $suffix
     * @param string $regex
     * @return string
     */
    public static function regexTruncate($str, $limit, $encoding = 'UTF-8', $suffix = '...', $regex = null)
    {
        $defaultRegex = array(
            'UTF-8'  => "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/",
            'GB2312' => "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/",
            'GBK'    => "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/",
            'BIG5'   => "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/"
        );

        $encoding = strtoupper($encoding);

        if (null === $regex && !isset($defaultRegex[$encoding])) {
            throw new Exception("Truncate failed: not supported encoding, you should supply a regex for $encoding encoding");
        }

        $regex || $regex = $defaultRegex[$encoding];

        preg_match_all($regex, $str, $match);

        $trueLimit = $limit - strlen($suffix);
        $len = $pos = 0;

        foreach ($match[0] as $word) {
            $len += strlen($word) > 1 ? 2 : 1;
            if ($len > $trueLimit) continue;
            $pos ++;
        }
        if ($len <= $limit) return $str;
        return join("",array_slice($match[0], 0, $pos)) . $suffix;
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
                $this->config = Cola::config();
                return $this->config;

            default:
                return null;
        }
    }
}