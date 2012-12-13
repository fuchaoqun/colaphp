<?php
/**
 *
 */
class Cola_Ext_Captcha
{
    /**
     * Captch config
     *
     * @var array
     */
    public $config = array(
        'type'            => 'png',
        'seed'            => '34678ABCDEFGHJKLMNPQRTUVWXYabcdefhjkmnpwxy',
        'fonts'           => array('c:\windows\fonts\times.ttf'),
        'size'            => 20,
        'padding'         => 5,
        'space'           => 5,                // Space between chars
        'width'           => 100,
        'height'          => 35,
        'length'          => 4,                //Num of chars in captcha
        'bgColor'         => '#f8f8f8',
        'ttl'             => 90,
        'minPoints'       => 256,
        'maxPoints'       => 512,
        'sessionValueKey' => '_COLA_CAPTCHA_VALUE_',
        'sessionTtlKey'   => '_COLA_CAPTCHA_TTL_',
    );

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
    public $error = null;

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct($config = array())
    {
        isset($_SESSION) || session_start();

        $this->config = $config + $this->config;
    }

    /**
     * Display captcha
     *
     * @param string $type | png/gif/jpeg
     */
    public function display()
    {
        $this->_image();
        $type = strtolower($this->config['type']);
        $func = "image{$type}";

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
        $this->_image = imagecreate($this->config['width'], $this->config['height']);

        imageFilledRectangle(
            $this->_image, 0, 0, $this->config['width'],
            $this->config['height'], $this->_color($this->config['bgColor'])
        );

        $seed  = $this->_seed();
        $fonts = $this->_fonts();

        $_SESSION[$this->config['sessionValueKey']] = $seed;
        $_SESSION[$this->config['sessionTtlKey']]   = time() + $this->config['ttl'];

        for ($i = 0; $i < $this->config['length']; $i++) {
            $char = substr($seed, $i, 1);
            $x = $this->config['padding'] + $i * ($this->config['size'] + $this->config['space']);
            $y = mt_rand(0.6 * $this->config['height'], 0.8 * $this->config['height']);
            $charColor = imageColorAllocate($this->_image, mt_rand(50, 155), mt_rand(50, 155), mt_rand(50, 155));
            imagettftext($this->_image, $this->config['size'], mt_rand(-18,18), $x, $y, $charColor, $fonts[$i], $char);
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
        $str = str_shuffle(str_repeat($this->config['seed'], $this->config['length']));
        return substr($str, 0, $this->config['length']);
    }

    /**
     * Random fonts
     *
     * @return array
     */
    protected function _fonts()
    {
        $fonts = $this->config['fonts'];
        for ($i = 0; $i < $this->config['length']; $i ++) {
            $fonts = array_merge($fonts, $this->config['fonts']);
        }
        shuffle($fonts);
        return array_slice($fonts, 0, $this->config['length']);
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
        $pointLimit = mt_rand($this->config['minPoints'], $this->config['maxPoints']);
        for ($i = 0; $i < $pointLimit; $i++) {
            $x = mt_rand($this->config['padding'], $this->config['width'] - $this->config['padding']);
            $y = mt_rand($this->config['padding'], $this->config['height'] - $this->config['padding']);
            $color = imagecolorallocate($this->_image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imagesetpixel($this->_image, $x, $y, $color);
        }

        $x1 = mt_rand($this->config['padding'], $this->config['width']/4);
        $y1 = mt_rand($this->config['height']/4, 3 * $this->config['height']/4);
        $x2 = mt_rand($this->config['width']/2 + $x1, $this->config['width'] - $this->config['padding']);
        $y2 = mt_rand($this->config['height']/4, 3 * $this->config['height']/4);

        imagesetthickness($this->_image, 2);
        imageline($this->_image, $x1, $y1, $x2, $y2, mt_rand(0, 255));
    }

    /**
     * Check captcha
     *
     * @param string $value
     * @param boolean $caseSensitive
     * @param boolean $once automatic remove
     * @return boolean
     */
    public function check($value, $caseSensitive = false, $once = true)
    {
        if (empty($_SESSION[$this->config['sessionTtlKey']]) || empty($_SESSION[$this->config['sessionValueKey']])) {
            $this->_error = array('code' => -1, 'msg' => 'NO_CAPTCHA_FOUND');
            return false;
        }

        $expireTime = $_SESSION[$this->config['sessionTtlKey']];
        $captchaCode = $_SESSION[$this->config['sessionValueKey']];

        if ($once) {
            unset($_SESSION[$this->config['sessionTtlKey']]);
            unset($_SESSION[$this->config['sessionValueKey']]);
        }

        if (time() > $expireTime) {
            $this->_error = array('code' => -2, 'msg' => 'CAPTCHA_IS_EXPIRED');
            return false;
        }

        $func = $caseSensitive ? 'strcmp' : 'strcasecmp';

        if (0 !== $func($value, $captchaCode)) {
            $this->_error = array('code' => -3, 'msg' => 'CAPTCHA_NOT_MATCHED');
            return false;
        }

        return true;
    }
}