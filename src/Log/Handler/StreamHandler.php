<?php

namespace Cola\Log\Handler;

class StreamHandler extends AbstractHandler
{
    protected $_dirCreated = false;

    public function __construct($config = [])
    {
        $config += ['stream' => null, 'mode' => 0755, 'lock' => false];
        parent::__construct($config);
        if ((!is_resource($this->config['stream']))&& (is_string($this->config['file']))) {
            $this->_createDir();
            $this->config['stream'] = \fopen($this->config['file'], 'a');
        }
    }

    public function _handle($text)
    {
        if ($this->config['lock']) {
            flock($this->config['stream'], LOCK_EX);
        }
        fwrite($this->config['stream'], $text);
        if ($this->config['lock']) {
            flock($this->config['stream'], LOCK_UN);
        }
        return true;
    }

    protected function _getDir()
    {
        $file = $this->config['file'];
        $pos = strpos($file, '://');
        if ($pos === false) {
            return dirname($file);
        }
        if ('file://' === substr($file, 0, 7)) {
            return dirname(substr($file, 7));
        }
        return null;
    }

    protected function _createDir()
    {
        if ($this->_dirCreated) {
            return true;
        }

        if (!$dir = $this->_getDir()) {
            return true;
        }


        if (file_exists($dir)) {
            $this->_dirCreated = true;
            return true;
        }

        @mkdir($dir, $this->config['mode'], true);

        if (file_exists($dir)) {
            $this->_dirCreated = true;
            return true;
        }

        throw new \Exception("CAN_NOT_CREATE_DIR_FOR_{$this->config['file']}");

    }
}