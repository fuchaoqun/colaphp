<?php
/**
 *
 */

class Cola_Ext_Upload
{
    /**
     * Upload config
     *
     * @var array
     */
    public $config = array(
        'savePath'     => '/tmp',
        'minSize'      => -1,
        'maxSize'      => -1,
        'minWidth'     => -1,
        'maxWidth'     => -1,
        'minHeight'    => -1,
        'maxHeight'    => -1,
        'allowedExts'  => '*',
        'allowedTypes' => '*',
        'imageExts'    => array('.png', '.jpg', '.gif', '.jpeg'),
        'override'     => false,
        'error'        => array(
            1 => 'Exceeds upload_max_filesize',
            2 => 'Exceeds MAX_FILE_SIZE',
            3 => 'Partially uploaded',
            4 => 'No file was uploaded',
            6 => 'Missing a temporary folder',
            7 => 'Failed to write file to disk',
            8 => 'A PHP extension stopped the file upload'
        )
    );

    /**
     * Formated $_FILES
     *
     * @var array
     */
    public $files = array();

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
    public function __construct($config = array())
    {
        $this->config = $config + $this->config;

        $this->config['savePath'] = rtrim($this->config['savePath'], DIRECTORY_SEPARATOR);

        $this->files();
    }

    /**
     * Format $_FILES
     *
     */
    public function files()
    {
        if ($this->files) return $this->files;

        $files = array();

        foreach ($_FILES as $field => $data) {
            if (empty($data['name'])) continue;
            if (is_string($data['name'])) {
                $files[] = $data + array('field' => $field, 'ext'  => $this->getExt($data['name'], true));
                continue;
            }

            if (!is_array($data['name'])) continue;

            $cnt = count($data['name']);
            $keys = array('name', 'type', 'tmp_name', 'error', 'size');

            for ($i = 0; $i < $cnt; $i++) {
                if (empty($data['name'][$i])) continue;
                $row = array();
                foreach ($keys as $key) {
                    if (!isset($data[$key][$i])) {
                        $row = array();
                        break;
                    }
                    $row[$key] = $data[$key][$i];
                }
                if ($row) {
                    $row['ext'] = $this->getExt($data['name'][$i], true);
                    $files[] = $row;
                }
            }
        }

        foreach ($files as $file) {
            if ($this->check($file)) continue;
            $files = array();
            break;
        }

        $this->files = $files;

        return $this->files;
    }

    /**
     * Save uploaded files
     *
     * @param array $file
     * @return array
     */
    public function save($namedBy = 'Cola_Ext_Upload::defaultName')
    {
        $ret = array();

        foreach ($this->files as $file) {
            $name = is_callable($namedBy, true) ?  call_user_func($namedBy, $file) : null;

            if ($tmp = $this->move($file, $name)) {
                $ret[] = $tmp;
            }
        }

        return $ret;
    }

    /**
     * Default Name function
     *
     * @param array $file
     * @return array
     */
    public static function defaultName($file)
    {
        return date('Ym') . DIRECTORY_SEPARATOR . uniqid('') . $file['ext'];
    }

    /**
     * Move one file
     *
     * @param array $file
     * @param string $name
     * @return boolean
     */
    public function move($file, $name = null)
    {
        if (null === $name) $name = $file['name'];

        $full = $this->config['savePath'] . DIRECTORY_SEPARATOR . $name;

        if (file_exists($full) && !$this->config['override']) {
            $this->error = array('code' => -25, 'msg' => "{$name}: File exited");
            return false;
        }

        $dir = dirname($full);
        if ((!file_exists($dir)) && is_writable(dirname($dir))) {
            mkdir($dir, 0755, true);
        }

        if (is_writable($dir) && move_uploaded_file($file['tmp_name'], $full)) {
            return $name;
        }

        $this->error = array('code' => -26, 'msg' => "{$dir}: Failed to move uploaded file");
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
            $code = $file['error'];
            $this->error = array('code' => 0 - $code, 'msg' => "{$file['name']}: {$this->config['error'][$code]}");
            return false;
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            $this->error = array('code' => -20, 'msg' => "{$file['name']}: Upload failed");
            return false;
        }

        $checks = array(
            'checkType'      => array('code' => -21, 'msg' => "{$file['name']}: File type not allowed"),
            'checkExt'       => array('code' => -22, 'msg' => "{$file['name']}: File ext not allowed"),
            'checkFileSize'  => array('code' => -23, 'msg' => "{$file['name']}: File size not allowed"),
            'checkImageSize' => array('code' => -24, 'msg' => "{$file['name']}: Image size not allowed"),
        );

        foreach ($checks as $func => $error) {
            if (!call_user_func(array($this, $func), $file)) {
                $this->error = $error;
                return false;
            }
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
			$size = @getimagesize($name);
			if (!empty($size)) return array($size[0], $size[1]);
		}

		return false;
    }

    /**
     * Get file extension
     *
     * @param string $fileName
     * @return string
     */
    public static function getExt($name, $withdot = false)
    {
        $pathinfo = pathinfo($name);
        if (isset($pathinfo['extension'])) {
            return ($withdot ? '.' : '' ) . strtolower($pathinfo['extension']);
        }
        return '';
    }

    /**
     * Check file type
     *
     * @param array $file
     * @return boolean
     */
    public function checkType($file)
    {
        $allowedTypes = $this->config['allowedTypes'];
        return ('*' === $allowedTypes) || in_array($file['type'], $allowedTypes);
    }

    /**
     * Check file ext
     *
     * @param array $file
     * @return boolean
     */
    public function checkExt($file)
    {
        $allowedExts = $this->config['allowedExts'];
        return ('*' === $allowedExts) || in_array($file['ext'], $allowedExts);
    }

    /**
     * Check file size
     *
     * @param array $file
     * @return boolean
     */
    public function checkFileSize($file)
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
    public function checkImageSize($file)
    {
        if (!in_array($file['ext'], $this->config['imageExts'])) {
            return true;
        }
        return ($size = $this->getImageSize($file['tmp_name']))
            && ((-1 === $this->config['minWidth'])  || $size[0] >= $this->config['minWidth'])
            && ((-1 === $this->config['maxWidth'])  || $size[0] <= $this->config['maxWidth'])
            && ((-1 === $this->config['minHeight']) || $size[1] >= $this->config['minHeight'])
            && ((-1 === $this->config['maxHeight']) || $size[1] <= $this->config['maxHeight']);
    }
}