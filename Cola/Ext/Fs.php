<?php
/**
 *
 */

class Cola_Com_Fs
{
    public static function mkdir($dir, $mode = 0755)
    {
        if (is_dir($dir)) return true;
        is_dir(dirname($dir)) || self::mkdir(dirname($dir), $mode);
        if (is_writable(dirname($dir))) {
            return mkdir($dir, $mode);
        } else {
            throw new Exception(dirname($dir) . " can not be written");
        }
    }
}