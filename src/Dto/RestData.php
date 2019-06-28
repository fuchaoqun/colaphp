<?php


namespace Cola\Dto;


class RestData extends RestResponse
{
    public function __construct($data = null, $message = null)
    {
        $this->_code = 200;

        if (!is_null($data)) {
            $this->_data = $data;
        }
        if (!is_null($message)) {
            $this->_message = $message;
        }
    }
}