<?php
namespace MyOrm;

class Model extends \Orm\Model
{
	protected static $_write_connection;
	protected static $_write_connection_default;
	protected static $_connection;
	protected static $_connection_default;

	protected static $image_prefix;
	protected static $basic_list_cache = array();
	protected static $basic_props = array();

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

	public static function clear_cache()
	{
		static::$_cached_objects[get_called_class()] = [];
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

	public function update_public_flag($public_flag)
	{
		$this->public_flag = $public_flag;
		return $this->save();
	}

	public function get_image_prefix()
	{
		return static::$image_prefix;
	}

	public static function check_authority($id, $target_member_id = 0, $related_tables = array(), $member_id_prop = 'member_id')
	{
		$id = (int)$id;
		if (!$id) throw new \HttpNotFoundException;

		$params = array('rows_limit' => 1);
		if ($related_tables && !is_array($related_tables)) $related_tables = (array)$related_tables;
		if ($related_tables) $params['related'] = $related_tables;
		if (!$obj = self::find($id, $params)) throw new \HttpNotFoundException;
		if ($target_member_id && $obj->{$member_id_prop} != $target_member_id) throw new \HttpForbiddenException;

		return $obj;
	}

	public static function get_one4pk($pk_value, $relateds = array(), $pk_name = 'id')
	{
		$query = self::query()->where($pk_name, $pk_value);
		if ($relateds) $query->related($relateds);

		return $query->get_one();
	}

	public static function get_one4unique_key($unique_key_value, $relateds = array(), $unique_key = 'id')
	{
		$query = self::query()->where($unique_key, $unique_key_value);
		if ($relateds) $query->related($relateds);

		return $query->get_one();
	}

	public static function get_one4id($id, $relateds = array())
	{
		return self::get_one4unique_key(intval($id), $relateds);
	}

	public static function check_authority4unique_key($unique_key_prop, $value, $target_member_id = 0, $relateds = array(), $member_id_prop = 'member_id')
	{
		if (!$value) throw new \HttpNotFoundException;
		if (!$obj = self::get_one4unique_key($value, $relateds, $unique_key_prop)) throw new \HttpNotFoundException;
		if ($target_member_id && $obj->{$member_id_prop} != $target_member_id) throw new \HttpForbiddenException;

		return $obj;
	}

	public static function get_one4conditions(array $conditions)
	{
		return self::query()
			->where($conditions)
			->get_one();
	}

	public static function get_list($where_conds = array(), $limit = 0, $is_latest = false, $is_desc = false, $since_id = 0, $max_id = 0, $relateds = array(), $is_return_array = false, $is_return_all_count = false, $select_props = array(), $sort_prop = 'id')
	{
		$is_reverse = false;
		if ($limit && $is_latest && !$is_desc)
		{
			$is_desc = true;
			$is_reverse = true;
		}

		if ($where_conds && !is_array($where_conds)) $where_conds = (array)$where_conds;
		$query = static::get_list_query($where_conds ? array('where' => $where_conds) : array());
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

	public static function get_all($order_by = null, $relateds = array(), $limit = 0, $params = array(), $selects = array(), $return_with_all_count = false)
	{
		$all_count = 0;
		if (!is_array($relateds)) $relateds = (array)$relateds;

		if (empty($order_by)) $order_by = array('id' => 'asc');
		$query = self::query()->order_by($order_by);
		if ($selects) $query->select($selects);
		if ($relateds) $query->related($relateds);
		if ($params) $query = static::set_where($query, $params);
		if ($return_with_all_count) $all_count = $query->count();
		if ($limit) $query->rows_limit($limit);

		if ($return_with_all_count) return array($query->get(), $all_count);

		return $query->get();
	}

	public static function get4slug($slug)
	{
		return self::query()->where('slug', $slug)->get_one();
	}

	public static function change_registered_status4unique_key(array $params)
	{
		if ($obj = self::get_one4conditions($params))
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

	public static function get_cols($col, $params = array(), $order_by = array())
	{
		$query = self::query()->select($col);
		if ($params) $query = static::set_where($query, $params);
		if ($order_by) $query->order_by($order_by);

		return \Util_Orm::conv_col2array($query->get(), $col);
	}

	public static function get_assoc($key_col, $value_col, $params = array(), $order_by = array(), $limit = 0)
	{
		$query = self::query()->select($key_col, $value_col);
		if ($params) $query = static::set_where($query, $params);
		if ($order_by) $query->order_by($order_by);
		if ($limit) $query->rows_limit($limit);

		return \Util_Orm::conv_cols2assoc($query->get(), $key_col, $value_col);
	}

	public static function get_pager_list($params = array(), $page = 1, $is_return_array = false, \Orm\Query $query = null)
	{
		if ($is_return_array && isset($params['related'])) unset($params['related']);
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
		if ($is_return_array)
		{
			foreach ($list as $key => $obj)
			{
				$list[$key] = $obj->to_array();
			}
		}

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
			if (!is_array($params['select'])) $selects = (array)$params['select'];
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
			$query = self::set_where($query, $params['where']);
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

	public static function get_one_basic4id($id, $props = array())
	{
		if (!empty(static::$basic_list_cache[$id])) return static::$basic_list_cache[$id];

		if (!is_array($props)) $props = (array)$props;
		if (!$props) $props = static::$basic_props;
		if (!$props) throw new \FuelException('basic_props not set.');

		static::$basic_list_cache[$id] = array();
		if ($obj = static::get_one4id($id))
		{
			static::$basic_list_cache[$id] = array();
			foreach ($props as $prop)
			{
				static::$basic_list_cache[$id][$prop] = $prop == 'file' ? $obj->get_image() : $obj->{$prop};
			}
		}

		return static::$basic_list_cache[$id];
	}

	public static function get_basic4ids($ids, $basic_props = array(), $is_clear_cache = false)
	{
		if ($is_clear_cache) static::clear_cache();

		$objs = array();
		foreach ($ids as $id)
		{
			$objs[$id] = static::get_one_basic4id($id, $basic_props);
		}

		return $objs;
	}

	public static function get_property($column_name, $delete_validation_rules = array())
	{
		$property = self::property($column_name);
		if ($delete_validation_rules && !empty($property['validation']))
		{
			$property['validation'] = \Util_Array::delete_in_array($property['validation'], $delete_validation_rules);
		}

		return $property;
	}

	public static function get_property_value($column_name, $key, $default = null)
	{
		$property = static::get_property($column_name);

		return \Arr::get($property, $key, $default);
	}

	public function set_values(array $values, $ignore_props_additional = array())
	{
		if (!$values) return;

		$ignore_props = array_unique(static::get_ignore_props2edit() + $ignore_props_additional);
		foreach ($values as $key => $value)
		{
			if (!isset($this->{$key})) continue;
			if (in_array($key, $ignore_props)) continue;

			$this->{$key} = $value;
		}
	}

	protected static function get_ignore_props2edit()
	{
		$ignore_props = array();
		foreach (static::$_properties as $prop => $attrs)
		{
			if (!is_array($attrs))
			{
				$ignore_props[] = $attrs;
				continue;
			}

			if (isset($attrs['form']['type']) && $attrs['form']['type'] !== false)
			{
				continue;
			}

			$ignore_props[] = $prop;
		}

		return $ignore_props;
	}

	protected static function set_where(\Orm\Query $query, $params = array())
	{
		if (!$params) return $query;

		if (!is_array($params)) $params = (array)$params;
		if (!\Arr::is_multi($params))
		{
			return static::set_where4not_multi($query, $params);
		}

		if (count($params) == 3 && !is_array($params[0]) && !is_array($params[1]) && in_array(strtolower($params[1]), array('in', '<', '>', '<=', '>=')))
		{
			return static::set_where4not_multi($query, $params);
		}

		$method = 'where';
		foreach ($params as $key => $param)
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
				$query = static::set_where4not_multi($query, $param);
			}
		}

		return $query;
	}

	protected static function set_where4not_multi(\Orm\Query $query, $params = array())
	{
		if (!$params) return $query;

		if (\Arr::is_assoc($params))
		{
			$query->where($params);

			return $query;
		}

		if (count($params) == 2)
		{
			$query->where($params[0], $params[1]);
		}
		elseif (count($params) == 3)
		{
			$query->where($params[0], $params[1], $params[2]);
		}
		else
		{
			throw new \InvalidArgumentException('Second parameter is invalid.');
		}

		return $query;
	}
}
