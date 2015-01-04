<?php
namespace Timeline;

/**
 * Model_TimelineCommentLike class tests
 *
 * @group Modules
 * @group Model
 */
class Test_Model_TimelineCommentLike extends \TestCase
{
	private static $member_id = 1;
	private static $like_count = 0;
	private static $timeline;
	private static $timeline_comment;
	private static $is_check_view_cache;
	private static $view_cache_before;
	private static $is_check_notice_cache;

	public static function setUpBeforeClass()
	{
		self::$is_check_view_cache = \Config::get('timeline.articles.cache.is_use');
		self::$is_check_notice_cache = (is_enabled('notice') && \Config::get('notice.cache.unreadCount.isEnabled'));
		self::$timeline = Site_Test::setup_timeline(self::$member_id);
		self::$timeline_comment = \Site_Test::save_comment('timeline', self::$timeline->id, self::$member_id);
	}

	protected function setUp()
	{
		self::$like_count = \Util_Orm::get_count_all('\Timeline\Model_TimelineCommentLike', array('timeline_comment_id' => self::$timeline_comment->id));
		// timeline view cache 作成
		if (self::$is_check_view_cache)
		{
			Site_Util::get_article_main_view(self::$timeline->id);
			self::$view_cache_before = \Cache::get(Site_Util::get_cache_key(self::$timeline->id), \Config::get('timeline.articles.cache.expir'));
		}
	}

	/**
	* @dataProvider update_like_provider
	*/
	public function test_update_like($member_id)
	{
		// timeline_like save
		\Util_Develop::sleep();
		$is_liked = self::execute_like(self::$timeline_comment->id, $member_id);

		self::$timeline_comment = Model_TimelineComment::find(self::$timeline_comment->id);
		$timeline_comment_like = \Util_Orm::get_last_row('\Timeline\Model_TimelineCommentLike', array('timeline_comment_id' => self::$timeline_comment->id));

		// 件数
		$like_count = \Util_Orm::get_count_all('\Timeline\Model_TimelineCommentLike', array('timeline_comment_id' => self::$timeline_comment->id));
		$like_count_expect = $is_liked ? self::$like_count + 1 : self::$like_count - 1;
		$this->assertEquals($like_count_expect, $like_count);

		// 値
		$this->assertEquals($like_count, self::$timeline_comment->like_count);
		if (!$is_liked)
		{
			$this->assertNull($timeline_comment_like);
		}

		// timeline view cache check
		if (self::$is_check_view_cache)
		{
			$this->assertEquals(self::$view_cache_before, \Timeline\Site_Util::get_view_cache(self::$timeline->id));
		}
	}

	public function update_like_provider()
	{
		$data = array();

		// 新規投稿
		$data[] = array(1);
		$data[] = array(1);
		$data[] = array(2);

		return $data;
	}

	public function test_get_members()
	{
		$timeline_comment = \Site_Test::save_comment('timeline', self::$timeline->id, self::$member_id);
		$timeline_comment_id = $timeline_comment->id;

		// like 実行
		self::execute_like($timeline_comment_id, 3);
		self::execute_like($timeline_comment_id, 4);
		self::execute_like($timeline_comment_id, 5);
		self::execute_like($timeline_comment_id, 3);

		// 件数
		$like_count = \Util_Orm::get_count_all('\Timeline\Model_TimelineCommentLike', array('timeline_comment_id' => $timeline_comment_id));
		$this->assertEquals(2, $like_count);
		
		$timeline_comment_likes = Model_TimelineCommentLike::query()->where('timeline_comment_id', $timeline_comment_id)->get();
		$this->assertCount(2, $timeline_comment_likes);
		foreach ($timeline_comment_likes as $timeline_comment_like)
		{
			$this->assertContains($timeline_comment_like->member_id, array(4, 5));
		}
	}

	public function test_delete_parent()
	{
		if (!\Util_Orm::get_count_all('\Timeline\Model_TimelineCommentLike', array('timeline_comment_id' => self::$timeline_comment->id)))
		{
			self::execute_like(self::$timeline_comment->id, 6);
			self::execute_like(self::$timeline_comment->id, 7);
		}
		self::$timeline_comment->delete();

		$like_count = \Util_Orm::get_count_all('\Timeline\Model_TimelineCommentLike', array('timeline_comment_id' => self::$timeline_comment->id));
		$this->assertEquals(0, $like_count);
	}

	public static function execute_like($timeline_comment_id, $member_id)
	{
		return (bool)Model_TimelineCommentLike::change_registered_status4unique_key(array(
			'timeline_comment_id' => $timeline_comment_id,
			'member_id' => $member_id,
		));
	}
}
