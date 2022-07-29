<?php
$LANGUAGE_ID = $this->config->get('config_language_id');
$consumer_keyy = $this->config->get('consumer_keyy');
$consumer_secrett = $this->config->get('consumer_secrett');
$access_token = $this->config->get('access_token');
$token_secret = $this->config->get('token_secret');
$custom_css = $this->config->get('custom_css');
$gazal_time_text = $this->config->get('gazal_time_text');
$gazal_firstblock_img = $this->config->get('gazal_firstblock_img');
$gazal_firstblock_title = $this->config->get('gazal_firstblock_title');
$gazal_firstblock_desc = $this->config->get('gazal_firstblock_desc');
$gazal_secondblock_img = $this->config->get('gazal_secondblock_img');
$gazal_secondblock_title = $this->config->get('gazal_secondblock_title');
$gazal_secondblock_desc = $this->config->get('gazal_secondblock_desc');
$gazal_thirdblock_img = $this->config->get('gazal_thirdblock_img');
$gazal_thirdblock_title = $this->config->get('gazal_thirdblock_title');
$gazal_thirdblock_desc = $this->config->get('gazal_thirdblock_desc');
$gazal_fourthblock_img = $this->config->get('gazal_fourthblock_img');
$gazal_fourthblock_title = $this->config->get('gazal_fourthblock_title');
$gazal_fourthblock_desc = $this->config->get('gazal_fourthblock_desc');
?>
<!DOCTYPE html>
<!--[if lt IE 7 ]> <html lang="en" class="no-js ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="en" class="no-js ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="en" class="no-js ie8"> <![endif]-->
<!--[if IE 9 ]>    <html lang="en" class="no-js ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!-->
<html dir="<?php echo $direction; ?>" lang="<?php echo $lang; ?>"
      prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">
<!--<![endif]--><head>

    <meta charset="UTF-8" />
    
    <!-- Mobile Specific Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />

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
    
    <?php foreach ($styles as $style) { ?>
    <link rel="<?php echo $style['rel']; ?>" href="<?php echo $style['href']; ?>" media="<?php echo $style['media']; ?>" />
    <?php } ?>
    
    <!-- jQuery Library --><script src="catalog/view/javascript/jquery/jquery-1.7.1.min.js"></script>
    <!--[if lt IE 9]><script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
    
    <!-- ///////////// IF RTL ///////////////// -->
    <?php if ($direction == 'rtl') { ?>
    <!-- MAIN STYLE --><link rel="stylesheet" href="catalog/view/theme/gazal/stylesheet/rtl/stylesheet.css" />
    <!-- cloud zoom --><link href="catalog/view/theme/gazal/stylesheet/rtl/cloud-zoom.css" />
    <!-- Custom.js for included scripts --><script src="catalog/view/javascript/jquery/custom-rtl.js"></script>
    <!-- cloud zoom --><script src="catalog/view/javascript/jquery/cloud-zoom.1.0.2.min-rtl.js"></script>
    
    <!-- ///////////// IF LTR ///////////////// -->
    <?php } else { ?>
    <!-- MAIN STYLE --><link rel="stylesheet" href="catalog/view/theme/gazal/stylesheet/stylesheet.css" />
	<!-- cloud zoom --><link href="catalog/view/theme/gazal/stylesheet/cloud-zoom.css" />
    <!-- Custom.js for included scripts --><script src="catalog/view/javascript/jquery/custom.js"></script>
    <!-- cloud zoom --><script src="catalog/view/javascript/jquery/cloud-zoom.1.0.2.min.js"></script>
    <?php } ?>

	<!--960 GRID SYSTEM--><script src="catalog/view/javascript/jquery/adapt.min.js"></script>
    <!-- UItoTop plugin --><link rel="stylesheet" media="screen,projection" href="catalog/view/theme/gazal/stylesheet/ui.totop.css" />
	<!-- UItoTop plugin --><script src="catalog/view/javascript/jquery/jquery.ui.totop.js" ></script>
    <!--flex slider--><link rel="stylesheet" href="catalog/view/theme/gazal/stylesheet/flexslider.css" media="screen" />
    <!--flex slider--><script src="catalog/view/javascript/jquery/jquery.easing.js"></script>
	<!--flex slider--><script defer src="catalog/view/javascript/jquery/jquery.flexslider.min.js"></script>
    <!--carouFredSel--><script src="catalog/view/javascript/jquery/jquery.carouFredSel-6.1.0-packed.js"></script>
    <!--carouFredSel - optional--><script src="catalog/view/javascript/jquery/jquery.touchSwipe.min.js"></script>
    <!--carouFredSel - optional--><script src="catalog/view/javascript/jquery/jquery.ba-throttle-debounce.min.js"></script>
    <!--twitter widget--><script src="catalog/view/javascript/jquery/twitter/jquery.tweet.min.js"></script>
    <?php     
    $_SESSION['consumer_keyy']=$consumer_keyy[$LANGUAGE_ID];
    $_SESSION['consumer_secrett']=$consumer_secrett[$LANGUAGE_ID];
    $_SESSION['access_token']=$access_token[$LANGUAGE_ID];
    $_SESSION['token_secret']=$token_secret[$LANGUAGE_ID];
    ?>
    
    <!-- Autofill search --><script src="catalog/view/javascript/jquery/livesearch.js"></script>
        
    <script src="catalog/view/javascript/jquery/ui/jquery-ui-1.8.16.custom.min.js"></script>
    <link rel="stylesheet" href="catalog/view/javascript/jquery/ui/themes/ui-lightness/jquery-ui-1.8.16.custom.css" />
    <script src="catalog/view/javascript/jquery/colorbox/jquery.colorbox.js"></script>
    <link rel="stylesheet" href="catalog/view/javascript/jquery/colorbox/colorbox.css" media="screen" />
    <script src="catalog/view/javascript/common.js"></script>
    <?php foreach ($scripts as $script) { ?>
    <script src="<?php echo $script; ?>"></script>
    <?php } ?>
   
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

    
<!--****************************************************************************************************************-->

