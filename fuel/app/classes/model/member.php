<?php

class Model_Member extends \MyOrm\Model
{
	protected static $_table_name = 'member';

	protected static $_has_one = array(
		'member_auth' => array(
			'key_from' => 'id',
			'model_to' => 'Model_MemberAuth',
			'key_to' => 'member_id',
			'cascade_save' => false,
			'cascade_delete' => false,
		),
	);

	protected static $_properties = array(
		'id',
		'name' => array(
			'validation' => array(
				'trim',
				'required',
				'min_length' => array(2),
				'max_length' => array(20),
				'no_controll',
				'unique' => array('member.name'),
			),
			'form' => array('type' => 'text'),
		),
		'login_hash' => array(
			'validation' => array('valid_string' => array('alpha_numeric'), 'max_length' => array(255)),
			'form' => array('type' => false),
		),
		'register_type' => array(
			'validation' => array('required', 'valid_string' => array('numeric'), 'max_length' => array(1)),
			'form' => array('type' => false),
		),
		'file_name' => array(
			'validation' => array('trim', 'max_length' => array(64)),
			'form' => array('type' => false),
		),
		'filesize_total' => array(
			'validation' => array('valid_string' => array('numeric')),
			'form' => array('type' => false),
		),
		'sex' => array(
			'data_type' => 'varchar',
			'validation' => array('trim', 'max_length' => array(16)),
			'form' => array('type' => 'select'),
		),
		'sex_public_flag' => array(
			'data_type' => 'integer',
			'form' => array('type' => 'radio'),
		),
		'birthyear' => array(
			'data_type' => 'int',
			'validation' => array('numeric_min' => array(1900), 'numeric_max' => array(2100)),
			'form' => array('type' => 'select'),
		),
		'birthyear_public_flag' => array(
			'data_type' => 'integer',
			'form' => array('type' => 'radio'),
		),
		'birthday' => array(
			'data_type' => 'varchar',
			'validation' => array('date_string'),
			'form' => array('type' => false),
		),
		'birthday_public_flag' => array(
			'data_type' => 'integer',
			'form' => array('type' => 'radio'),
		),
		'last_login' => array('form' => array('type' => false)),
		'created_at' => array('form' => array('type' => false)),
		'updated_at' => array('form' => array('type' => false)),
	);

	protected static $_to_array_exclude = array(
		'login_hash', 'register_type', 'filesize_total', 'last_login', 'created_at', 'updated_at'
	);

	protected static $_observers = array(
		'Orm\Observer_Validation' => array(
			'events' => array('before_save'),
		),
		'Orm\Observer_CreatedAt' => array(
			'events' => array('before_insert'),
			'mysql_timestamp' => true,
		),
		'Orm\Observer_UpdatedAt' => array(
			'events' => array('before_save'),
			'mysql_timestamp' => true,
		),
		'MyOrm\Observer_DeleteMember' => array(
			'events' => array('before_delete'),
		),
	);

	protected static $image_prefix = 'm';

	protected static $basic_list = array();

	public static function _init()
	{
		static::$_properties['name']['label'] = term('member.name');

		$sex_options = Site_Form::get_form_options4config('term.member.sex.options');
		static::$_properties['sex']['label'] = term('member.sex.label');
		static::$_properties['sex']['form']['options'] = $sex_options;
		static::$_properties['sex']['validation']['in_array'][] = array_keys($sex_options);

		$options_public_flag = Site_Util::get_public_flags();
		static::$_properties['sex_public_flag']['label'] = sprintf('%sの%s', term('member.sex.label'), term('public_flag.label'));
		static::$_properties['sex_public_flag']['form'] = Site_Form::get_public_flag_configs();
		static::$_properties['sex_public_flag']['validation']['in_array'][] = $options_public_flag;

		static::$_properties['birthyear']['label'] = term('member.birthyear');
		$options = Form_Util::get_year_options(conf('member.profile.birthday.year_from'), conf('member.profile.birthday.year_to'));
		static::$_properties['birthyear']['form']['options'] = $options;
		static::$_properties['birthyear']['validation']['in_array'][] = array_keys($options);

		static::$_properties['birthyear_public_flag']['label'] = sprintf('%sの%s', term('member.birthyear'), term('public_flag.label'));
		static::$_properties['birthyear_public_flag']['form'] = Site_Form::get_public_flag_configs();
		static::$_properties['birthyear_public_flag']['validation']['in_array'][] = $options_public_flag;

		static::$_properties['birthday']['label'] = term('member.birthday');

		static::$_properties['birthday_public_flag']['label'] = sprintf('%sの%s', term('member.birthday'), term('public_flag.label'));
		static::$_properties['birthday_public_flag']['form'] = Site_Form::get_public_flag_configs();
		static::$_properties['birthday_public_flag']['validation']['in_array'][] = $options_public_flag;
	}

	public static function get_basic_data($id)
	{
		if (!empty(self::$basic_list[$id])) return self::$basic_list[$id];

		self::$basic_list[$id] = array();
		if ($obj = self::find($id))
		{
			self::$basic_list[$id] = array(
				'id' => $obj->id,
				'name' => $obj->name,
				'file' => $obj->get_image(),
			);
		}

		return self::$basic_list[$id];
	}

	public static function recalculate_filesize_total($member_id = 0)
	{
		$filesize_total = Model_File::calc_filesize_total($member_id);
		$member = self::find($member_id);
		$member->filesize_total = $filesize_total;
		$member->save();

		return $filesize_total;
	}

	public static function add_filesize($member_id, $size = 0)
	{
		$expr = DB::expr(sprintf('CASE WHEN `filesize_total` + %d < 0 THEN 0 ELSE `filesize_total` + %d END', $size));

		return DB::update('member')
			->value('filesize_total', $expr)
			->where('id', intval($member_id))
			->execute();
	}
}
