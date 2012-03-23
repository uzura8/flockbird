<?php include_partial('member/_submenu'); ?>

<p>パスワードを入力してください</p>

<?php if (isset($html_error)): ?>
<?php echo $html_error; ?>
<?php endif; ?>
<?php echo $html_form; ?>