<!--=========================================================== THEME OPTIONS START HERE ===========================-->

<!--****************************************************************************************************************-->
<?php
// If gazal module is enabled
if($this->config->get('gazal_status')== 1) { ?>
	
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=<?php echo $this->config->get('gazal_body_font'); ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=<?php echo $this->config->get('gazal_title_font'); ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=<?php echo $this->config->get('gazal_button_font'); ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=<?php echo $this->config->get('gazal_category_font'); ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=<?php echo $this->config->get('gazal_menu_font'); ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=<?php echo $this->config->get('gazal_product_font'); ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=<?php echo $this->config->get('gazal_price_font'); ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=<?php echo $this->config->get('gazal_boxheading_font'); ?>">
		
<style>			
/********************************************
*********************************************

				Background/Patterns

*********************************************
********************************************/
		
		/** Body background image/pattern/color **/
		body {
			<?php if($this->config->get('gazal_background_color')!='') { ?>
				background-color: <?php echo $this->config->get('gazal_background_color'); ?>;
			<?php } 

			if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') $path_image = HTTPS_IMAGE;
			else $path_image = HTTP_IMAGE;

			if($this->config->get('gazal_custom_image')!='') { ?>
				background-image: url("<?php echo $path_image . $this->config->get('gazal_custom_image') ?>"); ?>;
				background-position: top center;
				background-repeat: no-repeat;
				background-attachment: fixed;
			<?php } else if($this->config->get('gazal_custom_pattern')!='') { ?>
				background-image: url("<?php echo $path_image . $this->config->get('gazal_custom_pattern') ?>"); ?>;
				background-attachment: scroll;
			<?php } else if($this->config->get('gazal_pattern_overlay')!='none') { ?>
				background-image: url("catalog/view/theme/gazal/image/patterns/<?php echo $this->config->get('gazal_pattern_overlay'); ?>.png");
				background-attachment: scroll;
			<?php } else { ?>
				background-image: none;
			<?php } ?>

		}


		/** Container background image/pattern/color **/
		.container_12 {
			<?php if($this->config->get('gazal_background_color_cont')!='') { ?>
				background-color: <?php echo $this->config->get('gazal_background_color_cont'); ?>;
			<?php } 

			if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') $path_image = HTTPS_IMAGE;
			else $path_image = HTTP_IMAGE;

			if($this->config->get('gazal_custom_image_cont')!='') { ?>
				background-image: url("<?php echo $path_image . $this->config->get('gazal_custom_image_cont') ?>"); ?>;
				background-position: top center;
				background-repeat: no-repeat;
			<?php } else if($this->config->get('gazal_custom_pattern_cont')!='') { ?>
				background-image: url("<?php echo $path_image . $this->config->get('gazal_custom_pattern_cont') ?>"); ?>;
			<?php } else if($this->config->get('gazal_pattern_cont')!='none') { ?>
				background-image: url("catalog/view/theme/gazal/image/patterns/<?php echo $this->config->get('gazal_pattern_cont'); ?>.png");
			<?php } else { ?>
				background-image: none;
			<?php } ?>

		}
		   <?php 




/********************************************
*********************************************

		 Colors / Background colors

*********************************************
********************************************/
		
		/*//////////////////////////////////////////////////////////////////////////////////////////////////////////////////// Header area **/		
		/** First Theme Color (Top bar) **/
		if($this->config->get('gazal_thickbar_color')!='') { ?>
			.strip, .menuDevices select {
				background: <?php echo $this->config->get('gazal_thickbar_color'); ?>;
			}
		<?php } 
		/** Welcome text color **/
		if($this->config->get('gazal_welcome_text_color')!='') { ?>
			#welcome, #welcome a, .timer {
				color: <?php echo $this->config->get('gazal_welcome_text_color'); ?> !important;
			}
		<?php } 
		
		/*//////////////////////////////////////////////////////////////////////////////////////////////////////////////////// Blocks area **/		
		/** block title **/
		if($this->config->get('gazal_block_title_color')!='') { ?>
			.title-block-top {
				color: <?php echo $this->config->get('gazal_block_title_color'); ?> !important;
			}
		<?php } 
		/** block desc **/
		if($this->config->get('gazal_block_desc_color')!='') { ?>
			.top-blocks li p {
				color: <?php echo $this->config->get('gazal_block_desc_color'); ?>;
			}
		<?php } 
		
		/*//////////////////////////////////////////////////////////////////////////////////////////////////////////////////// Menu **/
		/** Menu background **/
		if($this->config->get('gazal_menu_background')!='') { ?>
			.top-menu {
				background: <?php echo $this->config->get('gazal_menu_background'); ?>;
			}
		<?php } 
		/** Menu border **/
		if($this->config->get('gazal_menu_border')!='') { ?>
			.top-menu {
				border-bottom: 3px solid <?php echo $this->config->get('gazal_menu_border'); ?>;
			}
		<?php } 	
		/** Menu Links **/
		if($this->config->get('gazal_menu_color')!='') { ?>
			.top-menu ul li, .top-menu ul li a {
				color: <?php echo $this->config->get('gazal_menu_color'); ?>;
			}
		<?php }  	
		/** Menu Links :hover **/
		if($this->config->get('gazal_menu_color_hover')!='') { ?>
			.top-menu ul li:hover, .top-menu ul li:hover a {
				color: <?php echo $this->config->get('gazal_menu_color_hover'); ?>;
			}
		<?php }		
		/** Menu background links hover  **/
		if($this->config->get('gazal_menu_bg_links_hover')!='') { ?>
			.top-menu ul li:hover {
				background-color: <?php echo $this->config->get('gazal_menu_bg_links_hover')?>;
			}
		<?php } 
		/** Sub menu Headings **/
		if($this->config->get('gazal_sub_menu_heading')!='') { ?>
			a.mainCat, .mainCat, .one-dept li a:hover {
				color: <?php echo $this->config->get('gazal_sub_menu_heading')?> !important;
			}
		<?php }
		/** Sub Menu Links **/
		if($this->config->get('gazal_smenu_color')!='') { ?>
			.one-dept li a {
				color: <?php echo $this->config->get('gazal_smenu_color'); ?> !important;
			}
		<?php } 
		/** Sub Menu Links :hover **/
		if($this->config->get('gazal_smenuh_color')!='') { ?>
			.one-dept li a:hover {
				color: <?php echo $this->config->get('gazal_smenuh_color'); ?> !important;
			}
		<?php } 
		
		/*//////////////////////////////////////////////////////////////////////////////////////////////////////////////////// cart **/
		/** cart title **/
		if($this->config->get('gazal_cart_title_color')!='') { ?>
			#cart .title-block-top {
				color: <?php echo $this->config->get('gazal_cart_title_color'); ?> !important;
			}
		<?php } 
		/** cart desc **/
		if($this->config->get('gazal_cart_desc_color')!='') { ?>
			#cart-total {
				color: <?php echo $this->config->get('gazal_cart_desc_color'); ?> !important;
			}
		<?php } 
		
		/*//////////////////////////////////////////////////////////////////////////////////////////////////////////////////// search **/
		/** search bg **/
		if($this->config->get('gazal_search_bg')!='') { ?>
			#search {
				background: <?php echo $this->config->get('gazal_search_bg')?>;
			}
		<?php } 
		/** search border **/
		if($this->config->get('gazal_search_border')!='') { ?>
			#search {
				border-bottom: 3px solid <?php echo $this->config->get('gazal_search_border')?>;
			}
		<?php } 
			
		/*//////////////////////////////////////////////////////////////////////////////////////////////////////////////////// category **/
		/** Category heading background **/
		if($this->config->get('gazal_category_heading_bg')!='') { ?>
			.category .box-heading {
				background: <?php echo $this->config->get('gazal_category_heading_bg'); ?>  !important;
			}
		<?php }
		/** Category heading border **/
		if($this->config->get('gazal_category_heading_border')!='') { ?>
			.category .box-heading {
				border-bottom: 3px solid <?php echo $this->config->get('gazal_category_heading_border'); ?>  !important;
			}
		<?php } 
		/** Category heading color **/
		if($this->config->get('gazal_category_heading_color')!='') { ?>
			.category .box-heading {
				color: <?php echo $this->config->get('gazal_category_heading_color'); ?>  !important;
			}
		<?php } 
		/** Category Links color **/
		if($this->config->get('gazal_category_link_color')!='') { ?>
			ul.mega-category li a {
				color: <?php echo $this->config->get('gazal_category_link_color'); ?>;
			}
		<?php }
		/** Category Links color on hover **/
		if($this->config->get('gazal_category_link_color_hover')!='') { ?>
			ul.mega-category li a:hover {
				color: <?php echo $this->config->get('gazal_category_link_color_hover'); ?>;
			}
		<?php } 
		/** Category Internal title **/
		if($this->config->get('gazal_category_title')!='') { ?>
			.sub-menu h3 {
				color: <?php echo $this->config->get('gazal_category_title'); ?> !important;
			}
		<?php } 
		
		/*//////////////////////////////////////////////////////////////////////////////////////////////////////////////////// Box **/
		/** Box Heading background color **/
		if($this->config->get('gazal_heading_bg')!='') { ?>
			.box .box-heading, #column-left .box-heading, #column-right .box-heading, .realted-title, .product-filter, .tabs-head li a {
				background: <?php echo $this->config->get('gazal_heading_bg'); ?>;
			}
		<?php } 
		/** Box Heading border color **/
		if($this->config->get('gazal_heading_border')!='') { ?>
			.box .box-heading, #column-left .box-heading, #column-right .box-heading, .realted-title, .product-filter {
				border-bottom: 3px solid <?php echo $this->config->get('gazal_heading_border'); ?>;
			}
		<?php } 
		/** Box Heading Text color **/
		if($this->config->get('gazal_heading_color')!='') { ?>
			.box .box-heading, #column-left .box-heading, #column-right .box-heading, .realted-title, .product-filter, .tabs-head li a {
				color: <?php echo $this->config->get('gazal_heading_color'); ?> !important;
			}
		<?php } 
		/** Tabs Heading background color **/
		if($this->config->get('gazal_heading_tabs_bg')!='') { ?>
			.cat-title {
				background: <?php echo $this->config->get('gazal_heading_tabs_bg'); ?>;
			}
		<?php } 
		/** Tabs Heading border color **/
		if($this->config->get('gazal_heading_tabs_border')!='') { ?>
			.cat-title {
				border-bottom: 4px solid <?php echo $this->config->get('gazal_heading_tabs_border'); ?>;
			}
		<?php } 
		/** prodbcat title color **/
		if($this->config->get('gazal_prodbcat_title')!='') { ?>
			h2.cat-title {
				color: <?php echo $this->config->get('gazal_prodbcat_title'); ?> !important;
			}
		<?php } 
		/** prodbcat viewall color **/
		if($this->config->get('gazal_prodbcat_viewall')!='') { ?>
			.cat-title a {
				color: <?php echo $this->config->get('gazal_prodbcat_viewall'); ?>;
			}
		<?php } 
		/** prodbcat tab bg **/
		if($this->config->get('gazal_prodbcat_tab_bg')!='') { ?>
			.mod a {
				background: <?php echo $this->config->get('gazal_prodbcat_tab_bg'); ?>;
			}
		<?php } 
		/** prodbcat tab selected **/
		if($this->config->get('gazal_prodbcat_tab_bg_select')!='') { ?>
			.mod a.selected {
				background: <?php echo $this->config->get('gazal_prodbcat_tab_bg_select'); ?>;
			}
		<?php } 
		/** prodbcat tab link coloe **/
		if($this->config->get('gazal_prodbcat_tab_link')!='') { ?>
			.mod a {
				color: <?php echo $this->config->get('gazal_prodbcat_tab_link'); ?>;
			}
		<?php } 
		
		/*//////////////////////////////////////////////////////////////////////////////////////////////////////////////////// Product **/
		/** product border **/
		if($this->config->get('gazal_product_border')!='') { ?>
			.jcarousel-item + .jcarousel-item, .main2 + .main2, .main + .main {
				border-left: 1px dashed <?php echo $this->config->get('gazal_product_border'); ?>;
			}
		<?php } 
		/** Sale bg **/
		if($this->config->get('gazal_sale_bg')!='') { ?>
			.sale {
				background: <?php echo $this->config->get('gazal_sale_bg'); ?>;
			}
		<?php } 
		/** Price color **/
		if($this->config->get('gazal_price_color')!='') { ?>
			.price, .price-new {
				color: <?php echo $this->config->get('gazal_price_color'); ?> !important;
			}
		<?php }
		/** Old Price color **/
		if($this->config->get('gazal_other_oldprice_color')!='') { ?>
			.price-old {
				color: <?php echo $this->config->get('gazal_other_oldprice_color'); ?> !important;
			}
		<?php } 
		/** Product name **/
		if($this->config->get('gazal_product_name_color')!='') { ?>
			.name a, .product-grid .name a, .product-list .name a, .box-product .name a {
				color: <?php echo $this->config->get('gazal_product_name_color'); ?> !important;
			}
		<?php } 
		
		/*//////////////////////////////////////////////////////////////////////////////////////////////////////////////////// General **/
		/** Titles (h1, h2, h3, h4, h5, h6) **/
		if($this->config->get('gazal_title_color')!='') { ?>
			.heading h1, .checkout-heading, table.list thead td, h1, h2, h3, h4, h5, h6, .welcome  {
				color: <?php echo $this->config->get('gazal_title_color'); ?> !important;
			}
		<?php } 
		/** Body text color **/
		if($this->config->get('gazal_bodytext_color')!='') { ?>
			body, #maincontent a, #maincontent a:visited, a.backtotop, #cart .content a, .contentset {
				color: <?php echo $this->config->get('gazal_bodytext_color'); ?>;
			}
		<?php } 
		/** Other Links **/
		if($this->config->get('gazal_links_color')!='') { ?>
			a, .breadcrumb a + a, a:visited, a b{
				color: <?php echo $this->config->get('gazal_links_color'); ?>;
			}
		<?php } 

		/*//////////////////////////////////////////////////////////////////////////////////////////////////////////////////// Footer **/
		/** Footer BG **/
		if($this->config->get('gazal_footer_bg')!='') { ?>
			.footer-bg {
				background: <?php echo $this->config->get('gazal_footer_bg'); ?> !important;
			}
		<?php } 
		/** Footer Bottom BG **/
		if($this->config->get('gazal_footer_bottom_bg')!='') { ?>
			footer {
				background: <?php echo $this->config->get('gazal_footer_bottom_bg'); ?> !important;
			}
		<?php } 
		/** Footer Headers Text color **/
		if($this->config->get('gazal_footerheaders_color')!='') { ?>
			footer h3, .about h3 {
				color: <?php echo $this->config->get('gazal_footerheaders_color'); ?> !important;
			}
		<?php } 
		/** Footer widgets Headers Text color **/
		if($this->config->get('gazal_footerheaderswid_color')!='') { ?>
			h3.widgetsTitle {
				color: <?php echo $this->config->get('gazal_footerheaderswid_color'); ?> !important;
			}
		<?php } 
		/** Footer widgets Headers Text bg **/
		if($this->config->get('gazal_footerheaderswid_bg')!='') { ?>
			h3.widgetsTitle {
				background: <?php echo $this->config->get('gazal_footerheaderswid_bg'); ?>;
			}
		<?php } 
		/** Footer widgets Headers Text border **/
		if($this->config->get('gazal_footerheaderswid_border')!='') { ?>
			h3.widgetsTitle {
				border-bottom: 3px solid <?php echo $this->config->get('gazal_footerheaderswid_border'); ?>;
			}
		<?php } 
		/** Footer first border color **/
		if($this->config->get('gazal_first_border_color')!='') { ?>
			.about h3 {
				border-bottom: 1px solid <?php echo $this->config->get('gazal_first_border_color'); ?>;
			}
			hr {
				border-top: 1px solid <?php echo $this->config->get('gazal_first_border_color'); ?>;
			}
		<?php } 
		/** Footer second border color **/
		if($this->config->get('gazal_second_border_color')!='') { ?>
			hr {
				border-bottom: 1px solid <?php echo $this->config->get('gazal_second_border_color'); ?>;
			}
		<?php } 
		/** Footer info text color **/
		if($this->config->get('gazal_footer_info_color')!='') { ?>
			.footer-bg, .about, .shipping{
				color: <?php echo $this->config->get('gazal_footer_info_color'); ?> !important;
			}
		<?php } 
		/** Footer Links Text color **/
		if($this->config->get('gazal_footerlinks_color')!='') { ?>
			footer a, .footer-bg a, .tweet_list li a {
				color: <?php echo $this->config->get('gazal_footerlinks_color'); ?> !important;
			}
		<?php } 

