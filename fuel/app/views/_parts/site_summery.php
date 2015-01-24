<div class="site_summery">
<a href="<?php echo $url; ?>" target="_blank" class="simpleList-item simpleList-item-media">
<?php if (!empty($image)): ?>
	<?php echo Html::img($image, array('class' => 'pull-left-img')); ?>
<?php endif; ?>
	<div class="clearfix">
	<?php if (!empty($title)): ?><h5><?php echo $title; ?></h5><?php endif; ?>
	<?php if (!empty($description)): ?><div><?php echo $description; ?></div><?php endif; ?>
	</div>
</a>
</div>


