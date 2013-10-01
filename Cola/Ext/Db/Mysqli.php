<?php
/**
 *
 */

class Cola_Ext_Db_Mysqli extends Cola_Ext_Db_Abstract
{
    /**
     * Connect to database
     *
     */
    public function connect()
    {
        if ($this->ping(false)) {
            return $this->conn;
        }

        if (!extension_loaded('mysqli')) {
            throw new Cola_Ext_Db_Exception('NO_MYSQLI_EXTENSION_FOUND');
        }

        if ($this->config['persistent']) {
            throw new Cola_Ext_Db_Exception('MYSQLI_EXTENSTION_DOES_NOT_SUPPORT_PERSISTENT_CONNECTION');
        }

        $this->conn = mysqli_init();
        $connected = @mysqli_real_connect(
            $this->conn, $this->config['host'], $this->config['user'],
            $this->config['password'], $this->config['database'], $this->config['port']
        );

        if ($connected) {
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
        return $this->conn->select_db($database);
    }

    /**
     * Close db connection
     *
     */
    public function close()
    {
        if ($this->conn) {
            return $this->conn->close();
        }

        return true;
    }

    /**
     * Free query result
     *
     */
    public function free()
    {
        if ($this->query) {
            return $this->query->free();
        }
    }

    /**
     * Query SQL
     *
     * @param string $sql
     * @return Cola_Ext_Db_Mysqli
     */
    protected function _query($sql)
    {
        return $this->conn->query($sql);
    }

    /**
     * Return the rows affected of the last sql
     *
     * @return int
     */
    public function affectedRows()
    {
        return $this->conn->affected_rows;
    }

    /**
     * Fetch result
     *
     * @param string $type
     * @return mixed
     */
    public function fetch($type = 'ASSOC')
    {
        switch ($type) {
            case 'ASSOC':
                $func = 'fetch_assoc';
                break;
            case 'BOTH':
                $func = 'fetch_array';
                break;
            case 'OBJECT':
                $func = 'fetch_object';
                break;
            default:
                $func = 'fetch_assoc';
        }

        return $this->query->$func();
    }

    /**
     * Fetch all results
     *
     * @param string $type
     * @return mixed
     */
    public function fetchAll($type = 'ASSOC')
    {
        switch ($type) {
            case 'ASSOC':
                $func = 'fetch_assoc';
                break;
            case 'BOTH':
                $func = 'fetch_array';
                break;
            case 'OBJECT':
                $func = 'fetch_object';
                break;
            default:
                $func = 'fetch_assoc';
        }

        $result = array();
        while ($row = $this->query->$func()) {
            $result[] = $row;
        }
        $this->query->free();
        return $result;


    }

    /**
     * Get last insert id
     *
     * @return mixed
     */
    public function lastInsertId()
    {
        return $this->conn->insert_id;
    }

    /**
     * Begin transaction
     *
     */
    public function beginTransaction()
    {
        return $this->conn->autocommit(false);
    }

    /**
     * Commit transaction
     *
     */
    public function commit()
    {
        $this->conn->commit();
        $this->conn->autocommit(true);
    }

    /**
     * Rollback
     *
     */
    public function rollBack()
    {
        $this->conn->rollback();
        $this->conn->autocommit(true);
    }

    /**
     * Escape string
     *
     * @param string $str
     * @return string
     */
    public function escape($str)
    {
        if($this->conn) {
            return  $this->conn->real_escape_string($str);
        }else{
            return addslashes($str);
        }
    }

    /**
     * Get error
     *
     * @return array
     */
    public function error()
    {
        if ($this->conn) {
            $errno = $this->conn->errno;
            $error = $this->conn->error;
        } else {
            $errno = mysqli_connect_errno();
            $error = mysqli_connect_error();
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
        if ($this->conn && $this->conn->ping()) {
            return true;
        }

        if ($reconnect) {
            $this->close();
            $this->connect();
            return $this->conn->ping();
        }

        return false;
    }
}