<?php

class Model_Member extends \Orm\Model
{
	protected static $_table_name = 'member';

	protected static $_has_one = array(
		'member_auth' => array(
			'key_from' => 'id',
			'model_to' => 'Model_MemberAuth',
			'key_to' => 'member_id',
			'cascade_save' => false,
			//'cascade_delete' => false,
		),
		'file' => array(
			'key_from' => 'file_id',
			'model_to' => 'Model_File',
			'key_to' => 'id',
			'cascade_save' => false,
			//'cascade_delete' => false,
		),
	);
	protected static $_properties = array(
		'id',
		'name' => array(
			'validation' => array(
				'trim',
				'required',
				'max_length' => array(255),
			),
		),
		'login_hash' => array(
			'validation' => array(
				'trim',
				'max_length' => array(255),
			),
		),
		'last_login',
		'register_type' => array(// 0: normal, 1:facebook
			'validation' => array(
				'trim',
				'required',
				'match_pattern' => array('/[01]{1}/'),
			),
		),
		'file_id' => array(
			'validation' => array(
				'trim',
				'required',
				'valid_string' => array('integer'),
			),
		),
		'filesize_total' => array(
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

	public function get_image()
	{
		if (empty($this->file_id)) return 'm';

		return Model_File::get_name($this->file_id) ?: 'm';
	}

	public static function check_authority($id, $target_member_id = 0)
	{
		if (!$id) return false;

		$obj = self::find($id);
		if (!$obj) return false;

		if ($target_member_id && $obj->id != $target_member_id) return false;

		return $obj;
	}

	public static function recalculate_filesize_total($member_id = 0)
	{
		$filesize_total = Model_File::calc_filesize_total($member_id);
		if ($filesize_total)
		{
			$member = self::find($member_id);
			$member->filesize_total = $filesize_total;
			$member->save();
		}

		return $filesize_total;
	}

	public static function add_filesize($member_id, $size = 0)
	{
		$expr = DB::expr(sprintf('CASE WHEN `filesize_total` < 0 THEN 0 ELSE `filesize_total` + %d END', $size));

		return DB::update('member')
			->value('filesize_total', $expr)
			->where('id', intval($member_id))
			->execute();
	}
}
