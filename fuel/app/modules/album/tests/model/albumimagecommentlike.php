<?php
namespace Album;

/**
 * Model_AlbumImageCommentLike class tests
 *
 * @group Modules
 * @group Model
 */
class Test_Model_AlbumImageCommentLike extends \TestCase
{
	private static $member_id = 1;
	private static $like_count = 0;
	private static $album_image_id;
	private static $album_image_comment_id;
	private static $album_image_comment_before;

	public static function setUpBeforeClass()
	{
		$album = self::force_save_album(self::$member_id);
		$album_image = self::save_album_image($album->id);
		self::$album_image_id = $album_image->id;
		$album_image_comment = self::save_comment($album_image->id, self::$member_id);
		self::$album_image_comment_id = $album_image_comment->id;
	}

	protected function setUp()
	{
		self::$like_count = \Util_Orm::get_count_all('\Album\Model_AlbumImageCommentLike', array('album_image_comment_id' => self::$album_image_comment_id));
	}

	/**
	* @dataProvider update_like_provider
	*/
	public function test_update_like($member_id)
	{
		$album_image_comment_id = self::$album_image_comment_id;

		// note_like save
		\Util_Develop::sleep();
		$is_liked = self::execute_like($album_image_comment_id, $member_id);

		$album_image_comment = Model_AlbumImageComment::find($album_image_comment_id);
		$album_image_comment_like = \Util_Orm::get_last_row('\Album\Model_AlbumImageCommentLike', array('album_image_comment_id' => $album_image_comment_id));

		// 件数
		$like_count = \Util_Orm::get_count_all('\Album\Model_AlbumImageCommentLike', array('album_image_comment_id' => $album_image_comment_id));
		$like_count_expect = $is_liked ? self::$like_count + 1 : self::$like_count - 1;
		$this->assertEquals($like_count_expect, $like_count);

		// 値
		$this->assertEquals($like_count, $album_image_comment->like_count);
		if (!$is_liked)
		{
			$this->assertNull($album_image_comment_like);
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
		$album_image_comment = self::save_comment(self::$album_image_id, self::$member_id);
		$album_image_comment_id = $album_image_comment->id;

		// like 実行
		self::execute_like($album_image_comment_id, 3);
		self::execute_like($album_image_comment_id, 4);
		self::execute_like($album_image_comment_id, 5);
		self::execute_like($album_image_comment_id, 3);

		// 件数
		$like_count = \Util_Orm::get_count_all('\Album\Model_AlbumImageCommentLike', array('album_image_comment_id' => $album_image_comment_id));
		$this->assertEquals(2, $like_count);
		
		$album_image_comment_likes = Model_AlbumImageCommentLike::query()->where('album_image_comment_id', $album_image_comment_id)->get();
		$this->assertCount(2, $album_image_comment_likes);
		foreach ($album_image_comment_likes as $album_image_comment_like)
		{
			$this->assertContains($album_image_comment_like->member_id, array(4, 5));
		}
	}

	public function test_delete_parent()
	{
		$album_image_comment_id = self::$album_image_comment_id;
		$album_image_comment = Model_AlbumImageComment::find($album_image_comment_id);
		if (!\Util_Orm::get_count_all('\Album\Model_AlbumImageCommentLike', array('album_image_comment_id' => $album_image_comment_id)))
		{
			self::execute_like($album_image_comment_id, 6);
			self::execute_like($album_image_comment_id, 7);
		}
		$album_image_comment->delete();

		$like_count = \Util_Orm::get_count_all('\Album\Model_AlbumImageCommentLike', array('album_image_comment_id' => $album_image_comment_id));
		$this->assertEquals(0, $like_count);
	}


	private static function execute_like($album_image_comment_id, $member_id)
	{
		return (bool)Model_AlbumImageCommentLike::change_registered_status4unique_key(array(
			'album_image_comment_id' => $album_image_comment_id,
			'member_id' => $member_id,
		));
	}

	private static function save_comment($album_image_id, $member_id)
	{
		$comment = new Model_AlbumImageComment(array(
			'body' => 'Test for album_image_comment_like.',
			'album_image_id' => $album_image_id,
			'member_id' => $member_id,
		));
		$comment->save();

		return $comment;
	}

	private static function save_album_image($album_id, $album_image_public_flag = null)
	{
		if (is_null($album_image_public_flag)) $album_image_public_flag = PRJ_PUBLIC_FLAG_ALL;
		$values = array(
			'name' => 'test',
			'public_flag' => PRJ_PUBLIC_FLAG_ALL,
		);
		$upload_file_path = self::setup_upload_file();
		list($album_image, $file) = Model_AlbumImage::save_with_relations($album_id, null, null, $upload_file_path, 'album', $values);

		return $album_image;
	}

	private static function force_save_album($member_id)
	{
		$values = array(
			'name' => 'test album_image.',
			'body' => 'This is test for album_image.',
			'public_flag' => PRJ_PUBLIC_FLAG_ALL,
			'member_id' => $member_id,
		);
		$album = Model_Album::forge($values);
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
}
