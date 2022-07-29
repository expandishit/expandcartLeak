<?php 
/******************************************************
 * @package Pav Opencart Theme Framework for Opencart 1.5.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2013 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/
	$themeConfig = $this->config->get( 'themecontrol' );
	$themeName = $this->config->get('config_template');
	require_once(  DIR_TEMPLATE.$this->config->get('config_template')."/template/libs/module.php" );
	$helper = ThemeControlHelper::getInstance( $this->registry, $themeName );
?>
<!DOCTYPE html>
<html dir="<?php echo $direction; ?>" lang="<?php echo $lang; ?>">
<head>
<meta charset="UTF-8" />
<title><?php echo $title; ?></title>
<base href="<?php echo $base; ?>" />
<?php if ($description) { ?>
<meta name="description" content="<?php echo $description; ?>" />
<?php } ?>
<?php if ($keywords) { ?>
<meta name="keywords" content="<?php echo $keywords; ?>" />
<?php } ?>
<?php if ($icon) { ?>
<link href="<?php echo $icon; ?>" rel="icon" />
<?php } ?>
<?php foreach ($links as $link) { ?>
<link href="<?php echo $link['href']; ?>" rel="<?php echo $link['rel']; ?>" />
<?php } ?>
<!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame -->
 <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<!-- Mobile viewport optimized: h5bp.com/viewport -->
<meta name="viewport" content="width=device-width">

<link rel="stylesheet" type="text/css" href="catalog/view/theme/<?php echo $themeName;?>/stylesheet/bootstrap.css" />
<link rel="stylesheet" type="text/css" href="catalog/view/theme/<?php echo $themeName;?>/stylesheet/stylesheet.css" />
<style>
	<?php if( $themeConfig['theme_width'] &&  $themeConfig['theme_width'] != 'auto' ) { ?>
			#page-container .container{max-width:<?php echo $themeConfig['theme_width'];?>; width:auto}
	<?php } ?>
	
	<?php if( isset($themeConfig['use_custombg']) && $themeConfig['use_custombg'] ) {	?>
		body{
			background:url( "image/<?php echo STORECODE . '/' . $themeConfig['bg_image'];?>") <?php echo $themeConfig['bg_repeat'];?>  <?php echo $themeConfig['bg_position'];?> !important;
		}
	<?php } ?>
</style>
<?php 
	if( isset($themeConfig['enable_customfont']) && $themeConfig['enable_customfont'] ){
	$css=array();
	$link = array();
	for( $i=1; $i<=3; $i++ ){
		if( trim($themeConfig['google_url'.$i]) && $themeConfig['type_fonts'.$i] == 'google' ){
			$link[] = '<link rel="stylesheet" type="text/css" href="'.trim($themeConfig['google_url'.$i]) .'"/>';
			$themeConfig['normal_fonts'.$i] = $themeConfig['google_family'.$i];
		}
		if( trim($themeConfig['body_selector'.$i]) && trim($themeConfig['normal_fonts'.$i]) ){
			$css[]= trim($themeConfig['body_selector'.$i])." {font-family:".str_replace("'",'"',htmlspecialchars_decode(trim($themeConfig['normal_fonts'.$i])))."}\r\n"	;
		}
	}
	echo implode( "\r\n",$link );
?>
<style>
	<?php echo implode("\r\n",$css);?>
	
	<?php 
		if( isset($themeConfig['custom_css'])  && !empty($themeConfig['custom_css']) ){
			echo trim( html_entity_decode($themeConfig['custom_css']) );
		}
	?>
</style>
<?php } else { ?>
<link rel="stylesheet" type="text/css" href="catalog/view/theme/<?php echo $themeName;?>/stylesheet/font.css" />
<link href='https://fonts.googleapis.com/css?family=Lora:400,400italic,700,700italic' rel='stylesheet' type='text/css'>
<?php } ?>
<?php foreach ($styles as $style) { ?>
<link rel="<?php echo $style['rel']; ?>" type="text/css" href="<?php echo $style['href']; ?>" media="<?php echo $style['media']; ?>" />
<?php } ?>
<script type="text/javascript" src="catalog/view/javascript/jquery/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="catalog/view/javascript/jquery/ui/jquery-ui-1.8.16.custom.min.js"></script>
<link rel="stylesheet" type="text/css" href="catalog/view/javascript/jquery/ui/themes/ui-lightness/jquery-ui-1.8.16.custom.css" />

<script type="text/javascript" src="catalog/view/javascript/common.js"></script>
<script type="text/javascript" src="catalog/view/javascript/jquery/pavtheme.js"></script>
<script type="text/javascript" src="catalog/view/javascript/jquery/bootstrap/bootstrap.min.js"></script>
<?php foreach ($scripts as $script) { ?>
<script type="text/javascript" src="<?php echo $script; ?>"></script>
<?php } ?>

<?php if( $helper->getParam('skin') &&  $helper->getParam('skin') != 'default' ){ ?>
<link rel="stylesheet" type="text/css" href="catalog/view/theme/<?php echo $themeName;?>/skins/<?php echo  $helper->getParam('skin');?>/stylesheet/stylesheet.css" />
<?php } ?>
<?php if( isset($themeConfig['responsive']) && $themeConfig['responsive'] ){ ?>
<link rel="stylesheet" type="text/css" href="catalog/view/theme/<?php echo $themeName;?>/stylesheet/bootstrap-responsive.css" />
<link rel="stylesheet" type="text/css" href="catalog/view/theme/<?php echo $themeName;?>/stylesheet/theme-responsive.css" />
<link href='https://fonts.googleapis.com/css?family=Arbutus+Slab' rel='stylesheet' type='text/css'>
<?php } ?>

<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<?php if( isset($themeConfig['custom_javascript'])  && !empty($themeConfig['custom_javascript']) ){ ?>
	<script type="text/javascript"><!--
		$(document).ready(function() {
			<?php echo html_entity_decode(trim( $themeConfig['custom_javascript']) ); ?>
		});
//--></script>

<?php }	?>

<?php if ($stores) { ?>
<script type="text/javascript"><!--
$(document).ready(function() {
<?php foreach ($stores as $store) { ?>
$('body').prepend('<iframe src="<?php echo $store; ?>" style="display: none;"></iframe>');
<?php } ?>
});
//--></script>
<?php } ?>
<?php echo $google_analytics; ?>

 <?php if($this->config->get('config_image_logo_height')!='') { ?>
 <style>
 #logo img {
 	max-height: <?php echo $this->config->get('config_image_logo_height'); ?>px !important;
 }
 </style>
 <?php }?>

</head>
<body class="fs<?php echo $themeConfig['fontsize'];?> <?php echo $helper->getPageClass();?> <?php echo $helper->getParam('body_pattern','pattern2');?>">
<section id="page-container">
<section id="header">
	<?php if( isset($themeConfig['advertising_top']) )  { ?>
	<div class="adv-top hidden-phone">
		<div class="container">
			<?php echo html_entity_decode($themeConfig['advertising_top'], ENT_QUOTES, 'UTF-8'); ?>
		</div>
	</div>
	<?php } ?>
	<div class="header-top">
		<div class="container">
			<div class="row-fluid">
				<div class="span9 top-links">
					<div id="welcome">
						<?php if (!$logged) { ?>
						<?php echo $text_welcome; ?>
						<?php } else { ?>
						<?php echo $text_logged; ?>
						<?php } ?>
					</div>	
					<a href="<?php echo $wishlist; ?>" id="wishlist-total"><?php echo $text_wishlist; ?></a>
					<a href="<?php echo $account; ?>"><?php echo $text_account; ?></a>
					<a href="<?php echo $shopping_cart; ?>"><?php echo $text_shopping_cart; ?></a>
					<a href="<?php echo $checkout; ?>"><?php echo $text_checkout; ?></a>
				</div>
				<div class="pull-right select-top">
					<?php echo $language; ?>
					<?php echo $currency; ?>
				</div>
			</div>
		</div>
	</div>

	<div class="header">
	<div class="container">
		<div class="row-fluid">
			<div class="span4">
				<?php if ($logo) { ?>
				<div id="logo">
					<a href="https://www.cinselux.com/index.php">
						<img src="<?php echo $logo; ?>" title="<?php echo $name; ?>" alt="<?php echo $name; ?>" />
						<!--<span class="logo"></span>-->
					</a>
				</div>
				<?php } ?>
			</div>
			<?php if( isset($themeConfig['delivery_data_module']) )  { ?>
			<div class="span3 pull-right delivery">
				<?php echo html_entity_decode($themeConfig['delivery_data_module'], ENT_QUOTES, 'UTF-8'); ?>
			</div>
			<?php } ?>
		</div>
	</div>
	</div>
</section>
<?php
/**
 * Slideshow modules
 */
