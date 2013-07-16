<?php
class Site_Model
{
	public static function get_simple_pager_list($table, $page = 1, $params = array(), $namespace = '')
	{
		$page = (int)$page;
		if ($page < 1) $page = 1;

		$limit  = empty($params['limit']) ? \Config::get($table.'.articles.limit', 5) : $params['limit'];
		$offset = $limit * ($page - 1);

		$model = 'Model_'.Util_string::camelize($table);
		if ($namespace) $model = sprintf('\%s\%s', $namespace, $model);
		$query = $model::query();

		if (!empty($params['related']))
		{
			$query = $query->related($params['related']);
		}

		if (!empty($params['where']))
		{
			if (Arr::is_multi($params['where']))
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
		if (!empty($params['order_by']))
		{
			foreach ($params['order_by'] as $key => $value)
			{
				$query = $query->order_by($key, $value);
			}
		}

		$count = $query->count();
		$list = $query->rows_offset($offset)->rows_limit($limit)->get();

		$is_next = ($count > $offset + $limit) ? true : false;

		return array('list' => $list, 'page' => $page, 'is_next' => $is_next);
	}
}
