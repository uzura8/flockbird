<?php

class Controller_Member_Setting_Address extends \Controller_Site
{
	protected $check_not_auth_action = array();

	public function before()
	{
		parent::before();

		if (! conf('address.isEnabled', 'member')) throw new HttpNotFoundException;
	}

	/**
	 * Mmeber setting address
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		$page_name = __('member_address_setting');
		$member_address = Model_MemberAddress::get_one_main($this->u->id) ?: Model_MemberAddress::forge();
		$val = self::get_validation_object($member_address);
		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();

			try
			{
				if (!$val->run()) throw new \ValidationFailedException($val->show_errors());
				$post = $val->validated();
				$member_address->set_values($post);
				$member_address->member_id = $this->u->id;
				$member_address->type = $member_address::get_enum_value4key('type', 'main');
				if (! $member_address->country) $member_address->country = '';
				\DB::start_transaction();
				$member_address->save();
				\DB::commit_transaction();

				\Session::set_flash('message', __('message_member_address_edit_complete'));
				\Response::redirect('member/setting');
			}
			catch(\ValidationFailedException $e)
			{
				\Session::set_flash('error', $e->getMessage());
			}
			catch(\FuelException $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				\Session::set_flash('error', $e->getMessage());
			}
		}
		$this->set_title_and_breadcrumbs($page_name, array('member/setting' => term('site.setting')), $this->u);
		$this->template->content = \View::forge('member/setting/address', array(
			'val' => $val,
			'member_address' => $member_address,
		));
	}

	private static function get_validation_object(Model_MemberAddress $member_address)
	{
		$val = \Validation::forge();
		$val->add_model($member_address);
		$val->fieldset()->field('address01')->set_attribute('placeholder', __('member_form_address01_placeholder'));
		$val->fieldset()->field('address02')->set_attribute('placeholder', __('member_form_address02_placeholder'));
		if (! conf('address.country.isEnabled', 'member')) $val->fieldset()->delete('country');
		$val->fieldset()->delete('member_id');
		$val->fieldset()->delete('type');

		return $val;
	}
}
