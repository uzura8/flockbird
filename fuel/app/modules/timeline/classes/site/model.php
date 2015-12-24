<?php
namespace Timeline;

class Site_Model
{
	public static function get_list($self_member_id = 0, $target_member_id = 0, $is_mytimeline = false, $viewType = null, $max_id = 0, $limit = 0, $is_latest = true, $is_desc = true, $since_id = 0)
	{
		$follow_member_ids = null;
		$friend_member_ids = null;
		if (!$self_member_id || $target_member_id) $is_mytimeline = false;
		if ($is_mytimeline)
		{
			list($follow_member_ids, $friend_member_ids) = Site_Util::get_member_relation_member_ids($self_member_id, $viewType);
		}

		if (!$limit) $limit = (int)\Config::get('timeline.articles.limit');
		if ($limit > \Config::get('timeline.articles.limit_max')) $limit = \Config::get('timeline.articles.limit_max');
		$sort = array('id' => $is_desc ? 'desc' : 'asc');

		$query = Model_TimelineCache::query()->select('id', 'member_id', 'timeline_id', 'type', 'comment_count', 'like_count');

		if ($max_id || $since_id) $query->and_where_open();
		if ($is_mytimeline)
		{
			if ($follow_timeline_ids = Model_MemberFollowTimeline::get_cols('timeline_id', array('member_id' => $self_member_id)))
			{
				$query->or_where_open();
					$query->and_where_open();
						self::set_mytimeline_cond($query, $self_member_id, $follow_member_ids);
					$query->and_where_close();
					$query->where('timeline_id', 'in', $follow_timeline_ids);
					$query->where('is_follow', 1);
				$query->or_where_close();
				$query->or_where_open();
					$query->and_where_open();
						self::set_mytimeline_cond($query, $self_member_id, $follow_member_ids);
					$query->and_where_close();
					$query->where('timeline_id', 'not in', $follow_timeline_ids);
					$query->where('is_follow', 0);
				$query->or_where_close();
			}
			else
			{
				$query->and_where_open();
					self::set_mytimeline_cond($query, $self_member_id, $follow_member_ids);
				$query->and_where_close();
				$query->where('is_follow', 0);
			}
		}
		else
		{
			$is_mypage = ($self_member_id && $target_member_id && ($target_member_id == $self_member_id));
			$basic_cond = \Site_Model::get_where_params4list(
				$target_member_id,
				$self_member_id,
				$is_mypage
			);
			$query->where($basic_cond);
			$query->where('is_follow', 0);
		}
		if ($max_id || $since_id) $query->and_where_close();


		$is_reverse = false;
		if ($limit && $is_latest && !$is_desc)
		{
			$is_desc = true;
			$is_reverse = true;
		}

		if ($since_id)
		{
			$query->where('id', '>', $since_id);
		}
		if ($max_id)
		{
			$query->where('id', '<=', $max_id);
		}

		$query->order_by($sort);

		if ($limit)
		{
			$rows_limit = $limit + 1;
			$query->rows_limit($rows_limit);
		}

		$list = $query->get();

		$next_id = 0;
		if ($limit && count($list) > $limit)
		{
			$next_obj = array_pop($list);
			$next_id = $next_obj->id;
		}

		return array($list, $next_id);
	}

	private static function set_mytimeline_cond(&$query, $self_member_id, $follow_member_ids = null, $friend_member_ids = null)
	{
		if ($follow_member_ids)
		{
			$query->or_where_open();
				$query->where('member_id', 'in', $follow_member_ids);
				$query->where('public_flag', 'in', array(FBD_PUBLIC_FLAG_ALL, FBD_PUBLIC_FLAG_MEMBER));
			$query->or_where_close();
		}
		if ($friend_member_ids)
		{
			$query->or_where_open();
				$query->where('member_id', 'in', $friend_member_ids);
				$query->where('public_flag', 'in', array(FBD_PUBLIC_FLAG_ALL, FBD_PUBLIC_FLAG_MEMBER, FBD_PUBLIC_FLAG_FRIEND));
			$query->or_where_close();
		}
		if (is_null($follow_member_ids) && is_null($friend_member_ids))
		{
			$query->or_where('public_flag', 'in', array(FBD_PUBLIC_FLAG_ALL, FBD_PUBLIC_FLAG_MEMBER));
		}
		$query->or_where('member_id', $self_member_id);
	}

