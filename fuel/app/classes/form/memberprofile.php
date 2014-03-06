<?php

class Form_MemberProfile
{
	private $profiles = null;
	private $member_obj = null;
	private $member_profiles_profile_id_indexed = array();
	private $member_profile_public_flags = array();
	private $validation = null;
	private $validated_values = array();

	public function __construct($page_type, Model_Member $member_obj = null)
	{
		$this->member_obj = $member_obj;
		$this->profiles = Model_Profile::get4page_type($page_type);
		$this->set_member_profiles_profile_id_indexed();
		$this->set_member_profile_public_flags();
	}

	public function set_member_obj(Model_Member $member_obj)
	{
		$this->member_obj = $member_obj;
	}

	public function set_member_profiles_profile_id_indexed()
	{
		$member_profiles = $this->member_obj ? Model_MemberProfile::get4member_id($this->member_obj->id) : array();
		$this->member_profiles_profile_id_indexed = self::convert2member_profiles_profile_id_indexed($this->profiles, $member_profiles);
	}

	public function get_profiles()
	{
		return $this->profiles;
	}

	public function get_validation()
	{
		return $this->validation;
	}

	public function get_member_profile_public_flags()
	{
		return $this->member_profile_public_flags;
	}

	public function validate_public_flag($post_key = 'public_flag')
	{
		foreach ($this->profiles as $profile)
		{
			if (!$profile->is_edit_public_flag) continue;
			$values = Input::post($post_key);
			if (is_null($values[$profile->id]) || !in_array($values[$profile->id], Site_Util::get_public_flags()))
			{
				throw new HttpInvalidInputException('公開範囲の値が不正です。');
			}
		}
	}

	public function seve()
	{
		if (!$this->member_obj) throw new FuelException('Member Object is not set.');;

		if ($this->validation->fieldset()->field('member_name') && $this->member_obj->id)
		{
			$this->member_obj->name = $this->validated_values['member_name'];
			$is_changeed = $this->member_obj->is_changed('name');
			$this->member_obj->save();
			// timeline 投稿
			if (\Module::loaded('timeline') && $is_changeed)
			{
				$body = sprintf('%sを %s に変更しました。', term('member.name'), $this->member_obj->name);
				\Timeline\Site_Model::save_timeline($this->member_obj->id, PRJ_PUBLIC_FLAG_ALL, 'member_name', $this->member_obj->id, $body);
			}
		}

		foreach ($this->profiles as $profile)
		{
			$profile_options = $profile->profile_option;
			if ($profile->form_type == 'checkbox')
			{
				$member_profiles = (array)$this->member_profiles_profile_id_indexed[$profile->id];
				foreach ($profile_options as $profile_option)
				{
					if (isset($this->validated_values[$profile->name]) && in_array($profile_option->id, $this->validated_values[$profile->name]))
					{
						$member_profile = isset($member_profiles[$profile_option->id]) ? $member_profiles[$profile_option->id] : Model_MemberProfile::forge();
						$member_profile->member_id = $this->member_obj->id;
						$member_profile->profile_id = $profile->id;
						$member_profile->profile_option_id = $profile_option->id;
						if ($profile->is_edit_public_flag)
						{
							$member_profile->public_flag = $this->member_profile_public_flags[$profile->id];
						}
						else
						{
							$member_profile->public_flag = $profile->default_public_flag;
						}
						$member_profile->value = $profile_option->label;
						$member_profile->save();
					}
					else
					{
						if (!isset($member_profiles[$profile_option->id])) continue;
						$member_profiles[$profile_option->id]->delete();
					}
				}
			}
			else
			{
				$member_profile = $this->member_profiles_profile_id_indexed[$profile->id];
				if (is_null($member_profile)) $member_profile = Model_MemberProfile::forge();
				$member_profile->member_id = $this->member_obj->id;
				$member_profile->profile_id = $profile->id;
				if ($profile->is_edit_public_flag)
				{
					$member_profile->public_flag = $this->member_profile_public_flags[$profile->id];
				}
				else
				{
					$member_profile->public_flag = $profile->default_public_flag;
				}

				if (in_array($profile->form_type, array('radio', 'select')))
				{
					$profile_option_id = $this->validated_values[$profile->name];
					$member_profile->profile_option_id = $profile_option_id;
					$member_profile->value = $profile_options[$profile_option_id]->label;
				}
				else
				{
					$member_profile->value = $this->validated_values[$profile->name];
				}

				$member_profile->save();
			}
		}
	}

	public function set_validation_message($rule, $message)
	{
		$this->validation->set_message($rule, $message);
	}

