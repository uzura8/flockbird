<?php
namespace Album;

/**
 * Model_AlbumImage class tests
 *
 * @group Modules
 * @group Model
 */
class Test_Model_AlbumImage extends \TestCase
{
	private static $album;
	private static $album_image;
	private static $total_count = 0;
	private static $add_count = 0;
	private static $timeline_count = 0;
	private static $timeline_cache_count = 0;
	private static $member;
	private static $member_id = 1;
	private static $upload_file_path;
	private static $is_check_timeline_view_cache;

	public static function setUpBeforeClass()
	{
		self::$is_check_timeline_view_cache = (is_enabled('timeline') && \Config::get('timeline.articles.cache.is_use'));
		$values = array(
			'name' => 'test',
			'body' => 'This is test.',
			'public_flag' => PRJ_PUBLIC_FLAG_MEMBER,
		);
		self::$album = self::force_save_album(self::$member_id, $values);
		self::$member = \Model_Member::check_authority(self::$member_id);
		self::set_timeline_count();
	}

	protected function setUp()
	{
		self::set_count();
	}

	/**
	* @dataProvider insert_provider
	*/
	public function test_insert($public_flag, $timeline_public_flag_expected)
	{
		// timeline view cache 作成
		if (self::$is_check_timeline_view_cache)
		{
			$timeline_view_cache_before = \Timeline\Site_Util::make_view_cache4foreign_table_and_foreign_id('album', $album->id, \Config::get('timeline.types.albumi_image'));
		}

		// insert
		self::$upload_file_path = self::setup_upload_file();
		list($album_image, $file) = Model_AlbumImage::save_with_relations(self::$album->id, self::$member, $public_flag, self::$upload_file_path, 'album_image');
		self::$add_count++;

		// 返り値の確認
		$this->assertNotEmpty($album_image);
		$this->assertNotEmpty($file);
		// 件数
		$this->assertEquals(self::$total_count + 1, \Util_Orm::get_count_all('\Album\Model_AlbumImage'));
		// 公開範囲
		$this->assertEquals($public_flag, $album_image->public_flag);

		// timeline 関連
		if (is_enabled('timeline'))
		{
			// 件数
			$this->assertEquals(self::$timeline_count + 1, \Util_Orm::get_count_all('\Timeline\Model_Timeline'));
			$this->assertEquals(self::$timeline_cache_count + 2, \Util_Orm::get_count_all('\Timeline\Model_TimelineCache'));

			// timelines
			$timelines = \Timeline\Model_Timeline::get4foreign_table_and_foreign_ids('album', self::$album->id, \Config::get('timeline.types.album_image'));
			$this->assertCount(1, $timelines);
			$timeline = array_shift($timelines);

			// timeline_child_data
			$timeline_child_datas = \Timeline\Model_TimelineChildData::get4timeline_id($timeline->id);
			$this->assertCount(self::$add_count, $timeline_child_datas);

			$timeline_child_data = \Util_Array::get_last($timeline_child_datas);
			$this->assertEquals('album_image', $timeline_child_data->foreign_table);
			$this->assertEquals($album_image->id, $timeline_child_data->foreign_id);

			$this->assertEquals($timeline_public_flag_expected, $timeline->public_flag);
			$this->assertContains($timeline->sort_datetime, \Util_Date::get_datetime_list($album_image->created_at));
			$this->assertTrue(\Util_Date::check_is_future($timeline->sort_datetime, self::$album->created_at));

			// timeline view cache check
			if (self::$is_check_timeline_view_cache)
			{
				$this->assertEmpty(\Timeline\Site_Util::get_view_cache($timeline->id));
			}
		}
	}

	public function insert_provider()
	{
		$data = array();

		// アルバム編集(変更なし) #0
		$data[] = array(PRJ_PUBLIC_FLAG_MEMBER, PRJ_PUBLIC_FLAG_MEMBER);
		$data[] = array(PRJ_PUBLIC_FLAG_MEMBER, PRJ_PUBLIC_FLAG_MEMBER);
		$data[] = array(PRJ_PUBLIC_FLAG_PRIVATE, PRJ_PUBLIC_FLAG_MEMBER);
		$data[] = array(PRJ_PUBLIC_FLAG_ALL, PRJ_PUBLIC_FLAG_ALL);

		return $data;
	}