/********************************************
*********************************************

				Buttons

*********************************************
********************************************/		
		
		/** add to cart details **/
		if($this->config->get('gazal_details_addtocart')!='') { ?>
			 .product-info .cart input {
				background: <?php echo $this->config->get('gazal_details_addtocart'); ?>;
			}
		<?php }
		/** add to cart details : hover **/
		if($this->config->get('gazal_details_addtocart_hover')!='') { ?>
			 .product-info .cart input:hover {
				background: <?php echo $this->config->get('gazal_details_addtocart_hover'); ?>;
			}
		<?php }
		/** button text details color **/
		if($this->config->get('gazal_details_button_color')!='') { ?>
			.product-info .cart input{
				color: <?php echo $this->config->get('gazal_details_button_color'); ?>;
			}
		<?php } 
		/** All other buttons **/
		if($this->config->get('gazal_all_addtocart')!='') { ?>
			 a.button, .cart input, a.ch, a.ca, .sub-menu .button, input.button {
				background: <?php echo $this->config->get('gazal_all_addtocart'); ?>;
			}
		<?php }
		/** All other buttons : hover **/
		if($this->config->get('gazal_all_addtocart_hover')!='') { ?>
			a.button:hover, .cart input:hover, a.ch:hover, a.ca:hover, .sub-menu .button:hover, input.button:hover {
				background: <?php echo $this->config->get('gazal_all_addtocart_hover'); ?>;
			}
		<?php }
		/** All other buttons text color **/
		if($this->config->get('gazal_all_buttons_color')!='') { ?>
			input.button, a.button, a.ch, a.ca, .sub-menu .button, input.button {
				color: <?php echo $this->config->get('gazal_all_buttons_color'); ?> !important;
			}
		<?php } 
			
