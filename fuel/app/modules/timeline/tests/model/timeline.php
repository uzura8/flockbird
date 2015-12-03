<?php
namespace Timeline;

/**
 * Model_Timeline class tests
 *
 * @group Modules
 * @group Model
 */
class Test_Model_Timeline extends \TestCase
{
	private static $is_check_notice_cache;

	public static function setUpBeforeClass()
	{
		self::$is_check_notice_cache = \Site_Notification::check_is_enabled_cahce('notice', true);
	}

	protected function setUp()
	{
	}

	public function test_check_type_normal()
	{
		if (!$list = Model_Timeline::get4type_key('normal'))
		{
			\Util_Develop::output_test_info(__FILE__, __LINE__);
			$this->markTestSkipped();
		}

		foreach ($list as $obj)
		{
			// body に書き込みがあるか
			$this->assertGreaterThan(0, strlen($obj->body));

			// 未使用カラムの値が null か
			$this->assertEmpty($obj->foreign_table);
			$this->assertEmpty($obj->foreign_id);
		}
	}

	public function test_check_type_member_register()
	{
		if (!$list = Model_Timeline::get4type_key('member_register'))
		{
			\Util_Develop::output_test_info(__FILE__, __LINE__);
			$this->markTestSkipped();
		}

		foreach ($list as $obj)
		{
			// check for reference data.
			$this->assertEquals('member', $obj->foreign_table);
			$this->assertEquals($obj->member_id, $obj->foreign_id);
			$this->assertNotEmpty(\Model_Member::check_authority($obj->foreign_id));

			// check for public_flag.
			$this->assertEquals(FBD_PUBLIC_FLAG_ALL, $obj->public_flag);

			// 未使用カラムの値が null か
			$this->assertEmpty($obj->body);
		}
	}

	public function test_check_type_member_name()
	{
		if (!$list = Model_Timeline::get4type_key('member_name'))
		{
			\Util_Develop::output_test_info(__FILE__, __LINE__);
			$this->markTestSkipped();
		}

		foreach ($list as $obj)
		{
			// check for reference data.
			$this->assertEquals('member', $obj->foreign_table);
			$this->assertEquals($obj->member_id, $obj->foreign_id);
			$this->assertNotEmpty(\Model_Member::check_authority($obj->foreign_id));

			// check for public_flag.
			$this->assertEquals(FBD_PUBLIC_FLAG_ALL, $obj->public_flag);
		}
	}

	public function test_check_type_album_image_profile()
	{
		if (!$list = Model_Timeline::get4type_key('album_image_profile'))
		{
			\Util_Develop::output_test_info(__FILE__, __LINE__);
			$this->markTestSkipped();
		}

		foreach ($list as $obj)
		{
			// check for reference data.
			$this->assertEquals('album_image', $obj->foreign_table);
			$album_image = \Album\Model_AlbumImage::check_authority($obj->foreign_id);
			$this->assertNotEmpty($album_image);

			// check for member_id
			$this->assertEquals($album_image->album->member_id, $obj->member_id);

			// check for public_flag.
			$this->assertEquals(FBD_PUBLIC_FLAG_ALL, $obj->public_flag);
			$this->assertEquals(FBD_PUBLIC_FLAG_ALL, $album_image->public_flag);

			// 未使用カラムの値が null か
			$this->assertEmpty($obj->body);
		}
	}

	public function test_check_type_profile_image()
	{
		if (!$list = Model_Timeline::get4type_key('profile_image'))
		{
			\Util_Develop::output_test_info(__FILE__, __LINE__);
			$this->markTestSkipped('No record for test.');
		}

		foreach ($list as $obj)
		{
			// check for reference data.
			$this->assertEquals('file', $obj->foreign_table);
			$file = \Model_File::find($obj->foreign_id);
			$this->assertNotEmpty($file);

			$member = \Model_Member::check_authority($obj->member_id);
			$this->assertNotEmpty($member);
			$this->assertEquals($member->file_name, $obj->foreign_id);

			// check for member_id
			$this->assertEquals($file->member_id, $obj->member_id);

			// check for public_flag.
			$this->assertEquals(FBD_PUBLIC_FLAG_ALL, $obj->public_flag);

			// 未使用カラムの値が null か
			$this->assertEmpty($obj->body);
		}
	}

