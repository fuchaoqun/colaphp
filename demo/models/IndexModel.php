<?php
class IndexModel extends Cola_Model
{
    protected $_table = 'Tbl_Game';

    public function test()
    {
        try {
            $data = $this->sql("select * from foobar limit 5;");
            return $data;
        } catch (Exception $e) {
            echo $e;
        }
    }
}