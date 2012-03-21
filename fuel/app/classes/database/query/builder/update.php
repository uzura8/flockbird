<?php

class Database_Query_Builder_Update extends Fuel\Core\Database_Query_Builder_Update
{
	public function __construct($table = NULL)
	{
		if ($table)
		{
			// Set the inital table name
			$this->_table = $table;
		}

		if (\DBUtil::field_exists($table, array('updated_at')))
		{
			$this->set(array('updated_at' => date('Y-m-d H;i;s')));
		}

		// Start the query with no SQL
		return parent::__construct('', \DB::UPDATE);
	}
}
