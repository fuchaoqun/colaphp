<?php
/**
 *
 */
class Cola_Com_Cache_Dba extends Cola_Com_Cache_Abstract
{
	protected $_options = array(
		'dba_file' => '/tmp/dba.db',
		'dba_type' => 'db4'
	);

	protected $_rHandler = null;
	protected $_wHandler = null;

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
		return dba_replace($key, $value, $this->_handler('w'));
	}

	/**
     * Get Cache
     *
     * @param string $key
     * @return mixed
     */
	public function get($key)
	{
		return dba_fetch($key, $this->_handler('r'));
	}

	/**
     * Delete cache
     * @param string $id
     * @return boolean
     */
	public function delete($key)
	{
	    if (!dba_exists($key, $this->_handler('r'))) return true;
	    return dba_delete($key, $this->_handler('w'));
	}

	public function clear()
	{
		return unlink($this->_options['dba_file']);
	}

	protected function close()
	{
		if ($this->_handler) {
			dba_close($this->_handler);
		}
	}

	protected function _handler($mode = 'r')
	{
	    $handler = '_' . $mode . 'Handler';
	    if (null === $this->$handler) {
	        switch ($mode) {
    	        case 'r':
    	            $this->_rHandler = dba_popen($this->_options['dba_file'], 'r', $this->_options['dba_type']);
    	            break;
    	        case 'w':
    	            $this->_wHandler = dba_open($this->_options['dba_file'], 'c', $this->_options['dba_type']);
    	            $this->_rHandler = $this->_wHandler;
    	            break;
    	        default:
    	            throw new Exception('Not support mode, mode must be r or w.');
    	    }
	    }
	    return $this->$handler;
	}
}
