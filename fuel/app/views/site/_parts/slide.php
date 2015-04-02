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
					<h1><?php echo Config::get('page.site.index.slide.title') ?: FBD_SITE_NAME; ?></h1>
					<p><?php echo Config::get('page.site.index.slide.lead') ?: FBD_SITE_DESCRIPTION; ?></p>
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
					<h1><?php echo Config::get('page.site.index.slide.title') ?: FBD_SITE_NAME; ?></h1>
					<p><?php echo Config::get('page.site.index.slide.lead') ?: FBD_SITE_DESCRIPTION; ?></p>
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
					<h1><?php echo Config::get('page.site.index.slide.title') ?: FBD_SITE_NAME; ?></h1>
					<p><?php echo Config::get('page.site.index.slide.lead') ?: FBD_SITE_DESCRIPTION; ?></p>
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
