<?php

namespace Cola;

defined('COLA_DIR') || define('COLA_DIR', dirname(__FILE__));
require_once COLA_DIR . '/Config.php';
require_once COLA_DIR . '/Container.php';

class App
{
    /**
     * Singleton instance
     *
     * Marked only as protected to allow extension of the class. To extend,
     * simply override {@link getInstance()}.
     *
     * @var App
     */
    protected static $_instance = null;

    /**
     * Run time config
     *
     * @var Config
     */
    protected $_config;

    /**
     * Object container
     *
     * @var Container
     */
    protected $_container;

    /**
     * Router
     *
     * @var Router
     */
    protected $_router = null;

    /**
     * @var Dispatcher
     */
    protected $_dispatcher = null;

    /**
     * Path info
     *
     * @var string
     */
    protected $_pathInfo = null;

    /**
     * Constructor
     *
     */
    protected function __construct()
    {
        $this->_config = new Config([
            '_class' => [
                'Cola\Controller'                 => COLA_DIR . '/Controller.php',
                'Cola\Model'                      => COLA_DIR . '/Model.php',
                'Cola\View'                       => COLA_DIR . '/View.php',
                'Cola\Router'                     => COLA_DIR . '/Router.php',
                'Cola\Exception\VisibleException' => COLA_DIR . '/Exception/VisibleException.php',
                'Cola\Http\Request'               => COLA_DIR . '/Http/Request.php',
                'Cola\Http\Response'              => COLA_DIR . '/Http/Response.php',
                'Cola\Db\Pdo'                     => COLA_DIR . '/Db/Pdo.php',
                'Cola\Cache\SimpleCache'          => COLA_DIR . '/Cache/SimpleCache.php',
                'Cola\Cache\Redis'                => COLA_DIR . '/Cache/Redis.php',
                'Cola\Validation\Validator'       => COLA_DIR . '/Validation/Validator.php',
            ],
        ]);
        $this->_container = new Container();

        $this->registerAutoload([$this, 'loadClass']);
    }

    /**
     * Bootstrap
     *
     * @param mixed $config string as a file and array as config
     * @return App
     */
    public function boot($config = [])
    {
        if (is_string($config) && file_exists($config)) {
            include $config;
        }

        $this->_config->merge($config);
        return $this;
    }

    /**
     * Singleton instance
     *
     * @return App
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public static function config($name = null, $default = null, $delimiter = '.')
    {
        return self::getInstance()->getConfig()->get($name, $default, $delimiter);
    }

    public static function container($name, $default = null)
    {
        return self::getInstance()->getContainer()->get($name, $default);
    }

    /**
     * @return Router
     */
    public function getRouter()
    {
        return $this->_router;
    }

    /**
     * @param Router $router
     */
    public function setRouter($router)
    {
        $this->_router = $router;
    }

    /**
     * @return Dispatcher
     */
    public function getDispatcher()
    {
        return $this->_dispatcher;
    }

    /**
     * @param Dispatcher $dispatcher
     */
    public function setDispatcher($dispatcher)
    {
        $this->_dispatcher = $dispatcher;
    }

    /**
     * @return string
     */
    public function getPathInfo()
    {
        return $this->_pathInfo;
    }

    /**
     * @param string $pathInfo
     */
    public function setPathInfo($pathInfo)
    {
        $this->_pathInfo = $pathInfo;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->_container;
    }

    /**
     * Load class
     *
     * @param string $class
     * @param string $file
     * @return boolean
     */
    public function loadClass($class, $file = '')
    {
        if (class_exists($class, false) || interface_exists($class, false)) {
            return true;
        }

        if (!$file) {
            $file = $this->_config->get("_class.{$class}");
        }

        if (file_exists($file)) {
            include $file;
        }

        return (class_exists($class, false) || interface_exists($class, false)) || $this->psr4($class);
    }

    public function regClasses($classes)
    {
        $this->_config->merge(['_class' => $classes]);

        return $this;
    }

    public function regClass($class, $file)
    {
        $classes = [$class => $file];

        return $this->regClasses($classes);
    }

    /**
     * psr-4 autoload
     * @param string $class
     * @return boolean
     */
    public function psr4($class)
    {
        $prefix = $class;
        $psr4 = $this->_config->get('_psr4');
        while (false !== ($pos = strrpos($prefix, '\\'))) {
            $prefix = substr($class, 0, $pos);
            $rest = substr($class, $pos + 1);
            if (empty($psr4[$prefix])) continue;
            $file = $psr4[$prefix] . DIRECTORY_SEPARATOR
                  . str_replace('\\', DIRECTORY_SEPARATOR, $rest)
                  . '.php';
            if (file_exists($file)) {
                require_once $file;
                return true;
            }
        }
        return false;
    }

    /**
     * Add psr-4 namespace
     * @param $prefix
     * @param $base
     * @return App
     */
    public function addNamespace($prefix, $base)
    {
        $prefix = trim($prefix, '\\') . '\\';
        $base = rtrim($base, DIRECTORY_SEPARATOR);
        $key = "_psr4.{$prefix}";
        $this->_config->set($key, $base);
        return $this;
    }

    /**
     * Register autoload function
     *
     * @param mixed $func
     * @return App
     */
    public function registerAutoload($func)
    {
        spl_autoload_register($func);
        return $this;
    }

    /**
     * Unregister autoload function
     *
     * @param string $func
     * @return App
     */
    public function unregisterAutoload($func)
    {
        spl_autoload_unregister($func);
        return $this;
    }

    public function go($dispatchInfo = null)
    {
        if (is_null($dispatchInfo)) {
            $this->_router || ($this->_router = new Router($this->_config->get('_router', [])));
            $this->_pathInfo || ($this->_pathInfo = $_SERVER['PATH_INFO']);
            $dispatchInfo = $this->_router->match($this->_pathInfo);
        }

        $this->_dispatcher || ($this->_dispatcher = new Dispatcher($dispatchInfo));

        $this->_dispatcher->dispatch();
    }
}