<ul class="list-inline mt10">
	<li><small><label><?php echo term(array('site.last', 'form.updated', 'site.datetime')); ?>:</label> <?php echo site_get_time($news->updated_at) ?></small></li>
	<?php if ($news->published_at): ?><li><small><label><?php echo term(array('form.publish', 'site.datetime')); ?>:</label> <?php echo site_get_time($news->published_at) ?></small></li><?php endif; ?>
</ul>

<div class="edit btn-group">
	<?php echo render('_parts/button_edit'); ?>
	<ul class="dropdown-menu pull-right" role="menu">
<?php if (!$news->is_published): ?>
		<li><?php echo render('_parts/anchor_publish', array('uri' => 'admin/news/publish/'.$news->id)); ?></li>
<?php endif; ?>
		<li><?php echo Html::anchor('admin/news/edit/'.$news->id, icon_label('pencil', 'form.do_edit')); ?></li>
		<li><?php echo Html::anchor('#', icon_label('trash', 'form.do_delete')); ?></li>
	</ul>
</div><!-- /btn-group -->
