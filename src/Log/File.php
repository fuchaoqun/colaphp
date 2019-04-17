<?php
/**
 *
 */

class Cola_Ext_Log_File extends Cola_Ext_Log_Abstract
{
    public $config = array(
        'mode' => '0755',
        'file' => '/tmp/Cola.log',
    );

    public function write($text)
    {
        $dir = dirname($this->config['file']);
        is_dir($dir) || mkdir($dir, $this->config['mode'], true);
        return file_put_contents($this->config['file'], "{$text}\n", FILE_APPEND | LOCK_EX);
    }
}