<?php

namespace Cola\Validation;

use Cola\Exception\VisibleException;

class ValidationException extends VisibleException
{
    public $errors;

    public function __construct($errors, $code = 400)
    {
        $this->errors = $errors;
        parent::__construct(json_encode($errors, JSON_UNESCAPED_UNICODE), $code);
    }
}