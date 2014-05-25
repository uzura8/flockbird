<?php
namespace News;

class Model_NewsLink extends \MyOrm\Model
{
	protected static $_table_name = 'news_link';

	protected static $_belongs_to = array(
		'news' => array(
			'key_from' => 'news_id',
			'model_to' => '\News\Model_News',
			'key_to' => 'id',
			'cascade_save' => true,
			'cascade_delete' => false,
		),
	);

	protected static $_properties = array(
		'id',
		'news_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'uri' => array(
			'data_type' => 'varchar',
			'label' => 'URL',
			'validation' => array('trim', 'valid_url'),
			'form' => array('type' => 'url', 'class' => 'form-control'),
		),
		'label' => array(
			'data_type' => 'varchar',
			'label' => '表示名',
			'validation' => array('trim', 'max_length' => array(255)),
			'form' => array('type' => 'text', 'class' => 'form-control'),
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

	public static function get4news_id($news_id)
	{
		return self::query()->where('news_id', $news_id)->get();
	}
}
