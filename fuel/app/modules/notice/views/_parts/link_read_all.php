<?php
if (empty($tag))      $tag      = 'div';
if (empty($tag_attr)) $tag_attr = array();

echo html_tag($tag, $tag_attr, sprintf('%s ・ %s',
	anchor('#', term('common.all', 'form.do_read'), false, array('class' => 'js-notice-read')),
	anchor('member/setting/notice', term('site.setting'))
));
?>

