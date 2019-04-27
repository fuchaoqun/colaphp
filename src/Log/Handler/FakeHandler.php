<?php

namespace Cola\Log\Handler;

class FakeHandler extends AbstractHandler
{
    public function _handle($text)
    {
        return true;
    }
}