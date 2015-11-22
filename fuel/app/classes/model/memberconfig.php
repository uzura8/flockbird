	<?php
class Model_MemberConfig extends \MyOrm\Model
{
	protected static $_table_name = 'member_config';
	protected static $_belongs_to = array(
		'member' => array(
			'key_from' => 'member_id',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
		),
	);
	protected static $_properties = array(
		'id',
		'member_id' => array(
			'data_type' => 'varchar',
			'validation' => array('required'),
			'form' => array('type' => false),
		),
		'name' => array(
			'data_type' => 'varchar',
			'validation' => array('trim', 'required', 'max_length' => array(64)),
			'form' => array('type' => false),
		),
		'value' => array(
			'data_type' => 'varchar',
			'validation' => array(),
			'form' => array('type' => false),
		),
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

	public static function _init()
	{
		static::$_properties['member_id'] = Util_Orm::get_relational_numeric_key_prop();
	}

	public static function get_one4member_id_and_name($member_id, $name)
	{
		return self::query()->where('member_id', $member_id)->where('name', $name)->get_one();
	}

	public static function get4member_id($member_id, array $names = null)
	{
		$query = self::query()->where('member_id', $member_id);
		if ($names) $query->where('name', 'in', $names);

		return $query->get();
	}

	public static function get4name_and_member_ids($name, array $member_ids)
	{
		if (!$member_ids) return array();

		return self::query()
			->where('name', $name)
			->where('member_id', 'in', $member_ids)
			->get();
	}

	public static function get_value($member_id, $name, $is_return_default_value = false)
	{
		if (!$obj = self::get_one4member_id_and_name($member_id, $name))
		{
			return  $is_return_default_value ? \Form_MemberConfig::get_default_value($name) : null;
		}

		return $obj->value;
	}

	public static function set_value($member_id, $name, $value)
	{
		if ($obj = self::get_one4member_id_and_name($member_id, $name))
		{
			if ($value === $obj->value) return $obj;

			if (is_null($value))
			{
				$obj->delete();
				return null;
			}

			$obj->value = $value;
			$obj->save();

			return $obj;
		}

		if (is_null($value)) return null;

		$obj = self::forge(array(
			'member_id' => $member_id,
			'name' => $name,
			'value' => $value,
		));
		$obj->save();

		return $obj;
	}

	public static function delete_value($member_id, $name)
	{
		return self::set_value($member_id, $name, null);
	}
}
