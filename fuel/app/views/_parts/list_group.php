<?php $class = 'list-group-item'; ?>
<?php foreach ($items as $item): ?>
<div class="list-group">
<?php if (!empty($item['link'])): ?>
<?php if (!empty($item['is_active'])) $class .= ' active'; ?>
	<?php echo Html::anchor($item['link'], $item['text'], array('class' => $class)); ?>
<?php else: ?>
<?php endif; ?>
</div>
<?php endforeach; ?>