$modules = $helper->getModulesByPosition( 'slideshow' ); 
if( $modules ){
?>
<section id="slideshow" class="pav-slideshow">
	<div class="container">
		<?php foreach ($modules as $module) { ?>
			<?php echo $module; ?>
		<?php } ?>
	</div>
</section>
<?php } ?>
<?php 
/**
 * Main Menu modules
 */
$modules = $helper->getModulesByPosition( 'mainmenu' ); 
if( count($modules) ){ 
?>

		<?php foreach ($modules as $module) { ?>
			<?php echo $module; ?>
		<?php } ?>

<?php } else { ?>
<nav id="mainmenu"><div class="container navbar">
	<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
	  <span class="icon-bar"></span>
	  <span class="icon-bar"></span>
	  <span class="icon-bar"></span>
	</a>
	<div class="navbar-inner">
		<div class="row-fluid">
			<div class="span9 pull-right">
				<div id="search">
					
					<input type="text" name="search" placeholder="<?php echo $text_search; ?>" value="<?php echo $search; ?>" />
					<div class="button-search"></div>
					
				</div>	
				 <?php echo $cart; ?> 
			 </div>
		</div> 
	</div>	
	
</div></nav>
<?php } ?>


<?php
/**
 * Promotion modules
 * $ospans allow overrides width of columns base on thiers indexs. format array( 1=> 3 )[value from 1->12]
 */
