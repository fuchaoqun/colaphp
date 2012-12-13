<?php
/**
 *
 */

class Cola_Ext_Log_Echo extends Cola_Ext_Log_Abstract
{
    protected function _handler($text)
    {
        echo $text;
    }
}