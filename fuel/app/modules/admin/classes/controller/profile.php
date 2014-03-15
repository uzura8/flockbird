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
		$site_configs = \Model_SiteConfig::get4names_as_assoc(\Form_SiteConfig::get_names(array('profile_name', 'profile_sex', 'profile_birthday')));
		$labels = self::get_list_labels();
		$profiles = \Model_Profile::query()->order_by('sort_order')->get();
		$this->set_title_and_breadcrumbs(term('profile').'設定');
		$this->template->layout = 'wide';
		$this->template->post_footer = \View::forge('profile/_parts/index_footer');
		$this->template->content = \View::forge('profile/list', array('profiles' => $profiles, 'site_configs' => $site_configs, 'labels' => $labels));
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
		$this->template->content = \View::forge('profile/_parts/form', array('val' => $val, 'profile' => $profile, 'is_edit' => true));
	}

	/**
	 * The edit_name action.
	 * 
	 * @access  public
	 * @return  void
	 */
	public function action_edit_name()
	{	
		$val = \Form_SiteConfig::get_validation('profile_name', true);
		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();

			try
			{
				if (!$val->run()) throw new \FuelException($val->show_errors());
				$post = $val->validated();
				\DB::start_transaction();
				\Form_SiteConfig::save($val, $post);
				\DB::commit_transaction();

				\Session::set_flash('message', term('member.name').'を変更しました。');
				\Response::redirect('admin/profile');
			}
			catch(\FuelException $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				\Session::set_flash('error', $e->getMessage());
			}
		}
		$this->set_title_and_breadcrumbs(term('member.name').'設定');
		$this->template->content = \View::forge('profile/_parts/form_basic', array('val' => $val));
	}

	/**
	 * The edit_sex action.
	 * 
	 * @access  public
	 * @return  void
	 */
	public function action_edit_sex()
	{	
		$val = \Form_SiteConfig::get_validation('profile_sex', true);
		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();

			try
			{
				if (!$val->run()) throw new \FuelException($val->show_errors());
				$post = $val->validated();
				\DB::start_transaction();
				\Form_SiteConfig::save($val, $post);
				\DB::commit_transaction();

				\Session::set_flash('message', term('member.sex').'を変更しました。');
				\Response::redirect('admin/profile');
			}
			catch(\FuelException $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				\Session::set_flash('error', $e->getMessage());
			}
		}
		$this->set_title_and_breadcrumbs(term('member.sex').'設定');
		$this->template->content = \View::forge('profile/_parts/form_basic', array('val' => $val));
	}

	/**
	 * The edit_birthday action.
	 * 
	 * @access  public
	 * @return  void
	 */
	public function action_edit_birthday()
	{	
		$val = \Form_SiteConfig::get_validation('profile_birthday', true);
		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();

			try
			{
				if (!$val->run()) throw new \FuelException($val->show_errors());
				$post = $val->validated();
				\DB::start_transaction();
				\Form_SiteConfig::save($val, $post);
				\DB::commit_transaction();

				\Session::set_flash('message', term('member.birthday').'を変更しました。');
				\Response::redirect('admin/profile');
			}
			catch(\FuelException $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				\Session::set_flash('error', $e->getMessage());
			}
		}
		$this->set_title_and_breadcrumbs(term('member.birthday').'設定');
		$this->template->content = \View::forge('profile/_parts/form_basic', array('val' => $val));
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

	/**
	 * The edit_options action.
	 * 
	 * @access  public
	 * @return  void
	 */
	public function action_edit_options($id = null)
	{		
		if (!$id || !$profile = \Model_Profile::find($id))
		{
			throw new \HttpNotFoundException;
		}
		if (!in_array($profile->form_type, \Site_Profile::get_form_types_having_profile_options()))
		{
			throw new \HttpInvalidInputException;
		}
		$profile_options = \Model_ProfileOption::get4profile_id($id);

		$posted_vals = array();
		if (\Input::method() == 'POST')
		{
			try
			{
				\Util_security::check_csrf();
				$posted_vals = \Input::post('labels');
				if (count($posted_vals) != count($profile_options)) throw new \httpinvalidinputexception;

				\DB::start_transaction();
				foreach ($profile_options as $profile_option)
				{
					$value = $posted_vals[$profile_option->id];
					if (!strlen($value)) throw new \httpinvalidinputexception('未入力の項目があります。');
					if ($value !== $profile_option->label)
					{
						$profile_option->label = $value;
						$profile_option->save();
					}
				}
				\DB::commit_transaction();

				\Session::set_flash('message', term('profile').'選択肢を編集しました。');
				\Response::redirect('admin/profile/show_options/'.$profile->id);
			}
			catch(\FuelException $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				\Session::set_flash('error', $e->getMessage());
			}
		}
		$vals = array();
		foreach ($profile_options as $profile_option)
		{
			$vals[$profile_option->id] = isset($posted_vals[$profile_option->id]) ? $posted_vals[$profile_option->id] : $profile_option->label;
		}

		$this->set_title_and_breadcrumbs(sprintf('%s 編集: %s', term('profile'), $profile->caption));
		$this->template->content = \View::forge('profile/edit_options', array(
			'profile' => $profile,
			'vals' => $vals,
			'profile_options' => $profile_options
		));
	}

	private static function get_list_labels()
	{	
		$cols = array(
			'caption',
			'name',
			'display_type',
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
		if (!isset($obj->sort_order) || is_null($obj->sort_order)) $obj->sort_order = \Site_Model::get_next_sort_order('profile');

		return $obj;
	}
}

/* End of file profile.php */
