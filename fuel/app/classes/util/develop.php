<?php
class Util_Develop
{
	public static function sleep($execute_absolute = false, $sleep_time = null)
	{
		if (!Config::get('develop.unitTest.strictDatetimeCheck.isEnabled') && !$execute_absolute) return;
		if (!$sleep_time) $sleep_time = Config::get('develop.unitTest.strictDatetimeCheck.sleepTime');

		sleep($sleep_time);
	}

	public static function output_test_info($file, $line, $values = array())
	{
		if (!\Config::get('develop.unitTest.skippedTestInfo.isEnabled')) return;
		$msg = sprintf('Test is skipped. %s:%d%s', $file, $line, $values ? sprintf(' data(%s)', implode(', ', $values)) : '');
		if (\Config::get('develop.unitTest.skippedTestInfo.isOutputLog')) \Log::info($msg);
		if (\Config::get('develop.unitTest.skippedTestInfo.isOutputStd')) echo $msg.PHP_EOL;
	}
}
