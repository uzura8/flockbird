<?php
class Site_Util
{
	protected static $active_modules = array();

	public static function get_module_name()
	{
		return (isset(Request::main()->route->module))? Request::main()->route->module : '';
	}

	public static function get_controller_name($delimitter = null)
	{
		if (!isset(Request::main()->route->controller)) return '';

		$controller_name = Str::lower(preg_replace('/^([a-zA-Z0-9_]+\\\)?Controller_/', '', Request::main()->route->controller));
		if (!$delimitter) return $controller_name;

		return str_replace('_', $delimitter, $controller_name);
	}

	public static function get_action_name($is_api = false)
	{
		if ($is_api) return sprintf('%s_%s', Str::lower(Request::main()->get_method()), Request::active()->action);

		return Request::active()->action;
	}

	public static function get_action_path()
	{
		$items = array();
		$module_name = self::get_module_name() ?: '';
		$controller_name = self::get_controller_name('/');
		if ($module_name && $module_name == $controller_name) $module_name = '';

		if ($module_name) $items[] = $module_name;
		$items[] = $controller_name;
		$items[] = self::get_action_name();

		return implode('/', $items);
	}

	public static function check_ssl_required_uri($uri, $is_ssl_required_all = false, $is_check_module = true)
	{
		if(preg_match("#^((https?|ftp):)?//#i", $uri)) return false;
		if($is_ssl_required_all) return true;

		$uri = trim($uri, '/');
		if (in_array($uri, conf('ssl_required.actions'))) return true;

		if ($is_check_module)
		{
			$module = Util_string::get_exploded_first($uri, '/');
			if (in_array($module, conf('ssl_required.modules'))) return true;
		}

		return false;
	}

	public static function check_is_prod_env($is_include_staging = true)
	{
		if (FBD_ENVIRONMENT == 'PRODUCTION') return true;
		if ($is_include_staging && FBD_ENVIRONMENT == 'STAGING') return true;

		return false;
	}

	public static function check_is_dev_env()
	{
		return ! self::check_is_prod_env();
	}

	public static function get_uri_reservede_words($additional_list = array())
	{
		$uri_reservede_words = array(
			// common(汎用)
			'index',
			'list',
			'api',
			'get',
			'post',
			'put',
			'delete',
			'create',
			'edit',
			// site 特有
			'comment',
			'like',
		);
		$uri_reservede_words = array_merge($uri_reservede_words, array_keys(Module::loaded()));// modules
		$uri_reservede_words = array_merge($uri_reservede_words, Util_File::get_file_names(APPPATH.'classes/controller', false, true));// controller files
		if ($additional_list) $uri_reservede_words = array_merge($uri_reservede_words, $additional_list);

		return array_unique($uri_reservede_words);
	}

	public static function check_error_response()
	{
		return preg_match('#^error/?#', Uri::string());
	}

