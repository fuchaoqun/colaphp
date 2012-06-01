<?php
/**
 *
 */
class Cola_Com_Cache_File extends Cola_Com_Cache_Abstract
{
    protected $_options = array(
        'cache_dir' => '/tmp',
        'cache_dir_depth' => 1,
        'cache_md5_code' => ''
    );

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options=array())
    {
        $this->_options = $options + $this->_options;
        $this->_options['cache_dir'] = rtrim($this->_options['cache_dir'], '\\/');
    }

    /**
     * Set cache
     *
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return boolean
     */
    public function set($key, $value)
    {
        if (!is_string($value)) $value = serialize($value);
        $file = $this->_file($key);
        $dir = dirname($file);
        is_dir($dir) || Cola_Com_Fs::mkdir($dir);
        return file_put_contents($file, $value) ? true : false;
    }

    /**
     * Get Cache
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        $file = $this->_file($key);
        return is_file($file) ? file_get_contents($file) : false;
    }

    /**
     * Delete cache
     * @param string $id
     * @return boolean
     */
    public function delete($key)
    {
        $file = $this->_file($key);
        return is_file($file) ? unlink($file) : true;
    }

    /**
     * Get file by key
     *
     * @param string $key
     * @return string
     */
    protected function _file($key)
    {
        $md5 = md5($key . $this->_options['cache_md5_code']);
        $dir = $this->_options['cache_dir'];
        for ($i = 0; $i < $this->_options['cache_dir_depth']; $i ++) {
            $dir .= DIRECTORY_SEPARATOR . substr($md5, ($i - 1) * 2, 2);
        }

        $file = $dir . DIRECTORY_SEPARATOR . $md5 . '.tmp';

        return $file;
    }

    /**
     * clear cache
     */
    public function clear()
    {
        throw new Exception('use system command to clear all cache.');
    }
}
?>