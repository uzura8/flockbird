<?php
namespace Message;

class Site_Model
{
	public static function get_talks($type_key = null, $related_id = 0, $max_id = 0, $limit = 0, $is_latest = true, $is_desc = false, $since_id = 0)
	{
		if (!$limit) $limit = (int)view_params('limit', 'message');
		$limit_max = (int)view_params('limitMax', 'message');
		if ($limit > $limit_max) $limit = $limit_max;
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

	public static function convert_message_recieved_summary_to_array_for_view(Model_MessageRecievedSummary $recieved_summary, $member_id)
	{
		$row = $recieved_summary->to_array();
		$row['type_key'] = Site_Util::get_key4type($recieved_summary->type);
		$row['member_from'] = \Model_Member::get_one_basic4id($recieved_summary->message->member_id);
		$row['is_read'] = (int)$row['is_read'];
		$row['detail_page_uri'] = Site_Util::get_detail_page_uri(
			$recieved_summary->type,
			$recieved_summary->type_related_id,
			$recieved_summary->last_message_id,
			$recieved_summary->message->member_id
		);

		return $row;
	}

	public static function get_unread_message_ids($type_key, $message_sent_objs, $self_member_id, $member_ids)
	{
		if (empty($message_sent_objs)) return array();
		if (!$message_ids = \Util_Orm::conv_col2array($message_sent_objs, 'message_id')) return array();
		if (!$member_ids = \Util_Array::unset_item($self_member_id, $member_ids)) return array();
		if ($type_key == 'member' && count($member_ids) > 1) throw new InvalidArgumentException('Forth parameter is invalid.');

		return Model_MessageRecieved::get_unread_message_ids4member_ids($member_ids, $message_ids);
	}

	public static function save_send_target($member_id_from, $message_id, $type, $related_ids, $optional_props = array())
	{
		$type = Site_Util::get_type4key($type);
		$type_key = Site_Util::get_key4type($type);
		if (!is_array($related_ids)) $related_ids = (array)$related_ids;

		if (in_array($type_key, array('site_info_all', 'system_info'))) return array();
		if (in_array($type_key, array('member', 'group')))
		{
			if (count($related_ids) != 1) throw new \InvalidArgumentException('Forth parameter is invalid.');
		}

		$send_target_member_ids = array();
		foreach ($related_ids as $related_id)
		{
			static::save_related_model($message_id, $type, $related_id, $optional_props);
		}
	}

	public static function save_related_model($message_id, $type, $related_id, $optional_props = array())
	{
		list($related_model, $related_id_prop) = static::get_related_model_info4type($type);
		$related_model_obj = $related_model::forge();
		$related_model_obj->{$related_id_prop} = $related_id;
		$related_model_obj->message_id = $message_id;
		if ($optional_props)
		{
			foreach ($optional_props as $prop => $value)
			{
				$related_model_obj->{$prop} = $value;
			}
		}
		$related_model_obj->save();

		return $related_model_obj;
	}

	protected static function get_related_model_info4type($type_key)
	{
		$type_key = Site_Util::get_key4type($type_key);
		switch ($type_key)
		{
			case 'member':
				$model = '\Message\Model_MessageSentMemberRelationUnit';
				$id_prop = 'member_relation_unit_id';
				$relateds = 'message';
				break;
			case 'group':
				$model = '\Message\Model_MessageSentGroup';
				$id_prop = 'group_id';
				$relateds = 'message';
				break;
			case 'site_info':
				$model = '\Message\Model_MessageSentAdmin';
				$id_prop = 'member_id';
				$relateds = 'message';
				break;
		}

		return array($model, $id_prop, $relateds);
	}

	public static function get_related_ids4message_id($message_id, $type)
	{
		list($model, $id_prop, $relateds) = static::get_related_model_info4type($type);

		return $model::get_col_array($id_prop, array('where' => array('message_id' => $message_id)));
	}

	public static function get_send_target_member_ids($message_id, $type, $related_id, $exclude_member_id = 0)
	{
		if (Site_Util::check_type($type, array('member', 'group')) && !$related_id)
		{
			$related_ids = static::get_related_ids4message_id($message_id, $type);
			$related_id = array_shift($related_ids);
		}

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
			case 'site_info':
				$member_ids = \Message\Model_MessageSentAdmin::get_member_ids4message_id($message_id);
				break;
			case 'site_info_all':
				$member_ids = \Model_Member::get_col_array('id');
				break;
		}
		if ($exclude_member_id) $member_ids = \Util_Array::unset_item($exclude_member_id, $member_ids);

		return $member_ids;
	}

	public static function save_recieved_model($member_id_from, $message_id, $type, $related_id, $sent_at = null)
	{
		if (!$target_member_ids = static::get_send_target_member_ids($message_id, $type, $related_id, $member_id_from))
		{
			throw new \InvalidArgumentException('No send target member.');
		}

		foreach ($target_member_ids as $member_id)
		{
			// save message_recieved
			$message_recieved = Model_MessageRecieved::save_at_sent($member_id, $message_id, $sent_at);
			// save message_recieved_summary
			$type_related_id = self::get_type_related_id_for_message_recieved_summary($message_id, $type, $related_id);
			$message_recieved_summary = Model_MessageRecievedSummary::save_at_sent($member_id, $message_id, $type, $type_related_id, $sent_at);
		}
	}

	public static function get_type_related_id_for_message_recieved_summary($message_id, $type, $related_id)
	{
		if (Site_Util::check_admin_type($type)) return $message_id;

		return $related_id;
	}
}
