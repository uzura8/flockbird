<?php

/**
 * The Site Controller.
 *
 * A basic controller example.  Has examples of how to set the
 * response body and status.
 * 
 * @package  app
 * @extends  Controller
 */
class Controller_Site extends Controller_Base_Site
{
	protected $check_not_auth_action = array(
		'index',
	);

	public function before()
	{
		parent::before();

		$this->auth_check();
		$this->set_current_user();

		if (!IS_API)
		{
			$this->set_title_and_breadcrumbs(PRJ_SITE_NAME);
			$this->template->header_keywords = '';
			$this->template->header_description = '';
		}
	}

	protected function display_error($message_display = '', $messsage_log = '', $action = 'error/500', $status = 500)
	{
		if ($messsage_log) \Log::error($messsage_log);
		$this->template->title = ($message_display) ? $message_display : 'Error';
		$this->template->header_title = site_title($this->template->title);
		$this->template->content = View::forge($action);
		if ($status) $this->response->status = $status;
	}

	protected function set_title_and_breadcrumbs($title = array(), $middle_breadcrumbs = array(), $member_obj = null, $module = null, $info = array(), $is_no_breadcrumbs = false)
	{
		if ($title)
		{
			if (is_array($title))
			{
				$title_name  = !empty($title['name'])  ? $title['name'] : '';
				$title_label = !empty($title['label']) ? $title['label'] : array();
			}
			else
			{
				$title_name  = $title;
				$title_label = array();
			}
			$this->template->title = View::forge('_parts/page_title', array('name' => $title_name, 'label' => $title_label));
		}
		$this->template->header_title = site_title($title_name);

		if ($info) $this->template->header_info = View::forge('_parts/information', $info);

		$breadcrumbs = array();
		if (!$is_no_breadcrumbs)
		{
			$breadcrumbs = array('/' => Config::get('term.toppage'));
			if ($member_obj)
			{
				if ($this->check_is_mypage($member_obj->id))
				{
					$breadcrumbs['/member'] = Config::get('term.myhome');
					if ($module)
					{
						$breadcrumbs[sprintf('/%s/member/', $module)] = '自分の'.\Config::get('term.'.$module).'一覧';
					}
				}
				else
				{
					$prefix = $member_obj->name.'さんの';
					$name = $prefix.Config::get('term.profile');
					$breadcrumbs['/member/'.$member_obj->id] = $name;
					if ($module)
					{
						$key = sprintf('/%s/member/%d', $module, $member_obj->id);
						$breadcrumbs[$key] = $prefix.\Config::get('term.'.$module).'一覧';
					}
				}
			}
			if ($middle_breadcrumbs) $breadcrumbs += $middle_breadcrumbs;
			$breadcrumbs[''] = $title_name;
		}
		$this->template->breadcrumbs = $breadcrumbs;
	}

	/**
	 * Site index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		$this->set_title_and_breadcrumbs(PRJ_SITE_NAME.'メインメニュー', null, null, null, null, true);
		$this->template->content = View::forge('site/index');
	}
}
