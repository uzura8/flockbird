<?php

class Site_Member
{
	public static function get_prohibited_words_for_name()
	{
		return \Site_Util::get_uri_reservede_words(Util_File::get_file_names(APPPATH.'classes/controller/member', false, true));
	}

	public static function save_profile_image(Model_Member $member, $file_path = null)
	{
		if (conf('upload.types.img.types.m.save_as_album_image'))
		{
			$album_id = \Album\Model_Album::get_id_for_foreign_table($member->id, 'member');
			list($album_image, $file) = \Album\Model_AlbumImage::save_with_relations($album_id, $member, conf('public_flag.maxRange'), $file_path, 'album_image_profile');

			$member->file_name = $album_image->file_name;
			$member->save();
		}
		else
		{
			if ($member->file_name) Model_File::delete_with_timeline($member->file_name);

			$options = Site_Upload::get_uploader_options($member->id);
			$uploadhandler = new Site_Uploader($options);
			$file = $uploadhandler->save($file_path);
			if (!empty($file->error)) throw new FuelException($file->error);

			$member->file_name = $file->name;
			$member->save();

			// timeline 投稿
			if (is_enabled('timeline')) \Timeline\Site_Model::save_timeline($member->id, conf('public_flag.maxRange'), 'profile_image', $file->id, $member->updated_at);
		}

		return $file;
	}

	public static function get_access_from($target_member_id, $self_member_id = 0)
	{
		if (!$self_member_id) return 'guest';
		if ($target_member_id == $self_member_id) return 'self';

		return 'member';
	}

	public static function get_access_from_member_relation($member_id, $self_member_id = 0)
	{
		if (!$self_member_id) return 'others';

		if ($member_id == $self_member_id) return 'self';
		if (Model_MemberRelation::check_relation('friend', $self_member_id, $member_id)) return 'friend';

		return 'member';
	}

	public static function get_config($member_id, $name)
	{
		$value = Model_MemberConfig::get_value($member_id, $name);
		if (!is_null($value)) return $value;

		return self::get_config_default($name);
	}

	public static function get_config_default($name)
	{
		return conf('member_config_default.'.$name);
	}

	public static function get_config_array($member_id, $names = null)
	{
		$member_configs = Util_Orm::conv_cols2assoc(Model_MemberConfig::get4member_id($member_id, $names), 'name', 'value');
		foreach ($names as $name)
		{
			if (isset($member_configs[$name])) continue;

			$value = self::get_config_default($name);
			if (is_null($value)) continue;

			$member_configs[$name] = $value;
		}

		return $member_configs;
	}

	public static function get_member_ids4config_value($config_name, $config_value, $target_member_ids = array(), $is_fill_default_value = true)
	{
		if (!is_array($target_member_ids)) $target_member_ids = (array)$target_member_ids;

		$member_configs = Util_Orm::conv_cols2assoc(Model_MemberConfig::get4name_and_member_ids($config_name, $target_member_ids), 'member_id', 'value');
		$return_member_ids = array();
		foreach ($target_member_ids as $member_id)
		{
			if (isset($member_configs[$member_id]))
			{
				$value = $member_configs[$member_id];
			}
			else
			{
				if (!$is_fill_default_value) continue;
				$value = self::get_config_default($config_name);
			}
			if ($value != $config_value) continue;

			$return_member_ids[] = $member_id;
		}

		return $return_member_ids;
	}

	public static function get_member_config_with_default_value($member_id)
	{
		$member_config = new stdClass();
		$default_values = Form_MemberConfig::get_default_values();
		foreach ($default_values as $name => $value)
		{
			$member_config->$name = $value;
		}

		$member_config_objs = Model_MemberConfig::get4member_id($member_id);
		foreach ($member_config_objs as $obj)
		{
			$member_config->{$obj->name} = $obj->value;
		}

		return $member_config;
	}

