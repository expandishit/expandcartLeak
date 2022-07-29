<?php 
/******************************************************
 * @package Pav Opencart Theme Framework for Opencart 1.5.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2013 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/
	$themeConfig = $this->config->get( 'themecontrol' );
	$themeName =  $this->config->get('config_template');
	require_once( DIR_TEMPLATE.$this->config->get('config_template')."/template/libs/module.php" );
	$helper = ThemeControlHelper::getInstance( $this->registry, $themeName );

	/* Add scripts files */
	$helper->addScript( 'catalog/view/javascript/jquery/jquery-1.7.1.min.js' );
	$helper->addScript( 'catalog/view/javascript/jquery/ui/jquery-ui-1.8.16.custom.min.js' );
	$helper->addScript( 'catalog/view/javascript/jquery/ui/external/jquery.cookie.js' );
	$helper->addScript( 'catalog/view/javascript/common.js' );
	$helper->addScript( 'catalog/view/theme/'.$themeName.'/javascript/common.js' );
	$helper->addScript( 'catalog/view/javascript/jquery/bootstrap/bootstrap.min.js' );

?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>"
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
<style type="text/css">
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
<link href='https://fonts.googleapis.com/css?family=Lato:400,100,100italic,300,300italic,400italic,700,700italic,900,900italic' rel='stylesheet' type='text/css'>
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
<!--[if IE 8]>         
 <link rel="stylesheet" type="text/css" href="catalog/view/theme/<?php echo $themeName;?>/stylesheet/ie8.css" />
