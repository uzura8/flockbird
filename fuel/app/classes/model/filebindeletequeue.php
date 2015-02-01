<?php

class Model_FileBinDeleteQueue extends \MyOrm\Model
{
	protected static $_table_name = 'file_bin_delete_queue';

	protected static $_properties = array(
		'id',
		'name' => array(
			'validation' => array('trim', 'required', 'max_length' => array(64)),
		),
		'created_at',
	);

	protected static $_observers = array(
		'Orm\Observer_CreatedAt' => array(
			'events' => array('before_insert'),
			'mysql_timestamp' => true,
		),
		'MyOrm\Observer_RemoveFile' => array(
			'events' => array('before_delete'),
		),
	);
}
