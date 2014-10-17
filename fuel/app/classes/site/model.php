<?php
class Site_Model
{
	public static function get_where_params4list($target_member_id = 0, $self_member_id = 0, $is_mypage = false, $where = array(), $member_id_colmn = null)
	{
		if ($target_member_id) $where[] = array($member_id_colmn ?: 'member_id', $target_member_id);

		if ($self_member_id)
		{
			if (($target_member_id && $target_member_id != $self_member_id) || !$is_mypage)
			{
				$where[] = array('public_flag', 'IN', array(PRJ_PUBLIC_FLAG_ALL, PRJ_PUBLIC_FLAG_MEMBER));
			}
		}
		else
		{
			$where[] = array('public_flag', PRJ_PUBLIC_FLAG_ALL);
		}

		return $where;
	}

	public static function get_where_public_flag4access_from($access_from, $where = array())
	{
		switch ($access_from)
		{
			case 'others':
				$where[] = array('public_flag', PRJ_PUBLIC_FLAG_ALL);
				break;
			case 'member':
				$where[] = array('public_flag', 'IN', array(PRJ_PUBLIC_FLAG_ALL, PRJ_PUBLIC_FLAG_MEMBER));
				break;
			case 'friend':
				$where[] = array('public_flag', 'IN', array(PRJ_PUBLIC_FLAG_ALL, PRJ_PUBLIC_FLAG_MEMBER, PRJ_PUBLIC_FLAG_FRIEND));
				break;
			case 'self':
			default :
				break;
		}

		return $where;
	}

	public static function update_sort_order($ids, \Orm\Model $model, $sort_order_prop_name = 'sort_order', $id_prop_name = 'id')
	{
		$sort_order = 0;
		$sort_order_interval = conf('sort_order.interval');
		foreach ($ids as $id)
		{
			if (!$obj = $model::query()->where($id_prop_name, $id)->get_one()) continue;

			$obj->{$sort_order_prop_name} = $sort_order;
			$obj->save();
			$sort_order += $sort_order_interval;
		}
		if ($sort_order == 0) throw new \HttpInvalidInputException('Invalid input data.');
	}

	public static function get_liked_ids($parent_table, $member_id, array $parent_objs, $namespace = '', $like_model = null, $member_id_prop = 'member_id')
	{
		if (!$parent_objs) return array();

		if (!$like_model) $like_model = Util_Orm::get_model_name($parent_table.'_like', $namespace);
		$parent_foreign_key = $parent_table.'_id';

		return $like_model::get_cols($parent_foreign_key, array(
			array($member_id_prop => $member_id),
			array($parent_foreign_key, 'in', \Util_Orm::conv_col2array($parent_objs, 'id'))
		));
	}

	public static function get_value_for_observer_setting(\Orm\Model $obj, $value, $value_type)
	{
		if (!is_array($value_type))
		{
			switch ($value_type)
			{
				case 'value':
					return $value;
					break;
				case 'property':
					return $obj->{$value};
					break;
			}
			throw new \FuelException('Orm observer setting error.');
		}

		if ($value == 'related')
		{
			return self::get_related_value($obj, $value_type);
		}

		throw new \FuelException('Orm observer setting error.');
	}

	public static function get_related_value(\Orm\Model $obj, $relateds)
	{
		if (!Arr::is_assoc($relateds)) throw new InvalidArgumentException('Second parameter must be assoc.');

		foreach ($relateds as $table => $values)
		{
			if (!is_array($values))
			{
				return $obj->{$table}->{$values};
			}
			foreach ($values as $related_table => $value_col)
			{
				return $obj->{$table}->{$related_table}->{$value_col};
			}
		}
	}

	public static function get4relation($model_to, array $conditions, \Orm\Model $model_obj_from)
	{
		if (!class_exists($model_to))
		{
			throw new \FuelException('Class not found : '.$model_to);
		}
		$model_to = get_real_class($model_to);
		$query = $model_to::query();
		foreach ($conditions as $property_to => $froms)
		{
			foreach ($froms as $value_from => $type)
			{
				$query->where($property_to, \Site_Model::get_value_for_observer_setting($model_obj_from, $value_from, $type));
			}
		}

		return $query->get();
	}
}
