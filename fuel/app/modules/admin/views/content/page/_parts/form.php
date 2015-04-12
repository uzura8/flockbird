<div class="well">
<?php echo form_open(true); ?>
	<?php echo form_input($val, 'title', isset($content_page) ? $content_page->title : ''); ?>
<?php
$format_options = $val->fieldset()->field('format')->get_options();
?>
<?php if (count($format_options) == 1): ?>
	<?php echo Form::hidden('format', isset($content_page) ? $content_page->format : conf('page.form.formats.default', 'content')); ?>
<?php else: ?>
	<?php echo form_select($val, 'format', isset($content_page) ? $content_page->format : conf('page.form.formats.default', 'content'), 6); ?>
<?php endif; ?>
<?php if (\Content\Site_Util::check_editor_enabled()): ?>
<?php
$textarea_attr = array('style' => 'display:none;');
if (\Content\Site_Util::check_editor_enabled('markdown')) $textarea_attr['data-provide'] = 'markdown';
echo form_textarea($val, 'body', isset($content_page) ? $content_page->body : '', 12, true, null, null, $textarea_attr, true);
?>
<?php else: ?>
	<?php echo form_textarea($val, 'body', isset($content_page) ? $content_page->body : ''); ?>
<?php endif; ?>
	<?php echo form_input($val, 'slug', isset($content_page) ? $content_page->slug : '', 6); ?>
	<?php echo form_radio($val, 'is_secure', isset($content_page) ? $content_page->is_secure : 0, 2, 'inline'); ?>
	<?php echo form_button(empty($is_edit) ? 'form.do_create' : 'form.do_edit', 'submit', 'submit', array('class' => 'btn btn-default btn-warning btn_submit')); ?>
<?php if (!empty($is_edit)): ?>
	<?php echo form_anchor_delete('admin/content/page/delete/'.$content_page->id); ?>
<?php endif; ?>
<?php echo form_close(); ?>
</div><!-- well -->