	/**
	* @dataProvider update_provider
	*/
	public function test_update($default_values, $updated_values, $shot_at_expected, $timeline_public_flag_expected)
	{
		if (!self::$album_image || $default_values)
		{
			self::$album_image = $this->get_album_image($default_values, 2);
		}

		// timeline view cache 作成
		if (self::$is_check_timeline_view_cache)
		{
			$timeline_view_cache_before = \Timeline\Site_Util::make_view_cache4foreign_table_and_foreign_id('album', $album->id, \Config::get('timeline.types.albumi_image'));
		}

		$before = array(
			'name' => self::$album_image->name,
			'public_flag' => self::$album_image->public_flag,
			'created_at' => self::$album_image->created_at,
			'updated_at' => self::$album_image->updated_at,
			'shot_at' => self::$album_image->shot_at,
		);

		// update
		$is_changed = self::$album_image->update_with_relations($updated_values);

		// 件数
		$this->assertEquals(self::$total_count, \Util_Orm::get_count_all('\Album\Model_AlbumImage'));

		// 更新値: name
		if (isset($updated_values['name'])) $this->assertEquals($updated_values['name'], self::$album_image->name);
		// 更新値: public_flag
		if (isset($updated_values['public_flag'])) $this->assertEquals($updated_values['public_flag'], self::$album_image->public_flag);
		// 更新値: shot_at
		$this->assertEquals($shot_at_expected, self::$album_image->shot_at);

		// date
		$this->assertEquals($before['created_at'], self::$album_image->created_at);
		// 変更あり
		if ($is_changed)
		{
			$this->assertTrue(\Util_Date::check_is_future(self::$album_image->updated_at, self::$album_image->created_at));
			$this->assertTrue(\Util_Date::check_is_future(self::$album_image->updated_at, $before['updated_at']));
		}
		// 変更なし
		else
		{
			$this->assertEquals($before['updated_at'], self::$album_image->updated_at);
		}

		// timeline
		if (is_enabled('timeline'))
		{
			// 件数
			$this->assertEquals(self::$timeline_count, \Util_Orm::get_count_all('\Timeline\Model_Timeline'));
			$this->assertEquals(self::$timeline_cache_count, \Util_Orm::get_count_all('\Timeline\Model_TimelineCache'));

			$timelines = \Timeline\Model_Timeline::get4foreign_table_and_foreign_ids('album', self::$album_image->album_id, \Config::get('timeline.types.album_image'));
			$timeline = array_shift($timelines);

			$this->assertEquals($timeline_public_flag_expected, $timeline->public_flag);
			// 変更あり
			if ($is_changed)
			{
				$this->assertTrue(\Util_Date::check_is_future($timeline->sort_datetime, self::$album_image->created_at));
			}
			// 変更なし
			else
			{
				$this->assertEquals($before['updated_at'], $timeline->updated_at);
			}

			// timeline view cache check
			if (self::$is_check_timeline_view_cache)
			{
				$this->assertEmpty(\Timeline\Site_Util::get_view_cache($timeline->id));
			}
		}
	}

