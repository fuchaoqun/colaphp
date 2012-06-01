<?php
/**
 *
 */

class Cola_Com_Upload
{
    /**
     * Upload error message
     *
     * @var array
     */
    protected $_message = array(
        1 => 'upload_file_exceeds_limit',
        2 => 'upload_file_exceeds_form_limit',
        3 => 'upload_file_partial',
        4 => 'upload_no_file_selected',
        6 => 'upload_no_temp_directory',
        7 => 'upload_unable_to_write_file',
        8 => 'upload_stopped_by_extension'
    );

    /**
     * Upload config
     *
     * @var array
     */
    protected $_config = array(
        'savePath' => '/tmp',
        'maxSize' => 0,
        'maxWidth' => 0,
        'maxHeight' => 0,
        'allowedExts' => '*',
        'allowedTypes' => '*',
        'override' => false,
    );

    /**
     * The num of successfully uploader files
     *
     * @var int
     */
    protected $_num = 0;

    /**
     * Formated $_FILES
     *
     * @var array
     */
    protected $_files = array();

    /**
     * Error
     *
     * @var array
     */
    protected $_error;

    /**
     * Constructor
     *
     * Construct && formate $_FILES
     * @param array $config
     */
    public function __construct($config = array())
    {
        $this->_config += $config;

        $this->_config['savePath'] = rtrim($this->_config['savePath'], DIRECTORY_SEPARATOR);

        $this->_format();
    }

    /**
     * Config
     *
     * Set or get configration
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function config($name = null, $value = null)
    {
        if (null == $name) {
            return $this->_config;
        }

        if (null == $value) {
            return isset($this->_config[$name]) ? $this->_config[$name] : null;
        }

        $this->_config[$name] = $value;

        return $this;
    }

    /**
     * Format $_FILES
     *
     */
    protected function _format()
    {
        foreach ($_FILES as $field => $file) {

            if (empty($file['name'])) continue;

            if (is_array($file['name'])) {
                $cnt = count($file['name']);

                for ($i = 0; $i < $cnt; $i++) {
                    if (empty($file['name'][$i])) continue;
                    $this->_files[] = array(
                        'field' => $field,
                        'name' => $file['name'][$i],
                        'type' => $file['type'][$i],
                        'tmp_name' => $file['tmp_name'][$i],
                        'error' => $file['error'][$i],
                        'size' => $file['size'][$i],
                        'ext'  => $this->getExt($file['name'][$i], true)
                    );
                }

            } else {
                $this->_files[] = $file + array('field' => $field, 'ext'  => $this->getExt($file['name'], true));
            }
        }
    }

    /**
     * Save uploaded files
     *
     * @param array $file
     * @param string $name
     * @return boolean
     */
    public function save($file = null, $name = null)
    {
        if (!is_null($file)) {
            return $this->_move($file, $name);
        }

        $return = true;

        foreach ($this->_files as $file) {
            $return = $return && $this->_move($file);
        }

        return $return;
    }

    /**
     * Move file
     *
     * @param array $file
     * @param string $name
     * @return boolean
     */
    protected function _move($file, $name = null)
    {
        if (!$this->check($file)) {
            return false;
        }

        if (null === $name) $name = $file['name'];
        $fileFullName = $this->_config['savePath'] . DIRECTORY_SEPARATOR . $name;

        if (file_exists($fileFullName) && !$this->_config['override']) {
            $msg = 'file_already_exits:' . $fileFullName;
            $this->_error[] = $msg;
            return false;
        }

        $dir = dirname($fileFullName);
        is_dir($dir) || Cola_Com_Fs::mkdir($dir);

        if (is_writable($dir) && move_uploaded_file($file['tmp_name'], $fileFullName)) {
            $this->_num++;
            return true;
        }

        $this->_error[] = 'move_uploaded_file_failed:' . $dir . 'may not be writeable.';
        return false;
    }

    /**
     * Check file
     *
     * @param array $file
     * @return string
     */
    public function check($file)
    {
        if (UPLOAD_ERR_OK != $file['error']) {
            $this->_error[] = $this->_message[$file['error']] . ':' . $file['name'];
            return false;
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            $this->_error[] = 'file_upload_failed:' . $file['name'];
            return false;
        }

        if (!$this->checkType($file, $this->_config['allowedTypes'])) {
            $this->_error[] = 'file_type_not_allowed:' . $file['name'];
            return false;
        }

        if (!$this->checkExt($file, $this->_config['allowedExts'])) {
            $this->_error[] = 'file_ext_not_allowed:' . $file['name'];
            return false;
        }

        if (!$this->checkFileSize($file, $this->_config['maxSize'])) {
            $this->_error[] = 'file_size_not_allowed:' . $file['name'];
            return false;
        }

        if ($this->isImage($file) && !$this->checkImageSize($file, array($this->_config['maxWidth'], $this->_config['maxHeight']))) {
            $this->_error[] = 'image_size_not_allowed:' . $file['name'];
            return false;
        }

        return true;
    }

    /**
     * Get image size
     *
     * @param string $file
     * @return array like array(x, y),x is width, y is height
     */
    public function getImageSize($name)
    {
        if (function_exists('getimagesize')) {
			$size = getimagesize($name);
			return array($size[0], $size[1]);
		}

		return false;
    }

    /**
     * Get file extension
     *
     * @param string $fileName
     * @return string
     */
    public function getExt($name, $withdot = false)
    {
        $pathinfo = pathinfo($name);
        if (isset($pathinfo['extension'])) {
            return ($withdot ? '.' : '' ) . $pathinfo['extension'];
        }
        return '';
    }

    /**
     * Check if is image
     *
     * @param string $type
     * @param string $imageTypes
     * @return boolean
     */
    public function isImage($file)
    {
        return 'image' == substr($file['type'], 0, 5);
    }

    /**
     * Check file type
     *
     * @param string $type
     * @param string $allowedTypes
     * @return boolean
     */
    public function checkType($file, $allowedTypes)
    {
        return ('*' == $allowedTypes || false !== stripos($allowedTypes, $file['type'])) ? true :false;
    }

    /**
     * Check file ext
     *
     * @param string $ext
     * @param string $allowedExts
     * @return boolean
     */
    public function checkExt($file, $allowedExts)
    {
        return ('*' == $allowedExts || false !== stripos($allowedExts, $this->getExt($file['name']))) ? true :false;
    }

    /**
     * Check file size
     *
     * @param int $size
     * @param int $maxSize
     * @return boolean
     */
    public function checkFileSize($file, $maxSize)
    {
        return 0 === $maxSize || $file['size'] <= $maxSize;
    }

    /**
     * Check image size
     *
     * @param array $size
     * @param array $maxSize
     * @return unknown
     */
    public function checkImageSize($file, $maxSize)
    {
        $size = $this->getImageSize($file['tmp_name']);
        return (0 === $maxSize[0] || $size[0] <= $maxSize[0]) && (0 === $maxSize[1] || $size[1] <= $maxSize[1]);
    }

    /**
     * Get formated files
     *
     * @return array
     */
    public function files()
    {
        return $this->_files;
    }

    /**
     * Get the num of sucessfully uploaded files
     *
     * @return int
     */
    public function num()
    {
        return $this->_num;
    }

    /**
     * Get upload error
     *
     * @return array
     */
    public function error()
    {
        return $this->_error;
    }
}