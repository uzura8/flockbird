<div id="myCarousel" class="carousel slide" data-ride="carousel" data-interval="<?php echo Config::get('page.site.index.slide.interval'); ?>">
	<!-- Indicators -->
	<ol class="carousel-indicators hidden-xs">
		<li data-target="#myCarousel" data-slide-to="0" class="active"></li>
		<li data-target="#myCarousel" data-slide-to="1"></li>
		<li data-target="#myCarousel" data-slide-to="2"></li>
	</ol>
	<div class="carousel-inner" role="listbox">
<?php for ($i = 0; $i <= 2; $i++): ?>
		<div class="item<?php if (!$i): ?> active<?php endif; ?>">
			<?php echo Html::img(sprintf('assets/img/site/sample/%02d.jpg', $i + 1)); ?>
			<div class="container">
				<div class="carousel-caption">
					<h1><?php echo Config::get('page.site.index.slide.title') ?: FBD_SITE_NAME; ?></h1>
					<p><?php echo Config::get('page.site.index.slide.lead') ?: FBD_SITE_DESCRIPTION; ?></p>
<?php if (Config::get('page.site.index.slide.isDisplayRegisterBtn') && !Auth::check()): ?>
					<p><?php echo btn('member.registration', 'member/register/signup', '', true, '', 'primary', null, null, null, null, false); ?></p>
<?php endif; ?>
				</div>
			</div>
		</div>
<?php endfor; ?>
	</div>
	<a class="left carousel-control" href="#myCarousel" role="button" data-slide="prev">
		<span class="sr-only">Previous</span>
	</a>
	<a class="right carousel-control" href="#myCarousel" role="button" data-slide="next">
		<span class="sr-only">Next</span>
	</a>
</div><!-- /.carousel -->
