<?php

class Site_Profile
{
	public static function get_form_type_options($key = null)
	{
		$options = array(
			'input' => 'テキスト',
			'textarea' => 'テキスト(複数行)',
			'select' => '単一選択(プルダウン)',
			'radio' => '単一選択(ラジオボタン)',
			'checkbox' => '複数選択(チェックボックス)',
			//'date' => '日付',
		);

		if ($key) return $options[$key];

		return $options;
	}

	public static function get_display_type_options($key = null, $is_simple = false)
	{
		$options = array(
			Config::get('site.member.profile.display_type.detail')  => $is_simple ? '詳細' : '詳細画面のみ表示',// 0
			Config::get('site.member.profile.display_type.summery') => $is_simple ? '概要' : 'summery にも表示',// 1
		);
		$key_always = Config::get('site.member.profile.display_type.always');
		if ($key_always) $options[$key_always] = $is_simple ? '常に' : '常に表示';// 2

		if (!is_null($key) && isset($options[$key])) return $options[$key];

		return $options;
	}

	public static function get_value_type_options($key = null)
	{
		$options = array(
			'string'  => '文字列',
			'integer' => '数値',
			'email'   => 'メールアドレス',
			'url'     => 'URL',
			'regexp'  => '正規表現',
		);

		if ($key) return $options[$key];

		return $options;
	}

	public static function get_is_edit_public_flag_options($key = null)
	{
		$options = array(
			0 => '固定',
			1 => 'メンバー選択',
		);

		if (isset($key)) return $options[$key];

		return $options;
	}

	public static function get_is_unique_options($key = null)
	{
		$options = array(
			0 => '許可',
			1 => '禁止',
		);

		if (isset($key)) return $options[$key];

		return $options;
	}

	public static function get_is_disp_options($key = null)
	{
		$options = array(
			1 => '表示する',
			0 => '表示しない',
		);

		if (isset($key)) return $options[$key];

		return $options;
	}

	public static function get_form_types_having_profile_options()
	{
		return array(
			'select',
			'radio',
			'checkbox',
		);
	}
}
