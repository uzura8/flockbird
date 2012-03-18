<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title><?php echo $title; ?></title>
	<?php echo Asset::css('bootstrap.css'); ?>
</head>
<body>
<div id="header">
	<div class="row">
		<div id="logo"></div>
	</div>
</div><!-- header -->

<div class="container">
	<h1><?php echo $title; ?></h1>

	<?php echo $content; ?>

</div><!-- container -->

<footer>
	<p class="pull-right">Page rendered in {exec_time}s using {mem_usage}mb of memory.</p>
	<p>
		<a href="http://fuelphp.com">FuelPHP</a> is released under the MIT license.<br>
		<small>Version: <?php echo e(Fuel::VERSION); ?></small>
	</p>
</footer>
</body>
</html>
