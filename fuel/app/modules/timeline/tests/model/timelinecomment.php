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
	private static $member_id = 1;
	private static $commented_member_id_before = 0;
	private static $timeline_comment_count = 0;
	private static $timeline_id;
	private static $timeline_before;
	private static $view_cache_before;
	private static $is_check_view_cache;
	private static $is_check_notice_cache;
	private static $foreign_table = 'timeline';
	private static $type_key = 'comment';

	public static function setUpBeforeClass()
	{
		self::$is_check_view_cache = (is_enabled('timeline') && \Config::get('timeline.articles.cache.is_use'));
		self::$is_check_notice_cache = (is_enabled('notice') && \Config::get('notice.cache.unreadCount.isEnabled'));
		self::set_timeline();
	}

	protected function setUp()
	{
		self::$timeline_before = Model_Timeline::find(self::$timeline_id);
		self::$timeline_comment_count = \Util_Orm::get_count_all('\Timeline\Model_TimelineComment', array('timeline_id' => self::$timeline_id));

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
		\Util_Develop::sleep();
		$timeline_comment = self::save_comment($member_id, $body);
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
		$data[] = array(2, 'This is test comment2.');
		$data[] = array(3, 'This is test comment3.');

		return $data;
	}

	public function test_delete()
	{
		$timeline_id = self::$timeline_id;

		self::save_comment(self::$member_id, 'Test comment1.');
		self::save_comment(self::$member_id, 'Test comment2.');
		$timeline_comment = self::save_comment(self::$member_id, 'Test comment3.');

		// note_comment delete
		\Util_Develop::sleep();
		$timeline_comment->delete();

		$timeline = Model_Timeline::find($timeline_id);

		// 件数
		$comment_count = \Util_Orm::get_count_all('\Timeline\Model_TimelineComment', array('timeline_id' => $timeline_id));
		$this->assertEquals(self::$timeline_comment_count + 3 - 1, $comment_count);

		// 値
		$this->assertEquals($comment_count, $timeline->comment_count);
		$this->assertEquals(self::$timeline_before->sort_datetime, $timeline->sort_datetime);

		$timeline_caches = \DB::select()->from('timeline_cache')->where('timeline_id', $timeline_id)->execute();
		foreach ($timeline_caches as $timeline_cache)
		{
			$this->assertEquals($comment_count, $timeline_cache['comment_count']);
		}

		// timeline view cache check
		if (self::$is_check_view_cache)
		{
			$this->assertEquals(self::$view_cache_before, \Timeline\Site_Util::get_view_cache($timeline->id));
		}
	}

	/**
	* @dataProvider comment_notice_provider
	*/
	public function test_comment_notice($member_id_to, $mc_notice_comment, $member_id_from, $is_test_after_read, $is_cahce_deleted_exp, $is_countup_exp, $is_created_record_notice_exp)
	{
		if (!is_enabled('notice'))
		{
			\Util_Develop::output_test_info(__FILE__, __LINE__);
			$this->markTestSkipped('notice module is disabled.');
		}

		// 事前準備
		self::set_member_config_notice_comment($member_id_to, $mc_notice_comment);
		$is_new_timeline = false;
		if ($member_id_to != self::$member_id)
		{
			self::$member_id = $member_id_to;
			self::set_timeline();
			$is_new_timeline = true;
		}
		$timeline_id = self::$timeline_id;
		$foreign_id = self::$timeline_id;
		if ($is_test_after_read) $read_count = \Notice\Site_Util::change_status2read($member_id_to, 'timeline', $timeline_id, 'comment');
		$countup_num = $is_countup_exp ? 1 : 0;
		$notice_count_all_before = \Util_Orm::get_count_all('\Notice\Model_Notice');

		// set cache
		$notice_count_before = \Notice\Site_Util::get_unread_count($member_id_to);
		if (self::$is_check_notice_cache) $this->assertFalse(self::check_no_cache($member_id_to));// cache が生成されていることを確認

		// timeline_comment save
		$timeline_comment = self::save_comment($member_id_from);
		self::$commented_member_id_before = $member_id_from;
		if (self::$is_check_notice_cache)
		{
			if ($is_cahce_deleted_exp)
			{
				$this->assertTrue(self::check_no_cache($member_id_to));
			}
			else
			{
				$this->assertFalse(self::check_no_cache($member_id_to));
			}
		}

		// notice count に取得
		$notice_count = \Notice\Site_Util::get_unread_count($member_id_to);
		if (self::$is_check_notice_cache) $this->assertFalse(self::check_no_cache($member_id_to));// cache が生成されていることを確認

		// execute test
		$this->assertEquals($notice_count_before + $countup_num, $notice_count);// count up を確認

		// Model_Notice
		// 件数
		$notice_count_all = \Util_Orm::get_count_all('\Notice\Model_Notice');
		$notice_count_all_countup_num = $is_created_record_notice_exp ? 1 : 0;
		$this->assertEquals($notice_count_all_before + $notice_count_all_countup_num, $notice_count_all);

		// record
		if ($notice = \Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $foreign_id, \Notice\Site_Util::get_notice_type(self::$type_key)))
		{
			$notice_status = \Notice\Model_NoticeStatus::get4member_id_and_notice_id($member_id_to, $notice->id);
			$notice_member_from = \Util_Orm::get_last_row('\Notice\Model_NoticeMemberFrom');
			if ($mc_notice_comment !== 0 && $member_id_to != $member_id_from)
			{
				$this->assertEquals($member_id_from, $notice_member_from->member_id);
			}
			$this->assertEquals($notice_member_from->created_at, $notice_status->sort_datetime);
		}
	}

	public function comment_notice_provider()
	{
		$data = array();

		//($member_id_to, $mc_notice_comment, $member_id_from, $is_test_after_read, $is_cahce_deleted_exp, $is_countup_exp, $is_created_record_notice_exp)
		// お知らせを受け取る
		$data[] = array(2, 1, 2, false, false, false, false);// #0: 未読 / 自分が自分に
		$data[] = array(2, 1, 1, false, true,   true,  true);// #1: 未読 / 他人が自分に
		$data[] = array(2, 1, 1, false, true,  false, false);// #2: 未読 / 再度他人が自分に
		$data[] = array(3, 1, 3, true,  false, false, false);// #3: 既読 / 自分が自分に
		$data[] = array(3, 1, 1, true,  true,   true,  true);// #4: 既読 / 他人が自分に
		$data[] = array(3, 1, 1, true,  true,   true, false);// #5: 既読 / 再度他人が自分に

		// お知らせを受け取らない
		$data[] = array(4, 0, 1, false, false, false, false);// #6:  未読 / 他人が自分に
		$data[] = array(4, 1, 1, false, true,   true,  true);// #7:  未読 / 他人が自分に, 受け取るに変更

		// お知らせを受け取る
		$data[] = array(5, null, 1, false, true,  true , true);  // #8: 未読 / 他人が自分に
		$data[] = array(5,    0, 2, false, false,  false, false);// #9: 未読 / 再度他人が自分に

		return $data;
	}

	private static function save_comment($member_id, $body = null)
	{
		if (is_null($body)) $body = 'This is test comment.';
		$comment = new Model_TimelineComment(array(
			'body' => $body,
			'timeline_id' => self::$timeline_id,
			'member_id' => $member_id,
		));
		$comment->save();

		return $comment;
	}

	private static function set_timeline()
	{
		$timeline = Site_Model::save_timeline(self::$member_id, PRJ_PUBLIC_FLAG_ALL, 'normal', null, null, 'This is test.');
		self::$timeline_id = $timeline->id;
	}

	private static function set_member_config_notice_comment($member_id, $value)
	{
		$name = 'notice_comment';
		if ($member_config = \Model_MemberConfig::get_one4member_id_and_name($member_id, $name))
		{
			if (is_null($value))
			{
				$member_config->delete();
				return;
			}

			$member_config->value = $value;
			$member_config->save();
			return;
		}

		if (is_null($value)) return;

		$member_config = \Model_MemberConfig::forge(array(
			'member_id' => $member_id,
			'name' => $name,
			'value' => $value,
		));
		$member_config->save();
	}

	private static function check_no_cache($member_id)
	{
		return is_null(\Site_Develop::get_cache(\Notice\Site_Util::get_unread_count_cache_key($member_id), \Config::get('notice.cache.unreadCount.expir')));
	}
}
