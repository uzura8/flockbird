<div class="well">
<?php echo form_open(true); ?>
	<?php echo form_input($val, 'title', isset($content_page) ? $content_page->title : ''); ?>
<?php if (Config::get('news.form.isEnabledWysiwygEditor')): ?>
	<?php echo form_textarea($val, 'body', isset($content_page) ? $content_page->body : '', null, false, null, null, true); ?>
<?php else: ?>
	<?php echo form_textarea($val, 'body', isset($content_page) ? $content_page->body : ''); ?>
<?php endif; ?>
	<?php echo form_input($val, 'slug', isset($content_page) ? $content_page->slug : '', 6); ?>
	<?php echo form_radio($val, 'is_secure', isset($content_page) ? $content_page->is_secure : 0, 2, 'inline'); ?>
	<?php echo form_button(empty($is_edit) ? 'form.do_create' : 'form.do_edit', 'submit', 'submit', array('class' => 'btn btn-default btn-warning')); ?>
<?php if (!empty($is_edit)): ?>
	<?php echo form_anchor_delete('admin/content/page/delete/'.$content_page->id); ?>
<?php endif; ?>
<?php echo form_close(); ?>
</div><!-- well -->
