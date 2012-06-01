<?php
/**
 *
 */

class Cola_Com_Db_Masterslave extends Cola_Com_Db_Abstract
{
    /**
     * Configuration
     *
     * @var array
     */
    protected $_config = array();

    /**
     * Master MySQL Adapter
     *
     * @var Cola_Com_Db_Abstract
     */
    protected $_master = null;

    /**
     * Slave MySQL Adapter
     *
     * @var Cola_Com_Db_Abstract
     */
    protected $_slave = null;

    /**
     * Current MySQL Adapter
     *
     * @var Cola_Com_Db_Abstract
     */
    protected $_mysql = null;

    /**
     * Database connection
     *
     * @var object|resource|null
     */
    protected $_connection = null;

    /**
     * Query handler
     *
     * @var resource
     */
    protected $_query = null;

    /**
     * Debug or not
     *
     * @var boolean
     */
    protected $_debug = false;

    /**
     * Log
     *
     * @var array
     */
    protected $_log = array();

    /**
     * Constructor.
     *
     * $config is an array of key/value pairs
     * containing configuration options.  These options are common to most adapters:
     *
     * database       => (string) The name of the database to user
     * user           => (string) Connect to the database as this username.
     * password       => (string) Password associated with the username.
     * host           => (string) What host to connect to, defaults to localhost
     *
     * Some options are used on a case-by-case basis by adapters:
     *
     * port           => (string) The port of the database
     * persistent     => (boolean) Whether to use a persistent connection or not, defaults to false
     * charset        => (string) The charset of the database
     *
     * @param  array $config
     */
    public function __construct($config)
    {
        $keys = array('adapter', 'master', 'slave');

        foreach ($keys as $key) {
            if (isset($config[$key])) $this->_config[$key] = $config[$key];
        }
    }

    /**
     * Master MySQL Adapter
     *
     * @return Cola_Com_Db_Abstract
     */
    public function master()
    {
        if ($this->_master) return $this->_master;

        $config = array(
            'adapter' => $this->_config['adapter'],
            'params'  => $this->_config['master']
        );

        $this->_master = Cola_Com_Db::factory($config);
        $this->_slave = $this->_master;
        $this->_mysql = $this->_master;

        return $this->_master;
    }

    /**
     * Slave MySQL Adapter
     *
     * @param string $name
     * @return Cola_Com_Db_Abstract
     */
    public function slave($name = null)
    {
        if ($this->_slave) return $this->_slave;

        if (is_null($name) || empty($this->_config['slave'][$name])) {
            $name = array_rand($this->_config['slave']);
        }
        $params = $this->_config['slave'][$name];

        $config = array(
            'adapter' => $this->_config['adapter'],
            'params'  => $params
        );

        $this->_slave = Cola_Com_Db::factory($config);
        $this->_mysql = $this->_slave;

        return $this->_slave;
    }

    /**
     * Get db connection
     *
     * @return resource
     */
    public function connection()
    {
        return $this->_mysql->connection();
    }

    /**
     * Get query statment
     *
     * @return resource
     */
    public function statment()
    {
        return $this->_mysql->statment();
    }

    /**
     * Returns the underlying database connection object or resource.
     * If not presently connected, this initiates the connection.
     *
     * @return object|resource|null
     */
    public function connect()
    {
        return $this;
    }

    /**
     * Set Debug or not
     *
     * @param boolean $flag
     */
    public function debug($flag = true)
    {
        if ($this->_master) $this->_master->debug($flag);
        if ($this->_slave) $this->_slave->debug($flag);
        return $this;
    }

    /**
     * Get or set log
     *
     * if $msg is null, then will return log
     *
     * @param string $msg
     * @return array|Cola_Com_Db_Abstract
     */
    public function log($msg = null)
    {
        if (!is_null($msg)) {
            $this->_mysql ? $this->_mysql->log($msg) : $this->_log[] = $msg;
            return $this;
        }

        $log = array('default' => $this->_log);
        if ($this->_master) $log['master'] = $this->_master->log();
        if ($this->_slave) $log['master'] = $this->_slave->log();
        return $log;
    }

