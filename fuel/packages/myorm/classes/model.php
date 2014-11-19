<?php
namespace MyOrm;

class Model extends \Orm\Model
{
	protected static $_write_connection;
	protected static $_write_connection_default;
	protected static $_connection;
	protected static $_connection_default;

	protected static $image_prefix;

	/**
	 * Fetch the database connection name to use
	 *
	 * @param	bool	if true return the writeable connection (if set)
	 * @return  null|string
	 */
	public static function connection($writeable = false)
	{
		// if in tracsaction, conection force to be writable.
		if (\DB::in_transaction()) $writeable = true;

		if ($writeable)
		{
			if (empty(static::$_write_connection))
			{
				static::$_write_connection = static::get_write_connection_default();
			}

			return static::$_write_connection;
		}

		if (empty(static::$_connection))
		{
			static::$_connection = static::get_read_connection_default();
		}

		return static::$_connection;
	}

	protected static function get_write_connection_default()
	{
		if (static::$_write_connection_default) return static::$_write_connection_default;

		if (!$db_configs = \Config::get('db')) \FuelException('Db setting error.');

		if (!empty($db_configs['active'])) return $db_configs['active'];

		return static::search_write_connection_default();
	}

	protected static function search_write_connection_default()
	{
		$connection_set_first = '';
		foreach ($db_configs as $db => $config)
		{
			if (!static::check_is_db_config($config)) continue;

			// set first set db to master db.
			static::$_write_connection_default = $db;
			break;
		}
		if (empty(static::$_write_connection_default)) \FuelException('Db setting error.');

		return static::$_write_connection_default;
	}

	public static function get_read_connection_default()
	{
		if (static::$_connection_default) return static::$_connection_default;
		static::$_connection_default = static::get_read_connection_random();

		return static::$_connection_default;
	}

	protected static function get_read_connection_random()
	{
		$connection_rates = array();
		$db_configs = \Config::get('db');
		foreach ($db_configs as $db => $config)
		{
			if (!static::check_is_db_config($config)) continue;

			$connection_rates[$db] = 1;
			if (isset($config['connection_rate'])) $connection_rates[$db] = $config['connection_rate'];
		}

		return \Util_Array::rand_weighted($connection_rates);
	}

	protected static function check_is_db_config($config)
	{
		if (empty($config)) return false;
		if (!is_array($config)) return false;
		if (empty($config['type'])) return false;
		if (!in_array($config['type'], array('pdo', 'mysql', 'mysqli'))) return false;

		return true;
	}

	public static function get_table_name()
	{
		return static::$_table_name;
	}

	public function get_image()
	{
		if (!static::$image_prefix) return null;
		if (empty($this->file_name)) return static::$image_prefix;

		return $this->file_name;
	}

	public function get_image_prefix()
	{
		return static::$image_prefix;
	}

	public static function check_authority($id, $target_member_id = 0, $related_tables = array(), $member_id_prop = 'member_id')
	{
		if (!$id) throw new \HttpNotFoundException;

		$params = array('rows_limit' => 1);
		if ($related_tables && !is_array($related_tables)) $related_tables = (array)$related_tables;
		if ($related_tables) $params['related'] = $related_tables;
		if (!$obj = self::find($id, $params)) throw new \HttpNotFoundException;
		if ($target_member_id && $obj->{$member_id_prop} != $target_member_id) throw new \HttpForbiddenException;

		return $obj;
	}

	public static function check_authority4unique_key($unique_key_prop, $value, $target_member_id = 0, $related_tables = array(), $member_id_prop = 'member_id')
	{
		if (!$value) throw new \HttpNotFoundException;

		$query = self::query()->where($unique_key_prop, $value);
		if ($related_tables) $query->related($related_tables);
		if (!$obj = $query->get_one()) throw new \HttpNotFoundException;
		if ($target_member_id && $obj->{$member_id_prop} != $target_member_id) throw new \HttpForbiddenException;

		return $obj;
	}

	public static function get_row4unique_key(array $conditions)
	{
		return self::query()
			->where($conditions)
			->get_one();
	}

