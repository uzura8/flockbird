<?php
namespace Note;

/**
 * Model_Note class tests
 *
 * @group Modules
 * @group Model
 */
class Test_Model_Note extends \TestCase
{
	private static $note;
	private static $note_count = 0;
	private static $timeline_count = 0;
	private static $timeline_cache_count = 0;
	private static $self_member_id = 1;
	private static $is_check_timeline_view_cache;

	public static function setUpBeforeClass()
	{
		self::$is_check_timeline_view_cache = (is_enabled('timeline') && \Config::get('timeline.articles.cache.is_use'));
	}

	protected function setUp()
	{
		self::$note_count = \Util_Orm::get_count_all('\Note\Model_Note');
		if (is_enabled('timeline'))
		{
			self::$timeline_count = \Util_Orm::get_count_all('\Timeline\Model_Timeline');
			self::$timeline_cache_count = \Util_Orm::get_count_all('\Timeline\Model_TimelineCache');
		}
	}

	/**
	* @dataProvider save_provider
	*/
	public function test_save($is_update_test, $values)
	{
		$note = \Note\Model_Note::forge();
		$before = array();
		if ($is_update_test)
		{
			$note = $this->get_last_row();
			$before = array(
				'title' => $note->title,
				'body' => $note->body,
				'public_flag' => $note->public_flag,
				'is_published' => $note->is_published,
				'sort_datetime' => $note->sort_datetime,
				'comment_count' => $note->comment_count,
				'like_count' => $note->like_count,
			);
		}

		// timeline view cache 作成
		if (self::$is_check_timeline_view_cache)
		{
			$timeline_view_cache_before = \Timeline\Site_Util::make_view_cache4foreign_table_and_foreign_id('note', $note->id, \Config::get('timeline.types.note'));
		}

		// note save
		list($is_changed, $is_published, $moved_files) = $note->save_with_relations(self::$self_member_id, $values);

		// 返り値の確認
		$this->assertEmpty($moved_files);
		if (!empty($values['is_draft'])) $this->assertEquals(false, $is_published);

		// 新規投稿
		if (!$is_update_test)
		{
			$is_draft = !empty($values['is_draft']);
			// 件数
			$this->assertEquals(self::$note_count + 1, \Util_Orm::get_count_all('\Note\Model_Note'));
			if (is_enabled('timeline'))
			{
				if ($is_draft)
				{
					$this->assertEquals(self::$timeline_count, \Util_Orm::get_count_all('\Timeline\Model_Timeline'));
					$this->assertEquals(self::$timeline_cache_count, \Util_Orm::get_count_all('\Timeline\Model_TimelineCache'));
				}
				else
				{
					$this->assertEquals(self::$timeline_count + 1, \Util_Orm::get_count_all('\Timeline\Model_Timeline'));
					$this->assertEquals(self::$timeline_cache_count + 2, \Util_Orm::get_count_all('\Timeline\Model_TimelineCache'));
				}
			}

			// 値
			$this->assertEquals(0, $note->comment_count);
			$this->assertEquals($note->created_at, $note->sort_datetime);
		}
		// 編集
		else
		{
			// 件数
			$this->assertEquals(self::$note_count, \Util_Orm::get_count_all('\Note\Model_Note'));
			if (is_enabled('timeline'))
			{
				if ($is_published)
				{
					$this->assertEquals(self::$timeline_count + 1, \Util_Orm::get_count_all('\Timeline\Model_Timeline'));
					$this->assertEquals(self::$timeline_cache_count + 2, \Util_Orm::get_count_all('\Timeline\Model_TimelineCache'));
				}
				else
				{
					$this->assertEquals(self::$timeline_count, \Util_Orm::get_count_all('\Timeline\Model_Timeline'));
					$this->assertEquals(self::$timeline_cache_count, \Util_Orm::get_count_all('\Timeline\Model_TimelineCache'));
				}
			}

			// 値
			$this->assertEquals($before['comment_count'], $note->comment_count);
			if ($is_changed && self::check_sort_datetime_change($note, $before))
			{
				$this->assertEquals($note->updated_at, $note->sort_datetime);
			}
		}

		// timeline 関連
		if (is_enabled('timeline'))
		{
			// 公開済み日記
			if ($note->is_published)
			{
				$timelines = \Timeline\Model_Timeline::get4foreign_table_and_foreign_ids('note', $note->id, \Config::get('timeline.types.note'));
				$this->assertCount(1, $timelines);

				$timeline = array_shift($timelines);
				$this->assertEquals($note->member_id, $timeline->member_id);
				$this->assertEquals($note->public_flag, $timeline->public_flag);
				$this->assertEquals($note->sort_datetime, $timeline->sort_datetime);
				$this->assertEquals($note->comment_count, $timeline->comment_count);
				if ($is_published)
				{
					$this->assertEquals($note->updated_at, $timeline->created_at);
				}
				else
				{
					$this->assertEquals($note->created_at, $timeline->created_at);
				}

				// timeline view cache check
				if (self::$is_check_timeline_view_cache)
				{
					$this->assertEmpty(\Timeline\Site_Util::get_view_cache($timeline->id));
				}
			}
			// 下書き日記
			else
			{
				$timelines = \Timeline\Model_Timeline::get4foreign_table_and_foreign_ids('note', $note->id, \Config::get('timeline.types.note'));
				$this->assertEmpty($timelines);
			}
		}
	}

