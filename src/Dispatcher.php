<?php


namespace Cola;


class Dispatcher
{
    public $info;

    public function __construct($info)
    {
        $this->info = $info;
    }

    public function dispatch()
    {
        $class = $this->getController();
        $action = $this->getAction();
        $args = $this->getArgs();

        $controller = new $class;
        $rps = call_user_func_array([$controller, $action], $args);
        $type = gettype($rps);

        switch ($type) {
            case 'integer':
            case 'string':
            case 'double':
                echo $type;
                break;
            case 'array':
                echo json_encode($rps, JSON_UNESCAPED_UNICODE);
                break;
            case 'object':
                $rps->display();
                break;
            default:
                break;
        }
    }

    public function getController()
    {
        return $this->info['controller'];
    }

    public function getAction()
    {
        return $this->info['action'];
    }

    public function getArgs()
    {
        return $this->info['args'];
    }
}