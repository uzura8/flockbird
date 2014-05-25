<div class="well">
<?php echo form_open(true); ?>
	<?php echo form_select($val, 'news_category_id', isset($news) ? $news->news_category_id : 0, 6); ?>
	<?php echo form_input($val, 'title', isset($news) ? $news->title : ''); ?>
	<?php echo form_textarea($val, 'body', isset($news) ? $news->body : ''); ?>
<?php if (Config::get('news.image.isEnabled')): ?>
	<?php echo form_upload_files($files, $files ? false : true, false, true, 'M', array(), 'news', term('site.picture')); ?>
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
	<?php echo form_input($val, 'published_at_time', (isset($news) && isset_datatime($news->published_at)) ? substr($news->published_at, 0, 16) : '', 6); ?>
	<?php echo form_input($val, 'slug', isset($news) ? $news->slug : \News\Site_Util::get_slug(), 6); ?>
<?php if (empty($news->is_published)): ?>
	<?php echo form_button('form.draft', 'submit', 'is_draft', array('value' => 1, 'class' => 'btn btn-default btn-inverse')); ?>
<?php endif; ?>
<?php if (!empty($is_edit)): ?>
	<?php echo form_button(empty($news->is_published) ? 'form.do_publish' : 'form.do_edit'); ?>
<?php else: ?>
	<?php echo form_button('form.do_publish'); ?>
<?php endif; ?>
<?php if (!empty($is_edit)): ?>
	<?php echo form_anchor_delete('admin/news/delete/'.$news->id); ?>
<?php endif; ?>
<?php echo form_close(); ?>
</div><!-- well -->
