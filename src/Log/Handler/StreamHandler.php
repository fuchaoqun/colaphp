<?php

namespace Cola\Log\Handler;

use Exception;
use function fopen;

class StreamHandler extends AbstractHandler
{
    protected $_dirCreated = false;

    /**
     * StreamHandler constructor.
     * @param array $config
     * @throws Exception
     */
    public function __construct($config = [])
    {
        $config += ['stream' => null, 'mode' => 0755, 'lock' => false];
        parent::__construct($config);
        if ((!is_resource($this->_config['stream']))&& (is_string($this->_config['file']))) {
            $this->_createDir();
            $this->_config['stream'] = fopen($this->_config['file'], 'a');
        }
    }

    public function _handle($text)
    {
        if ($this->_config['lock']) {
            flock($this->_config['stream'], LOCK_EX);
        }
        fwrite($this->_config['stream'], $text);
        if ($this->_config['lock']) {
            flock($this->_config['stream'], LOCK_UN);
        }
        return true;
    }

    protected function _getDir()
    {
        $file = $this->_config['file'];
        $pos = strpos($file, '://');
        if ($pos === false) {
            return dirname($file);
        }
        if ('file://' === substr($file, 0, 7)) {
            return dirname(substr($file, 7));
        }
        return null;
    }

    /**
     * @return bool
     * @throws Exception
     */
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

        @mkdir($dir, $this->_config['mode'], true);

        if (file_exists($dir)) {
            $this->_dirCreated = true;
            return true;
        }

        throw new Exception("CAN_NOT_CREATE_DIR_FOR_{$this->_config['file']}");

    }
}