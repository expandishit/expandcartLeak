<?php 
/******************************************************
 * @package Pav Opencart Theme Framework for Opencart 1.5.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2013 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/
	$themeConfig = $this->config->get( 'themecontrol' );
	$themeName = $this->config->get('config_template') ;
	require_once( DIR_TEMPLATE.$this->config->get('config_template')."/template/libs/module.php" );
	$helper = ThemeControlHelper::getInstance( $this->registry, $themeName );
    $LANGUAGE_ID = $this->config->get( 'config_language_id' );

/* Add scripts files */
	$helper->addScript( 'catalog/view/javascript/jquery/jquery-1.7.1.min.js' );
	$helper->addScript( 'catalog/view/javascript/jquery/ui/jquery-ui-1.8.16.custom.min.js' );
	$helper->addScript( 'catalog/view/javascript/jquery/ui/external/jquery.cookie.js' );
	$helper->addScript( 'catalog/view/javascript/common.js' );
	$helper->addScript( 'catalog/view/theme/'.$themeName.'/javascript/common.js' );
	$helper->addScript( 'catalog/view/javascript/jquery/bootstrap/bootstrap.min.js' );

?>
<!DOCTYPE html>
<html dir="<?php echo $direction; ?>" lang="<?php echo $lang; ?>"
      prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">
<head>
<!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame -->
 <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<!-- Mobile viewport optimized: h5bp.com/viewport -->
<meta name="viewport" content="width=device-width">
<meta charset="UTF-8" />

    <!-- mod og:image para FB -->

    <!-- og:image -->

    <!-- mod og:image para FB -->

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
	<?php 
		if( isset($themeConfig['custom_css'])  && !empty($themeConfig['custom_css']) ){
			echo trim( html_entity_decode($themeConfig['custom_css']) );
		}
	?>
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
</style>
<?php } else { ?>
<link rel="stylesheet" type="text/css" href="catalog/view/theme/<?php echo $themeName;?>/stylesheet/font.css" />
<link href='https://fonts.googleapis.com/css?family=Lora:400,400italic,700,700italic' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,300italic,400italic,600,600italic,700,700italic|Arbutus+Slab&subset=latin,cyrillic-ext,greek,greek-ext,vietnamese,latin-ext,cyrillic' rel='stylesheet' type='text/css'>
<?php } ?>
<?php foreach ($styles as $style) { ?>
<link rel="<?php echo $style['rel']; ?>" type="text/css" href="<?php echo $style['href']; ?>" media="<?php echo $style['media']; ?>" />
<?php } ?>
<link rel="stylesheet" type="text/css" href="catalog/view/javascript/jquery/ui/themes/ui-lightness/jquery-ui-1.8.16.custom.css" />
<?php if( $helper->getParam('skin') &&  $helper->getParam('skin') != 'default' ){ ?>
<link rel="stylesheet" type="text/css" href="catalog/view/theme/<?php echo $themeName;?>/skins/<?php echo  $helper->getParam('skin');?>/stylesheet/stylesheet.css" />
<?php } ?>
<link rel="stylesheet" type="text/css" href="catalog/view/theme/<?php echo $themeName;?>/stylesheet/font-awesome.min.css" />
<?php if( isset($themeConfig['responsive']) && $themeConfig['responsive'] ){ ?>
<link rel="stylesheet" type="text/css" href="catalog/view/theme/<?php echo $themeName;?>/stylesheet/bootstrap-responsive.css" />
<link rel="stylesheet" type="text/css" href="catalog/view/theme/<?php echo $themeName;?>/stylesheet/theme-responsive.css" />
<?php } ?>
<?php if( $direction == 'rtl' ) { ?>
<link rel="stylesheet" type="text/css" href="catalog/view/theme/<?php echo $themeName;?>/stylesheet/bootstrap-rtl.css" />
<link rel="stylesheet" type="text/css" href="catalog/view/theme/<?php echo $themeName;?>/stylesheet/theme-rtl.css" />
<?php } ?>
<link rel="stylesheet" type="text/css" href="catalog/view/javascript/jquery/colorbox/colorbox.css" media="screen" />

<!--<script type="text/javascript" src="catalog/view/javascript/jquery/colorbox/jquery.colorbox-min.js"></script>-->

<?php foreach( $helper->getScriptFiles() as $script )  { ?>
<script type="text/javascript" src="<?php echo $script; ?>"></script>
<?php } ?>

<?php foreach ($scripts as $script) { ?>
<script type="text/javascript" src="<?php echo $script; ?>"></script>
<?php } ?>

<?php if( isset($themeConfig['custom_javascript'])  && !empty($themeConfig['custom_javascript']) ){ ?>
	<script type="text/javascript"><!--
		$(document).ready(function() {
			<?php echo html_entity_decode(trim( $themeConfig['custom_javascript']) ); ?>
		});
//--></script>
<?php }	?>

