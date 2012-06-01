<?php
class Cola_Com_Widget extends Cola_Controller
{
    /**
     * Template file
     *
     * @var strung
     */
    protected $_template;

    /**
     * Cache key
     *
     * @var string
     */
    protected $_cacheKey;

    /**
     * Cache config
     *
     * @var string|array
     */
    protected $_cacheConfig = '_cache';

    /**
     * Cache object
     *
     * @var Cola_Com_Cache
     */
    protected $_cache;

    /**
     * Init arg
     *
     * @var mixed
     */
    protected $_arg;

    /**
     * Updated template variables
     *
     * @var array
     */
    protected $_update = array();

    public function __construct($arg = null)
    {
        parent::__construct();
        $this->_arg = $arg;
    }

    /**
     * Display the widget
     *
     * @param string $tpl
     */
    public function display($tpl = null, $dir = null)
    {
        echo $this->fetch($tpl, $dir);
    }

    /**
     * Fetch HTML
     *
     * @param string $tpl
     * @param string $dir
     */
    public function fetch($tpl = null, $dir = null)
    {
        try {
            if ($this->_cacheKey && empty($this->_cache)) {
                $this->initCache();
            }

            if ($this->_cacheKey && $this->_cache && $data = $this->_cache->get($this->_cacheKey)) {
                return $data;
            }

            $this->_init($this->_arg);

            foreach ($this->_update as $key => $value) {
                $this->view->$key = $value;
            }

            if (empty($tpl)) {
                $tpl = $this->defaultTemplate();
            }

            if (empty($dir)) {
                $dir = Cola::config('_widgetsHome');
            }

            $data = $this->view->fetch($tpl, $dir);

            if ($this->_cacheKey && $this->_cache) {
                $this->_cache->set($this->_cacheKey, $data);
            }
        } catch (Exception $e) {
            $data = '';
        }

        return $data;
    }

    /**
     * Assign data to views
     *
     * @param string $key
     * @param mixed $value
     * @return Cola_Com_Widget
     */
    public function assign($key, $value = null, $update = false)
    {
        $data = array();

        if (is_string($key)) {
            $data = array($key => $value);
        } else if (is_array($key)) {
            $data = $key;
            $update = $value;
        }

        if ($update) {
            $this->_update = $data + $this->_update;
        } else {
            foreach ($data as $k => $v) {
                $this->view->$k = $v;
            }
        }

        return $this;
    }

    /**
     * Get default template file path
     *
     * @return string
     */
    protected function defaultTemplate()
    {
        if (empty($this->_template)) {
            $class = get_class($this);
            $this->_template = 'views' . DIRECTORY_SEPARATOR
                             . str_replace('_', DIRECTORY_SEPARATOR, substr($class, 0, -6))
                             . $this->_tplExt;
        }

        return $this->_template;
    }

    /**
     * Set widget use cache
     *
     * @param sting $key
     * @param string|array $name
     * @return Cola_Com_Widget
     */
    public function cache($key = null, $name = null)
    {
        if (empty($key)) {
            $key = md5(get_class($this));
        }

        $this->setCacheKey($key);
        $this->initCache($name);

        return $this;
    }

    /**
     * Set cache key
     *
     * @param string $key
     * @return Cola_Com_Widget
     */
    public function setCacheKey($key)
    {
        $this->_cacheKey = $key;

        return $this;
    }

    /**
     * Get cache key
     *
     * @return string
     */
    public function getCacheKey()
    {
        return $this->_cacheKey;
    }

    /**
     * Init cache object
     *
     * @param string|array $name
     * @return Cola_Com_Cache
     */
    public function initCache($name = null)
    {
        if (empty($name)) {
            $name = $this->_cacheConfig;
        }

        $this->_cache = Cola_Com::cache($name);

        return $this;
    }

    /**
     * Set widget not use cache
     *
     * @return Cola_Com_Widget
     */
    public function nocache()
    {
        $this->_cacheKey = null;
        return $this;
    }

    /**
     * Init widget data
     *
     */
    protected function _init($arg = null) {}
}