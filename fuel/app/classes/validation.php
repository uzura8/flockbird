<?php

class Validation extends Fuel\Core\Validation
{
	protected function __construct($fieldset)
	{
		parent::__construct($fieldset);
	}

	/**
	 * Filter space char (within double byte space)
	 *
	 * @param   string
	 * @return  string
	 */
	public static function _validation_trim($val)
	{
		$val = preg_replace('/^[\s　]+/u', '', $val);
		$val = preg_replace('/[\s　]+$/u', '', $val);

		return $val;
	}

	/**
	 * Validate if there is no controll char
	 *
	 * @param   string
	 * @return  true|Exception
	 */
	public static function _validation_no_controll($val, $is_accept_line_and_tab = false)
	{
		$accept_char = '[:^cntrl:]';
		if ($is_accept_line_and_tab) $accept_char .= '\r\n\t';
		if (preg_match('/\A['.$accept_char.']*\z/u', $val) === 1)
		{
			return true;
		}
		else
		{
			\Log::error(
				'Invalid controll charactors: '.
				\Input::uri().' '.
				urlencode($val).' '.
				\Input::ip().
				' "'.\Input::user_agent().'"'
			);
			throw new HttpInvalidInputException('Invalid input data');
		}
	}
	
	/**
	 * Validate for select, radio, checkbox
	 *
	 * @param   string|array
	 * @param   array  valid options
	 * @return  true|Exception
	 */
	public static function _validation_in_array($val, $compare)
	{
		if (Validation::_empty($val))
		{
			return true;
		}
		
		if ( ! is_array($val))
		{
			$val = array($val);
		}
		
		foreach ($val as $value)
		{
			if ( ! in_array($value, $compare))
			{
				throw new HttpInvalidInputException('Invalid input data');
			}
		}

		return true;
	}
	
	/**
	 * Validate for not required array input
	 *
	 * @param   null|array
	 * @param   array  valid options
	 * @return  true|array
	 */
	public static function _validation_not_required_array($val)
	{
		if (is_array($val))
		{
			return true;
		}
		else
		{
			return array();
		}
	}

	/**
	 * Validate if there is alpha_numeric small character
	 *
	 * @param   string
	 * @return  bool
	 */

	public static function _validation_public_flag($val)
	{
		return is_numeric($val) && in_array($val, Site_Util::get_public_flags());
	}
	public static function _validation_alpha_small_char_numeric($val)
	{
		return (bool)preg_match('/^[a-z0-9]*$/', $val);
	}

	/**
	 * Match specific other submitted field string value
	 * (must be both strings, check is type sensitive)
	 *
	 * @param   string
	 * @param   string
	 * @return  bool
	 */
	public function _validation_unmatch_field($val, $field)
	{
		return $this->_empty($val) || $this->input($field) !== $val;
	}

	public function _validation_datetime_except_second($val, $delimiter = '-')
	{
		if (empty($val)) return true;// if $val is empty, uncheck;

		$dlt = '\\'.$delimiter;
		$pattern = '#^([12]{1}[0-9]{3})'.$dlt.'([0-9]{2})'.$dlt.'([0-9]{2}) ([0-9]{2}):([0-9]{2})$#';
		if (!preg_match($pattern, $val, $matches)) return false;

		$year  = (int)$matches[1];
		$month = (int)$matches[2];
		$date  = (int)$matches[3];
		if (!checkdate($month, $date, $year)) return false;

		$hour   = (int)$matches[4];
		$minute = (int)$matches[5];
		if ($hour < 0 || $hour > 23)     return false;
		if ($minute < 0 || $minute > 60) return false;

		return true;
	}

	public function _validation_datetime_range($val, $time_range = array())
	{
		if (empty($val)) return true;// if $val is empty, uncheck;

		if (!$time = strtotime($val)) return false;

		$min_time = (empty($time_range['min'])) ? Config::get('site.posted_value_rule_default.time.default.min', strtotime('- 120 years')) : strtotime($time_range['min']);
		if ($time < $min_time) return false;

		$max_time = (empty($time_range['max'])) ? Config::get('site.posted_value_rule_default.time.default.max', strtotime('+ 50 years')) : strtotime($time_range['max']);
		if ($time > $max_time) return false;

		return true;
	}

	public function _validation_datetime_is_past($val, $base = '', $min = '')
	{
		if (empty($val)) return true;// if $val is empty, uncheck;

		return Util_Date::check_is_past($val, $base, $min ?: Config::get('site.posted_value_rule_default.time.default.min'));
	}

	public function _validation_datetime_is_future($val, $base = '', $max = '', $is_return_time = false)
	{
		if (empty($val)) return true;// if $val is empty, uncheck;

		return Util_Date::check_is_futer($val, $base, $max ?: Config::get('site.posted_value_rule_default.time.default.max'));
	}
}
