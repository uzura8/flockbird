<?php
namespace Notice;

class Model_NoticeStatus extends \MyOrm\Model
{
	protected static $_table_name = 'notice_status';

	protected static $_belongs_to = array(
		'member' => array(
			'key_from' => 'member_id',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
		),
		'notice' => array(
			'key_from' => 'notice_id',
			'model_to' => '\Notice\Model_Notice',
			'key_to' => 'id',
		),
	);

	protected static $_properties = array(
		'id',
		'member_id' => array(
			'data_type' => 'integer',
			'validation' => array('required', 'valid_string' => array('numeric')),
			'form' => array('type' => false),
		),
		'notice_id' => array(
			'data_type' => 'integer',
			'validation' => array('required', 'valid_string' => array('numeric')),
			'form' => array('type' => false),
		),
		'is_read' => array(
			'data_type' => 'integer',
			'validation' => array('max_length' => array(1), 'in_array' => array(array(0,1))),
			'default' => 0,
			'form' => array('type' => false),
		),
		'sort_datetime' => array('form' => array('type' => false)),
	);

	protected static $_observers = array(
		'Orm\Observer_Validation' => array(
			'events' => array('before_save'),
		),
		'Orm\Observer_CreatedAt' => array(
			'events' => array('before_insert'),
			'property' => 'sort_datetime',
			'mysql_timestamp' => true,
		),
		// Add notice_mail_queue record at inserted
		'MyOrm\Observer_InsertRelationialTable' => array(
			'events'   => array('after_insert'),
			'model_to' => '\Notice\Model_NoticeMailQueue',
			'properties' => array(
				'notice_status_id' => 'id',
				'member_id',
			),
		),
		// Delete relaton table record on updated
		'MyOrm\Observer_DeleteRelationalTablesOnUpdated' => array(
			'events' => array('after_update'),
			'relations' => array(
				'model_to' => '\Notice\Model_NoticeMailQueue',
				'conditions' => array(
					'notice_status_id' => array('id' => 'property'),
				),
				'check_changed' => array(
					'check_properties' => array('is_read' => 1),
				),
				'check_changed' => array(
					'check_properties' => array(
						'is_read' => array(
							'value' => 1,
						),
					),
				),
			),
		),
	);

	public static function change_status2unread($member_id, $notice_id)
	{
		if (!$obj = self::get4member_id_and_notice_id($member_id, $notice_id))
		{
			$obj = self::forge(array(
				'member_id' => $member_id,
				'notice_id' => $notice_id,
				'is_read' => 0,
			));
		}
		elseif ($obj->is_read)
		{
			$obj->is_read = 0;
		}

		return (bool)$obj->save();
	}

	public static function change_status2read($member_id, $notice_id)
	{
		if (!$obj = self::get4member_id_and_notice_id($member_id, $notice_id)) return;

		$obj->update_status(1);

		return $obj;
	}

	public function update_status($is_read)
	{
		$this->is_read = $is_read;
		return (bool)$this->save();
	}

	public static function get4member_id_and_notice_id($member_id, $notice_id, $relateds = array())
	{
		$query = self::query()
			->where('member_id', $member_id)
			->where('notice_id', $notice_id);
		if ($relateds) $query->related($relateds);

		return $query->get_one();
	}

	public static function get4member_id($member_id, $is_read = null)
	{
		$query = self::query()->where('member_id', $member_id);
		if (!is_null($is_read)) $query->where('is_read', (bool)$is_read);

		return $query->get();
	}

	public static function get_unread_count4member_id($member_id)
	{
		return self::query()
			->where('member_id', $member_id)
			->where('is_read', 0)
			->count();
	}

	public static function get_pager_list4member_id($member_id, $limit, $page = 1, $is_unread_only = false, $relateds = array('notice'), $sorts = array('sort_datetime' => 'desc'))
	{
		$params = array();
		$params['limit'] = $limit;
		$params['where'] = array(array('member_id', $member_id));
		if ($is_unread_only) $params['where']['is_read'] = 0;
		if ($relateds) $params['related'] = $relateds;
		if ($sorts) $params['order_by'] = $sorts;

		return static::get_pager_list($params, $page);
	}
}
