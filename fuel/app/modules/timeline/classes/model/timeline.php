<?php
namespace Timeline;

class Model_Timeline extends \Orm\Model
{
	protected static $_table_name = 'timeline';

	protected static $_has_one = array(
		'timeline_data' => array(
			'key_from' => 'id',
			'model_to' => '\Timeline\Model_TimelineData',
			'key_to' => 'timeline_id',
			'cascade_save' => false,
			//'cascade_delete' => false,
		),
	);
	protected static $_belongs_to = array(
		'member' => array(
			'key_from' => 'member_id',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
		)
	);
	//protected static $_has_many = array(
	//	'timeline_comment' => array(
	//		'key_from' => 'id',
	//		'model_to' => '\Timeline\Model_TimelineComment',
	//		'key_to' => 'timeline_id',
	//	)
	//);

	protected static $_properties = array(
		'id',
		'member_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'is_deleted' => array(
			'data_type' => 'integer',
			'validation' => array('max_length' => array(1), 'in_array' => array(0,1)),
			'form' => array('type' => false),
		),
		'public_flag' => array(
			'data_type' => 'integer',
			'validation' => array('required', 'max_length' => array(2)),
			'form' => array(),
		),
		'created_at' => array('form' => array('type' => false)),
		'updated_at' => array('form' => array('type' => false)),
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

	public static function _init()
	{
		static::$_properties['public_flag']['form'] = \Site_Form::get_public_flag_configs();
		static::$_properties['public_flag']['validation']['in_array'][] = \Site_Util::get_public_flags();
	}

	public static function check_authority($id, $target_member_id = 0)
	{
		if (!$id) return false;

		$obj = self::find($id);
		if (!$obj) return false;

		if ($target_member_id && $obj->member_id != $target_member_id) return false;

		return $obj;
	}
}
