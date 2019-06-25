<?php

namespace Cola\Image;

class Utils
{
    public static $convert = '/usr/bin/convert';
    public static $identify = '/usr/bin/identify';

    /**
     * Minimize image file size
     *
     * @param string $src
     * @param string $des
     * @param string $opts
     * @return boolean
     */
    public static function minimize($src, $des = null, $opts = '')
    {
        if (is_null($des)) $des = $src;
        $dir = dirname($des);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $pathInfo = pathinfo($des);
        $ext = isset($pathInfo['extension']) ? strtolower($pathInfo['extension']) : '';

        if (in_array($ext, ['png'])) {
            $cmd = self::$convert . " {$src} {$opts} -quality 100 {$des}";
        } else if (in_array($ext, ['jpg', 'jpeg'])) {
            $cmd = self::$convert . " {$src} {$opts} -strip -compress JPEG2000 $des";
        } else {
            $cmd = self::$convert . " {$src} {$opts} $des";
        }

        exec($cmd);

        return file_exists($des);
    }

    /**
     * Get image file color number
     *
     * @param string $img
     * @return int
     */
    public static function uniqColors($img)
    {
        $cmd = self::$identify . " -format '%k' {$img}";
        return intval(exec($cmd));
    }
}