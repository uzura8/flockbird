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
	private static $is_check_view_cache;
	private static $view_cache_before;
	private static $member_id = 1;
	private static $like_count = 0;
	private static $timeline;
	private static $timeline_comment;
	private static $commented_member_id_before;
	private static $is_check_notice_cache;
	private static $foreign_table = 'timeline_comment';
	private static $type_key = 'like';

	public static function setUpBeforeClass()
	{
		self::$is_check_view_cache = \Config::get('timeline.articles.cache.is_use');
		self::$is_check_notice_cache = \Site_Notification::check_is_enabled_cahce('notice', true);
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

	/**
	* @dataProvider like_notice_provider
	*/
	public function test_like_notice($member_id_to, $mc_notice_like, $member_id_from, $is_test_after_read, $is_cahce_deleted_exp, $countup_num, $countup_num_all)
	{
		if (!is_enabled('notice'))
		{
			\Util_Develop::output_test_info(__FILE__, __LINE__);
			$this->markTestSkipped('notice module is disabled.');
		}

		// 事前準備
		\Model_MemberConfig::set_value($member_id_to, \Notice\Form_MemberConfig::get_name('like'), $mc_notice_like);
		$is_new = false;
		if (!self::$commented_member_id_before || $member_id_to != self::$commented_member_id_before)
		{
			self::$timeline = Site_Test::setup_timeline(self::$member_id);
			self::$timeline_comment = \Site_Test::save_comment('timeline', self::$timeline->id, $member_id_to);
			$is_new = true;
		}
		self::$commented_member_id_before = $member_id_to;
		if ($is_test_after_read) $read_count = \Notice\Site_Util::change_status2read($member_id_to, self::$foreign_table, self::$timeline_comment->id, self::$type_key);
		$notice_count_all_before = \Notice\Model_Notice::get_count();

		// set cache
		$notice_count_before = \Site_Notification::get_unread_count('notice', $member_id_to);
		if (self::$is_check_notice_cache) $this->assertFalse(\Notice\Site_Test::check_no_cache4notice_unread($member_id_to));// cache が生成されていることを確認

		// like save
		$is_liked = (bool)Model_TimelineCommentLike::change_registered_status4unique_key(array(
			'timeline_comment_id' => self::$timeline_comment->id,
			'member_id' => $member_id_from
		));
		if (self::$is_check_notice_cache)
		{
			if ($is_cahce_deleted_exp)
			{
				$this->assertTrue(\Notice\Site_Test::check_no_cache4notice_unread($member_id_to));
			}
			else
			{
				$this->assertFalse(\Notice\Site_Test::check_no_cache4notice_unread($member_id_to));
			}
		}

		// notice count 取得
		$notice_count = \Site_Notification::get_unread_count('notice', $member_id_to);
		if (self::$is_check_notice_cache) $this->assertFalse(\Notice\Site_Test::check_no_cache4notice_unread($member_id_to));// cache が生成されていることを確認

		// execute test
		$this->assertEquals($notice_count_before + $countup_num, $notice_count);// count up を確認

		// Model_Notice
		// 件数
		$notice_count_all = \Notice\Model_Notice::get_count();
		$this->assertEquals($notice_count_all_before + $countup_num_all, $notice_count_all);

		// record
		if ($notice = \Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, self::$timeline_comment->id, \Notice\Site_Util::get_notice_type(self::$type_key)))
		{
			$notice_status = \Notice\Model_NoticeStatus::get4member_id_and_notice_id($member_id_to, $notice->id);
			$notice_member_from = \Notice\Model_NoticeMemberFrom::get_last();
			if ($mc_notice_like !== 0 && $member_id_to != $member_id_from)
			{
				$this->assertEquals($member_id_from, $notice_member_from->member_id);
			}
			$this->assertEquals($notice_member_from->created_at, $notice_status->sort_datetime);
		}
	}

	public function like_notice_provider()
	{
		$data = array();

		// ($member_id_to, $mc_notice_like, $member_id_from, $is_test_after_read, $is_cahce_deleted_exp, $countup_num, $countup_num_all)
		// お知らせを受け取る
		$data[] = array(2, 1, 2, false, false, 0,  0);// #0: 未読 / 自分が自分に
		$data[] = array(2, 1, 1, false, true,  1,  1);// #1: 未読 / 他人が自分に
		$data[] = array(2, 1, 1, false, true, -1, -1);// #2: 未読 / 再度他人が自分に(イイねの取り消し)
		$data[] = array(3, 1, 3, true,  false, 0,  0);// #3: 既読 / 自分が自分に
		$data[] = array(3, 1, 1, true,  true,  1,  1);// #4: 既読 / 他人が自分に
		$data[] = array(3, 1, 1, true,  true,  0, -1);// #5: 既読 / 再度他人が自分に(イイねの取り消し)

		// お知らせを受け取らない->受け取る
		$data[] = array(4, 0, 1, false, false, 0,  0);// #6:  未読 / 他人が自分に
		$data[] = array(4, 1, 1, false, false, 0,  0);// #7:  未読 / 他人が自分に, 受け取るに変更
		$data[] = array(4, 1, 1, false,  true, 1,  1);// #8:  未読 / 他人が自分に, 受け取るに変更

		// お知らせを受け取る->受け取らない
		$data[] = array(5, null, 1, false, true,  1, 1);//  #9: 未読 / 他人が自分に
		$data[] = array(5,    0, 2, false, false, 0, 0);// #10: 未読 / 再度他人が自分に

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

	public function test_delete_notice()
	{
		// 事前準備
		$config_type_key = 'like';
		\Model_MemberConfig::set_value(1, \Notice\Form_MemberConfig::get_name('comment'), 0);
		\Model_MemberConfig::set_value(2, \Notice\Form_MemberConfig::get_name($config_type_key), 1);
		\Model_MemberConfig::set_value(2, \Notice\Site_Util::get_member_config_name_for_watch_content('comment'), 0);

		self::$member_id = 1;
		self::$timeline = Site_Test::setup_timeline(self::$member_id);
		self::$timeline_comment = \Site_Test::save_comment('timeline', self::$timeline->id, 2);

		$notice_count_all_before = \Notice\Model_Notice::get_count();
		$notice_status_count_all_before = \Notice\Model_NoticeStatus::get_count();
		$notice_member_from_count_all_before = \Notice\Model_NoticeMemberFrom::get_count();

		// イイね実行
		$is_liked = (bool)Model_TimelineCommentLike::change_registered_status4unique_key(array(
			'timeline_comment_id' => self::$timeline_comment->id,
			'member_id' => 4,
		));
		$notice = \Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, self::$timeline_comment->id, \Notice\Site_Util::get_notice_type(self::$type_key));
		$this->assertNotNull($notice);

		// 件数確認
		$this->assertEquals($notice_count_all_before + 1, \Notice\Model_Notice::get_count());
		$this->assertEquals($notice_status_count_all_before + 1, \Notice\Model_NoticeStatus::get_count());
		$this->assertEquals($notice_member_from_count_all_before + 1, \Notice\Model_NoticeMemberFrom::get_count());

		// 関連テーブルのレコードが作成されていることを確認
		$this->assertNotNull(\Notice\Model_NoticeStatus::get4member_id_and_notice_id(2, $notice->id));
		$this->assertNotNull(\Notice\Model_NoticeMemberFrom::get4notice_id_and_member_id($notice->id, 4));
		$this->assertNotNull(\Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, self::$timeline_comment->id, \Notice\Site_Util::get_notice_type(self::$type_key)));

		// イイねを取り消し
		$is_liked = (bool)Model_TimelineCommentLike::change_registered_status4unique_key(array(
			'timeline_comment_id' => self::$timeline_comment->id,
			'member_id' => 4,
		));

		// 件数確認
		$this->assertEquals($notice_count_all_before, \Notice\Model_Notice::get_count());
		$this->assertEquals($notice_status_count_all_before, \Notice\Model_NoticeStatus::get_count());
		$this->assertEquals($notice_member_from_count_all_before, \Notice\Model_NoticeMemberFrom::get_count());

		// 関連テーブルのレコードが削除されていることを確認
		$this->assertNull(\Notice\Model_NoticeStatus::get4member_id_and_notice_id(2, $notice->id));
		$this->assertNull(\Notice\Model_NoticeMemberFrom::get4notice_id_and_member_id($notice->id, 4));
		$this->assertNull(\Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, self::$timeline_comment->id, \Notice\Site_Util::get_notice_type(self::$type_key)));

		// イイね実行
		$is_liked = (bool)Model_TimelineCommentLike::change_registered_status4unique_key(array(
			'timeline_comment_id' => self::$timeline_comment->id,
			'member_id' => 4,
		));
		$notice = \Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, self::$timeline_comment->id, \Notice\Site_Util::get_notice_type(self::$type_key));
		$this->assertNotNull($notice);

		// timeline_comment 削除
		self::$timeline_comment->delete();
		// 件数確認
		$this->assertEquals($notice_count_all_before, \Notice\Model_Notice::get_count());
		$this->assertEquals($notice_status_count_all_before, \Notice\Model_NoticeStatus::get_count());
		$this->assertEquals($notice_member_from_count_all_before, \Notice\Model_NoticeMemberFrom::get_count());

		// 再度 comment 投稿
		self::$timeline_comment = \Site_Test::save_comment('timeline', self::$timeline->id, 2);
		// イイね実行
		$is_liked = (bool)Model_TimelineCommentLike::change_registered_status4unique_key(array(
			'timeline_comment_id' => self::$timeline_comment->id,
			'member_id' => 4,
		));
		// 件数確認
		$this->assertEquals($notice_count_all_before + 1, \Notice\Model_Notice::get_count());
		$this->assertEquals($notice_status_count_all_before + 1, \Notice\Model_NoticeStatus::get_count());
		$this->assertEquals($notice_member_from_count_all_before + 1, \Notice\Model_NoticeMemberFrom::get_count());

		// timeline 削除
		self::$timeline->delete();

		// 件数確認
		$this->assertEquals($notice_count_all_before, \Notice\Model_Notice::get_count());
		$this->assertEquals($notice_status_count_all_before, \Notice\Model_NoticeStatus::get_count());
		$this->assertEquals($notice_member_from_count_all_before, \Notice\Model_NoticeMemberFrom::get_count());

		// 関連テーブルのレコードが削除されていることを確認
		$this->assertNull(\Notice\Model_NoticeStatus::get4member_id_and_notice_id(2, $notice->id));
		$this->assertNull(\Notice\Model_NoticeMemberFrom::get4notice_id_and_member_id($notice->id, 4));
		$this->assertNull(\Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, self::$timeline_comment->id, \Notice\Site_Util::get_notice_type(self::$type_key)));
	}

	public static function execute_like($timeline_comment_id, $member_id)
	{
		return (bool)Model_TimelineCommentLike::change_registered_status4unique_key(array(
			'timeline_comment_id' => $timeline_comment_id,
			'member_id' => $member_id,
		));
	}
}