<!--[if lt IE 9]>
<?php if( isset($themeConfig['load_live_html5'])  && $themeConfig['load_live_html5'] ) { ?>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<?php } else { ?>
<script src="catalog/view/javascript/html5.js"></script>
<?php } ?>
<![endif]-->

<?php if ( isset($stores) && $stores ) { ?>
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
			<?php echo html_entity_decode(is_array($themeConfig['advertising_top']) ? $themeConfig['advertising_top'][$LANGUAGE_ID] : $themeConfig['advertising_top'], ENT_QUOTES, 'UTF-8'); ?>
		</div>
	</div>
	<?php } ?>
	<div class="header-top">
		<div class="container">
			<div class="row-fluid hidden-tablet hidden-phone">
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
					<div class="language pull-right"><?php echo $language; ?></div>
					<div class="currency pull-right"><?php echo $currency; ?></div>
				</div>
			</div>
			<div class="show-mobile hidden-desktop pull-right">
				<div class="quick-user pull-left">
					<div class="quickaccess-toggle">
						<i class="fa fa-user"></i>															
					</div>	
					<div class="inner-toggle">
						<div class="login links">
							<?php if (!$logged) { ?>
							<?php echo $text_welcome; ?>
							<?php } else { ?>
							<?php echo $text_logged; ?>
							<?php } ?> 
						</div>
					</div>						
				</div>
				<div class="quick-access pull-left">
					<div class="quickaccess-toggle">
						<i class="fa fa-list"></i>															
					</div>	
					<div class="inner-toggle">
						<ul class="links pull-left">
							<!-- <li><a class="first" href="<?php echo $home; ?>"><?php echo $text_home; ?></a></li> -->
							<li><a class="wishlist" href="<?php echo $wishlist; ?>" id="wishlist-total"><span class="fa fa-heart"></span><?php echo $text_wishlist; ?></a></li>
							<li><a class="account" href="<?php echo $account; ?>"><span class="fa fa-user"></span><?php echo $text_account; ?></a></li>
							<li><a class="shoppingcart" href="<?php echo $shopping_cart; ?>"><span class="fa fa-shopping-cart"></span><?php echo $text_shopping_cart; ?></a></li>
                            <?php $queryAuctionModule = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'special_count_down'");
                            if($this->config->get('config_display_popular_auctions_block_on')==1 && $queryAuctionModule->num_rows) { ?>
                            <li><a class="auction" href="<?php echo $auction; ?>"><span class="fa"></span><?php echo $text_auction; ?></a></li>
                            <?php } ?>
							<li><a class="last checkout" href="<?php echo $checkout; ?>"><span class="fa fa-file"></span><?php echo $text_checkout; ?></a></li> 
				
						</ul>
					</div>						
				</div>
				<div class="search pull-left" id="search_mobile">				
					<div class="quickaccess-toggle">
						<i class="fa fa-search"></i>								
					</div>																								
					<div class="inner-toggle">					
						<div id="search">
							<input type="text" value="" placeholder="Search" name="search">
							<span class="button-search"></span>
						</div>
					</div>
				</div>
				<div class="currency-mobile pull-left">
					<div class="quickaccess-toggle">
						<i class="fa fa-calendar"></i>								
					</div>						
					<div class="inner-toggle">
						<div class="currency pull-left">
							<?php echo $currency; ?>
						</div> 
					</div>															
				</div>
				
				
				<div class="language-mobile pull-left">
					<div class="quickaccess-toggle">
						<i class="fa fa-sun-o"></i>								
					</div>						
					<div class="inner-toggle">	
						<div class="language pull-left">
							<?php echo $language; ?>
						</div>
					</div>															
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
					<a href="<?php echo $home; ?>">
						<img src="<?php echo $logo; ?>" title="<?php echo $name; ?>" alt="<?php echo $name; ?>" />
						<!--<span class="logo"></span>-->
					</a>
				</div>
				<?php } ?>
			</div>
			<?php if( isset($themeConfig['delivery_data_module']) )  { ?>
			<div class="span8 pull-right delivery hidden-tablet hidden-phone">
				<?php echo html_entity_decode(is_array($themeConfig['delivery_data_module']) ? $themeConfig['delivery_data_module'][$LANGUAGE_ID] : $themeConfig['delivery_data_module'], ENT_QUOTES, 'UTF-8'); ?>
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
<nav id="mainmenu"><div class="container navbar hidden-tablet hidden-phone">
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
					<div class="button-search"><i class="fa fa-search">&nbsp;</i></div>
					
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
<section id="sys-notification">
	<div class="container">
		<div id="notification"></div>
	</div>
</section>
<section id="columns">
	<div class="container">
		<div class="row-fluid">