<?php
class Cola_Ext_Image
{
    public static $convert = '/usr/bin/convert';
    public static $identify = '/usr/bin/identify';

    /**
     * 优化图片大小
     *
     * @param string $src
     * @param string $des
     * @return boolean
     */
    public static function minimize($src, $des = null, $opts = '')
    {
        if (is_null($des)) $des = $src;
        $dir = dirname($des);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $ext = substr($des, -4);
        if ('.png' == $ext) {
            $cmd = self::$convert . " {$src} {$opts} -quality 100 {$des}";
            exec($cmd);
        } else if ('.jpg' == $ext) {
            $cmd = self::$convert . " {$src} {$opts} -strip -compress JPEG2000 $des";
            exec($cmd);
        } else {
            copy($src, $des);
        }

        return file_exists($des);
    }

    /**
     * 获得唯一颜色值数
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