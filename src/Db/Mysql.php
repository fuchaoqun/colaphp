<?php

namespace Cola\Db;

use Exception;
use PDO;
use PDOException;

class Mysql
{
    protected $_config = [
        'user' => '',
        'password' => '',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    ];

    protected $_connection = null;

    protected $_query = null;

    protected $_log = [];

    public function __construct($config)
    {
        if (!empty($config['options'])) {
            $config['options'] += $this->_config['options'];
        }

        $this->_config = $config + $this->_config;
        $this->connect();
    }

    public function connect()
    {
        $this->_connection = new PDO($this->_config['dsn'], $this->_config['user'], $this->_config['password'], $this->_config['options']);
        return $this->_connection;
    }

    /**
     * Close connection
     *
     */
    public function close()
    {
        $this->_connection = null;
    }

    /**
     * Free result
     *
     */
    public function free()
    {
        $this->_query = null;
    }

    /**
     * Query sql
     *
     * @param string $sql
     * @param array $data
     * @return PDOStatement
     */
    public function query($sql, $data = [])
    {
        $this->_log[] = ['time' => date('Y-m-d H:i:s'), 'sql' => $sql, 'data' => $data];

        for ($i = 0; $i < 2; $i ++) {
            try {
                if ($data) {
                    $this->_query = $this->_connection->prepare($sql);
                    $this->_query->execute($data);
                } else {
                    $this->_query = $this->_connection->query($sql);
                }
                return $this->_query;
            } catch (PDOException $e) {
                $me = new MysqlException($e);
                if (2006 == $me->getCode()) {
                    $this->close();
                    $this->connect();
                } else {
                    throw $e;
                }
            }
        }
    }

    public function sql($sql, $data = [])
    {
        $this->query($sql, $data);
        $tags = explode(' ', $sql, 2);
        $type = strtoupper($tags[0]);
        switch ($type) {
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
                $result = $this->_query;
        }
        return $result;
    }

    /**
     * Get a result row
     *
     * @param string $sql
     * @param array $data
     * @return array
     */
    public function row($sql, $data = [])
    {
        $result = $this->sql($sql, $data);
        return empty($result) ? false : $result[0];
    }

    /**
     * Get first column of result
     *
     * @param string $sql
     * @param array $data
     * @return string
     */
    public function column($sql, $data = [])
    {
        $result = $this->sql($sql, $data);
        return empty($result) ? false : current($result[0]);
    }

    public function col($sql, $data = [])
    {
        return $this->column($sql, $data);
    }

    /**
     * Insert
     *
     * @param string $table
     * @param array $row
     * @return boolean
     */
    public function insert($table, $row)
    {
        $keys = [];
        $marks = [];
        $values = [];
        foreach ($row as $key => $val) {
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
    public function insertMultiple($table, $rows)
    {
        if (empty($rows)) {
            return true;
        }
        $bindOne = array_fill(0, count(current($rows)), '?');
        $bindAll = array_fill(0, count($rows), implode(',', $bindOne));
        $bind = '(' . implode('),(', $bindAll) . ')';
        $keys = array_keys(current($rows));
        $values = [];
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

    public function minsert($table, $rows)
    {
        return $this->insertMultiple($table, $rows);
    }

    public function upsert($table, $row)
    {
        $keys = [];
        $marks = [];
        $values = [];
        $dp = [];
        foreach ($row as $key => $val) {
            is_array($val) && ($val = json_encode($val, JSON_UNESCAPED_UNICODE));
            $keys[] = "`{$key}`";
            $marks[] = '?';
            $values[] = $val;
            $dp[] = "`{$key}`=values(`{$key}`)";
        }

        $keys = implode(',', $keys);
        $marks = implode(',', $marks);
        $dp = implode(',', $dp);

        $sql = "insert into {$table} ({$keys}) values ({$marks}) ON DUPLICATE KEY UPDATE {$dp}";
        return $this->sql($sql, $values);
    }

    public function upsertMultiple($table, $rows)
    {
        if (empty($rows)) {
            return true;
        }
        $bindOne = array_fill(0, count(current($rows)), '?');
        $bindAll = array_fill(0, count($rows), implode(',', $bindOne));
        $bind = '(' . implode('),(', $bindAll) . ')';
        $keys = array_keys(current($rows));
        $values = [];
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

        $dp = [];
        foreach ($keys as $k) {
            $dp[] = "`{$k}`=values(`{$k}`)";
        }
        $dp = implode(',', $dp);

        $sql = "insert into {$table}{$fields} values {$bind} ON DUPLICATE KEY UPDATE {$dp}";
        return $this->sql($sql, $values);
    }

    public function mupsert($table, $rows)
    {
        return $this->upsertMultiple($table, $rows);
    }

    /**
     * Replace
     *
     * @param string $table
     * @param array $row
     * @return boolean
     */
    public function replace($table, $row)
    {
        $keys = [];
        $marks = [];
        $values = [];
        foreach ($row as $key => $val) {
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
    public function replaceMultiple($table, $rows)
    {
        if (empty($rows)) {
            return true;
        }
        $bindOne = array_fill(0, count($rows[0]), '?');
        $bindAll = array_fill(0, count($rows), implode(',', $bindOne));
        $bind = '(' . implode('),(', $bindAll) . ')';
        $keys = array_keys($rows[0]);
        $values = [];
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

    public function mreplace($table, $rows)
    {
        return $this->replaceMultiple($table, $rows);
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
        $keys = [];
        $values = [];
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
            $where = [$where, []];
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
            $where = [$where, []];
        }

        $sql = "select count(1) as cnt from {$table} where {$where[0]}";
        return intval($this->col($sql, $where[1]));
    }

    /**
     * Fetch one row result
     *
     * @param int $style
     * @return mixd
     */
    public function fetch($style = PDO::FETCH_ASSOC)
    {
        return $this->_query->fetch($style);
    }

    /**
     * Fetch All result
     *
     * @param int $style
     * @return array
     */
    public function fetchAll($style = PDO::FETCH_ASSOC)
    {
        $result = $this->_query->fetchAll($style);
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
        return $this->_query->rowCount();
    }

    /**
     * Get the last inserted ID.
     *
     * @param string $name
     * @return mixed
     */
    public function lastInsertId($name = null)
    {
        $last = $this->_connection->lastInsertId($name);
        if (false === $last) {
            return false;
        }

        if ('0' === $last) {
            return true;
        }

        return intval($last);
    }

    /**
     * Ping server
     *
     * @return boolean
     */
    public function ping()
    {
        try {
            if ($this->_connection && $this->_connection->query('select 1')) {
                return true;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function beginTransaction()
    {
        return $this->_connection->beginTransaction();
    }

    public function commit()
    {
        return $this->_connection->commit();
    }

    public function rollBack()
    {
        return $this->_connection->rollBack();
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * @return null
     */
    public function getConnection()
    {
        return $this->_connection;
    }

    /**
     * @return null
     */
    public function getQuery()
    {
        return $this->_query;
    }

    /**
     * @return array
     */
    public function getLog()
    {
        return $this->_log;
    }
}