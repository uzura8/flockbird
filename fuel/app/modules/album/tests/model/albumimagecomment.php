<?php
namespace Album;

/**
 * Model_AlbumImageComment class tests
 *
 * @group Modules
 * @group Model
 */
class Test_Model_AlbumImageComment extends \TestCase
{
	private static $album;
	private static $member_id = 1;
	private static $commented_member_id_before = 0;
	private static $upload_file_path;
	private static $album_image;
	private static $album_image_comment_count = 0;
	private static $is_check_notice_cache;
	private static $foreign_table = 'album_image';
	private static $type_key = 'comment';

	public static function setUpBeforeClass()
	{
		self::set_album_image();
	}

	protected function setUp()
	{
		self::$album_image = self::get_album_image();
		self::$album_image_comment_count = self::get_album_image_comment_count();
		self::$is_check_notice_cache = \Site_Notification::check_is_enabled_cahce('notice', true);
	}

	/**
	* @dataProvider save_comment_provider
	*/
	public function test_save_comment($member_id, $body)
	{
		// album_image_comment save
		\Util_Develop::sleep();
		$album_image_comment = $this->save_comment($member_id, $body);

		$album_image_id = self::$album_image->id;

		// 件数
		$comment_count = \Util_Orm::get_count_all('\Album\Model_AlbumImageComment', array('album_image_id' => $album_image_id));
		$this->assertEquals(self::$album_image_comment_count + 1, $comment_count);

		// 値
		$this->assertEquals($comment_count, self::$album_image->comment_count);
		$this->assertEquals($album_image_comment->created_at, self::$album_image->sort_datetime);
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
		$album_image_id = self::$album_image->id;

		$this->save_comment(1, 'Test comment1.');
		$this->save_comment(1, 'Test comment2.');
		$album_image_comment = $this->save_comment(1, 'Test comment3.');

		// set before data.
		$album_image_before = self::get_album_image();
		$album_image_comment_count_before = self::get_album_image_comment_count();

		// album_image_comment delete
		\Util_Develop::sleep();
		$album_image_comment->delete();
		$album_image = self::get_album_image();

		// 件数
		$comment_count = self::get_album_image_comment_count();
		$this->assertEquals($album_image_comment_count_before - 1, $comment_count);

		// 値
		$this->assertEquals($comment_count, $album_image->comment_count);
		$this->assertEquals($album_image_before->sort_datetime, $album_image->sort_datetime);
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
		\Model_MemberConfig::set_value($member_id_to, \Notice\Form_MemberConfig::get_name(self::$type_key), $mc_notice_comment);
		$is_new = false;
		if ($member_id_to != self::$member_id)
		{
			self::$member_id = $member_id_to;
			self::$album_image = self::set_album_image();
			$is_new = true;
		}
		$album_image_id = self::$album_image->id;
		$foreign_id = self::$album_image->id;
		if ($is_test_after_read) $read_count = \Notice\Site_Util::change_status2read($member_id_to, self::$foreign_table, $album_image_id, self::$type_key);
		$countup_num = $is_countup_exp ? 1 : 0;
		$notice_count_all_before = \Notice\Model_Notice::get_count();

		// set cache
		$notice_count_before = \Site_Notification::get_unread_count('notice', $member_id_to);
		if (self::$is_check_notice_cache) $this->assertFalse(self::check_no_cache($member_id_to));// cache が生成されていることを確認

		// album_image_comment save
		$album_image_comment = $this->save_comment($member_id_from);
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

		// notice count
		$notice_count = \Site_Notification::get_unread_count('notice', $member_id_to);
		if (self::$is_check_notice_cache) $this->assertFalse(self::check_no_cache($member_id_to));// cache が生成されていることを確認

		// execute test
		$this->assertEquals($notice_count_before + $countup_num, $notice_count);// count up を確認

		// Model_Notice
		// count
		$notice_count_all = \Notice\Model_Notice::get_count();
		$notice_count_all_countup_num = $is_created_record_notice_exp ? 1 : 0;
		$this->assertEquals($notice_count_all_before + $notice_count_all_countup_num, $notice_count_all);

		// record
		if ($notice = \Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $foreign_id, \Notice\Site_Util::get_notice_type(self::$type_key)))
		{
			$notice_status = \Notice\Model_NoticeStatus::get4member_id_and_notice_id($member_id_to, $notice->id);
			$notice_member_from = \Notice\Model_NoticeMemberFrom::get_last();
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
		$data[] = array(2, 1, 1, false, false, false, false);// #2: 未読 / 再度他人が自分に
		$data[] = array(3, 1, 3, true,  false, false, false);// #3: 既読 / 自分が自分に
		$data[] = array(3, 1, 1, true,   true,  true,  true);// #4: 既読 / 他人が自分に
		$data[] = array(3, 1, 1, true,   true,  true, false);// #5: 既読 / 再度他人が自分に

		// お知らせを受け取らない
		$data[] = array(4, 0, 1, false, false, false, false);// #6:  未読 / 他人が自分に
		$data[] = array(4, 1, 1, false, true,   true,  true);// #7:  未読 / 他人が自分に, 受け取るに変更

		// お知らせを受け取る
		$data[] = array(5, null, 1, false, true,  true , true);  // #8: 未読 / 他人が自分に
		$data[] = array(5,    0, 2, false, false,  false, false);// #9: 未読 / 再度他人が自分に

		return $data;
	}

