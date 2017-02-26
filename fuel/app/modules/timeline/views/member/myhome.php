<div id="main_post_box">
<?php echo render('_parts/comment/post', array(
	'size' => 'M',
	'button_attrs' => array('id' => 'btn_timeline', 'class' => 'btn btn-default btn_comment'),
	'textarea_attrs' => array('class' => 'form-control autogrow input_timeline'),
	'with_public_flag_selector' => true,
	'with_uploader' => true,
	'uploader_selects' => array(
		'label'  => t('album.view'),
		'name'  => 'album_id',
		'value' => 0,
		'options' => array(
			'0' => t('common.delimitter.for', array('subject' => t('album.view'), 'object' => t('timeline.view'))),
		),
		'atters' => array('id' => 'album_id'),
	),
	'public_flag' => $public_flag,
	'uri_for_update_public_flag' => 'member/setting/api/config/timeline_public_flag.html',
)); ?>

	<div id="timeline_setting" class="text-right">
		<span class="text-muted"><?php echo term('site.display', 'site.setting'); ?>:</span>
		<?php echo render('member/_parts/timeline_viewType_selecter', array(
			'id' => $u->id,
			'timeline_viewType' => $member_config->timeline_viewType,
		)); ?>
	</div>
</div>

<div id="article_list" data-type="ajax" data-not_render_site_summary="1"></div>
