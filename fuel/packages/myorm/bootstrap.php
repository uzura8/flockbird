<?php
Autoloader::add_classes(array(
	'MyOrm\\Observer_CopyValue' => __DIR__.'/classes/observer/copyvalue.php',
	'MyOrm\\Observer_InsertCache' => __DIR__.'/classes/observer/insertcache.php',
	'MyOrm\\Observer_UpdateParentDatetime' => __DIR__.'/classes/observer/updateparentdatetime.php',
	'MyOrm\\Observer_InsertCacheDuplicate' => __DIR__.'/classes/observer/insertcacheduplicate.php',
));

/* End of file bootstrap.php */
