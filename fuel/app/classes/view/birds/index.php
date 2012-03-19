<?php

use \Model\Birds;

/**
 * The Birds Index view model.
 *
 * @package  app
 * @extends  ViewModel
 */
class View_Birds_Index extends ViewModel
{
	/**
	 * Prepare the view data, keeping this in here helps clean up
	 * the controller.
	 * 
	 * @return void
	 */
	public function view()
	{
		$this->initial_syllabary_list = self::get_initial_syllabary_list();
		foreach ($this->initial_syllabary_list as $key => $row)
		{
			$var_name = 'birds_list50'.$key;
			$this->$var_name = Birds::get_result_array4syllabary_range($row['initial'], array('name', 'url', 'img'));
		}
	}

	private static function get_initial_syllabary_list()
	{
		return array(
			'A' => array('initial' => 'ア', 'view' => 'ア'),
			'K' => array('initial' => 'カ', 'view' => 'カ'),
			'S' => array('initial' => 'サ', 'view' => 'サ'),
			'T' => array('initial' => 'タ', 'view' => 'タ'),
			'N' => array('initial' => 'ナ', 'view' => 'ナ'),
			'H' => array('initial' => 'ハ', 'view' => 'ハ'),
			'M' => array('initial' => 'マ', 'view' => 'マ'),
			'Y' => array('initial' => 'ヤ', 'view' => 'ヤ・ラ・ワ'),
		);
	}
}
