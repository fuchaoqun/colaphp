<?php
echo $this->id, '<br />';
echo $this->truncate($this->name, 12), '<br />';
?>
下面的内容来自Widget：<br />
<?php $this->widget('foobar', 1234)->assign('foo', '111222', true)->display();?>