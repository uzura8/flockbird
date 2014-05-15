<?php

class Form_SiteConfig_Profile extends Form_SiteConfig
{

	private static function get_name($parent_item, $item)
	{
		return sprintf('profile_%s_%s', $parent_item, $item);
	}

	public static function get_validation_name()
	{
		$val = \Validation::forge('site_config_profile_name');
		$name_prefix = 'name';

		$options_is_disp = array('1' => '表示する', '0' => '表示しない');
		$name = self::get_name($name_prefix, 'isDispConfig');
		$value = self::get_values($name, 0);
		$val->add($name, 'プロフィール変更', array('type' => 'radio', 'options' => $options_is_disp, 'value' => $value))
				->add_rule('valid_string', 'numeric', 'required')
				->add_rule('in_array', array_keys($options_is_disp));

		$name = self::get_name($name_prefix, 'isDispSearch');
		$value = self::get_values($name, 0);
		$val->add($name, 'メンバー検索', array('type' => 'radio', 'options' => $options_is_disp, 'value' => $value))
				->add_rule('valid_string', 'numeric', 'required')
				->add_rule('in_array', array_keys($options_is_disp));

		return $val;
	}

	public static function get_validation_sex()
	{
		$val = \Validation::forge('site_config_profile_sex');
		$name_prefix = 'sex';

		$name = self::get_name($name_prefix, 'isEnable');
		$value = self::get_values($name, 0);
		$options_enable = array('0' => '無効', '1' => '有効');
		$val->add($name, term('member.sex.label').'設定を有効にするか', array('type' => 'radio', 'options' => $options_enable, 'value' => $value))
				->add_rule('valid_string', 'numeric', 'required')
				->add_rule('in_array', array_keys($options_enable));

		$name = self::get_name($name_prefix, 'isDispRegist');
		$value = self::get_values($name, 0);
		$options_is_disp = array('1' => '表示する', '0' => '表示しない');
		$val->add($name, '新規登録', array('type' => 'radio', 'options' => $options_is_disp, 'value' => $value))
				->add_rule('valid_string', 'numeric', 'required')
				->add_rule('in_array', array_keys($options_is_disp));

		$name = self::get_name($name_prefix, 'isDispConfig');
		$value = self::get_values($name, 0);
		$val->add($name, 'プロフィール変更', array('type' => 'radio', 'options' => $options_is_disp, 'value' => $value))
				->add_rule('valid_string', 'numeric', 'required')
				->add_rule('in_array', array_keys($options_is_disp));

		$name = self::get_name($name_prefix, 'isDispSearch');
		$value = self::get_values($name, 0);
		$val->add($name, 'メンバー検索', array('type' => 'radio', 'options' => $options_is_disp, 'value' => $value))
				->add_rule('valid_string', 'numeric', 'required')
				->add_rule('in_array', array_keys($options_is_disp));

		$name = self::get_name($name_prefix, 'displayType');
		$value = self::get_values($name, 0);
		$options_display_type = \Site_Profile::get_display_type_options();
		$val->add($name, '表示場所', array('type' => 'select', 'options' => $options_display_type, 'value' => $value))
				->add_rule('valid_string', 'numeric')
				->add_rule('in_array', array_keys($options_display_type));

		$name = self::get_name($name_prefix, 'isRequired');
		$value = self::get_values($name, 0);
		$options_is_required = array('1' => '');
		$val->add($name, '必須', array('type' => 'checkbox', 'options' => $options_is_required, 'value' => $value))
				->add_rule('checkbox_val', $options_is_required);

		$name_prefix .= '_publicFlag';
		$name = self::get_name($name_prefix, 'isEdit');
		$value = self::get_values($name, 0);
		$options_is_edit_public_flag = array('0' => '固定', '1' => 'メンバー選択');
		$val->add($name, '公開設定の選択', array('type' => 'radio', 'options' => $options_is_edit_public_flag, 'value' => $value))
				->add_rule('valid_string', 'numeric')
				->add_rule('in_array', array_keys($options_is_edit_public_flag));

		$name = self::get_name($name_prefix, 'default');
		$atters = Site_Form::get_public_flag_configs();
		$atters['value'] = self::get_values($name, conf('public_flag.default'));
		$val->add($name, '公開設定デフォルト値', $atters)
				->add_rule('valid_string', 'numeric')
				->add_rule('in_array', \Site_Util::get_public_flags());

		return $val;
	}

