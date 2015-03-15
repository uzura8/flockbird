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
	 * Create category
	 * 
	 * @access  public
	 * @return  Response(json|html)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see     Controller_Base::controller_common_api
	 */
	public function post_create()
	{
		$this->api_accept_formats = array('html', 'json');
		$this->controller_common_api(function()
		{
			$news_category = \News\Model_NewsCategory::forge();
			// Lazy validation
			$name  = trim(\Input::post('name', ''));
			$label = trim(\Input::post('label', ''));
			if (!strlen($name) || !strlen($label)) throw new \ValidationFailedException('入力してください。');
			$news_category->name  = $name;
			$news_category->label = $label;

			\DB::start_transaction();
			$news_category->sort_order = \News\Model_NewsCategory::get_next_sort_order();
			$result = (bool)$news_category->save();
			\DB::commit_transaction();
			$data = array(
				'result' => $result,
				'id' => $news_category->id,
			);
			if ($this->format == 'html')
			{
				$data += array(
					'name' => $news_category->name,
					'label' => $news_category->label,
					'delete_uri' => sprintf('admin/news/category/api/delete/%s.json', $news_category->id),
					'edit_uri' => sprintf('admin/news/category/edit/%d', $news_category->id),
				);
			}
			$this->set_response_body_api($data, $this->format == 'html' ? '_parts/table/simple_row_sortable' : null);
		});
	}

	/**
	 * Delete category
	 * 
	 * @access  public
	 * @param   int  $id  target id
	 * @return  Response(json)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see     Controller_Base::api_delete_common
	 */
	public function post_delete($id = null)
	{
		$this->api_delete_common('news_category', $id, null, term('news.category.view'));
	}

	/**
	 * Update category
	 * 
	 * @access  public
	 * @param   string  $field  Edit field
	 * @return  Response(json)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see     \Admin\Controller_Api::common_post_update
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

		return \Site_Model::update_sort_order($ids, \News\Model_NewsCategory::forge());
	}
}
