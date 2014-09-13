<?php
namespace Album;

/**
 * Model_Album class tests
 *
 * @group Modules
 * @group Model
 */
class Test_Model_Album extends \TestCase
{
	private static $album_count = 0;
	private static $timeline_count = 0;
	private static $timeline_cache_count = 0;
	private static $upload_file = '';

	public static function setUpBeforeClass()
	{
	}

	protected function setUp()
	{
		self::$album_count = \Util_Orm::get_count_all('\Album\Model_Album');
		if (is_enabled('timeline'))
		{
			self::$timeline_count = \Util_Orm::get_count_all('\Timeline\Model_Timeline');
			self::$timeline_cache_count = \Util_Orm::get_count_all('\Timeline\Model_TimelineCache');
		}
	}

	/**
	* @dataProvider save_provider
	*/
	public function test_save(Model_Album $album, $self_member_id, $values)
	{
		self::setup_upload_file();

		$is_new = $album->is_new();
		$is_check_timeline_view_cache = (!$is_new && is_enabled('timeline') && \Config::get('timeline.articles.cache.is_use'));
		$before = array(
			'name' => $album->name,
			'body' => $album->body,
			'public_flag' => $album->public_flag,
		);

		// timeline view cache 作成
		if ($is_check_timeline_view_cache)
		{
			$timelines = \Timeline\Model_Timeline::get4foreign_table_and_foreign_ids('album', $album->id, \Config::get('timeline.types.album'));
			$timeline = array_shift($timelines);
			\Timeline\Site_Util::get_article_main_view($timeline->id);
			$timeline_view_cache_before = \Cache::get(\Timeline\Site_Util::get_cache_key($timeline->id), \Config::get('timeline.articles.cache.expir'));
		}

		// album save
		list($album, $member) = self::save_album($self_member_id, $values);
		list($album_image, $file) = Model_AlbumImage::save_with_relations($album->id, $member, $values['public_flag'], self::$upload_file, 'album');

		// 返り値の確認
		$this->assertNotEmpty($album);
		$this->assertNotEmpty($album_image);
		$this->assertNotEmpty($file);

		// 新規投稿
		if ($is_new)
		{
			// 件数
			$this->assertEquals(self::$album_count + 1, \Util_Orm::get_count_all('\Album\Model_Album'));
			if (is_enabled('timeline'))
			{
				$this->assertEquals(self::$timeline_count + 1, \Util_Orm::get_count_all('\Timeline\Model_Timeline'));
				$this->assertEquals(self::$timeline_cache_count + 2, \Util_Orm::get_count_all('\Timeline\Model_TimelineCache'));
			}
		}
		// 編集
		else
		{
			// 件数
			$this->assertEquals(self::$album_count, \Util_Orm::get_count_all('\Album\Model_Album'));
			if (is_enabled('timeline'))
			{
				$this->assertEquals(self::$timeline_count, \Util_Orm::get_count_all('\Timeline\Model_Timeline'));
				$this->assertEquals(self::$timeline_cache_count, \Util_Orm::get_count_all('\Timeline\Model_TimelineCache'));
			}

			// 値
			$this->assertEquals($before['comment_count'], $album->comment_count);
		}

		// timeline 関連
		if (is_enabled('timeline'))
		{
			$timelines = \Timeline\Model_Timeline::get4foreign_table_and_foreign_ids('album', $album->id, \Config::get('timeline.types.album'));
			$this->assertCount(1, $timelines);

			$timeline = array_shift($timelines);
			$this->assertEquals($album->member_id, $timeline->member_id);
			$this->assertEquals($album->public_flag, $timeline->public_flag);
			$this->assertEquals($album->created_at, $timeline->created_at);

			// timeline view cache check
			if ($is_check_timeline_view_cache)
			{
				try
				{
					$timeline_view_cache = \Cache::get(\Timeline\Site_Util::get_cache_key($timeline->id), \Config::get('timeline.articles.cache.expir'));
				}
				catch (\CacheNotFoundException $e)
				{
					$timeline_view_cache = null;
				}
				if (\Util_Orm::check_is_changed($album, array('name', 'body', 'public_flag'), $before))
				{
					$this->assertEmpty($timeline_view_cache);
				}
				else
				{
					$this->assertEquals($timeline_view_cache_before, $timeline_view_cache);
				}
			}
		}
	}

	public function save_provider()
	{
		$data = array();

		// アルバム新規投稿
		$album = \Album\Model_Album::forge();
		$data[] = array($album, 1, array(
			'name' => 'test',
			'body' => 'This is test.',
			'public_flag' => PRJ_PUBLIC_FLAG_ALL,
		));
		$album = \Util_Orm::get_last_row('\Album\Model_Album');
		// アルバム編集(変更なし)
		$album = \Album\Model_Album::forge();
		$data[] = array($album, 1, array(
			'name' => 'test_edit',
			'body' => 'This is test edit.',
			'public_flag' => PRJ_PUBLIC_FLAG_ALL,
		));
		// アルバム編集
		$album = \Album\Model_Album::forge();
		$data[] = array($album, 1, array(
			'name' => 'test1',
			'body' => 'This is test1.',
			'public_flag' => PRJ_PUBLIC_FLAG_MEMBER,
		));

		return $data;
	}

	/**
	* @dataProvider delete_with_relations_provider
	*/
	public function test_delete_with_relations(Model_Album $album)
	{
		$album_id = $album->id;
		$deleted_files = Model_Album::delete_relations($album);

		// 件数
		// album
		$this->assertEquals(self::$album_count - 1, \Util_Orm::get_count_all('\Album\Model_Album'));

		// album_image
		$album_images = Model_AlbumImage::get4album_id($album_id);
		$this->assertEmpty($album_images);

		// timeline
		if (is_enabled('timeline'))
		{
			$this->assertEquals(self::$timeline_count - 1, \Util_Orm::get_count_all('\Timeline\Model_Timeline'));
			$this->assertEquals(self::$timeline_cache_count - 2, \Util_Orm::get_count_all('\Timeline\Model_TimelineCache'));

			$timelines = \Timeline\Model_Timeline::get4foreign_table_and_foreign_ids('album', $album_id, \Config::get('timeline.types.album'));
			$this->assertEmpty($timelines);
		}
	}

	public function delete_with_relations_provider()
	{
		$data = array();
		self::setup_upload_file();
		$values = array(
			'name' => 'test',
			'body' => 'This is test.',
			'public_flag' => PRJ_PUBLIC_FLAG_ALL,
		);
		list($album, $member) = self::save_album(1, $values);
		list($album_image, $file) = Model_AlbumImage::save_with_relations($album->id, $member, $values['public_flag'], APPPATH.'tmp/sample.jpg', 'album');
		$data[] = array($album);

		return $data;
	}

	private static function save_album($member_id, $values)
	{
		// album save
		$album = Model_Album::forge();
		$album->name = $values['name'];
		$album->body = $values['body'];
		$album->public_flag = $values['public_flag'];
		$album->member_id = $member_id;
		$album->save();
		// album_image save
		$member = \Model_Member::check_authority($member_id);

		return array($album, $member);
	}

	private static function setup_upload_file()
	{
		// prepare upload file.
		$original_file = PRJ_BASEPATH.'data/development/test/media/img/sample_01.jpg';
		self::$upload_file = APPPATH.'tmp/sample.jpg';
		\Util_file::copy($original_file, self::$upload_file);
		chmod(self::$upload_file, 0777);
	}
}
