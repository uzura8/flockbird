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
<?php if (is_enabled('notice')): ?>
<?php
$notice_btn_attr = array(
	'class' => 'btn btn-default navbar-inverse js-modal',
	'type' => 'button',
	'data-uri' => 'notice/api/list.json',
	'data-get_data' => array('limit' => Config::get('notice.modalArticles.limit')),
	'data-target' => '#modal_notice_navbar',
	'data-tmpl' => '#notices-template',
	'id' => 'btn_notice_navbar',
);
if (!empty($notification_counts['notice'])) $notice_btn_attr['class'] .= ' notified';
?>
	<button <?php echo Util_Array::conv_array2attr_string($notice_btn_attr); ?>>
		<?php echo icon('info-circle', 'fa fa-', 'i', array('class' => 'icon')); ?>
<?php 	if (!empty($notification_counts['notice'])): ?>
		<span class="badge"><?php echo $notification_counts['notice']; ?></span>
<?php 	endif; ?>
	</button>
<?php endif; ?>

</div>
