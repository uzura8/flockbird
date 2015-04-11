<div class="well">
<?php echo form_open(true); ?>
	<?php echo form_select($val, 'news_category_id', isset($news) ? $news->news_category_id : 0, 6); ?>
	<?php echo form_input($val, 'title', isset($news) ? $news->title : ''); ?>
<?php
$format_options = $val->fieldset()->field('format')->get_options();
?>
<?php if (count($format_options) == 1): ?>
	<?php echo Form::hidden('format', isset($news) ? $news->format : conf('form.formats.default', 'news')); ?>
<?php else: ?>
	<?php echo form_select($val, 'format', isset($news) ? $news->format : conf('form.formats.default', 'news'), 6); ?>
<?php endif; ?>
<?php if (\News\Site_Util::check_editor_enabled()): ?>
<?php
$textarea_attr = array('style' => 'display:none;');
if (\News\Site_Util::check_editor_enabled('markdown')) $textarea_attr['data-provide'] = 'markdown';
echo form_textarea($val, 'body', isset($news) ? $news->body : '', 12, true, null, null, $textarea_attr, true);
?>
<?php else: ?>
	<?php echo form_textarea($val, 'body', isset($news) ? $news->body : ''); ?>
<?php endif; ?>
</small>
<?php if (conf('image.isEnabled', 'news')): ?>
<?php 	if (conf('image.isModalUpload', 'news')): ?>
	<?php echo form_modal(
		icon_label('form.add_picture'),
		sprintf('admin/news/image/api/list/%d.html', $news->id),
		'modal_images', term('form.add_picture'),
		null,
		term('site.picture')
	); ?>
<?php 	else: ?>
<?php
$insert_target = null;
if (conf('image.isInsertBody', 'news'))
{
	$insert_target = (isset($news) && $news->format == 1) ? '.note-editable' : '#form_body';
}
echo form_upload_files(
	$images,
	$images ? false : true,
	false,
	true,
	'M',
	array(),
	'news',
	term('site.picture'),
	!empty($news) ? sprintf('admin/news/image/api/upload/%d.html', $news->id) : null,
	$insert_target
);
?>
<?php 	endif; ?>
<?php endif; ?>
<?php if (conf('file.isEnabled', 'news')): ?>
	<?php echo form_upload_files($files, $files ? false : true, false, true, 'M', array(), 'news', term('site.file'), null, null, 2, 'file'); ?>
<?php endif; ?>
<?php if (Config::get('news.link.isEnabled')): ?>
	<div class="form-group">
		<?php echo Form::label(term('site.link'), null, array('class' => 'control-label col-sm-2')); ?>
		<div class="col-sm-10">
			<div id="link_list">
<?php 	if (!empty($saved_links)): ?>
			<?php echo render('news/_parts/form/link_uri', array('val' => $val, 'links' => $saved_links, 'is_saved' => true)); ?>
<?php 	endif; ?>
<?php 	if (!empty($posted_links)): ?>
			<?php echo render('news/_parts/form/link_uri', array('val' => $val, 'links' => $posted_links)); ?>
<?php 	endif; ?>
			</div>
			<?php echo btn('form.add_link', '#', 'add_link', true, null, null, array('data-id' => 0), null, null, null, false); ?>
			<?php echo Form::hidden('link_id_max', $posted_links ? max(array_keys($posted_links)) : 0, array('id' => 'link_id_max')); ?>
		</div>
	</div>
<?php endif; ?>
	<?php echo form_input_datetime($val, 'published_at_time', isset($news) ? check_and_get_datatime($news->published_at, 'datetime_minutes') : ''); ?>
	<?php echo form_input($val, 'slug', isset($news) ? $news->slug : \News\Site_Util::get_slug(), 6); ?>
<?php if (empty($news->is_published)): ?>
	<?php echo form_button('form.draft', 'submit', 'is_draft', array('value' => 1)); ?>
<?php endif; ?>
<?php if (!empty($is_edit)): ?>
	<?php echo form_button(empty($news->is_published) ? 'form.do_publish' : 'form.do_edit', 'submit', 'submit', array('class' => 'btn btn-default btn-warning')); ?>
<?php else: ?>
	<?php echo form_button('form.do_publish', 'submit', 'submit', array('class' => 'btn btn-default btn-warning')); ?>
<?php endif; ?>
<?php if (!empty($is_edit)): ?>
	<?php echo form_anchor_delete('admin/news/delete/'.$news->id); ?>
<?php endif; ?>
<?php echo form_close(); ?>
</div><!-- well -->
