<?php
namespace Admin;

class Controller_News_Category_Api extends Controller_Admin
{
	public function before()
	{
		parent::before();
	}

	/**
	 * Api post_create
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function post_create()
	{
		if (!in_array($this->format, array('html', 'json'))) throw new \HttpNotFoundException();

		$response = array('status' => 0);
		try
		{
			\Util_security::check_csrf();
			$news_category = \News\Model_NewsCategory::forge();

			// Lazy validation
			$name = trim(\Input::post('name', ''));
			if (!strlen($name)) throw new \HttpInvalidInputException;

			$news_category->name = $name;
			$news_category->sort_order = \Site_Model::get_next_sort_order('news_category', 'News');

			\DB::start_transaction();
			$news_category->save();
			\DB::commit_transaction();

			$status_code = 200;
			if ($this->format == 'html')
			{
				$response = \View::forge('news/category/_parts/table_row', array('news_category' => $news_category));
				return \Response::forge($response, $status_code);
			}
			else
			{
				$response['status'] = 1;
				$response['id'] = $news_category->id;
			}
		}
		catch(\FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}

	/**
	 * Api post delete
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function post_delete()
	{
		$response = array('status' => 0);
		try
		{
			\Util_security::check_csrf();
			$news_category_id = (int)\Input::post('id');
			if (!$news_category_id || !$news_category = \News\Model_NewsCategory::find($news_category_id))
			{
				throw new \HttpNotFoundException;
			}

			\DB::start_transaction();
			$news_category->delete();
			\DB::commit_transaction();

			$response['status'] = 1;
			$status_code = 200;
		}
		catch(\FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}

	/**
	 * Api post_update
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function post_update($field = null)
	{
		$response = array('status' => 0);
		try
		{
			$accepts_fields = array('sort_order');
			if (!$field || !in_array($field, $accepts_fields)) throw new \HttpNotFoundException;
			\Util_security::check_csrf();

			\DB::start_transaction();
			$method = 'update_'.$field;
			$this->$method();
			\DB::commit_transaction();

			$response['status'] = 1;
			$status_code = 200;
		}
		catch(\FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}

	private function update_sort_order()
	{
		if (!$profile_option_ids = \Util_Array::cast_values(explode(',', \Input::post('ids')), 'int', true))
		{
			throw new \HttpInvalidInputException('Invalid input data.');
		}
		\Site_Model::update_sort_order($profile_option_ids, \News\Model_NewsCategory::forge());
	}
}
