<?php
ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');
ini_set('html_errors', 'on');
ini_set('log_errors', 'on');
error_reporting(-1);
ini_set('error_log', './log/error.log');

define ('WEPPO_ROOT', realpath('../src').'/');
echo WEPPO_ROOT;
require_once WEPPO_ROOT."WEPPO/autoload.php";



$p = new WEPPO\Routing\MemoryPage();

$p->setMatchMap(array('a','b', 'c'));
$p->setMatches(array('ABC', 'A','B','C'));
o($p->getMatches());