/********************************************
*********************************************

				Fonts

*********************************************
********************************************/
		/** Body Fonts **/ ?>

        <?php if ($direction != 'rtl') {
		if ($this->config->get('gazal_body_font') != '' ) { ?>
		body, p, .mod a { font-family:'<?php echo $this->config->get('gazal_body_font_family'); ?>', Tahoma, Geneva, sans-serif; 
		font-size: <?php echo $this->config->get('gazal_body_font_size'); ?>px !important;
		}
		<?php } 
		/** Title Fonts **/
		if($this->config->get('gazal_title_font')!='') { ?>
		h1, h2, h3, h4, .contentset, legend, .checkout-heading, .welcome {
			font-family:'<?php echo $this->config->get('gazal_title_font_family'); ?>', Tahoma, Geneva, sans-serif;
			font-size: <?php echo $this->config->get('gazal_title_font_size'); ?>px !important;
		}
		<?php }
		/** Button Fonts **/
		if ($this->config->get('gazal_button_font') != '') { ?>
		input, input.button, a.button, .button {
			font-family:'<?php echo $this->config->get('gazal_button_font_family'); ?>', Tahoma, Geneva, sans-serif;
			font-size: <?php echo $this->config->get('gazal_button_font_size'); ?>px !important;
		}
		<?php }
		/** category Fonts **/
		if ($this->config->get('gazal_category_font') != '') { ?>
		 ul.mega-category li a, ul.mega-category li .sub-sub-menu ul li.heading-sub a {
			font-family:'<?php echo $this->config->get('gazal_category_font_family'); ?>', Tahoma, Geneva, sans-serif;
			font-size: <?php echo $this->config->get('gazal_category_font_size'); ?>px !important;
		}
		<?php }
		/** menu Fonts **/
		if ($this->config->get('gazal_menu_font') != '') { ?>
		 .top-menu ul li, .top-menu ul li a {
			font-family:'<?php echo $this->config->get('gazal_menu_font_family'); ?>', Tahoma, Geneva, sans-serif;
			font-size: <?php echo $this->config->get('gazal_menu_font_size'); ?>px !important;
		}
		<?php }
		/** product Fonts **/
		if ($this->config->get('gazal_product_font') != '') { ?>
		 .box-product .name a {
			font-family:'<?php echo $this->config->get('gazal_product_font_family'); ?>', Tahoma, Geneva, sans-serif;
			font-size: <?php echo $this->config->get('gazal_product_font_size'); ?>px !important;
		}
		<?php }
		/** price Fonts **/
		if ($this->config->get('gazal_price_font') != '') { ?>
		 .box-product .price {
			font-family:'<?php echo $this->config->get('gazal_price_font_family'); ?>', Tahoma, Geneva, sans-serif !important;
			font-size: <?php echo $this->config->get('gazal_price_font_size'); ?>px !important;
		}
		<?php }
		/** boxheading Fonts **/
		if ($this->config->get('gazal_boxheading_font') != '') { ?>
	    .box-heading, h2.cat-title {
			font-family:'<?php echo $this->config->get('gazal_boxheading_font_family'); ?>', Tahoma, Geneva, sans-serif !important;
			font-size: <?php echo $this->config->get('gazal_boxheading_font_size'); ?>px !important;
		}
		<?php } }
		
		/*IF CUSTOM CSS SO GOES HERE*/
		if ($custom_css[$LANGUAGE_ID] != '')  ?>
			<?php echo $custom_css[$LANGUAGE_ID]; ?>
		<?php 
		
		?>
		
	</style>
