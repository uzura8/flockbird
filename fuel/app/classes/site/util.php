<?php
class Site_Util
{
	public static function get_module_name()
	{
		return (isset(Request::main()->route->module))? Request::main()->route->module : '';
	}

	public static function get_controller_name()
	{
		if (!isset(Request::main()->route->controller)) return '';

		return Str::lower(preg_replace('/^[a-zA-Z0-9_]+\\\Controller_/', '', Request::main()->route->controller));
	}

	public static function get_action_name()
	{
		return (isset(Request::main()->route->action))? Request::main()->route->action : '';
	}

	public static function check_is_admin_request()
	{
		if (Module::loaded('admin') && Request::main()->route->module == 'admin')
		{
			return true;
		}

		return false;
	}

	public static function get_form_instance($name = 'default', $model_obj = null, $with_submit_button = false, $is_horizontal = true)
	{
		$form = Fieldset::forge($name);
		$attributes = $is_horizontal ? array('class' => 'form-horizontal') : array();
		$form->set_config('form_attributes', $attributes);
		$form->add(\Config::get('security.csrf_token_key'), '', array('type'=>'hidden', 'value' => \Util_security::get_csrf()));

		if ($model_obj) $form->add_model($model_obj);
		if ($with_submit_button) $form->add('submit', '', array('type'=>'submit', 'value' => '送信', 'class' => 'btn'));

		return $form;
	}

	public static function check_is_api_request()
	{
		return strpos(Site_Util::get_controller_name(), 'api') !== false;
	}

	public static function check_ids_in_model_objects($target_ids, $model_objects)
	{
		$ids = Util_db::get_ids_from_model_objects($model_objects);

		return Util_Array::array_in_array($target_ids, $ids);
	}

	public static function get_login_page_uri()
	{
		if (Site_Util::check_is_admin_request()) return Config::get('site.login_uri.admin');

		return Config::get('site.login_uri.site');
	}

	public static function merge_module_configs($config, $config_name)
	{
		$modules = Module::loaded();
		foreach ($modules as $module => $path)
		{
			Config::load($module.'::'.$config_name, $module.'_'.$config_name);
			$module_config = Config::get($module.'_'.$config_name);
			if (!empty($module_config)) $config = array_merge_recursive($config, $module_config);
		}

		return $config;
	}

	public static function html_entity_decode($value, $flags = null, $encoding = null)
	{
		is_null($flags) and $flags = \Config::get('security.htmlentities_flags', ENT_QUOTES);
		is_null($encoding) and $encoding = \Fuel::$encoding;

		return html_entity_decode($value, $flags, $encoding);
	}

	public static function get_public_flags()
	{
		$public_flags = array();
		if (defined('PRJ_PUBLIC_FLAG_PRIVATE')) $public_flags[] = PRJ_PUBLIC_FLAG_PRIVATE;
		if (defined('PRJ_PUBLIC_FLAG_ALL'))     $public_flags[] = PRJ_PUBLIC_FLAG_ALL;
		if (defined('PRJ_PUBLIC_FLAG_MEMBER'))  $public_flags[] = PRJ_PUBLIC_FLAG_MEMBER;
		//if (defined('PRJ_PUBLIC_FLAG_FRIEND'))  $public_flags[] = PRJ_PUBLIC_FLAG_FRIEND;

		return $public_flags;
	}

	public static function get_have_public_flags_models()
	{
		return array('note', 'album', 'album_image');
	}

	public static function validate_params_public_flag($current_public_flag)
	{
		$public_flag = \Input::post('public_flag', null);
		if ($public_flag === null) throw new \HttpInvalidInputException('Invalid input data');
		$public_flag = (int)$public_flag;
		if (!in_array($public_flag, self::get_public_flags())) throw new \HttpInvalidInputException('Invalid input data');
		if ($current_public_flag == $public_flag) throw new \HttpInvalidInputException('Invalid input data');

		$model = \Input::post('model', null);
		if ($model === null || !in_array($model, self::get_have_public_flags_models())) throw new \HttpInvalidInputException('Invalid input data');

		return array($public_flag, $model);
	}
}