	public function update_provider()
	{
		$data = array();

		// 編集(変更なし) #0
		$default_value = array(
			'name' => 'update test default.',
			'shot_at' => '2014-01-01 11:11:11',
			'public_flag' => PRJ_PUBLIC_FLAG_PRIVATE,
		);
		$update_value = array(
			'name' => 'update test default.',
			'shot_at_time' => '2014-01-01 11:11',
			'public_flag' => PRJ_PUBLIC_FLAG_PRIVATE,
		);
		$data[] = array($default_value, $update_value, '2014-01-01 11:11:11', PRJ_PUBLIC_FLAG_PRIVATE);

		// 編集(変更あり) #1
		$update_value = array(
			'name' => 'update test edited.',
			'shot_at_time' => '2014-01-01 11:12',
			'public_flag' => PRJ_PUBLIC_FLAG_MEMBER,
		);
		$data[] = array(null, $update_value, '2014-01-01 11:12:00', PRJ_PUBLIC_FLAG_MEMBER);

		// 編集(変更あり) #2
		$update_value = array(
			'name' => '',
			'shot_at' => '2014-01-01 11:13:11',
			'shot_at_time' => '2014-01-01 11:13',
			'public_flag' => PRJ_PUBLIC_FLAG_PRIVATE,
		);
		$data[] = array(null, $update_value, '2014-01-01 11:13:11', PRJ_PUBLIC_FLAG_PRIVATE);

		return $data;
	}

	/**
	* @dataProvider update_public_flag_provider
	*/
	public function test_update_public_flag($default_public_flag, $public_flag, $timeline_public_flag_expected)
	{
		if (!self::$album_image || !is_null($default_public_flag))
		{
			self::$album_image = $this->get_album_image(array('public_flag' => $default_public_flag), 2);
		}

		// timeline view cache 作成
		if (self::$is_check_timeline_view_cache)
		{
			$timeline_view_cache_before = \Timeline\Site_Util::make_view_cache4foreign_table_and_foreign_id('album', $album->id, \Config::get('timeline.types.albumi_image'));
		}

		$before = array(
			'public_flag' => self::$album_image->public_flag,
			'created_at' => self::$album_image->created_at,
			'updated_at' => self::$album_image->updated_at,
		);
		// update public_flag
		self::$album_image->update_public_flag($public_flag);
		$is_updated = (self::$album_image->public_flag != $before['public_flag']);

		// 値
		$this->assertEquals($public_flag, self::$album_image->public_flag);

		// date
		// 変更あり
		if ($is_updated)
		{
			$this->assertTrue(\Util_Date::check_is_future(self::$album_image->updated_at, self::$album_image->created_at));
			$this->assertTrue(\Util_Date::check_is_future(self::$album_image->updated_at, $before['updated_at']));
		}
		// 変更なし
		else
		{
			$this->assertEquals($before['updated_at'], self::$album_image->updated_at);
		}

		// timeline
		if (is_enabled('timeline'))
		{
			// 件数
			$this->assertEquals(self::$timeline_count, \Util_Orm::get_count_all('\Timeline\Model_Timeline'));
			$this->assertEquals(self::$timeline_cache_count, \Util_Orm::get_count_all('\Timeline\Model_TimelineCache'));

			$timelines = \Timeline\Model_Timeline::get4foreign_table_and_foreign_ids('album', self::$album_image->album_id, \Config::get('timeline.types.album_image'));
			$timeline = array_shift($timelines);

			$this->assertEquals($timeline_public_flag_expected, $timeline->public_flag);
			// 変更あり
			if ($is_updated)
			{
				$this->assertTrue(\Util_Date::check_is_future($timeline->sort_datetime, self::$album_image->created_at));
			}
			// 変更なし
			else
			{
				$this->assertEquals($before['updated_at'], $timeline->updated_at);
			}

			// timeline view cache check
			if (self::$is_check_timeline_view_cache)
			{
				$this->assertEmpty(\Timeline\Site_Util::get_view_cache($timeline->id));
			}
		}
	}

	public function update_public_flag_provider()
	{
		$data = array();
		$data[] = array(PRJ_PUBLIC_FLAG_PRIVATE, PRJ_PUBLIC_FLAG_PRIVATE, PRJ_PUBLIC_FLAG_PRIVATE);//#0
		$data[] = array(null, PRJ_PUBLIC_FLAG_MEMBER, PRJ_PUBLIC_FLAG_MEMBER);//#1
		$data[] = array(null, PRJ_PUBLIC_FLAG_PRIVATE, PRJ_PUBLIC_FLAG_PRIVATE);//#2
		$data[] = array(null, PRJ_PUBLIC_FLAG_ALL, PRJ_PUBLIC_FLAG_ALL);//#3

		$data[] = array(PRJ_PUBLIC_FLAG_MEMBER, PRJ_PUBLIC_FLAG_MEMBER, PRJ_PUBLIC_FLAG_MEMBER);//#4
		$data[] = array(null, PRJ_PUBLIC_FLAG_PRIVATE, PRJ_PUBLIC_FLAG_MEMBER);//#5
		$data[] = array(null, PRJ_PUBLIC_FLAG_ALL, PRJ_PUBLIC_FLAG_ALL);//#6

		return $data;
	}