	public function set_validation($is_set_member_name = false, $add_fields = array())
	{
		$this->validation = \Validation::forge();
		if ($is_set_member_name)
		{
			$member_field_properties = Form_Util::get_model_field('member', 'name');
			$member_field_attrs = $member_field_properties['attributes'];
			$member_field_attrs['value'] = $this->member_obj ? $this->member_obj->name : '';
			$this->validation->add(
				'member_name',
				$member_field_properties['label'],
				$member_field_attrs,
				$member_field_properties['rules']
			);
		}
		foreach ($this->profiles as $profile)
		{
			$member_profile = $this->member_profiles_profile_id_indexed[$profile->id];
			$rules = array();
			if ($profile->is_required) $rules[] = 'required';
			switch ($profile->form_type)
			{
				case 'input':
				case 'textarea':
					$type = 'text';
					if ($profile->value_type == 'email')
					{
						$type = 'email';
						$rules[] = 'valid_email';
					}
					elseif ($profile->value_type == 'integer')
					{
						$type = 'number';
						$rules[] = array('valid_string', 'numeric');
					}
					elseif ($profile->value_type == 'url')
					{
						$type = 'url';
						$rules[] = 'valid_url';
					}
					elseif ($profile->value_type == 'regexp')
					{
						$rules[] = array('match_pattern', $profile->value_regexp);
					}
					if ($profile->form_type == 'textarea') $type = 'textarea';

					if ($profile->value_min)
					{
						$rule_name = ($profile->value_type == 'integer') ? 'numeric_min' : 'min_length';
						$rules[] = array($rule_name, $profile->value_min);
					}
					if ($profile->value_max)
					{
						$rule_name = ($profile->value_type == 'integer') ? 'numeric_max' : 'max_length';
						$rules[] = array($rule_name, $profile->value_max);
					}

					if ($profile->is_unique)
					{
						$rules[] = array('unique', 'member_profile.value', array(array('profile_id', $profile->id)));
					}

					$value = !is_null($member_profile) ? $member_profile->value : '';

					$this->validation->add(
						$profile->name,
						$profile->caption,
						array('type' => $type, 'value' => $value, 'placeholder' => $profile->placeholder),
						$rules
					);
					break;

				case 'select':
				case 'radio':
					$type = $profile->form_type;
					$options = Util_Orm::conv_cols2assoc($profile->profile_option, 'id', 'label');
					if (is_null($member_profile))
					{
						$options_keys = array_keys($options);
						$value = array_shift($options_keys);
					}
					else
					{
						$value = $member_profile->profile_option_id;
					}
					$rules[] = array('valid_string', 'numeric');
					$rules[] = array('in_array', array_keys($options));

					$this->validation->add(
						$profile->name,
						$profile->caption,
						array('type' => $type, 'value' => $value, 'options' => $options),
						$rules
					);
					break;
				case 'checkbox':
					$type = $profile->form_type;
					$options = Util_Orm::conv_cols2assoc($profile->profile_option, 'id', 'label');
					$value = !is_null($member_profile) ? Util_Orm::conv_col2array($member_profile, 'profile_option_id') : array();
					$rules[] = array('checkbox_val', $options);
					if ($profile->is_required) $rules[] = array('checkbox_require', 1);

					$this->validation->add(
						$profile->name,
						$profile->caption,
						array('type' => $type, 'value' => $value, 'options' => $options),
						$rules
					);
					break;
			}
		}
		foreach ($add_fields as $name => $params)
		{
			$this->add_field($name, $params);
		}
	}

	public function add_field($name, $params = array())
	{
		$this->validation->add(
			$name,
			isset($params['label']) ? $params['label'] : '',
			isset($params['attributes']) ? $params['attributes'] : array(),
			isset($params['rules']) ? $params['rules'] : array()
		);
	}

	// 識別名の変更がない場合は unique を確認しない
	public function remove_unique_restraint_for_updated_value()
	{
		foreach ($this->profiles as $profile)
		{
			if (!$profile->is_unique) continue;
			if (!in_array($profile->form_type, array('input', 'textarea'))) continue;
			if (!$member_profile = $this->member_profiles_profile_id_indexed[$profile->id]) continue;
			if (trim(\Input::post($profile->name)) != $member_profile->value) continue;

			$this->validation->fieldset()->field($profile->name)->delete_rule('unique');
		}
	}

	public function validate()
	{
		$result = $this->validation->run();
		$this->validated_values = $this->validation->validated();

		return $result;
	}

	public function get_validation_errors()
	{
		return $this->validation->show_errors();
	}

	public function get_validated_values()
	{
		return $this->validated_values;
	}

	private static function convert2member_profiles_profile_id_indexed($profiles, $member_profiles)
	{
		$member_profiles_profile_id_indexed = array();
		foreach ($profiles as $profile)
		{
			$member_profile = self::get_member_profile($member_profiles, $profile->id, $profile->form_type == 'checkbox') ?: null;
			$member_profiles_profile_id_indexed[$profile->id] = $member_profile;
		}

		return $member_profiles_profile_id_indexed;
	}

	private function set_member_profile_public_flags()
	{
		$posted_public_flags = Input::post('public_flag');
		foreach ($this->profiles as $profile)
		{
			if (!$profile->is_edit_public_flag) continue;

			$member_profile = $this->member_profiles_profile_id_indexed[$profile->id];
			if (is_array($member_profile))
			{
				$member_profile = array_shift($member_profile);
			}
			$public_flag = isset($member_profile->public_flag) ? $member_profile->public_flag : $profile->default_public_flag;
			if (isset($posted_public_flags[$profile->id])) $public_flag = $posted_public_flags[$profile->id];

			$this->member_profile_public_flags[$profile->id] = $public_flag;
		}
	}

	private static function get_member_profile($member_profiles, $profile_id, $is_array = false)
	{
		foreach ($member_profiles as $member_profile)
		{
			if ($member_profile->profile_id == $profile_id)
			{
				if (!$is_array) return $member_profile;

				if (!isset($member_profile_list)) $member_profile_list = array();
				$member_profile_list[$member_profile->profile_option_id] = $member_profile;
			}
		}
		if (isset($member_profile_list)) return $member_profile_list;

		return false;
	}
}
