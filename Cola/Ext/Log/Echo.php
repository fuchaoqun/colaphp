<?php
/**
 *
 */

class Cola_Ext_Log_Echo extends Cola_Ext_Log_Abstract
{
    public function write($text)
    {
        echo $text;
    }
}