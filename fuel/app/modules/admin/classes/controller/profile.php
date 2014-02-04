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
		$this->action_list();
	}

	/**
	 * The list action.
	 * 
	 * @access  public
	 * @return  void
	 */
	public function action_list()
	{	
		$labels = self::get_list_labels();
		$profiles = \Model_Profile::query()->order_by('sort_order')->get();
		$this->set_title_and_breadcrumbs(term('profile').'項目一覧');
		$this->template->layout = 'wide';
		$this->template->content = \View::forge('profile/list', array('profiles' => $profiles, 'labels' => $labels));
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
		$this->template->content = \View::forge('profile/_parts/form', array('val' => $val));
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
		$obj->sort_order = \Site_Model::get_next_sort_order('profile');

		return $obj;
	}
}

/* End of file profile.php */
