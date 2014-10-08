<?php
namespace Timeline;

class Controller_Member_Setting extends \Controller_Site
{
	protected $check_not_auth_action = array(
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Mmeber setting viewtype
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_viewtype()
	{
		$page_name = term('timeline', 'site.display', 'site.setting');
		$val = \Form_MemberConfig::get_validation($this->u->id, 'viewtype', 'Timeline');
		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();

			try
			{
				if (!$val->run()) throw new \FuelException($val->show_errors());
				$post = $val->validated();
				\DB::start_transaction();
				\Form_MemberConfig::save($this->u->id, $val, $post);
				\DB::commit_transaction();

				\Session::set_flash('message', $page_name.'を変更しました。');
				\Response::redirect('member/setting');
			}
			catch(\FuelException $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				\Session::set_flash('error', $e->getMessage());
			}
		}
		$this->set_title_and_breadcrumbs($page_name, array('member/setting' => term('site.setting', 'form.update')), $this->u);
		$this->template->content = \View::forge('member/setting/timeline_viewtype', array('val' => $val));
	}
}
