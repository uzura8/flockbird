<p><?php echo Config::get('site.term.profile'); ?>画面です</p>
<div><?php echo site_profile_image($current_user->image, 'medium', '', true); ?></div>

<ul>
  <li><?php echo Html::anchor('member/profile/setting_image', Config::get('site.term.profile').'写真設定'); ?></li>
</ul>
