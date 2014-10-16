<?php
class Site_Model
{
	public static function get_model_name($table, $namespace = '')
	{
		$model = '\Model_'.Inflector::camelize($table);
		if ($namespace) $model = sprintf('\%s%s', ucfirst($namespace), $model);

		return $model;
	}

	public static function get4ids($table, $values, $field = 'id', $namespace = null)
	{
		$model = self::get_model_name($table, $namespace);

		return $model::query()->where($field, 'in', $values)->get();
	}

	public static function get_list_query($table, $params = array(), $namespace = '')
	{
		$model = self::get_model_name($table, $namespace);
		$query = $model::query();

		// select
		if (!empty($params['select']))
		{
			$selects = is_array($params['select']) ? $params['select'] : (array)$params['select'];
			foreach ($selects as $select)
			{
				$query = $query->select($select);
			}
		}

		// related
		if (!empty($params['related']))
		{
			$query = $query->related($params['related']);
		}

		// where
		if (!empty($params['where']))
		{
			if (Arr::is_multi($params['where'], true))
			{
				foreach ($params['where'] as $key => $where)
				{
					if ($key === 'and' || $key === 'or')
					{
						$method_open  = $key.'_where_open';
						$method_close = $key.'_where_close';
						$query = $query->$method_open();
						foreach ($where as $key_child => $where_child)
						{
							$query = self::add_where($query, $where_child, $key_child);
						}
						$query = $query->$method_close();
					}
					else
					{
						$query = self::add_where($query, $where);
					}
				}
			}
			else
			{
				$where = $params['where'];
				$query = self::add_where($query, $where);
			}
		}
		// order by
		if (!empty($params['order_by']))
		{
			foreach ($params['order_by'] as $key => $value)
			{
				if (is_numeric($key) && !in_array($value, array('asc', 'desc')))
				{
					$key   = $value;
					$value = 'asc';
				}
				$query = $query->order_by($key, $value);
			}
		}

		return $query;
	}

	public static function add_where(\Orm\Query $query, $wheres, $key = null)
	{
		$method = 'where';
		if ($key)
		{
			if ($key === 'or')
			{
				$method = 'or_where';
			}
			elseif ($key === 'and')
			{
				$method = 'and_where';
			}
		}
		if (count($wheres) == 2)
		{
			$query = $query->$method($wheres[0], $wheres[1]);
		}
		elseif (count($wheres) === 3)
		{
			$query = $query->$method($wheres[0], $wheres[1], $wheres[2]);
		}

		return $query;
	}

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

	public static function get_list($table, $params = array(), $namespace = '')
	{
		$query = self::get_list_query($table, $params, $namespace);

		return $query->get();
	}

	public static function get_col_array($table, $column, $params = array(), $namespace = '')
	{
		$params['select'] = $column;
		$query = self::get_list_query($table, $params, $namespace);

		return Util_Orm::conv_col2array($query->get(), $column);
	}

	public static function get_list_and_count($table, $params = array(), $namespace = '')
	{
		$query = self::get_list_query($table, $params, $namespace);
		$count = $query->count();

		if (!empty($params['limit']))
		{
			$query = $query->rows_limit($params['limit']);
		}
		if (!empty($params['offset']))
		{
			$query = $query->rows_offset($params['offset']);
		}
		$list = $query->get();

		return array($list, $count);
	}

	public static function get_next_sort_order($table, $namespace = null, $sort_order_col_name = 'sort_order')
	{
		$model = self::get_model_name($table, $namespace);
		$max = (int)$model::query()->max($sort_order_col_name);

		return Site_Util::get_next_sort_order_num($max);
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
			if (empty($value_type['table']) || empty($value_type['property']) || empty($obj->{$value_type['table']}))
			{
				throw new \FuelException('Orm observer setting error.');
			}

			return $obj->{$value_type['table']}->{$value_type['property']};
		}

		throw new \FuelException('Orm observer setting error.');
	}

	public static function get_liked_ids($parent_table, $member_id, array $parent_objs, $namespace = '', $like_model = null, $member_id_prop = 'member_id')
	{
		if (!$parent_objs) return array();

		if (!$like_model) $like_model = self::get_model_name($parent_table.'_like', $namespace);
		$parent_foreign_key = $parent_table.'_id';

		return $like_model::get_cols($parent_foreign_key, array(
			array($member_id_prop => $member_id),
			array($parent_foreign_key, 'in', \Util_Orm::conv_col2array($parent_objs, 'id'))
		));
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
