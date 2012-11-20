<?php
/**
 *
 */
class Cola_Controller
{
    /**
     * The home directory of model
     *
     * @var string
     */
    public $modelsHome = null;

    /**
     * The home directory of view
     *
     * @var string
     */
    public $viewsHome = null;

    /**
     * Template file extension
     *
     * @var string
     */
    public $tplExt = '.php';

    /**
     * Constructor
     *
     * Init $_modelsHome & $_viewsHome from config if they are null
     */
    public function __construct()
    {
        if (null === $this->modelsHome) {
            $this->modelsHome = $this->config['_modelsHome'];
        }
        if (null === $this->viewsHome) {
            $this->viewsHome = $this->config['_viewsHome'];
        }
    }

    /**
     * Magic method
     *
     * @param string $methodName
     * @param array $args
     */
    public function __call($methodName, $args)
    {
        throw new Cola_Exception("Call to undefined method: Cola_Controller::$methodName()");
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
    protected function view($params = array())
    {
        $params = (array)$params + array('viewsHome' => $this->_viewsHome) + (array) Cola::config('_view');

        return $this->view = new Cola_View($params);
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
        $cola = Cola::getInstance();
        $dispatchInfo = $cola->getDispatchInfo();

        $tpl = str_replace('_', DIRECTORY_SEPARATOR, substr($dispatchInfo['controller'], 0, -10))
             . DIRECTORY_SEPARATOR
             . substr($dispatchInfo['action'], 0, -6)
             . $this->_tplExt;

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
                $this->config = Cola::config();
                return $this->config;

            default:
                throw new Cola_Exception('Undefined property: ' . get_class($this) . '::' . $key);
        }
    }
}