	public static function get_term_file_name($is_check_member_lang_setting = true)
	{
		$config = 'term';
		$lang = Site_Lang::get_lang($is_check_member_lang_setting);
		if ($lang == 'ja') return $config;

		$config .= '_'.$lang;
		if (! Finder::search('config', $config)) return 'term_en';

		return $config;
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
					$btn_attr = array('type'=> $btn_field, 'value' => term('form.do_submit'), 'class' => 'btn btn-default btn-primary');
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
				if (empty($btn_field['attributes']['value'])) $btn_field['attributes']['value'] = term('form.submit');
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

	public static function merge_db_configs($configs, $table)
	{
		$class = 'Model_'.Inflector::camelize($table);
		$db_configs = $class::get_valueas_assoc();
		foreach ($db_configs as $name => $value)
		{
			$key = str_replace('_', '.', $name);
			Arr::set($configs, $key, $value);
		}

		return $configs;
	}

	public static function setup_configs_template($configs)
	{
		$configs = static::merge_db_configs_template($configs);
		$configs = static::setup_configs_template_body($configs);

		return $configs;
	}

	public static function merge_db_configs_template($configs)
	{
		if ($db_configs = Model_Template::query()->get())
		{
			foreach ($db_configs as $db_config)
			{
				$key = str_replace('_', '.', $db_config->name);
				$values = array();
				if ($db_config->type) $values['type'] = $db_config->type;
				if ($db_config->title) $values['title'] = $db_config->title;
				if ($db_config->body) $values['body'] = $db_config->body;
				if (!$values) continue;

				Arr::set($configs, $key, $values);
			}
		}

		return $configs;
	}

	public static function setup_configs_template_body($configs)
	{
		foreach ($configs as $type => $types)
		{
			foreach ($types as $module => $modules)
			{
				foreach ($modules as $item_key => $items)
				{
					if (!isset($items['body'])) continue;
					if (!is_array($items['body'])) continue;

					if (!empty($items['body']['default']['file']))
					{
						$ext = (!empty($items['format'])) ? $items['format'] : 'php';
						$body = file_get_contents(sprintf('%sviews/%s.%s', APPPATH, $items['body']['default']['file'], $ext));
					}
					elseif (!empty($items['body']['default']['value']))
					{
						$body = $items['body']['default']['value'];
					}
					else
					{
						continue;
					}
					$key = implode('.', array($type, $module, $item_key, 'body'));
					Arr::set($configs, $key, $body);
				}
			}
		}

		return $configs;
	}

	public static function html_entity_decode($value, $flags = null, $encoding = null)
	{
		is_null($flags) and $flags = \Config::get('security.htmlentities_flags', ENT_QUOTES);
		is_null($encoding) and $encoding = \Fuel::$encoding;

		return html_entity_decode($value, $flags, $encoding);
	}

	public static function get_public_flags($type = 'default')
	{
		if (!in_array($type, array('default', 'public'))) throw new InvalidArgumentException('First parameter is invalid.');

		$public_flags = array();
		if (is_enabled_public_flag('private') && $type != 'public') $public_flags[] = FBD_PUBLIC_FLAG_PRIVATE;
		if (is_enabled_public_flag('all')) $public_flags[] = FBD_PUBLIC_FLAG_ALL;
		if (is_enabled_public_flag('member')) $public_flags[] = FBD_PUBLIC_FLAG_MEMBER;
		if (is_enabled_public_flag('friend')) $public_flags[] = FBD_PUBLIC_FLAG_FRIEND;

		return $public_flags;
	}

	public static function get_have_public_flags_models()
	{
		return array('note', 'album', 'album_image', 'timeline', 'thread', 'member_profile');
	}

	public static function get_public_flag_name($public_flag)
	{
		return term('public_flag.options.'.$public_flag);
	}

	public static function get_public_flag_icon($public_flag, $with_tag = true)
	{
		$icon_key = 'public_flag.options.'.$public_flag;

		return $with_tag ? icon($icon_key, 'fa fa-') : Config::get('icon.'.$icon_key);
	}

	public static function get_public_flag_coloer_type($public_flag)
	{
		return conf('public_flag.colorTypes.'.$public_flag);
	}

	public static function validate_posted_public_flag($current_public_flag = null, $type = 'default', $posted_key = 'public_flag')
	{
		$public_flag = \Input::post($posted_key, null);
		if (is_null($public_flag)) throw new \HttpInvalidInputException('Invalid input data');

		$public_flag = (int)$public_flag;
		if (!in_array($public_flag, self::get_public_flags($type))) throw new \HttpInvalidInputException('Invalid input data');
		if ($current_public_flag && $current_public_flag == $public_flag) throw new \HttpInvalidInputException('Invalid input data');

		return $public_flag;
	}

	public static function validate_params_for_update_public_flag($current_public_flag = null, $type = 'default', $posted_key = 'public_flag', $is_check_posted_model = true)
	{
		$public_flag = self::validate_posted_public_flag($current_public_flag, $type, $posted_key);

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
		if ($changed_public_flag == FBD_PUBLIC_FLAG_PRIVATE)  return false;
		if ($original_public_flag == FBD_PUBLIC_FLAG_PRIVATE) return true;
		if ($changed_public_flag < $original_public_flag) return true;

		return false;
	}

	public static function get_public_flag_min_range($public_flags)
	{
		if (!is_array($public_flags)) return $public_flags;

		foreach ($public_flags as $public_flag)
		{
			if (!isset($public_flag_min))
			{
				$public_flag_min = $public_flag;
				continue;
			}
			if (self::check_is_expanded_public_flag_range($public_flag_min, $public_flag)) continue;

			$public_flag_min = $public_flag;
		}

		return $public_flag_min;
	}

	public static function check_is_reduced_public_flag_range($original_public_flag, $changed_public_flag)
	{
		if ($original_public_flag == $changed_public_flag) return false;
		if ($changed_public_flag == FBD_PUBLIC_FLAG_PRIVATE)  return true;
		if ($original_public_flag == FBD_PUBLIC_FLAG_PRIVATE) return false;
		if ($changed_public_flag > $original_public_flag) return true;

		return false;
	}

	public static function convert_img_size_down($size, $type = 'm')
	{
		$size  = strtoupper($size);
		$sizes = conf('upload.types.img.types.'.$type.'.sizes');
		if (!array_key_exists($size, $sizes)) return false;

		return Arr::previous_by_key($sizes, $size);
	}

	public static function get_noimage_tag($size, $file_cate = '', $attr = array())
	{
		if (empty($attr['alt'])) $attr['alt'] = 'No image.';

		$noimage_filename  = conf('upload.types.img.noimage_filename');
		$noimage_tag = Asset::img('site/'.$noimage_filename, $attr);
		if ($file_cate)
		{
			if ($size == 'raw')
			{
				$noimage_file_root_path = sprintf('assets/site/%s_%s', $file_cate, $noimage_filename);
			}
			else
			{
				$noimage_file_root_path = sprintf('%s/img/%s/%s/all/%s', FBD_UPLOAD_DIRNAME, $size, $file_cate, $noimage_filename);
			}
			$noimage_tag = Html::img($noimage_file_root_path, $attr);
		}

		return $noimage_tag;
	}

	public static function get_next_sort_order_num($num, $min_interval = null)
	{
		if (is_null($min_interval)) $min_interval = conf('sort_order.interval');
		$num += $min_interval;
		$ext_num = $num % 10;
		if (!$ext_num) return $num;

		return $num + (10 - $ext_num);
	}

	public static function get_redirect_uri($default_uri = '')
	{
		$redirect_uri = Input::post('destination');
		if (!$redirect_uri || !Util_string::check_uri_for_redilrect($redirect_uri))
		{
			$redirect_uri = $default_uri;
		}

		return $redirect_uri;
	}

	public static function check_token_lifetime($point_datetime, $lifetime = null, $target_datetime = null)
	{
		if ($lifetime === false) return true;

		if (is_null($lifetime)) $lifetime = conf('default.token_lifetime');
		$expire = strtotime($point_datetime.' +'.$lifetime);

		$target_time = $target_datetime ? strtotime($target_datetime) : time();

		return $target_time < $expire;
	}

	public static function check_ext_uri($url, $is_admin = false)
	{
		if (preg_match('#(https?\:)?//#', $url))
		{
			$items = parse_url($url);
			if ($items['host'] != FBD_DOMAIN) return true;
			if (!$items['path'] && (FBD_URI_PATH && FBD_URI_PATH != '/')) return true;
			if (strpos($items['path'], FBD_URI_PATH, 0) !== 0) return true;
			if ($is_admin && strpos($items['path'], FBD_URI_PATH.'admin', 0) === false)
			{
				return true;
			}

			return false;
		}

		if ($is_admin && strpos($url, 'admin', 0) === false) return true;

		return false;
	}

	public static function get_confirm_msg($type)
	{
		switch ($type)
		{
			case 'form.delete':
			case 'form.do_delete':
				return '削除します。よろしいですか？';
				break;
			case 'form.publish':
			case 'form.do_publish':
				return term('form.publish').'します。よろしいですか？';
				break;
			case 'form.unpublish':
			case 'form.do_unpublish':
				return term('form.unpublish').'にします。よろしいですか？';
				break;
		}

		return false;
	}

	public static function get_acl_path($path)
	{
		return (substr($path, -1) == '/') ? $path.'index' : $path;
	}

	public static function get_api_uri_update_like($path_prefix, $parent_id)
	{
		return sprintf('%s/like/api/update/%d.json', $path_prefix, $parent_id);
	}

	public static function get_api_uri_get_liked_members($path_prefix, $parent_id)
	{
		return sprintf('%s/like/api/member/%d.html', $path_prefix, $parent_id);
	}

	public static function convert_is_secure2public_flag($is_secure)
	{
		switch ($is_secure)
		{
			case 1:
				return FBD_PUBLIC_FLAG_MEMBER;
			case 0:
			default :
				break;
		}

		return FBD_PUBLIC_FLAG_ALL;
	}

	public static function get_is_secure_label_parts($is_secure)
	{
		$public_flag = static::convert_is_secure2public_flag($is_secure);

		return array(
			static::get_public_flag_name($public_flag),
			static::get_public_flag_icon($public_flag),
			static::get_public_flag_coloer_type($public_flag)
		);
	}

	public static function get_map_markers($locations)
	{
		$markers = array();
		$markers[] = array(
			'lat' => $locations[0],
			'lng' => $locations[1],
		);

		return $markers;
	}

	public static function get_media_uri($uri, $is_absolute_url = false)
	{
		if (FBD_MEDIA_BASE_URL) return Uri::convert_protocol2requested(FBD_MEDIA_BASE_URL.$uri);

		return $is_absolute_url ? Uri::base(false).$uri : Uri::base_path($uri);
	}

	public static function check_is_api()
	{
		if (Site_Util::check_is_dev_env() && Input::param('is_api')) return true;

		return Input::is_ajax();
	}

	public static function get_action_uri($table, $id, $action, $api_response_format = null)
	{
		$action_path_prefix = $action ? '/' : '';
		$controller_path = Site_Model::convert_table2controller_path($table);
		if ($api_response_format) return sprintf('%s/api%s%s/%d.%s', $controller_path, $action_path_prefix, $action, $id, $api_response_format);

		return sprintf('%s%s%s/%d', $controller_path, $action_path_prefix, $action, $id);
	}

	public static function get_active_modules()
	{
		if (static::$active_modules) return static::$active_modules;

		$modules = Module::loaded();
		foreach ($modules as $module => $module_path)
		{
			if (!conf($module.'.isEnabled')) continue;
			static::$active_modules[$module] = $module_path;
		}

		return static::$active_modules;
	}

	public static function validate_tags($tag_string)
	{
		$tags_validated = array();
		$tags = explode(',', $tag_string);
		foreach ($tags as $tag)
		{
			$tag = urldecode($tag);
			if (strlen($tag) > 128) throw new \ValidationFailedException;

			$tags_validated[] = $tag;
		}

		return $tags_validated;
	}

	public static function get_copyright_name()
	{
		if (FBD_ADMIN_COMPANY_NAME)    return FBD_ADMIN_COMPANY_NAME;
		if (FBD_ADMIN_COMPANY_NAME_JP) return FBD_ADMIN_COMPANY_NAME_JP;

		return FBD_SITE_NAME;
	}

	public static function get_image_uri4image_list($images, $file_cate, $size = 'L')
	{
		if (empty($images)) return '';

		$image = is_array($images) ? array_shift($images) : $images;
		$file_name = in_array($file_cate, array('ai', 't')) ? $image->file_name : $image->name;
		$file_size = ($size == 'raw') ? 'raw' : img_size($file_cate, $size);

		return Site_Upload::get_uploaded_file_path($file_name, $file_size, 'img', false, true);
	}

	public static function get_image_uri4file_name($file_name, $size = 'L', $additional_table = '')
	{
		if (!$file_name) return '';
		if (strlen($file_name) < 4) return '';

		$file_cate = Site_Upload::get_file_cate_from_filename($file_name);
		$file_size = img_size($file_cate, $size, $additional_table);

		return Site_Upload::get_uploaded_file_path($file_name, $file_size, 'img', false, true);
	}

	public static function get_public_flag_value4key($public_flag_key)
	{
		if (!in_array($public_flag_key, array('private', 'all', 'member', 'friend'))) throw new InvalidArgumentException('Parameter is invalid.');
		switch ($public_flag_key)
		{
			case 'private':
				return FBD_PUBLIC_FLAG_PRIVATE;
			case 'all':
				return FBD_PUBLIC_FLAG_ALL;
			case 'member':
				return FBD_PUBLIC_FLAG_MEMBER;
			case 'friend':
				return FBD_PUBLIC_FLAG_FRIEND;
		}
	}
}
