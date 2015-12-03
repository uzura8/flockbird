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
	private static $member_id = 1;
	private static $liked_member_id_before = 0;
	private static $timeline_like_count = 0;
	private static $timeline;
	private static $timeline_before;
	private static $is_check_view_cache;
	private static $view_cache_before;
	private static $is_check_notice_cache;
	private static $foreign_table = 'timeline';
	private static $type_key = 'like';

	public static function setUpBeforeClass()
	{
		self::$timeline = Site_Test::setup_timeline(self::$member_id);
		self::$timeline_before = self::$timeline;
		self::$is_check_notice_cache = \Site_Notification::check_is_enabled_cahce('notice', true);
	}

	protected function setUp()
	{
		self::$timeline_like_count = \Util_Orm::get_count_all('\Timeline\Model_TimelineLike', array('timeline_id' => self::$timeline->id));
		self::$is_check_view_cache = \Config::get('timeline.articles.cache.is_use');
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
		$is_liked = (bool)Model_TimelineLike::change_registered_status4unique_key(array(
			'timeline_id' => self::$timeline->id,
			'member_id' => $member_id,
		));
		$timeline = \DB::select()->from('timeline')->where('id', self::$timeline->id)->execute()->current();
		$timeline_like = \Util_Orm::get_last_row('\Timeline\Model_TimelineLike', array('timeline_id' => self::$timeline->id));

		// 件数
		$like_count = \Util_Orm::get_count_all('\Timeline\Model_TimelineLike', array('timeline_id' => self::$timeline->id));
		$like_count_after = $is_liked ? self::$timeline_like_count + 1 : self::$timeline_like_count - 1;
		$this->assertEquals($like_count_after, $like_count);

		// 値
		$this->assertEquals($like_count, $timeline['like_count']);
		if ($is_liked)
		{
			$this->assertEquals(self::$timeline_before->sort_datetime, $timeline['sort_datetime']);
		}
		else
		{
			$this->assertNull($timeline_like);
		}

		$timeline_caches = \DB::select()->from('timeline_cache')->where('timeline_id', self::$timeline->id)->execute();
		foreach ($timeline_caches as $timeline_cache)
		{
			$this->assertEquals($like_count, $timeline_cache['like_count']);
		}

		//// timeline_cache が最新レコードになっているか
		//$timeline_cache = \Util_Orm::get_last_row('\Timeline\Model_TimelineCache');
		//$this->assertEquals(self::$timeline->id, $timeline_cache->timeline_id);

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
		\Model_MemberConfig::set_value($member_id_to, \Notice\Form_MemberConfig::get_name(self::$type_key), $mc_notice_like);
		$is_new_timeline = false;
		if ($member_id_to != self::$member_id)
		{
			self::$member_id = $member_id_to;
			self::$timeline = Site_Test::setup_timeline(self::$member_id);
			$is_new_timeline = true;
		}
		if ($is_test_after_read) $read_count = \Notice\Site_Util::change_status2read($member_id_to, self::$foreign_table, self::$timeline->id, self::$type_key);
		$notice_count_all_before = \Notice\Model_Notice::get_count();

		// set cache
		$notice_count_before = \Site_Notification::get_unread_count('notice', $member_id_to);
		if (self::$is_check_notice_cache) $this->assertFalse(\Notice\Site_Test::check_no_cache4notice_unread($member_id_to));// cache が生成されていることを確認


		// timeline_like save
		$timeline_like_id = Model_TimelineLike::change_registered_status4unique_key(array(
			'timeline_id' => self::$timeline->id,
			'member_id' => $member_id_from
		));
		self::$liked_member_id_before = $member_id_from;
		if (self::$is_check_notice_cache)
		{
			if ($is_cahce_deleted_exp)
			{
				$this->assertTrue(\Notice\Site_Test::check_no_cache4notice_unread($member_id_to));// cache が生成されていないことを確認
			}
			else
			{
				$this->assertFalse(\Notice\Site_Test::check_no_cache4notice_unread($member_id_to));// cache が生成されていることを確認
			}
		}

		// notice count に取得
		$notice_count = \Site_Notification::get_unread_count('notice', $member_id_to);
		if (self::$is_check_notice_cache) $this->assertFalse(\Notice\Site_Test::check_no_cache4notice_unread($member_id_to));// cache が生成されていることを確認

		// execute test
		$this->assertEquals($notice_count_before + $countup_num, $notice_count);// count up を確認

		// Model_Notice
		// 件数
		$notice_count_all = \Notice\Model_Notice::get_count();
		$this->assertEquals($notice_count_all_before + $countup_num_all, $notice_count_all);

		// record
		if ($notice = \Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, self::$timeline->id, \Notice\Site_Util::get_notice_type(self::$type_key)))
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

	/**
	* @dataProvider like_watch_provider
	*/
	public function test_like_watch($member_id_to, $member_id_from, $mc_notice_comment, $mc_watch_liked, $member_id_add_comment, $is_countup_watch_exp, $is_created_watch_exp, $notice_countup_num_exp)
	{
		if (!is_enabled('notice'))
		{
			\Util_Develop::output_test_info(__FILE__, __LINE__);
			$this->markTestSkipped('notice module is disabled.');
		}

		// 事前準備
		\Model_MemberConfig::set_value($member_id_from, \Notice\Form_MemberConfig::get_name('comment'), $mc_notice_comment);
		\Model_MemberConfig::set_value($member_id_from, \Notice\Site_Util::get_member_config_name_for_watch_content(self::$type_key), $mc_watch_liked);
		$is_new_timeline = false;
		if ($member_id_to != self::$member_id)
		{
			self::$member_id = $member_id_to;
			self::$timeline = Site_Test::setup_timeline(self::$member_id);
			$is_new_timeline = true;
		}
		$watch_count_all_before = \Notice\Model_MemberWatchContent::get_count();
		// 既読処理
		$read_count = \Notice\Site_Util::change_status2read($member_id_from, self::$foreign_table, self::$timeline->id, self::$type_key);

		// timeline_like save
		$timeline_like_id = Model_TimelineLike::change_registered_status4unique_key(array(
			'timeline_id' => self::$timeline->id,
			'member_id' => $member_id_from
		));
		self::$liked_member_id_before = $member_id_from;

		// Model_Notice
		$member_watch_content = \Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id(self::$foreign_table, self::$timeline->id, $member_id_from);
		if ($is_created_watch_exp)
		{
			$this->assertNotNull($member_watch_content);
		}
		else
		{
			$this->assertNull($member_watch_content);
		}
		// 件数
		$watch_count_all = \Notice\Model_MemberWatchContent::get_count();
		$watch_count_all_countup_num = $is_countup_watch_exp ? 1 : 0;
		$this->assertEquals($watch_count_all_before + $watch_count_all_countup_num, $watch_count_all);


		// set cache
		$notice_count_before = \Site_Notification::get_unread_count('notice', $member_id_from);

		// timeline_comment save
		$timeline_comment_added = \Site_Test::save_comment('timeline', self::$timeline->id, $member_id_add_comment);

		// notice count を取得
		$notice_count = \Site_Notification::get_unread_count('notice', $member_id_from);

		// execute test
		$this->assertEquals($notice_count_before + $notice_countup_num_exp, $notice_count);// count up を確認
	}

	public function like_watch_provider()
	{
		$data = array();

		//($member_id_to, $member_id_from, $mc_notice_comment, $mc_watch_liked, $member_id_add_comment, $is_countup_watch_exp, $is_created_watch_exp, $notice_countup_num_exp)
		// 自分がイイねした投稿をウォッチする
		//   member_watch_content レコードが追加されるか？
		//   member_watch_content レコードが作成されるか？
		//   追加コメントが通知されるか？
		$data[] = array(6, 4, 1, 1, 4,  true,  true, 0);// #0: 他人が自分に -> 自分が追加コメント
		$data[] = array(6, 4, 1, 1, 5, false,  true, 1);// #1: 他人が自分に -> 他人が追加コメント
		$data[] = array(6, 6, 1, 1, 5, false, false, 1);// #2: 自分が自分に -> 他人が追加コメント
		$data[] = array(6, 6, 1, 1, 6, false, false, 0);// #3: 自分が自分に -> 自分が追加コメント
		// 自分がイイねした投稿をウォッチしない(設定が NULL)
		$data[] = array(6, 8, 1, null, 9, false, false, 0);// #4: 他人が自分に -> 他人が追加コメント
		$data[] = array(6, 6, 1, null, 9, false, false, 1);// #5: 自分が自分に -> 他人が追加コメント
		$data[] = array(6, 6, 1, null, 6, false, false, 0);// #6: 自分が自分に -> 自分が追加コメント
		//// 自分がイイねした投稿をウォッチしない
		$data[] = array(7, 4, 1, 0, 4, false, false, 0);// #7:  他人が自分に -> 自分が追加コメント
		$data[] = array(7, 4, 1, 0, 5, false, false, 0);// #8:  他人が自分に -> 他人が追加コメント
		$data[] = array(7, 7, 1, 0, 5, false, false, 1);// #9: 自分が自分に -> 他人が追加コメント
		$data[] = array(7, 7, 1, 0, 7, false, false, 0);// #10: 自分が自分に -> 自分が追加コメント

		return $data;
	}

	public function test_delete_notice()
	{
		// 事前準備
		\Model_MemberConfig::set_value(2, \Notice\Form_MemberConfig::get_name(self::$type_key), 1);
		\Model_MemberConfig::set_value(2, \Notice\Site_Util::get_member_config_name_for_watch_content(self::$type_key), 1);
		\Model_MemberConfig::set_value(3, \Notice\Form_MemberConfig::get_name(self::$type_key), 1);
		\Model_MemberConfig::set_value(3, \Notice\Site_Util::get_member_config_name_for_watch_content(self::$type_key), 1);
		self::$member_id = 1;
		self::$timeline = Site_Test::setup_timeline(self::$member_id);
		$notice_count_all_before = \Notice\Model_Notice::get_count();
		$notice_status_count_all_before = \Notice\Model_NoticeStatus::get_count();
		$notice_member_from_count_all_before = \Notice\Model_NoticeMemberFrom::get_count();
		$member_watch_content_count_all_before = \Notice\Model_MemberWatchContent::get_count();

		// 他人がイイね
		$timeline_like_id = Model_TimelineLike::change_registered_status4unique_key(array(
			'timeline_id' => self::$timeline->id,
			'member_id' =>2,
		));
		$notice = \Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, self::$timeline->id, \Notice\Site_Util::get_notice_type(self::$type_key));
		$this->assertNotNull($notice);

		// 件数確認
		$this->assertEquals($notice_count_all_before + 1, \Notice\Model_Notice::get_count());
		$this->assertEquals($notice_status_count_all_before + 1, \Notice\Model_NoticeStatus::get_count());
		$this->assertEquals($notice_member_from_count_all_before + 1, \Notice\Model_NoticeMemberFrom::get_count());
		$this->assertEquals($member_watch_content_count_all_before + 1, \Notice\Model_MemberWatchContent::get_count());

		// 関連テーブルのレコードが作成されていることを確認
		$this->assertNotNull(\Notice\Model_NoticeStatus::get4member_id_and_notice_id(self::$member_id, $notice->id));
		$this->assertNotNull(\Notice\Model_NoticeMemberFrom::get4notice_id_and_member_id($notice->id, 2));
		$this->assertNotNull(\Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id(self::$foreign_table, self::$timeline->id, 2));
		$this->assertNotNull(\Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, self::$timeline->id, \Notice\Site_Util::get_notice_type(self::$type_key)));

		// イイねを取り消し
		$timeline_like_id = Model_TimelineLike::change_registered_status4unique_key(array(
			'timeline_id' => self::$timeline->id,
			'member_id' =>2,
		));

		// 件数確認
		$this->assertEquals($notice_count_all_before, \Notice\Model_Notice::get_count());
		$this->assertEquals($notice_status_count_all_before, \Notice\Model_NoticeStatus::get_count());
		$this->assertEquals($notice_member_from_count_all_before, \Notice\Model_NoticeMemberFrom::get_count());
		$this->assertEquals($member_watch_content_count_all_before + 1, \Notice\Model_MemberWatchContent::get_count());// watch は解除されない

		// 関連テーブルのレコードが削除されていることを確認
		$this->assertNull(\Notice\Model_NoticeStatus::get4member_id_and_notice_id(self::$member_id, $notice->id));
		$this->assertNull(\Notice\Model_NoticeMemberFrom::get4notice_id_and_member_id($notice->id, 2));
		$this->assertNotNull(\Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id(self::$foreign_table, self::$timeline->id, 2));// watch は解除されない
		$this->assertNull(\Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, self::$timeline->id, \Notice\Site_Util::get_notice_type(self::$type_key)));


		// 他人のイイね＋自分がコメント
		$timeline_like_id = Model_TimelineLike::change_registered_status4unique_key(array(
			'timeline_id' => self::$timeline->id,
			'member_id'   => 2,
		));
		\Site_Test::save_comment('timeline', self::$timeline->id, 1);
		$notice_like = \Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, self::$timeline->id, \Notice\Site_Util::get_notice_type(self::$type_key));
		$notice_comment = \Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, self::$timeline->id, \Notice\Site_Util::get_notice_type('comment'));
		$this->assertNotNull($notice_like);
		$this->assertNotNull($notice_comment);

		// 件数確認
		$this->assertEquals($notice_count_all_before + 2, \Notice\Model_Notice::get_count());
		$this->assertEquals($notice_status_count_all_before + 2, \Notice\Model_NoticeStatus::get_count());
		$this->assertEquals($notice_member_from_count_all_before + 2, \Notice\Model_NoticeMemberFrom::get_count());
		$this->assertEquals($member_watch_content_count_all_before + 1, \Notice\Model_MemberWatchContent::get_count());

		// 関連テーブルのレコードが作成されていることを確認
		$this->assertNotNull(\Notice\Model_NoticeStatus::get4member_id_and_notice_id(2, $notice_comment->id));
		$this->assertNotNull(\Notice\Model_NoticeStatus::get4member_id_and_notice_id(self::$member_id, $notice_like->id));
		$this->assertNotNull(\Notice\Model_NoticeMemberFrom::get4notice_id_and_member_id($notice_like->id, 2));
		$this->assertNotNull(\Notice\Model_NoticeMemberFrom::get4notice_id_and_member_id($notice_comment->id, self::$member_id));
		$this->assertNotNull(\Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id(self::$foreign_table, self::$timeline->id, 2));
		$this->assertNull(\Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id(self::$foreign_table, self::$timeline->id, self::$member_id));
		$this->assertNotNull(\Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, self::$timeline->id, \Notice\Site_Util::get_notice_type(self::$type_key)));

		// イイねを取り消し
		$timeline_like_id = Model_TimelineLike::change_registered_status4unique_key(array(
			'timeline_id' => self::$timeline->id,
			'member_id' =>2,
		));

		// 件数確認
		$this->assertEquals($notice_count_all_before + 1, \Notice\Model_Notice::get_count());
		$this->assertEquals($notice_status_count_all_before + 1, \Notice\Model_NoticeStatus::get_count());
		$this->assertEquals($notice_member_from_count_all_before + 1, \Notice\Model_NoticeMemberFrom::get_count());
		$this->assertEquals($member_watch_content_count_all_before + 1, \Notice\Model_MemberWatchContent::get_count());// watch は解除されない

		// 関連テーブルのレコードが削除されていることを確認
		$this->assertNotNull(\Notice\Model_NoticeStatus::get4member_id_and_notice_id(2, $notice_comment->id));
		$this->assertNull(\Notice\Model_NoticeStatus::get4member_id_and_notice_id(self::$member_id, $notice_like->id));
		$this->assertNull(\Notice\Model_NoticeMemberFrom::get4notice_id_and_member_id($notice_like->id, 2));
		$this->assertNotNull(\Notice\Model_NoticeMemberFrom::get4notice_id_and_member_id($notice_comment->id, self::$member_id));
		$this->assertNotNull(\Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id(self::$foreign_table, self::$timeline->id, 2));
		$this->assertNull(\Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id(self::$foreign_table, self::$timeline->id, self::$member_id));
		$this->assertNull(\Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, self::$timeline->id, \Notice\Site_Util::get_notice_type(self::$type_key)));


		// 他人がイイね
		$timeline_like_id = Model_TimelineLike::change_registered_status4unique_key(array(
			'timeline_id' => self::$timeline->id,
			'member_id'   => 3,
		));
		$notice = \Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, self::$timeline->id, \Notice\Site_Util::get_notice_type(self::$type_key));
		$this->assertNotNull($notice);

		// timeline 削除
		self::$timeline->delete();

		// 件数確認
		$this->assertEquals($notice_count_all_before, \Notice\Model_Notice::get_count());
		$this->assertEquals($notice_status_count_all_before, \Notice\Model_NoticeStatus::get_count());
		$this->assertEquals($notice_member_from_count_all_before, \Notice\Model_NoticeMemberFrom::get_count());
		$this->assertEquals($member_watch_content_count_all_before, \Notice\Model_MemberWatchContent::get_count());

		// 関連テーブルのレコードが削除されていることを確認
		$this->assertNull(\Notice\Model_NoticeStatus::get4member_id_and_notice_id(self::$member_id, $notice->id));
		$this->assertNull(\Notice\Model_NoticeMemberFrom::get4notice_id_and_member_id($notice->id, 3));
		$this->assertNull(\Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id(self::$foreign_table, self::$timeline->id, 3));
		$this->assertNull(\Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, self::$timeline->id, \Notice\Site_Util::get_notice_type(self::$type_key)));
	}
}
