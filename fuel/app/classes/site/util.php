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

		return Str::lower(preg_replace('/^([a-zA-Z0-9_]+\\\)?Controller_/', '', Request::main()->route->controller));
	}

	public static function get_action_name()
	{
		return (isset(Request::main()->route->action))? Request::main()->route->action : 'index';
	}

	public static function check_is_admin_request()
	{
		if (Module::loaded('admin') && Request::main()->route->module == 'admin')
		{
			return true;
		}

		return false;
	}

	public static function get_form_instance($name = 'default', $model_obj = null, $is_horizontal = true, $add_fields = array(), $btn_field = array(), $form_attr = array(), $hide_fields = array())
	{
		$form = Fieldset::forge($name);
		if ($is_horizontal)
		{
			if (empty($form_attr['class']))
			{
				$form_attr['class'] = 'form-horizontal';
			}
			else
			{
				$form_attr['class'] .= ' form-horizontal';
			}
		}
		$form->set_config('form_attributes', $form_attr);
		$form->add(\Config::get('security.csrf_token_key'), '', array('type'=>'hidden', 'value' => \Util_security::get_csrf()));

		if (!empty($add_fields['pre']))
		{
			foreach ($add_fields['pre'] as $name => $item)
			{
				$form->add(
					$name,
					isset($item['label']) ? $item['label'] : '',
					isset($item['attributes']) ? $item['attributes'] : '',
					isset($item['rules']) ? $item['rules'] : ''
				);
			}
			unset($add_fields['pre']);
		}

		if ($model_obj) $form->add_model($model_obj);

		if (!empty($add_fields['post']) || !empty($add_fields))
		{
			$add_fields_post = !empty($add_fields['post']) ? $add_fields['post'] : $add_fields;
			foreach ($add_fields_post as $name => $item)
			{
				$form->add(
					$name,
					isset($item['label']) ?      $item['label']      : '',
					isset($item['attributes']) ? $item['attributes'] : array(),
					isset($item['rules']) ?      $item['rules']      : array()
				);
			}
		}

		if (!empty($btn_field))
		{
			$btn_name = '';
			$btn_attr = array();
			if (!is_array($btn_field))
			{
				if (in_array($btn_field, array('submit', 'button')))
				{
					$btn_name = $btn_field;
					$btn_attr = array('type'=> $btn_field, 'value' => '送信', 'class' => 'btn btn-default btn-primary');
				}
			}
			else
			{
				if (!isset($btn_field['attributes']))
				{
					$tmp = $btn_field;
					unset($btn_field);
					$btn_field = array('attributes' => $tmp);
				}
				if (empty($btn_field['attributes']['type'])) $btn_field['attributes']['type'] = 'submit';
				if (empty($btn_field['attributes']['value'])) $btn_field['attributes']['value'] = '送信';
				if (empty($btn_field['attributes']['class'])) $btn_field['attributes']['class'] = 'btn btn-default btn-primary';
				$btn_attr = $btn_field['attributes'];

				$btn_name = isset($btn_field['name']) ? $btn_field['name'] : $btn_field['attributes']['type'];
			}
			if (!empty($btn_name)) $form->add($btn_name, '', $btn_attr);
		}

		foreach($hide_fields as $hide_field_name)
		{
			$form->disable($hide_field_name, $hide_field_name);
			$form->field($hide_field_name)->delete_rule('required');
		}

		return $form;
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
		return array('note', 'album', 'album_image', 'timeline');
	}

	public static function validate_posted_public_flag($current_public_flag = null, $posted_key = 'public_flag')
	{
		$public_flag = \Input::post($posted_key, null);
		if (is_null($public_flag)) throw new \HttpInvalidInputException('Invalid input data');

		$public_flag = (int)$public_flag;
		if (!in_array($public_flag, self::get_public_flags())) throw new \HttpInvalidInputException('Invalid input data');
		if ($current_public_flag && $current_public_flag == $public_flag) throw new \HttpInvalidInputException('Invalid input data');

		return $public_flag;
	}

	public static function validate_params_for_update_public_flag($current_public_flag = null, $posted_key = 'public_flag', $is_check_posted_model = true)
	{
		$public_flag = self::validate_posted_public_flag($current_public_flag, $posted_key);

		$model = null;
		if ($is_check_posted_model)
		{
			$model = \Input::post('model', null);
			if ($model === null || !in_array($model, self::get_have_public_flags_models()))
			{
				throw new \HttpInvalidInputException('Invalid input data');
			}
		}

		return array($public_flag, $model);
	}

	public static function check_is_expanded_public_flag_range($original_public_flag, $changed_public_flag)
	{
		if ($original_public_flag == $changed_public_flag) return false;
		if ($changed_public_flag == PRJ_PUBLIC_FLAG_PRIVATE)  return false;
		if ($original_public_flag == PRJ_PUBLIC_FLAG_PRIVATE) return true;
		if ($changed_public_flag < $original_public_flag) return true;

		return false;
	}

	public static function check_is_reduced_public_flag_range($original_public_flag, $changed_public_flag)
	{
		if ($original_public_flag == $changed_public_flag) return false;
		if ($changed_public_flag == PRJ_PUBLIC_FLAG_PRIVATE)  return true;
		if ($original_public_flag == PRJ_PUBLIC_FLAG_PRIVATE) return false;
		if ($changed_public_flag > $original_public_flag) return true;

		return false;
	}

	public static function get_uri_last_real_segment($check_string = '', $extends = array())
	{
		$last_real_segment = Util_string::get_exploded_last(Input::server('REQUEST_URI'), '/');
		if ($check_string)
		{
			$ext_pattern = '[^\.]+';
			if ($extends)
			{
				$ext_pattern = implode('|', $extends);
			}
			if (!preg_match('/^'.$check_string.'\.('.$ext_pattern.')$/', $last_real_segment)) return false;
		}

		return $last_real_segment;
	}

	public static function convert_img_size_down($size, $type = 'm')
	{
		$size  = strtoupper($size);
		$sizes = Config::get('site.upload.types.img.types.'.$type.'.sizes');
		if (!array_key_exists($size, $sizes)) return false;

		return Arr::previous_by_key($sizes, $size);
	}
}
