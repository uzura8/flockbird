<?php
namespace Admin;

class Controller_Member_Group extends Controller_Admin
{
	protected $check_not_auth_action = array();

	public function before()
	{
		parent::before();
	}

	/**
	 * edit group
	 * 
	 * @access  public
	 * @params  integer
	 * @params  string
	 * @return  Response
	 */
	public function action_edit($member_id = null, $group_key = null)
	{
		if (!conf('member.group.edit.isEnabled', 'admin')) throw new \HttpNotFoundException;
		\Util_security::check_method('POST');
		\Util_security::check_csrf();
		$member = \Model_Member::check_authority($member_id);
		$this->check_acl_edit_group($member->group, $group_key);
		$destination = \Input::post('destination') ?: 'admin/member';
		try
		{
			$group = self::validate_group($group_key, $member->group);
			\DB::start_transaction();
			$member->group = $group;
			$member->save();
			\DB::commit_transaction();
			\Session::set_flash('message', sprintf('%sを%sしました。', term('member.group.view'), term('form.update')));
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

		\Response::redirect($destination);
	}

	protected function check_acl_edit_group($member_group_before, $group_key)
	{
		if (!check_acl('admin/member/group/edit', 'POST')) throw new \HttpForbiddenException;
		$member_group_key_before = \Site_Member::get_group_key($member_group_before);
		if (!Site_AdminUser::check_editable_member_group(\Auth::get_groups(), $member_group_key_before)) throw new \HttpForbiddenException;
		if (!Site_AdminUser::check_editable_member_group(\Auth::get_groups(), $group_key)) throw new \HttpForbiddenException;
	}

	protected static function validate_group($group_key, $member_group)
	{
		if (!$group = \Site_Member::get_group_value(trim($group_key))) throw new \ValidationFailedException('値が不正です。');
		if ($group == $member_group) throw new \ValidationFailedException('既に登録済みです。');

		return $group;
	}
}
