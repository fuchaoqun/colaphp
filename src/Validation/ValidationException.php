<?php

namespace Cola\Validation;

use Cola\Exception\VisibleException;

class ValidationException extends VisibleException
{
    protected $_errors;

    public function __construct($errors, $code = 400)
    {
        $this->_errors = $errors;
        parent::__construct(json_encode($errors, JSON_UNESCAPED_UNICODE), $code);
    }

    /**
     * @return mixed
     */
    public function getErrors()
    {
        return $this->_errors;
    }
}