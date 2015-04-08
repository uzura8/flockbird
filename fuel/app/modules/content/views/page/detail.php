<div class="article_body">
<?php if ($content_page->format == 1): ?>
<?php 	echo $html_body ?>
<?php elseif ($content_page->format == 2): ?>
<?php 	echo Markdown::parse($html_body); ?>
<?php else: ?>
<?php 	echo nl2br($content_page->body) ?>
<?php endif; ?>
</div>
