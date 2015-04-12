<?php
namespace News;

class Model_News extends \MyOrm\Model
{
	protected static $_table_name = 'news';

	protected static $_belongs_to = array(
		'users' => array(
			'key_from' => 'users_id',
			'model_to' => '\Admin\Model_AdminUser',
			'key_to' => 'id',
			'cascade_save' => false,
			'cascade_delete' => false,
		),
		'news_category' => array(
			'key_from' => 'news_category_id',
			'model_to' => '\News\Model_NewsCategory',
			'key_to' => 'id',
			'cascade_save' => false,
			'cascade_delete' => false,
		)
	);
	protected static $_has_many = array(
		'news_image' => array(
			'key_from' => 'id',
			'model_to' => '\News\Model_NewsImage',
			'key_to' => 'news_id',
		),
		'news_link' => array(
			'key_from' => 'id',
			'model_to' => '\News\Model_NewsLink',
			'key_to' => 'news_id',
		)
	);

	protected static $_properties = array(
		'id',
		'news_category_id' => array(
			'data_type' => 'integer',
			'label' => 'ニュースカテゴリ',
			'validation' => array('valid_string' => array('numeric')),
			'form' => array('type' => 'select'),
		),
		'slug' => array(
			'data_type' => 'varchar',
			'label' => '記事識別名',
			'validation' => array(
				'trim', 'required',
				'max_length' => array(32),
				'match_pattern' => array('/^[a-z0-9_-]*[a-z0-9]+[a-z0-9_-]*$/i'),
				'unique' => array('content_page.slug')
			),
			'form' => array('type' => 'text'),
		),
		'title' => array(
			'data_type' => 'varchar',
			'label' => 'タイトル',
			'validation' => array('trim', 'required', 'max_length' => array(255)),
			'form' => array('type' => 'text'),
		),
		'body' => array(
			'data_type' => 'text',
			'label' => '本文',
			'validation' => array('trim'),
			'form' => array('type' => 'textarea', 'rows' => 16),
		),
		'format' => array(
			'data_type' => 'integer',
			'label' => '形式',
			'default' => 0,
			'validation' => array('required', 'valid_string' => array('numeric'), 'max_length' => array(1)),
			'form' => array('type' => 'select'),
		),
		'is_published' => array(
			'data_type' => 'integer',
			'validation' => array('in_array' => array(array(0,1))),
			'form' => array('type' => false),
		),
		'published_at' => array(
			'data_type' => 'datetime',
			'label' => '公開日時',
			'validation' => array('valid_date' => array('Y-m-d H:i:s')),
			'form' => array('type' => 'text'),
		),
		'users_id' => array(
			'data_type' => 'integer',
			'form' => array('type' => false),
		),
		'token' => array(
			'data_type' => 'varchar',
			'form' => array('type' => false),
			'validation' => array('trim', 'max_length' => array(255)),
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
		// delete 時に紐づくデータを削除する
		'MyOrm\Observer_DeleteRelationalTables' => array(
			'events' => array('before_delete'),
			'relations' => array(
				array(
					'model_to' => '\News\Model_NewsImage',
					'conditions' => array(
						'news_id' => array('id' => 'property'),
					),
				),
				array(
					'model_to' => '\News\Model_NewsFile',
					'conditions' => array(
						'news_id' => array('id' => 'property'),
					),
				),
			),
		),
	);

	public static function _init()
	{
		$format_options = conf('form.formats.options', 'news');
		static::$_properties['format']['form']['options'] = $format_options;
		static::$_properties['format']['validation']['in_array'][] = array_keys($format_options);

		if (\Config::get('news.category.isEnabled'))
		{
			static::$_properties['news_category_id']['label'] = term('news.category.simple');
			$news_category_id_options = \Util_Orm::conv_cols2assoc(Model_NewsCategory::get_all(), 'id', 'label');
			static::$_properties['news_category_id']['form']['options'] = $news_category_id_options;
			static::$_properties['news_category_id']['validation']['in_array'][] = array_keys($news_category_id_options);
		}
		else
		{
			static::$_properties['news_category_id']['form']['type'] = false;
		}
		//if (!Site_Util::check_editor_enabled('html_editor') || !(conf('image.isEnabled', 'news') && conf('image.isInsertBody', 'news')))
		//{
		//	static::$_properties['body']['validation'][] = 'required';
		//}
	}

	public static function check_exists4slug($slug)
	{
		return (bool)self::get4slug($slug);
	}

	public static function create_instantly($user_id)
	{
		$obj = self::forge();
		$obj->slug         = Site_Util::get_slug();
		$obj->title        = date(conf('default.dateFormat')).'の'.term('news.view');
		$obj->users_id     = $user_id;
		$obj->token        = \Security::generate_token();
		$obj->is_published = 0;
		$obj->format = conf('form.formats.default', 'news');
		$obj->save();

		return $obj;
	}
}
