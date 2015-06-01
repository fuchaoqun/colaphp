<?php
/**
 *
 */
abstract class Cola_Controller
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
    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
    }

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
        $tpl = $_SERVER['PATH_INFO'] . $this->tplExt;

        return $tpl;
    }

    /**
     * Redirect to other url
     *
     * @param string $url
     */
    protected function redirect($url, $code = 302)
    {
        $this->response->redirect($url, $code);
    }

    /**
     * JSON
     *
     * @param mixed $data
     * @param string $var jsonp var name
     *
     */
    protected function json($data, $var = null, $encode = 'UTF-8', $exit = true)
    {
        if (!is_string($data)) {
            $data = json_encode($data);
        }

        if ($var) {
            Cola_Response::charset($encode, 'application/javascript');
            echo "var {$var}={$data};";
        } else {
            Cola_Response::charset($encode, 'application/json');
            echo $data;
        }

        $exit && exit();
    }

    /**
     * Abort
     *
     * @param mixed $data
     * @param string $var jsonp var name
     *
     */
    protected function abort($data, $var = null)
    {
        $this->json($data, $var);
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