	public static function get_list($params = array(), $limit = 0, $is_latest = false, $is_desc = false, $since_id = 0, $max_id = 0, $relateds = array(), $is_return_array = false, $is_return_all_count = false, $select_props = array(), $sort_prop = 'id')
	{
		$is_reverse = false;
		if ($limit && $is_latest && !$is_desc)
		{
			$is_desc = true;
			$is_reverse = true;
		}

		$query = self::query();
		if (!is_array($params)) $params = (array)$params;
		if ($params) $query->where($params);
		$all_records_count = $is_return_all_count ? $query->count() : false;

		$params = array();
		if ($since_id)
		{
			$params[] = array($sort_prop, '>', $since_id);
		}
		if ($max_id)
		{
			$params[] = array($sort_prop, '<=', $max_id);
		}
		if ($params) $query->where($params);

		if ($select_props)
		{
			if (!is_array($select_props)) $select_props = (array)$select_props;
			foreach ($select_props as $select_prop) $query->select($select_prop);
		}

		if ($relateds) $query->related($relateds);

		if ($limit)
		{
			$rows_limit = $limit + 1;
			$query->rows_limit($rows_limit);
		}
		$query->order_by($sort_prop, ($is_desc) ? 'desc' : 'asc');
		$list = $query->get();

		$next_id = 0;
		if ($limit && count($list) > $limit)
		{
			$next_obj = array_pop($list);
			$next_id = $next_obj->id;
		}
		if ($is_reverse) $list = array_reverse($list);

		if ($is_return_array)
		{
			$list_array = array();
			foreach ($list as $key => $obj) $list_array[] = $obj->to_array();
		}

		return array($is_return_array ? $list_array : $list, $next_id, $all_records_count);
	}

	public static function change_registered_status4unique_key(array $params)
	{
		if ($obj = self::get_row4unique_key($params))
		{
			$obj->delete();
		}
		else
		{
			$obj = self::forge($params);
			$obj->save();
		}

		return $obj->id;
	}

	public static function get_cols($col, $params)
	{
		$query = self::query()->select($col);
		if ($params) $query->where($params);

		return \Util_Orm::conv_col2array($query->get(), $col);
	}

	public static function get_pager_list($params = array(), $page = 1, \Orm\Query $query = null)
	{
		if (!$query) $query = self::get_list_query($params);
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
		$next_page = ($limit && $count > $offset + $limit) ? $page + 1 : 0;

		$list = $query->get();

		return array('list' => $list, 'page' => $page, 'next_page' => $next_page);
	}

	public static function get_list_count($params = array(), \Orm\Query $query = null)
	{
		if (!$query) $query = self::get_list_query($params);

		return $query->count();
	}

	public static function get_list_query($params = array())
	{
		$query = self::query();

		// select
		if (!empty($params['select']))
		{
			$selects = is_array($params['select']) ? $params['select'] : (array)$params['select'];
			foreach ($selects as $select)
			{
				$query->select($select);
			}
		}

		// related
		if (!empty($params['related']))
		{
			$query->related($params['related']);
		}

		// where
		if (!empty($params['where']))
		{
			if (\Arr::is_multi($params['where'], true))
			{
				foreach ($params['where'] as $key => $where)
				{
					if ($key === 'and' || $key === 'or')
					{
						$method_open  = $key.'_where_open';
						$method_close = $key.'_where_close';
						$query->$method_open();
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
				$query->order_by($key, $value);
			}
		}

		return $query;
	}

	public static function get_list_and_count($params = array())
	{
		$query = self::get_list_query($params);
		$count = $query->count();

		if (!empty($params['limit']))
		{
			$query->rows_limit($params['limit']);
		}
		if (!empty($params['offset']))
		{
			$query->rows_offset($params['offset']);
		}
		$list = $query->get();

		return array($list, $count);
	}

	public static function get_count($conditions = array())
	{
		$query = self::query();
		if ($conditions) $query = $query->where($conditions);

		return $query->count();
	}

	public static function get4ids($ids, $limit = 0, $sort = array('id' => 'asc'), $relateds = array())
	{
		if (!is_array($ids)) $ids = (array)$ids;
		if (!$ids = \Util_Array::cast_values($ids, 'int', true)) throw new \InvalidArgumentException('First parameter is invalid.');

		$query = self::query()->where('id', 'in', $ids);
		if ($relateds) $query->related($relateds);
		if ($sort)
		{
			foreach ($sort as $column => $order)
			{
				$query->order_by($column, $order);
			}
		}
		if ($limit) $query->rows_limit($limit);

		return $query->get();
	}

	public static function get_next_sort_order($sort_order_col_name = 'sort_order')
	{
		$max = (int)self::query()->max($sort_order_col_name);

		return \Site_Util::get_next_sort_order_num($max);
	}

	public static function get_col_array($column, $params = array())
	{
		$params['select'] = $column;
		$query = self::get_list_query($params);

		return \Util_Orm::conv_col2array($query->get(), $column);
	}

	public static function get_last($conditions = array(), $sort_col = 'id')
	{
		$query = self::query();
		if ($conditions) $query->where($conditions);
		$query->order_by($sort_col, 'desc')->rows_limit(1);

		return $query->get_one();
	}

	private static function add_where(\Orm\Query $query, $wheres, $key = null)
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
}
