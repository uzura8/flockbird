<?php
namespace Timeline;

/**
 * Site_Model class tests
 *
 * @group Modules
 * @group Site
 */
class Test_Site_Model extends \TestCase
{
	private static $total_count = 0;

	public static function setUpBeforeClass()
	{
		$query = Model_Timeline::query()->order_by('created_at');
		self::$total_count = $query->count();
	}

	/**
	* @dataProvider get_list_provider
	*/
	public function test_get_list($self_member_id = null, $target_member_id = null, $is_mypage = null, $is_mytimeline = null, $last_id = null, $is_over = null, $limit = null, $sort = null)
	{
		$public_flags_all = \Site_Util::get_public_flags();
		list($test_list, $is_next) = Site_Model::get_list($self_member_id, $target_member_id, $is_mypage, $is_mytimeline, $last_id, $is_over, $limit, $sort);

		// test for limit
		if ($limit)
		{
			$this->assertTrue(count($test_list) <= $limit);
		}

		foreach ($test_list as $timeline)
		{
			// test for public_flag
			if ($timeline->member_id != $self_member_id)
			{
				$this->assertTrue(in_array($timeline->public_flag, array(PRJ_PUBLIC_FLAG_ALL, PRJ_PUBLIC_FLAG_MEMBER)));
			}
			if ($timeline->public_flag ==  PRJ_PUBLIC_FLAG_PRIVATE)
			{
				$this->assertEquals($self_member_id, $timeline->member_id);
			}

			// test for last_id and sort
			if ($last_id)
			{
				if ($is_over)
				{
					$this->assertTrue($timeline->id > $last_id);
				}
				elseif (!empty($sort[0]) && $sort[0] == 'desc')
				{
					$this->assertTrue($timeline->id > $last_id);
				}
				else
				{
					$this->assertTrue($timeline->id < $last_id);
				}
			}
		}
	}

	public function get_list_provider()
	{
		$data = array();
		//($self_member_id, $target_member_id, $is_mypage, $is_mytimeline, $last_id, $is_over, $limit, $sort)
		$data[] = array(1, 0, false, true, null, null, 30, null);
		$data[] = array(1, 0, false, true, null, null, 30, null);
		$data[] = array(1, 0, false, true, 2, null, 30, null);
		$data[] = array(1, 0, false, true, 2, null, 30, array('id' => 'desc'));
		$data[] = array(1, 0, false, true, 2, null, 30, array('id' => 'asc'));
		$data[] = array(1, 0, false, true, 2, null, 30, array('id'));
		$data[] = array(1, 0, false, true, 2, true, 30, null);

		return $data;
	}
}
