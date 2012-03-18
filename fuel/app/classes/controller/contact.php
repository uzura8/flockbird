<?php

/**
 * The Contact Controller.
 *
 * A basic controller example.  Has examples of how to set the
 * response body and status.
 * 
 * @package  app
 * @extends  Controller
 */
class Controller_Contact extends Controller_Template
{
	
	public function form()
	{
		$form = Fieldset::forge();

		$form->add('name', '名前')
			->add_rule('trim')
			->add_rule('required')
			->add_rule('no_controll')
			->add_rule('max_length', 20);

		$form->add('email', 'メールアドレス')
			->add_rule('trim')
			->add_rule('required')
			->add_rule('no_controll')
			->add_rule('valid_email');

		$form->add('comment', 'コメント', 
					array('type' => 'textarea', 'cols' => 70, 'rows' => 6))
			->add_rule('required')
			->add_rule('max_length', 400);

		$ops = array(
			'男性' => '男性',
			'女性' => '女性',
		);
		$form->add('gender', '性別',
					array('options' => $ops, 'type' => 'radio'))
			->add_rule('in_array', $ops);
		
		$ops = array(
			'' => '選択してください',
			'使い方について'   => '使い方について',
			'その他'           => 'その他',
		);
		$form->add('kind', '問い合わせの種類',
					array('options' => $ops, 'type' => 'select'))
			->add_rule('in_array', $ops);
		
		$ops = array(
			'カワセミ' => 'カワセミ',
			'ヒヨドリ' => 'ヒヨドリ',
			'オオルリ' => 'オオルリ',
		);
		$form->add('lang', '好きな鳥は？',
					array('options' => $ops, 'type' => 'checkbox'))
			->add_rule('in_array', $ops)
			->add_rule('not_required_array');
		
		$form->add('submit', '', array('type'=>'submit', 'value' => '確認'));
		
		return $form;
	}

	/**
	 * Contact form input
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		$form = $this->form();

		if (Input::method() === 'POST')
		{
			$form->repopulate();
		}

		$this->template->title = 'お問い合わせフォーム';
		$this->template->content = View::forge('contact/index');
		$this->template->content->set_safe('html_form', $form->build('/contact/confirm'));// form の action に入る
	}

	/**
	 * Contact form confirm
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_confirm()
	{
		$form = $this->form();
		$val  = $form->validation();
		$val->add_callable('myvalidation');
		
		if ($val->run())
		{
			$data['input'] = $val->validated();
			$this->template->title = 'コンタクトフォーム: 確認';
			$this->template->content = View::forge('contact/confirm', $data);
		}
		else
		{
			$form->repopulate();
			
			$this->template->title = 'コンタクトフォーム: エラー';
			$this->template->content = View::forge('contact/index');
			$this->template->content->set_safe('html_error', $val->show_errors());
			$this->template->content->set_safe('html_form', $form->build('/contact/confirm'));
		}
	}

	public function action_send()
	{
		if ( ! \Security::check_token())
		{
			\Log::error(
				'CSRF: '.
				\Input::uri().' '.
				\Input::ip().
				' "'.\Input::user_agent().'"'
			);
			throw new HttpInvalidInputException('Invalid input data');
		}
		$val = $this->form()->validation();
		$val->add_callable('myvalidation');
		
		if ($val->run())
		{
			$post = $val->validated();
			
			\Config::load('contact', true);
			
			$data = array();
			$data['email']      = $post['email'];
			$data['name'] = $post['name'];
			$data['to']        = \Config::get('contact.admin_email');
			$data['to_name']   = \Config::get('contact.admin_name');
			$data['subject']   = \Config::get('contact.mail_subject');
			
			$data['ip']        = \Input::ip();
			$data['ua']        = \Input::user_agent();
			$langs = implode(' ', $post['lang']);
			
			$data['body'] = <<< END
====================
名前: {$post['name']}
メールアドレス: {$post['email']}
IPアドレス: {$data['ip']}
ブラウザ: {$data['ua']}
====================
コメント: 
{$post['comment']}

性別: {$post['gender']}
問い合わせの種類: {$post['kind']}
好きな鳥: $langs
====================
END;
			
			try
			{
				$this->sendmail($data);
				$this->save($data);
				$this->template->title = 'コンタクトフォーム: 送信完了';
				$this->template->content = View::forge('contact/send');
			}
			catch(EmailValidationFailedException $e)
			{
				$this->template->title = 'コンタクトフォーム: 送信エラー';
				$this->template->content = View::forge('contact/error');

				\Log::error(
					__METHOD__ . ' email validation error: ' .
					$e->getMessage()
				);
			}
			catch(EmailSendingFailedException $e)
			{
				$this->template->title = 'コンタクトフォーム: 送信エラー';
				$this->template->content = View::forge('contact/error');

				\Log::error(
					__METHOD__ . ' email sending error: ' .
					$e->getMessage()
				);
			}
			catch(EmailSavingFailedException $e)
			{
				$this->template->title = 'コンタクトフォーム: 送信エラー';
				$this->template->content = View::forge('contact/error');

				\Log::error(
					__METHOD__ . ' email saving error: ' .
					$e->getMessage()
				);
			}
		}
		else
		{
			$this->template->title = 'コンタクトフォーム: エラー';
			$this->template->content = View::forge('contact/index');
			$this->template->content->set_safe('html_error', $val->show_errors());
		}
	}

	public function save($data)
	{
		unset($data['to'], $data['to_name']);
		$contact = Model_Contact::forge($data);

		if (!$contact->save())
		{
			throw new EmailSavingFailedException('One or more email did not saved '.$item);
		}
	}

	public function sendmail($data)
	{
		Package::load('email');
		
		$items = array('email', 'name', 'to', 'to_name', 'subject');
		foreach ($items as $item)
		{
			if (preg_match('/[\r\n]/u', $data[$item]) === 1)
			{
				throw new EmailValidationFailedException('One or more email headers did not pass validation: '.$item);
			}
		}
		
		$email = Email::forge();
		$email->from($data['email'], $data['name']);
		$email->to($data['to'], $data['to_name']);
		$email->subject($data['subject']);
		$email->body($data['body']);
		
		$email->send();
	}
}
