<?php
/**
 *
 */
abstract class Cola_Model
{
    /**
     * Db name
     *
     * @var string
     */
    protected $_db = '_db';

    /**
     * Table name, with prefix and main name
     *
     * @var string
     */
    protected $_table;

    /**
     * Primary key
     *
     * @var string
     */
    protected $_pk = 'id';

    /**
     * Cache config
     *
     * @var mixed, string for config key and array for config
     */
    protected $_cache = '_cache';

    /**
     * Cache expire time
     *
     * @var int
     */
    protected $_ttl = 60;

    /**
     * Validate rules
     *
     * @var array
     */
    protected $_validate = array();

    /**
     * Error infomation
     *
     * @var array
     */
    public $error = array();

    /**
     * Load data
     *
     * @param int $id
     * @return array
     */
    public function load($id, $col = null)
    {
        is_null($col) && $col = $this->_pk;

        $sql = "select * from {$this->_table} where {$col} = '{$id}'";

        try {
            $result = $this->db->row($sql);
            return $result;
        } catch (Exception $e) {
            $this->error = array('code' => $e->getCode(), 'msg' => $e->getMessage());
            return false;
        }
    }

    /**
     * Find result
     *
     * @param array $conditions
     * @return array
     */
    public function find($conditions = array())
    {
        is_string($conditions) && $conditions = array('where' => $conditions);

        $conditions += array('table' => $this->_table);

        try {
            $result = $this->db->find($conditions);
            return $result;
        } catch (Exception $e) {
            $this->error = array('code' => $e->getCode(), 'msg' => $e->getMessage());
            return false;
        }
    }

    /**
     * Count result
     *
     * @param string $where
     * @param string $table
     * @return int
     */
    public function count($where, $table = null)
    {
        is_null($table) && $table = $this->_table;

        try {
            $result = $this->db->count($where, $table);
            return $result;
        } catch (Exception $e) {
            $this->error = array('code' => $e->getCode(), 'msg' => $e->getMessage());
            return false;
        }
    }

    /**
     * Query SQL
     *
     * @param string $sql
     * @return mixed
     */
    public function query($sql)
    {
        try {
            $result = $this->db->query($sql);
            return $result;
        } catch (Exception $e) {
            $this->error = array('code' => $e->getCode(), 'msg' => $e->getMessage());
            return false;
        }
    }

    /**
     * Get SQL result
     *
     * @param string $sql
     * @return array
     */
    public function sql($sql)
    {
        try {
            $result = $this->db->sql($sql);
            return $result;
        } catch (Exception $e) {
            $this->error = array('code' => $e->getCode(), 'msg' => $e->getMessage());
            return false;
        }
    }

    /**
     * Insert
     *
     * @param array $data
     * @param string $table
     * @return boolean
     */
    public function insert($data, $table = null)
    {
        is_null($table) && $table = $this->_table;

        try {
            $result = $this->db->insert($data, $table);
            return $result;
        } catch (Exception $e) {
            $this->error = array('code' => $e->getCode(), 'msg' => $e->getMessage());
            return false;
        }
    }

    /**
     * Update
     *
     * @param int $id
     * @param array $data
     * @return boolean
     */
    public function update($id, $data)
    {
        $where = $this->_pk . '=' . (is_int($id) ? $id : "'$id'");

        try {
            $result = $this->db->update($data, $where, $this->_table);
            return true;
        } catch (Exception $e) {
            $this->error(array('code' => self::SYSTEM_ERROR, 'msg' => $e->getMessage()));
            return false;
        }
    }

    /**
     * Delete
     *
     * @param string $where
     * @param string $table
     * @return boolean
     */
    public function delete($id, $col = null)
    {
        is_null($col) && $col = $this->_pk;
        $id = $this->escape($id);
        $where = "{$col} = '{$id}'";

        try {
            $result = $this->db->delete($where, $this->_table);
            return $result;
        } catch (Exception $e) {
            $this->error = array('code' => $e->getCode(), 'msg' => $e->getMessage());
            return false;
        }
    }

    /**
     * Escape string
     *
     * @param string $str
     * @return string
     */
    public function escape($str)
    {
        return $this->db->escape($str);
    }

    /**
     * Connect db from config
     *
     * @param array $config
     * @param string
     * @return Cola_Ext_Db
     */
    public function db($name = null)
    {
        is_null($name) && $name = $this->_db;

        if (is_array($name)) {
            return Cola::factory('Cola_Ext_Db', $name);
        }

        $regName = "_cola_db_{$name}";
        if (!$db = Cola::getReg($regName)) {
            $config = (array)Cola::getConfig($name) + array('adapter' => 'Pdo_Mysql');
            $db = Cola::factory('Cola_Ext_Db', $config);
            Cola::setReg($regName, $db);
        }

        return $db;
    }

    /**
     * Init Cola_Ext_Cache
     *
     * @param mixed $name
     * @return Cola_Ext_Cache
     */
    public function cache($name = null)
    {
        is_null($name) && ($name = $this->_cache);

        if (is_array($name)) {
            return Cola::factory('Cola_Ext_Cache', $name);
        }

        $regName = "_cola_cache_{$name}";
        if (!$cache = Cola::getReg($regName)) {
            $config = (array)Cola::getConfig($name);
            $cache = Cola::factory('Cola_Ext_Cache', $config);
            Cola::setReg($regName, $cache);
        }

        return $cache;
    }

    /**
     * Get function cache
     *
     * @param string $func
     * @param mixed $args
     * @param int $ttl
     * @return mixed
     */
    public function cached($func, $args = array(), $ttl = null)
    {
        is_null($ttl) && ($ttl = $this->_ttl);

        if (!is_array($args)) {
            $args = array($args);
        }

        $key = md5(get_class($this) . $func . serialize($args));

        if (!$data = $this->cache->get($key)) {
            $data = call_user_func_array(array($this, $func), $args);
            $this->cache->set($key, $data, $ttl);
        }

        return $data;
    }

    /**
     * Validate
     *
     * @param array $data
     * @param boolean $ignoreNotExists
     * @param array $rules
     * @return boolean
     */
    public function validate($data, $ignoreNotExists = false, $rules = null)
    {
        is_null($rules) && $rules = $this->_validate;

        $validate = new Cola_Ext_Validate();

        $result = $validate->check($data, $rules, $ignoreNotExists);

        if (!$result) {
            $this->_error = array('code' => self::VALIDATE_ERROR, 'msg' => $validate->errors);
        }

        return $result;
    }

    /**
     * Dynamic set vars
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value = null)
    {
        $this->$key = $value;
    }

    /**
     * Dynamic get vars
     *
     * @param string $key
     */
    public function __get($key)
    {
        switch ($key) {
            case 'db' :
                $this->db = $this->db();
                return $this->db;

            case 'cache' :
                $this->cache = $this->cache();
                return $this->cache;

            case 'config':
                $this->config = Cola::getInstance()->config;
                return $this->config;

            default:
                throw new Cola_Exception('Undefined property: ' . get_class($this). '::' . $key);
        }
    }
}