<?php
/**
 *
 */

abstract class Cola_Com_Db_Abstract
{
    /**
     * Configuration
     *
     * @var array
     */
    protected $_config = array();

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
     * Last query sql
     *
     * @var string
     */
    protected $_lastSql;

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
        $keys = array('host', 'port', 'user', 'password', 'database', 'persistent', 'charset', 'options');

        foreach ($keys as $key) {
            if (isset($config[$key])) {
                $this->_config[$key] = $config[$key];
            }
        }

        /**
         * Default config
         *
         * @var array
         */
        $defaults = array(
            'host' => '127.0.0.1',
            'port' => 3306,
            'user' => 'root',
            'password' => '',
            'database' => 'test',
            'charset' => 'UTF-8',
            'persistent' => false,
            'options' => array()
        );

        $this->_config += $defaults;

        //$this->connect();
    }

    /**
     * Get db connection
     *
     * @return resource
     */
    public function connection()
    {
        return $this->_connection;
    }

    /**
     * Get query statment
     *
     * @return resource
     */
    public function statment()
    {
        return $this->_query;
    }

    /**
     * Returns the underlying database connection object or resource.
     * If not presently connected, this initiates the connection.
     *
     * @return object|resource|null
     */
    public function connect()
    {
        if (null === $this->_connection) {
            $this->_connect($this->_config);
        }

        return $this;
    }

    /**
     * Set Debug or not
     *
     * @param boolean $flag
     */
    public function debug($flag = true)
    {
        $this->_debug = $flag;
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
        if (null === $msg) {
            return $this->_log;
        }

        $this->_log[] = $msg;

        return $this;
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
        return $this->sql($sql, $type);
    }

    /**
     * Get SQL result
     *
     * @param string $sql
     * @param string $type
     * @return mixed
     */
    public function sql($sql, $type = 'ASSOC')
    {
        $this->query($sql);

        $tags = explode(' ', $sql, 2);
        switch (strtoupper($tags[0])) {
            case 'SELECT':
                ($result = $this->fetchAll($type)) || ($result = array());
                break;
            case 'INSERT':
                $result = $this->lastInsertId();
                break;
            case 'UPDATE':
            case 'DELETE':
                $result = $this->affectedRows();
                break;
            default:
                $result = $this->_query;
        }

        return $result;
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
        $this->query($sql);
        return $this->fetch($type);
    }

    /**
     * Get first column of result
     *
     * @param string $sql
     * @return string
     */
    public function col($sql)
    {
        $this->query($sql);
        $result = $this->fetch();
        return empty($result) ? null : current($result);
    }

    /**
     * Find data
     *
     * @param array $conditions
     * @return array
     */
    public function find($conditions)
    {
        $result = array();

        if (is_string($conditions)) $conditions = array('where' => $conditions);

        $conditions = $conditions + array(
            'fileds' => '*',
            'where' => 1,
            'order' => null,
            'start' => -1,
            'limit' => -1
        );

        extract($conditions);

        $sql = "select {$fileds} from $table where $where";

        if ($order) $sql .= " order by {$order}";

        if (0 <=$start && 0 <= $limit) $sql .= " limit {$start}, {$limit}";

        $data = $this->result($sql);

        return $data;
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
        $keys = '';
        $values = '';
        foreach ($data as $key => $value) {
            $keys .= "`$key`,";
            $values .= "'" . $this->escape($value) . "',";
        }
        $sql = "insert into $table (" . substr($keys, 0, -1) . ") values (" . substr($values, 0, -1) . ");";
        return $this->result($sql);
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
        $tmp = array();

        foreach ($data as $key => $value) {
            $tmp[] = "`$key`='" . $this->escape($value) . "'";
        }

        $str = implode(',', $tmp);

        $sql = "update $table set " . $str . " where $where";

        return $this->result($sql);
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
        $sql = "delete from $table where $where";
        return $this->result($sql);
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
        $sql = "select count(1) as cnt from $table where $where";
        $this->query($sql);
        $result = $this->fetch();
        return empty($result['cnt']) ? 0 : $result['cnt'];
    }

    /**
     * Get last query sql
     *
     * @return string
     */
    public function lastSql()
    {
        return $this->_lastSql;
    }

    abstract protected function _connect($params);

    abstract public function error($type = 'STRING');

    abstract public function close();

    abstract public function query($sql);

    abstract public function affectedRows();

    abstract public function fetch();

    abstract public function fetchAll();

    abstract public function lastInsertId();

    abstract public function beginTransaction();

    abstract public function commit();

    abstract public function rollBack();

    abstract public function free();

    abstract public function escape($str);
}
