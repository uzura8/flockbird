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
	public static function _validation_no_controll($val, $is_accept_line_and_tab = false, $is_throw_input_error = false)
	{
		$accept_char = '[:^cntrl:]';
		if ($is_accept_line_and_tab) $accept_char .= '\r\n\t';
		if (preg_match('/\A['.$accept_char.']*\z/u', $val) === 1)
		{
			return true;
		}
		elseif ($is_throw_input_error)
		{
			Util_Toolkit::log_error('Invalid control characters: '.urlencode($value));
			throw new HttpInvalidInputException('Invalid input data');
		}

		return false;
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
			if ( ! in_array($value, $compare)) return false;
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

		$min_time = (empty($time_range['min'])) ? conf('posted_value_rule_default.time.default.min', null, strtotime('- 120 years')) : strtotime($time_range['min']);
		if ($time < $min_time) return false;

		$max_time = (empty($time_range['max'])) ? conf('posted_value_rule_default.time.default.max', null, strtotime('+ 50 years')) : strtotime($time_range['max']);
		if ($time > $max_time) return false;

		return true;
	}

	public static function _validation_datetime_is_past($val, $base = '', $min = '')
	{
		if (empty($val)) return true;// if $val is empty, uncheck;

		return Util_Date::check_is_past($val, $base, $min ?: conf('posted_value_rule_default.time.default.min'));
	}

	public function _validation_datetime_is_future($val, $base = '', $max = '', $is_return_time = false)
	{
		if (empty($val)) return true;// if $val is empty, uncheck;

		return Util_Date::check_is_future($val, $base, $max ?: conf('posted_value_rule_default.time.default.max'));
	}

	public static function _validation_unique($val, $options, array $additional_conds_list = array())
	{
		list($table, $field) = explode('.', $options);
		if (!$table || !$field) throw new InvalidArgumentException("Second parameter must be format 'table.field'.");

		$query = DB::select(DB::expr("LOWER (\"$field\")"))
		->from($table)
		->where($field, '=', Str::lower($val));

		foreach ($additional_conds_list as $additional_conds)
		{
			$query = $query->and_where($additional_conds[0], $additional_conds[1]);
		}
		$result = $query->execute();

		return ! ($result->count() > 0);
	}

	public function _validation_date_string($val, $year_field = null, $delimiter = '-')
	{
		if (empty($val)) return true;// if $val is empty, uncheck;

		$date_items = Util_Date::sprit_date_str($val, true, $delimiter);
		$month = $date_items['month'];
		$date = $date_items['date'];

		$year = 2000;// 閏年の年を初期値としてセット
		if (!empty($date_items['year']))
		{
			$year = $date_items['year'];
		}
		elseif ($year_field && $this->input($year_field))
		{
			$year = $this->input($year_field);
		}

		if ($month < 1 || $month > 12) return false;
		if ($date < 1) return false;
		if ($date > Date::days_in_month($month, $year)) return false;

		return checkdate($month, $date, $year);
	}

	/**
	 * 値の正当性チェック
	 */
	public static function _validation_checkbox_val($val, $options)
	{
		if ($val) {
			if (!is_array($val)) {
				return false;
			}
			foreach ($val as $v) {
				if (!array_key_exists($v, $options)) return false;
			}
		}

		return true;
	}

	/**
	 * 必須チェック
	 *
	 * $minで最低チェック数を指定
	 */
	public static function _validation_checkbox_require($val, $min = null)
	{
		if (!$val || !is_array($val)) {
			return false;
		}
		$min_count = $min ? $min : 1;

		return count($val) >= $min_count;
	}
}
