<?php
namespace Admin;

class Controller_News_Category_Api extends Controller_Api
{
	protected $check_not_auth_action = array();

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
			$name  = trim(\Input::post('name', ''));
			$label = trim(\Input::post('label', ''));
			if (!strlen($name) || !strlen($label)) throw new \HttpInvalidInputException;

			$news_category->name       = $name;
			$news_category->label      = $label;
			$news_category->sort_order = \News\Model_NewsCategory::get_next_sort_order();

			\DB::start_transaction();
			$news_category->save();
			\DB::commit_transaction();

			$status_code = 200;
			if ($this->format == 'html')
			{
				$response = \View::forge('_parts/table/simple_row_sortable', array(
					'id' => $news_category->id,
					'name' => $news_category->name,
					'label' => $news_category->label,
					'delete_uri' => sprintf('admin/news/category/api/delete/%s.json', $news_category->id),
					'edit_uri' => sprintf('admin/news/category/edit/%d', $news_category->id),
				));

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
	public function post_delete($news_category_id = null)
	{
		$response = array('status' => 0);
		try
		{
			\Util_security::check_csrf();
			$news_category_id = (int)$news_category_id;
			if (\Input::post('id')) $news_category_id = (int)\Input::post('id');
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
		catch(\HttpNotFoundException $e)
		{
			$status_code = 404;
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
		$this->common_post_update($field, array('sort_order'));
	}

	protected function update_sort_order()
	{
		if (!$ids = \Util_Array::cast_values(explode(',', \Input::post('ids')), 'int', true))
		{
			throw new \HttpInvalidInputException('Invalid input data.');
		}
		\Site_Model::update_sort_order($ids, \News\Model_NewsCategory::forge());
	}
}
