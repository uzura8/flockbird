<?php

class Controller_Member_Profile extends Controller_Member
{
	protected $check_not_auth_action = array(
		'index',
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Mmeber_profile index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index($id = null)
	{
		list($is_mypage, $member) = $this->check_auth_and_is_mypage($id);
		$this->set_title_and_breadcrumbs(sprintf('%sの%s', $is_mypage ? '自分' : $member->name.'さん', term('profile')), null, $member);
		$this->template->subtitle = $is_mypage ? \View::forge('member/profile/_parts/profile_subtitle') : '';
		$this->template->content = View::forge('member/profile/index', array('member' => $member, 'is_mypage' => $is_mypage));
	}

	/**
	 * Mmeber_profile edit
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_edit()
	{
		$profiles = Model_Profile::get4page_type('config');
		$member_profiles = Model_MemberProfile::get4member_id($this->u->id);
		$member_profiles_profile_id_indexed = self::convert2member_profiles_profile_id_indexed($profiles, $member_profiles);
		$member_profile_public_flags = self::get_member_profile_public_flags($profiles, $member_profiles_profile_id_indexed);
		$val = self::get_validation_object($profiles, $member_profiles_profile_id_indexed);
		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();
			try
			{
				self::validate_public_flag($profiles);

				// 識別名の変更がない場合は unique を確認しない
				$val = self::check_value_updated_for_unique($val, $profiles, $member_profiles_profile_id_indexed);

				if (!$val->run()) throw new \FuelException($val->show_errors());
				$post = $val->validated();
				\DB::start_transaction();
				self::seve_member_profile($this->u->id, $profiles, $member_profiles_profile_id_indexed, $post, $member_profile_public_flags);
				\DB::commit_transaction();

				\Session::set_flash('message', term('profile').'を編集しました。');
				\Response::redirect('member/profile');
			}
			catch(\FuelException $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				\Session::set_flash('error', $e->getMessage());
			}
		}
		$this->set_title_and_breadcrumbs(term('profile').term('form.edit'), array('member/profile' => '自分の'.term('profile')), $this->u);
		$this->template->content = View::forge('member/profile/edit', array('val' => $val, 'profiles' => $profiles, 'public_flags' => $member_profile_public_flags));
	}

	private static function convert2member_profiles_profile_id_indexed($profiles, $member_profiles)
	{
		$member_profiles_profile_id_indexed = array();
		foreach ($profiles as $profile)
		{
			$member_profile = self::get_member_profile($member_profiles, $profile->id) ?: null;
			$member_profiles_profile_id_indexed[$profile->id] = $member_profile;
		}

		return $member_profiles_profile_id_indexed;
	}

	private static function validate_public_flag($profiles, $post_key = 'public_flag')
	{
		foreach ($profiles as $profile)
		{
			if (!in_array($profile->form_type, array('input', 'textarea', 'select'))) continue;// !!!!!!!開発用!!!!!

			if (!$profile->is_edit_public_flag) continue;
			$values = Input::post($post_key);
			if (is_null($values[$profile->id]) || !in_array($values[$profile->id], Site_Util::get_public_flags()))
			{
				throw new HttpInvalidInputException('公開範囲の値が不正です。');
			}
		}
	}

	private static function get_member_profile_public_flags($profiles, $member_profiles_profile_id_indexed)
	{
		$public_flags = array();
		$posted_public_flags = Input::post('public_flag');
		foreach ($profiles as $profile)
		{
			if (!$profile->is_edit_public_flag) continue;

			$member_profile = $member_profiles_profile_id_indexed[$profile->id];
			$public_flag = isset($member_profile->public_flag) ? $member_profile->public_flag : $profile->default_public_flag;
			if (isset($posted_public_flags[$profile->id])) $public_flag = $posted_public_flags[$profile->id];

			$public_flags[$profile->id] = $public_flag;
		}

		return $public_flags;
	}

	private static function seve_member_profile($member_id, $profiles, $member_profiles_profile_id_indexed, $posted_values, $member_profile_public_flags)
	{
		foreach ($profiles as $profile)
		{
			if (!in_array($profile->form_type, array('input', 'textarea', 'select'))) continue;// !!!!!!!開発用!!!!!

			$member_profile = $member_profiles_profile_id_indexed[$profile->id];
			if (is_null($member_profile)) $member_profile = Model_MemberProfile::forge();
			$member_profile->member_id = $member_id;
			$member_profile->profile_id = $profile->id;
			if ($profile->is_edit_public_flag) $member_profile->public_flag = $member_profile_public_flags[$profile->id];

			$profile_options = $profile->profile_option;
			switch ($profile->form_type)
			{
				case 'input':
				case 'textarea':
					$member_profile->value = $posted_values[$profile->name];
					break;
				case 'select':
					$profile_option_id = $posted_values[$profile->name];
					$member_profile->profile_option_id = $profile_option_id;
					$member_profile->value = $profile_options[$profile_option_id]->label;
					break;
			}
			$member_profile->save();
		}
	}

	private static function get_member_profile($member_profiles, $profile_id)
	{
		foreach ($member_profiles as $member_profile)
		{
			if ($member_profile->profile_id == $profile_id) return $member_profile;
		}

		return false;
	}

	private static function get_validation_object($profiles, $member_profiles_profile_id_indexed)
	{
		$val = \Validation::forge();
		foreach ($profiles as $profile)
		{
			$member_profile = $member_profiles_profile_id_indexed[$profile->id];
			$value = !is_null($member_profile) ? $member_profile->value : '';
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

					$val->add(
						$profile->name,
						$profile->caption,
						array('type' => $type, 'value' => $value, 'placeholder' => $profile->placeholder),
						$rules
					);
					break;

				case 'select':
					$type = 'select';
					$options = Util_Orm::conv_cols2assoc($profile->profile_option, 'id', 'label');

					$value = !is_null($member_profile) ? $member_profile->profile_option_id : 0;
					$rules[] = array('valid_string', 'numeric');
					$rules[] = array('in_array', array_keys($options));

					if ($profile->is_unique)
					{
						$rules[] = array('unique', 'member_profile.profile_option_id', array(array('profile_id', $profile->id)));
					}

					$val->add(
						$profile->name,
						$profile->caption,
						array('type' => $type, 'value' => $value, 'options' => $options, 'placeholder' => $profile->placeholder),
						$rules
					);
					break;
			}
		}

		return $val;
	}

	private static function check_value_updated_for_unique($val, $profiles, $member_profiles_profile_id_indexed)
	{
		foreach ($profiles as $profile)
		{
			if (!$profile->is_unique) continue;
			if (!in_array($profile->form_type, array('input', 'textarea', 'select'))) continue;
			if (!$member_profile = $member_profiles_profile_id_indexed[$profile->id]) continue;

			$check_field = 'value';
			if ($profile->form_type == 'select') $check_field = 'profile_option_id';
			if (trim(\Input::post($profile->name)) != $member_profile->$check_field) continue;

			$val->fieldset()->field($profile->name)->delete_rule('unique');
		}

		return $val;
	}
}
