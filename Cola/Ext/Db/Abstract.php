<?php
/**
 *
 */

abstract class Cola_Ext_Db_Abstract
{
    /**
     * Configuration
     *
     * @var array
     */
    public $config = array(
        'host'       => '127.0.0.1',
        'port'       => 3306,
        'user'       => 'test',
        'password'   => '',
        'database'   => 'test',
        'charset'    => 'utf8',
        'persistent' => false,
        'options'    => array()
    );

    /**
     * Connection
     *
     * @var resource
     */
    public $conn = null;

    /**
     * Query handler
     *
     * @var resource
     */
    public $query = null;

    /**
     * Debug or not
     *
     * @var boolean
     */
    public $debug = false;

    /**
     * Log
     *
     * @var array
     */
    public $log = array();

    /**
     * Constructor.
     *
     * $config is an array of key/value pairs
     * containing configuration options.  These options are common to most adapters:
     *
     * host           => (string) What host to connect to, defaults to localhost
     * user           => (string) Connect to the database as this username.
     * password       => (string) Password associated with the username.
     * database       => (string) The name of the database to user
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
        $this->config = $config + $this->config;
    }

    /**
     * Query sql
     *
     * @param string $sql
     * @return resource
     */
    public function query($sql)
    {
        if (is_null($this->conn)) {
            $this->connect();
        }

        $log = $sql . '@' . date('Y-m-d H:i:s');
        if ($this->debug) {
            $this->log[] = $log;
        }

        if ($this->query = $this->_query($sql)) {
            return $this->query;
        }

        $this->log[] = $log;
        $this->_throwException();
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
                $result = $this->query;
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
     * @param array $opts
     * @return array
     */
    public function find($opts)
    {
        if (is_string($opts)) {
            $opts = array('where' => $opts);
        }

        $opts = $opts + array(
            'fileds' => '*',
            'where' => 1,
            'order' => null,
            'start' => -1,
            'limit' => -1
        );

        $sql = "select {$opts['fileds']} from {$opts['table']} where {$opts['where']}";

        if ($opts['order']) {
            $sql .= " order by {$opts['order']}";
        }

        if (0 <= $opts['start'] && 0 <= $opts['limit']) {
            $sql .= " limit {$opts['start']}, {$opts['limit']}";
        }

        return $this->sql($sql);
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
        $keys = array();
        $values = array();
        foreach ($data as $key => $value) {
            $keys[] = "`$key`";
            $values[] = "'" . $this->escape($value) . "'";
        }
        $keys = implode(',', $keys);
        $values = implode(',', $values);
        $sql = "insert into {$table} ({$keys}) values ({$values});";
        return $this->sql($sql);
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

        $sql = "update {$table} set {$str} where {$where}";

        return $this->sql($sql);
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
        return $this->sql($sql);
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
     * Throw error exception
     *
     */
    protected function _throwException()
    {
        $error = $this->error();
        throw new Cola_Ext_Db_Exception($error['msg'], $error['code']);
    }

    abstract public function connect();

    abstract public function close();

    abstract protected function _query($sql);

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
