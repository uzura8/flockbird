<?php
namespace Admin;

class Controller_News_Category extends Controller_Admin {

	public function before()
	{
		parent::before();
	}

	/**
	 * The show_options action.
	 * 
	 * @access  public
	 * @return  void
	 */
	public function action_index()
	{		
		$news_category = \News\Model_NewsCategory::forge();
		$val = \Validation::forge()->add_model($news_category);
		$news_categories = \News\Model_NewsCategory::get_all();

		$this->set_title_and_breadcrumbs(
			term('news.category.view', 'site.management'),
			array('admin/news' => term('news.view', 'site.management'))
		);
		$this->template->subtitle = \View::forge('news/category/_parts/list_subtitle');
		$this->template->post_footer = \View::forge('_parts/load_asset_files', array('type' => 'js', 'files' => array(
			'jquery-ui-1.10.3.custom.min.js',
			'util/jquery-ui.js',
		)));
		$this->template->content = \View::forge('news/category/list', array(
			'news_categories' => $news_categories,
			'val' => $val,
		));
	}

	/**
	 * The edit_all action.
	 * 
	 * @access  public
	 * @return  void
	 */
	public function action_edit_all()
	{		
		$news_categories = \News\Model_NewsCategory::get_all();

		$posted_vals = array();
		if (\Input::method() == 'POST')
		{
			try
			{
				\Util_security::check_csrf();
				$posted_vals = \Input::post('names');
				if (count($posted_vals) != count($news_categories)) throw new \httpinvalidinputexception;

				\DB::start_transaction();
				foreach ($news_categories as $news_category)
				{
					$value = $posted_vals[$news_category->id];
					if (!strlen($value)) throw new \httpinvalidinputexception('未入力の項目があります。');
					if ($value !== $news_category->name)
					{
						$news_category->name = $value;
						$news_category->save();
					}
				}
				\DB::commit_transaction();

				\Session::set_flash('message', term('news.category.view').'を編集しました。');
				\Response::redirect('admin/news/category');
			}
			catch(\FuelException $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				\Session::set_flash('error', $e->getMessage());
			}
		}
		$vals = array();
		foreach ($news_categories as $news_category)
		{
			$vals[$news_category->id] = isset($posted_vals[$news_category->id]) ? $posted_vals[$news_category->id] : $news_category->name;
		}

		$this->set_title_and_breadcrumbs(
			term('news.category.view', 'form.edit'),
			array(
				'admin/news' => term('news.view', 'site.management'),
				'admin/news/category' => term('news.category.view', 'site.management'),
			)
		);
		$this->template->content = \View::forge('news/category/edit_all', array(
			'vals' => $vals,
			'news_categories' => $news_categories
		));
	}
}

/* End of file category.php */
