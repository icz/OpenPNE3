<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
<?php include_http_metas() ?>
<?php include_metas() ?>
<title><?php echo ($op_config['sns_title']) ? $op_config['sns_title'] : $op_config['sns_name'] ?></title>
</head>
<body id="<?php printf('page_%s_%s', $view->getModuleName(), $view->getActionName()) ?>">
<div id="Body">
<div id="Container">

<div id="Header">
<div id="HeaderContainer">
<?php include_partial('global/header') ?>
</div><!-- HeaderContainer -->
</div><!-- Header -->

<div id="Contents">
<div id="ContentsContainer">

<div id="localNav">
<?php include_component('default', 'localNav') ?>
</div><!-- localNav -->

<div id="Layout<?php echo $layout ?>" class="Layout">

<?php if ($sf_user->hasFlash('error')): ?>
<?php op_include_parts('alertBox', 'flashError', array('body' => __($sf_user->getFlash('error')))) ?>
<?php endif; ?>
<?php if ($sf_user->hasFlash('notice')): ?>
<?php op_include_parts('alertBox', 'flashNotice', array('body' => __($sf_user->getFlash('notice')))) ?>
<?php endif; ?>

<?php if (has_slot('op_top')): ?>
<div id="Top">
<?php include_slot('op_top') ?>
</div><!-- Top -->
<?php endif; ?>

<?php if (has_slot('op_sidemenu')): ?>
<div id="Left">
<?php include_slot('op_sidemenu') ?>
</div><!-- Left -->
<?php endif; ?>

<div id="Center">
<?php echo $sf_content ?>
</div><!-- Center -->

<?php if (has_slot('op_bottom')): ?>
<div id="Bottom">
<?php include_slot('op_bottom') ?>
</div><!-- Bottom -->
<?php endif; ?>

</div><!-- Layout -->

<div id="sideBanner">
<?php include_component('default', 'sideBannerGadgets'); ?>
</div><!-- sideBanner -->

</div><!-- ContentsContainer -->
</div><!-- Contents -->

<div id="Footer">
<div id="FooterContainer">
<?php include_partial('global/footer') ?>
</div><!-- FooterContainer -->
</div><!-- Footer -->

</div><!-- Container -->
</div><!-- Body -->
</body>
</html>
