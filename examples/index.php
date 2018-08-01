<?php
include __DIR__ . '/../autoload.php';

use lyhiving\debug\debug;
define('IA_ROOT', dirname(__DIR__));
$debug = new debug(__DIR__.'/debug.txt');
$_GET['im'] = 'debug';
$_GET['array'] = array('apple','orange','bananer');

$_POST['im'] = 'a POST data';
$_POST['array'] = array('apple','orange','bananer');

$debug->log('MAC','MAKE A BETTER WORLD');

$debug->dump('MAKE A BETTER WORLD');
$debug->_dump('OK. I\'m the last line.');
