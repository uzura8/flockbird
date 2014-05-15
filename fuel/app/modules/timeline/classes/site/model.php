<?php
namespace Timeline;

class Site_Model
{
	public static function get_list($self_member_id = 0, $target_member_id = 0, $is_mytimeline = false, $viewType = null, $last_id = 0, $is_over = false, $limit = 0, $sort = array())
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
		if (empty($sort)) $sort = array('id' => 'desc');

		$query = Model_TimelineCache::query()->select('id', 'timeline_id');

		if ($last_id) $query->and_where_open();
		if ($is_mytimeline)
		{
			if ($follow_timeline_ids = self::get_follow_timeline_ids($self_member_id))
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
		if ($last_id) $query->and_where_close();

		if ($last_id)
		{
			$inequality_sign = '>';
			if (empty($sort[1]) || $sort[1] == 'asc')
			{
				$inequality_sign = '<';
			}
			if ($is_over) $inequality_sign = '>';

			$query->where('id', $inequality_sign, $last_id);
		}

		$query->order_by($sort);

		if ($limit)
		{
			$rows_limit = $limit + 1;
			$query->rows_limit($rows_limit);
		}

		$list = $query->get();

		$is_next = false;
		if ($limit)
		{
			$is_next = count($list) > $limit;
			if ($is_next) array_pop($list);
		}

		return array($list, $is_next);
	}

	private static function set_mytimeline_cond(&$query, $self_member_id, $follow_member_ids = null, $friend_member_ids = null)
	{
		if ($follow_member_ids)
		{
			$query->or_where_open();
				$query->where('member_id', 'in', $follow_member_ids);
				$query->where('public_flag', 'in', array(PRJ_PUBLIC_FLAG_ALL, PRJ_PUBLIC_FLAG_MEMBER));
			$query->or_where_close();
		}
		if ($friend_member_ids)
		{
			$query->or_where_open();
				$query->where('member_id', 'in', $friend_member_ids);
				$query->where('public_flag', 'in', array(PRJ_PUBLIC_FLAG_ALL, PRJ_PUBLIC_FLAG_MEMBER, PRJ_PUBLIC_FLAG_FRIEND));
			$query->or_where_close();
		}
		if (is_null($follow_member_ids) && is_null($friend_member_ids))
		{
			$query->or_where('public_flag', 'in', array(PRJ_PUBLIC_FLAG_ALL, PRJ_PUBLIC_FLAG_MEMBER));
		}
		$query->or_where('member_id', $self_member_id);
	}

	public static function get_comments($type, $timeline_id, $foreign_id = 0, $limit = 0)
	{
		if (!$limit) $limit = \Config::get('timeline.articles.comment.limit');
		switch ($type)
		{
			case \Config::get('timeline.types.note'):
				return \Note\Model_NoteComment::get_comments($foreign_id, $limit);

			case \Config::get('timeline.types.album_image_profile'):
				return \Album\Model_AlbumImageComment::get_comments($foreign_id, $limit);
		}

		return Model_TimelineComment::get_comments($timeline_id, $limit);;
	}

	public static function get_foreign_table_obj($type, $foreign_id)
	{
		switch ($type)
		{
			case \Config::get('timeline.types.note'):
				return \Note\Model_Note::find($foreign_id);
			case \Config::get('timeline.types.album'):
			case \Config::get('timeline.types.album_image'):
			case \Config::get('timeline.types.album_image_timeline'):
				return \Album\Model_Album::find($foreign_id);
		}

		return null;
	}

	public static function get_follow_timeline_ids($member_id)
	{
		return \Util_db::conv_col(
			\DB::select('timeline_id')->from('member_follow_timeline')
				->where('member_id', $member_id)
				->order_by('updated_at', 'desc')
				->limit(\Config::get('timeline.follow_timeline_limit_max'))
				->execute()->as_array()
		);
	}

	public static function save_timeline($member_id, $public_flag = null, $type_key = null, $foreign_id = null,  $body = null, Model_Timeline $timeline = null, $child_foreign_ids = array())
	{
		list($type, $foreign_table, $child_foreign_table) = Site_Util::get_timeline_save_values($type_key);
		if (!$timeline) $timeline = Site_Util::get_timeline_object($type_key, $foreign_id);
		$is_new = empty($timeline->id);

		if ($is_new)
		{
			$timeline->member_id = $member_id;
			$timeline->type = $type;
			$timeline->public_flag = is_null($public_flag) ? conf('public_flag.default') : $public_flag;
			$timeline->foreign_table = $foreign_table;
			$timeline->foreign_id = $foreign_id;
			if (!is_null($body)) $timeline->body = $body;
		}
		else
		{
			$timeline->sort_datetime = date('Y-m-d H:i:s');
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
		$deleted_files = array();
		if (Site_Util::check_type($timeline->type, 'album_image_timeline'))
		{
			list($result, $deleted_files) = $timeline->delete_with_album_image($member_id);
		}
		else
		{
			$result = $timeline->delete();
		}

		return array($result, $deleted_files);
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
}