	/**
	* @dataProvider comment_mention_provider
	*/
	public function test_comment_mention($member_id_to, $mc_notice_comment, $member_id_from, $mention_member_ids, $countup_nums_exp, $is_cahced_mention_member_ids_exp, $countup_nums_all_exp)
	{
		if (!is_enabled('notice'))
		{
			\Util_Develop::output_test_info(__FILE__, __LINE__);
			$this->markTestSkipped('notice module is disabled.');
		}

		// 事前準備
		\Model_MemberConfig::set_value($member_id_to, \Notice\Form_MemberConfig::get_name(self::$type_key), $mc_notice_comment);
		$is_new = false;
		if ($member_id_to != self::$member_id)
		{
			self::$member_id = $member_id_to;
			self::$album_image = self::set_album_image();
			$is_new = true;
		}
		$album_image_id = self::$album_image->id;
		$foreign_id = self::$album_image->id;
		$notice_count_all_before = \Notice\Model_Notice::get_count();

		// set cache
		$notice_counts_before = array();
		foreach ($mention_member_ids as $mention_member_id)
		{
			$notice_counts_before[$mention_member_id] = \Site_Notification::get_unread_count('notice', $mention_member_id);
		}

		// comment save
		$body = \Site_Test::get_mention_body($mention_member_ids);
		$comment = self::save_comment($member_id_from, $body);
		self::$commented_member_id_before = $member_id_from;

		// check_cache
		if (self::$is_check_notice_cache)
		{
			foreach ($mention_member_ids as $mention_member_id)
			{
				if (in_array($mention_member_id, $is_cahced_mention_member_ids_exp))
				{
					$this->assertFalse(self::check_no_cache($mention_member_id));
				}
				else
				{
					$this->assertTrue(self::check_no_cache($mention_member_id));
				}
			}
		}

		foreach ($mention_member_ids as $mention_member_id)
		{
			// notice count 取得
			$notice_count = \Site_Notification::get_unread_count('notice', $member_id_to);
			if (self::$is_check_notice_cache) $this->assertFalse(self::check_no_cache($mention_member_id));// cache が生成されていることを確認
			// execute test
			$this->assertEquals($notice_counts_before[$mention_member_id] + $countup_nums_exp[$mention_member_id], $notice_count);// count up を確認
		}

		// Model_Notice
		// 件数
		$notice_count_all = \Notice\Model_Notice::get_count();
		$this->assertEquals($notice_count_all_before + $countup_nums_all_exp, $notice_count_all);
	}

