<?php

class Model_MemberRelationUnit extends \MyOrm\Model
{
	protected static $_table_name = 'member_relation_unit';
	protected static $_has_one = array(
		'member_lower' => array(
			'key_from' => 'member_id_lower',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
			'cascade_save' => false,
			'cascade_delete' => false,
		),
		'member_upper' => array(
			'key_from' => 'member_id_upper',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
			'cascade_save' => false,
			'cascade_delete' => false,
		),
	);

	protected static $_properties = array(
		'id',
		'member_id_lower' => array(
			'validation' => array(
				'numelic_less_than_field' => array('member_id_upper'),
			),
			'form' => array('type' => false),
		),
		'member_id_upper' => array(
			'validation' => array(
				'numelic_more_than_field' => array('member_id_lower'),
			),
			'form' => array('type' => false),
		),
	);

	protected static $_observers = array();
	protected static $_to_array_exclude = array();

	public static function get_id4member_ids($member_ids)
	{
		list($member_id_lower, $member_id_upper) = \Util_Array::sort_pairs_num($member_ids, true);
		$props = array('member_id_lower' => $member_id_lower, 'member_id_upper' => $member_id_upper);
		if (!$obj = self::get_one4conditions($props))
		{
			$obj = self::forge($props);
			$obj->save();
		}

		return $obj->id;
	}
}
