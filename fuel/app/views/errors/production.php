<?php $title = rm_period(__('message_error_occurred_for', array('label' => t('common.server')))); ?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex,nofollow">
	<title><?php echo $title; ?></title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
	<style type="text/css">
		body { background-color: #EEE; margin-top: 40px; }
		#wrapper { padding: 30px; background: #fff; color: #333; margin: 0 auto; }
		h1 { color: #000; padding: 0 0 25px; line-height: 1em; }
		p { margin: 0 0 15px; line-height: 22px;}
	</style>
</head>
<body>
<div class="container" id="main_container">
<div id="wrapper">
	<h1 class="h2"><?php $title; ?></h1>
	<p><?php echo __('message_error_occurred_for', array('label' => t('common.error_unexpected'))); ?></p>
</div>
</div>
</body>
</html>
