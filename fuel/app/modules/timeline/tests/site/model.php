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
	private static $total_first_id = 0;
	private static $total_last_id = 0;

	public static function setUpBeforeClass()
	{
		$query = Model_TimelineCache::query();
		self::$total_count = $query->count();
		$total_list = $query->get();
		$total_first_obj = \Util_Array::get_first($total_list);
		self::$total_first_id = $total_first_obj->id;
		$total_last_obj = \Util_Array::get_last($total_list);
		self::$total_last_id = $total_last_obj->id;
	}

	/**
	* @dataProvider get_list_provider
	*/
	public function test_get_list($self_member_id = null, $target_member_id = null, $is_mytimeline = null, $viewType = null, $max_id = null, $limit = null, $is_latest = null, $is_desc = null, $since_id = null)
	{
		$public_flags_all = \Site_Util::get_public_flags();
		list($test_list, $next_id) = Site_Model::get_list($self_member_id, $target_member_id, $is_mytimeline, $viewType, $max_id, $limit, $is_latest, $is_desc, $since_id);
		if (!$test_list) return;

		$is_limitted = (count($test_list) <= self::$total_count);

		// test for limit
		if ($limit)
		{
			$this->assertTrue(count($test_list) <= $limit);
		}
		else
		{
			$this->assertTrue(count($test_list) <= \Config::get('timeline.articles.limit'));
		}

		$first_id = 0;
		$before_id = 0;
		foreach ($test_list as $timeline_cache)
		{
			if (!$first_id) $first_id = $timeline_cache->id;
			// member_id
			if ($target_member_id)
			{
				$this->assertEquals($target_member_id, $timeline_cache->member_id);
			}

			// test for public_flag
			if ($timeline_cache->public_flag == FBD_PUBLIC_FLAG_PRIVATE)
			{
				$this->assertEquals($self_member_id, $timeline_cache->member_id);
			}
			if (!$self_member_id)
			{
				$this->assertEquals($timeline_cache->public_flag, FBD_PUBLIC_FLAG_ALL);
			}
			if ($self_member_id && $timeline_cache->member_id != $self_member_id)
			{
				$this->assertTrue(in_array($timeline_cache->public_flag, array(FBD_PUBLIC_FLAG_ALL, FBD_PUBLIC_FLAG_MEMBER)));
			}
			if ($self_member_id && $self_member_id == $target_member_id)
			{
				$this->assertTrue(in_array($timeline_cache->public_flag, array(FBD_PUBLIC_FLAG_ALL, FBD_PUBLIC_FLAG_MEMBER, FBD_PUBLIC_FLAG_PRIVATE)));
			}
			if ($self_member_id && $target_member_id && $self_member_id != $target_member_id)
			{
				$this->assertTrue(in_array($timeline_cache->public_flag, array(FBD_PUBLIC_FLAG_ALL, FBD_PUBLIC_FLAG_MEMBER)));
			}
			if ($self_member_id && $is_mytimeline && $timeline_cache->member_id != $self_member_id)
			{
				$this->assertTrue(in_array($timeline_cache->public_flag, array(FBD_PUBLIC_FLAG_ALL, FBD_PUBLIC_FLAG_MEMBER)));
			}
			if ($self_member_id && $is_mytimeline && $timeline_cache->member_id == $self_member_id)
			{
				$this->assertTrue(in_array($timeline_cache->public_flag, array(FBD_PUBLIC_FLAG_ALL, FBD_PUBLIC_FLAG_MEMBER, FBD_PUBLIC_FLAG_PRIVATE)));
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

			// test for param max_id
			if ($max_id)
			{
				$this->assertTrue($timeline_cache->id <= $max_id);
			}
			// test for param since_id
			if ($since_id)
			{
				$this->assertTrue($timeline_cache->id > $since_id);
			}

			if ($before_id)
			{
				// test for param is_desc
				if ($is_desc)
				{
					$this->assertTrue($timeline_cache->id < $before_id);
				}
				else
				{
					$this->assertTrue($timeline_cache->id > $before_id);
				}
			}
			$before_id = $timeline_cache->id;
		}
		$last_id = $timeline_cache->id;

		// test for param is_latest
		if ($is_latest && !$is_limitted)
		{
			$this->assertTrue($first_id == self::$total_last_id);
		}

		// test for return next_id
		if ($next_id)
		{
			if ($is_desc)
			{
				$this->assertTrue($next_id < $last_id);
			}
			else
			{
				$this->assertTrue($next_id > $last_id);
			}
		}
	}

	public function get_list_provider()
	{
		$data = array();
		//($self_member_id = 0, $target_member_id = 0, $is_mytimeline = false, $viewType = null, $max_id = 0, $limit = 0, $is_latest = true, $is_desc = true, $since_id = 0)

		// 全体のタイムライン
		//   認証
		$data[] = array(1, 0, false, 0, 3, true, true, 0);// 最新の降順
		$data[] = array(1, 0, false, 0, 3, true, false, 0);// 最新の昇順
		$data[] = array(1, 0, false, 0, 3, false, true, 0);// 古いものから降順
		$data[] = array(1, 0, false, 0, 3, false, false, 0);// 古いものから昇順
		//   非認証
		$data[] = array(0, 0, false, 0, 3, true, true, 0);// 最新の降順
		$data[] = array(0, 0, false, 0, 3, true, false, 0);// 最新の昇順
		$data[] = array(0, 0, false, 0, 3, false, true, 0);// 古いものから降順
		$data[] = array(0, 0, false, 0, 3, false, false, 0);// 古いものから昇順
		//   max_id
		$data[] = array(1, 0, false, 4, 3, true, true, 0);// 最新の降順
		//   since_id
		$data[] = array(1, 0, false, 0, 3, true, true, 2);// 最新の降順
		//   max_id & since_id
		$data[] = array(1, 0, false, 4, 3, true, true, 2);// 最新の降順

		// myhome のタイムライン
		$data[] = array(1, 0, true, 0, 0, true, true, 0);// 最新の降順
		// followしているメンバーのタイムライン
		$data[] = array(1, 0, true, 1, 0, true, true, 0);// 最新の降順
		// friendのタイムライン
		//$data[] = array(1, 0, true, 2, 0, true, true, 0);// 最新の降順

		// 自分のタイムライン
		$data[] = array(1, 1, true, 0, 0, true, true, 0);// 最新の降順

		// メンバーのタイムライン
		$data[] = array(1, 2, true, 0, 0, true, true, 0);// 最新の降順

		return $data;
	}
}
