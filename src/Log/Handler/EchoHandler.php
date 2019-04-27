<?php

namespace Cola\Log\Handler;

class EchoHandler extends AbstractHandler
{
    public function _handle($text)
    {
        echo $text;
        return true;
    }
}