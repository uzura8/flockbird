<?php
namespace Album;

/**
 * Model_AlbumImageLike class tests
 *
 * @group Modules
 * @group Model
 */
class Test_Model_AlbumImageLike extends \TestCase
{
	private static $album;
	private static $member_id = 1;
	private static $upload_file_path;
	private static $album_image;
	private static $album_image_like_count = 0;

	public static function setUpBeforeClass()
	{
		self::set_album_image();
	}

	protected function setUp()
	{
		self::$album_image = self::get_album_image();
		self::$album_image_like_count = self::get_album_image_like_count();
	}

	/**
	* @dataProvider update_like_provider
	*/
	public function test_update_post_like($member_id)
	{
		$album_image_id = self::$album_image->id;
		$album_image_before = self::$album_image;

		// album_image_like save
		\Util_Develop::sleep();
		$is_liked = (bool)Model_AlbumImageLike::change_registered_status4unique_key(array(
			'album_image_id' => $album_image_id,
			'member_id' => $member_id,
		));
		$album_image = self::get_album_image();
		$album_image_like = \Util_Orm::get_last_row('\Album\Model_AlbumImageLike', array('album_image_id' => $album_image_id));

		// 件数
		$like_count = \Util_Orm::get_count_all('\Album\Model_AlbumImageLike', array('album_image_id' => $album_image_id));
		$like_count_after = $is_liked ? self::$album_image_like_count + 1 : self::$album_image_like_count - 1;
		$this->assertEquals($like_count_after, $like_count);
		if (!$is_liked) $this->assertNull($album_image_like);

		// 値
		$this->assertEquals($like_count, $album_image->like_count);
		$this->assertEquals($album_image_before->sort_datetime, $album_image->sort_datetime);
	}

	public function update_like_provider()
	{
		$data = array();

		$data[] = array(1);
		$data[] = array(1);

		return $data;
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

	private static function get_album_image_like_count()
	{
		return \Util_Orm::get_count_all('\Album\Model_AlbumImageLike', array('album_image_id' => self::$album_image->id));
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
