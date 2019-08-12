<?php

namespace Cola\Log\Handler;

class FakeHandler extends AbstractHandler
{
    public function handle($log, $context = [])
    {
        return true;
    }
}