<![endif]-->
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
	<div id="page-container">
	<header id="header">
		<div class="container">
			<div class="container-inner">
				<?php if ($logo) { ?>
					<div id="logo"><a href="<?php echo $home; ?>"><img src="<?php echo $logo; ?>" title="<?php echo $name; ?>" alt="<?php echo $name; ?>" /></a></div>
				<?php } ?>
				<div id="headertop" class="hidden-phone">
					<?php
					$LANGUAGE_ID = $this->config->get( 'config_language_id' );  
			 		 if( isset($themeConfig['widget_return_data'][$LANGUAGE_ID]) ) { ?>
							<div class="hidden-tablet pull-left"><?php echo html_entity_decode( $themeConfig['widget_return_data'][$LANGUAGE_ID], ENT_QUOTES, 'UTF-8' ); ?></div>
				 	<?php } ?>

					<div class="pull-right cart-top">
						<?php echo $cart; ?>
					</div>
					
			
				</div>

				<div id="headerbottom" class="hidden-tablet hidden-phone ">
							<div class="links">
								<span class="login">
										<?php if (!$logged) { ?>
										<?php echo $text_welcome; ?>
										<?php } else { ?>
										<?php echo $text_logged; ?>
										<?php } ?> 
								</span>
								<!--<a class="first" href="<?php //echo $home; ?>"><?php // echo $text_home; ?></a>-->
								<a class="account" href="<?php echo $account; ?>"><?php echo $text_account; ?></a>
								<a class="wishlist" href="<?php echo $wishlist; ?>" id="wishlist-total"><?php echo $text_wishlist; ?></a>
								<a href="<?php echo $shopping_cart; ?>"><?php echo $text_shopping_cart; ?></a>
								<a class="last checkout" href="<?php echo $checkout; ?>"><?php echo $text_checkout; ?></a>
								
							</div>
							
						<div class="pull-right language-currency ">
							<span class="currency ">
								<?php echo $currency; ?>
							</span> 
							<span class="language">
								<?php echo $language; ?>
							</span> 
						</div>
				</div>

				<div id="mainnav">
					<?php 
					/**
					 * Main Menu modules: as default if do not put megamenu, the theme will use categories menu for main menu
					 */
					$modules = $helper->getModulesByPosition( 'mainmenu' ); 
					if( count($modules) ){ 
					?>

							<?php foreach ($modules as $module) { ?>
							<nav id="mainmenu" class="pull-left">	<?php echo $module; ?></nav>
							<?php } ?>

					<?php } elseif ($categories) { ?>
					<nav id="mainmenu" class="pull-left">
						<div class="navbar">
							<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
							  <span class="icon-bar"></span>
							  <span class="icon-bar"></span>
							  <span class="icon-bar"></span>
							</a>
							<div class="navbar-inner">

							<div class="nav-collapse collapse">
									
								  <ul class="nav megamenu">
									<?php foreach ($categories as $category) { ?>
									
									<?php if ($category['children']) { ?>
									<?php
										$items_childrens = array_chunk($category['children'], $category['column']);
										$col_settings = explode(',', $category['column']);
										//var_dump($col_settings);
									?>
									<?php } else { ?>
									  <li><a href="<?php echo $category['href']; ?>"><?php echo $category['name']; ?></a></li>
									<?php } ?>
									  <?php if ($category['children'] && count($col_settings) > 1) { ?>
									  <li class="parent dropdown deeper parent-columns-menu hidden-phone hidden-tablet ">
										  <a href="<?php echo $category['href'];?>" class="dropdown-toggle" data-toggle="dropdown">
											  <?php echo $category['name']; ?>
											  <b class="caret"></b>
										  </a>
										  <div class="dropdown-menu columns-image" style="top: 46px; background-image:<?php echo $category['image'] && $category['column'] > 1 ? 'url(' . $category['image'] . ')' : 'none'; ?>; background-repeat: no-repeat; background-position-y: top; padding: 20px;">
								  <?php
								  $no_of_children = $col_settings[0];
								  $no_of_subchildren = $col_settings[1];
								  $allCategories = array();
								  $i = 0;
								  foreach($category['children'] as $child) {
								  	  if(count($child['children']) < 2) continue;
									  $i++; if($i > $no_of_children) break;
									  $allCategories[] = '<li style="margin-top: 18px;"><b>' . $child['name'] . '</b></li>';
									  foreach (array_slice($child['children'], 0, $no_of_subchildren) as $sub_child) {
										$allCategories[] = '<li><a href="' . $sub_child['href'] . '">' . $sub_child['name'] . '</a></li>';
									  }
									  if (count($child['children']) < $no_of_subchildren) {
									  	for ($j = 0; $j < $no_of_subchildren - count($child['children']); $j++) {
									  		$allCategories[] = '<li></li>';
									  	}
									  }
									  $allCategories[] = '<li><a href="' . $child['href'] . '">' . $text_shop_all . '</a></li>';
								  }
								  $columns_count = 4;
								  $items_per_column = ($no_of_children * ($no_of_subchildren+2)) / $columns_count;

								  $items_childrens = array_chunk($allCategories, $items_per_column);
								  $i = 0;
								  foreach(array_slice($items_childrens, 0, $columns_count) as $children) {
									  echo '<ul class="span2" style="width: 180px;">';
									  foreach($children as $child) {
										  $i++;
										  //if($i == ($items_per_column * $columns_count)-1) {
										//  echo '<li><a href="' . $category['href'] . '"><b>See all categories</b></a></li>';
										//	  break;
										  //}
										  echo $child;
									  }
									  echo '</ul>';
								  }
								  ?>
									<div class="span12" style="margin-left: 0px; margin-top: 9px;">
									<?php
									//var_dump($category['brands']);
										foreach($category['brands'] as $brand) {
											echo '<a href="'.$brand['href'].'"><img src="'.$brand['image'].'" title="'.$brand['name'].'" alt="'.$brand['name'].'" style="height: 50px;padding: 16px"/></a>';
										}
									?>
									</div>
									</div>
								</li>

								<li class="parent dropdown deeper hidden-desktop ">
									<a href="<?php echo $category['href'];?>" class="dropdown-toggle" data-toggle="dropdown">
										<?php echo $category['name']; ?>
										<b class="caret"></b>
									</a>
									<ul class="dropdown-menu">
										<?php for ($i = 0; $i < count($category['children']);) { ?>

										<?php $j = $i + ceil(count($category['children']) / $category['column']); ?>
										<?php for (; $i < $j; $i++) { ?>
										<?php if (isset($category['children'][$i])) { ?>
										<li><a href="<?php echo $category['children'][$i]['href']; ?>"><?php echo $category['children'][$i]['name']; ?></a></li>
										<?php } ?>
										<?php } ?>

										<?php } ?>
									</ul>
								</li>
									<?php } else if ($category['children'] && $category['column'] > 1) { ?>
									  <li class="parent dropdown deeper parent-columns-menu hidden-phone hidden-tablet ">
										  <a href="<?php echo $category['href'];?>" class="dropdown-toggle" data-toggle="dropdown">
											  <?php echo $category['name']; ?>
											  <b class="caret"></b>
										  </a>
										<div class="dropdown-menu columns-image" style="background-image:<?php echo $category['image'] && $category['column'] > 1 ? 'url(' . $category['image'] . ')' : 'none'; ?>; background-repeat: no-repeat; background-size: contain;">
											<?php

												foreach ($items_childrens as $item_childrens) {
													echo '<ul class="span2" style="width: 220px;">';
													foreach ($item_childrens as $children) {
														echo '<li><a href="' . $children['href'] . '">' . $children['name'] . '</a></li>';
													}
									  				echo '</ul>';
												}
											?>
										</div>
							</li>

								<li class="parent dropdown deeper hidden-desktop ">
									<a href="<?php echo $category['href'];?>" class="dropdown-toggle" data-toggle="dropdown">
										<?php echo $category['name']; ?>
										<b class="caret"></b>
									</a>
								<ul class="dropdown-menu">
									<?php for ($i = 0; $i < count($category['children']);) { ?>

									<?php $j = $i + ceil(count($category['children']) / $category['column']); ?>
									<?php for (; $i < $j; $i++) { ?>
									<?php if (isset($category['children'][$i])) { ?>
									<li><a href="<?php echo $category['children'][$i]['href']; ?>"><?php echo $category['children'][$i]['name']; ?></a></li>
									<?php } ?>
									<?php } ?>

									<?php } ?>
								</ul>
									</li>
								<?php } else if ($category['children'] && $category['column'] <= 1) { ?>
								<li class="parent dropdown deeper ">
									<a href="<?php echo $category['href'];?>" class="dropdown-toggle" data-toggle="dropdown">
										<?php echo $category['name']; ?>
										<b class="caret"></b>
									</a>
								<ul class="dropdown-menu">
									<?php for ($i = 0; $i < count($category['children']);) { ?>

									<?php $j = $i + ceil(count($category['children']) / $category['column']); ?>
									<?php for (; $i < $j; $i++) { ?>
									<?php if (isset($category['children'][$i])) { ?>
									<li><a href="<?php echo $category['children'][$i]['href']; ?>"><?php echo $category['children'][$i]['name']; ?></a></li>
									<?php } ?>
									<?php } ?>

									<?php } ?>
								</ul>
								</li>
								<?php } ?>
									<?php } ?>
								  </ul>
							</div>	
							</div>		  
						</div>
					</nav>
					<?php } ?>

				
					<div id="search" class="pull-right hidden-tablet hidden-phone ">
							<input type="text" name="search1" placeholder="<?php echo $text_search; ?>" value="<?php echo $search; ?>" />
							<span class="button-search">Search</span>
					</div>
									
			<div class="show-mobile hidden-desktop  pull-right">
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
								<i class="fa fa-shopping-cart"></i>
							</div>	
							<div class="inner-toggle">
								<ul class="links pull-left">
									<!-- <li><a class="first" href="<?php echo $home; ?>"><?php echo $text_home; ?></a></li> -->
									<li><a class="account" href="<?php echo $account; ?>"><?php echo $text_account; ?></a></li>
									<li><a class="wishlist" href="<?php echo $wishlist; ?>" id="wishlist-total"><?php echo $text_wishlist; ?></a></li>
									<li><a class="shoppingcart" href="<?php echo $shopping_cart; ?>"><?php echo $text_shopping_cart; ?></a></li>
									<li><a class="last checkout" href="<?php echo $checkout; ?>"><?php echo $text_checkout; ?></a></li> 
						
								</ul>
							</div>						
						</div>


						<div id="search_mobile" class="search pull-left">				
							<div class="quickaccess-toggle">
								<i class="fa fa-search"></i>								
							</div>																								
							<div class="inner-toggle">
							
								<div id="search">
						<input type="text" name="search" placeholder="<?php echo $text_search; ?>" value="<?php echo $search; ?>" />
							<span class="button-search">Search</span>
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
		</div>
	</header>
