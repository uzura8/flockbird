<?php $col_sm_size = 6; ?>
<div class="well">
<?php echo form_open(true); ?>
<?php 	if ($target_type == 'all'): ?>
	<?php echo form_text(term('member.view', 'member.all'), '宛先'); ?>
<?php 	elseif ($target_type == 'member' && $member): ?>
	<?php echo form_text(member_name($member), '宛先'); ?>
	<?php echo Form::hidden('target_member_id', $member->id); ?>
<?php 	else: ?>
	<?php echo form_text(term('member.view', 'member.all'), '宛先'); ?>
<?php 	endif; ?>
	<?php echo form_input($val, 'subject', isset($message) ? $message->subject : ''); ?>
	<?php echo form_textarea($val, 'body', isset($message) ? $message->body : ''); ?>
<?php if (empty($message->is_sent)): ?>
	<?php echo form_button('form.draft', 'submit', 'is_draft', array('value' => 1, 'id' => 'form_draft', 'class' => 'btn btn-default btn-primary submit_btn')); ?>
<?php endif; ?>
<?php if (!empty($is_edit)): ?>
	<?php echo form_button(empty($message->is_published) ? 'form.do_send' : 'form.do_edit', 'submit', 'submit', array('class' => 'btn btn-default btn-warning submit_btn')); ?>
<?php else: ?>
	<?php echo form_button('form.do_send', 'submit', 'submit', array('class' => 'btn btn-default btn-warning submit_btn')); ?>
<?php endif; ?>
	<?php echo Form::hidden('target_type', $target_type); ?>
<?php echo form_close(); ?>
</div><!-- well -->

