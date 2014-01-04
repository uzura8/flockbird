<?php
namespace Timeline;

/**
 * Model_TimelineCache class tests
 *
 * @group Modules
 * @group Model
 */
class Test_Model_TimelineCache extends \TestCase
{
	public function test_timeline_exists()
	{
		if (!$list = Model_TimelineCache::find('all'))
		{
			$this->markTestSkipped('No record for test.');
		}
		foreach ($list as $obj)
		{
			// test for cache exists.
			$timelines = Model_Timeline::check_authority($obj->timeline_id);
			$this->assertNotEmpty($timelines);
		}
	}
}
