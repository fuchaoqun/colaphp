<?php
/**
 *
 */

class Cola_Com_Log_Null extends Cola_Com_Log_Abstract
{
    protected function _handler($text)
    {
        return;
    }
}