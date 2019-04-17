<?php

namespace Cola;

class Router
{
    public $defaults = [
        'namespace'  => 'App',
        'module'     => 'Home',
        'controller' => 'IndexController',
        'action'     => 'indexAction',
        'args'       => []
    ];

    /**
     * Router urls
     *
     * @var array
     */
    public $rules = [];

    /**
     * Constructor
     * @param array $config
     */
    public function __construct($config = [])
    {
        $config += ['defaults' => [], 'rules' => []];
        $this->defaults = $config['defaults'] + $this->defaults;
        foreach($config['rules'] as $rule) {
            $rule += [
                'namespace' => $this->defaults['namespace'],
                'methods' => ['*'],
            ];
            $rule['methods'] = array_map('strtoupper', $rule['methods']);
            $this->rules[] = $rule;
        }
    }

    /**
     * Dynamic Match
     *
     * @param string $pathInfo
     * @return array
     */
    public function dynamic($pathInfo)
    {
        $es = $this->defaults;

        if (preg_match('/^[a-zA-Z\d\/_]+$/', $pathInfo)) {
            $tmp = explode('/', $pathInfo);
            isset($tmp[0]) && $es['module'] = ucfirst($tmp[0]);
            isset($tmp[1]) && $es['controller'] = ucfirst($tmp[1]) . 'Controller';
            isset($tmp[2]) && $es['action'] = "{$tmp[2]}Action";
        }

        $controller = implode('\\', [$es['namespace'], $es['module'], $es['controller']]);

        return [
            'controller' => $controller,
            'action'     => $es['action'],
            'args'       => $es['args']
        ];
    }

    /**
     * Match path
     *
     * @param string $pathInfo
     * @return array
     */
    public function match($pathInfo = null)
    {
        $pathInfo = trim($pathInfo, '/');
        $method = $_SERVER['REQUEST_METHOD'];

        foreach ($this->rules as $rule) {
            $rule += [
                'namespace' => $this->defaults['namespace'],
                'methods' => ['*'],
                'maps' => [],
                'args' => []
            ];

            if ((!in_array('*', $rule['methods'])) && (!in_array($method, $rule['methods']))) {
                continue;
            }

            if (!preg_match($rule['regex'], $pathInfo, $matches)) {
                continue;
            }

            if ($rule['maps']) {
                foreach ($rule['maps'] as $pos => $key) {
                    $rule['args'][$key] = urldecode($matches[$pos]);
                }
            }

            $controller = ('\\' == $rule['controller'][0]) ? $rule['controller'][0]
                : implode('\\', [$rule['namespace'], $rule['controller']]);

            return [
                'controller' => $controller,
                'action'     => $rule['action'],
                'args'       => $rule['args']
            ];
        }

        return $this->dynamic($pathInfo);
    }
}