	public function test_check_type_note()
	{
		if (!$list = Model_Timeline::get4type_key('note'))
		{
			\Util_Develop::output_test_info(__FILE__, __LINE__);
			$this->markTestSkipped('No record for test.');
		}

		foreach ($list as $obj)
		{
			// check for reference data.
			$this->assertEquals('note', $obj->foreign_table);
			$note = \Note\Model_Note::check_authority($obj->foreign_id);
			$this->assertNotEmpty($note);

			$member = \Model_Member::check_authority($obj->member_id);
			$this->assertNotEmpty($member);

			// check for member_id
			$this->assertEquals($note->member_id, $obj->member_id);

			// check for public_flag.
			$this->assertEquals($note->public_flag, $obj->public_flag);

			// 未使用カラムの値が null か
			$this->assertEmpty($obj->body);
		}
	}

	public function test_check_type_album()
	{
		if (!$list = Model_Timeline::get4type_key('album'))
		{
			$this->markTestSkipped('No record for test.');
		}
			\Util_Develop::output_test_info(__FILE__, __LINE__);

		foreach ($list as $obj)
		{
			// check for reference data.
			$this->assertEquals('album', $obj->foreign_table);
			$album = \Album\Model_Album::check_authority($obj->foreign_id);
			$this->assertNotEmpty($album);

			$member = \Model_Member::check_authority($obj->member_id);
			$this->assertNotEmpty($member);

			// check for member_id
			$this->assertEquals($album->member_id, $obj->member_id);

			// check for public_flag.
			$public_flag_range_max = Model_TimelineChildData::get_public_flag_range_max4timeline_id($obj->id);
			$this->assertContains($obj->public_flag, array($public_flag_range_max, $album->public_flag));

			// 未使用カラムの値が null か
			$this->assertEmpty($obj->body);
		}
	}

	public function test_check_type_album_image()
	{
		if (!$list = Model_Timeline::get4type_key('album_image'))
		{
			\Util_Develop::output_test_info(__FILE__, __LINE__);
			$this->markTestSkipped('No record for test.');
		}

		foreach ($list as $obj)
		{
			// check for reference data.
			$this->assertEquals('album', $obj->foreign_table);
			$album = \Album\Model_Album::check_authority($obj->foreign_id);
			$this->assertNotEmpty($album);

			// check for member
			$member = \Model_Member::check_authority($obj->member_id);
			$this->assertNotEmpty($member);

			// check for member_id
			$this->assertEquals($album->member_id, $obj->member_id);

			// check for timeline_child_data
			$timeline_child_datas = Model_TimelineChildData::get4timeline_id($obj->id);
			$this->assertNotEmpty($timeline_child_datas);

			$public_flag_max_range = null;
			if ($timeline_child_datas)
			{
				foreach ($timeline_child_datas as $timeline_child_data)
				{
					// check for reference data.
					$this->assertEquals('album_image', $timeline_child_data->foreign_table);

					// check for album_image
					$album_image = \Album\Model_AlbumImage::check_authority($timeline_child_data->foreign_id);
					$this->assertNotEmpty($album_image);

					// check for album_id
					$this->assertEquals($album->id, $album_image->album_id);

					// check for public_flag.
					if (is_null($public_flag_max_range)) $public_flag_max_range = $album_image->public_flag;
					if (\Site_Util::check_is_expanded_public_flag_range($public_flag_max_range, $album_image->public_flag))
					{
						$public_flag_max_range = $album_image->public_flag;
					}
				}
				$this->assertEquals($public_flag_max_range, $obj->public_flag);
			}
		}
	}

	public function test_check_type_album_image_timeline()
	{
		if (!$list = Model_Timeline::get4type_key('album_image_timeline'))
		{
			$this->markTestSkipped('No record for test.');
		}
			\Util_Develop::output_test_info(__FILE__, __LINE__);

		foreach ($list as $obj)
		{
			// check for reference data.
			$this->assertEquals('album', $obj->foreign_table);
			$album = \Album\Model_Album::check_authority($obj->foreign_id);
			$this->assertNotEmpty($album);

			// check for member
			$member = \Model_Member::check_authority($obj->member_id);
			$this->assertNotEmpty($member);

			// check for member_id
			$this->assertEquals($album->member_id, $obj->member_id);

			// check for timeline_child_data
			$timeline_child_datas = Model_TimelineChildData::get4timeline_id($obj->id);
			$this->assertNotEmpty($timeline_child_datas);

			$public_flag_max_range = null;
			if ($timeline_child_datas)
			{
				foreach ($timeline_child_datas as $timeline_child_data)
				{
					// check for reference data.
					$this->assertEquals('album_image', $timeline_child_data->foreign_table);

					// check for album_image
					$album_image = \Album\Model_AlbumImage::check_authority($timeline_child_data->foreign_id);
					$this->assertNotEmpty($album_image);

					// check for album_id
					$this->assertEquals($album->id, $album_image->album_id);

					// check for public_flag.
					$this->assertEquals($album_image->public_flag, $obj->public_flag);
				}
			}
		}
	}

