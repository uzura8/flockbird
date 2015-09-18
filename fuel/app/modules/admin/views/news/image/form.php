<div class="well">
<?php echo form_open(false); ?>
	<?php echo form_upload_files($images, false, true, 'M', array(), 'news', null, sprintf('admin/news/image/api/upload/%d.html', $news->id), '.note-editable', 0); ?>
<?php echo form_close(); ?>
</div><!-- well -->
<?php echo render('filetmp/_parts/upload_footer'); ?>
