<?php

namespace Cola\Validation;

class ValidationException extends \Cola\Exception\VisibleException
{
    public $error;

    public function __construct($error, $code = 400)
    {
        $this->error = $error;
        parent::__construct(json_encode($error, JSON_UNESCAPED_UNICODE), $code);
    }
}