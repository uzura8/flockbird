<?php if (!empty($title) || !empty($subtitle) || !empty($page_lead)): ?>
			<div class="page-header">
<?php 	if (isset($header_info)): ?>
				<?php echo $header_info; ?>
<?php 	endif; ?>
<?php 	if ($title): ?>
				<?php echo $title; ?>
<?php endif; ?>
<?php 	if (isset($subtitle)): ?>
				<div id="subtitle"><?php echo $subtitle; ?></div>
<?php 	endif; ?>
<?php 	if (isset($page_lead)): ?>
				<div class="text-muted"><?php echo $page_lead; ?></div>
<?php 	endif; ?>
			</div><!-- page-header -->
<?php endif; ?>