	public function save_provider()
	{
		$data = array();
		// #0: 通常日記新規投稿
		$data[] = array(0, array(
			'title' => 'test',
			'body' => '#0: 通常日記新規投稿',
			'is_draft' => 0,
			'public_flag' => PRJ_PUBLIC_FLAG_ALL,
		));
		// #1: 通常日記編集(変更なし)
		$data[] = array(1, array(
			'title' => 'test',
			'body' => '#1: 通常日記編集(変更なし)',
			'public_flag' => PRJ_PUBLIC_FLAG_ALL,
			'is_draft' => 0,
		));
		// #2: 通常日記編集
		$data[] = array(1, array(
			'title' => '#2: 通常日記編集',
			'body' => 'This is test1.',
			'public_flag' => PRJ_PUBLIC_FLAG_MEMBER,
		));

		// #3: 日記下書き
		$data[] = array(0, array(
			'title' => 'test2',
			'body' => '#3: 日記下書き',
			'public_flag' => PRJ_PUBLIC_FLAG_MEMBER,
			'is_draft' => 1,
		));
		// #4: 日記下書き編集
		$data[] = array(1, array(
			'title' => 'test2edit',
			'body' => '#4: 日記下書き編集',
			'public_flag' => PRJ_PUBLIC_FLAG_ALL,
			'is_draft' => 1,
		));
		// #5: 下書き日記公開
		$data[] = array(1, array(
			'is_draft' => 0,
		));

		return $data;
	}

	public function test_delete_with_relations()
	{
		$note = $this->get_last_row();
		$is_draft = !$note->is_published;
		$note_id = $note->id;
		$note->delete_with_relations();

		// 件数
		// note
		$this->assertEquals(self::$note_count - 1, Model_Note::get_count());

		// note_album_image
		$note_album_images = \Note\Model_NoteAlbumImage::query()->where('note_id', $note_id)->get();
		$this->assertEmpty($note_album_images);

		// timeline
		if (is_enabled('timeline'))
		{
			if ($is_draft)
			{
				$this->assertEquals(self::$timeline_count, \Util_Orm::get_count_all('\Timeline\Model_Timeline'));
				$this->assertEquals(self::$timeline_cache_count, \Util_Orm::get_count_all('\Timeline\Model_TimelineCache'));
			}
			else
			{
				$this->assertEquals(self::$timeline_count - 1, \Util_Orm::get_count_all('\Timeline\Model_Timeline'));
				$this->assertEquals(self::$timeline_cache_count - 2, \Util_Orm::get_count_all('\Timeline\Model_TimelineCache'));

				$timelines = \Timeline\Model_Timeline::get4foreign_table_and_foreign_ids('note', $note->id, \Config::get('timeline.types.note'));
				$this->assertEmpty($timelines);
			}
		}
	}

