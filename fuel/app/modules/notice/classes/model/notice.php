<?php
namespace Notice;

class Model_Notice extends \MyOrm\Model
{
	protected static $_table_name = 'notice';

	protected static $_properties = array(
		'id',
		'foreign_table' => array(
			'data_type' => 'varchar',
			'validation' => array('trim', 'required', 'max_length' => array(20)),
			'form' => array('type' => false),
		),
		'foreign_id',
		'type' => array(
			'data_type' => 'integer',
			'validation' => array('required', 'valid_string' => array('numeric'), 'max_length' => array(2)),
			'form' => array('type' => false),
		),
		'body' => array(
			'data_type' => 'text',
			'validation' => array('trim'),
			'form' => array('type' => false),
		),
		'parent_table' => array(
			'data_type' => 'varchar',
			'validation' => array('trim', 'max_length' => array(20)),
			'form' => array('type' => false),
		),
		'parent_id',
		'created_at' => array('form' => array('type' => false)),
		'updated_at' => array('form' => array('type' => false)),
	);

	protected static $_observers = array(
		'Orm\Observer_Validation' => array(
			'events' => array('before_save'),
		),
		'Orm\Observer_CreatedAt' => array(
			'events' => array('before_insert'),
			'mysql_timestamp' => true,
		),
		'Orm\Observer_UpdatedAt' => array(
			'events' => array('before_save'),
			'mysql_timestamp' => true,
		),
	);

	protected static $_to_array_exclude = array(
		'created_at', 'updated_at'
	);

	public static function _init()
	{
		static::$_properties['foreign_table']['validation']['in_array'][] = Site_Util::get_accept_foreign_tables();
		static::$_properties['foreign_id'] = \Util_Orm::get_relational_numeric_key_prop();
		static::$_properties['type']['validation']['in_array'][] = \Config::get('notice.types');
		static::$_properties['parent_table']['validation']['in_array'][] = Site_Util::get_accept_parent_tables();
		static::$_properties['parent_id'] = \Util_Orm::get_relational_numeric_key_prop(false);

		// unread cache を削除
		if (\Site_Notification::check_is_enabled_cahce('notice'))
		{
			static::$_observers['MyOrm\Observer_DeleteUnreadNoticeCountCache'] = array(
				'events' => array('before_delete'),
			);
		}
	}

	public static function check_and_create($foreign_table, $foreign_id, $type)
	{
		$since_datetime = \Date::forge(strtotime('-'.\Config::get('notice.periode_to_update.default')))->format('mysql');
		if (!$obj = self::get_last4foreign_data($foreign_table, $foreign_id, $type, $since_datetime))
		{
			$obj = self::forge(array(
				'foreign_table' => $foreign_table,
				'foreign_id' => $foreign_id,
				'type' => $type,
				'body' => Site_Util::get_notice_body($foreign_table, $type),
			));

			list($parent_table, $parent_id_prop) = self::get_parent_info_for_save($foreign_table);
			if ($parent_table && $parent_id_prop)
			{
				$obj->parent_table = $parent_table;
				$foreign_obj_name = \Site_Model::get_model_name($foreign_table);
				$foreign_obj = $foreign_obj_name::find($foreign_id);
				$obj->parent_id = $foreign_obj->{$parent_id_prop};
			}
			$obj->save();
		}

		return $obj;
	}

	protected static function get_parent_info_for_save($foreign_table)
	{
		if (in_array($foreign_table, Site_Util::get_accept_parent_tables())) return array(null, null);
		if ($foreign_table == 'member_relation') return array('member', 'member_id_from');
		if ($parent_table = \Site_Model::get_parent_table($foreign_table)) return array($parent_table, $parent_table.'_id');

		return array(null, null);
	}

	public static function get_last4foreign_data($foreign_table, $foreign_id, $type, $since_datetime = null)
	{
		$query = self::query()
			->where('foreign_table', $foreign_table)
			->where('foreign_id', $foreign_id)
			->where('type', $type);

		if ($since_datetime) $query = $query->where('created_at', '>', $since_datetime);

		return $query->order_by('created_at', 'desc')
			->rows_limit(1)
			->get_one();
	}

	public static function get4foreign_data($foreign_table, $foreign_id, $types = null)
	{
		if ($types && !is_array($types)) $types = (array)$types;

		$query = self::query()
			->where('foreign_table', $foreign_table)
			->where('foreign_id', $foreign_id);

		if ($types)
		{
			if (count($types) == 1)
			{
				$query->where('type', $types[0]);
			}
			else
			{
				$query->where('type', 'in', $types);
			}
		}

		return $query->get();
	}

	public static function get4parent_data($parent_table, $parent_id)
	{
		$query = self::query()
			->where('parent_table', $parent_table)
			->where('parent_id', $parent_id);

		return $query->get();
	}
}
