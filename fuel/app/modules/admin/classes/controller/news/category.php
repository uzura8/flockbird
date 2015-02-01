<?php
namespace Admin;

class Controller_News_Category extends Controller_Admin
{
	protected $check_not_auth_action = array();

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
	 * News category edit
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_edit($id = null)
	{
		$news_category = \News\Model_NewsCategory::check_authority($id);
		$val = \Validation::forge()->add_model($news_category);

		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();
			try
			{
				// 識別名の変更がない場合は unique を確認しない
				if (trim(\Input::post('name')) == $news_category->name) $val->fieldset()->field('name')->delete_rule('unique');
				if (!$val->run()) throw new \FuelException($val->show_errors());

				$post = $val->validated();
				$news_category->name  = $post['name'];
				$news_category->label = $post['label'];
				\DB::start_transaction();
				$news_category->save();
				\DB::commit_transaction();

				\Session::set_flash('message', sprintf('%sを%sしました。', term('news.category.view'), term('form.edit')));
				\Response::redirect('admin/news/category');
			}
			catch(\FuelException $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				\Session::set_flash('error', $e->getMessage());
			}
		}

		$this->set_title_and_breadcrumbs(term('news.category.view', 'form.edit'), array(
			'admin/news' => term('news.view', 'admin.view'),
			'admin/news/category' => term('news.category.view')
		));
		$this->template->post_header = \View::forge('news/_parts/form_header');
		$this->template->post_footer = \View::forge('news/_parts/form_footer');
		$this->template->content = \View::forge('news/category/edit', array('val' => $val, 'news' => $news_category));
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
				$posted_vals = \Input::post('labels');
				if (count($posted_vals) != count($news_categories)) throw new \httpinvalidinputexception;

				\DB::start_transaction();
				foreach ($news_categories as $news_category)
				{
					$value = $posted_vals[$news_category->id];
					if (!strlen($value)) throw new \httpinvalidinputexception('未入力の項目があります。');
					if ($value !== $news_category->label)
					{
						$news_category->label = $value;
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
			$vals[$news_category->id] = isset($posted_vals[$news_category->id]) ? $posted_vals[$news_category->id] : $news_category->label;
		}

		$this->set_title_and_breadcrumbs(
			term('news.view', 'news.category.label', 'form.edit_all'),
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
