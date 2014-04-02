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
	public function test_get_list($self_member_id = null, $target_member_id = null, $is_mytimeline = null, $viewType = null, $last_id = null, $is_over = null, $limit = null, $sort = null)
	{
		$public_flags_all = \Site_Util::get_public_flags();
		list($test_list, $is_next) = Site_Model::get_list($self_member_id, $target_member_id, $is_mytimeline, $viewType, $last_id, $is_over, $limit, $sort);

		// test for limit
		if ($limit)
		{
			$this->assertTrue(count($test_list) <= $limit);
		}

		foreach ($test_list as $timeline)
		{
			// member_id
			if ($target_member_id)
			{
				$this->assertEquals($target_member_id, $timeline->member_id);
			}

			// test for public_flag
			if ($timeline->public_flag == PRJ_PUBLIC_FLAG_PRIVATE)
			{
				$this->assertEquals($self_member_id, $timeline->member_id);
			}
			if (!$self_member_id)
			{
				$this->assertEquals($timeline->public_flag, PRJ_PUBLIC_FLAG_ALL);
			}
			if ($self_member_id && $timeline->member_id != $self_member_id)
			{
				$this->assertTrue(in_array($timeline->public_flag, array(PRJ_PUBLIC_FLAG_ALL, PRJ_PUBLIC_FLAG_MEMBER)));
			}
			if ($self_member_id && $self_member_id == $target_member_id)
			{
				$this->assertTrue(in_array($timeline->public_flag, array(PRJ_PUBLIC_FLAG_ALL, PRJ_PUBLIC_FLAG_MEMBER, PRJ_PUBLIC_FLAG_PRIVATE)));
			}
			if ($self_member_id && $target_member_id && $self_member_id != $target_member_id)
			{
				$this->assertTrue(in_array($timeline->public_flag, array(PRJ_PUBLIC_FLAG_ALL, PRJ_PUBLIC_FLAG_MEMBER)));
			}
			if ($self_member_id && $is_mytimeline && $timeline->member_id != $self_member_id)
			{
				$this->assertTrue(in_array($timeline->public_flag, array(PRJ_PUBLIC_FLAG_ALL, PRJ_PUBLIC_FLAG_MEMBER)));
			}
			if ($self_member_id && $is_mytimeline && $timeline->member_id == $self_member_id)
			{
				$this->assertTrue(in_array($timeline->public_flag, array(PRJ_PUBLIC_FLAG_ALL, PRJ_PUBLIC_FLAG_MEMBER, PRJ_PUBLIC_FLAG_PRIVATE)));
			}

			// test for viewType
			if ($is_mytimeline && $viewType == 1)
			{
				$member_ids = \Model_MemberRelation::get_member_ids($self_member_id, 'follow');
				$member_ids[] = $self_member_id;
				$this->assertTrue(in_array($timeline->member_id, $member_ids));
			}
			if ($is_mytimeline && $viewType == 2)
			{
				$member_ids = \Model_MemberRelation::get_member_ids($self_member_id, 'firiend');
				$member_ids[] = $self_member_id;
				$this->assertTrue(in_array($timeline->member_id, $member_ids));
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
		//($self_member_id, $target_member_id, $is_mytimeline, $timeline_viewType, $last_id, $is_over, $limit, $sort)
		$data[] = array(1, 0, true, 0, null, null, 30, null);
		$data[] = array(1, 0, true, 1, null, null, 30, null);
		$data[] = array(1, 0, true, 0, 2, null, 30, null);
		$data[] = array(1, 0, true, 1, 2, null, 30, null);
		$data[] = array(1, 0, true, 0, 2, null, 30, array('id' => 'desc'));
		$data[] = array(1, 0, true, 0, 2, null, 30, array('id' => 'asc'));
		$data[] = array(1, 0, true, 0, 2, null, 30, array('id'));
		$data[] = array(1, 0, true, 0, 2, true, 30, null);
		$data[] = array(2, 2, false, 0, null, null, 30, null);
		$data[] = array(1, 2, false, 0, null, null, 30, null);
		$data[] = array(0, 0, false, 0, null, null, 30, null);
		$data[] = array(0, 0, false, 0, 2, true, 30, null);
		$data[] = array(0, 0, false, 0, 2, null, 30, array('id' => 'desc'));
		$data[] = array(0, 0, false, 0, 2, null, 30, array('id' => 'asc'));
		$data[] = array(0, 1, false, 0, null, null, 30, null);

		return $data;
	}
}
