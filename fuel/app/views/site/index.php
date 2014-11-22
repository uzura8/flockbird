<?php if (Config::get('page.site.index.slide.isEnabled')): ?>
<div id="myCarousel" class="carousel slide" data-ride="carousel" data-interval="<?php echo Config::get('page.site.index.slide.interval'); ?>">
	<!-- Indicators -->
	<ol class="carousel-indicators">
		<li data-target="#myCarousel" data-slide-to="0" class="active"></li>
		<li data-target="#myCarousel" data-slide-to="1"></li>
		<li data-target="#myCarousel" data-slide-to="2"></li>
	</ol>
	<div class="carousel-inner" role="listbox">
		<div class="item active">
			<?php echo Html::img('assets/img/site/sample/01.jpg'); ?>
			<div class="container">
				<div class="carousel-caption">
					<h1><?php echo Config::get('page.site.index.slide.title') ?: PRJ_SITE_NAME; ?></h1>
					<p><?php echo Config::get('page.site.index.slide.lead') ?: PRJ_SITE_DESCRIPTION; ?></p>
<?php if (Config::get('page.site.index.slide.isDisplayRegisterBtn') && !Auth::check()): ?>
					<p><?php echo btn('member.registration', 'member/register/signup', '', true, '', 'primary'); ?></p>
<?php endif; ?>
				</div>
			</div>
		</div>
		<div class="item">
			<?php echo Html::img('assets/img/site/sample/02.jpg'); ?>
			<div class="container">
				<div class="carousel-caption">
					<h1><?php echo Config::get('page.site.index.slide.title') ?: PRJ_SITE_NAME; ?></h1>
					<p><?php echo Config::get('page.site.index.slide.lead') ?: PRJ_SITE_DESCRIPTION; ?></p>
<?php if (Config::get('page.site.index.slide.isDisplayRegisterBtn') && !Auth::check()): ?>
					<p><?php echo btn('member.registration', 'member/register/signup', '', true, '', 'primary'); ?></p>
<?php endif; ?>
				</div>
			</div>
		</div>
		<div class="item">
			<?php echo Html::img('assets/img/site/sample/03.jpg'); ?>
			<div class="container">
				<div class="carousel-caption">
					<h1><?php echo Config::get('page.site.index.slide.title') ?: PRJ_SITE_NAME; ?></h1>
					<p><?php echo Config::get('page.site.index.slide.lead') ?: PRJ_SITE_DESCRIPTION; ?></p>
<?php if (Config::get('page.site.index.slide.isDisplayRegisterBtn') && !Auth::check()): ?>
					<p><?php echo btn('member.registration', 'member/register/signup', '', true, '', 'primary'); ?></p>
<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
	<a class="left carousel-control" href="#myCarousel" role="button" data-slide="prev">
		<span class="sr-only">Previous</span>
	</a>
	<a class="right carousel-control" href="#myCarousel" role="button" data-slide="next">
		<span class="sr-only">Next</span>
	</a>
</div><!-- /.carousel -->
<?php endif; ?>

<?php if (!empty($timeline)): ?>
<h4><?php echo term('site.latest', 'timeline'); ?></h4>
<?php echo render('timeline::_parts/list', array(
	'list' => $timeline['list'],
	'next_id' => $timeline['next_id'],
	'since_id' => $timeline['since_id'],
	'is_display_load_before_link' => $timeline['is_display_load_before_link'],
	'liked_timeline_ids' => $timeline['liked_timeline_ids'],
	'see_more_link' => array('uri' => 'timeline'),
)); ?>
<?php endif; ?>

<div class="jumbotron">
	<h1>Hello, world!</h1>
	<p>This is a template for a simple marketing or informational website. It includes a large callout called the hero unit and three supporting pieces of content. Use it as a starting point to create something more unique.</p>
	<p><a class="btn btn-primary btn-default btn-lg">Learn more &raquo;</a></p>
</div>
<div class="row">
	<div class="col-md-4">
		<h2>Heading</h2>
		<p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
		<p><a class="btn btn-default" href="#">View details &raquo;</a></p>
	</div><!--/col-md -->
	<div class="col-md-4">
		<h2>Heading</h2>
		<p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
		<p><a class="btn btn-default" href="#">View details &raquo;</a></p>
	</div><!--/col-md -->
	<div class="col-md-4">
		<h2>Heading</h2>
		<p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
		<p><a class="btn btn-default" href="#">View details &raquo;</a></p>
	</div><!--/col-md -->
</div><!--/row-->
<div class="row">
	<div class="col-md-4">
		<h2>Heading</h2>
		<p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
		<p><a class="btn btn-default" href="#">View details &raquo;</a></p>
	</div><!--/col-md -->
	<div class="col-md-4">
		<h2>Heading</h2>
		<p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
		<p><a class="btn btn-default" href="#">View details &raquo;</a></p>
	</div><!--/col-md -->
	<div class="col-md-4">
		<h2>Heading</h2>
		<p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
		<p><a class="btn btn-default" href="#">View details &raquo;</a></p>
	</div><!--/col-md -->
</div><!--/row-->
