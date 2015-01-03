<?php
namespace Note;

/**
 * Model_NoteCommentLike class tests
 *
 * @group Modules
 * @group Model
 */
class Test_Model_NoteCommentLike extends \TestCase
{
	private static $member_id = 1;
	private static $commented_member_id_before;
	private static $like_count = 0;
	private static $note_id;
	private static $note_before;
	private static $note_comment_id;
	private static $note_comment_before;
	private static $timeline_id;
	private static $is_check_view_cache;
	private static $view_cache_before;
	private static $foreign_table = 'note_comment';
	private static $type_key = 'like';
	private static $is_check_notice_cache;

	public static function setUpBeforeClass()
	{
		$note = self::set_note();
		$timeline = \Util_Orm::get_last_row('\Timeline\Model_Timeline');
		self::$timeline_id = $timeline->id;

		self::$note_id = $note->id;
		$note_comment = self::save_comment(self::$note_id, self::$member_id);
		self::$note_comment_id = $note_comment->id;
		\Model_MemberConfig::set_value(self::$member_id, \Notice\Form_MemberConfig::get_name('comment'), 0);
	}

	protected function setUp()
	{
		self::$note_before = Model_Note::find(self::$note_id);
		self::$note_comment_before = Model_NoteComment::find(self::$note_comment_id);
		self::$like_count = \Util_Orm::get_count_all('\Note\Model_NoteCommentLike', array('note_comment_id' => self::$note_comment_id));
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
		$note_id = self::$note_id;
		$note_comment_id = self::$note_comment_id;

		// note_like save
		\Util_Develop::sleep();
		$is_liked = self::execute_like($note_comment_id, $member_id);

		$note = Model_Note::find($note_id);
		$note_comment = Model_NoteComment::find($note_comment_id);
		$note_comment_like = \Util_Orm::get_last_row('\Note\Model_NoteCommentLike', array('note_comment_id' => $note_comment_id));

		// 件数
		$like_count = \Util_Orm::get_count_all('\Note\Model_NoteCommentLike', array('note_comment_id' => $note_comment_id));
		$like_count_expect = $is_liked ? self::$like_count + 1 : self::$like_count - 1;
		$this->assertEquals($like_count_expect, $like_count);

		// 値
		$this->assertEquals($like_count, $note_comment->like_count);
		if (!$is_liked)
		{
			$this->assertNull($note_comment_like);
		}

		// timeline view cache check
		if (self::$is_check_view_cache)
		{
			$this->assertEquals(self::$view_cache_before, \Note\Site_Util::get_view_cache(self::$timeline_id));
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
			$note = self::set_note();
			$note_comment = self::save_comment(self::$note_id, $member_id_to);
			self::$note_comment_id = $note_comment->id;
			$is_new = true;
		}
		self::$commented_member_id_before = $member_id_to;
		$note_id = self::$note_id;
		$foreign_id = self::$note_comment_id;
		if ($is_test_after_read) $read_count = \Notice\Site_Util::change_status2read($member_id_to, self::$foreign_table, $foreign_id, self::$type_key);
		$notice_count_all_before = \Notice\Model_Notice::get_count();

		// set cache
		$notice_count_before = \Notice\Site_Util::get_unread_count($member_id_to);
		if (self::$is_check_notice_cache) $this->assertFalse(\Notice\Site_Test::check_no_cache4notice_unread($member_id_to));// cache が生成されていることを確認

		// like save
		$is_liked = (bool)Model_NoteCommentLike::change_registered_status4unique_key(array(
			'note_comment_id' => self::$note_comment_id,
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
		$notice_count = \Notice\Site_Util::get_unread_count($member_id_to);
		if (self::$is_check_notice_cache) $this->assertFalse(\Notice\Site_Test::check_no_cache4notice_unread($member_id_to));// cache が生成されていることを確認

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

	public function test_get_members()
	{
		$note_comment = self::save_comment(self::$note_id, self::$member_id);
		$note_comment_id = $note_comment->id;

		// like 実行
		self::execute_like($note_comment_id, 3);
		self::execute_like($note_comment_id, 4);
		self::execute_like($note_comment_id, 5);
		self::execute_like($note_comment_id, 3);

		// 件数
		$like_count = \Util_Orm::get_count_all('\Note\Model_NoteCommentLike', array('note_comment_id' => $note_comment_id));
		$this->assertEquals(2, $like_count);
		
		$note_comment_likes = Model_NoteCommentLike::query()->where('note_comment_id', $note_comment_id)->get();
		$this->assertCount(2, $note_comment_likes);
		foreach ($note_comment_likes as $note_comment_like)
		{
			$this->assertContains($note_comment_like->member_id, array(4, 5));
		}
	}

	public function test_delete_parent()
	{
		$note_comment_id = self::$note_comment_id;
		$note_comment = Model_NoteComment::find($note_comment_id);
		if (!\Util_Orm::get_count_all('\Note\Model_NoteCommentLike', array('note_comment_id' => $note_comment_id)))
		{
			self::execute_like($note_comment_id, 6);
			self::execute_like($note_comment_id, 7);
		}
		$note_comment->delete();

		$like_count = \Util_Orm::get_count_all('\Note\Model_NoteCommentLike', array('note_comment_id' => $note_comment_id));
		$this->assertEquals(0, $like_count);
	}

	public function test_delete_notice()
	{
		// 事前準備
		$config_type_key = 'like';
		\Model_MemberConfig::set_value(1, \Notice\Form_MemberConfig::get_name('comment'), 1);
		\Model_MemberConfig::set_value(2, \Notice\Form_MemberConfig::get_name($config_type_key), 1);
		\Model_MemberConfig::set_value(2, \Notice\Site_Util::get_member_config_name_for_watch_content('comment'), 0);
		self::$member_id = 1;
		$note = self::set_note();
		$note_comment = self::save_comment(self::$note_id, 2);
		self::$note_comment_id = $note_comment->id;

		$note_id = self::$note_id;
		$foreign_id = self::$note_comment_id;
		$notice_count_all_before = \Notice\Model_Notice::get_count();
		$notice_status_count_all_before = \Notice\Model_NoticeStatus::get_count();
		$notice_member_from_count_all_before = \Notice\Model_NoticeMemberFrom::get_count();

		// イイね実行
		$is_liked = (bool)Model_NoteCommentLike::change_registered_status4unique_key(array(
			'note_comment_id' => self::$note_comment_id,
			'member_id' => 4,
		));
		$notice = \Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $foreign_id, \Notice\Site_Util::get_notice_type(self::$type_key));
		$this->assertNotNull($notice);

		// 件数確認
		$this->assertEquals($notice_count_all_before + 1, \Notice\Model_Notice::get_count());
		$this->assertEquals($notice_status_count_all_before + 1, \Notice\Model_NoticeStatus::get_count());
		$this->assertEquals($notice_member_from_count_all_before + 1, \Notice\Model_NoticeMemberFrom::get_count());

		// 関連テーブルのレコードが作成されていることを確認
		$this->assertNotNull(\Notice\Model_NoticeStatus::get4member_id_and_notice_id(2, $notice->id));
		$this->assertNotNull(\Notice\Model_NoticeMemberFrom::get4notice_id_and_member_id($notice->id, 4));
		$this->assertNotNull(\Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $foreign_id, \Notice\Site_Util::get_notice_type(self::$type_key)));

		// イイねを取り消し
		$is_liked = (bool)Model_NoteCommentLike::change_registered_status4unique_key(array(
			'note_comment_id' => self::$note_comment_id,
			'member_id' => 4,
		));

		// 件数確認
		$this->assertEquals($notice_count_all_before, \Notice\Model_Notice::get_count());
		$this->assertEquals($notice_status_count_all_before, \Notice\Model_NoticeStatus::get_count());
		$this->assertEquals($notice_member_from_count_all_before, \Notice\Model_NoticeMemberFrom::get_count());

		// 関連テーブルのレコードが削除されていることを確認
		$this->assertNull(\Notice\Model_NoticeStatus::get4member_id_and_notice_id(2, $notice->id));
		$this->assertNull(\Notice\Model_NoticeMemberFrom::get4notice_id_and_member_id($notice->id, 4));
		$this->assertNull(\Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $foreign_id, \Notice\Site_Util::get_notice_type(self::$type_key)));

		// イイね実行
		$is_liked = (bool)Model_NoteCommentLike::change_registered_status4unique_key(array(
			'note_comment_id' => self::$note_comment_id,
			'member_id' => 4,
		));
		$notice = \Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $foreign_id, \Notice\Site_Util::get_notice_type(self::$type_key));
		$this->assertNotNull($notice);