    /**
     * Get SQL result
     *
     * @param string $sql
     * @param string $type
     * @return mixed
     */
    public function result($sql, $type = 'ASSOC')
    {
        $tags = explode(' ', $sql, 2);
        switch (strtoupper($tags[0])) {
            case 'SELECT':
                return $this->slave()->result($sql, $type);
            default:
                return $this->master()->result($sql, $type);
        }
    }

    /**
     * Get a result row
     *
     * @param string $sql
     * @param string $type
     * @return array
     */
    public function row($sql, $type = 'ASSOC')
    {
        return $this->slave()->row($sql, $type);
    }

    /**
     * Get first column of result
     *
     * @param string $sql
     * @return string
     */
    public function col($sql)
    {
        return $this->slave()->col($sql);
    }

    /**
     * Find data
     *
     * @param array $conditions
     * @return array
     */
    public function find($conditions)
    {
        return $this->slave()->find($conditions);
    }

    /**
     * Insert
     *
     * @param array $data
     * @param string $table
     * @return boolean
     */
    public function insert($data, $table)
    {
        return $this->master()->insert($data, $table);
    }

    /**
     * Update table
     *
     * @param array $data
     * @param string $where
     * @param string $table
     * @return int
     */
    public function update($data, $where = '0', $table)
    {
        return $this->master()->update($data, $where, $table);
    }

    /**
     * Delete from table
     *
     * @param string $where
     * @param string $table
     * @return int
     */
    public function delete($where = '0', $table)
    {
        return $this->master()->delete($where, $table);
    }

    /**
     * Count num rows
     *
     * @param string $where
     * @param string $table
     * @return int
     */
    public function count($where, $table)
    {
        return $this->slave()->count($where, $table);
    }

    protected function _connect($params)
    {
        return $this;
    }

    /**
     * Get error
     *
     * @return string|array
     */
    public function error($type = 'STRING')
    {
        return $this->_mysql->error($type);
    }

    /**
     * Close mysql connection
     *
     */
    public function close()
    {
        if ($this->_master) $this->_master->close();
        if ($this->_slave) $this->_slave->close();
    }

    /**
     * Query sql
     *
     * @param string $sql
     */
    public function query($sql)
    {
        $this->_lastSql = $sql;

        $tags = explode(' ', $sql, 2);
        switch (strtoupper($tags[0])) {
            case 'SELECT':
                $this->slave()->query($sql);
                break;
            default:
                $this->master()->query($sql);
        }
    }

    /**
     * Return the rows affected of the last sql
     *
     * @return int
     */
    public function affectedRows()
    {
        return $this->_mysql->affectedRows();
    }

    /**
     * Fetch one row result
     *
     * @param string $type
     * @return mixd
     */
    public function fetch($type = 'ASSOC')
    {
        return $this->_mysql->fetch($type = 'ASSOC');
    }

    /**
     * Fetch All result
     *
     * @param string $type
     * @return array
     */
    public function fetchAll($type = 'ASSOC')
    {
        return $this->_mysql->fetchAll($type = 'ASSOC');
    }

    /**
     * Get last insert id
     *
     * @return int
     */
    public function lastInsertId()
    {
        return $this->master()->lastInsertId();
    }

    /**
     * Beging transaction
     *
     */
    public function beginTransaction()
    {
        return $this->master()->beginTransaction();
    }

    /**
     * Commit transaction
     *
     * @return boolean
     */
    public function commit()
    {
        return $this->master()->commit();
    }

    /**
     * Roll back transaction
     *
     * @return boolean
     */
    public function rollBack()
    {
        return $this->master()->rollBack();
    }

    /**
     * Free result
     *
     */
    public function free()
    {
        $this->_mysql->free();
    }

    /**
     * Escape string
     *
     * @param string $str
     * @return string
     */
    public function escape($str)
    {
        foreach (array($this->_master, $this->_slave) as $mysql) {
            if ($mysql) return $mysql->escape($str);
        }

        return addslashes($str);
    }

    /**
     * Magic get value
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        switch ($key) {
            case 'master':
                return $this->master = $this->master();
            case 'slave':
                return $this->slave = $this->slave();
            default:
                throw new Cola_Com_Db_Exception('Undefined property: ' . get_class($this). '::' . $key);
        }
    }
}
