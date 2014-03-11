<?php

class Form_SiteConfig
{
	public static function get_validation4edit_sex($is_set_saved_value = false)
	{
		$val = \Validation::forge('site_config_sex');

		$name = 'profile_sex_is_enable';
		$value = $is_set_saved_value ? self::get_site_config_values($name, 0) : 0;
		$options_enable = array('0' => '無効', '1' => '有効');
		$val->add($name, term('member.sex').'設定を有効にするか', array('type' => 'radio', 'options' => $options_enable, 'value' => $value))
				->add_rule('valid_string', 'numeric', 'required')
				->add_rule('in_array', array_keys($options_enable));

		$name = 'profile_sex_is_disp_regist';
		$value = $is_set_saved_value ? self::get_site_config_values($name, 1) : 1;
		$options_is_disp = array('1' => '表示する', '0' => '表示しない');
		$val->add($name, '新規登録', array('type' => 'radio', 'options' => $options_is_disp, 'value' => $value))
				->add_rule('valid_string', 'numeric', 'required')
				->add_rule('in_array', array_keys($options_is_disp));

		$name = 'profile_sex_is_disp_config';
		$value = $is_set_saved_value ? self::get_site_config_values($name, 1) : 1;
		$val->add($name, 'プロフィール変更', array('type' => 'radio', 'options' => $options_is_disp, 'value' => $value))
				->add_rule('valid_string', 'numeric', 'required')
				->add_rule('in_array', array_keys($options_is_disp));

		$name = 'profile_sex_is_disp_search';
		$value = $is_set_saved_value ? self::get_site_config_values($name, 1) : 1;
		$val->add($name, 'メンバー検索', array('type' => 'radio', 'options' => $options_is_disp, 'value' => $value))
				->add_rule('valid_string', 'numeric', 'required')
				->add_rule('in_array', array_keys($options_is_disp));

		$name = 'profile_sex_display_type';
		$value = $is_set_saved_value ? self::get_site_config_values($name, 0) : 0;
		$options_display_type = \Site_Profile::get_display_type_options();
		$val->add($name, '表示場所', array('type' => 'select', 'options' => $options_display_type, 'value' => $value))
				->add_rule('valid_string', 'numeric')
				->add_rule('in_array', array_keys($options_display_type));

		$name = 'profile_sex_is_required';
		$value = $is_set_saved_value ? self::get_site_config_values($name, 0) : 0;
		$options_is_required = array('1' => '');
		$val->add($name, '必須', array('type' => 'checkbox', 'options' => $options_is_required, 'value' => $value))
				->add_rule('checkbox_val', $options_is_required);

		$name = 'profile_sex_is_edit_public_flag';
		$value = $is_set_saved_value ? self::get_site_config_values($name, 0) : 0;
		$options_is_edit_public_flag = array('0' => '固定', '1' => 'メンバー選択');
		$val->add($name, '公開設定の選択', array('type' => 'radio', 'options' => $options_is_edit_public_flag, 'value' => $value))
				->add_rule('valid_string', 'numeric')
				->add_rule('in_array', array_keys($options_is_edit_public_flag));

		$name = 'profile_sex_default_public_flag';
		$atters = \Site_Form::get_public_flag_configs();
		$atters['value'] = $is_set_saved_value ? self::get_site_config_values($name, \Config::get('site.public_flag.default')) : \Config::get('site.public_flag.default');
		$val->add($name, '公開設定デフォルト値', $atters)
				->add_rule('valid_string', 'numeric')
				->add_rule('in_array', \Site_Util::get_public_flags());

		return $val;
	}

