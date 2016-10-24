<?php

class Cola_Ext_Pdo
{
    public $pdo = null;

    public $stmt = null;

    public $log = array();

    public $dsn;
    public $user;
    public $password;
    public $options;

    public function __construct($dsn, $user = '', $password = '', $options = array())
    {
        $options += array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
        $this->dsn = $dsn;
        $this->user = $user;
        $this->password = $password;
        $this->options = $options;
        $this->connect();
    }

    public function connect()
    {
        $this->pdo = new PDO($this->dsn, $this->user, $this->password, $this->options);
        return $this->pdo;
    }

    /**
     * Close connection
     *
     */
    public function close()
    {
        $this->pdo = null;
    }

    /**
     * Free result
     *
     */
    public function free()
    {
        $this->stmt = null;
    }

    /**
     * Query sql
     *
     * @param string $sql
     * @return Cola_Ext_Db_Mysql
     */
    public function query($sql, $data = array())
    {
        $this->log[] = array('time' => date('Y-m-d H:i:s'), 'sql' => $sql, 'data' => $data);
        $this->stmt = $this->pdo->prepare($sql);
        $this->stmt->execute($data);
        return $this->stmt;
    }

    public function sql($sql, $data = array())
    {
        $this->query($sql, $data);
        $tags = explode(' ', $sql, 2);
        switch (strtoupper($tags[0])) {
            case 'SELECT':
                $result = $this->fetchAll();
                break;
            case 'INSERT':
                $result = $this->lastInsertId();
                break;
            case 'UPDATE':
            case 'DELETE':
            case 'REPLACE':
                $result = (0 <= $this->affectedRows());
                break;
            default:
                $result = $this->stmt;
        }
        return $result;
    }

    /**
     * Get a result row
     *
     * @param string $sql
     * @param int $style
     * @return array
     */
    public function row($sql, $data = array())
    {
        $result = $this->sql($sql, $data);
        return empty($result) ? false : $result[0];
    }

    /**
     * Get first column of result
     *
     * @param string $sql
     * @return string
     */
    public function col($sql, $data = array())
    {
        $result = $this->sql($sql, $data);
        return empty($result) ? false : current($result[0]);
    }

    /**
     * Insert
     *
     * @param string $table
     * @param array $data
     * @return boolean
     */
    public function insert($table, $data)
    {
        $keys = array();
        $marks = array();
        $values = array();
        foreach ($data as $key => $val) {
            is_array($val) && ($val = json_encode($val, JSON_UNESCAPED_UNICODE));
            $keys[] = "`{$key}`";
            $marks[] = '?';
            $values[] = $val;
        }

        $keys = implode(',', $keys);
        $marks = implode(',', $marks);
        $sql = "insert into {$table} ({$keys}) values ({$marks})";
        return $this->sql($sql, $values);
    }

    /**
     * Multiple insert
     *
     * @param string $table
     * @param array $rows
     * @return boolean
     */
    public function minsert($table, $rows)
    {
        if (empty($rows)) {
            return true;
        }
        $bindOne = array_fill(0, count(current($rows)), '?');
        $bindAll = array_fill(0, count($rows), implode(',', $bindOne));
        $bind = '(' . implode('),(', $bindAll) . ')';
        $keys = array_keys(current($rows));
        $values = array();
        foreach ($rows as $row) {
            foreach ($keys as $key) {
                $value = is_array($row[$key]) ? json_encode($row[$key], JSON_UNESCAPED_UNICODE) : $row[$key];
                $values[] = $value;
            }
        }
        if (is_int($keys[0])) {
            $fields = '';
        } else {
            $fields = ' (`' . implode('`,`', $keys) . '`) ';
        }

        $sql = "insert into {$table}{$fields} values {$bind}";
        return $this->sql($sql, $values);
    }

    /**
     * Replace
     *
     * @param string $table
     * @param array $data
     * @return boolean
     */
    public function replace($table, $data)
    {
        $keys = array();
        $marks = array();
        $values = array();
        foreach ($data as $key => $val) {
            is_array($val) && ($val = json_encode($val, JSON_UNESCAPED_UNICODE));
            $keys[] = "`{$key}`";
            $marks[] = '?';
            $values[] = $val;
        }

        $keys = implode(',', $keys);
        $marks = implode(',', $marks);
        $sql = "replace into {$table} ({$keys}) values ({$marks})";
        return $this->sql($sql, $values);
    }

