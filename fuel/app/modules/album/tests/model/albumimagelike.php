<?php
namespace Album;

/**
 * Model_AlbumImageLike class tests
 *
 * @group Modules
 * @group Model
 */
class Test_Model_AlbumImageLike extends \TestCase
{
	private static $member_id = 1;
	private static $album;
	private static $album_image;
	private static $album_image_before;
	private static $album_image_like_count = 0;
	private static $is_check_notice_cache;
	private static $foreign_table = 'album_image';
	private static $type_key = 'like';

	public static function setUpBeforeClass()
	{
		self::$is_check_notice_cache = \Site_Notification::check_is_enabled_cahce('notice', true);

		self::$album_image = \Album\Site_Test::setup_album_image(self::$member_id, null, 1);
		self::$album = Model_Album::find(self::$album_image->album_id);
	}

	protected function setUp()
	{
		self::$album_image_like_count = Model_AlbumImageLike::get_count(array('album_image_id' => self::$album_image->id));
	}

	/**
	* @dataProvider update_like_provider
	*/
	public function test_update_post_like($member_id)
	{
		$album_image_id = self::$album_image->id;
		$album_image_before = self::$album_image;

		// album_image_like save
		\Util_Develop::sleep();
		$is_liked = (bool)Model_AlbumImageLike::change_registered_status4unique_key(array(
			'album_image_id' => $album_image_id,
			'member_id' => $member_id,
		));
		$album_image = self::$album_image;
		$album_image_like = \Util_Orm::get_last_row('\Album\Model_AlbumImageLike', array('album_image_id' => $album_image_id));

		// 件数
		$like_count = \Util_Orm::get_count_all('\Album\Model_AlbumImageLike', array('album_image_id' => $album_image_id));
		$like_count_after = $is_liked ? self::$album_image_like_count + 1 : self::$album_image_like_count - 1;
		$this->assertEquals($like_count_after, $like_count);
		if (!$is_liked) $this->assertNull($album_image_like);

		// 値
		$this->assertEquals($like_count, $album_image->like_count);
		$this->assertEquals($album_image_before->sort_datetime, $album_image->sort_datetime);
	}

	public function update_like_provider()
	{
		$data = array();

		$data[] = array(1);
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
		$is_new = false;
		if ($member_id_to != self::$member_id)
		{
			self::$member_id = $member_id_to;
			self::$album_image = \Album\Site_Test::setup_album_image(self::$member_id, null, 1);
			$is_new = true;
		}
		$album_image_id = self::$album_image->id;
		$foreign_id = self::$album_image->id;
		if ($is_test_after_read) $read_count = \Notice\Site_Util::change_status2read($member_id_to, self::$foreign_table, $album_image_id, self::$type_key);
		$notice_count_all_before = \Notice\Model_Notice::get_count();

		// set cache
		$notice_count_before = \Site_Notification::get_unread_count('notice', $member_id_to);
		if (self::$is_check_notice_cache) $this->assertFalse(\Notice\Site_Test::check_no_cache4notice_unread($member_id_to));// cache が生成されていることを確認


		// like save
		$like_id = Model_AlbumImageLike::change_registered_status4unique_key(array(
			'album_image_id' => $album_image_id,
			'member_id' => $member_id_from
		));
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

		// notice count 取得
		$notice_count = \Site_Notification::get_unread_count('notice', $member_id_to);
		if (self::$is_check_notice_cache) $this->assertFalse(\Notice\Site_Test::check_no_cache4notice_unread($member_id_to));// cache が生成されていることを確認

		// execute test
		$this->assertEquals($notice_count_before + $countup_num, $notice_count);// check count up

		// Model_Notice
		// 件数
		$notice_count_all = \Notice\Model_Notice::get_count();
		$this->assertEquals($notice_count_all_before + $countup_num_all, $notice_count_all);

		// record
		if ($notice = \Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $foreign_id, \Notice\Site_Util::get_notice_type(self::$type_key)))
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
		\Model_MemberConfig::set_value($member_id_add_comment, \Notice\Site_Util::get_member_config_name_for_watch_content('comment'), $mc_watch_liked);
		$is_new = false;
		if ($member_id_to != self::$member_id)
		{
			self::$member_id = $member_id_to;
			self::$album_image = \Album\Site_Test::setup_album_image(self::$member_id, null, 1);
			self::$album = Model_Album::find(self::$album_image->album_id);
			$is_new = true;
		}
		$album_image_id = self::$album_image->id;
		$foreign_id = $album_image_id;
		$watch_count_all_before = \Notice\Model_MemberWatchContent::get_count();
		// change status unread to read
		$read_count = \Notice\Site_Util::change_status2read($member_id_from, self::$foreign_table, $album_image_id, self::$type_key);

		// like save
		$like_id = Model_AlbumImageLike::change_registered_status4unique_key(array(
			'album_image_id' => $album_image_id,
			'member_id' => $member_id_from
		));

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
		// count check
		$watch_count_all = \Notice\Model_MemberWatchContent::get_count();
		$watch_count_all_countup_num = $is_countup_watch_exp ? 1 : 0;
		$this->assertEquals($watch_count_all_before + $watch_count_all_countup_num, $watch_count_all);