	//public function delete_with_relations_provider()
	//{
	//	$data = array();

	//	// 通常日記新規投稿
	//	$note = \Note\Model_Note::forge();
	//	$values = array(
	//		'title' => 'test',
	//		'body' => 'This is test.',
	//		'public_flag' => PRJ_PUBLIC_FLAG_ALL,
	//	);
	//	$note->save_with_relations(1, $values);
	//	$data[] = array($note);

	//	$note = \Note\Model_Note::forge();
	//	$values = array(
	//		'title' => 'test',
	//		'body' => 'This is test.',
	//		'public_flag' => PRJ_PUBLIC_FLAG_ALL,
	//		'is_draft' => 1,
	//	);
	//	$note->save_with_relations(1, $values);
	//	$data[] = array($note);

	//	return $data;
	//}

	/**
	* @dataProvider update_public_flag_with_relations_provider
	*/
	public function test_update_public_flag_with_relations($public_flag)
	{
		$note = $this->get_last_row();
		$before = array(
			'public_flag' => $note->public_flag,
			'updated_at' => $note->updated_at,
			'sort_datetime' => $note->sort_datetime,
		);
		$note->update_public_flag_with_relations($public_flag);

		// 件数
		$this->assertEquals(self::$note_count, \Util_Orm::get_count_all('\Note\Model_Note'));

		// 値
		$this->assertEquals($public_flag, $note->public_flag);

		// date
		// 変更なし
		if ($note->public_flag == $before['public_flag'])
		{
			$this->assertEquals($before['updated_at'], $note->updated_at);
			$this->assertEquals($before['sort_datetime'], $note->sort_datetime);
		}
		// 変更あり
		else
		{
			$this->assertTrue(\Util_Date::check_is_future($note->updated_at, $note->created_at));
			$this->assertEquals($note->updated_at, $note->sort_datetime);
		}

		// timeline
		if (is_enabled('timeline'))
		{
			$timelines = \Timeline\Model_Timeline::get4foreign_table_and_foreign_ids('note', $note->id, \Config::get('timeline.types.note'));
			if ($note->is_published)
			{
				$this->assertCount(1, $timelines);
				$timeline = array_shift($timelines);

				$this->assertEquals($note->public_flag, $timeline->public_flag);
				$this->assertEquals($note->sort_datetime, $timeline->sort_datetime);
			}
			else
			{
				$this->assertCount(0, $timelines);
			}
		}
	}

	public function update_public_flag_with_relations_provider()
	{
		$data = array();

		$note = \Note\Model_Note::forge();
		$values = array(
			'title' => 'test',
			'body' => 'This is test.',
			'public_flag' => PRJ_PUBLIC_FLAG_ALL,
		);
		$note->save_with_relations(1, $values);
		$data[] = array(PRJ_PUBLIC_FLAG_MEMBER);

		// 変更なし
		$data[] = array(PRJ_PUBLIC_FLAG_MEMBER);

		return $data;
	}

	private static function check_sort_datetime_change(Model_Note $note, $before)
	{
		$check_properties = array(
			'title',
			'body',
			'public_flag',
			'is_published',
		);
		foreach ($check_properties as $property)
		{
			if ($note->{$property} != $before[$property]) return true;
		}

		return false;
	}

	private function get_last_row()
	{
		return \Util_Orm::get_last_row('\Note\Model_Note');
	}

	private function set_count()
	{
		self::$note_count = \Util_Orm::get_count_all('\Note\Model_Note');
		if (is_enabled('timeline'))
		{
			self::$timeline_count = \Util_Orm::get_count_all('\Timeline\Model_Timeline');
			self::$timeline_cache_count = \Util_Orm::get_count_all('\Timeline\Model_TimelineCache');
		}
	}
}
