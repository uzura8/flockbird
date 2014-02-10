<?php
namespace Admin;

class Controller_Profile extends Controller_Admin {

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
		$labels = self::get_list_labels();
		$profiles = \Model_Profile::query()->order_by('sort_order')->get();
		$this->set_title_and_breadcrumbs(term('profile').'項目一覧');
		$this->template->layout = 'wide';
		$this->template->post_footer = \View::forge('profile/_parts/index_footer');
		$this->template->content = \View::forge('profile/list', array('profiles' => $profiles, 'labels' => $labels));
	}

	/**
	 * The list action.
	 * 
	 * @access  public
	 * @return  void
	 */
	public function action_list()
	{	
		$this->action_index();
	}

	/**
	 * The create action.
	 * 
	 * @access  public
	 * @return  void
	 */
	public function action_create()
	{	
		$profile = \Model_Profile::forge();
		$val = \Validation::forge()->add_model($profile);
		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();

			try
			{
				if (!$val->run()) throw new \FuelException($val->show_errors());
				$post = $val->validated();
				$profile = $this->set_values_profile($profile, $post);
				\DB::start_transaction();
				$profile->save();
				\DB::commit_transaction();

				\Session::set_flash('message', term('profile').'項目を作成しました。');
				\Response::redirect('admin/profile');
			}
			catch(\FuelException $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				\Session::set_flash('error', $e->getMessage());
			}
		}

		$this->set_title_and_breadcrumbs(term('profile').'項目作成');
		$this->template->layout = 'wide';
		$this->template->post_footer = \View::forge('_parts/load_asset_files', array('type' => 'js', 'files' => 'site/modules/admin/profile/common/form.js'));
		$this->template->content = \View::forge('profile/_parts/form', array('val' => $val));
	}

	/**
	 * The edit action.
	 * 
	 * @access  public
	 * @return  void
	 */
	public function action_edit($id = null)
	{	
		if (!$id || !$profile = \Model_Profile::find($id))
		{
			throw new \HttpNotFoundException;
		}
		$val = \Validation::forge()->add_model($profile);

		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();

			try
			{
				// 識別名の変更がない場合は unique を確認しない
				if (trim(\Input::post('name')) == $profile->name) $val->fieldset()->field('name')->delete_rule('unique');

				if (!$val->run()) throw new \FuelException($val->show_errors());
				$post = $val->validated();
				$profile = $this->set_values_profile($profile, $post);
				\DB::start_transaction();
				$profile->save();
				\DB::commit_transaction();

				\Session::set_flash('message', term('profile').'項目を変更しました。');
				\Response::redirect('admin/profile');
			}
			catch(\FuelException $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				\Session::set_flash('error', $e->getMessage());
			}
		}

		$this->set_title_and_breadcrumbs(term('profile').'項目編集');
		$this->template->layout = 'wide';
		$this->template->post_footer = \View::forge('_parts/load_asset_files', array('type' => 'js', 'files' => 'site/modules/admin/profile/common/form.js'));
		$this->template->content = \View::forge('profile/_parts/form', array('val' => $val, 'profile' => $profile));
	}

	/**
	 * The delete action.
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_delete($id = null)
	{
		\Util_security::check_csrf();

		if (!$id || !$profile = \Model_Profile::find($id))
		{
			throw new \HttpNotFoundException;
		}
		try
		{
			\DB::start_transaction();
			$profile->delete();
			\DB::commit_transaction();
			\Session::set_flash('message', term('profile').'を削除しました。');
		}
		catch(\FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			\Session::set_flash('error', $e->getMessage());
		}

		\Response::redirect('admin/profile');
	}

	/**
	 * The show_options action.
	 * 
	 * @access  public
	 * @return  void
	 */
	public function action_show_options($id = null)
	{		
		if (!$id || !$profile = \Model_Profile::find($id))
		{
			throw new \HttpNotFoundException;
		}
		if (!in_array($profile->form_type, \Site_Profile::get_form_types_having_profile_options()))
		{
			throw new \HttpInvalidInputException;
		}
		$val = \Validation::forge()->add_model($profile);
		$profile_options = \Model_ProfileOption::get4profile_id($id);

		$this->set_title_and_breadcrumbs(sprintf('%s選択肢一覧: %s', term('profile'), $profile->caption));
		$this->template->post_footer = \View::forge('_parts/load_asset_files', array('type' => 'js', 'files' => array(
			'jquery-ui-1.10.3.custom.min.js',
			'util/jquery-ui.js',
		)));
		$this->template->content = \View::forge('profile/show_options', array(
			'profile' => $profile,
			'val' => $val,
			'profile_options' => $profile_options
		));
	}

	private static function get_list_labels()
	{	
		$cols = array(
			'caption',
			'name',
			'is_required',
			'is_edit_public_flag',
			'default_public_flag',
			'is_unique',
			'form_type',
			'is_disp_regist',
			'is_disp_config',
			'is_disp_search',
		);
		$titles = array();
		$val = \Validation::forge()->add_model(\Model_Profile::forge());
		foreach ($cols as $col)
		{
			$titles[$col] = $val->fieldset()->field($col)->get_attribute('label');
		}

		return $titles;
	}

	private function set_values_profile(\Model_Profile $obj, $values)
	{	
		$cols = \DB::list_columns('profile');
		foreach ($cols as $col => $props)
		{
			if (in_array($col, array('id', 'sort_order', 'created_at', 'updated_at'))) continue;
			$obj->$col = $values[$col];	
		}
		if (!isset($obj->sort_order)) $obj->sort_order = \Site_Model::get_next_sort_order('profile');

		return $obj;
	}
}

/* End of file profile.php */
