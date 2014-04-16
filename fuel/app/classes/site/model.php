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

	private static function add_where($query, $wheres, $key = null)
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

	public static function get_where_params4list($target_member_id = 0, $self_member_id = 0, $is_myapge = false, $where = array(), $member_id_colmn = null)
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

		return Util_Orm::conv_col2array($query->get(), $column);
	}

	public static function get_pager_list($table, $last_id = 0, $params = array(), $namespace = '', $is_check_next = false, $is_over = false, $primary_key = 'id')
	{
		if ($last_id)
		{
			$inequality_sign = '>';
			if (empty($params['order_by'][0]) || $params['order_by'][0] != 'desc')
			{
				$inequality_sign = '<';
			}
			if ($is_over) $inequality_sign = '>';

			if (!isset($params['where'])) $params['where'] = array();
			$params['where'][] = array($primary_key, $inequality_sign, $last_id);
		}
		$query = self::get_list_query($table, $params, $namespace);

		if (empty($params['limit']))
		{
			$is_check_next = false;
		}
		else
		{
			$limit = $params['limit'];
			if ($is_check_next) $limit += 1;
			$query = $query->rows_limit($limit);
		}
		$list = $query->get();

		$is_next = false;
		if ($is_check_next)
		{
			$is_next = count($list) > $params['limit'];
			if ($is_next) array_pop($list);
		}

		return array($list, $is_next);
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

	public static function get_count($table, $params = array(), $namespace = '')
	{
		$query = self::get_list_query($table, $params, $namespace);

		return $query->count();
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
}
