<?php

namespace Cola\Log\Handler;

class EchoHandler extends AbstractHandler
{
    public function handle($log, $context = [])
    {
        $text = $this->_config['formatter']->format($log, $context);
        echo $text;
        return true;
    }
}