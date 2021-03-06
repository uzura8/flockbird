<?php
namespace Admin;

class Controller_Content_Page extends Controller_Admin
{
	protected $check_not_auth_action = array();

	public function before()
	{
		parent::before();
	}

	/**
	 * The index action.
	 * 
	 * @access  public
	 * @return  void
	 */
	public function action_index()
	{		
		$data = array();
		list($data['list'], $data['pagination']) = \Site_Model::get_pagenation_list('content_page');
		$this->set_title_and_breadcrumbs(
			term('content.page', 'site.management'),
			array('admin/content' => term('site.content', 'site.management'))
		);
		$this->template->subtitle = \View::forge('_parts/subtitle_edit', array('uri' => 'admin/content/page/create'));
		$this->template->content = \View::forge('content/page/list', $data);
	}

	/**
	 * The list action.
	 * 
	 * @access  public
	 * @return  void
	 */
	Public function action_list()
	{	
		$this->action_index();
	}

	/**
	 * Actin detail
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_detail($id = null)
	{
		$content_page = \Content\Model_ContentPage::check_authority($id);
		$this->set_title_and_breadcrumbs(
			$content_page->title,
			array(
				'admin/content' => term('site.content', 'site.management'),
				'admin/content/page' => term('content.page', 'site.management'),
			)
		);
		$this->template->subtitle = \View::forge('content/page/_parts/detail_subtitle', array('content_page' => $content_page));
		$this->template->content = \View::forge('content/page/detail', array('content_page' => $content_page));
		if (\Content\Site_Util::check_editor_enabled()) $this->template->content->set_safe('html_body', $content_page->body);
	}

	/**
	 * Actin create
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_create()
	{
		$content_page = \Content\Model_ContentPage::forge();
		$val = \Validation::forge();
		$val->add_model($content_page);

		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();
			$error_message = '';
			try
			{
				if (!$val->run()) throw new \FuelException($val->show_errors());
				$post = $val->validated();
				if ($post['format'] == 2) $post['body'] = preg_replace('/^\&gt;/um', '>', $post['body']);
				$content_page->set_values($post);
				$content_page->admin_user_id = $this->u->id;
				\DB::start_transaction();
				$content_page->save();
				\DB::commit_transaction();
				$message = sprintf('%sを%sしました。', term('content.page'), term('form.create_simple'));
				\Session::set_flash('message', $message);
				\Response::redirect('admin/content/page/detail/'.$content_page->id);
			}
			catch(\Database_Exception $e)
			{
				$error_message = \Site_Controller::get_error_message($e, true);
			}
			catch(\FuelException $e)
			{
				$error_message = $e->getMessage();
			}
			if ($error_message)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				\Session::set_flash('error', $error_message);
			}
		}

		$this->set_title_and_breadcrumbs(
			term('content.page', 'form.create'),
			array(
				'admin/content' => term('site.content', 'site.management'),
				'admin/content/page' => term('content.page', 'site.management'),
			)
		);
		$this->template->post_header = \View::forge('content/page/_parts/form_header');
		$this->template->post_footer = \View::forge('content/page/_parts/form_footer');
		$this->template->content = \View::forge('content/page/_parts/form', array('val' => $val));
	}

	/**
	 * Action edit
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_edit($id = null)
	{
		$content_page = \Content\Model_ContentPage::check_authority($id);
		$val = \Validation::forge();
		$val->add_model($content_page);

		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();
			$error_message = '';
			try
			{
				// 識別名の変更がない場合は unique を確認しない
				if (trim(\Input::post('slug')) == $content_page->slug) $val->fieldset()->field('slug')->delete_rule('unique');

				if (!$val->run()) throw new \FuelException($val->show_errors());
				$post = $val->validated();
				if ($post['format'] == 2) $post['body'] = preg_replace('/^\&gt;/um', '>', $post['body']);
				$content_page->set_values($post);
				\DB::start_transaction();
				$content_page->save();
				\DB::commit_transaction();

				$message = sprintf('%sを%sしました。', term('content.page'), term('form.edit'));
				\Session::set_flash('message', $message);
				\Response::redirect('admin/content/page/detail/'.$content_page->id);
			}
			catch(\Database_Exception $e)
			{
				$error_message = \Site_Controller::get_error_message($e, true);
			}
			catch(\FuelException $e)
			{
				$error_message = $e->getMessage();
			}
			if ($error_message)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				\Session::set_flash('error', $error_message);
			}
		}

		$this->set_title_and_breadcrumbs(
			term('content.page', 'form.edit'),
			array(
				'admin/content' => term('site.content', 'site.management'),
				'admin/content/page' => term('content.page', 'site.management'),
			)
		);
		$this->template->post_header = \View::forge('content/page/_parts/form_header');
		$this->template->post_footer = \View::forge('content/page/_parts/form_footer');
		$this->template->content = \View::forge('content/page/_parts/form', array(
			'val' => $val,
			'content_page' => $content_page,
			'is_edit' => true,
		));
	}

	/**
	 * News delete
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_delete($id = null)
	{
		\Util_security::check_method('POST');
		\Util_security::check_csrf();
		$content_page = \Content\Model_ContentPage::check_authority($id);
		$error_message = '';
		try
		{
			\DB::start_transaction();
			$content_page->delete();
			\DB::commit_transaction();
			\Session::set_flash('message', term('content.page').'を削除しました。');
		}
		catch(\Database_Exception $e)
		{
			$error_message = \Site_Controller::get_error_message($e, true);
		}
		catch(\FuelException $e)
		{
			$error_message = $e->getMessage();
		}
		if ($error_message)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			\Session::set_flash('error', $error_message);
		}

		\Response::redirect(\Site_Util::get_redirect_uri('admin/content/page'));
	}
}

/* End of page.php */
