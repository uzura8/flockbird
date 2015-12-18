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
			list($album_image, $file) = \Album\Model_AlbumImage::save_with_relations($album_id, $member, FBD_PUBLIC_FLAG_ALL, $file_path, 'album_image_profile');

			$member->file_name = $album_image->file_name;
			$member->save();
		}
		else
		{
			if ($member->file_name) Model_File::delete_with_timeline($member->file_name);

			$options = Site_Upload::get_uploader_options($member->id);
			$uploadhandler = new Site_Uploader($options);
			$file = $uploadhandler->save();
			if (!empty($file->error)) throw new FuelException($file->error);

			$member->file_name = $file->name;
			$member->save();

			// timeline 投稿
			if (is_enabled('timeline')) \Timeline\Site_Model::save_timeline($member->id, FBD_PUBLIC_FLAG_ALL, 'profile_image', $file->id, $member->updated_at);
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
		if (conf('profile.birthday.birthday.isRequired') && empty($member->birthday)) return false;

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

	public static function get_screen_name_additional_info($member_id = null)
	{
		return '';
	}
}
