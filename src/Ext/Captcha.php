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
        'seed'            => '3478ABCDEFGHJKLMNPQRTUVWXYacdefhjkmnpwxy',
        'fonts'           => array(),
        'size'            => array(16, 24),              // min & max font size
        'width'           => 100,
        'height'          => 35,
        'count'           => array(4, 4),                // min & max num of chars in captcha
        'bgColor'         => '#f8f8f8',
        'ttl'             => 120,
        'points'          => array(256, 512),
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
     * Captcha chars count
     *
     * @var int
     */
    protected $_count;

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct($config = array())
    {
        isset($_SESSION) || session_start();

        $this->config = $config + $this->config;

        if (!is_array($this->config['size'])) {
            $size = intval($this->config['size']);
            $this->config['size'] = array($size, $size);
        }

        if (is_array($this->config['count'])) {
            $this->_count = mt_rand($this->config['count'][0], $this->config['count'][1]);
        } else {
            $this->_count = intval($this->config['count']);
        }
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

        $color = $this->_color($this->config['bgColor']);
        imageFilledRectangle(
            $this->_image, 0, 0, $this->config['width'],
            $this->config['height'], $color
        );

        $seed  = $this->_seed();
        $fonts = $this->_fonts();

        $_SESSION[$this->config['sessionValueKey']] = $seed;
        $_SESSION[$this->config['sessionTtlKey']]   = time() + $this->config['ttl'];

        $avgWidth = $this->config['width'] / $this->_count;

        for ($i = 0; $i < $this->_count; $i++) {
            $char = substr($seed, $i, 1);
            $font = $fonts[$i];
            $angle = mt_rand(-18, 18);
            $size = mt_rand($this->config['size'][0], $this->config['size'][1]);

            $box = imagettfbbox($size, 0, $font, $char);
            $charWidth = abs(max($box[2], $box[4]) - min($box[0], $box[6]));
            $charHeight = abs(max($box[1], $box[3]) - min($box[5], $box[7]));

            $offset = abs($avgWidth - $charWidth);
            $x = $avgWidth * $i + mt_rand($offset / 4,  $offset / 2);
            $offset = abs($this->config['height'] - $charHeight);
            $y = mt_rand($charHeight + $offset / 4, $charHeight + $offset / 2);

            $charColor = imageColorAllocate($this->_image, mt_rand(50, 155), mt_rand(50, 155), mt_rand(50, 155));
            imagettftext($this->_image, $size, $angle, $x, $y, $charColor, $font, $char);
        }

        // $this->_noise();
    }

    /**
     * Make seed
     *
     * @return string
     */
    protected function _seed()
    {
        $str = str_shuffle(str_repeat($this->config['seed'], $this->_count));
        return substr($str, 0, $this->_count);
    }

    /**
     * Random fonts
     *
     * @return array
     */
    protected function _fonts()
    {
        $fonts = $this->config['fonts'];
        for ($i = 0; $i < $this->_count; $i ++) {
            $fonts = array_merge($fonts, $this->config['fonts']);
        }
        shuffle($fonts);
        return array_slice($fonts, 0, $this->_count);
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
        if (empty($color)) {
            return imagecolorallocatealpha($this->_image, 0, 0, 0, 127);
        } else {
            $color = ltrim($color, '#');
            $dec = hexdec($color);
            return ImageColorAllocate($this->_image, 0xFF & ($dec >> 0x10), 0xFF & ($dec >> 0x8), 0xFF & $dec);
        }
    }

    /**
     * Noise
     *
     */
    protected function _noise()
    {

        for($i = 0; $i < $this->config['height']; $i++) {
			for($j = 0; $j < $this->config['width']; $j++) {
				$rgb[$j] = imagecolorat($this->_image, $j , $i);
			}
			for($j = 0; $j < $this->config['width']; $j++) {
				$r = mt_rand(-1, 1);
				// $r = sin($i / $this->config['height'] * 2 * M_PI - M_PI * 0.5) * (-10);
				// $r = 0;
				imagesetpixel($this->_image, $j + $r , $i , $rgb[$j]);
			}
		}

        /**
        $pointLimit = mt_rand($this->config['points'][0], $this->config['points'][1]);
        for ($i = 0; $i < $pointLimit; $i++) {
            $x = mt_rand(0, $this->config['width']);
            $y = mt_rand(0, $this->config['height']);
            $color = imagecolorallocate($this->_image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imagesetpixel($this->_image, $x, $y, $color);
        }
        **/

        /**
        $x1 = mt_rand(0, $this->config['width']/4);
        $y1 = mt_rand($this->config['height']/4, 3 * $this->config['height']/4);
        $x2 = mt_rand($x1, $this->config['width']);
        $y2 = mt_rand($this->config['height']/4, 3 * $this->config['height']/4);

        imagesetthickness($this->_image, 1);
        imageline($this->_image, $x1, $y1, $x2, $y2, mt_rand(0, 255));
        **/
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
        if (empty($_SESSION[$this->config['sessionTtlKey']]) || empty($_SESSION[$this->config['sessionValueKey']])) {
            $this->error = array('code' => -1, 'msg' => 'NO_CAPTCHA_FOUND');
            return false;
        }

        $expireTime  = $_SESSION[$this->config['sessionTtlKey']];
        $captchaCode = $_SESSION[$this->config['sessionValueKey']];

        // clear
        unset($_SESSION[$this->config['sessionTtlKey']]);
        unset($_SESSION[$this->config['sessionValueKey']]);

        if (time() > $expireTime) {
            $this->error = array('code' => -2, 'msg' => 'CAPTCHA_IS_EXPIRED');
            return false;
        }

        $func = $caseSensitive ? 'strcmp' : 'strcasecmp';

        if (0 !== $func($value, $captchaCode)) {
            $this->error = array('code' => -3, 'msg' => 'CAPTCHA_NOT_MATCHED');
            return false;
        }

        return true;
    }
}