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
	public static function setUpBeforeClass()
	{
	}

	protected function setUp()
	{
	}

	public function test_check_type_normal()
	{
		if (!$list = Model_Timeline::get4type_key('normal'))
		{
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
			$this->markTestSkipped();
		}

		foreach ($list as $obj)
		{
			// check for reference data.
			$this->assertEquals('member', $obj->foreign_table);
			$this->assertEquals($obj->member_id, $obj->foreign_id);
			$this->assertNotEmpty(\Model_Member::find($obj->foreign_id));

			// check for public_flag.
			$this->assertEquals(PRJ_PUBLIC_FLAG_ALL, $obj->public_flag);

			// 未使用カラムの値が null か
			$this->assertEmpty($obj->body);
		}
	}

	public function test_check_type_album_image_profile()
	{
		if (!$list = Model_Timeline::get4type_key('album_image_profile'))
		{
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
			$this->assertEquals($album_image->public_flag, $obj->public_flag);

			// 未使用カラムの値が null か
			$this->assertEmpty($obj->body);
		}
	}
}
