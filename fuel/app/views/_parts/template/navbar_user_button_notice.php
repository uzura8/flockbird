<div class="btn-group notice pull-right">
<?php if (conf('site.navbar.request.isEnabled', 'page')): ?>
<?php
$request_btn_attr = array(
	'class' => 'btn btn-default navbar-inverse js-modal',
	'type' => 'button',
	'data-uri' => 'request/api/list.json',
	'data-get_data' => array('limit' => view_params('limit', 'request', 'mordal')),
	'data-target' => '#modal_request_navbar',
	'data-tmpl' => '#requests-template',
	'data-is_list' => 1,
	'id' => 'btn_request_navbar',
);
if (!empty($notification_counts['request'])) $request_btn_attr['class'] .= ' notified';
?>
	<button <?php echo Util_Array::conv_array2attr_string($request_btn_attr); ?>>
		<?php echo icon('user-plus', 'fa fa-', 'i', array('class' => 'icon')); ?>
<?php 	if (!empty($notification_counts['request'])): ?>
		<span class="badge"><?php echo $notification_counts['request']; ?></span>
<?php 	endif; ?>
	</button>
<?php endif; ?>

<?php if (is_enabled('message')): ?>
<?php
$message_btn_attr = array(
	'class' => 'btn btn-default navbar-inverse js-modal',
	'type' => 'button',
	'data-uri' => 'message/api/list.json',
	'data-get_data' => array('limit' => view_params('limit', 'message', 'mordal')),
	'data-target' => '#modal_message_navbar',
	'data-tmpl' => '#messages-template',
	'data-is_list' => 1,
	'id' => 'btn_message_navbar',
);
if (!empty($notification_counts['message'])) $message_btn_attr['class'] .= ' notified';
?>
	<button <?php echo Util_Array::conv_array2attr_string($message_btn_attr); ?>>
		<?php echo icon('envelope', 'fa fa-', 'i', array('class' => 'icon')); ?>
<?php 	if (!empty($notification_counts['message'])): ?>
		<span class="badge"><?php echo $notification_counts['message']; ?></span>
<?php 	endif; ?>
	</button>
<?php endif; ?>

<?php if (is_enabled('notice')): ?>
<?php
$notice_btn_attr = array(
	'class' => 'btn btn-default navbar-inverse js-modal',
	'type' => 'button',
	'data-uri' => 'notice/api/list.json',
	'data-get_data' => array('limit' => Config::get('notice.modalArticles.limit')),
	'data-target' => '#modal_notice_navbar',
	'data-tmpl' => '#notices-template',
	'data-is_list' => 1,
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
