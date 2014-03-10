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
		$site_configs = \Model_SiteConfig::get4names_as_assoc(self::get_site_configs_birthday_names());
		$this->set_title_and_breadcrumbs(term('profile').'項目一覧');
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
	 * The edit_birthday action.
	 * 
	 * @access  public
	 * @return  void
	 */
	public function action_edit_birthday()
	{	
		$val = self::get_validation4edit_birthday();
		$site_configs = self::get_site_configs_birthday();

		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();

			try
			{
				if (!$val->run()) throw new \FuelException($val->show_errors());
				$post = $val->validated();
				\DB::start_transaction();
				self::save_site_configs_birthday($site_configs, $post);
				\DB::commit_transaction();

				\Session::set_flash('message', '生年月日設定を変更しました。');
				\Response::redirect('admin/profile');
			}
			catch(\FuelException $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				\Session::set_flash('error', $e->getMessage());
			}
		}

		$this->set_title_and_breadcrumbs('生年月日設定');
		$this->template->content = \View::forge('profile/edit_birthday', array(
			'val' => $val,
			'site_configs' => $site_configs,
			'site_configs_form_config' => self::get_site_configs_birthday_form_config(),
		));
	}

	private static function get_validation4edit_birthday()
	{
		$val = \Validation::forge();

		$options_enable = array('0' => '無効', '1' => '有効');
		$val->add('profile_birthday_is_enable', '生年月日設定を有効にするか', array('type' => 'radio', 'options' => $options_enable))
				->add_rule('valid_string', 'numeric', 'required')
				->add_rule('in_array', array_keys($options_enable));

		$options_is_disp = array('1' => '表示する', '0' => '表示しない');
		$val->add('profile_birthday_is_disp_regist', '新規登録', array('type' => 'radio', 'options' => $options_is_disp))
				->add_rule('valid_string', 'numeric', 'required')
				->add_rule('in_array', array_keys($options_is_disp));

		$val->add('profile_birthday_is_disp_config', 'プロフィール変更', array('type' => 'radio', 'options' => $options_is_disp))
				->add_rule('valid_string', 'numeric', 'required')
				->add_rule('in_array', array_keys($options_is_disp));

		$val->add('profile_birthday_is_disp_search', 'メンバー検索', array('type' => 'radio', 'options' => $options_is_disp))
				->add_rule('valid_string', 'numeric', 'required')
				->add_rule('in_array', array_keys($options_is_disp));

		$val->add('profile_birthday_is_enable_generation_view', '年代表示を有効にするか', array('type' => 'radio', 'options' => $options_enable))
				->add_rule('valid_string', 'numeric')
				->add_rule('in_array', array_keys($options_enable));

		$options = array('0' => '10年単位', '1' => '5年単位');
		$val->add('profile_birthday_generation_unit', '年代区切り', array('type' => 'radio', 'options' => $options))
				->add_rule('valid_string', 'numeric')
				->add_rule('in_array', array_keys($options));

		$options = array('0' => '生年表示', '1' => '年齢表示');
		$val->add('profile_birthday_birthyear_view_type', '生年表示タイプ', array('type' => 'radio', 'options' => $options))
				->add_rule('valid_string', 'numeric')
				->add_rule('in_array', array_keys($options));

		$options_display_type = \Site_Profile::get_display_type_options();
		$val->add('profile_birthday_display_type_birthyear', '表示場所(生年)', array('type' => 'select', 'options' => $options_display_type))
				->add_rule('valid_string', 'numeric')
				->add_rule('in_array', array_keys($options_display_type));

		$options_is_edit_public_flag = array('0' => '固定', '1' => 'メンバー選択');
		$val->add('profile_birthday_is_edit_public_flag_birthyear', '公開設定の選択(生年)', array('type' => 'radio', 'options' => $options_is_edit_public_flag))
				->add_rule('valid_string', 'numeric')
				->add_rule('in_array', array_keys($options_is_edit_public_flag));

		$val->add('profile_birthday_default_public_flag_birthyear', '公開設定デフォルト値(生年)', \Site_Form::get_public_flag_configs())
				->add_rule('valid_string', 'numeric')
				->add_rule('in_array', \Site_Util::get_public_flags());

		$options_display_type = \Site_Profile::get_display_type_options();
		$val->add('profile_birthday_display_type_birthday', '表示場所(誕生日)', array('type' => 'select', 'options' => $options_display_type))
				->add_rule('valid_string', 'numeric')
				->add_rule('in_array', array_keys($options_display_type));

		$val->add('profile_birthday_is_edit_public_flag_birthday', '公開設定の選択(誕生日)', array('type' => 'radio', 'options' => $options_is_edit_public_flag))
				->add_rule('valid_string', 'numeric')
				->add_rule('in_array', array_keys($options_is_edit_public_flag));

		$val->add('profile_birthday_default_public_flag_birthday', '公開設定デフォルト値(誕生日)', \Site_Form::get_public_flag_configs())
				->add_rule('valid_string', 'numeric')
				->add_rule('in_array', \Site_Util::get_public_flags());

		return $val;
	}

	private static function get_site_configs_birthday_names()
	{
		return array_keys(self::get_site_configs_birthday_form_config());
	}

	private static function get_site_configs_birthday_form_config()
	{
		return array(
			'profile_birthday_is_enable' => array('form' => 'radio', 'default_value' => 0),
			'profile_birthday_is_disp_regist' => array('form' => 'radio', 'default_value' => 1),
			'profile_birthday_is_disp_config' => array('form' => 'radio', 'default_value' => 1),
			'profile_birthday_is_disp_search' => array('form' => 'radio', 'default_value' => 1),
			'profile_birthday_is_enable_generation_view' => array('form' => 'radio', 'default_value' => 0),
			'profile_birthday_generation_unit' => array('form' => 'radio', 'default_value' => 0),
			'profile_birthday_birthyear_view_type' => array('form' => 'radio', 'default_value' => 0),
			'profile_birthday_display_type_birthyear' => array('form' => 'select', 'default_value' => 0),
			'profile_birthday_is_edit_public_flag_birthyear' => array('form' => 'radio', 'default_value' => 0),
			'profile_birthday_default_public_flag_birthyear' => array('form' => 'public_flag', 'default_value' => PRJ_PUBLIC_FLAG_MEMBER),
			'profile_birthday_display_type_birthday' => array('form' => 'select', 'default_value' => 0),
			'profile_birthday_is_edit_public_flag_birthday' => array('form' => 'radio', 'default_value' => 0),
			'profile_birthday_default_public_flag_birthday' => array('form' => 'public_flag', 'default_value' => PRJ_PUBLIC_FLAG_MEMBER),
		);
	}

	private static function get_site_configs_birthday()
	{
		$site_configs_birthday = 	self::get_site_configs_birthday_form_config();
		$site_configs_birthday_as_assoc = \Model_SiteConfig::get4names_as_assoc(self::get_site_configs_birthday_names());
		$return = array();
		foreach ($site_configs_birthday as $name => $configs)
		{
			$return[$name] = isset($site_configs_birthday_as_assoc[$name]) ? $site_configs_birthday_as_assoc[$name] : $configs['default_value'];
		}

		return $return;
	}

	private static function save_site_configs_birthday($site_configs, $posted_values)
	{
		foreach ($site_configs as $name => $value)
		{
			$site_config_obj = \Model_SiteConfig::get4name($name);
			if ($site_config_obj && $site_config_obj->value == $posted_values[$name]) continue;
			if (!$site_config_obj) $site_config_obj = \Model_SiteConfig::forge();

			$site_config_obj->name  = $name;
			$site_config_obj->value = $posted_values[$name];
			$site_config_obj->save();
		}
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