	public static function get_validation4edit_birthday($is_set_saved_value = false)
	{
		$val = \Validation::forge('site_config_birthday');

		$name = 'profile_birthday_is_enable';
		$value = $is_set_saved_value ? self::get_site_config_values($name, 0) : 0;
		$options_enable = array('0' => '無効', '1' => '有効');
		$val->add($name, term('member.birthday').'設定を有効にするか', array('type' => 'radio', 'options' => $options_enable, 'value' => $value))
				->add_rule('valid_string', 'numeric', 'required')
				->add_rule('in_array', array_keys($options_enable));

		$name = 'profile_birthday_is_disp_regist';
		$value = $is_set_saved_value ? self::get_site_config_values($name, 1) : 1;
		$options_is_disp = array('1' => '表示する', '0' => '表示しない');
		$val->add($name, '新規登録', array('type' => 'radio', 'options' => $options_is_disp, 'value' => $value))
				->add_rule('valid_string', 'numeric', 'required')
				->add_rule('in_array', array_keys($options_is_disp));

		$name = 'profile_birthday_is_disp_config';
		$value = $is_set_saved_value ? self::get_site_config_values($name, 1) : 1;
		$val->add($name, 'プロフィール変更', array('type' => 'radio', 'options' => $options_is_disp, 'value' => $value))
				->add_rule('valid_string', 'numeric', 'required')
				->add_rule('in_array', array_keys($options_is_disp));

		$name = 'profile_birthday_is_disp_search';
		$value = $is_set_saved_value ? self::get_site_config_values($name, 1) : 1;
		$val->add($name, 'メンバー検索', array('type' => 'radio', 'options' => $options_is_disp, 'value' => $value))
				->add_rule('valid_string', 'numeric', 'required')
				->add_rule('in_array', array_keys($options_is_disp));

		$name = 'profile_birthday_is_enable_generation_view';
		$value = $is_set_saved_value ? self::get_site_config_values($name, 0) : 0;
		$val->add($name, '年代表示を有効にするか', array('type' => 'radio', 'options' => $options_enable, 'value' => $value))
				->add_rule('valid_string', 'numeric')
				->add_rule('in_array', array_keys($options_enable));

		$name = 'profile_birthday_generation_unit';
		$value = $is_set_saved_value ? self::get_site_config_values($name, 0) : 0;
		$options = array('0' => '10年単位', '1' => '5年単位');
		$val->add($name, '年代区切り', array('type' => 'radio', 'options' => $options, 'value' => $value))
				->add_rule('valid_string', 'numeric')
				->add_rule('in_array', array_keys($options));

		$name = 'profile_birthday_is_required';
		$value = $is_set_saved_value ? self::get_site_config_values($name, 0) : 0;
		$options_is_required = array('1' => '');
		$val->add($name, '必須', array('type' => 'checkbox', 'options' => $options_is_required, 'value' => $value))
				->add_rule('checkbox_val', $options_is_required);

		$name = 'profile_birthday_birthyear_view_type';
		$value = $is_set_saved_value ? self::get_site_config_values($name, 0) : 0;
		$options = array('0' => '生年表示', '1' => '年齢表示');
		$val->add($name, '生年表示タイプ', array('type' => 'radio', 'options' => $options, 'value' => $value))
				->add_rule('valid_string', 'numeric')
				->add_rule('in_array', array_keys($options));

		$name = 'profile_birthday_display_type_birthyear';
		$value = $is_set_saved_value ? self::get_site_config_values($name, 0) : 0;
		$options_display_type = \Site_Profile::get_display_type_options();
		$val->add($name, '表示場所(生年)', array('type' => 'select', 'options' => $options_display_type, 'value' => $value))
				->add_rule('valid_string', 'numeric')
				->add_rule('in_array', array_keys($options_display_type));

		$name = 'profile_birthday_is_edit_public_flag_birthyear';
		$value = $is_set_saved_value ? self::get_site_config_values($name, 0) : 0;
		$options_is_edit_public_flag = array('0' => '固定', '1' => 'メンバー選択');
		$val->add($name, '公開設定の選択(生年)', array('type' => 'radio', 'options' => $options_is_edit_public_flag, 'value' => $value))
				->add_rule('valid_string', 'numeric')
				->add_rule('in_array', array_keys($options_is_edit_public_flag));

		$name = 'profile_birthday_default_public_flag_birthyear';
		$atters = \Site_Form::get_public_flag_configs();
		$atters['value'] = $is_set_saved_value ? self::get_site_config_values($name, \Config::get('site.public_flag.default')) : \Config::get('site.public_flag.default');
		$val->add($name, '公開設定デフォルト値(生年)', $atters)
				->add_rule('valid_string', 'numeric')
				->add_rule('in_array', \Site_Util::get_public_flags());

		$name = 'profile_birthday_display_type_birthday';
		$value = $is_set_saved_value ? self::get_site_config_values($name, 0) : 0;
		$options_display_type = \Site_Profile::get_display_type_options();
		$val->add($name, '表示場所(誕生日)', array('type' => 'select', 'options' => $options_display_type, 'value' => $value))
				->add_rule('valid_string', 'numeric')
				->add_rule('in_array', array_keys($options_display_type));

		$name = 'profile_birthday_is_edit_public_flag_birthday';
		$value = $is_set_saved_value ? self::get_site_config_values($name, 0) : 0;
		$val->add($name, '公開設定の選択(誕生日)', array('type' => 'radio', 'options' => $options_is_edit_public_flag, 'value' => $value))
				->add_rule('valid_string', 'numeric')
				->add_rule('in_array', array_keys($options_is_edit_public_flag));

		$name = 'profile_birthday_default_public_flag_birthday';
		$atters = \Site_Form::get_public_flag_configs();
		$atters['value'] = $is_set_saved_value ? self::get_site_config_values($name, \Config::get('site.public_flag.default')) : \Config::get('site.public_flag.default');
		$val->add($name, '公開設定デフォルト値(誕生日)', $atters)
				->add_rule('valid_string', 'numeric')
				->add_rule('in_array', \Site_Util::get_public_flags());

		return $val;
	}

	private static function get_site_config_values($name, $default_value = null)
	{
		$saved_value = \Model_SiteConfig::get_value4name_as_assoc($name);
		if (false === $saved_value) return $default_value;

		return $saved_value;
	}

	public static function get_site_config_names($suffix)
	{
		$method = 'get_validation4edit_'.$suffix;
		return \Form_Util::get_field_names(self::$method());
	}

	public static function save_site_configs(\Validation $val, $posted_values)
	{
		$field_names = \Form_Util::get_field_names($val);
		foreach ($field_names as $name)
		{
			$site_config_obj = \Model_SiteConfig::get4name($name);
			if ($site_config_obj && $site_config_obj->value == $posted_values[$name]) continue;
			if (!$site_config_obj) $site_config_obj = \Model_SiteConfig::forge();

			$site_config_obj->name  = $name;
			$site_config_obj->value = $posted_values[$name];
			$site_config_obj->save();
		}
	}
}
