<?php

class Fieldset extends \Fuel\Core\Fieldset
{
	public static function reset()
	{
		parent::$_instances = array();
	}
}
