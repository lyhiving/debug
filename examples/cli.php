<?php
include __DIR__ . '/../autoload.php';

use lyhiving\debug\debug;
define('IA_ROOT', dirname(__DIR__));

$debug = new debug(__DIR__."/#debug.txt");
$debug->log('MAC', 'MAKE A BETTER WORLD');
$debug->log('ArrayNormal', array('apple', 'orange', 'banner'));
//Set less log info
$debug->set('log_level',0);
//If you want less infomation
$debug->log('ArrayCanCopyDirect', array('apple', 'orange', 'banner'));
$debug->dump('MAKE A BETTER WORLD');
$debug->_dump('OK. I\'m the last line.');
//$debug->file(__DIR__.'/new.txt')