	public function test_delete()
	{
		if (!self::$album_image)
		{
			self::$album_image = $this->get_album_image(array('public_flag' => PRJ_PUBLIC_FLAG_MEMBER));
		}
		$album_image_id = self::$album_image->id;
		$file_id = self::$album_image->file_id;
		$file_size = self::$album_image->file->filesize;
		$file_path = \Site_Upload::get_uploaded_file_real_path(self::$album_image->file->path, self::$album_image->file->name);
		$member_filesize_before = self::get_member_filesize_total(self::$member_id);

		// timeline view cache 作成
		if (self::$is_check_timeline_view_cache)
		{
			$timeline_view_cache_before = \Timeline\Site_Util::make_view_cache4foreign_table_and_foreign_id('album', $album->id, \Config::get('timeline.types.albumi_image'));
		}

		// file
		$this->assertTrue(file_exists($file_path));

		// album_image delete.
		self::$album_image->delete();

		// 件数
		$this->assertEquals(self::$total_count - 1, \Util_Orm::get_count_all('\Album\Model_AlbumImage'));

		// file
		$this->assertEmpty(\Model_File::find($file_id));
		$this->assertFalse(file_exists($file_path));

		// meber_filesize
		$this->assertEquals($member_filesize_before - $file_size, self::get_member_filesize_total(self::$member_id));

		// timeline
		if (is_enabled('timeline'))
		{
			$timeline_child_datas = \Timeline\Model_TimelineChildData::get4foreign_table_and_foreign_ids('album_image', $album_image_id);
			$this->assertEmpty($timeline_child_datas);

			// timeline view cache check
			if (self::$is_check_timeline_view_cache)
			{
				$this->assertEmpty(\Timeline\Site_Util::get_view_cache($timeline->id));
			}
		}
	}

	private function get_album_image($values, $create_count = 1, $album_id = null)
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
		for ($i = 0; $i < $create_count; $i++)
		{
			self::$upload_file_path = self::setup_upload_file();
			list($album_image, $file) = Model_AlbumImage::save_with_relations(self::$album->id, self::$member, $public_flag, self::$upload_file_path, 'album_image', $values);
		}
		self::set_count();
		self::set_timeline_count();

		return $album_image;
	}

	private function get_last_row()
	{
		return \Util_Orm::get_last_row('\Note\Model_Note');
	}

	private static function set_count()
	{
		self::$total_count = \Util_Orm::get_count_all('\Album\Model_AlbumImage');
	}

	private static function set_timeline_count()
	{
		if (is_enabled('timeline'))
		{
			self::$timeline_count = \Util_Orm::get_count_all('\Timeline\Model_Timeline');
			self::$timeline_cache_count = \Util_Orm::get_count_all('\Timeline\Model_TimelineCache');
		}
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

		//// album_image save
		//$member = \Model_Member::check_authority($member_id);
		//list($album_image, $file) = Model_AlbumImage::save_with_relations($album->id, $member, $values['public_flag'], $upload_file_path, 'album');

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

	private static function get_album_filesize_total($album_id)
	{
		$album_images = Model_AlbumImage::get4album_id($album_id, true);
		$size = 0;
		foreach ($album_images as $album_image)
		{
			$size += $album_image->file->filesize;
		}

		return $size;
	}

	private static function get_member_filesize_total($member_id)
	{
		$member = \Model_Member::check_authority($member_id);

		return $member->filesize_total;
	}
}
