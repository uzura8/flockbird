<?php include_partial('member/_submenu'); ?>

<p>プロフィール画面です</p>
<div><?php echo site_profile_image($current_user->image, 'medium', '', true); ?></div>

<ul>
  <li><?php echo Html::anchor('member/profile/setting_image', 'プロフィール写真設定'); ?></li>
</ul>
