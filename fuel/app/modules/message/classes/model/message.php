<?php
namespace Message;

class Model_Message extends \MyOrm\Model
{
	protected static $_table_name = 'message';

	protected static $_belongs_to = array(
		'member' => array(
			'key_from' => 'member_id',
			'model_to' => 'Model_Member',
			'key_to' => 'id',
			'cascade_save' => false,
			'cascade_delete' => false,
		)
	);

	protected static $_properties = array(
		'id',
		'member_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'subject' => array(
			'data_type' => 'varchar',
			'label' => '件名',
			'validation' => array('trim', 'max_length' => array(255)),
			'form' => array('type' => 'text'),
		),
		'body' => array(
			'data_type' => 'text',
			'label' => '本文',
			'validation' => array('trim'),
			'form' => array('type' => 'textarea', 'rows' => 10),
		),
		'type' => array(
			'data_type' => 'integer',
			'validation' => array('max_length' => array(2)),
			'form' => array('type' => false),
		),
		'foreign_table' => array(
			'data_type' => 'varchar',
			'validation' => array('trim', 'max_length' => array(20)),
			'form' => array('type' => false),
		),
		'foreign_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'is_sent' => array(
			'data_type' => 'integer',
			'default' => 0,
			'validation' => array('max_length' => array(1), 'in_array' => array(array(0, 1))),
			'form' => array('type' => false),
		),
		'is_deleted' => array(
			'data_type' => 'integer',
			'default' => 0,
			'validation' => array('max_length' => array(1), 'in_array' => array(array(0, 1))),
			'form' => array('type' => false),
		),
		'created_at' => array('form' => array('type' => false)),
		'updated_at' => array('form' => array('type' => false)),
		'sent_at' => array('form' => array('type' => false)),
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
		'MyOrm\Observer_SortDatetime' => array(
			'events' => array('before_save'),
			'mysql_timestamp' => true,
			'property' => 'sent_at',
			'check_changed' => array(
				'check_properties' => array(
					'is_sent' => array(
						'value' => 1,
					),
				),
			),
		),
	);

	protected static $_to_array_exclude = array('foreign_table', 'foreign_id');

	public static function _init()
	{
		static::$_properties['type']['validation']['in_array'][] = Site_Util::get_types(true);
		//static::$_properties['foreign_table']['validation']['in_array'][] = Site_Util::get_message_foreign_tables();
	}

	public function send_message($member_id, $type, $related_id, $body, $is_draft = false)
	{
		if (!$target_member_ids = Site_Model::get_member_ids_joined_related_model($type, $related_id, $member_id))
		{
			throw new InvalidArgumentException('Third parameter is invalid.');
		}

		// save message
		$this->member_id = $member_id;
		$this->body = $body;
		$this->type = Site_Util::get_type4key($type);
		$this->is_sent = $is_draft ? 0 : 1;
		$this->save();

		$related_model_obj = Site_Model::save_related_model($this->id, $type, $related_id, $this->sent_at);

		foreach ($target_member_ids as $member_id)
		{
			// save message_recieved
			$message_recieved = Model_MessageRecieved::save_at_sent($member_id, $this->id, $this->sent_at);
			// save message_recieved_summary
			$message_recieved_summary = Model_MessageRecievedSummary::save_at_sent($member_id, $this->id, $type, $related_id, $this->sent_at);
		}
	}
}
