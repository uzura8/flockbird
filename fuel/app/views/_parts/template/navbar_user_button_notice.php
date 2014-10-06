<div class="btn-group notice">
<?php /*
	<button class="btn btn-default navbar-inverse" type="button">
		<?php echo icon('group', 'fa fa-', 'i', array('class' => 'icon')); ?>
		<span class="badge">99</span>
	</button>
	<button class="btn btn-default navbar-inverse" type="button">
		<?php echo icon('comments', 'fa fa-', 'i', array('class' => 'icon')); ?>
		<span class="badge">99</span>
	</button>
*/ ?>
	<button class="btn btn-default navbar-inverse<?php if (!empty($notification_counts['notice'])): ?> notified<?php endif; ?>" type="button">
		<?php echo icon('info-circle', 'fa fa-', 'i', array('class' => 'icon')); ?>
<?php if (!empty($notification_counts['notice'])): ?>
		<span class="badge"><?php echo $notification_counts['notice']; ?></span>
<?php endif; ?>
	</button>
</div>
