<?php
namespace Admin;

class Controller_Content_Image extends Controller_Admin
{
	protected $check_not_auth_action = array();

	public function before()
	{
		parent::before();
	}

	/**
	 * Index action.
	 * 
	 * @access  public
	 * @return  void
	 */
	public function action_index()
	{	
		$this->set_title_and_breadcrumbs(
			term('site.image', 'site.management'),
			array('admin/content' => term('site.content', 'site.management'))
		);
		$this->template->subtitle = \View::forge('content/image/_parts/list_subtitle');
		$this->template->post_footer = \View::forge('_parts/load_masonry');

		list($limit, $page) = $this->common_get_pager_list_params(\Config::get('admin.articles.images.limit'), \Config::get('admin.articles.images.limit_max'));
		$data = \Model_SiteImage::get_pager_list(array(
			'order_by' => array('id' => 'desc'),
			'limit'    => $limit,
		), $page);
		$this->template->content = \View::forge('content/image/_parts/list', $data);
	}

	/**
	 * List action.
	 * 
	 * @access  public
	 * @return  void
	 */
	public function action_list()
	{
		$this->action_index();
	}

	/**
	 * Detail action.
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_detail($id = null)
	{	
		$id = (int)$id;
		$site_image = \Model_SiteImage::check_authority($id);
		$data = array('site_image' => $site_image);

		// 前後の id の取得
		$ids = \Model_SiteImage::get_col_array('id', array('order_by' => array('id' => 'desc')));
		list($data['before_id'], $data['after_id']) = \Util_Array::get_neighborings($id, $ids);

		$this->set_title_and_breadcrumbs(
			term('site.image', 'site.detail'),
			array(
				'admin/content' => term('site.content', 'site.management'),
				'admin/content/image' => term('site.image', 'site.management'),
			)
		);
		$this->template->layout = 'wide';
		$this->template->subtitle = \View::forge('content/image/_parts/detail_subtitle', array('site_image' => $site_image));
		$this->template->post_footer = \View::forge('_parts/load_masonry');
		$this->template->content = \View::forge('content/image/detail', $data);
	}

	/**
	 * Upload action
	 * @access  public
	 * @return  Response
	 */
	public function action_upload()
	{
		$files = array();
		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();

			$file_tmps = array();
			$moved_files = array();
			try
			{
				//if (!$val->run()) throw new \FuelException($val->show_errors());
				$file_tmps = \Site_FileTmp::get_file_tmps_uploaded($this->u->id, true);
				//\Site_FileTmp::check_uploaded_under_accepted_filesize($file_tmps, $this->u->filesize_total, \Site_Upload::get_accepted_filesize());

				\DB::start_transaction();
				list($moved_files, $site_image_ids) = \Site_FileTmp::save_images($file_tmps, $this->u->id, 'admin_user_id', 'site_image');
				\DB::commit_transaction();

				// thumbnail 作成 & tmp_file thumbnail 削除
				\Site_FileTmp::make_and_remove_thumbnails($moved_files);

				$message = sprintf('%sをアップロードしました。', term('site.image'));
				\Session::set_flash('message', $message);
				\Response::redirect('admin/content/image');
			}
			catch(\FuelException $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				if ($moved_files) \Site_FileTmp::move_files_to_tmp_dir($moved_files);
				$files = \Site_FileTmp::get_file_objects($file_tmps, $this->u->id);

				\Session::set_flash('error', $e->getMessage());
			}
		}

		$this->template->post_header = \View::forge('filetmp/_parts/upload_header');
		$this->template->post_footer = \View::forge('_parts/form/upload_footer');
		$this->set_title_and_breadcrumbs(
			term('site.image', 'form.upload'),
			array(
				'admin/content' => term('site.content', 'site.management'),
				'admin/content/image' => term('site.image', 'site.management'),
			)
		);
		$this->template->content = \View::forge('_parts/form/upload', array('files' => $files));
	}

	/**
	 * Action image delete
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_delete($id = null)
	{
		\Util_security::check_csrf();
		$site_image = \Model_SiteImage::check_authority($id);
		try
		{
			\DB::start_transaction();
			$site_image->delete();
			\DB::commit_transaction();

			\Session::set_flash('message', term('site.image').'を削除しました。');
			\Response::redirect('admin/content/image');
		}
		catch(\Database_Exception $e)
		{
			$error_message = \Site_Controller::get_error_message($e, true);
		}
		catch (Exception $e)
		{
			$error_message = $e->getMessage();
		}
		if (\DB::in_transaction()) \DB::rollback_transaction();
		\Session::set_flash('error', $error_message);

		\Response::redirect('admin/content/image/'.$id);
	}
}

/* End of file image.php */
