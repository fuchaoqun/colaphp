<?php
/**
 *
 */

class Cola_Com_Db_Mysqli extends Cola_Com_Db_Abstract
{
    /**
     * Connect to database
     *
     */
    protected function _connect($params)
    {
        if (!extension_loaded('mysqli')) {
            throw new Cola_Com_Db_Exception('NO_MYSQLI_EXTENSION_FOUND');
        }

        if ($params['persistent']) {
            throw new Cola_Com_Db_Exception('MYSQLI_EXTENSTION_DOES_NOT_SUPPORT_PERSISTENT_CONNECTION');
        }

        $this->_connection = mysqli_init();

        $connected = @mysqli_real_connect(
            $this->_connection,
            $params['host'],
            $params['user'],
            $params['password'],
            $params['database'],
            $params['port']
        );

        if (false === $connected) {
            throw new Cola_Com_Db_Exception($this->error());
        }

        $this->query("SET NAMES '" . $this->_config['charset'] . "';");
    }

    /**
     * Select Database
     *
     * @param string $database
     * @return boolean
     */
    public function selectDb($database)
    {
        return $this->_connection->select_db($database);
    }

    /**
     * Close db connection
     *
     */
    public function close()
    {
        $this->_connection->close();
    }

    /**
     * Free query result
     *
     */
    public function free()
    {
        if ($this->_query) $this->_query->free();
    }

    /**
     * Query SQL
     *
     * @param string $sql
     * @return Cola_Com_Db_Mysqli
     */
    public function query($sql)
    {
        $this->_lastSql = $sql;

        if ($this->_debug) {
            $this->log($sql . '@' . date('Y-m-d H:i:s'));
        }

        $this->ping();

        if ($this->_query = $this->_connection->query($sql)) {
            return $this;
        }

        $msg = $this->error() . '@' . $sql . '@' . date('Y-m-d H:i:s');

        $this->log($msg);

        throw new Cola_Com_Db_Exception($msg);
    }

    /**
     * Return the rows affected of the last sql
     *
     * @return int
     */
    public function affectedRows()
    {
        return $this->_connection->affected_rows;
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

        return $this->_query->$func();
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
        while ($row = $this->_query->$func()) {
            $result[] = $row;
        }
        $this->_query->free();
        return $result;


    }

    /**
     * Get last insert id
     *
     * @return mixed
     */
    public function lastInsertId()
    {
        return $this->_connection->insert_id;
    }

    /**
     * Begin transaction
     *
     */
    public function beginTransaction()
    {
        $this->_connection->autocommit(false);
    }

    /**
     * Commit transaction
     *
     */
    public function commit()
    {
        $this->_connection->commit();
        $this->_connection->autocommit(true);
    }

    /**
     * Rollback
     *
     */
    public function rollBack()
    {
        $this->_connection->rollback();
        $this->_connection->autocommit(true);
    }

    /**
     * Escape string
     *
     * @param string $str
     * @return string
     */
    public function escape($str)
    {
        if($this->_connection) {
            return  $this->_connection->real_escape_string($str);
        }else{
            return addslashes($str);
        }
    }

    /**
     * Get error
     *
     * @return string|array
     */
    public function error($type = 'STRING')
    {
        $type = strtoupper($type);

        if ($this->_connection) {
            $errno = $this->_connection->errno;
            $error = $this->_connection->error;
        } else {
            $errno = mysqli_connect_errno();
            $error = mysqli_connect_error();
        }

        if ('ARRAY' == $type) {
            return array('code' => $errno, 'msg' => $error);
        }
        return $errno . ':' . $error;
    }

    /**
     * Ping mysql server
     *
     * @param boolean $reconnect
     * @return boolean
     */
    public function ping($reconnect = true)
    {
        $this->connect();
        if (!($ping = $this->_connection->ping()) && $reconnect) {
            $this->close();
            $this->connect();
            $ping = $this->_connection->ping();
        }
        return $ping;
    }
}