<?php
class Site_Model
{
	public static function get_list_query($table, $params = array(), $namespace = '')
	{
		$model = 'Model_'.Util_string::camelize($table);
		if ($namespace) $model = sprintf('\%s\%s', $namespace, $model);
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
				foreach ($params['where'] as $where)
				{
					if (count($where) == 2)
					{
						$query = $query->where($where[0], $where[1]);
					}
					elseif (count($where) === 3)
					{
						$query = $query->where($where[0], $where[1], $where[2]);
					}
				}
			}
			else
			{
				$where = $params['where'];
				if (count($where) == 2)
				{
					$query = $query->where($where[0], $where[1]);
				}
				elseif (count($where) === 3)
				{
					$query = $query->where($where[0], $where[1], $where[2]);
				}
			}
		}
		// order by
		if (!empty($params['order_by']))
		{
			foreach ($params['order_by'] as $key => $value)
			{
				$query = $query->order_by($key, $value);
			}
		}

		return $query;
	}

	public static function get_where_params4list($target_member_id = 0, $self_member_id = 0, $is_myapge = false, $member_id_colmn = null, $where = array())
	{
		if ($target_member_id) $where[] = array($member_id_colmn ?: 'member_id', $target_member_id);

		if ($self_member_id)
		{
			if (($target_member_id && $target_member_id != $self_member_id) || !$is_myapge)
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

	public static function get_list($table, $params = array(), $namespace = '')
	{
		$query = self::get_list_query($table, $params, $namespace);

		return $query->get();
	}

	public static function get_col_array($table, $column, $params = array(), $namespace = '')
	{
		$params['select'] = $column;
		$query = self::get_list_query($table, $params, $namespace);

		return Util_Orm::conv_col2array($column, $query->get());
	}

	public static function get_simple_pager_list($table, $page = 1, $params = array(), $namespace = '')
	{
		$query = self::get_list_query($table, $params, $namespace);
		$count = $query->count();

		// limit, offset
		$page = (int)$page;
		if ($page < 1) $page = 1;

		$limit  = 0;
		$offset = 0;
		if (!empty($params['limit']))
		{
			$limit  = $params['limit'];
			$offset = $limit * ($page - 1);

			$query = $query->rows_limit($limit);
			$query = $query->rows_offset($offset);
		}
		$is_next = ($limit && $count > $offset + $limit) ? true : false;

		$list = $query->get();

		return array('list' => $list, 'page' => $page, 'is_next' => $is_next);
	}
}
