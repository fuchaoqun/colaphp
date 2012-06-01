<?php
/**
 *
 */

class Cola_Com_Log_Echo extends Cola_Com_Log_Abstract
{
    protected function _handler($text)
    {
        echo $text;
    }
}