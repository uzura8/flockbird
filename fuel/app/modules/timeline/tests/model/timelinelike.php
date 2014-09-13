<?php
namespace Timeline;

/**
 * Model_TimelineLike class tests
 *
 * @group Modules
 * @group Model
 */
class Test_Model_TimelineLike extends \TestCase
{
	private static $timeline_like_count = 0;
	private static $timeline_id;
	private static $timeline_before;
	private static $is_check_view_cache;
	private static $view_cache_before;

	public static function setUpBeforeClass()
	{
		$body = 'This is test.';
		$timeline = Site_Model::save_timeline(1, PRJ_PUBLIC_FLAG_ALL, 'normal', null, $body);
		self::$timeline_id = $timeline->id;
		self::$timeline_before = $timeline;
	}

	protected function setUp()
	{
		self::$timeline_like_count = \Util_Orm::get_count_all('\Timeline\Model_TimelineLike', array('timeline_id' => self::$timeline_id));
		self::$is_check_view_cache = \Config::get('timeline.articles.cache.is_use');
		// timeline view cache 作成
		if (self::$is_check_view_cache)
		{
			Site_Util::get_article_main_view(self::$timeline_id);
			self::$view_cache_before = \Cache::get(Site_Util::get_cache_key(self::$timeline_id), \Config::get('timeline.articles.cache.expir'));
		}
	}

	/**
	* @dataProvider update_like_provider
	*/
	public function test_update_like($member_id)
	{
		$timeline_id = self::$timeline_id;

		// timeline_like save
		$is_liked = (bool)Model_TimelineLike::change_registered_status4unique_key(array(
			'timeline_id' => $timeline_id,
			'member_id' => $member_id,
		));
		$timeline = \DB::select()->from('timeline')->where('id', $timeline_id)->execute()->current();
		$timeline_like = \Util_Orm::get_last_row('\Timeline\Model_TimelineLike', array('timeline_id' => $timeline_id));

		// 件数
		$like_count = \Util_Orm::get_count_all('\Timeline\Model_TimelineLike', array('timeline_id' => $timeline_id));
		$like_count_after = $is_liked ? self::$timeline_like_count + 1 : self::$timeline_like_count - 1;
		$this->assertEquals($like_count_after, $like_count);

		// 値
		$this->assertEquals($like_count, $timeline['like_count']);
		if ($is_liked)
		{
			$this->assertEquals($timeline_like->created_at, $timeline['sort_datetime']);
		}
		else
		{
			$this->assertNull($timeline_like);
		}

		$timeline_caches = \DB::select()->from('timeline_cache')->where('timeline_id', $timeline_id)->execute();
		foreach ($timeline_caches as $timeline_cache)
		{
			$this->assertEquals($like_count, $timeline_cache['like_count']);
		}

		//// timeline_cache が最新レコードになっているか
		//$timeline_cache = \Util_Orm::get_last_row('\Timeline\Model_TimelineCache');
		//$this->assertEquals($timeline_id, $timeline_cache->timeline_id);

		// timeline view cache check
		if (self::$is_check_view_cache)
		{
			try
			{
				$view_cache = \Cache::get(Site_Util::get_cache_key($timeline_id), \Config::get('timeline.articles.cache.expir'));
			}
			catch (\CacheNotFoundException $e)
			{
				$view_cache = null;
			}
			$this->assertEquals(self::$view_cache_before, $view_cache);
		}
	}

	public function update_like_provider()
	{
		$data = array();

		// 新規投稿
		$data[] = array(1);

		return $data;
	}
}