$modules = $helper->getModulesByPosition( 'showcase' ); 
$ospans = array();

if( count($modules) ){
$cols = isset($config['block_showcase'])&& $config['block_showcase']?(int)$config['block_showcase']:count($modules);	
$class = $helper->calculateSpans( $ospans, $cols );
?>
<div class="pav-showcase" id="pavo-showcase">
	<div class="container">
	<?php $j=1;foreach ($modules as $i =>  $module) {   ?>
			<?php if( $i++%$cols == 0 ){  $j=1;?><div class="row-fluid"><?php } ?>	
			<div class="<?php echo $class[$j];?>"><?php echo $module; ?></div>
			<?php if( $i%$cols == 0 || $i==count($modules) ){ ?></div><?php } ?>	
	<?php  $j++;  } ?>	
	</div>
</div>
<?php } ?>
<?php
/**
 * Promotion modules
 * $ospans allow overrides width of columns base on thiers indexs. format array( 1=> 3 )[value from 1->12]
 */
$modules = $helper->getModulesByPosition( 'promotion' ); 
$ospans = array();

if( count($modules) ){
$cols = isset($config['block_promotion'])&& $config['block_promotion']?(int)$config['block_promotion']:count($modules);	
$class = $helper->calculateSpans( $ospans, $cols );
?>
<div class="pav-promotion" id="pav-promotion">
	<div class="container">
	<?php $j=1;foreach ($modules as $i =>  $module) {   ?>
			<?php if( $i++%$cols == 0 ){  $j=1;?><div class="row-fluid"><?php } ?>	
			<div class="<?php echo $class[$j];?>"><?php echo $module; ?></div>
			<?php if( $i%$cols == 0 || $i==count($modules) ){ ?></div><?php } ?>	
	<?php  $j++;  } ?>	
	</div>
</div>
<?php } ?>
<section id="sys-notification"><div class="container"><div id="notification"></div></div></section>
<section id="columns"><div class="container">

<div class="row-fluid">