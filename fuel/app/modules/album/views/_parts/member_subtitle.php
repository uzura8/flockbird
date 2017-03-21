<div id="btn_menu">
<?php if ($is_mypage) : ?>
<?php echo btn(t('form.do_create_for', array('label' => t('album.view'))),
						'album/create', 'mr', true, null, null, null, 'plus', null, null, false); ?>
<?php endif; ?>
<?php
$controller = Site_Util::get_controller_name();
$label_target = $is_mypage ? t('common.own_for_myself_of', array('label' => t($controller == 'album' ? 'album.image.plural' : 'album.plural')))
										 : t('common.own_for_member_of', array(
													'label' => t($controller == 'album' ? 'album.image.plural' : 'album.plural'),
													'name' => member_name($member),
												));
$btn_label = t('site.see_all_for', array('label' => $label_target));
$uri = $controller == 'album' ? sprintf('album/member/%d/images', $member->id) : sprintf('album/member/%d', $member->id);
echo btn($btn_label, $uri, 'mr', true, null, null, null, 'picture', null, null, false);
?>
</div>

