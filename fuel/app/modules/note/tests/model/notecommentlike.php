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
	private static $like_count = 0;
	private static $note_id;
	private static $note_before;
	private static $note_comment_id;
	private static $note_comment_before;
	private static $timeline_id;
	private static $is_check_view_cache;
	private static $view_cache_before;

	public static function setUpBeforeClass()
	{
		$note = \Note\Model_Note::forge();
		$note->save_with_relations(self::$member_id, array(
			'title' => 'test',
			'body' => 'This is test.',
			'public_flag' => PRJ_PUBLIC_FLAG_ALL,
		));
		$timeline = \Util_Orm::get_last_row('\Timeline\Model_Timeline');
		self::$timeline_id = $timeline->id;

		self::$note_id = $note->id;
		$note_comment = self::save_comment(self::$note_id, self::$member_id);
		self::$note_comment_id = $note_comment->id;
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

	public static function save_comment($note_id, $member_id)
	{
		$comment = new Model_NoteComment(array(
			'body' => 'Test for note_comment_like.',
			'note_id' => $note_id,
			'member_id' => $member_id,
		));
		$comment->save();

		return $comment;
	}

	public static function execute_like($note_comment_id, $member_id)
	{
		return (bool)Model_NoteCommentLike::change_registered_status4unique_key(array(
			'note_comment_id' => $note_comment_id,
			'member_id' => $member_id,
		));
	}
}
