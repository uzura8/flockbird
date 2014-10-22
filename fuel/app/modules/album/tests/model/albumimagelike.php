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
	private static $album;
	private static $member_id = 1;
	private static $liked_member_id_before = 0;
	private static $upload_file_path;
	private static $album_image;
	private static $album_image_id;
	private static $album_image_before;
	private static $album_image_like_count = 0;
	private static $is_check_notice_cache;
	private static $foreign_table = 'album_image';
	private static $type_key = 'like';

	public static function setUpBeforeClass()
	{
		self::$album_image = self::set_album_image();
	}

	protected function setUp()
	{
		self::$album_image = self::get_album_image();
		self::$album_image_like_count = self::get_album_image_like_count();
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
		$album_image = self::get_album_image();
		$album_image_like = \Util_Orm::get_last_row('\Album\Model_AlbumImageLike', array('album_image_id' => $album_image_id));

		// 莉ｶ謨ｰ
		$like_count = \Util_Orm::get_count_all('\Album\Model_AlbumImageLike', array('album_image_id' => $album_image_id));
		$like_count_after = $is_liked ? self::$album_image_like_count + 1 : self::$album_image_like_count - 1;
		$this->assertEquals($like_count_after, $like_count);
		if (!$is_liked) $this->assertNull($album_image_like);

		// 蛟､
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
			self::set_album_image();
			$is_new = true;
		}
		$album_image_id = self::$album_image_id;
		$foreign_id = self::$album_image_id;
		if ($is_test_after_read) $read_count = \Notice\Site_Util::change_status2read($member_id_to, self::$foreign_table, $album_image_id, self::$type_key);
		$notice_count_all_before = \Notice\Model_Notice::get_count();

		// set cache
		$notice_count_before = \Notice\Site_Util::get_unread_count($member_id_to);
		if (self::$is_check_notice_cache) $this->assertFalse(self::check_no_cache($member_id_to));// cache が生成されていることを確認

		// like save
		$like_id = Model_AlbumImageLike::change_registered_status4unique_key(array(
			'album_image_id' => self::$album_image_id,
			'member_id' => $member_id_from
		));
		self::$liked_member_id_before = $member_id_from;
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
		$is_new = false;
		if ($member_id_to != self::$member_id)
		{
			self::$member_id = $member_id_to;
			self::set_album_image();
			$is_new = true;
		}
		$album_image_id = self::$album_image_id;
		$foreign_id = self::$album_image_id;
		$watch_count_all_before = \Notice\Model_MemberWatchContent::get_count();
		// 既読処理
		$read_count = \Notice\Site_Util::change_status2read($member_id_from, self::$foreign_table, $album_image_id, self::$type_key);

		// like save
		$like_id = Model_AlbumImageLike::change_registered_status4unique_key(array(
			'album_image_id' => $album_image_id,
			'member_id' => $member_id_from
		));
		self::$liked_member_id_before = $member_id_from;

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
		$notice_count_before = \Notice\Site_Util::get_unread_count($member_id_from);

		// album_image_comment save
		$album_image_comment_added = self::save_comment($member_id_add_comment);

		// notice count を取得
		$notice_count = \Notice\Site_Util::get_unread_count($member_id_from);

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
		self::set_album_image();
		$album_image_id = self::$album_image_id;
		$foreign_id = self::$album_image_id;
		$notice_count_all_before = \Notice\Model_Notice::get_count();
		$notice_status_count_all_before = \Notice\Model_NoticeStatus::get_count();
		$notice_member_from_count_all_before = \Notice\Model_NoticeMemberFrom::get_count();
		$member_watch_content_count_all_before = \Notice\Model_MemberWatchContent::get_count();

		// 他人がイイね
		$like_id = Model_AlbumImageLike::change_registered_status4unique_key(array(
			'album_image_id' => $album_image_id,
			'member_id' =>2,
		));
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

		// イイねを取り消し
		$like_id = Model_AlbumImageLike::change_registered_status4unique_key(array(
			'album_image_id'   => $album_image_id,
			'member_id' => 2,
		));

		// 件数確認
		$this->assertEquals($notice_count_all_before, \Notice\Model_Notice::get_count());
		$this->assertEquals($notice_status_count_all_before, \Notice\Model_NoticeStatus::get_count());
		$this->assertEquals($notice_member_from_count_all_before, \Notice\Model_NoticeMemberFrom::get_count());
		$this->assertEquals($member_watch_content_count_all_before + 1, \Notice\Model_MemberWatchContent::get_count());// watch は解除されない

		// 関連テーブルのレコードが削除されていることを確認
		$this->assertNull(\Notice\Model_NoticeStatus::get4member_id_and_notice_id(self::$member_id, $notice->id));
		$this->assertNull(\Notice\Model_NoticeMemberFrom::get4notice_id_and_member_id($notice->id, 2));
		$this->assertNotNull(\Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id(self::$foreign_table, $album_image_id, 2));// watch は解除されない
		$this->assertNull(\Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $album_image_id, \Notice\Site_Util::get_notice_type(self::$type_key)));


		// 他人のイイね＋自分がコメント
		$album_image_like_id = Model_AlbumImageLike::change_registered_status4unique_key(array(
			'album_image_id' => $album_image_id,
			'member_id'   => 2,
		));
		self::save_comment(1, 'Test comment1.');
		$notice_like = \Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $album_image_id, \Notice\Site_Util::get_notice_type(self::$type_key));
		$notice_comment = \Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $album_image_id, \Notice\Site_Util::get_notice_type('comment'));
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
		$this->assertNotNull(\Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id(self::$foreign_table, $album_image_id, 2));
		$this->assertNull(\Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id(self::$foreign_table, $album_image_id, self::$member_id));
		$this->assertNotNull(\Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $album_image_id, \Notice\Site_Util::get_notice_type(self::$type_key)));

		// イイねを取り消し
		$like_id = Model_AlbumImageLike::change_registered_status4unique_key(array(
			'album_image_id' => $album_image_id,
			'member_id' => 2,
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
		$this->assertNotNull(\Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id(self::$foreign_table, $album_image_id, 2));
		$this->assertNull(\Notice\Model_MemberWatchContent::get_one4foreign_data_and_member_id(self::$foreign_table, $album_image_id, self::$member_id));
		$this->assertNull(\Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $album_image_id, \Notice\Site_Util::get_notice_type(self::$type_key)));


		// 他人がイイね
		$album_image_like_id = Model_AlbumImageLike::change_registered_status4unique_key(array(
			'album_image_id' => $album_image_id,
			'member_id'   => 3,
		));
		$notice = \Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $album_image_id, \Notice\Site_Util::get_notice_type(self::$type_key));
		$this->assertNotNull($notice);

		// album_image 削除
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

		// 他人がイイね
		$album_image_like_id = Model_AlbumImageLike::change_registered_status4unique_key(array(
			'album_image_id' => $album_image_id,
			'member_id'   => 4,
		));
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

	private static function set_album_image($album_image_values = null, $create_count = 1, $album_id = null)
	{
		$public_flag = isset($values['public_flag']) ? $values['public_flag'] : PRJ_PUBLIC_FLAG_ALL;
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
				'public_flag' => PRJ_PUBLIC_FLAG_ALL,
			);
		}
		for ($i = 0; $i < $create_count; $i++)
		{
			self::$upload_file_path = self::setup_upload_file();
			list($album_image, $file) = Model_AlbumImage::save_with_relations(self::$album->id, null, null, self::$upload_file_path, 'album', $album_image_values);
		}
		self::$album_image_id = $album_image->id;

		return $album_image;
	}

	private static function get_album_image()
	{
		return Model_AlbumImage::query()->where('album_id', self::$album->id)->get_one();
	}

	private static function get_album_image_like_count()
	{
		return \Util_Orm::get_count_all('\Album\Model_AlbumImageLike', array('album_image_id' => self::$album_image->id));
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
		$original_file = PRJ_BASEPATH.'data/development/test/media/img/sample_01.jpg';
		$upload_file = APPPATH.'tmp/sample.jpg';
		\Util_file::copy($original_file, $upload_file);
		chmod($upload_file, 0777);

		return $upload_file;
	}

	private static function save_comment($member_id, $body = null)
	{
		if (is_null($body)) $body = 'This is test comment.';
		$comment = Model_AlbumImageComment::forge(array(
			'body' => $body,
			'album_image_id' => self::$album_image_id,
			'member_id' => $member_id,
		));
		$comment->save();

		return $comment;
	}
}
