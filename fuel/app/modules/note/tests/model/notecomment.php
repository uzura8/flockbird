<?php
namespace Note;

/**
 * Model_NoteComment class tests
 *
 * @group Modules
 * @group Model
 */
class Test_Model_NoteComment extends \TestCase
{
	private static $note_comment_count = 0;
	private static $note_id;
	private static $note_before;
	private static $timeline_id;
	private static $is_check_timeline_view_cache;
	private static $timeline_view_cache_before;

	public static function setUpBeforeClass()
	{
		$note = \Note\Model_Note::forge();
		$values = array(
			'title' => 'test',
			'body' => 'This is test.',
			'public_flag' => PRJ_PUBLIC_FLAG_ALL,
		);
		$note->save_with_relations(1, $values);
		self::$note_id = $note->id;
		self::$note_before = $note;
	}

	protected function setUp()
	{
		self::$note_comment_count = \Util_Orm::get_count_all('\Note\Model_NoteComment', array('note_id' => self::$note_id));

		self::$is_check_timeline_view_cache = (is_enabled('timeline') && \Config::get('timeline.articles.cache.is_use'));
		if (is_enabled('timeline'))
		{
			$timelines = \Timeline\Model_Timeline::get4foreign_table_and_foreign_ids('note', self::$note_id, \Config::get('timeline.types.note'));
			$timeline = array_shift($timelines);
			self::$timeline_id = $timeline->id;
		}

		// timeline view cache 作成
		if (self::$is_check_timeline_view_cache)
		{
			\Timeline\Site_Util::get_article_main_view(self::$timeline_id);
			self::$timeline_view_cache_before = \Cache::get(\Timeline\Site_Util::get_cache_key(self::$timeline_id), \Config::get('timeline.articles.cache.expir'));
		}
	}

	/**
	* @dataProvider save_comment_provider
	*/
	public function test_save_comment($member_id, $body)
	{
		$note_id = self::$note_id;

		// note_comment save
		\Note\Model_NoteComment::save_comment($note_id, $member_id, $body);
		$note_comment = \Util_Orm::get_last_row('\Note\Model_NoteComment', array('note_id' => $note_id));
		$note = \DB::select()->from('note')->where('id', $note_id)->execute()->current();

		// 件数
		$comment_count = \Util_Orm::get_count_all('\Note\Model_NoteComment', array('note_id' => $note_id));
		$this->assertEquals(self::$note_comment_count + 1, $comment_count);

		// 値
		$this->assertEquals($comment_count, $note['comment_count']);
		$this->assertEquals($note_comment->created_at, $note['sort_datetime']);

		// timeline 関連
		if (is_enabled('timeline'))
		{
			$timeline_id = self::$timeline_id;
			$timeline = \DB::select()->from('timeline')->where('id', $timeline_id)->execute()->current();
			// 値
			$this->assertEquals($comment_count, $timeline['comment_count']);
			$this->assertEquals($note_comment->created_at, $timeline['sort_datetime']);

			$timeline_caches = \DB::select()->from('timeline_cache')->where('timeline_id', $timeline_id)->execute();
			foreach ($timeline_caches as $timeline_cache)
			{
				$this->assertEquals($comment_count, $timeline_cache['comment_count']);
			}

			// timeline_cache が最新レコードになっているか
			$timeline_cache = \Util_Orm::get_last_row('\Timeline\Model_TimelineCache');
			$this->assertEquals($timeline_id, $timeline_cache->timeline_id);

			// timeline view cache check
			if (self::$is_check_timeline_view_cache)
			{
				try
				{
					$timeline_view_cache = \Cache::get(\Timeline\Site_Util::get_cache_key($timeline_id), \Config::get('timeline.articles.cache.expir'));
				}
				catch (\CacheNotFoundException $e)
				{
					$timeline_view_cache = null;
				}
				$this->assertEquals(self::$timeline_view_cache_before, $timeline_view_cache);
			}
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
		$note_id = self::$note_id;

		\Note\Model_NoteComment::save_comment(self::$note_id, 1, 'Test comment1.');
		\Note\Model_NoteComment::save_comment(self::$note_id, 1, 'Test comment2.');
		$note_comment = \Note\Model_NoteComment::save_comment(self::$note_id, 1, 'Test comment3.');

		// note_comment delete
		$note_comment->delete();
		$note = \DB::select()->from('note')->where('id', $note_id)->execute()->current();

		// 件数
		$comment_count = \Util_Orm::get_count_all('\Note\Model_NoteComment', array('note_id' => $note_id));
		$this->assertEquals(self::$note_comment_count + 3 - 1, $comment_count);

		// 値
		$this->assertEquals($comment_count, $note['comment_count']);
		$this->assertEquals(self::$note_before->sort_datetime, $note['sort_datetime']);

		// timeline 関連
		if (is_enabled('timeline'))
		{
			$timeline_id = self::$timeline_id;
			$timeline = \DB::select()->from('timeline')->where('id', $timeline_id)->execute()->current();
			// 値
			$this->assertEquals($comment_count, $timeline['comment_count']);
			$this->assertEquals(self::$note_before->sort_datetime, $timeline['sort_datetime']);

			$timeline_caches = \DB::select()->from('timeline_cache')->where('timeline_id', $timeline_id)->execute();
			foreach ($timeline_caches as $timeline_cache)
			{
				$this->assertEquals($comment_count, $timeline_cache['comment_count']);
			}

			// timeline view cache check
			if (self::$is_check_timeline_view_cache)
			{
				try
				{
					$timeline_view_cache = \Cache::get(\Timeline\Site_Util::get_cache_key($timeline_id), \Config::get('timeline.articles.cache.expir'));
				}
				catch (\CacheNotFoundException $e)
				{
					$timeline_view_cache = null;
				}
				$this->assertEquals(self::$timeline_view_cache_before, $timeline_view_cache);
			}
		}
	}
}
