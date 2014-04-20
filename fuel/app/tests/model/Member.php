<?php

/**
 * Model_Memeber class tests
 *
 * @group App
 * @group Model
 */
class Test_Model_Memeber extends TestCase
{
	protected $member;

	protected function setUp()
	{
		if (!$this->members = \Model_Member::find('all'))
		{
			$this->markTestSkipped('No data.');
		}
	}

	public function test_recalculate_filesize_total($member_id = null, $expected = null)
	{
		foreach ($this->members as $member)
		{
			$test = Model_Member::recalculate_filesize_total($member->id);
			$this->assertEquals($member->filesize_total, $test);
		}
	}
}
