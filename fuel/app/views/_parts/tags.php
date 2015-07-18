<div class="tag-group">
	<h5><?php echo icon_label('site.tag'); ?></h5>
	<ul>
	<?php foreach ($tags as $tag): ?>
		<li><?php echo anchor('news/tag/'.urlencode($tag), $tag, IS_ADMIN, array('class' => 'label label-default')); ?></li>
	<?php endforeach; ?>
	</ul>
</div>