	public static function get_comments($type, $timeline_id, $foreign_id = 0, $limit = 0)
	{
		$model  = 'Timeline\\Model_TimelineComment';
		$params = array('timeline_id' => $timeline_id);
		if (!$limit) $limit = \Config::get('timeline.articles.comment.limit');
		switch ($type)
		{
			case \Config::get('timeline.types.note'):
				$model  = '\Note\Model_NoteComment';
				$params = array('note_id' => $foreign_id);
			case \Config::get('timeline.types.album_image_profile'):
				$model  = '\Album\Model_AlbumImageComment';
				$params = array('album_image_id' => $foreign_id);
		}

		return $model::get_list($params, $limit, true, false, 0, 0, null, false, true);
	}

	public static function get_foreign_table_obj($type, $foreign_id)
	{
		switch ($type)
		{
			case \Config::get('timeline.types.note'):
				return \Note\Model_Note::find($foreign_id);
			case \Config::get('timeline.types.thread'):
				return \thread\Model_thread::find($foreign_id);
			case \Config::get('timeline.types.album'):
			case \Config::get('timeline.types.album_image'):
			case \Config::get('timeline.types.album_image_timeline'):
				return \Album\Model_Album::find($foreign_id);
		}

		return null;
	}

	public static function save_timeline($member_id, $public_flag = null, $type_key = null, $foreign_id = null, $save_datetime = null, $body = null, Model_Timeline $timeline = null, $child_foreign_ids = array())
	{
		if (!Site_Util::check_type_enabled($type_key)) return;

		list($type, $foreign_table, $child_foreign_table) = Site_Util::get_timeline_save_values($type_key);
		if (!$timeline) $timeline = Site_Util::get_timeline_object($type_key, $foreign_id);
		$is_new = empty($timeline->id);

		if (!is_null($body)) $timeline->body = $body;
		if ($is_new)
		{
			$timeline->member_id = $member_id;
			$timeline->type = $type;
			$timeline->public_flag = is_null($public_flag) ? conf('public_flag.default') : $public_flag;
			$timeline->foreign_table = $foreign_table;
			$timeline->foreign_id = $foreign_id;
			$timeline->created_at = $save_datetime ?: \Date::time()->format('mysql');
		}
		else
		{
			if (\Site_Util::check_is_expanded_public_flag_range($timeline->public_flag, $public_flag))
			{
				$timeline->public_flag = $public_flag;
			}
			if ($child_foreign_ids) $timeline->sort_datetime = $save_datetime ?: \Date::time()->format('mysql');
			if ($timeline->is_changed() && $save_datetime) $timeline->updated_at = $save_datetime;
		}
		$timeline->save();

		if ($child_foreign_ids)
		{
			Model_TimelineChildData::save_multiple($timeline->id, $child_foreign_table, $child_foreign_ids);
		}

		return $timeline;
	}

	public static function delete_timeline(Model_Timeline $timeline, $member_id)
	{
		if (Site_Util::check_type($timeline->type, 'album_image_timeline'))
		{
			$timeline->delete_with_album_image($member_id);
		}
		else
		{
			$timeline->delete();
		}
	}

	public static function validate_timeline_viewType($viewType = null)
	{
		$default_viewType = 0;

		if (is_null($viewType)) return $default_viewType;
		if (!in_array($viewType, array_keys(Form_MemberConfig::get_viewType_options()))) return $default_viewType;
		switch ($viewType)
		{
			case '3':
				if (!conf('memberRelation.follow.isEnabled')) return $default_viewType;
				if (!conf('memberRelation.friend.isEnabled')) return $default_viewType;
				break;
			case '2':
				if (!conf('memberRelation.friend.isEnabled')) return $default_viewType;
				break;
			case '1':
				if (!conf('memberRelation.follow.isEnabled')) return $default_viewType;
				break;
		}

		return $viewType;
	}

	public static function delete_note_view_cache4album_image_id($album_image_id)
	{
		if (!$note_id = \Note\Model_NoteAlbumImage::get_note_id4album_image_id($album_image_id)) return;
		if (!$timelines = \Timeline\Model_Timeline::get4foreign_table_and_foreign_ids('note', $note_id)) return;

		$timeline = array_shift($timelines);
		\Timeline\Site_Util::delete_cache($timeline->id, $timeline->type);
	}
}
