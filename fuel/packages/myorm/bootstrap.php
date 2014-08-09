<?php
Autoloader::add_classes(array(
	'MyOrm\\Model' => __DIR__.'/classes/model.php',
	'MyOrm\\Observer_CopyValue' => __DIR__.'/classes/observer/copyvalue.php',
	//'MyOrm\\Observer_InsertCache' => __DIR__.'/classes/observer/insertcache.php',
	//'MyOrm\\Observer_InsertCacheDuplicate' => __DIR__.'/classes/observer/insertcacheduplicate.php',
	'MyOrm\\Observer_UpdateCacheDuplicate' => __DIR__.'/classes/observer/updatecacheduplicate.php',
	'MyOrm\\Observer_InsertRelationialTable' => __DIR__.'/classes/observer/insertrelationaltable.php',
	'MyOrm\\Observer_UpdateRelationalTable' => __DIR__.'/classes/observer/updaterelationaltable.php',
	'MyOrm\\Observer_RemoveFile' => __DIR__.'/classes/observer/removefile.php',
	'MyOrm\\Observer_AddMemberFilesizeTotal' => __DIR__.'/classes/observer/addmemberfilesizetotal.php',
	'MyOrm\\Observer_SubtractMemberFilesizeTotal' => __DIR__.'/classes/observer/subtractmemberfilesizetotal.php',
	'MyOrm\\Observer_DeleteAlbumImage' => __DIR__.'/classes/observer/deletealbumimage.php',
	'MyOrm\\Observer_DeleteNewsImage' => __DIR__.'/classes/observer/deletenewsimage.php',
	'MyOrm\\Observer_InsertTimelineCache' => __DIR__.'/classes/observer/inserttimelinecache.php',
	'MyOrm\\Observer_UpdateTimelineCache' => __DIR__.'/classes/observer/updatetimelinecache.php',
	'MyOrm\\Observer_UpdateTimeline' => __DIR__.'/classes/observer/updatetimeline.php',
	'MyOrm\\Observer_DeleteTimeline' => __DIR__.'/classes/observer/deletetimeline.php',
	'MyOrm\\Observer_DeleteOrUpdateTimeline4ChildData' => __DIR__.'/classes/observer/deleteorupdatetimeline4childdata.php',
	'MyOrm\\Observer_InsertMemberFollowTimeline' => __DIR__.'/classes/observer/insertmemberfollowtimeline.php',
));

/* End of file bootstrap.php */
