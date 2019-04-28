<?php

namespace Cola;

class Router
{
    public $config = [
        'namespace' => 'App',
        'rules' => [],
        'defaults' => [
            'module'     => 'Home',
            'controller' => 'IndexController',
            'action'     => 'indexAction',
            'args'       => []
        ]
    ];

    protected $_rules = [
        '*' => [],
        'GET' => [],
        'HEAD' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => [],
        'CONNECT' => [],
        'OPTIONS' => [],
        'TRACE' => [],
        'PATCH' => []
    ];

    /**
     * Constructor
     * @param array $config
     */
    public function __construct($config = [])
    {
        if (!empty($config['defaults'])) {
            $config['defaults'] = $config['defaults'] + $this->config['defaults'];
        };

        $this->config = $config + $this->config;

        foreach ($this->config['rules'] as $rule) {
            $rule += [
                'namespace' => $this->config['namespace'],
                'methods' => ['*'],
                'maps' => [],
                'args' => []
            ];
            $rule['methods'] = array_map('strtoupper', $rule['methods']);
            foreach ($rule['methods'] as $method) {
                if (!isset($this->_rules[$method])) {
                    $this->_rules[$method] = [];
                }
                $this->_rules[$method][$rule['regex']] = $rule;
            }
        }

        foreach ($this->_rules['GET'] as $key => $value) {
            if (!isset($this->_rules['HEAD'])) {
                $this->_rules['HEAD'][$key] = $value;
            }
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
        $es = $this->config['defaults'];

        if (preg_match('/^[a-zA-Z\d\/_]+$/', $pathInfo)) {
            $tmp = explode('/', $pathInfo);
            isset($tmp[0]) && $es['module'] = ucfirst($tmp[0]);
            isset($tmp[1]) && $es['controller'] = ucfirst($tmp[1]) . 'Controller';
            isset($tmp[2]) && $es['action'] = "{$tmp[2]}Action";
        }

        $controller = implode('\\', [$this->config['namespace'], $es['module'], $es['controller']]);

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
        $methods = [$_SERVER['REQUEST_METHOD'], '*'];

        foreach ($methods as $method) {
            foreach ($this->_rules[$method] as $regex => $rule) {
                if (!preg_match($regex, $pathInfo, $matches)) {
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
        }

        return $this->dynamic($pathInfo);
    }
}