	public function comment_mention_provider()
	{
		$data = array();

		//($member_id_to, $mc_notice_comment, $member_id_from, $mention_member_ids, $countup_nums_exp, $is_cahced_mention_member_ids_exp, $countup_nums_all_exp)
		// お知らせを受け取る
		$data[] = array(2, 1, 2, array(2), array(2 => 0), array(2), 0);// #0: 自分が自分に / @自分
		$data[] = array(2, 1, 3, array(2), array(2 => 1, 3 => 0), array(), 1);// #1: Aが自分に / @自分 / type=comment_mention が追加
		$data[] = array(2, 1, 3, array(3), array(2 => 0, 3 => 0), array(3), 1);// #2: Aが自分に / @A / type=comment が追加
		$data[] = array(2, 1, 3, array(3, 4), array(2 => 0, 3 => 0, 4 => 1), array(3), 0);// #3: Aが自分に / @A,B
		$data[] = array(2, 1, 3, array(3, 4, 5), array(2 => 0, 3 => 0, 4 => 0, 5 => 1), array(3, 4), 0);// #4: Aが自分に / @A,B,C
		$data[] = array(2, 1, 3, array(3, 4, 5), array(2 => 0, 3 => 0, 4 => 0, 5 => 0), array(3, 4, 5), 0);// #5: Aが自分に / @A,B,C
		$data[] = array(3, 1, 4, array(5, 6), array(3 => 1, 5 => 1, 6 => 1), array(), 2);// #6: Aが自分に / @A,B,C

		// お知らせを受け取らない
		$data[] = array(4, 0, 1, array(), array(4 => 0), array(4), 0);// #0: Aが自分に / mention 無し
		$data[] = array(4, 0, 1, array(4), array(4 => 1), array(), 1);// #0: Aが自分に / @自分

		return $data;
	}

	/**
	* @dataProvider comment_watch_provider
	*/
	public function test_comment_watch($member_id_to, $member_id_from, $mc_notice_comment, $mc_watch_commented, $member_id_add_comment, $is_countup_watch_exp, $is_created_watch_exp, $is_countup_exp)
	{
		if (!is_enabled('notice'))
		{
			\Util_Develop::output_test_info(__FILE__, __LINE__);
			$this->markTestSkipped('notice module is disabled.');
		}

		// 事前準備
		\Model_MemberConfig::set_value($member_id_from, \Notice\Form_MemberConfig::get_name(self::$type_key), $mc_notice_comment);
		\Model_MemberConfig::set_value($member_id_from, \Notice\Site_Util::get_member_config_name_for_watch_content(self::$type_key), $mc_watch_commented);
		$is_new = false;
		if ($member_id_to != self::$member_id)
		{
			self::$member_id = $member_id_to;
			self::$album_image = self::set_album_image();
			$is_new = true;
		}
		$album_image_id = self::$album_image->id;
		$foreign_id = self::$album_image->id;
		$watch_count_all_before = \Notice\Model_MemberWatchContent::get_count();
		// 既読処理
		$read_count = \Notice\Site_Util::change_status2read($member_id_from, self::$foreign_table, $album_image_id, self::$type_key);

		// album_image_comment save
		$album_image_comment = $this->save_comment($member_id_from);
		self::$commented_member_id_before = $member_id_from;

		// Model_Notice
		$member_watch_content = \Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id(self::$foreign_table, $foreign_id, $member_id_from);
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

		// album_image_comment save
		$album_image_comment_added = $this->save_comment($member_id_add_comment);

		// notice count を取得
		$notice_count = \Site_Notification::get_unread_count('notice', $member_id_from);

		// execute test
		$countup_num = $is_countup_exp ? 1 : 0;
		$this->assertEquals($notice_count_before + $countup_num, $notice_count);// count up を確認
	}

	public function comment_watch_provider()
	{
		$data = array();

		//($member_id_to, $member_id_from, $mc_notice_comment, $mc_watch_commented, $member_id_add_comment, $is_countup_watch_exp, $is_created_watch_exp, $is_countup_exp)
		// 自分がコメントした投稿をウォッチする
		$data[] = array(6, 4, 1, 1, 4,  true,  true, false);// #0: 他人が自分に -> 自分が追加コメント
		$data[] = array(6, 4, 1, 1, 5, false,  true,  true);// #1: 他人が自分に -> 他人が追加コメント
		$data[] = array(6, 6, 1, 1, 5, false, false,  true);// #2: 自分が自分に -> 他人が追加コメント
		$data[] = array(6, 6, 1, 1, 6, false, false, false);// #3: 自分が自分に -> 自分が追加コメント
		// 自分がコメントした投稿をウォッチする(設定が NULL)
		$data[] = array(6, 8, 1, null, 8,  true,  true, false);// #4: 他人が自分に -> 自分が追加コメント
		$data[] = array(6, 8, 1, null, 9, false,  true,  true);// #5: 他人が自分に -> 他人が追加コメント
		$data[] = array(6, 6, 1, null, 9, false, false,  true);// #6: 自分が自分に -> 他人が追加コメント
		$data[] = array(6, 6, 1, null, 6, false, false, false);// #7: 自分が自分に -> 自分が追加コメント
		//// 自分がコメントした投稿をウォッチしない
		$data[] = array(7, 4, 1, 0, 4, false, false, false);// #8:  他人が自分に -> 自分が追加コメント
		$data[] = array(7, 4, 1, 0, 5, false, false, false);// #9:  他人が自分に -> 他人が追加コメント
		$data[] = array(7, 7, 1, 0, 5, false, false,  true);// #10: 自分が自分に -> 他人が追加コメント
		$data[] = array(7, 7, 1, 0, 7, false, false, false);// #11: 自分が自分に -> 自分が追加コメント

		return $data;
	}

