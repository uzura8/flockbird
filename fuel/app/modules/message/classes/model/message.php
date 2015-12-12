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
		static::$_properties['subject']['label'] = term('message.form.subject');
		static::$_properties['body']['label'] = term('message.form.body');
		static::$_properties['type']['validation']['in_array'][] = Site_Util::get_types(true);
		//static::$_properties['foreign_table']['validation']['in_array'][] = Site_Util::get_message_foreign_tables();
	}


	public function save_with_relations($member_id_from, $type, $related_ids, $body, $subject = '', $is_draft = false, $related_optional_props = array())
	{
		// save message
		$this->member_id = $member_id_from;
		$this->body = $body;
		if ($subject) $this->subject = $subject;
		$this->type = Site_Util::get_type4key($type);
		$this->is_sent = $is_draft ? 0 : 1;
		$this->save();
		Site_Model::save_send_target($member_id_from, $this->id, $type, $related_ids, $related_optional_props);

		if ($is_draft) return;

		Site_Model::save_recieved_model($member_id_from, $this->id, $type, $related_ids, $this->sent_at);
	}

	public function update_messaage($is_send = false, $related_ids = null, $values = array())
	{
		if ($this->is_sent) throw new \FuelException('Message is already sent.');

		if (!$target_member_ids = Site_Model::get_send_target_member_ids($this->id, $this->type, $related_ids, $this->member_id))
		{
			throw new \InvalidArgumentException('No send target member.');
		}

		$accept_update_props = array('subject', 'body');
		foreach ($values as $key => $value)
		{
			if (!in_array($key, $accept_update_props)) continue;
			$this->{$key} = $value;
		}
		if ($is_send) $this->is_sent = 1;
		$this->save();

		if ($is_send) Site_Model::save_recieved_model($this->member_id, $this->id, $this->type, $related_ids, $this->sent_at);
	}
}
