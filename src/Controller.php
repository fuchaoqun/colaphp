<?php

namespace Cola;

use \Cola\Http\Request;
use \Cola\Http\Response;
use Cola\I18n\Translator;

/**
 * @property View view
 * @property Request request
 * @property Response response
 * @property Config config
 */
abstract class Controller
{
    /**
     * Magic method
     *
     * @param string $method
     * @param array $args
     * @throws \Exception
     */
    public function __call($method, $args)
    {
        $cls = get_class($this);
        throw new \Exception("Call to undefined method: {$cls}->{$method}()");
    }

    /**
     * Get var
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function get($key = null, $default = null)
    {
        return Request::get($key, $default);
    }

    /**
     * Post var
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function post($key = null, $default = null)
    {
        return Request::post($key, $default);
    }

    /**
     * View
     *
     * @param array $file
     * @return View
     * @throws \ReflectionException
     */
    protected function view($file = null)
    {
        empty($file) && $file = $this->defaultTemplate();
        return $this->view = new \Cola\View($file);
    }

    /**
     * Display the view
     *
     * @param string $file
     * @throws \ReflectionException
     */
    protected function display($file = null)
    {
        empty($file) && $file = $this->defaultTemplate();
        $this->view->file = $file;
        $this->view->display();
    }

    /**
     * Get default template file path
     *
     * @return string
     * @throws \ReflectionException
     */
    protected function defaultTemplate()
    {
        $dispatcher = App::getInstance()->dispatcher;
        $parts = explode('\\', $dispatcher->getController());
        $controller = strtolower(substr(end($parts), 0, -10));
        $action = strtolower(substr($dispatcher->getAction(), 0, -6));

        $reflector = new \ReflectionClass(\get_class($this));
        $dir = dirname($reflector->getFileName());
        return "{$dir}/views/{$controller}.{$action}.php";
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

    protected function message($key, $locales = null)
    {
        $translator = Translator::getFromContainer();
        return $translator->message($key, $locales);
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
     * @return Request|Response|View
     * @throws \ReflectionException
     */
    public function __get($key)
    {
        switch ($key) {
            case 'get':
                return $_GET;

            case 'post':
                return $_POST;

            case 'view':
                return $this->view();

            case 'request':
                $this->request = new Request();
                return $this->request;

            case 'response':
                $this->response = new Response();
                return $this->response;

            case 'config':
                $this->config = App::getInstance()->config;
                return $this->config;

            default:
                throw new \Exception('Undefined property: ' . get_class($this) . '::' . $key);
        }
    }
}
