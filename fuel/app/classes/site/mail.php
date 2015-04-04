<?php

class Site_Mail
{
	protected $config;
	protected $options = array();
	protected $module;
	protected $parser;

	public function __construct($config_key, $options = array())
	{
		Package::load('email');
		$this->options = array(
			'is_admin' => false,
			'is_add_signature' => true,
			'from_name'  => conf('mail.site.from_name'),
			'from_email' => conf('mail.site.from_email'),
			'to_name'    => '',
			'to_email'   => '',
			'subject'    => '',
			'body'    => '',
			'common_variavles' => array(),
			'is_use_normalizer' => conf('library.PEAR_I18N_UnicodeNormalizer.isEnabled', null, false),
			'debug_log_is_enabled' => conf('mail.log.develop.isEnabled'),
			'debug_log_file_path' => conf('mail.log.develop.file_path'),
		);
		$this->configure($config_key, $options);
	}

	protected function configure($config_key, $options)
	{
		$this->setup_options($options);
		$this->setup_config($config_key);
		$this->setup_parser();
	}

	protected function setup_options($options)
	{
		if ($options) $this->options = $options + $this->options;
		$this->set_common_variables_default();
	}

	protected function set_common_variables_default()
	{
		$this->options['common_variables']['base_url'] = FBD_BASE_URL;
		$this->options['common_variables']['site_name'] = FBD_SITE_NAME;
		$this->options['common_variables']['site_description'] = FBD_SITE_DESCRIPTION;
		$this->options['common_variables']['admin_name'] = FBD_ADMIN_NAME;
		$this->options['common_variables']['admin_mail'] = FBD_ADMIN_MAIL;
	}

	protected function setup_config($config_key)
	{
		$this->module = $this->options['is_admin'] ? 'admin' : 'site';
		$this->config = Config::get(sprintf('template.mail.%s.%s', $this->module, $config_key));
	}

	protected function setup_parser()
	{
		if ($this->config['format'] != 'twig')
		{
			throw new FuelException('Template format is invalid.');
		}

		$this->parser = Util_Parser::get_twig_string_parser();
	}

	public function send($to_email = null, $data = array())
	{
		if ($to_email)
		{
			$this->options['to_email'] = $to_email;
		}
		$data['to_email'] = $this->options['to_email'];

		if (!$this->options['to_name'] && !empty($data['to_name']))
		{
			$this->options['to_name'] = $data['to_name'];
		}

		$data += $this->options['common_variables'];
		$this->set_subject($data);
		$this->set_body($data);
		$this->validate();
		$this->sendmail();
	}

	protected function set_subject($data = array())
	{
		if ($this->options['subject']) return;
		if (!$this->config['title']) return;

		$this->options['subject']  = $this->parser->render($this->config['title'], $data);
		$this->options['subject'] = Util_String::normalize_platform_dependent_chars($this->options['subject'], $this->options['is_use_normalizer']);
	}

	protected function set_body($data = array())
	{
		$this->options['body']  = $this->parser->render($this->config['body'], $data);
		$this->options['body'] .= $this->get_signature($data);
		$this->options['body']  = Util_String::normalize_platform_dependent_chars($this->options['body'], $this->options['is_use_normalizer']);
	}

	protected function get_signature($data = array())
	{
		if (!$this->options['is_add_signature']) return;

		$config = Config::get(sprintf('template.mail.%s.signature', $this->module));
		if (!$data) $data = $this->options['common_variables'];

		return $this->parser->render($config['body'], $data);
	}

	protected function validate()
	{
		if (!$this->options['from_email']) throw new EmailValidationFailedException('From address not set.');
		if (!$this->options['to_email']) throw new EmailValidationFailedException('To address not set.');

		$check_items = array('from_email', 'from_name', 'to_email', 'to_name', 'subject');
		foreach ($check_items as $item)
		{
			if (isset($this->options[$item]) && preg_match('/[\r\n]/u', $this->options[$item]) === 1)
			{
				throw new EmailValidationFailedException('One or more email headers did not pass validation: '.$item);
			}
		}
	}

	protected function sendmail()
	{
		$email = Email::forge();
		$email->from($this->options['from_email'], !empty($this->options['from_name']) ? $this->options['from_name'] : null);
		$email->to($this->options['to_email'], !empty($this->options['to_name']) ? $this->options['to_name'] : null);
		$email->subject($this->options['subject']);
		$email->body($this->options['body']);

		static::write_log();
		$email->send();
	}

	protected function write_log()
	{
		if (!$this->options['debug_log_is_enabled']) return;

		$output_keys = array('from_email', 'from_name', 'to_email', 'to_name', 'subject', 'body');
		$outputs = array('', '', '-------------', 'date' => \Date::time()->format('mysql'));
		foreach ($output_keys as $key) $outputs[$key] = $this->options[$key];

		return file_put_contents($this->options['debug_log_file_path'], Util_Array::conv_arrays2key_value_str($outputs), FILE_APPEND);
	}
}
