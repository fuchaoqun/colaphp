<?php

namespace Cola\Exception;

use Cola\Dto\RestError;
use RuntimeException;

class VisibleException extends RuntimeException
{
    public function display()
    {
        $re = new RestError($this->getCode(), $this->getMessage());
        $re->display();
    }
}