	public static function get_validation_birthday()
	{
		$val = \Validation::forge('site_config_profile_birthday');
		$name_prefix = 'birthday';

		$name = self::get_name($name_prefix, 'isEnable');
		$value = self::get_values($name, 0);
		$options_enable = array('0' => '無効', '1' => '有効');
		$val->add($name, term('member.birthday').'設定を有効にするか', array('type' => 'radio', 'options' => $options_enable, 'value' => $value))
				->add_rule('valid_string', 'numeric', 'required')
				->add_rule('in_array', array_keys($options_enable));

		$name = self::get_name($name_prefix, 'isDispRegist');
		$value = self::get_values($name, 1);
		$options_is_disp = array('1' => '表示する', '0' => '表示しない');
		$val->add($name, '新規登録', array('type' => 'radio', 'options' => $options_is_disp, 'value' => $value))
				->add_rule('valid_string', 'numeric', 'required')
				->add_rule('in_array', array_keys($options_is_disp));

		$name = self::get_name($name_prefix, 'isDispConfig');
		$value = self::get_values($name, 1);
		$val->add($name, 'プロフィール変更', array('type' => 'radio', 'options' => $options_is_disp, 'value' => $value))
				->add_rule('valid_string', 'numeric', 'required')
				->add_rule('in_array', array_keys($options_is_disp));

		$name = self::get_name($name_prefix, 'isDispSearch');
		$value = self::get_values($name, 1);
		$val->add($name, 'メンバー検索', array('type' => 'radio', 'options' => $options_is_disp, 'value' => $value))
				->add_rule('valid_string', 'numeric', 'required')
				->add_rule('in_array', array_keys($options_is_disp));

		$name_prefix = 'birthday_birthyear';
		$name = self::get_name($name_prefix, 'viewType');
		$value = self::get_values($name, 0);
		$options = array('0' => '生年表示', '1' => '年齢表示');
		$val->add($name, '生年表示タイプ', array('type' => 'radio', 'options' => $options, 'value' => $value))
				->add_rule('valid_string', 'numeric')
				->add_rule('in_array', array_keys($options));

		$name = self::get_name($name_prefix, 'displayType');
		$value = self::get_values($name, 0);
		$options_display_type = \Site_Profile::get_display_type_options();
		$val->add($name, '表示場所(生年)', array('type' => 'select', 'options' => $options_display_type, 'value' => $value))
				->add_rule('valid_string', 'numeric')
				->add_rule('in_array', array_keys($options_display_type));

		$name = self::get_name($name_prefix, 'isRequired');
		$value = self::get_values($name, 0);
		$options_is_required = array('1' => '');
		$val->add($name, '必須(生年)', array('type' => 'checkbox', 'options' => $options_is_required, 'value' => $value))
				->add_rule('checkbox_val', $options_is_required);

		$name_prefix .= '_publicFlag';
		$name = self::get_name($name_prefix, 'isEdit');
		$value = self::get_values($name, 0);
		$options_is_edit_public_flag = array('0' => '固定', '1' => 'メンバー選択');
		$val->add($name, '公開設定の選択(生年)', array('type' => 'radio', 'options' => $options_is_edit_public_flag, 'value' => $value))
				->add_rule('valid_string', 'numeric')
				->add_rule('in_array', array_keys($options_is_edit_public_flag));

		$name = self::get_name($name_prefix, 'default');
		$atters = \Site_Form::get_public_flag_configs();
		$atters['value'] = self::get_values($name, conf('public_flag.default'));
		$val->add($name, '公開設定デフォルト値(生年)', $atters)
				->add_rule('valid_string', 'numeric')
				->add_rule('in_array', \Site_Util::get_public_flags());

		$name_prefix = 'birthday_birthday';
		$name = self::get_name($name_prefix, 'displayType');
		$value = self::get_values($name, 0);
		$options_display_type = \Site_Profile::get_display_type_options();
		$val->add($name, '表示場所(誕生日)', array('type' => 'select', 'options' => $options_display_type, 'value' => $value))
				->add_rule('valid_string', 'numeric')
				->add_rule('in_array', array_keys($options_display_type));

		$name = self::get_name($name_prefix, 'isRequired');
		$value = self::get_values($name, 0);
		$options_is_required = array('1' => '');
		$val->add($name, '必須(誕生日)', array('type' => 'checkbox', 'options' => $options_is_required, 'value' => $value))
				->add_rule('checkbox_val', $options_is_required);

		$name_prefix .= '_publicFlag';
		$name = self::get_name($name_prefix, 'isEdit');
		$value = self::get_values($name, 0);
		$val->add($name, '公開設定の選択(誕生日)', array('type' => 'radio', 'options' => $options_is_edit_public_flag, 'value' => $value))
				->add_rule('valid_string', 'numeric')
				->add_rule('in_array', array_keys($options_is_edit_public_flag));

		$name = self::get_name($name_prefix, 'default');
		$atters = \Site_Form::get_public_flag_configs();
		$atters['value'] = self::get_values($name, conf('public_flag.default'));
		$val->add($name, '公開設定デフォルト値(誕生日)', $atters)
				->add_rule('valid_string', 'numeric')
				->add_rule('in_array', \Site_Util::get_public_flags());

		if (conf('member.profile.birthday.use_generation_view'))
		{
			$name_prefix = 'birthday_generationView';
			$name = self::get_name($name_prefix, 'isEnable');
			$value = self::get_values($name, 0);
			$val->add($name, '年代表示を有効にするか', array('type' => 'radio', 'options' => $options_enable, 'value' => $value))
					->add_rule('valid_string', 'numeric')
					->add_rule('in_array', array_keys($options_enable));

			$name = self::get_name($name_prefix, 'unit');
			$value = self::get_values($name, 0);
			$options = array('0' => '10年単位', '1' => '5年単位');
			$val->add($name, '年代区切り', array('type' => 'radio', 'options' => $options, 'value' => $value))
					->add_rule('valid_string', 'numeric')
					->add_rule('in_array', array_keys($options));

			$name_prefix .= '_publicFlag';
			$name = self::get_name($name_prefix, 'isEdit');
			$value = self::get_values($name, 0);
			$val->add($name, '公開設定の選択(年代)', array('type' => 'radio', 'options' => $options_is_edit_public_flag, 'value' => $value))
					->add_rule('valid_string', 'numeric')
					->add_rule('in_array', array_keys($options_is_edit_public_flag));

			$name = self::get_name($name_prefix, 'default');
			$atters = \Site_Form::get_public_flag_configs();
			$atters['value'] = self::get_values($name, conf('public_flag.default'));
			$val->add($name, '公開設定デフォルト値(年代)', $atters)
					->add_rule('valid_string', 'numeric')
					->add_rule('in_array', \Site_Util::get_public_flags());
		}

		return $val;
	}
}
