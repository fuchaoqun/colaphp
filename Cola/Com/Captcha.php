<?php
/**
 *
 */
class Cola_Com_Captcha
{
    /**
     * Error define
     *
     */
    const CAPTCHA_NOT_MATCHED = 1;
    const CAPTCHA_IS_EXPIRED = 2;

    /**
     * Captcha session key
     *
     */
    protected $_sessionValueKey = 'COLA_CAPTCHA_VALUE';
    protected $_sessionTtlKey = 'COLA_CAPTCHA_TTL';

    /**
     * Captcha life time
     *
     * @var int
     */
    protected $_ttl = 90;

    /**
     * Seed
     *
     * @var string
     */
    protected $_seed = '346789ABCDEFGHJKLMNPQRTUVWXYabcdefhjkmnpwxy';

    /**
     * Font
     *
     * @var string
     */
    protected $_font = 'c:\windows\fonts\times.ttf';

    /**
     * Font size
     *
     * @var int
     */
    protected $_size = 20;

    /**
     * Padding
     *
     * @var int
     */
    protected $_padding = 5;

    /**
     * Space between chars
     *
     * @var int
     */
    protected $_space = 5;

    /**
     * Captcha width
     *
     * @var int
     */
    protected $_width = 100;

    /**
     * Captcha height
     *
     * @var int
     */
    protected $_height = 35;

    /**
     * Num of chars in captcha
     *
     * @var int
     */
    protected $_length = 4;

    /**
     * Background color
     *
     * @var string
     */
    protected $_bgColor = '#f8f8f8';

    /**
     * Image
     *
     * @var resource
     */
    protected $_image;

    /**
     * Error
     *
     * @var string
     */
    protected $_error = null;

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct($config = array())
    {
        isset($_SESSION) || session_start();

        $this->_init($config);
    }

    /**
     * Init config
     *
     * @param array $config
     */
    protected function _init($config)
    {
        $keys = array('sessionValueKey', 'sessionTtlKey', 'ttl', 'seed', 'font', 'size', 'width', 'height', 'length', 'bgColor', 'padding');
        foreach ($keys as $key)
        {
            if (isset($config[$key])) {
                $this->{'_' . $key} = $config[$key];
            }
        }
    }

    /**
     * Display captcha
     *
     * @param string $type | png/gif/jpeg
     */
    public function display($type = 'png')
    {
        $this->_image();
        $type = strtolower($type);
        $func = "image$type";

        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header("Content-type: image/$type");

        $func($this->_image);
        imagedestroy($this->_image);
    }

    /**
     * Make image
     *
     */
    protected function _image()
    {
        $this->_image = imagecreate($this->_width, $this->_height);

        imageFilledRectangle($this->_image, 0, 0, $this->_width, $this->_height, $this->_color($this->_bgColor));

        $seed = $this->_seed();

        $_SESSION[$this->_sessionValueKey] = $seed;
        $_SESSION[$this->_sessionTtlKey] = time() + $this->_ttl;

        for ($i = 0; $i < $this->_length; $i++) {
            $text = substr($seed, $i, 1);
            $x = $this->_padding + $i * ($this->_size + $this->_space);
            $y = rand(0.6 * $this->_height, 0.8 * $this->_height);
            $textColor = imageColorAllocate($this->_image, rand(50, 155), rand(50, 155), rand(50, 155));
            imagettftext($this->_image, $this->_size, rand(-18,18), $x, $y, $textColor, $this->_font, $text);
        }

        $this->_noise();
    }

    /**
     * Make seed
     *
     * @return string
     */
    protected function _seed()
    {
        $str = str_shuffle(str_repeat($this->_seed, $this->_length));
        return substr($str, 0, $this->_length);
    }

    /**
     * Colar
     *
     * HEX color to RGB
     * @param string $color
     * @return int | ImageColorAllocate
     */
    protected function _color($color)
    {
        $color = ltrim($color, '#');
        $dec = hexdec($color);
        return ImageColorAllocate($this->_image, 0xFF & ($dec >> 0x10), 0xFF & ($dec >> 0x8), 0xFF & $dec);
    }

    /**
     * Noise
     *
     */
    protected function _noise()
    {
        $pointLimit = rand(128, 192);
        for ($i = 0; $i < $pointLimit; $i++) {
            $x = rand($this->_padding, $this->_width - $this->_padding);
            $y = rand($this->_padding, $this->_height - $this->_padding);
            $color = imagecolorallocate($this->_image, rand(0,255), rand(0,255), rand(0,255));

            imagesetpixel($this->_image, $x, $y, $color);
        }

        $lineLimit = rand(3, 5);
        for($i = 0; $i < $lineLimit; $i++) {
            $x1 = rand($this->_padding, $this->_width - $this->_padding);
            $y1 = rand($this->_padding, $this->_height - $this->_padding);
            $x2 = rand($x1, $this->_width - $this->_padding);
            $y2 = rand($y1, $this->_height - $this->_padding);

            imageline($this->_image, $x1, $y1, $x2, $y2, rand(0, 255));
        }
    }

    /**
     * Check captcha
     *
     * @param string $value
     * @param boolean $caseSensitive
     * @return boolean
     */
    public function check($value, $caseSensitive = false)
    {
        isset($_SESSION) || session_start();
        $expireTime = $_SESSION[$this->_sessionTtlKey];
        $captchaCode = $_SESSION[$this->_sessionValueKey];

        // make captcha session expire
        unset($_SESSION[$this->_sessionTtlKey]);
        unset($_SESSION[$this->_sessionValueKey]);

        if (time() > $expireTime) {
            $this->_error = self::CAPTCHA_IS_EXPIRED;
            return false;
        }

        $func = $caseSensitive ? 'strcmp' : 'strcasecmp';

        if (0 !== $func($value, $captchaCode)) {
            $this->_error = self::CAPTCHA_NOT_MATCHED;
            return false;
        }

        return true;
    }

    /**
     * Get error
     *
     * @return string
     */
    public function error()
    {
        return $this->_error;
    }
}