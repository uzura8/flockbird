<?php
class Model_File extends \Orm\Model
{
	protected static $_table_name = 'file';
	protected static $_properties = array(
		'id',
		'name' => array(
			'validation' => array(
				'trim',
				'max_length' => array(64),
			),
		),
		'type' => array(
			'validation' => array(
				'trim',
				'max_length' => array(64),
			),
		),
		'filesize' => array(
			'validation' => array(
				'trim',
				'valid_string' => array('integer'),
			),
		),
		'original_filename' => array(
			'validation' => array(
				'trim',
				'max_length' => array(255),
			),
		),
		'member_id' => array(
			'validation' => array(
				'trim',
				'valid_string' => array('integer'),
			),
		),
		'created_at',
		'updated_at'
	);

	protected static $_observers = array(
		'Orm\Observer_CreatedAt' => array(
			'events' => array('before_insert'),
			'mysql_timestamp' => true,
		),
		'Orm\Observer_UpdatedAt' => array(
			'events' => array('before_save'),
			'mysql_timestamp' => true,
		),
	);

	public static function validate($factory)
	{
		$val = Validation::forge($factory);
		//$val->add_field('title', 'Title', 'required|max_length[255]');

		return $val;
	}

	public static function calc_filesize_total($member_id = 0)
	{
		if (!$member_id) return false;

		$result = DB::query('SELECT SUM(filesize) as sum FROM file WHERE member_id = :member_id')->param('member_id', $member_id)->execute();

		return (int)$result[0]['sum'];
	}
}
