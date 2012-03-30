<?php include_partial('member/_submenu'); ?>

<?php echo Form::open(array('action' => 'member/profile/edit_image', 'class' => 'form-stacked', 'enctype' => 'multipart/form-data', 'method' => 'post')); ?>
<?php echo Form::hidden(Config::get('security.csrf_token_key'), Security::fetch_token()); ?>

<?php echo Form::input('image', '写真', array('type' => 'file')); ?>
<?php echo Form::input('submit', '送信', array('type' => 'submit')); ?>

<?php echo Form::close(); ?>
