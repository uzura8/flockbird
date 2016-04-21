<?php
namespace Contact;

class Controller_Report_Api extends \Controller_Site_Api
{
	protected $check_not_auth_action = array(
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Get form
	 * 
	 * @access  public
	 * @return  Response (html)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	public function get_form()
	{
		$this->api_accept_formats = 'html';
		$this->controller_common_api(function()
		{
			if (!conf('report.isEnabled', 'contact')) throw \HttpNotFoundException;

			$this->set_response_body_api(array(
				'report_data' => self::get_report_data(),
				'val' => self::get_validation(),
			), 'report/_parts/form');
		});
	}

	/**
	 * Get send
	 * 
	 * @access  public
	 * @return  Response (json)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	public function post_send()
	{
		$this->controller_common_api(function()
		{
			if (!conf('report.isEnabled', 'contact')) throw new \HttpNotFoundException;
			\Util_security::check_csrf();

			$val = self::get_validation('', true);
			if (!$val->run()) throw new \ValidationFailedException;
			$post = $val->validated();
			if (!$post['member_id'] || !$member_to = \Model_Member::get_one4id($post['member_id']))
			{
				throw new \ValidationFailedException;
			}

			$mail = new \Site_Mail('report');
			$mail->send(FBD_ADMIN_MAIL, array(
				'report_category' => $post['category'],
				'report_body' => $post['body'],
				'content_type' => \Arr::get(conf('report.types', 'contact'), $post['type']),
				'content_url' => \Uri::create($post['uri']),
				'content_body' => trim($post['content']),
				'member_id_to' => $post['member_id'],
				'member_name_to' => $member_to->name,
				'member_to_admin_page_url' => \Uri::create('admin/member/'.$member_to->id),
				'member_id_from' => $this->u->id,
				'member_name_from' => $this->u->name,
			), true);

			$this->set_response_body_api(array(
				'message' => term('report.view').'しました。',
			));
		});
	}

	private static function get_validation($form_name = '', $is_posted = false)
	{
		$val = \Validation::forge($form_name);

		if ($confs = conf('report.fields.pre', 'contact'))
		{
			$val = Site_Util::set_form_fields($val, $confs);
		}
		if ($confs = conf('report.fields.default', 'contact'))
		{
			$val = Site_Util::set_form_fields($val, $confs);
		}
		if ($confs = conf('report.fields.post', 'contact'))
		{
			$val = Site_Util::set_form_fields($val, $confs);
		}

		if ($is_posted)
		{
			$val->add('type')
				->add_rule('required')
				->add_rule('in_array', array_keys(conf('report.types', 'contact')));

			$val->add('member_id')
				->add_rule('required')
				->add_rule('valid_string', 'numeric');

			$val->add('uri');
			$val->add('content')
				->add_rule('trim');
		}
		
		return $val;
	}

	private static function get_report_data()
	{
		$inputs = array();
		if (!$inputs['member_id']   = (int)\Input::get('member_id')) throw new \HttpInvalidInputException;
		$inputs['type'] = \Input::get('type');
		if (!$inputs['type'] || !in_array($inputs['type'], array_keys(conf('report.types', 'contact'))))
		{
			throw new \HttpInvalidInputException;
		}
		$inputs['uri'] = \Input::get('uri');
		$inputs['content'] = \Input::get('content');
		
		return $inputs;
	}
}

