<?php
namespace Content;

class Model_ContentPage extends \MyOrm\Model
{
	protected static $_table_name = 'content_page';

	protected static $_properties = array(
		'id',
		'slug' => array(
			'data_type' => 'varchar',
			'label' => '記事識別名',
			'validation' => array(
				'trim', 'required',
				'max_length' => array(32),
				'match_pattern' => array('/^[a-z0-9_-]*[a-z0-9]+[a-z0-9_-]*$/i'),
				'unique' => array('content_page.slug')
			),
			'form' => array('type' => 'text'),
		),
		'title' => array(
			'data_type' => 'varchar',
			'label' => 'タイトル',
			'validation' => array('trim', 'required', 'max_length' => array(255)),
			'form' => array('type' => 'text'),
		),
		'body' => array(
			'data_type' => 'text',
			'label' => '本文',
			'validation' => array('trim'),
			'form' => array('type' => 'textarea', 'rows' => 16),
		),
		'format' => array(
			'data_type' => 'integer',
			'label' => '形式',
			'default' => 0,
			'validation' => array('required', 'valid_string' => array('numeric'), 'max_length' => array(1)),
			'form' => array('type' => 'select'),
		),
		'admin_user_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'is_secure' => array(
			'data_type' => 'integer',
			'validation' => array('required'),
			'form' => array('type' => 'radio'),
		),
		'created_at' => array('form' => array('type' => false)),
		'updated_at' => array('form' => array('type' => false)),
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
	);

	public static function _init()
	{
		$format_options = conf('page.form.formats.options', 'content');
		static::$_properties['format']['form']['options'] = $format_options;
		static::$_properties['format']['validation']['in_array'][] = array_keys($format_options);

		$is_secure_options = \Site_Form::get_form_options4config('term.isSecure.options');
		static::$_properties['is_secure']['label'] = term('isSecure.label');
		static::$_properties['is_secure']['form']['options'] = $is_secure_options;
		static::$_properties['is_secure']['validation']['in_array'][] = array_keys($is_secure_options);

		//if (!Site_Util::check_editor_enabled('html_editor'))
		//{
		//	static::$_properties['body']['validation'][] = 'required';
		//}
	}

	public static function check_exists4slug($slug)
	{
		return (bool)self::get4slug($slug);
	}
}