    /**
     * Multiple Replace
     *
     * @param string $table
     * @param array $rows
     * @return boolean
     */
    public function mreplace($table, $rows)
    {
        if (empty($rows)) {
            return true;
        }
        $bindOne = array_fill(0, count($rows[0]), '?');
        $bindAll = array_fill(0, count($rows), implode(',', $bindOne));
        $bind = '(' . implode('),(', $bindAll) . ')';
        $keys = array_keys($rows[0]);
        $values = array();
        foreach ($rows as $row) {
            foreach ($keys as $key) {
                $value = is_array($row[$key]) ? json_encode($row[$key], JSON_UNESCAPED_UNICODE) : $row[$key];
                $values[] = $value;
            }
        }
        if (is_int($keys[0])) {
            $fields = '';
        } else {
            $fields = ' (`' . implode('`,`', $keys) . '`) ';
        }

        $sql = "replace into {$table}{$fields} values {$bind}";
        return $this->sql($sql, $values);
    }

    /**
     * Update table
     *
     * @param string $table
     * @param array $data
     * @param string $where
     * @return int
     */
    public function update($table, $data, $where = '0')
    {
        $keys = array();
        $values = array();
        foreach ($data as $key => $val) {
            is_array($val) && ($val = json_encode($val, JSON_UNESCAPED_UNICODE));
            $keys[] = "`{$key}`=?";
            $values[] = $val;
        }
        $keys = implode(',', $keys);
        if (is_string($where)) {
            $where = array($where, array());
        }
        $values = array_merge($values, $where[1]);
        $sql = "update {$table} set {$keys} where {$where[0]}";
        return $this->sql($sql, $values);
    }

    /**
     * Delete from table
     *
     * @param string $table
     * @param string $where
     * @return int
     */
    public function delete($table, $where = '0')
    {
        if (is_string($where)) {
            $where = array($where, array());
        }
        $sql = "delete from {$table} where {$where[0]}";
        return $this->sql($sql, $where[1]);
    }

    public function del($table, $where = '0')
    {
        return $this->delete($table, $where);
    }

    /**
     * Count num rows
     *
     * @param string $table
     * @param string $where
     * @return int
     */
    public function count($table, $where)
    {
        if (is_string($where)) {
            $where = array($where, array());
        }

        $sql = "select count(1) as cnt from {$table} where {$where[0]}";
        return intval($this->col($sql, $where[1]));
    }

    /**
     * Fetch one row result
     *
     * @param string $style
     * @return mixd
     */
    public function fetch($style = PDO::FETCH_ASSOC)
    {
        return $this->stmt->fetch($style);
    }

    /**
     * Fetch All result
     *
     * @param string $style
     * @return array
     */
    public function fetchAll($style = PDO::FETCH_ASSOC)
    {
        $result = $this->stmt->fetchAll($style);
        $this->free();
        return $result;
    }

    /**
     * Return the rows affected of the last sql
     *
     * @return int
     */
    public function affectedRows()
    {
        return $this->rowCount();
    }

    public function rowCount()
    {
        return $this->stmt->rowCount();
    }

    /**
     * Get the last inserted ID.
     *
     * @param string $name
     * @return mixed
     */
    public function lastInsertId($name = null)
    {
        $last = $this->pdo->lastInsertId($name);
        if (false === $last) {
            return false;
        } else if ('0' === $last) {
            return true;
        } else {
            return intval($last);
        }
    }

    /**
     * Ping server
     *
     * @param boolean $reconnect
     * @return boolean
     */
    public function ping($reconnect = true)
    {
        try {
            if ($this->pdo && $this->pdo->query('select 1')) {
                return true;
            }
        } catch (Exception $e) {

        }


        if ($reconnect) {
            $this->close();
            $this->connect();
            return $this->ping(false);
        }

        return false;
    }
}