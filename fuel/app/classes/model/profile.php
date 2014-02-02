<?php

class Model_Profile extends \Orm\Model
{
	protected static $_table_name = 'profile';

	protected static $_properties = array(
		'id',
		'name' => array(
			'data_type' => 'varchar',
			'label' => '識別名',
			'validation' => array('trim', 'required', 'max_length' => array(32)),
			'form' => array('type' => 'text'),
		),
		'caption' => array(
			'data_type' => 'text',
			'label' => '項目名',
			'validation' => array('trim', 'required'),
			'form' => array('type' => 'text'),
		),
		'information' => array(
			'data_type' => 'text',
			'label' => '説明',
			'validation' => array('trim'),
			'form' => array('type' => 'text'),
		),
		'is_required' => array(
			'data_type' => 'integer',
			'label' => '必須',
			'validation' => array('in_array' => array(0,1)),
			'form' => array('type' => 'checkbox'),
		),
		'is_unique' => array(
			'data_type' => 'integer',
			'label' => '重複の可否',
			'validatimn' => array('required', 'in_array' => array(0,1)),
			'form' => array('type' => 'checkbox'),
		),
		'is_edit_public_flag' => array(
			'data_type' => 'integer',
			'label' => '公開設定の選択',
			'validation' => array('required'),
			'form' => array('type' => 'radio'),
		),
		'default_public_flag' => array(
			'data_type' => 'integer',
			'label' => '公開設定デフォルト値',
			'validation' => array('required'),
			'form' => array('type' => 'select'),
		),
		'is_disp_regist' => array(
			'data_type' => 'integer',
			'label' => '新規登録',
			'validation' => array('in_array' => array(0,1)),
			'form' => array('type' => 'radio'),
		),
		'is_disp_config' => array(
			'data_type' => 'integer',
			'label' => 'プロフィール変更',
			'validation' => array('required', 'in_array' => array(0,1)),
			'form' => array('type' => 'radio'),
		),
		'is_disp_search' => array(
			'data_type' => 'integer',
			'label' => 'メンバー検索',
			'validation' => array('required', 'in_array' => array(0,1)),
			//'form' => array('type' => 'radio'),
			'form' => array('type' => false),
		),
		'form_type' => array(
			'data_type' => 'varchar',
			'label' => 'フォームタイプ',
			'validation' => array('trim', 'required', 'max_length' => array(32)),
			'form' => array('type' => 'select'),
		),
		'placeholder' => array(
			'data_type' => 'text',
			'label' => '入力欄内に表示する説明',
			'validation' => array('trim'),
			'form' => array('type' => 'text'),
		),
		'value_type' => array(
			'data_type' => 'varchar',
			'label' => '入力値タイプ',
			'validation' => array('trim', 'required', 'max_length' => array(32)),
			'form' => array('type' => 'select'),
		),
		'value_regexp' => array(
			'data_type' => 'varchar',
			'label' => '正規表現',
			'validation' => array(),
			'form' => array('type' => 'textarea'),
		),
		'value_min' => array(
			'data_type' => 'integer',
			'label' => '最小値',
			'validation' => array('valid_string' => array('numeric')),
			'form' => array('type' => 'text'),
		),
		'value_max' => array(
			'data_type' => 'integer',
			'label' => '最大値',
			'validation' => array('valid_string' => array('numeric')),
			'form' => array('type' => 'text'),
		),
		'sort_order' => array(
			'data_type' => 'integer',
			'label' => '並び順',
			'validation' => array('valid_string' => array('numeric')),
			'form' => array('type' => false),
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
		static::$_properties['default_public_flag']['form'] = Site_Form::get_public_flag_configs();
		static::$_properties['default_public_flag']['validation']['in_array'][] = Site_Util::get_public_flags();

		$option_keys = array(
			'form_type',
			'value_type',
			'is_edit_public_flag',
			'is_unique',
			'is_disp_regist' => 'get_is_disp_options',
			'is_disp_config' => 'get_is_disp_options',
			'is_disp_search' => 'get_is_disp_options',
		);
		foreach ($option_keys as $option_key => $method)
		{
			if (is_int($option_key))
			{
				$option_key = $method;
				$method = sprintf('get_%s_options', $option_key);
			}
			self::set_properties_options($option_key, $method);
		}
	}

	private static function set_properties_options($property, $get_options_method)
	{
		$form_type_options = Site_Profile::$get_options_method();
		static::$_properties[$property]['form']['options'] = $form_type_options;
		static::$_properties[$property]['validation']['in_array'][] = array_keys($form_type_options);
	}
}
