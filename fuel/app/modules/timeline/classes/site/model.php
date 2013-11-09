<?php
namespace Timeline;

class Site_Model
{
	public static function get_list($self_member_id = 0, $target_member_id = 0, $is_mypage = false, $is_mytimeline = false, $last_id = 0, $is_over = false, $limit = 0, $sort = array())
	{
		if (!$limit) $limit = (int)\Config::get('timeline.articles.limit');
		if ($limit > \Config::get('timeline.articles.limit_max')) $limit = \Config::get('timeline.articles.limit_max');
		if (empty($sort)) $sort = array('sort_datetime' => 'desc');

		$basic_cond = \Site_Model::get_where_params4list(
			$target_member_id,
			$self_member_id,
			$is_mypage
		);
		$where = $basic_cond;
		if ($is_mytimeline && $self_member_id)
		{
			$where = array();
			$where['and'] = array();
			$where['and'] = $basic_cond;
			$where['and']['or'] = array('member_id', $self_member_id);
		}
		$params = array('where' => $where, 'order_by' => $sort, 'limit' => $limit);

		return \Site_Model::get_pager_list('timeline_cache', $last_id, $params, 'Timeline', true, $is_over, 'timeline_id');
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

	public static function save_timeline($member_id, $values = array(), $type_key = null, Model_Timeline $timeline = null)
	{
		if (!$timeline) $timeline = Model_Timeline::forge();

		if (!isset($values['member_id']))   $values['member_id'] = $member_id;
		if (!isset($values['public_flag'])) $values['public_flag'] = \Config::get('site.public_flag.default');

		$type = $type_key ? \Config::get('timeline.types.'.$type_key) : \Config::get('timeline.types.normal');
		if (!isset($values['type'])) $values['type'] = $type;

		$timeline->set($values);
		$timeline->save();

		return $timeline;
	}

	public static function delete_timeline($foreign_table, $foreign_id)
	{
		$timeline_data = Model_TimelineData::query()->related('timeline')
			->where('foreign_table', $foreign_table)
			->where('foreign_id', $foreign_id)
			->get_one();
		if (!$timeline_data) return false;

		return $timeline_data->timeline->delete();
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
