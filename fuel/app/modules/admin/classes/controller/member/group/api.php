<?php
namespace Admin;

class Controller_Member_Group_Api extends Controller_Api
{
	protected $check_not_auth_action = array();

	public function before()
	{
		parent::before();
	}

	/**
	 * Get member group edit menu
	 * 
	 * @access  public
	 * @param   int  $member_id
	 * @return  Response (html)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Site_Api::api_get_menu_common
	 */
	public function get_menu($member_id = null, $group = null)
	{
		$this->api_accept_formats = 'html';
		$this->controller_common_api(function() use($member_id)
		{
			$member_id = (int)$member_id;
			$member    = \Model_Member::check_authority($member_id);
			$page = \Input::get('page', 1);
			$menus = array();
			$groups = Site_AdminUser::get_editable_member_groups(\Auth::get_groups());
			foreach ($groups as $key => $value)
			{
				if ($member->group == $value)
				{
					$menus[] = array('label' => $member->display_group(), 'tag' => 'disabled');
				}
				else
				{
					$menus[] = array('label' => \Site_Member::get_group_label($value), 'attr' => array(
						'class' => 'js-simplePost',
						'data-uri' => sprintf('admin/member/group/edit/%d/%s', $member->id, $key),
						'data-msg' => sprintf('%sを%sしますか？', term('member.group.view'), term('form.update')),
						'data-post_data' => json_encode(array('destination' => sprintf('admin/member?page=%d', $page))),
					));
				}
			}

			$this->set_response_body_api(array('menus' => $menus, 'is_ajax_loaded' => true), '_parts/dropdown_menu');
		});
	}
}
