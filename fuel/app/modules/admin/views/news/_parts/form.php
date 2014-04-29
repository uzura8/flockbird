<div class="well">
<?php echo form_open(true); ?>
	<?php echo form_select($val, 'news_category_id', isset($news) ? $news->news_category_id : 0, 6); ?>
	<?php echo form_input($val, 'title', isset($news) ? $news->title : ''); ?>
	<?php echo form_textarea($val, 'body', isset($news) ? $news->body : ''); ?>
<?php if (Config::get('news.image.isEnabled')): ?>
	<?php echo form_upload_files($files, $files ? false : true, false, true, 'M', array(), 'news'); ?>
<?php endif; ?>
	<?php echo form_input($val, 'published_at_time', (!empty($news->published_at)) ? substr($news->published_at, 0, 16) : '', 6); ?>
<?php if (empty($news->is_published)): ?>
	<?php echo form_button(term('form.draft'), 'submit', 'is_draft', array('value' => 1, 'class' => 'btn btn-default btn-inverse')); ?>
<?php endif; ?>
<?php if (!empty($is_edit)): ?>
	<?php echo form_button(empty($news->is_published) ? term('form.do_publish') : term('form.do_edit')); ?>
<?php else: ?>
	<?php echo form_button(term('form.do_publish')); ?>
<?php endif; ?>
<?php if (!empty($is_edit)): ?>
	<?php echo form_anchor_delete('admin/news/delete/'.$news->id); ?>
<?php endif; ?>
<?php echo form_close(); ?>
</div><!-- well -->