	public static function remove(Model_Member $member)
	{
		if (conf('member.leave.isRemoveOnBatch'))
		{
			$name = $member->name;
			$member_auth = Model_MemberAuth::query()->where('member_id', $member->id)->get_one();
			$email = $member_auth ? $member_auth->email : '';

			DB::start_transaction();
			$member_delete_queue = Model_MemberDeleteQueue::forge(array(
				'member_id' => $member->id,
				'name' => $name,
				'email' => $email,
			));
			$member_delete_queue->save();
			$member_auth->delete();
			DB::commit_transaction();
			$message = term('site.left').'を'.term('form.reserve').'しました。';
		}
		else
		{
			static::delete($member->id);
			$message = term('site.left').'が'.term('form.complete').'しました。';
		}

		return $message;
	}

	public static function delete($member_id)
	{
		if (!$member = Model_Member::query()->related('member_auth')->where('id', $member_id)->get_one())
		{
			throw new FuelException('Member not exists.');
		}
		$name = $member->name;
		$email = !empty($member->member_auth->email) ? $member->member_auth->email : '';

		if (is_enabled('timeline')) \Timeline\Site_NoOrmModel::delete_timeline4member_id($member_id);
		if (is_enabled('album')) \Album\Site_NoOrmModel::delete_album4member_id($member_id);
		if (is_enabled('note')) \Note\Site_NoOrmModel::delete_note4member_id($member_id);
		if (is_enabled('message')) \Message\Site_NoOrmModel::delete_message_recieved4member_id($member_id);
		static::delete_file_all4member_id($member_id);
		static::delete_file_all4member_id($member_id, true);

		DB::start_transaction();
		if (!$member->delete()) throw new FuelException('Delete user error. user_id:'.$member_id);
		DB::commit_transaction();

		if ($name && $email)
		{
			$mail = new Site_Mail('memberLeave');
			$mail->send($email, array('to_name' => $name));
		}
	}

	public static function delete_file_all4member_id($member_id, $is_tmp = false, $limit = 0)
	{
		if (!$limit) $limit = conf('batch.default.limit.model.delete.file', 10);

		$model = Site_Model::get_model_name($is_tmp ? 'file_tmp' : 'file');
		$query = $model::query();
		if ($is_tmp) $query->where('user_type', 0);
		$query->where('member_id', $member_id)->limit($limit);

		while ($objs = $query->get())
		{
			DB::start_transaction();
			foreach ($objs as $obj) $obj->delete();
			DB::commit_transaction();
		}
	}

	public static function get_accept_member_register_types()
	{
		$register_types = array(0);
		if (FBD_FACEBOOK_APP_ID && FBD_FACEBOOK_APP_SECRET) $register_types[] = 1;
		if (FBD_TWITTER_APP_ID && FBD_TWITTER_APP_SECRET)   $register_types[] = 2;
		if (FBD_GOOGLE_APP_ID && FBD_GOOGLE_APP_SECRET)     $register_types[] = 3;

		return $register_types;
	}

	public static function check_saved_member_profile_required(Model_Member $member, $disp_type = 'regist')
	{
		if (!in_array($disp_type, array('regist'))) throw new InvalidArgumentException('Second parameter is invalid.');

		if (conf('profile.sex.isRequired') && empty($member->sex)) return false;
		if (conf('profile.birthday.birthyear.isRequired') && empty($member->birthyear)) return false;
		if (conf('profile.birthday.birthdate.isRequired') && empty($member->birthdate)) return false;

		if (!$profiles = Model_Profile::get_assoc('id', 'is_required', array('is_required' => 1, 'is_disp_'.$disp_type => 1))) return true;
		$profileds_for_check = $profiles;
		if (!$member_profiles = Model_MemberProfile::get4member_id($member->id)) return false;
		foreach ($member_profiles as $member_profile)
		{
			unset($profileds_for_check[$member_profile->profile_id]);

			if (empty($profiles[$member_profile->profile_id])) continue;
			if ($member_profile->profile_option_id) continue;
			if ($member_profile->value) continue;
			if (strlen($member_profile->value)) continue;

			return false;
		}
		if ($profileds_for_check) return false;

		return true;
	}

	public static function get_groups()
	{
		return conf('group.options', 'member');
	}

	public static function get_group_keys()
	{
		return array_keys(static::get_groups());
	}

	public static function get_group_key($group_value)
	{
		if (!$groups = conf('group.options', 'member')) return false;

		return array_search($group_value, $groups);
	}

