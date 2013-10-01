<?php
/**
 *
 */

class Cola_Ext_Db_Mysql extends Cola_Ext_Db_Abstract
{
    /**
     * Connect to MySQL
     *
     * @return resource connection
     */
    public function connect()
    {
        if ($this->ping(false)) {
            return $this->conn;
        }

        if (!extension_loaded('mysql')) {
            throw new Cola_Ext_Db_Exception('Can not find mysql extension.');
        }

        $func = ($this->config['persistent']) ? 'mysql_pconnect' : 'mysql_connect';
        $this->conn = @$func("{$this->config['host']}:{$this->config['port']}", $this->config['user'], $this->config['password']);

        if (is_resource($this->conn)) {
            if ($this->config['database']) $this->selectDb($this->config['database']);
            if ($this->config['charset']) $this->query("SET NAMES '{$this->config['charset']}';");
            return $this->conn;
        }

        $this->_throwException();
    }

    /**
     * Select Database
     *
     * @param string $database
     * @return boolean
     */
    public function selectDb($database)
    {
        return mysql_select_db($database, $this->conn);
    }

    /**
     * Close mysql connection
     *
     */
    public function close()
    {
        if (is_resource($this->conn)) {
            return mysql_close($this->conn);
        }
    }

    /**
     * Free result
     *
     */
    public function free()
    {
        return mysql_free_result($this->query);
    }

    /**
     * Query sql
     *
     * @param string $sql
     * @return resource
     */
    protected function _query($sql)
    {
        return mysql_query($sql, $this->conn);
    }

    /**
     * Return the rows affected of the last sql
     *
     * @return int
     */
    public function affectedRows()
    {
        return mysql_affected_rows($this->conn);
    }

    /**
     * Fetch one row result
     *
     * @param string $type
     * @return mixd
     */
    public function fetch($type = 'ASSOC')
    {
        $type = strtoupper($type);

        switch ($type) {
            case 'ASSOC':
                $func = 'mysql_fetch_assoc';
                break;
            case 'NUM':
                $func = 'mysql_fetch_array';
                break;
            case 'OBJECT':
                $func = 'mysql_fetch_object';
                break;
            default:
                $func = 'mysql_fetch_assoc';
        }

        return $func($this->query);
    }

    /**
     * Fetch All result
     *
     * @param string $type
     * @return array
     */
    public function fetchAll($type = 'ASSOC')
    {
        switch ($type) {
            case 'ASSOC':
                $func = 'mysql_fetch_assoc';
                break;
            case 'NUM':
                $func = 'mysql_fetch_array';
                break;
            case 'OBJECT':
                $func = 'mysql_fetch_object';
                break;
            default:
                $func = 'mysql_fetch_assoc';
        }
        $result = array();
        while ($row = $func($this->query)) {
            $result[] = $row;
        }
        mysql_free_result($this->query);
        return $result;
    }

    /**
     * Get last insert id
     *
     * @return int
     */
    public function lastInsertId()
    {
        return mysql_insert_id($this->conn);
    }

    /**
     * Beging transaction
     *
     */
    public function beginTransaction()
    {
        mysql_query('START TRANSACTION', $this->conn);
    }

    /**
     * Commit transaction
     *
     * @return boolean
     */
    public function commit()
    {
        if ($result = mysql_query('COMMIT', $this->conn)) {
            return true;
        }

        $this->_throwException();
    }

    /**
     * Roll back transaction
     *
     * @return boolean
     */
    public function rollBack()
    {
        if ($result = mysql_query('ROLLBACK', $this->conn)) {
            return true;
        }

        $this->_throwException();
    }

    /**
     * Escape string
     *
     * @param string $str
     * @return string
     */
    public function escape($str)
    {
        return $this->conn ? mysql_real_escape_string($str, $this->conn) : mysql_escape_string($str);
    }

    /**
     * Get error
     *
     * @return array
     */
    public function error()
    {
        if (is_resource($this->conn)) {
            $errno = mysql_errno($this->conn);
            $error = mysql_error($this->conn);
        } else {
            $errno = mysql_errno();
            $error = mysql_error();
        }

        return array('code' => intval($errno), 'msg' => $error);
    }

    /**
     * Ping mysql server
     *
     * @param boolean $reconnect
     * @return boolean
     */
    public function ping($reconnect = true)
    {
        if (is_resource($this->conn) && mysql_ping($this->conn)) {
            return true;
        }

        if ($reconnect) {
            $this->close();
            $this->connect();
            return  mysql_ping($this->conn);
        }

        return false;
    }
}