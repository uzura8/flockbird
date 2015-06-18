<?php
namespace News;

class Model_NewsTag extends \MyOrm\Model
{
	protected static $_table_name = 'news_tag';

	protected static $_belongs_to = array(
		'tag' => array(
			'key_from' => 'tag_id',
			'model_to' => 'Model_Tag',
			'key_to' => 'id',
		),
	);

//	protected static $_belongs_to = array(
//		'news' => array(
//			'key_from' => 'news_id',
//			'model_to' => '\News\Model_News',
//			'key_to' => 'id',
//			'cascade_save' => false,
//			'cascade_delete' => false,
//		),
//	);

	protected static $_properties = array(
		'id',
		'news_id' => array(
			'data_type' => 'integer',
			'validation' => array('required'),
			'form' => array('type' => false),
		),
		'tag_id' => array(
			'data_type' => 'integer',
			'validation' => array('required'),
			'form' => array('type' => false),
		),
		'created_at' => array('form' => array('type' => false)),
	);

	protected static $_observers = array(
		'Orm\Observer_Validation' => array(
			'events' => array('before_save'),
		),
		'Orm\Observer_CreatedAt' => array(
			'events' => array('before_insert'),
			'mysql_timestamp' => true,
		),
	);

	public static function get4news_id($news_id)
	{
		return self::query()
			->related('tag')
			->where('news_id', $news_id)
			->get();
	}

	public static function get_one4news_id_and_tag_id($news_id, $tag_id)
	{
		return self::query()
			->where('news_id', $news_id)
			->where('tag_id', $tag_id)
			->get();
	}

	public static function get_names4news_id($news_id)
	{
		$objs = self::query()
			->related('tag')
			->where('news_id', $news_id)
			->get();

		$returns = array();
		foreach ($objs as $obj)
		{
			$returns[] = $obj->tag->name;
		}

		return $returns;
	}

	public static function get_news_ids4tags($tags)
	{
		if (!is_array($tags)) $tags = (array)$tags;
		if (!$tag_ids = \Model_Tag::get_ids4names($tags)) return array();

		$objs = self::query()
			->where('tag_id', 'in', $tag_ids)
			->get();

		return \Util_Orm::conv_col2array($objs, 'news_id');
	}

	public static function save_tags($tag_names, $news_id)
	{
		$saved_ids = array();
		if (!is_array($tag_names)) $tag_names = (array)$tag_names;
		if (!$tag_names) return $saved_ids;

		$news_tag_ids4delete = self::get_assoc('tag_id', 'id', array('news_id' => $news_id));
		$tag_ids = \Model_Tag::get_assoc('name', 'id', array('name', 'in', $tag_names));
		foreach ($tag_names as $name)
		{
			if (!empty($tag_ids[$name]))
			{
				$tag_id = $tag_ids[$name];
			}
			else
			{
				$tag = \Model_Tag::forge(array('name' => $name));
				$tag->save();
				$tag_id = $tag->id;
			}

			if (!$news_tag = self::get_one4news_id_and_tag_id($news_id, $tag_id))
			{
				$news_tag = self::forge(array('news_id' => $news_id, 'tag_id' =>$tag_id));
				$news_tag->save();
				$saved_ids[] = $news_tag->id;
			}

			if (isset($news_tag_ids4delete[$tag_id])) unset($news_tag_ids4delete[$tag_id]);
		}

		// delete records
		if ($news_tag_ids4delete)
		{
			foreach ($news_tag_ids4delete as $news_tag_id4delete)
			{
				if ($news_tag = self::find($news_tag_id4delete)) $news_tag->delete();
			}
		}

		return $saved_ids;
	}
}
