<?php
class Cola_Ext_GoogleAuthenticator
{
    public static $ttl   = 30;
    public static $limit = 6;

    // Lookup needed for Base32 encoding
    private static $map = array(
        "A" => 0,  "B" => 1,  "C" => 2,  "D" => 3,
        "E" => 4,  "F" => 5,  "G" => 6,  "H" => 7,
        "I" => 8,  "J" => 9,  "K" => 10, "L" => 11,
        "M" => 12, "N" => 13, "O" => 14, "P" => 15,
        "Q" => 16, "R" => 17, "S" => 18, "T" => 19,
        "U" => 20, "V" => 21, "W" => 22, "X" => 23,
        "Y" => 24, "Z" => 25, "2" => 26, "3" => 27,
        "4" => 28, "5" => 29, "6" => 30, "7" => 31
    );

    /**
     * Get Google Authenticator code
     *
     * @param string $secret
     * @param int $time
     * @return string
     */
    public static function getCode($secret, $time = null)
    {
        if (!$time) $time = floor(time() / 30);
        $secret = self::base32Decode($secret);

        $bin  = pack('N*', 0) . pack('N*', $time);        // Counter must be 64-bit int
        $hash = hash_hmac('sha1', $bin, $secret, true);

        return str_pad(self::truncate($hash, self::$limit), self::$limit, '0', STR_PAD_LEFT);
    }

    /**
     * Check Google Authenticator code
     *
     * @param string $secret
     * @param string $code
     * @package int $window
     * @return boolean
     */
    public static function checkCode($secret, $code, $window = 2)
    {
        $time = floor(time() / 30);
        for ( $i = 0 - $window; $i <= $window; $i++) {
            if ($code == self::getCode($secret,$time + $i)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get Google Authenticator QR image url
     *
     * @param string $user
     * @param string $secret
     * @param string $hostname
     * @return string
     */
    public static function getQrCode($secret, $user, $hostname, $prefix = 'https://www.google.com/chart?chs=200x200&chld=M|0&cht=qr&chl=')
    {
        return $prefix . urlencode("otpauth://totp/{$user}@{$hostname}?secret={$secret}");
    }

    /**
     * Generate random secret
     *
     * @param int $limit
     * @return string
     */
    public static function genSecret($limit = 16)
    {
        return substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'), 0, $limit);
    }

    /**
     * Base32 decode
     *
     * @param string $str
     * @return binary
     */
    public static function base32Decode($str)
    {
        $str = strtoupper($str);

        if (!preg_match('/^[A-Z2-7]+$/', $str)) {
            throw new Exception('Invalid characters in the base32 string.');
        }

        $l = strlen($str);
        $n = 0;
        $j = 0;
        $binary = "";

        for ($i = 0; $i < $l; $i++) {
            $n = $n << 5;                 // Move buffer left by 5 to make room
            $n = $n + self::$map[$str[$i]];     // Add value into buffer
            $j = $j + 5;                // Keep track of number of bits in buffer
            if ($j >= 8) {
                $j = $j - 8;
                $binary .= chr(($n & (0xFF << $j)) >> $j);
            }
        }

        return $binary;
    }

    /**
     * Extracts the OTP from the SHA1 hash.
     * @param binary $hash
     * @return integer
     **/
    public static function truncate($hash, $limit)
    {
        $offset = ord($hash[19]) & 0xf;

        return (
            ((ord($hash[$offset+0]) & 0x7f) << 24 ) |
            ((ord($hash[$offset+1]) & 0xff) << 16 ) |
            ((ord($hash[$offset+2]) & 0xff) << 8 ) |
            (ord($hash[$offset+3]) & 0xff)
        ) % pow(10, $limit);
    }
}