<?php
/**
 *
 */
class Cola_Controller
{
    /**
     * Template file extension
     *
     * @var string
     */
    public $tplExt = '.php';

    /**
     * Constructor
     *
     */
    public function __construct() {}

    /**
     * Magic method
     *
     * @param string $methodName
     * @param array $args
     */
    public function __call($method, $args)
    {
        throw new Cola_Exception("Call to undefined method: Cola_Controller::{$method}()");
    }

    /**
    * Get var
    *
    * @param string $key
    * @param mixed $default
    */
    protected function get($key = null, $default = null)
    {
        return Cola_Request::get($key, $default);
    }

    /**
    * Post var
    *
    * @param string $key
    * @param mixed $default
    */
    protected function post($key = null, $default = null)
    {
        return Cola_Request::post($key, $default);
    }

    /**
    * Param var
    *
    * @param string $key
    * @param mixed $default
    */
    protected function param($key = null, $default = null)
    {
        return Cola_Request::param($key, $default);
    }

    /**
     * View
     *
     * @param array $config
     * @return Cola_View
     */
    protected function view($viewsHome = null)
    {
        return $this->view = new Cola_View($viewsHome);
    }

    /**
     * Display the view
     *
     * @param string $tpl
     */
    protected function display($tpl = null, $dir = null)
    {
        if (empty($tpl)) {
            $tpl = $this->defaultTemplate();
        }

        $this->view->display($tpl, $dir);
    }

    /**
     * Get default template file path
     *
     * @return string
     */
    protected function defaultTemplate()
    {
        $dispatchInfo = Cola::getInstance()->dispatchInfo;

        $tpl = str_replace('_', DIRECTORY_SEPARATOR, substr($dispatchInfo['controller'], 0, -10))
             . DIRECTORY_SEPARATOR
             . substr($dispatchInfo['action'], 0, -6)
             . $this->tplExt;

        return $tpl;
    }

    /**
     * Abort
     *
     * @param mixed $data
     *
     */
    protected function abort($data)
    {
        if (!is_string($data)) {
            $data = json_encode($data);
        }
        echo $data;
        exit();
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
            case 'view':
                $this->view();
                return $this->view;

            case 'request':
                $this->request = new Cola_Request();
                return $this->request;

            case 'response':
                $this->response = new Cola_Response();
                return $this->response;

            case 'config':
                $this->config = Cola::getInstance()->config;
                return $this->config;

            default:
                throw new Cola_Exception('Undefined property: ' . get_class($this) . '::' . $key);
        }
    }
}
