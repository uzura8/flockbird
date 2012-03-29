<!--<h2><?php echo $note->title ?></h2>-->
<p>
<strong>投稿日: </strong><?php echo date('jS F, Y', strtotime($note->created_at)) ?>
(<?php echo Date::time_ago(strtotime($note->created_at)) ?>)
by <?php echo $note->member->name ?>
</p>

<p><?php echo nl2br($note->body) ?></p>

<?php if (isset($current_user) && $current_user->id == $note->member_id): ?>
<ul>
<li><?php echo Html::anchor('note/edit/'.$note->id, '編集'); ?></li>
<li><?php echo Html::anchor('note/delete/'.$note->id, '削除'); ?></li>
<ul>
<?php endif; ?>

<hr />

<h3 id="comments">Comments</h3>

<?php foreach ($comments as $comment): ?>
  <div><?php echo Html::anchor('/member/'.$comment->member_id, $comment->member->name); ?></div>
  <p><?php echo $comment->body ?>"</p>
<?php if (isset($current_user) && in_array($current_user->id, array($comment->member_id, $note->member_id))): ?>
  <div><?php echo Html::anchor('note/comment/delete/'.$comment->id, '削除'); ?></div>
<?php endif ; ?>
<?php endforeach; ?>

<p>Write a comment</p>

<?php echo Form::open('note/comment/create/'.$note->id) ?>

<div class="row">
   <label for="cbody">Comment:</label>
   <div class="input"><?php echo Form::textarea('body'); ?></div>
</div>

<div class="row">
   <div class="input"><?php echo Form::submit('submit'); ?></div>
</div>

<?php echo Form::close() ?>
