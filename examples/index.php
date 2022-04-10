<?php
include __DIR__ . '/../autoload.php';

use lyhiving\debug\debug;
define('IA_ROOT', dirname(__DIR__));
$debug = new debug(__DIR__."/debug.txt");
$_GET['im'] = 'debug';
$_GET['array'] = array('apple','orange','bananer');
$_POST['im'] = 'a POST data';
$_POST['array'] = array('apple','orange','bananer');

//开启记录毫秒时间，并且记录同时输出相同内容
$debug->set('log_microtime', true)->set('log_and_echo', true);
//开启记录毫秒时间，并且记录详细信息，输出时去掉所在行和执行时间，更接近console_info
// $debug->set('log_microtime', true)->set('log_and_echo', 'timed');
$debug->log('MAC','MAKE A BETTER WORLD');
$debug->log('TIME','@time');
$debug->dump('MAKE A BETTER WORLD');
$debug->_dump('OK. I\'m the last line.');

