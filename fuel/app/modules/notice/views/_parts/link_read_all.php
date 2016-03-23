<?php
if (empty($tag))      $tag      = 'div';
if (empty($tag_attr)) $tag_attr = array();

$anchor_attr = array('class' => 'js-notice-read_all');
if (!empty($is_message)) $anchor_attr['data-type'] = 'message';

echo html_tag($tag, $tag_attr, sprintf('%s ・ %s',
	anchor('#', term('common.all', 'form.do_read'), false, $anchor_attr),
	anchor('member/setting/notice', term('site.setting'))
));
?>

