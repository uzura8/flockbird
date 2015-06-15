<?php

class Site_PostedBodyHandler
{
	protected $options = array();
	protected $is_truncated;
	protected $url2link_site_summary_url;
	protected $url2link_site_summary_data;
	protected $access_device_type;

	public function __construct($options = array())
	{
		$this->options = array(
			// truncate_options
			'encoding'       => Config::get('encoding'),
			'is_strip_tags'  => false,
			'allowable_tags' => '',
			'is_truncate'    => true,
			'truncate_width' => conf('view_params_default.list.trim_width.body'),
			'truncate_line'  => conf('view_params_default.list.truncate_lines.body'),
			'is_rtrim'       => true,
			'trimmarker'     => conf('view_params_default.list.trimmarker'),
			'read_more_uri'  => '',
			// other converts
			'nl2br' => true,
			// mention2link
			'mention2link' => false,
			'mention2link_is_enabled' => conf('mention.isEnabled', 'notice'),
			// url2link options
			'url2link' => true,
			'url2link_is_enabled' => conf('view_params_default.post.url2link.isEnabled'),
			'url2link_url_pattern' => '/https?:\/\/(?:[a-zA-Z0-9_\-\/.,:;~?@=+$%#!()]|&amp;)+/',
			'url2link_truncate_width' => conf('view_params_default.post.url2link.truncateWidth'),
			'url2link_trimmarker' => conf('view_params_default.post.url2link.trimmarker'),
			'url2link_display_summary_type' => conf('view_params_default.post.url2link.displaySummary.renderAt'),
			'url2link_summary_cache_is_enabled' => conf('view_params_default.post.url2link.displaySummary.cache.isEnabled'),
			'url2link_summary_cache_expire' => conf('view_params_default.post.url2link.displaySummary.cache.expire'),
			'url2link_summary_cache_prefix' => conf('view_params_default.post.url2link.displaySummary.cache.prefix'),
		);
		if (!is_array($options)) $options = (array)$options;
		if ($options) $this->options = $options + $this->options;

		$this->access_device_type = (defined('IS_SP') && IS_SP) ? 'sp' : 'pc';
	}

	public function convert($body)
	{
		if ($this->options['is_strip_tags'])
		{
			$allowable_tags = $this->options['allowable_tags'] ?: '';
			if (is_array($allowable_tags)) $allowable_tags = implode('', $allowable_tags);
			$body = strip_tags($body, $allowable_tags);
		}

		$body = e($body);
		if ($this->options['nl2br']) $body = nl2br($body);
		$body = $this->convert_url2link($body);
		$body = $this->convert_mention2link($body);
		$body = $this->truncate($body);

		$data = array();
		if ($this->is_truncated && $this->options['read_more_uri']) $data['read_more_uri'] = $this->options['read_more_uri'];
		$data['display_summary_type'] = $this->options['url2link_display_summary_type'];
		if ($this->url2link_site_summary_url) $data['site_summary_url'] = $this->url2link_site_summary_url;
		if ($this->url2link_site_summary_data) $data['site_summary_data'] = $this->url2link_site_summary_data;
		$view = View::forge('_parts/converted_body', $data);
		$view->set_safe('body', $body);

		return $view->render();
	}

	protected function convert_url2link($string)
	{
		if (!$this->options['url2link_is_enabled']) return $string;
		if (!$this->options['url2link']) return $string;

		$string = preg_replace_callback($this->options['url2link_url_pattern'], array('self', 'url2link_callback'), $string);
		static::set_url2link_site_summary_data();

		return $string;
	}

	protected function url2link_callback($matches)
	{
		$url = str_replace('&amp;', '&', $matches[0]);
		$items = parse_url($url);
		$length = $this->options['url2link_truncate_width'];
		$truncated_marker = $this->options['url2link_trimmarker'];

		if (!$this->url2link_site_summary_url) $this->url2link_site_summary_url = $url;

		if (strlen($url) > $length)
		{
			$length -= strlen($truncated_marker);
			$urlstr = substr($url, 0, $length).$truncated_marker;
		}
		else
		{
			$urlstr = $url;
		}

		$attr = array();
		if (Site_Util::check_ext_uri($url))
		{
			$attr['target'] = '_blank';
		}
		else
		{
			//TODO: add album image view.
		}

		$url    = Security::htmlentities($url);
		$urlstr = Security::htmlentities($urlstr);

		return Html::anchor($url, $urlstr, $attr);
	}

	protected function set_url2link_site_summary_data()
	{
		if ($this->options['url2link_display_summary_type'] != 'server') return;
		if ($this->url2link_site_summary_data) return;
		if (!$this->url2link_site_summary_url) return;

		$this->url2link_site_summary_data = Site_OpenGraph::get_analized_data(
			$this->url2link_site_summary_url,
			$this->options['url2link_summary_cache_is_enabled'],
			$this->options['url2link_summary_cache_prefix'],
			$this->options['url2link_summary_cache_expire']
		);
	}

	protected function convert_mention2link($string)
	{
		if (!$this->options['mention2link_is_enabled']) return $string;
		if (!$this->options['mention2link']) return $string;

		return preg_replace_callback(\Notice\Site_Util::get_match_pattern2mention(), array('static', 'mention2link_callback'), $string);
	}

	protected static function mention2link_callback($matches)
	{
		$member_name = $matches[2];
		if (!$member = Model_Member::get_one4name($member_name)) return $matches[0];

		$url    = Uri::create('member/'.$member->id);
		$urlstr = Security::htmlentities($matches[1].$member_name);

		return Html::anchor($url, $urlstr);
	}

	protected function truncate($body)
	{
		$is_truncated4line = false;
		$is_truncated4count = false;
		if ($this->options['is_truncate'])
		{
			if ($this->options['truncate_line'])
			{
				list($body, $is_truncated4line) = Util_string::truncate4line($body, $this->options['truncate_line'], $this->options['trimmarker'], $this->options['is_rtrim'], $this->options['encoding']);
			}
			if ($this->options['truncate_width'])
			{
				list($body, $is_truncated4count) = Util_string::truncate($body, $this->options['truncate_width'], $this->options['trimmarker'], true);
			}
			$this->is_truncated = $is_truncated4line || $is_truncated4count;
		}

		return $body;
	}
}