<?php
/**
 * Slideshow modules
 */


$modules = $helper->getModulesByPosition( 'slideshow' );
$ospans = array();

if( count($modules) ){
$cols = isset($config['block_slideshow'])&& $config['block_slideshow']?(int)$config['block_slideshow']:count($modules);
$class = $helper->calculateSpans( $ospans, $cols );
?>
<section id="slideshow" class="pav-slideshow">
	<div class="container">
		<div class="container-inner">
			<?php if( count($modules) == 1 ) { ?>
				<?php echo $modules[0]; ?>
			<?php } else { ?>
				<?php $j=1;foreach ($modules as $i =>  $module) {   ?>
				<?php if(  $i++%$cols == 0 || count($modules)==1 ){  $j=1;?><div class="row-fluid"><?php } ?>	
				<div class="<?php echo $class[$j];?>"><?php echo $module; ?></div>
				<?php if( $i%$cols == 0 || $i==count($modules) ){ ?></div><?php } ?>	
				<?php  $j++;  } ?>
			<?php } ?>
		</div>
	</div>
</section>
		<?php } ?>

	<?php
/**
 * Promotion modules
 * $ospans allow overrides width of columns base on thiers indexs. format array( column-index=>span number ), example array( 1=> 3 )[value from 1->12]
 */
$modules = $helper->getModulesByPosition( 'showcase' ); 
$ospans = array();
$cols   = 1;
if( count($modules) ){?>
<section class="pav-showcase" id="pavo-showcase">
	<div class="container">
		<div class="container-inner">
				<?php $j=1;foreach ($modules as $i =>  $module) {   ?>
			<?php if( $i++%$cols == 0 || count($modules)==1 ){  $j=1;?><div class="row-fluid"><?php } ?>	
			<div class="span<?php echo floor(12/$cols);?>"><?php echo $module; ?></div>
			<?php if( $i%$cols == 0 || $i==count($modules) ){ ?></div><?php } ?>	
			<?php  $j++;  } ?>
		</div>
	</div>
</section>
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
		<section class="pav-promotion" id="pav-promotion">
			<div class="container">
				<div class="container-inner">
				<?php $j=1;foreach ($modules as $i =>  $module) {   ?>
					<?php if( $i++%$cols == 0 || count($modules)==1 ){  $j=1;?><div class="row-fluid"><?php } ?>	
					<div class="<?php echo $class[$j];?>"><?php echo $module; ?></div>
					<?php if( $i%$cols == 0 || $i==count($modules) ){ ?></div><?php } ?>	
				<?php  $j++;  } ?>
				</div>
			</div>
		</section>
		<?php } ?>
	<div id="sys-notification"><div class="container"><div class="container-inner"><div id="notification"></div></div></div></div>
	<section id="columns"><div class="container"><div class="container-inner"><div class="row-fluid">
		
		