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

        if (!preg_match('/^[a-zA-Z\d\/_]+$/', $pathInfo)) {
            return $dispatchInfo;
        }

        $tmp = explode('/', $pathInfo);
        $cnt = count($tmp);

        switch ($cnt) {
            case 1:
                $dispatchInfo['controller'] = ucfirst($tmp[0]) . 'Controller';
                break;
            case 2:
                $dispatchInfo['controller'] = ucfirst($tmp[0]) . 'Controller';
                $dispatchInfo['action'] = $tmp[1] . 'Action';
                break;
            case 3:
                $dispatchInfo['sub'] = $tmp[0];
                $dispatchInfo['controller'] = ucfirst($tmp[1]) . 'Controller';
                $dispatchInfo['action'] = $tmp[2] . 'Action';
                break;
            default:
                break;
        }

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

            $rule['params'] = array_slice($matches, 1);
            return $rule;
        }

        if ($this->enableDynamicMatch) {
            return $this->dynamicMatch($pathInfo);
        }
        return false;
    }
}