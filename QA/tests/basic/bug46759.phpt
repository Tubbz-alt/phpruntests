--TEST--
Testing magic_quotes_gpc
--SKIPIF--
<?php if (php_sapi_name()=='cli') echo 'skip'; ?>
--INI--
magic_quotes_gpc=1
--GET--
a='&b="&c=\"
--FILE--
<?php 

foreach ($_GET AS $key => $value)
{
	echo $key . ": " . $value . "\n";
}

?>
--EXPECT--
a: \'
b: \"
c: \\\"
