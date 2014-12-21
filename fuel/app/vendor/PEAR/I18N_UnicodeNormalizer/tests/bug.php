<?php

function substring($string, $length)
{
    $substr = mb_substr($string, 0, $length , 'UTF-8');
    $length = strlen($substr);
    $chars = $length? unpack("C{$length}chars", $substr) : array();
    $decs = array_map('dechex', $chars);
    return array($substr, $decs);
}

$test['string'] = "\x44\xCC\x87";
$test['utf8'] = '\x44\xCC\x87';
$test['unicode'] = '\u0044\u0307';
$test['PHP_INT_MAX'] = PHP_INT_MAX;
$test['php_int_max'] = substring($test['string'], PHP_INT_MAX);
$test['9999'] = substring($test['string'], 9999);

//print_r($test);
//exit;

date_default_timezone_set('UTC');
ob_start();
phpinfo();
$test['phpinfo'] = ob_get_contents();
ob_end_clean();

file_put_contents('bug.txt', print_r($test, true));

?>