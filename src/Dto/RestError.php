<?php


namespace Cola\Dto;


class RestError extends RestResponse
{
    public function __construct($code, $message = null)
    {
        $this->_code = $code;

        if (!is_null($message)) {
            $this->_message = $message;
        }
    }
}