	public function test_timeline_cache()
	{
		if (!$list = Model_Timeline::find('all'))
		{
			$this->markTestSkipped('No record for test.');
		}
			\Util_Develop::output_test_info(__FILE__, __LINE__);
		foreach ($list as $obj)
		{
			// test for cache exists.
			$caches = Model_TimelineCache::get4timeline_id($obj->id);
			$this->assertNotEmpty($caches);

			// test for cache count.
			$this->assertCount(2, $caches);

			foreach ($caches as $cache)
			{
				// test for same values.
				$this->assertEquals($obj->member_id, $cache->member_id);
				$this->assertEquals($obj->member_id_to, $cache->member_id_to);
				$this->assertEquals($obj->group_id, $cache->group_id);
				$this->assertEquals($obj->page_id, $cache->page_id);
				$this->assertEquals($obj->public_flag, $cache->public_flag);
				$this->assertEquals($obj->type, $cache->type);
				$this->assertEquals($obj->comment_count, $cache->comment_count);

				// test for is_follow.
				$this->assertContains($cache->is_follow, array(0, 1));
			}
		}
	}

	/**
	* @dataProvider mention_provider
	*/
	public function test_comment_mention($member_id_from, $mention_member_ids, $countup_nums_exp, $is_cahced_mention_member_ids_exp, $countup_nums_all_exp)
	{
		if (!is_enabled('notice'))
		{
			\Util_Develop::output_test_info(__FILE__, __LINE__);
			$this->markTestSkipped('notice module is disabled.');
		}

		// 事前準備
		$notice_count_all_before = \Notice\Model_Notice::get_count();

		// set cache
		$notice_counts_before = array();
		foreach ($mention_member_ids as $mention_member_id)
		{
			$notice_counts_before[$mention_member_id] = \Site_Notification::get_unread_count('notice', $mention_member_id);
		}

		// timeline_comment save
		$body = \Site_Test::get_mention_body($mention_member_ids);
		$timeline = Site_Model::save_timeline($member_id_from, FBD_PUBLIC_FLAG_ALL, 'normal', null, null, $body);
		$timeline_id = $timeline->id;

		// check_cache
		if (self::$is_check_notice_cache)
		{
			foreach ($mention_member_ids as $mention_member_id)
			{
				if (in_array($mention_member_id, $is_cahced_mention_member_ids_exp))
				{
					$this->assertFalse(\Notice\Site_Test::check_no_cache4notice_unread($mention_member_id));
				}
				else
				{
					$this->assertTrue(\Notice\Site_Test::check_no_cache4notice_unread($mention_member_id));
				}
			}
		}

		foreach ($mention_member_ids as $mention_member_id)
		{
			// notice count 取得
			$notice_count = \Site_Notification::get_unread_count('notice', $mention_member_id);
			if (self::$is_check_notice_cache) $this->assertFalse(\Notice\Site_Test::check_no_cache4notice_unread($mention_member_id));// cache が生成されていることを確認
			// execute test
			$this->assertEquals($notice_counts_before[$mention_member_id] + $countup_nums_exp[$mention_member_id], $notice_count);// count up を確認
		}

		// Model_Notice
		// 件数
		$notice_count_all = \Notice\Model_Notice::get_count();
		$this->assertEquals($notice_count_all_before + $countup_nums_all_exp, $notice_count_all);
	}

	public function mention_provider()
	{
		$data = array();

		//($member_id_from, $mention_member_ids, $countup_nums_exp, $is_cahced_mention_member_ids_exp, $countup_nums_all_exp)
		// お知らせを受け取る
		$data[] = array(2, array(2), array(2 => 0), array(2), 0);// #0: @自分
		$data[] = array(2, array(3), array(2 => 0, 3 => 1), array(2), 1);// #1: @自分,A
		$data[] = array(2, array(3, 4), array(2 => 0, 3 => 1, 4 => 1), array(2), 1);// #2: @自分,A,B

		return $data;
	}
}
