<?php
namespace Timeline;

class Site_Model
{
	public static function get_list($self_member_id = 0, $target_member_id = 0, $is_mypage = false, $is_mytimeline = false, $last_id = 0, $is_over = false, $limit = 0, $sort = array())
	{
		if (!$limit) $limit = (int)\Config::get('timeline.articles.limit');
		if ($limit > \Config::get('timeline.articles.limit_max')) $limit = \Config::get('timeline.articles.limit_max');
		if (empty($sort)) $sort = array('id' => 'desc');

		$query = Model_TimelineCache::query()->select('id', 'timeline_id');

		$basic_cond = \Site_Model::get_where_params4list(
			$target_member_id,
			$self_member_id,
			$is_mypage
		);

		if ($last_id) $query->and_where_open();
		if ($is_mytimeline && $self_member_id)
		{
			if ($follow_timeline_ids = self::get_follow_timeline_ids($self_member_id))
			{
				$query->or_where_open();
					$query->and_where_open();
						$query->where($basic_cond);
						$query->or_where('member_id', $self_member_id);
					$query->and_where_close();
					$query->where('timeline_id', 'in', $follow_timeline_ids);
					$query->where('is_follow', 1);
				$query->or_where_close();
				$query->or_where_open();
					$query->and_where_open();
						$query->where($basic_cond);
						$query->or_where('member_id', $self_member_id);
					$query->and_where_close();
					$query->where('timeline_id', 'not in', $follow_timeline_ids);
					$query->where('is_follow', 0);
				$query->or_where_close();
			}
			else
			{
				$query->and_where_open();
					$query->where($basic_cond);
					$query->or_where('member_id', $self_member_id);
				$query->and_where_close();
				$query->where('is_follow', 0);
			}
		}
		else
		{
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

	public static function get_comments($type, $timeline_id, $foreign_table = '', $foreign_id = 0, $limit = 0)
	{
		if (!$limit) $limit = \Config::get('timeline.articles.comment.limit');

		if ($type == \Config::get('timeline.types.note'))
		{
			return \Note\Model_NoteComment::get_comments($foreign_id, $limit);
		}
		elseif ($type == \Config::get('timeline.types.profile_image') && $foreign_table == 'album_image')
		{
			return \Album\Model_AlbumImageComment::get_comments($foreign_id, $limit);
		}

		return Model_TimelineComment::get_comments($timeline_id, $limit);;
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
		$is_new = empty($timeline->id) ? true : false;

		if ($is_new)
		{
			$timeline->member_id = $member_id;
			$timeline->type = $type;
			$timeline->public_flag = is_null($public_flag) ? \Config::get('site.public_flag.default') : $public_flag;
			$timeline->foreign_table = $foreign_table;
			$timeline->foreign_id = $foreign_id;
			if (!is_null($body)) $timeline->body = $body;
		}
		$timeline->save();

		if ($child_foreign_ids)
		{
			Model_TimelineChildData::save_multiple($timeline->id, $child_foreign_table, $child_foreign_ids);
		}

		return $timeline;
	}

	public static function delete_timeline($foreign_table, $foreign_id)
	{
		$timeline = Model_Timeline::query()
			->where('foreign_table', $foreign_table)
			->where('foreign_id', $foreign_id)
			->get_one();
		if (!$timeline) return false;

		return $timeline->delete();
	}

	public static function delete_timelines($foreign_table, array $foreign_ids)
	{
		$timeline_ids = \Util_db::conv_col(
			\DB::select('timeline_id')->from('timeline_data')
				->where('foreign_table', $foreign_table)
				->where('foreign_id', 'in', $foreign_ids)
				->execute()->as_array()
		);
		if (!$timeline_ids) return false;

		return \DB::delete('timeline')->where('id', 'in', $timeline_ids)->execute();
	}
}
