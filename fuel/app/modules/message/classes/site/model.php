<?php
namespace Message;

class Site_Model
{
	public static function get_talks($self_member_id = 0, $type_key = null, $related_id = 0, $max_id = 0, $limit = 0, $is_latest = true, $is_desc = false, $since_id = 0)
	{
		if (!$limit) $limit = (int)\Config::get('message.articles.limit');
		if ($limit > \Config::get('message.articles.limit_max')) $limit = \Config::get('message.articles.limit_max');
		list($model, $id_prop, $relateds) = static::get_related_model_info4type($type_key);
		$query = $model::query();
		if ($relateds) $query->related($relateds);
		$query->where($id_prop, $related_id);

		$is_reverse = false;
		if ($limit && $is_latest && !$is_desc)
		{
			$is_desc = true;
			$is_reverse = true;
		}
		$sort = array('id' => $is_desc ? 'desc' : 'asc');

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
		if ($is_reverse) $list = array_reverse($list);

		return array($list, $next_id);
	}

	public static function get_related_model_info4type($type_key)
	{
		$type_key = Site_Util::get_key4type($type_key);
		switch ($type_key)
		{
			case 'member':
				$model = '\Message\Model_MessageSentMemberRelationUnit';
				$id_prop = 'member_relation_unit_id';
				$relateds = array('message');
				break;
			case 'group':
				$model = '\Message\Model_MessageSentGroup';
				$id_prop = 'group_id';
				$relateds = array('message');
				break;
		}

		return array($model, $id_prop, $relateds);
	}

	public static function get_member_ids_joined_related_model($type, $related_id, $exclude_member_id = 0)
	{
		$member_ids = array();
		switch ($type_key = Site_Util::get_key4type($type))
		{
			case 'member':
				$member_relation_unit = \Model_MemberRelationUnit::get_one4id($related_id);
				$member_ids[] = $member_relation_unit->member_id_lower;
				$member_ids[] = $member_relation_unit->member_id_upper;
				break;
			case 'group':
				$member_ids = \Model_GroupMember::get_member_ids4group_id($related_id);
				break;
		}
		if ($exclude_member_id) $member_ids = \Util_Array::unset_item($exclude_member_id, $member_ids);

		return $member_ids;
	}

	public static function save_related_model($message_id, $type, $related_id, $datetime = null)
	{
		list($related_model, $related_id_prop) = static::get_related_model_info4type($type);
		$related_model_obj = $related_model::forge();
		$related_model_obj->{$related_id_prop} = $related_id;
		$related_model_obj->message_id = $message_id;
		if ($datetime) $related_model_obj->created_at = $datetime;
		$related_model_obj->save();

		return $related_model_obj;
	}
}
