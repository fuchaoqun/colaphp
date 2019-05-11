<?php


namespace Cola\Db;


class MysqlException extends \PDOException
{
    public function __construct(\PDOException $e)
    {
        preg_match('/: (\d+) (.+)/', $e->getMessage(), $matches);

        if (!empty($matches[1])) {
            $this->code = intval($matches[1]);
            $this->message = $matches[2];
        } else {
            $this->code = $e->getCode();
            $this->message = $e->getMessage();
        }
    }
}