		// note_comment 削除
		$note_comment->delete();
		// 件数確認
		$this->assertEquals($notice_count_all_before, \Notice\Model_Notice::get_count());
		$this->assertEquals($notice_status_count_all_before, \Notice\Model_NoticeStatus::get_count());
		$this->assertEquals($notice_member_from_count_all_before, \Notice\Model_NoticeMemberFrom::get_count());

		// 再度 comment 投稿
		$note_comment = self::save_comment(self::$note_id, 2);
		self::$note_comment_id = $note_comment->id;
		// イイね実行
		$is_liked = (bool)Model_NoteCommentLike::change_registered_status4unique_key(array(
			'note_comment_id' => self::$note_comment_id,
			'member_id' => 4,
		));
		// 件数確認
		$this->assertEquals($notice_count_all_before + 1, \Notice\Model_Notice::get_count());
		$this->assertEquals($notice_status_count_all_before + 1, \Notice\Model_NoticeStatus::get_count());
		$this->assertEquals($notice_member_from_count_all_before + 1, \Notice\Model_NoticeMemberFrom::get_count());

		// note 削除
		$note->delete_with_relations();

		// 件数確認
		$this->assertEquals($notice_count_all_before, \Notice\Model_Notice::get_count());
		$this->assertEquals($notice_status_count_all_before, \Notice\Model_NoticeStatus::get_count());
		$this->assertEquals($notice_member_from_count_all_before, \Notice\Model_NoticeMemberFrom::get_count());

		// 関連テーブルのレコードが削除されていることを確認
		$this->assertNull(\Notice\Model_NoticeStatus::get4member_id_and_notice_id(2, $notice->id));
		$this->assertNull(\Notice\Model_NoticeMemberFrom::get4notice_id_and_member_id($notice->id, 4));
		$this->assertNull(\Notice\Model_Notice::get_last4foreign_data(self::$foreign_table, $foreign_id, \Notice\Site_Util::get_notice_type(self::$type_key)));
	}

	private static function set_note()
	{
		$note = \Note\Model_Note::forge();
		$values = array(
			'title' => 'test',
			'body' => 'This is test.',
			'public_flag' => PRJ_PUBLIC_FLAG_ALL,
		);
		$note->save_with_relations(self::$member_id, $values);
		self::$note_id = $note->id;

		return $note;
	}

	private static function save_comment($note_id, $member_id)
	{
		$comment = new Model_NoteComment(array(
			'body' => 'Test for note_comment_like.',
			'note_id' => $note_id,
			'member_id' => $member_id,
		));
		$comment->save();

		return $comment;
	}

	private static function execute_like($note_comment_id, $member_id)
	{
		return (bool)Model_NoteCommentLike::change_registered_status4unique_key(array(
			'note_comment_id' => $note_comment_id,
			'member_id' => $member_id,
		));
	}
}
