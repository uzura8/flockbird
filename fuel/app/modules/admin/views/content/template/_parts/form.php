<div class="well">
<?php echo form_open(true); ?>
	<?php echo form_text($configs['view'], t('site.item')); ?>
<?php if (is_enabled_i18n()): ?>
	<?php echo form_text(conf('lang.options.'.$lang, 'i18n'), t('site.lang')); ?>
<?php endif; ?>
<?php if (!empty($contents['title'])): ?>
	<?php echo form_input($val, 'title', $contents['title']); ?>
<?php endif; ?>
	<?php echo form_textarea($val, 'body', $contents['body']); ?>
	<?php echo form_button('form.do_edit', 'submit', 'submit', array('class' => 'btn btn-default btn-warning')); ?>
	<?php echo form_anchor_delete(
		sprintf('admin/content/template/mail/reset/%s/%s', $db_key, $lang),
		icon_label('form.do_reset_default', 'both', false), array('data-msg' => 'デフォルトに戻します。よろしいですか？')
	); ?>
<?php echo form_close(); ?>

<?php if (!empty($configs['variables'])): ?>
<h4 class="col-sm-offset-2 mt20">使用可能な変数</h4>
<?php 	foreach ($configs['variables'] as $val_name => $explain): ?>
<div class="row">
	<div class="col-sm-offset-2 col-xs-3 col-xs-4"><label><?php echo $val_name; ?></label></div>
	<div class="col-sm-7 col-xs-8"><?php echo $explain; ?></div>
</div>
<?php 	endforeach; ?>
<?php endif; ?>
</div><!-- well -->

