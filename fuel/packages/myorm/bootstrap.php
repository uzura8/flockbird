<?php
Autoloader::add_classes(array(
	'MyOrm\\Observer_CopyValue' => __DIR__.'/classes/observer/copyvalue.php',
	'MyOrm\\Observer_InsertCache' => __DIR__.'/classes/observer/insertcache.php',
	'MyOrm\\Observer_UpdateParentDatetime' => __DIR__.'/classes/observer/updateparentdatetime.php',
	'MyOrm\\Observer_InsertCacheDuplicate' => __DIR__.'/classes/observer/insertcacheduplicate.php',
	'MyOrm\\Observer_UpdateCacheDuplicate' => __DIR__.'/classes/observer/updatecacheduplicate.php',
	'MyOrm\\Observer_InsertRelationialTable' => __DIR__.'/classes/observer/insertrelationaltable.php',
	'MyOrm\\Observer_UpdateRelationalTable' => __DIR__.'/classes/observer/updaterelationaltable.php',
));

/* End of file bootstrap.php */
