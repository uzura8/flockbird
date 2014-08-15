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
		$query = Model_Timeline::query();
		self::$total_count = $query->count();
	}

	/**
	* @dataProvider get_list_provider
	*/
	public function test_get_list($self_member_id = null, $target_member_id = null, $is_mytimeline = null, $viewType = null, $last_id = null, $limit = null, $is_desc = null, $is_before = null, $limit_id = null)
	{
		$public_flags_all = \Site_Util::get_public_flags();
		list($test_list, $is_next) = Site_Model::get_list($self_member_id, $target_member_id, $is_mytimeline, $viewType, $last_id, $limit, $is_desc, $is_before, $limit_id);

		// test for limit
		if ($limit)
		{
			$this->assertTrue(count($test_list) <= $limit);
		}

		foreach ($test_list as $timeline_cache)
		{
			// member_id
			if ($target_member_id)
			{
				$this->assertEquals($target_member_id, $timeline_cache->member_id);
			}

			// test for public_flag
			if ($timeline_cache->public_flag == PRJ_PUBLIC_FLAG_PRIVATE)
			{
				$this->assertEquals($self_member_id, $timeline_cache->member_id);
			}
			if (!$self_member_id)
			{
				$this->assertEquals($timeline_cache->public_flag, PRJ_PUBLIC_FLAG_ALL);
			}
			if ($self_member_id && $timeline_cache->member_id != $self_member_id)
			{
				$this->assertTrue(in_array($timeline_cache->public_flag, array(PRJ_PUBLIC_FLAG_ALL, PRJ_PUBLIC_FLAG_MEMBER)));
			}
			if ($self_member_id && $self_member_id == $target_member_id)
			{
				$this->assertTrue(in_array($timeline_cache->public_flag, array(PRJ_PUBLIC_FLAG_ALL, PRJ_PUBLIC_FLAG_MEMBER, PRJ_PUBLIC_FLAG_PRIVATE)));
			}
			if ($self_member_id && $target_member_id && $self_member_id != $target_member_id)
			{
				$this->assertTrue(in_array($timeline_cache->public_flag, array(PRJ_PUBLIC_FLAG_ALL, PRJ_PUBLIC_FLAG_MEMBER)));
			}
			if ($self_member_id && $is_mytimeline && $timeline_cache->member_id != $self_member_id)
			{
				$this->assertTrue(in_array($timeline_cache->public_flag, array(PRJ_PUBLIC_FLAG_ALL, PRJ_PUBLIC_FLAG_MEMBER)));
			}
			if ($self_member_id && $is_mytimeline && $timeline_cache->member_id == $self_member_id)
			{
				$this->assertTrue(in_array($timeline_cache->public_flag, array(PRJ_PUBLIC_FLAG_ALL, PRJ_PUBLIC_FLAG_MEMBER, PRJ_PUBLIC_FLAG_PRIVATE)));
			}

			// test for viewType
			if ($is_mytimeline && $viewType == 1)
			{
				$member_ids = \Model_MemberRelation::get_member_ids($self_member_id, 'follow');
				$member_ids[] = $self_member_id;
				$this->assertTrue(in_array($timeline_cache->member_id, $member_ids));
			}
			if ($is_mytimeline && $viewType == 2)
			{
				$member_ids = \Model_MemberRelation::get_member_ids($self_member_id, 'firiend');
				$member_ids[] = $self_member_id;
				$this->assertTrue(in_array($timeline_cache->member_id, $member_ids));
			}

			// test for last_id and sort
			if ($last_id)
			{
				if ($is_desc)
				{
					$this->assertTrue($timeline_cache->id > $last_id);
				}
				else
				{
					$this->assertTrue($timeline_cache->id < $last_id);
				}
			}
		}
	}

	public function get_list_provider()
	{
		$data = array();
		//($self_member_id, $target_member_id, $is_mytimeline, $viewType, $last_id, $limit, $is_desc, $is_before, $limit_id)

		// myhome の timeline を表示
		$data[] = array(1, 0, true, 0, null, 30, null, null);
		$data[] = array(1, 0, true, 1, null, 30, null, null);
		$data[] = array(1, 0, true, 0, 2, 30, null, null);
		$data[] = array(1, 0, true, 1, 2, 30, null, null);
		$data[] = array(2, 2, false, 0, null, 30, null, null);
		$data[] = array(1, 2, false, 0, null, 30, null, null);
		$data[] = array(0, 0, false, 0, null, 30, null, null);
		$data[] = array(0, 0, false, 0, 2, 30, null);
		$data[] = array(0, 1, false, 0, null, 30, null, null);

		return $data;
	}
}
