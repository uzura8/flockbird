<div class="article_body">
<?php if (Config::get('content.page.form.isEnabledWysiwygEditor')): ?>
<?php echo $html_body ?>
<?php else: ?>
<?php echo nl2br($content_page->body) ?>
<?php endif; ?>
</div>
