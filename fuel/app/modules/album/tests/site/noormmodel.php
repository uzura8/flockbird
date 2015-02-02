<?php
namespace Album;

/**
 * Site_NoOrmModel class tests
 *
 * @group Modules
 * @group Model
 */
class Test_Site_NoOrmModel extends \TestCase
{
	private static $member_id = 1;
	private static $member;
	private static $album;
	private static $album_image;
	private static $album_image_comment;
	private static $is_check_timeline_view_cache;
	private static $is_check_notice_cache;

	public static function setUpBeforeClass()
	{
		self::$is_check_timeline_view_cache = (is_enabled('timeline') && \Config::get('timeline.articles.cache.is_use'));
		self::$is_check_notice_cache = (is_enabled('notice') && \Config::get('notice.cache.unreadCount.isEnabled'));

		self::$member = \Model_Member::check_authority(self::$member_id);
		self::$album_image = \Album\Site_Test::setup_album_image(self::$member_id, null, 1);
		self::$album = Model_Album::find(self::$album_image->album_id);
		self::$album_image_comment = \Site_Test::save_comment('album_image', self::$album_image->id, self::$member_id);
		Model_AlbumImageLike::change_registered_status4unique_key(array(
			'album_image_id' => self::$album_image->id,
			'member_id' => 2,
		));
		Model_AlbumImageCommentLike::change_registered_status4unique_key(array(
			'album_image_comment_id' => self::$album_image_comment->id,
			'member_id' => 3,
		));
		\Site_Test::save_comment('album_image', self::$album_image->id, 2);
		\Site_Test::save_comment('album_image', self::$album_image->id, 3);
	}

	private static function set_timeline_count()
	{
	}

	protected function setUp()
	{
		self::set_timeline_count();
	}

	public function test_delete_album_image_multiple4ids()
	{
		$timeline_ids = array_unique(\Util_Orm::conv_col2array(\Timeline\Model_TimelineChildData::get4foreign_table_and_foreign_ids('album_image_id', self::$album_image->id), 'timeline_id'));
		$notices = array();
		if (is_enabled('notice'))
		{
			$notice_ids  = \Util_Orm::conv_col2array(\Notice\Model_Notice::get4foreign_data('album_image', self::$album_image->id), 'id');
			$notice_ids += \Util_Orm::conv_col2array(\Notice\Model_Notice::get4parent_data('album_image', self::$album_image->id), 'id');
			$notice_ids  = array_unique($notice_ids);
		}

		$notice_statuses = \Notice\Model_NoticeStatus::query()->where('notice_id', 'in', $notice_ids)->get();
		$file_name = self::$album_image->file_name;
		$file = \Model_File::get4name($file_name);
		$file_size = $file->filesize;
		$member_filesize_before = self::$member->filesize_total;

		// timeline view cache 作成
		if (self::$is_check_timeline_view_cache)
		{
			$timeline_view_cache_before = \Timeline\Site_Util::make_view_cache4foreign_table_and_foreign_id('album', $album->id, \Config::get('timeline.types.album_image'));
		}

		// file
		if (conf('upload.isSaveDb'))
		{
			$this->assertNotNull(\Model_FileBin::get4name(self::$album_image->file_name));
		}

		// album_image delete.
		Site_NoOrmModel::delete_album_image_multiple4ids(self::$album_image->id, true);

		// 件数
		$this->assertEmpty(Model_AlbumImage::query()->where('id', self::$album_image->id)->get());
		$this->assertEmpty(Model_AlbumImageLike::query()->where('album_image_id', self::$album_image_comment->id)->get());
		$this->assertEmpty(Model_AlbumImageComment::query()->where('album_image_id', self::$album_image->id)->get());
		$this->assertEmpty(Model_AlbumImageCommentLike::query()->where('album_image_comment_id', self::$album_image_comment->id)->get());

		// file
		$this->assertEmpty(\Model_File::get4name($file_name));

		// meber_filesize
		$this->assertEquals($member_filesize_before - $file_size, self::get_member_filesize_total(self::$member_id));

		// timeline
		if (is_enabled('timeline'))
		{
			$timeline_child_datas = \Timeline\Model_TimelineChildData::get4foreign_table_and_foreign_ids('album_image', self::$album_image->id);
			$this->assertEmpty($timeline_child_datas);
			if ($timeline_ids) $this->assertEmpty(\Timeline\Model_Timeline::query()->where('id', 'in', $timeline_ids)->get());

			// timeline view cache check
			if (self::$is_check_timeline_view_cache && $timeline_ids)
			{
				foreach ($timeline_ids as $$timeline_id) $this->assertEmpty(\Timeline\Site_Util::get_view_cache($timeline_id));
			}
		}

		// notice
		if (is_enabled('notice'))
		{
			$this->assertEmpty(\Notice\Model_MemberWatchContent::get4foreign_data('album_image', self::$album_image->id));
			if ($notice_ids)
			{
				$this->assertEmpty(\Notice\Model_Notice::query()->where('id', 'in', $notice_ids)->get());
				$this->assertEmpty(\Notice\Model_NoticeStatus::query()->where('notice_id', 'in', $notice_ids)->get());
				$this->assertEmpty(\Notice\Model_NoticeMemberFrom::query()->where('notice_id', 'in', $notice_ids)->get());
			}
			if (self::$is_check_notice_cache)
			{
				foreach ($notice_statuses as $notice_statuse)
				{
					$this->assertEmpty(\Notice\Site_Util::get_unread_count_cache($notice_statuse->member_id));
				}
			}
		}
	}

	private static function get_album_filesize_total($album_id)
	{
		$album_images = Model_AlbumImage::get4album_id($album_id);
		$size = 0;
		foreach ($album_images as $album_image)
		{
			$file = \Model_File::get4name($album_image->file_name);
			$size += $file->filesize;
		}

		return $size;
	}

	private static function get_member_filesize_total($member_id)
	{
		$member = \Model_Member::check_authority($member_id);

		return $member->filesize_total;
	}
}
