<?php

class Site_Mail
{
	protected $config_key;
	protected $config;
	protected $contents;
	protected $options = array();
	protected $module;
	protected $parser;
	protected $lang;

	public function __construct($config_key, $options = array(), $lang = null)
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
			'reply_to'    => '',
			'common_variavles' => array(),
			'is_use_normalizer' => conf('library.PEAR_I18N_UnicodeNormalizer.isEnabled', null, false),
			'debug_log_is_enabled' => conf('mail.log.develop.isEnabled'),
			'debug_log_file_path' => conf('mail.log.develop.file_path'),
		);
		$this->configure($config_key, $options, $lang);
	}

	protected function configure($config_key, $options, $lang)
	{
		$this->set_lang($lang);
		$this->setup_options($options);
		$this->module = $this->options['is_admin'] ? 'admin' : 'site';
		$this->setup_config($config_key);
		$this->setup_contents();
		$this->setup_parser();
	}

	protected function setup_options($options)
	{
		if (! is_array($options)) $options = (array)$options;
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
		$this->options['common_variables']['admin_company_name'] = FBD_ADMIN_COMPANY_NAME;
		$this->options['common_variables']['admin_company_name_jp'] = FBD_ADMIN_COMPANY_NAME_JP;
		if (FBD_INTERNATIONALIZED_DOMAIN)
		{
			$this->options['common_variables']['idn_url'] = str_replace(FBD_DOMAIN, FBD_INTERNATIONALIZED_DOMAIN, FBD_BASE_URL);
		}

	}

	protected function set_lang($lang = null)
	{
		if (! $lang) $lang = get_default_lang();
		$this->lang = $lang;
	}

	protected function setup_config($config_key)
	{
		Config::load('template', 'template');
		if (! $this->config = Config::get(sprintf('template.mail.%s.%s', $this->module, $config_key)))
		{
			throw new FuelException('Parameter config_key is invalid');
		}
		$this->config_key = $config_key;
	}

	protected function setup_contents()
	{
		Config::load('template_content_'.$this->lang, 'template_content', true);
		$this->contents = Config::get(sprintf('template_content.mail.%s.%s', $this->module, $this->config_key));
	}

	protected function reset_lang($lang = null)
	{
		if (! $lang) return;

		$this->set_lang($lang);
		$this->setup_contents();
	}

	protected function setup_parser()
	{
		if ($this->contents['format'] != 'twig')
		{
			throw new FuelException('Template format is invalid.');
		}

		$this->parser = Util_Parser::get_twig_string_parser();
	}

	public function reset_options($target_option_items = array())
	{
		if (!$target_option_items)
		{
			$target_option_items = array(
				'to_name',
				'to_email',
				'subject',
				'body',
				'reply_to',
			);
		}
		foreach ($target_option_items as $item)
		{
			$this->options[$item] = '';
		}
	}

	public function send($to_email = null, $data = array(), $is_to_admin = false, $lang = null)
	{
		if ($lang && $lang != $this->lang) $this->reset_lang($lang);

		if (!$to_email && !empty($data['to_email'])) $to_email = $data['to_email'];
		if (empty($data['to_email'])) $data['to_email'] = $to_email;
		$this->set_to_email($to_email);

		if (!$is_to_admin && !$this->options['to_name'] && !empty($data['to_name']))
		{
			$this->options['to_name'] = $data['to_name'];
		}

		$data += $this->options['common_variables'];
		$this->set_subject($data);
		$this->set_body($data);
		$this->validate();
		$this->sendmail();
	}

	protected function set_to_email($to_email = null, $data = array())
	{
		if (!$to_email && empty($this->options['to_email']))
		{
			throw new EmailValidationFailedException('To address not set.');
		}

		if (empty($this->options['to_email']))
		{
			$this->options['to_email'] = $to_email;
			return;
		}

		if (is_array($this->options['to_email']))
		{
			$targets = array_values($this->options['to_email']);
			if (in_array($to_email, $targets)) return;

			$targets = array_keys($this->options['to_email']);
			if (in_array($to_email, $targets)) return;

			$this->options['to_email'][] = $to_email;
			return;
		}

		if ($to_email == $this->options['to_email']) return;

		$this->options['to_email'] = (array)$this->options['to_email'];
		$this->options['to_email'][] = $to_email;
	}

	protected function set_subject($data = array())
	{
		if ($this->options['subject']) return;
		if (!$this->contents['title']) return;

		$this->options['subject']  = $this->parser->render($this->contents['title'], $data);
		$this->options['subject'] = Util_String::normalize_platform_dependent_chars($this->options['subject'], $this->options['is_use_normalizer']);
	}

	protected function set_body($data = array())
	{
		$this->options['body']  = $this->parser->render($this->contents['body'], $data);
		$this->options['body'] .= $this->get_signature($data);
		$this->options['body']  = Util_String::normalize_platform_dependent_chars($this->options['body'], $this->options['is_use_normalizer']);
	}

	protected function get_signature($data = array())
	{
		if (!$this->options['is_add_signature']) return;

		$contents = Config::get(sprintf('template_content.mail.%s.signature', $this->module));
		if (!$data) $data = $this->options['common_variables'];

		return $this->parser->render($contents['body'], $data);
	}

	protected function validate()
	{
		if (!$this->options['from_email']) throw new EmailValidationFailedException('From address not set.');
		if (!$this->options['to_email']) throw new EmailValidationFailedException('To address not set.');

		$check_items = array('from_email', 'from_name', 'to_email', 'to_name', 'subject');
		foreach ($check_items as $item)
		{
			if (!isset($this->options[$item])) continue;

			if (!is_array($this->options[$item]))
			{
				self::check_is_single_line($this->options[$item], $item);
				continue;
			}

			foreach ($this->options[$item] as $key => $value)
			{
				self::check_is_single_line($key, $item);
				self::check_is_single_line($value, $item);
			}
		}
	}

	protected static function check_is_single_line($value, $item)
	{
		if (preg_match('/[\r\n]/u', $value) === 1)
		{
			throw new EmailValidationFailedException('One or more email headers did not pass validation: '.$item);
		}
	}

	protected function sendmail()
	{
		$email = Email::forge();
		$email->from($this->options['from_email'], !empty($this->options['from_name']) ? $this->options['from_name'] : null);
		$email->to($this->options['to_email'], !empty($this->options['to_name']) ? $this->options['to_name'] : null);
		if (!empty($this->options['reply_to'])) $email->reply_to($this->options['reply_to']);
		$email->subject($this->options['subject']);
		$email->body($this->options['body']);

		static::write_log();
		$email->send();
	}

	protected function write_log()
	{
		if (!$this->options['debug_log_is_enabled']) return;

		$output_keys = array('from_email', 'from_name', 'reply_to', 'to_email', 'to_name', 'subject', 'body');
		//if ($this->options['reply_to']) $output_keys['reply_to'] = $this->options['reply_to'];
		$outputs = array('', '', '-------------', 'date' => \Date::time()->format('mysql'));
		foreach ($output_keys as $key)
		{
			$outputs[$key] = is_array($this->options[$key]) ? serialize($this->options[$key]) : $this->options[$key];
		}

		return file_put_contents($this->options['debug_log_file_path'], Util_Array::conv_arrays2key_value_str($outputs), FILE_APPEND);
	}
}
