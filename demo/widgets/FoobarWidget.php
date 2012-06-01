<?php
class FoobarWidget extends Cola_Com_Widget
{
    protected function _init($foobar = 9527)
    {
        $this->assign('foo', 'aaa');
    }
}