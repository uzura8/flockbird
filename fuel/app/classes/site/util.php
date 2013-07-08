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
}