		// set cache
		$notice_count_before = \Site_Notification::get_unread_count('notice', $member_id_from);

		// album_image_comment save
		$album_image_comment_added = \Site_Test::save_comment('album_image', $album_image_id, $member_id_add_comment);

		// get notice count
		$notice_count = \Site_Notification::get_unread_count('notice', $member_id_from);

		// execute test
		$this->assertEquals($notice_count_before + $notice_countup_num_exp, $notice_count);// check count up
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
		self::$album_image = \Album\Site_Test::setup_album_image(self::$member_id, null, 1);
		self::$album = Model_Album::find(self::$album_image->album_id);
		$album_image_id = self::$album_image->id;
		$foreign_id = self::$album_image->id;
		$notice_count_all_before = \Notice\Model_Notice::get_count();
		$notice_status_count_all_before = \Notice\Model_NoticeStatus::get_count();
		$notice_member_from_count_all_before = \Notice\Model_NoticeMemberFrom::get_count();
		$member_watch_content_count_all_before = \Notice\Model_MemberWatchContent::get_count();

		// liked from others.
		$like_id = Model_AlbumImageLike::change_registered_status4unique_key(array(
			'album_image_id' => $album_image_id,
			'member_id' =>2,
		));
		$notice = \Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $album_image_id, \Notice\Site_Util::get_notice_type(self::$type_key));
		$this->assertNotNull($notice);

		// check count
		$this->assertEquals($notice_count_all_before + 1, \Notice\Model_Notice::get_count());
		$this->assertEquals($notice_status_count_all_before + 1, \Notice\Model_NoticeStatus::get_count());
		$this->assertEquals($notice_member_from_count_all_before + 1, \Notice\Model_NoticeMemberFrom::get_count());
		$this->assertEquals($member_watch_content_count_all_before + 1, \Notice\Model_MemberWatchContent::get_count());

		// check created related table records.
		$this->assertNotNull(\Notice\Model_NoticeStatus::get4member_id_and_notice_id(self::$member_id, $notice->id));
		$this->assertNotNull(\Notice\Model_NoticeMemberFrom::get4notice_id_and_member_id($notice->id, 2));
		$this->assertNotNull(\Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id(self::$foreign_table, $album_image_id, 2));
		$this->assertNotNull(\Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $album_image_id, \Notice\Site_Util::get_notice_type(self::$type_key)));

		// undo like.
		$like_id = Model_AlbumImageLike::change_registered_status4unique_key(array(
			'album_image_id'   => $album_image_id,
			'member_id' => 2,
		));

		// check count
		$this->assertEquals($notice_count_all_before, \Notice\Model_Notice::get_count());
		$this->assertEquals($notice_status_count_all_before, \Notice\Model_NoticeStatus::get_count());
		$this->assertEquals($notice_member_from_count_all_before, \Notice\Model_NoticeMemberFrom::get_count());
		$this->assertEquals($member_watch_content_count_all_before + 1, \Notice\Model_MemberWatchContent::get_count());// keep watched

		// check deleted related table records.
		$this->assertNull(\Notice\Model_NoticeStatus::get4member_id_and_notice_id(self::$member_id, $notice->id));
		$this->assertNull(\Notice\Model_NoticeMemberFrom::get4notice_id_and_member_id($notice->id, 2));
		$this->assertNotNull(\Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id(self::$foreign_table, $album_image_id, 2));// keep watched
		$this->assertNull(\Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $album_image_id, \Notice\Site_Util::get_notice_type(self::$type_key)));


		// others execute like and coment myself.
		$album_image_like_id = Model_AlbumImageLike::change_registered_status4unique_key(array(
			'album_image_id' => $album_image_id,
			'member_id'   => 2,
		));
		\Site_Test::save_comment('album_image', $album_image_id, 1, 'Test comment1.');
		$notice_like = \Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $album_image_id, \Notice\Site_Util::get_notice_type(self::$type_key));
		$notice_comment = \Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $album_image_id, \Notice\Site_Util::get_notice_type('comment'));
		$this->assertNotNull($notice_like);
		$this->assertNotNull($notice_comment);

		// check count
		$this->assertEquals($notice_count_all_before + 2, \Notice\Model_Notice::get_count());
		$this->assertEquals($notice_status_count_all_before + 2, \Notice\Model_NoticeStatus::get_count());
		$this->assertEquals($notice_member_from_count_all_before + 2, \Notice\Model_NoticeMemberFrom::get_count());
		$this->assertEquals($member_watch_content_count_all_before + 1, \Notice\Model_MemberWatchContent::get_count());

		// check created related table records.
		$this->assertNotNull(\Notice\Model_NoticeStatus::get4member_id_and_notice_id(2, $notice_comment->id));
		$this->assertNotNull(\Notice\Model_NoticeStatus::get4member_id_and_notice_id(self::$member_id, $notice_like->id));
		$this->assertNotNull(\Notice\Model_NoticeMemberFrom::get4notice_id_and_member_id($notice_like->id, 2));
		$this->assertNotNull(\Notice\Model_NoticeMemberFrom::get4notice_id_and_member_id($notice_comment->id, self::$member_id));
		$this->assertNotNull(\Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id(self::$foreign_table, $album_image_id, 2));
		$this->assertNull(\Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id(self::$foreign_table, $album_image_id, self::$member_id));
		$this->assertNotNull(\Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $album_image_id, \Notice\Site_Util::get_notice_type(self::$type_key)));

		// undo like.
		$like_id = Model_AlbumImageLike::change_registered_status4unique_key(array(
			'album_image_id' => $album_image_id,
			'member_id' => 2,
		));

		// check count
		$this->assertEquals($notice_count_all_before + 1, \Notice\Model_Notice::get_count());
		$this->assertEquals($notice_status_count_all_before + 1, \Notice\Model_NoticeStatus::get_count());
		$this->assertEquals($notice_member_from_count_all_before + 1, \Notice\Model_NoticeMemberFrom::get_count());
		$this->assertEquals($member_watch_content_count_all_before + 1, \Notice\Model_MemberWatchContent::get_count());// keep watch

		// check created related table records.
		$this->assertNotNull(\Notice\Model_NoticeStatus::get4member_id_and_notice_id(2, $notice_comment->id));
		$this->assertNull(\Notice\Model_NoticeStatus::get4member_id_and_notice_id(self::$member_id, $notice_like->id));
		$this->assertNull(\Notice\Model_NoticeMemberFrom::get4notice_id_and_member_id($notice_like->id, 2));
		$this->assertNotNull(\Notice\Model_NoticeMemberFrom::get4notice_id_and_member_id($notice_comment->id, self::$member_id));
		$this->assertNotNull(\Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id(self::$foreign_table, $album_image_id, 2));
		$this->assertNull(\Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id(self::$foreign_table, $album_image_id, self::$member_id));
		$this->assertNull(\Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $album_image_id, \Notice\Site_Util::get_notice_type(self::$type_key)));


		// others execute like.
		$album_image_like_id = Model_AlbumImageLike::change_registered_status4unique_key(array(
			'album_image_id' => $album_image_id,
			'member_id'   => 3,
		));
		$notice = \Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $album_image_id, \Notice\Site_Util::get_notice_type(self::$type_key));
		$this->assertNotNull($notice);

		// delete album_image.
		$album_image = Model_AlbumImage::find($album_image_id);
		$album_image->delete();

		// check count
		$this->assertEquals($notice_count_all_before, \Notice\Model_Notice::get_count());
		$this->assertEquals($notice_status_count_all_before, \Notice\Model_NoticeStatus::get_count());
		$this->assertEquals($notice_member_from_count_all_before, \Notice\Model_NoticeMemberFrom::get_count());
		$this->assertEquals($member_watch_content_count_all_before, \Notice\Model_MemberWatchContent::get_count());

		// check deleted related table records.
		$this->assertNull(\Notice\Model_NoticeStatus::get4member_id_and_notice_id(self::$member_id, $notice->id));
		$this->assertNull(\Notice\Model_NoticeMemberFrom::get4notice_id_and_member_id($notice->id, 3));
		$this->assertNull(\Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id(self::$foreign_table, $album_image_id, 3));
		$this->assertNull(\Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $album_image_id, \Notice\Site_Util::get_notice_type(self::$type_key)));


		// album delete test
		// setup
		self::$album_image = \Album\Site_Test::setup_album_image(self::$member_id, null, 1, self::$album->id);
		$foreign_id = self::$album_image->id;
		$album_image_id = self::$album_image->id;

		// others execute like.
		$album_image_like_id = Model_AlbumImageLike::change_registered_status4unique_key(array(
			'album_image_id' => $album_image_id,
			'member_id'   => 4,
		));
		$notice = \Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $album_image_id, \Notice\Site_Util::get_notice_type(self::$type_key));
		$this->assertNotNull($notice);

		// delete album_image
		self::$album->delete();

		// check count
		$this->assertEquals($notice_count_all_before, \Notice\Model_Notice::get_count());
		$this->assertEquals($notice_status_count_all_before, \Notice\Model_NoticeStatus::get_count());
		$this->assertEquals($notice_member_from_count_all_before, \Notice\Model_NoticeMemberFrom::get_count());
		$this->assertEquals($member_watch_content_count_all_before, \Notice\Model_MemberWatchContent::get_count());

		// check deleted related table records.
		$this->assertNull(\Notice\Model_NoticeStatus::get4member_id_and_notice_id(self::$member_id, $notice->id));
		$this->assertNull(\Notice\Model_NoticeMemberFrom::get4notice_id_and_member_id($notice->id, 3));
		$this->assertNull(\Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id(self::$foreign_table, $album_image_id, 3));
		$this->assertNull(\Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $album_image_id, \Notice\Site_Util::get_notice_type(self::$type_key)));
	}
}