<?php } ?>
<!--**************************************************************************************************************-->
<!--=========================================================== THEME OPTIONS END HERE ===========================-->
<!--**************************************************************************************************************-->
 
</head>

<body>

<div class="container_12">

    <header>
      
      <!-- Our Time -->
	  <div class="timer grid_6">
      <?php if($gazal_time_text[$LANGUAGE_ID] != '') { ?>
      <img src="catalog/view/theme/gazal/image/clock.png" alt="" >&nbsp; <?php echo $gazal_time_text[$LANGUAGE_ID]?>
      <?php } ?>
      </div>
      
      <!-- Login -->
	  <div id="welcome" class="grid_6">
        <?php if (!$logged) { ?>
        <?php echo $text_welcome; ?>
        <?php } else { ?>
        <?php echo $text_logged; ?>
        <?php } ?>
      </div>
      
      
      <div class="clearfix"></div>
      
      
      <div class="strip grid_12">
      	<span class="left-part"></span>
        <span class="right-part"></span>
        <!-- Links -->
        <div class="links">
          <a href="<?php echo $wishlist; ?>" id="wishlist-total"><?php echo $text_wishlist; ?></a>
          <a href="<?php echo $account; ?>"><?php echo $text_account; ?></a>
          <a href="<?php echo $shopping_cart; ?>"><?php echo $text_shopping_cart; ?></a>
          <a href="<?php echo $checkout; ?>"><?php echo $text_checkout; ?></a>
      </div>
      <?php echo $currency; ?>
      <?php echo $language; ?>
      </div><!--/strip-->
      
      
      <div class="clearfix"></div>
      
      
      
      <?php if ($logo) { ?>
      <div id="logo" class="grid_3">
      	<a href="<?php echo $home; ?>">
        	<img src="<?php echo $logo; ?>" title="<?php echo $name; ?>" alt="<?php echo $name; ?>" />
        </a>
      </div>
      <?php } ?>
      
      <!-- Top Blocks -->
      <div class="grid_7">
      	<ul class="top-blocks">
        	<li>
            	<?php 
                if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') $path_image = HTTPS_IMAGE;
				else $path_image = HTTP_IMAGE; 
                if($gazal_firstblock_img[$LANGUAGE_ID]!='') { ?>
            	<img src="<?php echo $path_image . $gazal_firstblock_img[$LANGUAGE_ID] ?>" alt="" ><!--custom image-->
                <?php } ?>    
                <!--title-->
                <?php if($gazal_firstblock_title[$LANGUAGE_ID] != '') { ?>
                <h5 class="title-block-top"><?php echo $gazal_firstblock_title[$LANGUAGE_ID]?></h5><!--custom title-->
                <?php } ?>
                <p><!--custom data-->
                <?php if($gazal_firstblock_desc[$LANGUAGE_ID] != '') { ?>
                <?php echo $gazal_firstblock_desc[$LANGUAGE_ID]?>
                <?php } ?>
                </p>
            </li>
            <li>
            	<?php 
                if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') $path_image = HTTPS_IMAGE;
				else $path_image = HTTP_IMAGE; 
                if($gazal_secondblock_img[$LANGUAGE_ID]!='') { ?>
            	<img src="<?php echo $path_image . $gazal_secondblock_img[$LANGUAGE_ID] ?>" alt="" ><!--custom image-->
                <?php } ?>    
                <!--title-->
                <?php if($gazal_secondblock_title[$LANGUAGE_ID] != '') { ?>
                <h5 class="title-block-top"><?php echo $gazal_secondblock_title[$LANGUAGE_ID]?></h5><!--custom title-->
                <?php } ?>
                <p><!--custom data-->
                <?php if($gazal_secondblock_desc[$LANGUAGE_ID] != '') { ?>
                <?php echo $gazal_secondblock_desc[$LANGUAGE_ID]?>
                <?php } ?>
                </p>
            </li>
            <li>
            	<?php 
                if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') $path_image = HTTPS_IMAGE;
				else $path_image = HTTP_IMAGE; 
                if($gazal_thirdblock_img[$LANGUAGE_ID]!='') { ?>
            	<img src="<?php echo $path_image . $gazal_thirdblock_img[$LANGUAGE_ID] ?>" alt="" ><!--custom image-->
                <?php } ?>    
                <!--title-->
                <?php if($gazal_thirdblock_title[$LANGUAGE_ID] != '') { ?>
                <h5 class="title-block-top"><?php echo $gazal_thirdblock_title[$LANGUAGE_ID]?></h5><!--custom title-->
                <?php } ?>
                <p><!--custom data-->
                <?php if($gazal_thirdblock_desc[$LANGUAGE_ID] != '') { ?>
                <?php echo $gazal_thirdblock_desc[$LANGUAGE_ID]?>
                <?php } ?>
                </p>
            </li>
            <li>
            	<?php 
                if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') $path_image = HTTPS_IMAGE;
				else $path_image = HTTP_IMAGE; 
                if($gazal_fourthblock_img[$LANGUAGE_ID]!='') { ?>
            	<img src="<?php echo $path_image . $gazal_fourthblock_img[$LANGUAGE_ID] ?>" alt="" ><!--custom image-->
                <?php } ?>    
                <!--title-->
                <?php if($gazal_fourthblock_title[$LANGUAGE_ID] != '') { ?>
                <h5 class="title-block-top"><?php echo $gazal_fourthblock_title[$LANGUAGE_ID]?></h5><!--custom title-->
                <?php } ?>
                <p><!--custom data-->
                <?php if($gazal_fourthblock_desc[$LANGUAGE_ID] != '') { ?>
                <?php echo $gazal_fourthblock_desc[$LANGUAGE_ID]?>
                <?php } ?>
                </p>
            </li>
        </ul>
      </div>
      

      <div class="grid_2"><?php echo $cart; ?></div>
      
      
      <div class="clearfix"></div>
        
    </header>
    
    <div class="clearfix"></div>
    
    <!-- top-menu -->
    <div class="top-menu grid_9">
    	<ul>
        	<li><a href="<?php echo $home; ?>"><img class="homeIcon" src="catalog/view/theme/gazal/image/home.png" width="25" alt"<?php echo $text_home; ?>"></a></li>
            <?php if ($categories) { ?>
            <li class="cats"><?php echo $text_categories; ?>
            	<div class="cat-wrap">
                	<?php foreach ($categories as $category) { ?>
                    	<div class="one-dept">
                            <a class="mainCat" href="<?php echo $category['href']; ?>"><?php echo $category['name']; ?></a>
                              <?php if ($category['children']) { ?>
                                  <div>
                                    <?php for ($i = 0; $i < count($category['children']);) { ?>
                                        <ul>
                                          <?php $j = $i + ceil(count($category['children']) / $category['column']); ?>
                                          <?php for (; $i < $j; $i++) { ?>
                                          <?php if (isset($category['children'][$i])) { ?>
                                          <li><a href="<?php echo $category['children'][$i]['href']; ?>"><?php echo $category['children'][$i]['name']; ?></a></li>
                                          <?php } ?>
                                          <?php } ?>
                                        </ul>
                                    <?php } ?>
                                  </div>
                              <?php } ?>
                       </div><!--/one-dept-->
                    <?php } ?>
                </div><!--/cat-wrap-->
            </li>
            <?php } ?>
            <li><a href="index.php?route=information/information&information_id=4"><?php echo $text_about; ?></a></li>
            <li><a href="index.php?route=product/special"><?php echo $text_special; ?></a></li>
            <li><a href="index.php?route=product/manufacturer"><?php echo $text_manufacturer; ?></a></li>
            <li><a href="index.php?route=information/contact"><?php echo $text_contact; ?></a></li>
            <li><a href="index.php?route=news/headlines"><?php echo $text_blog; ?></a></li>
        </ul>
    </div><!-- /top-menu -->
   
    
    <!--***** MENU FOR MOBILE DEVICES RETURNS INTO SELECT ****-->
    <?php if ($categories) { ?>
    <div class="menuDevices grid_9" style="display:none">
      <div class="select_outer">
          <div class="bg_select"></div>
          <select onchange="location=this.value">
                <option><?php echo $text_menu; ?></option>
                <option value="index.php?route=news/headlines"><?php echo $text_blog; ?></option>
                <?php foreach ($categories as $category) { ?>
                <option value="<?php echo $category['href']; ?>"><?php echo $category['name']; ?></option>
                <?php } ?>    
          </select>
      </div><!--/select_outer-->
    </div>
    <?php } ?>
    
    <!-- Search -->
    <div class="grid_3 search">
        <div id="search">
        	<!--btn-->
            <div class="button-search"><img src="catalog/view/theme/gazal/image/search.png" alt="search" ></div>
            <!--input-->
            <input type="text" name="search" placeholder="<?php echo $text_search; ?>" value="<?php echo $search; ?>" />
        </div>
    </div>
    
    <div class="clearfix"></div>
    
    <div id="notification"></div>
	
    <div class="clearfix"></div>