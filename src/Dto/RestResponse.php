<?php


namespace Cola\Dto;


use Cola\Http\Response;
use Cola\I18n\Translator;

abstract class RestResponse
{
    public $_code;
    public $_data = null;
    public $_message = null;

    public function display()
    {
        $json = $this->toString();

        if (isset($_GET['_callback']) && (preg_match('/^[a-zA-Z\d_]+$/', $_GET['_callback']))) {
            Response::charset('utf-8', 'application/javascript');
            echo "{$_GET['_callback']}({$json});";
        } else if (isset($_GET['_var']) && (preg_match('/^[a-zA-Z\d_]+$/', $_GET['_var']))) {
            Response::charset('utf-8', 'application/javascript');
            echo " var {$_GET['_var']}={$json};";
        } else {
            Response::charset('utf-8', 'application/json');
            echo $json;
        }
    }

    public function i18n($replacements = [], $locales = null)
    {
        if (!is_null($this->_message)) {
            $this->_message = Translator::getFromContainer()->message($this->_message, $replacements, $locales);
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * @param mixed $code
     * @return RestResponse
     */
    public function setCode($code)
    {
        $this->_code = $code;
        return $this;
    }

    /**
     * @return null
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * @param null $data
     * @return RestResponse
     */
    public function setData($data)
    {
        $this->_data = $data;
        return $this;
    }

    /**
     * @return null
     */
    public function getMessage()
    {
        return $this->_message;
    }

    /**
     * @param null $message
     * @return RestResponse
     */
    public function setMessage($message)
    {
        $this->_message = $message;
        return $this;
    }

    public function toString()
    {
        $rps = ['code' => $this->_code];
        if (!is_null($this->_data)) $rps['data'] = $this->_data;
        if (!is_null($this->_message)) $rps['message'] = $this->_message;

        return json_encode($rps, JSON_UNESCAPED_UNICODE);
    }

    public function __toString()
    {
        return $this->toString();
    }
}