<?php
namespace Note;

/**
 * Model_NoteLike class tests
 *
 * @group Modules
 * @group Model
 */
class Test_Model_NoteLike extends \TestCase
{
	private static $note_like_count = 0;
	private static $note_id;
	private static $note_before;
	private static $timeline_id;
	private static $timeline_before;
	private static $is_check_timeline_view_cache;
	private static $timeline_view_cache_before;

	public static function setUpBeforeClass()
	{
		$note = \Note\Model_Note::forge();
		$values = array(
			'title' => 'test',
			'body' => 'This is test.',
			'public_flag' => PRJ_PUBLIC_FLAG_ALL,
		);
		$note->save_with_relations(1, $values);
		self::$note_id = $note->id;
		self::$note_before = $note;
	}

	protected function setUp()
	{
		self::$note_like_count = \Util_Orm::get_count_all('\Note\Model_NoteLike', array('note_id' => self::$note_id));

		self::$is_check_timeline_view_cache = (is_enabled('timeline') && \Config::get('timeline.articles.cache.is_use'));
		if (is_enabled('timeline'))
		{
			$timelines = \Timeline\Model_Timeline::get4foreign_table_and_foreign_ids('note', self::$note_id, \Config::get('timeline.types.note'));
			$timeline = array_shift($timelines);
			self::$timeline_id = $timeline->id;
			self::$timeline_before = $timeline;
		}

		// timeline view cache 作成
		if (self::$is_check_timeline_view_cache)
		{
			\Timeline\Site_Util::get_article_main_view(self::$timeline_id);
			self::$timeline_view_cache_before = \Cache::get(\Timeline\Site_Util::get_cache_key(self::$timeline_id), \Config::get('timeline.articles.cache.expir'));
		}
	}

	/**
	* @dataProvider update_like_provider
	*/
	public function test_update_post_like($member_id)
	{
		$note_id = self::$note_id;
		$note_before = \DB::select()->from('note')->where('id', $note_id)->execute()->current();

		// note_like save
		\Util_Develop::sleep();
		$is_liked = (bool)Model_NoteLike::change_registered_status4unique_key(array(
			'note_id' => $note_id,
			'member_id' => $member_id,
		));
		$note = \DB::select()->from('note')->where('id', $note_id)->execute()->current();
		$note_like = \Util_Orm::get_last_row('\Note\Model_NoteLike', array('note_id' => $note_id));

		// 件数
		$like_count = \Util_Orm::get_count_all('\Note\Model_NoteLike', array('note_id' => $note_id));
		$like_count_after = $is_liked ? self::$note_like_count + 1 : self::$note_like_count - 1;
		$this->assertEquals($like_count_after, $like_count);
		if (!$is_liked) $this->assertNull($note_like);

		// 値
		$this->assertEquals($like_count, $note['like_count']);
		$this->assertEquals($note_before['sort_datetime'], $note['sort_datetime']);

		// timeline 関連
		if (is_enabled('timeline'))
		{
			$timeline_id = self::$timeline_id;
			$timeline = \DB::select()->from('timeline')->where('id', $timeline_id)->execute()->current();
			// 値
			$this->assertEquals($like_count, $timeline['like_count']);
			$this->assertEquals(self::$timeline_before->sort_datetime, $timeline['sort_datetime']);

			$timeline_caches = \DB::select()->from('timeline_cache')->where('timeline_id', $timeline_id)->execute();
			foreach ($timeline_caches as $timeline_cache)
			{
				$this->assertEquals($like_count, $timeline_cache['like_count']);
			}

			//// timeline_cache が最新レコードになっているか
			//$timeline_cache = \Util_Orm::get_last_row('\Timeline\Model_TimelineCache');
			//$this->assertEquals($timeline_id, $timeline_cache->timeline_id);

			// timeline view cache check
			if (self::$is_check_timeline_view_cache)
			{
				try
				{
					$timeline_view_cache = \Cache::get(\Timeline\Site_Util::get_cache_key($timeline_id), \Config::get('timeline.articles.cache.expir'));
				}
				catch (\CacheNotFoundException $e)
				{
					$timeline_view_cache = null;
				}
				$this->assertEquals(self::$timeline_view_cache_before, $timeline_view_cache);
			}
		}
	}

	public function update_like_provider()
	{
		$data = array();

		$data[] = array(1);
		$data[] = array(1);

		return $data;
	}
}
