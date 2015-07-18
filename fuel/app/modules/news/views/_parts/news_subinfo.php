<ul class="list-inline mt10 mb0">
<?php if ($news->news_category): ?>
	<li><?php echo Html::anchor('news/category/'.$news->news_category->name, $news->news_category->label, array('class' => 'label label-default')); ?></li>
<?php endif; ?>

<?php 	if (!empty($tags)): ?>
	<li><small>
		<label><?php echo icon_label('site.tag'); ?>:</label>
<?php 		foreach ($tags as $tag): ?>
		<?php echo anchor('news/tag/'.urlencode($tag), $tag, IS_ADMIN, array('class' => 'ml')); ?>
<?php 		endforeach; ?>
	</small></li>
<?php 	endif; ?>

	<li><small>
		<label><?php echo empty($is_simple_view) ? term('form.publish', 'site.datetime') : term('form.publish'); ?>:</label>
		<?php echo site_get_time($news->published_at) ?>
	</small></li>
<?php if (empty($is_simple_view)): ?>
	<li><small><label><?php echo term('site.last', 'form.updated', 'site.datetime'); ?>:</label> <?php echo site_get_time($news->updated_at) ?></small></li>
<?php endif; ?>
</ul>