	public function test_delete_notice()
	{
		// prepare test
		\Model_MemberConfig::set_value(1, \Notice\Form_MemberConfig::get_name('comment'), 1);
		\Model_MemberConfig::set_value(2, \Notice\Form_MemberConfig::get_name(self::$type_key), 1);
		\Model_MemberConfig::set_value(2, \Notice\Site_Util::get_member_config_name_for_watch_content(self::$type_key), 1);
		\Model_MemberConfig::set_value(3, \Notice\Form_MemberConfig::get_name(self::$type_key), 1);
		\Model_MemberConfig::set_value(3, \Notice\Site_Util::get_member_config_name_for_watch_content(self::$type_key), 1);
		$notice_count_all_before = \Notice\Model_Notice::get_count();
		$notice_status_count_all_before = \Notice\Model_NoticeStatus::get_count();
		$notice_member_from_count_all_before = \Notice\Model_NoticeMemberFrom::get_count();
		$member_watch_content_count_all_before = \Notice\Model_MemberWatchContent::get_count();
		self::$member_id = 1;
		self::$album_image = self::set_album_image();
		$foreign_id = self::$album_image->id;
		$album_image_id = self::$album_image->id;

		// 他人がコメント
		$album_image_comment = $this->save_comment(2, 'Test comment1.');
		$notice = \Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $album_image_id, \Notice\Site_Util::get_notice_type(self::$type_key));
		$this->assertNotNull($notice);

		// 件数確認
		$this->assertEquals($notice_count_all_before + 1, \Notice\Model_Notice::get_count());
		$this->assertEquals($notice_status_count_all_before + 1, \Notice\Model_NoticeStatus::get_count());
		$this->assertEquals($notice_member_from_count_all_before + 1, \Notice\Model_NoticeMemberFrom::get_count());
		$this->assertEquals($member_watch_content_count_all_before + 1, \Notice\Model_MemberWatchContent::get_count());

