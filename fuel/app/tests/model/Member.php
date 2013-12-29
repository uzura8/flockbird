<?php

/**
 * Model_Memeber class tests
 *
 * @group App
 * @group Model
 */
class Test_Model_Memeber extends TestCase
{
	/**
	* @dataProvider recalculate_filesize_total_provider
	*/
	public function test_recalculate_filesize_total($member_id = null, $expected = null)
	{
		$test = Model_Member::recalculate_filesize_total($member_id);
		$this->assertEquals($expected, $test);
	}

	public function recalculate_filesize_total_provider()
	{
		$data = array();
		$members = Model_Member::find('all');
		foreach ($members as $member)
		{
			$data[] = array(
				$member->id,
				$member->filesize_total,
			);
		}

		return $data;
	}
}
