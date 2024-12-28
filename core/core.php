<?php

define('CACHE_PATH', 'cache/');
define('DOWN_PATH', 'down/');
define('LOG_PATH', 'tmp/');

define('LOG_DATA', LOG_PATH . date('Y-m-d') . '_data.txt');

require_once('core/fun.php');
require_once('core/clash.php');
require_once(__DIR__.'/../vendor/autoload.php');

$year = date('Y');
$month = date('m');
$day = date('d');

$cur_data =  $year . $month . $day;
