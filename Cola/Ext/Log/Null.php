<?php
/**
 *
 */

class Cola_Ext_Log_Null extends Cola_Ext_Log_Abstract
{
    protected function _handler($text)
    {
        return;
    }
}