<?php echo Html::doctype(); ?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title><?php echo (!empty($header_title)) ? $header_title : $title; ?></title>

  <meta name="viewport" content="width=device-width,minimum-scale=1">
  <?php echo Asset::css('bootstrap.min.css');?>
  <?php echo Asset::css('bootstrap-responsive.min.css');?>
  <?php //echo Asset::css('my-style.css');?>

  <meta NAME="robots" CONTENT="index,follow">
  <meta name="description" content="<?php echo (!$header_description) ? $header_description : PRJ_HEADER_DESCRIPTION_DEFAULT; ?>">
  <meta name="keywords" content="<?php echo site_header_keywords($header_keywords); ?>">
  <meta http-equiv="Content-Style-Type" content="text/css">
<?php if (GOOGLE_SITE_VERIFICATION): ?>  <meta name="google-site-verification" content="<?php echo GOOGLE_SITE_VERIFICATION; ?>" /><?php endif; ?>
  <link rel="shortcut icon"href="/favicon.ico">
	<?php //echo Asset::css(array('bootstrap.css', 'torilife/bw.css')); ?>
</head>
<body>
<div id="all"><!-- #page ページの整形：中央寄せとか -->

<div id="head"><!-- #header 画面上部のヘッド部分 -->
  <h1><a href="/"><img src="/img/logo.gif" alt="<?php echo PRJ_SITE_DESCRIPTION.' '.PRJ_SITE_NAME; ?>"></a></h1>
<?php if (Auth::check()): ?>
  <p>こんにちは <?php echo site_get_screen_name($current_user); ?> さん[<a href="<?php echo Uri::create('site/logout'); ?>">ログアウト</a>]<p>
<?php else: ?>
  <p>こんにちは <?php echo site_get_screen_name($current_user); ?> さん[<a href="<?php echo Uri::create('site/login'); ?>">ログイン</a>]<p>
<?php endif; ?>
  <p class="sitemap"><a href="/sitemap/">サイトマップ"</a><p>
</div><!-- head -->

<div id="pankuzu">
<?php if (isset($breadcrumbs)): ?>
<?php foreach ($breadcrumbs as $name => $path): ?>
<?php echo ($path) ? Html::anchor($path, $name).'&nbsp;&gt;&nbsp;' : sprintf('<strong>%s</strong>', $name);?>
<?php endforeach; ?>
<?php endif; ?>
</div>
<div class="clear">

<div id="main">
	<div class="padding">
<?php if ($message = Session::get_flash('message')): ?>
	<div id="message"><?php echo $message; ?></div>
<?php endif; ?>
<?php if ($error = Session::get_flash('error')): ?>
	<div id="error_message"><?php echo view_convert_list($error); ?></div>
<?php endif; ?>
	<div class="pageTitle"><h2><?php echo $title; ?></h2></div>
<?php echo $content; ?>
	</div><!-- padding -->
</div><!-- main -->
</div><!-- clear -->

<div id="foot">
  <p>Copyright : 2007 - <?php echo date('Y'); ?> <?php echo PRJ_SITE_NAME; ?></p>
</div><!-- foot -->

</div><!-- all -->

<?php echo Asset::js('jquery-1.7.2.min.js');?>
<?php echo Asset::js('bootstrap.min.js');?>

</body>
</html>
