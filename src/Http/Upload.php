<?php

namespace Cola\Http;

class Upload
{
    /**
     * Upload config
     *
     * @var array
     */
    public $config = [
        'minSize'      => -1,
        'maxSize'      => -1,
        'minWidth'     => -1,
        'maxWidth'     => -1,
        'minHeight'    => -1,
        'maxHeight'    => -1,
        'allowedExts'  => ['*'],
        'allowedTypes' => ['*'],
        'imageExts'    => ['png', 'jpg', 'gif', 'jpeg', 'bmp', 'webp'],
        'override'     => false,
        'error'        => [
            1 => 'Exceeds upload_max_filesize',
            2 => 'Exceeds MAX_FILE_SIZE',
            3 => 'Partially uploaded',
            4 => 'No file was uploaded',
            6 => 'Missing a temporary folder',
            7 => 'Failed to write file to disk',
            8 => 'A PHP extension stopped the file upload'
        ]
    ];

    /**
     * Formated $_FILES
     *
     * @var array
     */
    public $files = [];

    /**
     * Error
     *
     * @var array
     */
    public $error;

    /**
     * Constructor
     *
     * Construct && formate $_FILES
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->config = $config + $this->config;

        $this->config['dir'] = rtrim($this->config['dir'], DIRECTORY_SEPARATOR);

        $this->_init();
    }

    /**
     * Format $_FILES
     *
     */
    private function _init()
    {
        $files = Request::getUploadedFiles();

        foreach ($files as $file) {
            $this->_check($file) && $this->files[] = $file;
        }
    }

    /**
     * Check file
     *
     * @param array $file
     * @return string
     */
    private function _check($file)
    {
        if (UPLOAD_ERR_OK != $file['error']) {
            throw new \Exception("{$file['name']}: {$this->config['error'][$code]}", $code);
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            throw new \Exception("{$file['name']}: NOT_UPLOADED_FILE", 20);
        }

        $checks = [
            '_checkType'      => ['code' => 21, 'message' => "{$file['name']}: NOT_ALLOWED_TYPE"],
            '_checkExt'       => ['code' => 22, 'message' => "{$file['name']}:NOT_ALLOWED_EXT"],
            '_checkFileSize'  => ['code' => 23, 'message' => "{$file['name']}: NOT_ALLOWED_FILE_SIZE"],
            '_checkImageSize' => ['code' => 24, 'message' => "{$file['name']}: NOT_ALLOWED_IMAGE_SIZE"],
        ];

        foreach ($checks as $func => $error) {
            if (!$this->$func($file)) {
                throw new \Exception($error['message'], $error['code']);
            }
        }

        return true;
    }

    /**
     * Check file type
     *
     * @param array $file
     * @return boolean
     */
    private function _checkType($file)
    {
        $allowedTypes = $this->config['allowedTypes'];
        return in_array('*', $allowedTypes) || in_array($file['type'], $allowedTypes);
    }

    /**
     * Check file ext
     *
     * @param array $file
     * @return boolean
     */
    private function _checkExt($file)
    {
        $allowedExts = $this->config['allowedExts'];
        return in_array('*', $allowedExts) || in_array($file['ext'], $allowedExts);
    }

    /**
     * Check file size
     *
     * @param array $file
     * @return boolean
     */
    public function _checkFileSize($file)
    {
        $minSize = $this->config['minSize'];
        $maxSize = $this->config['maxSize'];
        return (-1 === $minSize || $file['size'] >= $minSize) && (-1 === $maxSize || $file['size'] <= $maxSize);
    }

    /**
     * Check image size
     *
     * @param array $file
     * @return boolean
     */
    public function _checkImageSize($file)
    {
        if (!in_array($file['ext'], $this->config['imageExts'])) {
            return true;
        }
        return ($size = $this->_getImageSize($file['tmp_name']))
            && ((-1 === $this->config['minWidth'])  || $size[0] >= $this->config['minWidth'])
            && ((-1 === $this->config['maxWidth'])  || $size[0] <= $this->config['maxWidth'])
            && ((-1 === $this->config['minHeight']) || $size[1] >= $this->config['minHeight'])
            && ((-1 === $this->config['maxHeight']) || $size[1] <= $this->config['maxHeight']);
    }

    /**
     * Get image size
     *
     * @param string $file
     * @return array like array(x, y),x is width, y is height
     */
    private function _getImageSize($name)
    {
        $size = @getimagesize($name);
		return empty($size) ? false : [$size[0], $size[1]];
    }

    /**
     * Move one file
     *
     * @param array $file
     * @param string $name
     * @return boolean
     */
    public function move($file, $dest)
    {
        if (file_exists($dest) && (!$this->config['override'])) {
            throw new \Exception(25, "{$dest}: FILE_EXISTED");
        }

        $dir = dirname($dest);
        if (!file_exists($dest)) {
            mkdir($dir, 0755, true);
        }

        if (is_writable($dir) && move_uploaded_file($file['tmp_name'], $dest)) {
            return true;
        }

        throw new \Exception(26, "{$file['tmp_name']}: MOVE_FAILED");
    }
}