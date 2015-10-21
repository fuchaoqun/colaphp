<?php
/**
 *
 */
class Cola_Router
{
    public $default = array(
        'module'     => 'home',
        'controller' => 'IndexController',
        'action'     => 'indexAction',
        'args'       => array()
    );

    /**
     * Router rules
     *
     * @var array
     */
    public $rules = array();

    /**
     * Constructor
     *
     */
    public function __construct() {}

    /**
     * Dynamic Match
     *
     * @param string $pathInfo
     * @return array $di
     */
    public function dynamic($pathInfo)
    {
        $di = $this->default;

        if (!preg_match('/^[a-zA-Z\d\/_]+$/', $pathInfo)) {
            return $di;
        }

        $tmp = explode('/', $pathInfo);
        isset($tmp[0]) && $di['module'] = $tmp[0];
        isset($tmp[1]) && $di['controller'] = ucfirst($tmp[1]) . 'Controller';
        isset($tmp[2]) && $di['action'] = "{$tmp[2]}Action";

        return $di;
    }

    /**
     * Match path
     *
     * @param string $path
     * @return boolean
     */
    public function match($pathInfo = null)
    {
        $pathInfo = trim($pathInfo, '/');

        foreach ($this->rules as $regex => $rule) {
            $rule += array('maps' => array(), 'args' => array());
            if (!preg_match($regex, $pathInfo, $matches)) {
                continue;
            }

            if ($rule['maps']) {
                foreach ($rule['maps'] as $pos => $key) {
                    $rule['args'][$key] = urldecode($matches[$pos]);
                }
            }

            return $rule;
        }

        return $this->dynamic($pathInfo);
    }
}