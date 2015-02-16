<?php

class Util_Exif
{
	public static function get_exif($file_path, $target_tags = array())
	{
		$exifs = exif_read_data($file_path) ?: array();
		if ($exifs)
		{
			if ($target_tags)
			{
				$exifs = static::filter($exifs, $target_tags);
			}
			else
			{
				$exifs = util_string::validate_exif($exifs);
			}
		}

		return $exifs;
	}

	public static function filter($exifs, $target_tags)
	{
		$filtered = array();
		foreach ($target_tags as $tag)
		{
			if (isset($exifs[$tag]))
			{
				if (is_array($exifs[$tag]) && $exifs[$tag])
				{
					$filtered[$tag] = $exifs[$tag];
				}
				elseif (strlen(trim($exifs[$tag])))
				{
					$filtered[$tag] = trim($exifs[$tag]);
				}
			}
		}

		return $filtered;
	}

	public static function validate_exif($value)
	{
		// 配列の場合は再帰的に処理
		if (is_array($value))
		{
			return array_map(array('Util_Exif', 'validate_exif'), $value);
		}

		if ($encoding = mb_detect_encoding($value))
		{
			$value = mb_convert_encoding($value, \Fuel::$encoding, $encoding ?: 'auto');
		}
		else
		{
			$value = '';
		}

		return trim($value);
	}

	public static function get_original_datetime($exif)
	{
		if (empty($exif['DateTimeOriginal'])) return null;

		if (!$exif_time = \Util_Date::check_is_past($exif['DateTimeOriginal'], null, '-30 years', true)) return null;

		return $exif_time;
	}

	public static function get_location($exif)
	{
		if (empty($exif['GPSLatitudeRef'])) return null;
		if (empty($exif['GPSLatitude'])) return null;
		if (empty($exif['GPSLongitudeRef'])) return null;
		if (empty($exif['GPSLongitude'])) return null;

		if (!$lat = static::convert2decimal_geolocation($exif['GPSLatitudeRef'], $exif['GPSLatitude'])) return false;
		if (!$lnt = static::convert2decimal_geolocation($exif['GPSLongitudeRef'], $exif['GPSLongitude'])) return false;

		return array($lat, $lnt);
	}

	public static function convert2decimal_geolocation($ref, array $decimal60_values)
	{
		if (!in_array($ref, array('N', 'S', 'E', 'W'))) return false;
		if (count($decimal60_values) < 3) return false;

		//60進数から10進数に変換
		$degree = static::convert_exif_gps_str2int($decimal60_values[0]);
		$min    = static::convert_exif_gps_str2int($decimal60_values[1]);
		$sec    = static::convert_exif_gps_str2int($decimal60_values[2]);
		$decimal10_value = $degree + ($min / 60) + ($sec / 3600);

		if (in_array($ref, array('N', 'S')) && $decimal10_value > 90) return false;
		if (in_array($ref, array('E', 'W')) && $decimal10_value > 180) return false;

		//南緯、または西経の場合はマイナスにして返却
		return (in_array($ref, array('S', 'W'))) ? ($decimal10_value * -1) : $decimal10_value;
	}

	public static function convert_exif_gps_str2int($slus_exploded_string)
	{
		$list = explode('/', $slus_exploded_string);

		return (int)$list[0] / (int)$list[1];
	}
}