		// 関連テーブルのレコードが作成されていることを確認
		$this->assertNotNull(\Notice\Model_NoticeStatus::get4member_id_and_notice_id(self::$member_id, $notice->id));
		$this->assertNotNull(\Notice\Model_NoticeMemberFrom::get4notice_id_and_member_id($notice->id, 2));
		$this->assertNotNull(\Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id(self::$foreign_table, $album_image_id, 2));
		$this->assertNotNull(\Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $album_image_id, \Notice\Site_Util::get_notice_type(self::$type_key)));

		// コメントを削除
		$album_image_comment->delete();

		// 件数確認
		$this->assertEquals($notice_count_all_before, \Notice\Model_Notice::get_count());
		$this->assertEquals($notice_status_count_all_before, \Notice\Model_NoticeStatus::get_count());
		$this->assertEquals($notice_member_from_count_all_before, \Notice\Model_NoticeMemberFrom::get_count());
		$this->assertEquals($member_watch_content_count_all_before + 1, \Notice\Model_MemberWatchContent::get_count());// keep watch count

		// 関連テーブルのレコードが削除されていることを確認
		$this->assertNull(\Notice\Model_NoticeStatus::get4member_id_and_notice_id(self::$member_id, $notice->id));
		$this->assertNull(\Notice\Model_NoticeMemberFrom::get4notice_id_and_member_id($notice->id, 2));
		$this->assertNotNull(\Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id(self::$foreign_table, $album_image_id, 2));// keep watch count
		$this->assertNull(\Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $album_image_id, \Notice\Site_Util::get_notice_type(self::$type_key)));


		// 自分もコメント
		$album_image_comment = $this->save_comment(2, 'Test comment2-2.');
		$this->save_comment(1, 'Test comment1.');
		$notice = \Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $album_image_id, \Notice\Site_Util::get_notice_type(self::$type_key));
		$this->assertNotNull($notice);

		// 件数確認
		$this->assertEquals($notice_count_all_before + 1, \Notice\Model_Notice::get_count());
		$this->assertEquals($notice_status_count_all_before + 2, \Notice\Model_NoticeStatus::get_count());
		$this->assertEquals($notice_member_from_count_all_before + 2, \Notice\Model_NoticeMemberFrom::get_count());
		$this->assertEquals($member_watch_content_count_all_before + 1, \Notice\Model_MemberWatchContent::get_count());

		// 関連テーブルのレコードが作成されていることを確認
		$this->assertNotNull(\Notice\Model_NoticeStatus::get4member_id_and_notice_id(2, $notice->id));
		$this->assertNotNull(\Notice\Model_NoticeStatus::get4member_id_and_notice_id(self::$member_id, $notice->id));
		$this->assertNotNull(\Notice\Model_NoticeMemberFrom::get4notice_id_and_member_id($notice->id, 2));
		$this->assertNotNull(\Notice\Model_NoticeMemberFrom::get4notice_id_and_member_id($notice->id, self::$member_id));
		$this->assertNotNull(\Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id(self::$foreign_table, $album_image_id, 2));
		$this->assertNotNull(\Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $album_image_id, \Notice\Site_Util::get_notice_type(self::$type_key)));

		// コメントを削除
		$album_image_comment->delete();

		// 件数確認
		$this->assertEquals($notice_count_all_before, \Notice\Model_Notice::get_count());
		$this->assertEquals($notice_status_count_all_before, \Notice\Model_NoticeStatus::get_count());
		$this->assertEquals($notice_member_from_count_all_before, \Notice\Model_NoticeMemberFrom::get_count());
		$this->assertEquals($member_watch_content_count_all_before + 1, \Notice\Model_MemberWatchContent::get_count());// watch は解除されない

		// 関連テーブルのレコードが削除されていることを確認
		$this->assertNull(\Notice\Model_NoticeStatus::get4member_id_and_notice_id(2, $notice->id));
		$this->assertNull(\Notice\Model_NoticeStatus::get4member_id_and_notice_id(self::$member_id, $notice->id));
		$this->assertNull(\Notice\Model_NoticeMemberFrom::get4notice_id_and_member_id($notice->id, 2));
		$this->assertNull(\Notice\Model_NoticeMemberFrom::get4notice_id_and_member_id($notice->id, self::$member_id));
		$this->assertNotNull(\Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id(self::$foreign_table, $album_image_id, 2));// watch は解除されない
		$this->assertNull(\Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $album_image_id, \Notice\Site_Util::get_notice_type(self::$type_key)));


		// 他人がコメント
		$album_image_comment = $this->save_comment(3, 'Test comment1.');
		$notice = \Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $album_image_id, \Notice\Site_Util::get_notice_type(self::$type_key));
		$this->assertNotNull($notice);

		// delete album_image
		$album_image = Model_AlbumImage::find($album_image_id);
		$album_image->delete();

		// 件数確認
		$this->assertEquals($notice_count_all_before, \Notice\Model_Notice::get_count());
		$this->assertEquals($notice_status_count_all_before, \Notice\Model_NoticeStatus::get_count());
		$this->assertEquals($notice_member_from_count_all_before, \Notice\Model_NoticeMemberFrom::get_count());
		$this->assertEquals($member_watch_content_count_all_before, \Notice\Model_MemberWatchContent::get_count());

		// 関連テーブルのレコードが削除されていることを確認
		$this->assertNull(\Notice\Model_NoticeStatus::get4member_id_and_notice_id(self::$member_id, $notice->id));
		$this->assertNull(\Notice\Model_NoticeMemberFrom::get4notice_id_and_member_id($notice->id, 3));
		$this->assertNull(\Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id(self::$foreign_table, $album_image_id, 3));
		$this->assertNull(\Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $album_image_id, \Notice\Site_Util::get_notice_type(self::$type_key)));

		// album delete test
		// setup
		self::$album_image = self::set_album_image(null, 1, self::$album->id);
		$foreign_id = self::$album_image->id;
		$album_image_id = self::$album_image->id;

		// comment athers
		$album_image_comment = $this->save_comment(3, 'Test comment1.');
		$notice = \Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $album_image_id, \Notice\Site_Util::get_notice_type(self::$type_key));
		$this->assertNotNull($notice);

		// delete album_image
		self::$album->delete();

		// 件数確認
		$this->assertEquals($notice_count_all_before, \Notice\Model_Notice::get_count());
		$this->assertEquals($notice_status_count_all_before, \Notice\Model_NoticeStatus::get_count());
		$this->assertEquals($notice_member_from_count_all_before, \Notice\Model_NoticeMemberFrom::get_count());
		$this->assertEquals($member_watch_content_count_all_before, \Notice\Model_MemberWatchContent::get_count());

		// 関連テーブルのレコードが削除されていることを確認
		$this->assertNull(\Notice\Model_NoticeStatus::get4member_id_and_notice_id(self::$member_id, $notice->id));
		$this->assertNull(\Notice\Model_NoticeMemberFrom::get4notice_id_and_member_id($notice->id, 3));
		$this->assertNull(\Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id(self::$foreign_table, $album_image_id, 3));
		$this->assertNull(\Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $album_image_id, \Notice\Site_Util::get_notice_type(self::$type_key)));

	}

	private function save_comment($member_id, $body = null)
	{
		if (is_null($body)) $body = 'This is test comment.';
		$comment = new Model_AlbumImageComment(array(
			'body' => $body,
			'album_image_id' => self::$album_image->id,
			'member_id' => $member_id,
		));
		$comment->save();

		return $comment;
	}

	private static function set_album_image($album_image_values = null, $create_count = 1, $album_id = null)
	{
		$public_flag = isset($values['public_flag']) ? $values['public_flag'] : FBD_PUBLIC_FLAG_ALL;
		if (!$album_id)
		{
			$album_values = array(
				'name' => 'test album_image.',
				'body' => 'This is test for album_image.',
				'public_flag' => $public_flag,
			);
			self::$album = self::force_save_album(self::$member_id, $album_values);
		}
		if (!$album_image_values)
		{
			$album_image_values = array(
				'name' => 'test',
				'public_flag' => FBD_PUBLIC_FLAG_ALL,
			);
		}
		for ($i = 0; $i < $create_count; $i++)
		{
			self::$upload_file_path = self::setup_upload_file();
			list($album_image, $file) = Model_AlbumImage::save_with_relations(self::$album->id, null, null, self::$upload_file_path, 'album', $album_image_values);
		}

		return $album_image;
	}

	private static function get_album_image()
	{
		return Model_AlbumImage::query()->where('album_id', self::$album->id)->get_one();
	}

	private static function get_album_image_comment_count()
	{
		return \Util_Orm::get_count_all('\Album\Model_AlbumImageComment', array('album_image_id' => self::$album_image->id));
	}

	private static function force_save_album($member_id, $values, Model_Album $album = null)
	{
		// album save
		if (!$album) $album = Model_Album::forge();
		$album->name = $values['name'];
		$album->body = $values['body'];
		$album->public_flag = $values['public_flag'];
		$album->member_id = $member_id;
		$album->save();
		if (\Module::loaded('timeline'))
		{
			\Timeline\Site_Model::save_timeline($member_id, $values['public_flag'], 'album', $album->id, $album->updated_at);
		}

		return $album;
	}

	private static function setup_upload_file()
	{
		// prepare upload file.
		$original_file = FBD_BASEPATH.'data/development/test/media/img/sample_01.jpg';
		$upload_file = APPPATH.'tmp/sample.jpg';
		\Util_file::copy($original_file, $upload_file);
		chmod($upload_file, 0777);

		return $upload_file;
	}

	private static function check_no_cache($member_id)
	{
		return is_null(\Site_Develop::get_cache(\Site_Notification::get_unread_count_cache_key('notice', $member_id), \Site_Notification::get_cahce_expire()));
	}
}
