<?php
namespace Timeline;

/**
 * Model_TimelineComment class tests
 *
 * @group Modules
 * @group Model
 */
class Test_Model_TimelineComment extends \TestCase
{
	private static $timeline_comment_count = 0;
	private static $timeline_id;
	private static $timeline_before;
	//private static $timeline_id;
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
		self::$timeline_comment_count = \Util_Orm::get_count_all('\Timeline\Model_TimelineComment', array('timeline_id' => self::$timeline_id));

		self::$is_check_view_cache = (is_enabled('timeline') && \Config::get('timeline.articles.cache.is_use'));

		// timeline view cache 作成
		if (self::$is_check_view_cache)
		{
			Site_Util::get_article_main_view(self::$timeline_id);
			self::$view_cache_before = \Cache::get(\Timeline\Site_Util::get_cache_key(self::$timeline_id), \Config::get('timeline.articles.cache.expir'));
		}
	}

	/**
	* @dataProvider save_comment_provider
	*/
	public function test_save_comment($member_id, $body)
	{
		$timeline_id = self::$timeline_id;

		// timeline_comment save
		$comment = new Model_TimelineComment(array(
			'body' => $body,
			'timeline_id' => $timeline_id,
			'member_id' => $member_id,
		));
		$comment->save();
		$timeline_comment = \Util_Orm::get_last_row('\Timeline\Model_TimelineComment', array('timeline_id' => $timeline_id));
		$timeline = \DB::select()->from('timeline')->where('id', $timeline_id)->execute()->current();

		// 件数
		$comment_count = \Util_Orm::get_count_all('\Timeline\Model_TimelineComment', array('timeline_id' => $timeline_id));
		$this->assertEquals(self::$timeline_comment_count + 1, $comment_count);

		// 値
		$this->assertEquals($comment_count, $timeline['comment_count']);
		$this->assertEquals($timeline_comment->created_at, $timeline['sort_datetime']);

		$timeline_caches = \DB::select()->from('timeline_cache')->where('timeline_id', $timeline_id)->execute();
		foreach ($timeline_caches as $timeline_cache)
		{
			$this->assertEquals($comment_count, $timeline_cache['comment_count']);
		}

		// timeline_cache が最新レコードになっているか
		$timeline_cache = \Util_Orm::get_last_row('\Timeline\Model_TimelineCache');
		$this->assertEquals($timeline_id, $timeline_cache->timeline_id);

		// timeline view cache check
		if (self::$is_check_view_cache)
		{
			try
			{
				$view_cache = \Cache::get(\Timeline\Site_Util::get_cache_key($timeline_id), \Config::get('timeline.articles.cache.expir'));
			}
			catch (\CacheNotFoundException $e)
			{
				$view_cache = null;
			}
			$this->assertEquals(self::$view_cache_before, $view_cache);
		}
	}

	public function save_comment_provider()
	{
		$data = array();

		// 新規投稿
		$data[] = array(1, 'This is test comment1.');
		$data[] = array(1, 'This is test comment2.');

		return $data;
	}

	public function test_delete()
	{
		$timeline_id = self::$timeline_id;

		$this->save_comment(1, 'Test comment1.');
		$this->save_comment(1, 'Test comment2.');
		$timeline_comment = $this->save_comment(1, 'Test comment3.');

		// note_comment delete
		$timeline_comment->delete();
		$timeline = \DB::select()->from('timeline')->where('id', $timeline_id)->execute()->current();

		// 件数
		$comment_count = \Util_Orm::get_count_all('\Timeline\Model_TimelineComment', array('timeline_id' => $timeline_id));
		$this->assertEquals(self::$timeline_comment_count + 3 - 1, $comment_count);

		// 値
		$this->assertEquals($comment_count, $timeline['comment_count']);
		$this->assertEquals(self::$timeline_before->sort_datetime, $timeline['sort_datetime']);

		$timeline_caches = \DB::select()->from('timeline_cache')->where('timeline_id', $timeline_id)->execute();
		foreach ($timeline_caches as $timeline_cache)
		{
			$this->assertEquals($comment_count, $timeline_cache['comment_count']);
		}

		// timeline view cache check
		if (self::$is_check_view_cache)
		{
			try
			{
				$view_cache = \Cache::get(\Timeline\Site_Util::get_cache_key($timeline_id), \Config::get('timeline.articles.cache.expir'));
			}
			catch (\CacheNotFoundException $e)
			{
				$view_cache = null;
			}
			$this->assertEquals(self::$view_cache_before, $view_cache);
		}
	}

	private function save_comment($member_id, $body)
	{
		$comment = new Model_TimelineComment(array(
			'body' => $body,
			'timeline_id' => self::$timeline_id,
			'member_id' => $member_id,
		));
		$comment->save();

		return $comment;
	}
}