	public static function get_group_value($group_key)
	{
		if (!$groups = conf('group.options', 'member')) return false;
		if (empty($groups[$group_key])) return false;

		return $groups[$group_key];
	}

	public static function get_group_label($group_value)
	{
		if (false === ($group_key = static::get_group_key($group_value))) return symbol('noValue');

		return static::get_group_label4key($group_key);
	}

	public static function get_group_label4key($group_key)
	{
		return term('member.group.options.'.$group_key);
	}

	public static function get_group_options($target_group_keys = array())
	{
		$options = array();
		$groups = static::get_groups();
		foreach ($groups as $key => $value)
		{
			if ($target_group_keys && !in_array($key, $target_group_keys)) continue;
			$options[$value] = \Site_Member::get_group_label($value);
		}

		return $options;
	}

	public static function get_screen_name_additional_info($member_id = null)
	{
		return '';
	}

	public static function get_detail_search_pager_list($self_member_id = 0, $limit = null, $page = 1, $order_by = array('member_id' => 'desc'))
	{
		$accept_public_flags = array(FBD_PUBLIC_FLAG_ALL);
		if ($self_member_id) $accept_public_flags[] = FBD_PUBLIC_FLAG_MEMBER;

		$form_member_profile = new Form_MemberProfile('search');
		$form_member_profile->set_validation(array(), 'member_search');
		$inputs = $form_member_profile->validate_for_search();
		$profiles = $form_member_profile->get_profiles();

		$query = DB::select()->from('member_profile_cache')->as_object();

		// member.name
		if (!empty($inputs['member_name'])
				&& $form_member_profile->check_is_enabled_member_field('name')
				&& $wheres = Site_Model::get_search_word_conds($inputs['member_name'], 'name', false, false, false))
		{
			$query->where_open();
			foreach ($wheres as $where) $query->where($where[0], $where[1], $where[2]);
			$query->where_close();
		}
		// member.sex
		if (!empty($inputs['member_sex']) && $form_member_profile->check_is_enabled_member_field('sex'))
		{
			$query->where_open();
			$query->where('sex', $inputs['member_sex']);
			$query->where('sex_public_flag', 'in', $accept_public_flags);
			$query->where_close();
		}

		// profile
		foreach ($profiles as $profile)
		{
			$name = $profile->name;
			switch ($profile->form_type)
			{
				case 'input':
				case 'textarea':
					if ($wheres = Site_Model::get_search_word_conds($inputs[$name], $name, false, false, false))
					{
						$query->where_open();
						foreach ($wheres as $where) $query->where($where[0], $where[1], $where[2]);
						$query->where($name.'_public_flag', 'in', $accept_public_flags);
						$query->where_close();
					}
					break;

				case 'select':
				case 'radio':
					if ($inputs[$name])
					{
						$query->where_open();
						$query->where($name, $inputs[$name]);
						$query->where($name.'_public_flag', 'in', $accept_public_flags);
						$query->where_close();
					}
					break;

				case 'checkbox':
					if ($member_ids = Model_MemberProfile::get_member_ids4profile_id_option_ids($profile->id, (array)$inputs[$name], $accept_public_flags))
					{
						$query->where_open();
						$query->where('member_id', 'in', array_unique($member_ids));
						$query->where_close();
					}
					break;
			}
		}

		// order by
		foreach ($order_by as $key => $value)
		{
			if (is_numeric($key))
			{
				$query->order_by($value);
			}
			else
			{
				$query->order_by($key, $value);
			}
		}

		// limit, offset
		$page = (int)$page;
		if ($page < 1) $page = 1;

		$offset = 0;
		$offset = $limit * ($page - 1);
		$query->limit($limit);
		$query->offset($offset);

		// execute
		$list = $query->execute();
		$count = DB::count_last_query();

		$next_page = ($limit && $count > $offset + $limit) ? $page + 1 : 0;

		return array(
			'profiles' => $form_member_profile->get_profiles(),
			'val' => $form_member_profile->get_validation(),
			'inputs' => $inputs,
			'list' => $query->execute(),
			'page' => $page,
			'next_page' => $next_page,
			'count' => $count
		);
	}

	public static function get_admin_member_id()
	{
		return conf('original_user_id.site');
	}
}

