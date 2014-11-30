<?php
namespace Admin;

class Controller_Content_Template_Mail extends Controller_Admin
{
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
		$configs = \Config::get('template.mail');
		$this->set_title_and_breadcrumbs(
			term('site.mail', 'site.template', 'site.management'),
			array('admin/content' => term('site.content', 'site.management'))
		);
		$this->template->content = \View::forge('content/template/mail/list', array('list' => $configs));
	}

	/**
	 * The list action.
	 * 
	 * @access  public
	 * @return  void
	 */
	Public function action_list()
	{	
		$this->action_index();
	}

	/**
	 * Action edit
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_edit($module = null, $name = null)
	{
		list($db_key, $configs) = self::get_template_configs($module, $name);
		if (!$configs) throw new \HttpNotFoundException;
		$val = self::get_validation($configs);

		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();
			$error_message = '';
			try
			{
				if (!$val->run()) throw new \FuelException($val->show_errors());
				$post = $val->validated();

				\DB::start_transaction();
				if (!$template = \Model_Template::get4name($db_key))
				{
					$template = \Model_Template::forge();
					$template->name = $db_key;
					$template->format = $configs['format'];
				}
				$template->title = isset($post['title']) ? $post['title'] : $configs['title'];
				$template->body  = isset($post['body']) ? $post['body'] : $configs['body'];
				$template->save();
				\DB::commit_transaction();

				$message = sprintf('%sを%sしました。', term('site.template'), term('form.edit'));
				\Session::set_flash('message', $message);
				\Response::redirect('admin/content/template/mail');
			}
			catch(\Database_Exception $e)
			{
				$error_message = \Util_Db::get_db_error_message($e);
			}
			catch(\FuelException $e)
			{
				$error_message = $e->getMessage();
			}
			if ($error_message)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				\Session::set_flash('error', $error_message);
			}
		}

		$this->set_title_and_breadcrumbs(
			term('site.mail', 'site.template', 'form.edit'),
			array(
				'admin/content' => term('site.content', 'site.management'),
				'admin/content/template/mail' => term('site.mail', 'site.template', 'site.management'),
			)
		);
		$this->template->content = \View::forge('content/template/_parts/form', array(
			'val' => $val,
			'configs' => $configs,
			'db_key' => $db_key,
		));
	}

	/**
	 * Reset action.
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_reset($db_key = null)
	{
		\Util_security::check_method('POST');
		\Util_security::check_csrf();
		if (!$template = \Model_Template::get4name($db_key))
		{
			throw new \HttpNotFoundException;
		}
		$error_message = '';
		try
		{
			\DB::start_transaction();
			$template->delete();
			\DB::commit_transaction();
			\Session::set_flash('message', 'デフォルトに戻しました。');
		}
		catch(\Database_Exception $e)
		{
			$error_message = \Util_Db::get_db_error_message($e);
		}
		catch(\FuelException $e)
		{
			$error_message = $e->getMessage();
		}
		if ($error_message)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			\Session::set_flash('error', $error_message);
		}

		\Response::redirect('admin/content/template/mail');
	}

	private static function get_validation(array $configs)
	{
		$val = \Validation::forge();
		$val->add_model(\Model_Template::forge());
		$val->field('name')->delete_rule('required');
		$val->field('format')->delete_rule('required');
		$val->field('body')->add_rule('required');
		if (empty($configs['title']))
		{
			$val->fieldset()->delete('title');
		}
		else
		{
			$val->field('title')->add_rule('required');
		}

		return $val;
	}

	private static function get_template_configs($module, $name)
	{
		$db_key = sprintf('mail_%s_%s', $module, $name);
		if (!$configs = \Config::get('template.'.str_replace('_', '.', $db_key)))
		{
			return array($db_key, null);
		}

		if (!isset($configs['variables'])) $configs['variables'] = array();
		$common_variables = \Config::get(sprintf('template.mail.%s.common_variables', $module));
		$configs['variables'] = array_merge($configs['variables'], $common_variables);

		return array($db_key, $configs);
	}
}

/* End of mail.php */
