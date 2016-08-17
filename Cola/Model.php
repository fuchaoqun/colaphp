<?php
/**
 *
 */
abstract class Cola_Model
{
    protected $_invalidErrorCode = -400;

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
    protected $_ttl = 86400;

    /**
     * Validator rules
     *
     * @var array
     */
    protected $_rules = array();

    /**
     * Error infomation
     *
     * @var array
     */
    public $error = array();

    public function __construct() {}

    /**
     * Load data
     *
     * @param int $id
     * @return array
     */
    public function load($id, $col = null)
    {
        is_null($col) && $col = $this->_pk;

        $sql = "select * from {$this->_table} where {$col} = ? limit 1";

        try {
            $result = $this->db->sql($sql, array($id));
            return empty($result) ? null : $result[0];
        } catch (Exception $e) {
            $this->error = array('code' => $e->getCode(), 'msg' => $e->getMessage());
            return false;
        }
    }

    /**
     * Multi load data
     *
     * @param int $ids
     * @return array
     */
    public function mload($ids, $col = null)
    {
        is_null($col) && $col = $this->_pk;
        if (empty($ids)) {
            return null;
        }
        $bind = implode(',', array_fill(0, count($ids), '?'));
        $sql = "select * from {$this->_table} where {$col} in ({$bind})";
        try {
            if ($raw = $this->db->sql($sql, $ids)) {
                $result = array();
                foreach ($raw as $row) {
                    $result[$row[$col]] = $row;
                }
                return $result;
            } else {
                return null;
            }
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
    public function count($where)
    {
        try {
            $result = $this->db->count($this->_table, $where);
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
     * @param array $data
     * @return array
     */
    public function sql($sql, $data = array())
    {
        try {
            $result = $this->db->sql($sql, $data);
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
    public function insert($data)
    {
        try {
            $result = $this->db->insert($this->_table, $data);
            return $result;
        } catch (Exception $e) {
            $this->error = array('code' => $e->getCode(), 'msg' => $e->getMessage());
            return false;
        }
    }

    /**
     * Replace
     *
     * @param array $data
     * @param string $table
     * @return boolean
     */
    public function replace($data)
    {
        try {
            $result = $this->db->replace($this->_table, $data);
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
        $where = array("{$this->_pk}=?", array($id));

        try {
            $result = $this->db->update($this->_table, $data, $where);
            return true;
        } catch (Exception $e) {
            $this->error = array('code' => $e->getCode(), 'msg' => $e->getMessage());
            return false;
        }
    }

    public function mupdate($todos)
    {
        foreach ($todos as $id => $data) {
            if (!$this->update($id, $data)) {
                return false;
            }
        }

        return true;
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
        $sql = "delete from {$this->_table} where {$col} = ?";

        try {
            return $this->db->sql($sql, array($id));
        } catch (Exception $e) {
            $this->error = array('code' => $e->getCode(), 'msg' => $e->getMessage());
            return false;
        }
    }

    public function del($id, $col = null)
    {
        return $this->delete($id, $col = null);
    }

    public function mdel($ids, $col = null)
    {

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
            $name += array('user' => '', 'password' => '', 'options' => array());
            return new Cola_Ext_Pdo(
                $name['dsn'], $name['user'], $name['password'], $name['options']
            );
        }

        $regName = "_cola_db_{$name}";
        if (!$db = Cola::getReg($regName)) {
            $config = (array)Cola::getConfig($name)
                    + array('user' => '', 'password' => '', 'options' => array());
            $db = new Cola_Ext_Pdo(
                $config['dsn'], $config['user'], $config['password'], $config['options']
            );
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
     * @param string $key
     * @return mixed
     */
    public function cached($func, $args = array(), $ttl = null, $key = null)
    {
        is_null($ttl) && ($ttl = $this->_ttl);

        if (!is_array($args)) {
            $args = array($args);
        }

        if (is_null($key)) {
            $key = get_class($this) . '-' . $func . '-' . sha1(serialize($args));
        }

        if (!$data = $this->cache->get($key)) {
            $data = json_encode(call_user_func_array(array($this, $func), $args));
            $this->cache->set($key, $data, $ttl);
        }

        return json_decode($data, true);
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
        is_null($rules) && $rules = $this->_rules;
        if (empty($rules)) {
            return true;
        }

        $validator = new Cola_Ext_Validator();

        $result = $validator->check($data, $rules, $ignoreNotExists);

        if (!$result) {
            $this->error = array(
                'code' => $this->_invalidErrorCode,
                'msg'  => current($validator->errors),
                'ref' => $validator->errors
            );
            return false;
        }

        return true;
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
