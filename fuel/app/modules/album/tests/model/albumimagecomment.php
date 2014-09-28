<?php
namespace Album;

/**
 * Model_AlbumImageComment class tests
 *
 * @group Modules
 * @group Model
 */
class Test_Model_AlbumImageComment extends \TestCase
{
	private static $album;
	private static $member_id = 1;
	private static $upload_file_path;
	private static $album_image;
	private static $album_image_comment_count = 0;

	public static function setUpBeforeClass()
	{
		self::set_album_image();
	}

	protected function setUp()
	{
		self::$album_image = self::get_album_image();
		self::$album_image_comment_count = self::get_album_image_comment_count();
	}

	/**
	* @dataProvider save_comment_provider
	*/
	public function test_save_comment($member_id, $body)
	{
		// album_image_comment save
		\Util_Develop::sleep();
		$album_image_comment = $this->save_comment($member_id, $body);

		$album_image_id = self::$album_image->id;

		// 件数
		$comment_count = \Util_Orm::get_count_all('\Album\Model_AlbumImageComment', array('album_image_id' => $album_image_id));
		$this->assertEquals(self::$album_image_comment_count + 1, $comment_count);

		// 値
		$this->assertEquals($comment_count, self::$album_image->comment_count);
		$this->assertEquals($album_image_comment->created_at, self::$album_image->sort_datetime);
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
		$album_image_id = self::$album_image->id;

		$this->save_comment(1, 'Test comment1.');
		$this->save_comment(1, 'Test comment2.');
		$album_image_comment = $this->save_comment(1, 'Test comment3.');

		// set before data.
		$album_image_before = self::get_album_image();
		$album_image_comment_count_before = self::get_album_image_comment_count();

		// album_image_comment delete
		\Util_Develop::sleep();
		$album_image_comment->delete();
		$album_image = self::get_album_image();

		// 件数
		$comment_count = self::get_album_image_comment_count();
		$this->assertEquals($album_image_comment_count_before - 1, $comment_count);

		// 値
		$this->assertEquals($comment_count, $album_image->comment_count);
		$this->assertEquals($album_image_before->sort_datetime, $album_image->sort_datetime);
	}

	private function save_comment($member_id, $body)
	{
		$comment = new Model_AlbumImageComment(array(
			'body' => $body,
			'album_image_id' => self::$album_image->id,
			'member_id' => $member_id,
		));
		$comment->save();

		return $comment;
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

		return $album_image;
	}

	private static function get_album_image()
	{
		return Model_AlbumImage::query()->where('album_id', self::$album->id)->get_one();
	}

	private static function get_album_image_comment_count()
	{
		return \Util_Orm::get_count_all('\Album\Model_AlbumImageComment', array('album_image_id' => self::$album_image->id));
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
}
