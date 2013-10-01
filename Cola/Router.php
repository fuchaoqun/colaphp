<?php
/**
 *
 */
class Cola_Router
{
    public $enableDynamicMatch = true;
    public $defaultDynamicRule = array(
        'controller' => 'IndexController',
        'action'     => 'indexAction'
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
     * @return array $dispatchInfo
     */
    public function dynamicMatch($pathInfo)
    {
        $dispatchInfo = $this->defaultDynamicRule;

        $tmp = explode('/', $pathInfo);

        if ($controller = current($tmp)) {
            $dispatchInfo['controller'] = ucfirst($controller) . 'Controller';
        }

        if ($action = next($tmp)) {
            $dispatchInfo['action'] = $action . 'Action';
        }

        $params = array();
        while (false !== ($next = next($tmp))) {
            $params[$next] = urldecode(next($tmp));
        }

        Cola::setReg('_params', $params);

        return $dispatchInfo;
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

            if (!preg_match($regex, $pathInfo, $matches)) {
                continue;
            }

            if (isset($rule['maps']) && is_array($rule['maps'])) {
                $params = array();
                foreach ($rule['maps'] as $pos => $key) {
                    if (isset($matches[$pos]) && '' !== $matches[$pos]) {
                        $params[$key] = urldecode($matches[$pos]);
                    }
                }
                if (isset($rule['defaults'])) {
                    $params += $rule['defaults'];
                }

                Cola::setReg('_params', $params);
            }
            return $rule;
        }

        if ($this->enableDynamicMatch) {
            return $this->dynamicMatch($pathInfo);
        }
        return false;
    }
}