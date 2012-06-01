<?php
error_reporting(E_ALL);
ini_set('display_errors', 'on');

date_default_timezone_set('Asia/Shanghai');

require '../Cola/Cola.php';

$cola = Cola::getInstance();


//$benchmark = new Cola_Com_Benchmark();

$cola->boot()->dispatch();

//echo "<br />cost:", $benchmark->cost(), 's';