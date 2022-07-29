<?php if (isset($_SERVER['HTTP_USER_AGENT']) && !strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6')) echo '<?xml version="1.0" encoding="UTF-8"?>'. "\n"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
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
	<link href="https://fonts.googleapis.com/css?family=Francois%20One" rel="stylesheet" type="text/css" media="screen" />
	<link href="catalog/view/theme/unistore/stylesheet/stylesheet.css" rel="stylesheet" type="text/css" />
	<link href="catalog/view/theme/unistore/stylesheet/nivo-slider.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" type="text/css" href="catalog/view/theme/unistore/stylesheet/carousel.css" media="screen" />
	<script type="text/javascript" src="catalog/view/theme/unistore/js/jquery-1.6.2.js"></script>
	<script type="text/javascript" src="catalog/view/theme/unistore/js/jquery/ui/jquery-ui-1.8.16.custom.min.js"></script>
	<link rel="stylesheet" type="text/css" href="catalog/view/theme/unistore/js/jquery/ui/themes/ui-lightness/jquery-ui-1.8.16.custom.css" />
	<script type="text/javascript" src="catalog/view/javascript/jquery/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
	<link rel="stylesheet" type="text/css" href="catalog/view/javascript/jquery/fancybox/jquery.fancybox-1.3.4.css" media="screen" />
	<script type="text/javascript" src="catalog/view/javascript/jquery/tabs.js"></script>
	<script type="text/javascript" src="catalog/view/theme/unistore/js/jquery.jcarousel.min.js"></script>
	<script type="text/javascript" src="catalog/view/theme/unistore/js/jquery-workarounds.js"></script>
	<script type="text/javascript" src="catalog/view/theme/unistore/js/nivo-slider/jquery.nivo.slider.pack.js"></script>
	<script type="text/javascript" src="catalog/view/javascript/jquery/ui/external/jquery.cookie.js"></script>
	
	<?php foreach ($scripts as $script) { ?>
	<script type="text/javascript" src="<?php echo $script; ?>"></script>
	<?php } ?>
	<?php echo $google_analytics; ?>

 <?php if($this->config->get('config_image_logo_height')!='') { ?>
 <style>
 #logo img {
 	max-height: <?php echo $this->config->get('config_image_logo_height'); ?>px !important;
 }
 </style>
 <?php }?>

	
	<?php if($this->config->get('unistore_color') == '1') { ?>
	<link href="catalog/view/theme/unistore/stylesheet/sport.css" rel="stylesheet" type="text/css" />
	<?php } ?>
	<?php if($this->config->get('unistore_color') == '2') { ?>
	<link href='https://fonts.googleapis.com/css?family=Leckerli+One' rel='stylesheet' type='text/css'>
	<link href="catalog/view/theme/unistore/stylesheet/kids.css" rel="stylesheet" type="text/css" />
	<?php } ?>
	<?php if($this->config->get('unistore_color') == '3') { ?>
	<link href='https://fonts.googleapis.com/css?family=Old+Standard+TT' rel='stylesheet' type='text/css'>
	<link href="catalog/view/theme/unistore/stylesheet/jew.css" rel="stylesheet" type="text/css" />
	<?php } ?>
	<?php if($this->config->get('unistore_color') == '4') { ?>
	<link href='https://fonts.googleapis.com/css?family=Old+Standard+TT' rel='stylesheet' type='text/css'>
	<link href="catalog/view/theme/unistore/stylesheet/jew2.css" rel="stylesheet" type="text/css" />
	<?php } ?>
	<?php if($this->config->get('unistore_color') == '5') { ?>
	<link href="catalog/view/theme/unistore/stylesheet/fash.css" rel="stylesheet" type="text/css" />
	<?php } ?>
	<?php if($this->config->get('unistore_options_status') == '1') { ?>
		<?php if( $this->config->get('headlines_font') != '' && $this->config->get('headlines_font') != 'standard'){	?>
		<link href='//fonts.googleapis.com/css?family=<?php echo $this->config->get('headlines_font') ?>&v1' rel='stylesheet' type='text/css'>
		
		
		<?php } ?>
		

		<style type="text/css">
				
		<?php if($this->config->get('body_text_content') != '') { ?>
					
			div#content, div#content a, .old-price, .price-old { color:#<?php echo $this->config->get('body_text_content') ?>; }
			
		<?php } ?>
				
		<?php if($this->config->get('body_text_footer') != '') { ?>
					
			div#footer, div#footer a { color:#<?php echo $this->config->get('body_text_footer') ?> !important; }
			
		<?php } ?>
				
		<?php if($this->config->get('new_price_and_footer_contact') != '') { ?>
					
			.new_price, .price, div#footer ul#contact-us li { color:#<?php echo $this->config->get('new_price_and_footer_contact') ?> !important; }
			
		<?php } ?>
				
		<?php if($this->config->get('headlines_content') != '') { ?>
					
			#content h1, #content h2, #content h3, #content h4, #content h5, #content h6, #content strong, #content b, .box-heading { color:#<?php echo $this->config->get('headlines_content') ?> !important; }
			
		<?php } ?>
				
		<?php if($this->config->get('headlines_footer') != '') { ?>
					
			div#footer .footer-top-outside h2, div#footer .footer-navigation h3 { color:#<?php echo $this->config->get('headlines_footer') ?> !important; }
			
		<?php } ?>
				
		<?php if($this->config->get('product_name_and_categories_on_subpages') != '') { ?>
					
			.box-product > div .name a, .box-product > div .name, ul.list-items li .name, ul.list-items li .name a, div.product-grid > div .name, div.product-grid > div .name a, div.product-list > div .description .name, div.product-list > div .description .name a, .box-category a { color:#<?php echo $this->config->get('product_name_and_categories_on_subpages') ?> !important; }
			
		<?php } ?>
		
		<?php if($this->config->get('categories_in_top') != '') { ?>
					
			#categories a { color:#<?php echo $this->config->get('categories_in_top') ?> !important; }
			
		<?php } ?>
		
		<?php if($this->config->get('text_in_top') != '') { ?>
					
			div#header .search .enterkey, div#header .shopping-cart .welcome-text, div#header .shopping-cart .welcome-text a, div#header .shopping-cart > div h2 { color:#<?php echo $this->config->get('text_in_top') ?> !important; }
			
		<?php } ?>
		
		<?php if($this->config->get('text_in_bar_in_top') != '') { ?>
					
			div.header-bar, div.header-bar a { color:#<?php echo $this->config->get('text_in_bar_in_top') ?> !important; }
			
		<?php } ?>
		
		<?php if( $this->config->get('headlines_font') != '' && $this->config->get('headlines_font') != 'standard'){	?>
		<?php $toReplace =  $this->config->get('headlines_font'); $font = str_replace("+", " ", $toReplace); ?>
		h1, h2, h3, h4, h5, h6, div.box div.box-heading, div.pagination .links a, div.pagination .links b, .htabs a, div#footer ul#contact-us li { font-family:<?php echo $font; ?> !important; }
		div#categories ul li a { font-family:<?php echo $font; ?>; }
		<?php } ?>
		
		<?php if($this->config->get('body_font') != '' && $this->config->get('body_font') != 'standard') { ?>
					
			body { font-family:<?php echo $this->config->get('body_font') ?> !important; }
			
		<?php } ?>
		
		<?php if($this->config->get('content_top_gradient') != '' && $this->config->get('content_bottom_gradient') != '') { ?>
					
			div#content .button {
				background:#<?php echo $this->config->get('content_bottom_gradient'); ?>;
				background-image: linear-gradient(bottom, #<?php echo $this->config->get('content_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('content_top_gradient'); ?> 60%);
				background-image: -o-linear-gradient(bottom, #<?php echo $this->config->get('content_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('content_top_gradient'); ?> 60%);
				background-image: -moz-linear-gradient(bottom, #<?php echo $this->config->get('content_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('content_top_gradient'); ?> 60%);
				background-image: -webkit-linear-gradient(bottom, #<?php echo $this->config->get('content_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('content_top_gradient'); ?> 60%);
				background-image: -ms-linear-gradient(bottom, #<?php echo $this->config->get('content_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('content_top_gradient'); ?> 60%);

				background-image: -webkit-gradient(
					linear,
					left bottom,
					left top,
					color-stop(0.2, #<?php echo $this->config->get('content_bottom_gradient'); ?>),
					color-stop(0.6, #<?php echo $this->config->get('content_top_gradient'); ?>)
				);
			
			}
			ul.btn li a.prev, .jcarousel-skin-opencart .jcarousel-prev-horizontal {
				background:#<?php echo $this->config->get('content_bottom_gradient'); ?>;
				background:url(catalog/view/theme/unistore/images/button-prev.png) top right no-repeat,  linear-gradient(bottom, #<?php echo $this->config->get('content_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('content_top_gradient'); ?> 60%);
				background:url(catalog/view/theme/unistore/images/button-prev.png) top right no-repeat,  -o-linear-gradient(bottom, #<?php echo $this->config->get('content_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('content_top_gradient'); ?> 60%);
				background:url(catalog/view/theme/unistore/images/button-prev.png) top right no-repeat,  -moz-linear-gradient(bottom, #<?php echo $this->config->get('content_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('content_top_gradient'); ?> 60%);
				background:url(catalog/view/theme/unistore/images/button-prev.png) top right no-repeat, -webkit-linear-gradient(bottom, #<?php echo $this->config->get('content_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('content_top_gradient'); ?> 60%);
				background:url(catalog/view/theme/unistore/images/button-prev.png) top right no-repeat,  -ms-linear-gradient(bottom, #<?php echo $this->config->get('content_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('content_top_gradient'); ?> 60%);

				background:url(catalog/view/theme/unistore/images/button-prev.png) top right no-repeat,  -webkit-gradient(
					linear,
					left bottom,
					left top,
					color-stop(0.2, #<?php echo $this->config->get('content_bottom_gradient'); ?>),
					color-stop(0.6, #<?php echo $this->config->get('content_top_gradient'); ?>)
				);
				border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;
			
			}
			ul.btn li a.next, .jcarousel-skin-opencart .jcarousel-next-horizontal {
				background:#<?php echo $this->config->get('content_bottom_gradient'); ?>;
				background:url(catalog/view/theme/unistore/images/button-next1.png) top left no-repeat,  linear-gradient(bottom, #<?php echo $this->config->get('content_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('content_top_gradient'); ?> 60%);
				background:url(catalog/view/theme/unistore/images/button-next1.png) top left no-repeat,  -o-linear-gradient(bottom, #<?php echo $this->config->get('content_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('content_top_gradient'); ?> 60%);
				background:url(catalog/view/theme/unistore/images/button-next1.png) top left no-repeat,  -moz-linear-gradient(bottom, #<?php echo $this->config->get('content_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('content_top_gradient'); ?> 60%);
				background:url(catalog/view/theme/unistore/images/button-next1.png) top left no-repeat, -webkit-linear-gradient(bottom, #<?php echo $this->config->get('content_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('content_top_gradient'); ?> 60%);
				background:url(catalog/view/theme/unistore/images/button-next1.png) top left no-repeat,  -ms-linear-gradient(bottom, #<?php echo $this->config->get('content_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('content_top_gradient'); ?> 60%);

				background:url(catalog/view/theme/unistore/images/button-next1.png) top left no-repeat,  -webkit-gradient(
					linear,
					left bottom,
					left top,
					color-stop(0.2, #<?php echo $this->config->get('content_bottom_gradient'); ?>),
					color-stop(0.6, #<?php echo $this->config->get('content_top_gradient'); ?>)
				);
				border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;
			
			}
					
		<?php } ?>
		
		<?php if($this->config->get('top_and_footer_top_gradient') != '' && $this->config->get('top_and_footer_bottom_gradient') != '') { ?>
					
			#header .button, #footer .button {
				background:#<?php echo $this->config->get('top_and_footer_bottom_gradient'); ?>;
				background-image: linear-gradient(bottom, #<?php echo $this->config->get('top_and_footer_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('top_and_footer_top_gradient'); ?> 60%);
				background-image: -o-linear-gradient(bottom, #<?php echo $this->config->get('top_and_footer_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('top_and_footer_top_gradient'); ?> 60%);
				background-image: -moz-linear-gradient(bottom, #<?php echo $this->config->get('top_and_footer_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('top_and_footer_top_gradient'); ?> 60%);
				background-image: -webkit-linear-gradient(bottom, #<?php echo $this->config->get('top_and_footer_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('top_and_footer_top_gradient'); ?> 60%);
				background-image: -ms-linear-gradient(bottom, #<?php echo $this->config->get('top_and_footer_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('top_and_footer_top_gradient'); ?> 60%);

				background-image: -webkit-gradient(
					linear,
					left bottom,
					left top,
					color-stop(0.2, #<?php echo $this->config->get('top_and_footer_bottom_gradient'); ?>),
					color-stop(0.6, #<?php echo $this->config->get('top_and_footer_top_gradient'); ?>)
				);
			
			}
					
		<?php } ?>
		
		<?php if($this->config->get('sale_ribbon_top_gradient') != '' && $this->config->get('sale_ribbon_bottom_gradient') != '') { ?>
		
			.product-sale { 
			
				background:#<?php echo $this->config->get('sale_ribbon_bottom_gradient'); ?> !important;
				background: url(catalog/view/theme/unistore/images/text-sale.png) top center no-repeat, linear-gradient(bottom, #<?php echo $this->config->get('sale_ribbon_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('sale_ribbon_top_gradient'); ?> 60%) !important;
				background: url(catalog/view/theme/unistore/images/text-sale.png) top center no-repeat, -o-linear-gradient(bottom, #<?php echo $this->config->get('sale_ribbon_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('sale_ribbon_top_gradient'); ?> 60%) !important;
				background: url(catalog/view/theme/unistore/images/text-sale.png) top center no-repeat, -moz-linear-gradient(bottom, #<?php echo $this->config->get('sale_ribbon_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('sale_ribbon_top_gradient'); ?> 60%) !important;
				background:url(catalog/view/theme/unistore/images/text-sale.png) top center no-repeat, -webkit-linear-gradient(bottom, #<?php echo $this->config->get('sale_ribbon_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('sale_ribbon_top_gradient'); ?> 60%) !important;
				background:url(catalog/view/theme/unistore/images/text-sale.png) top center no-repeat,  -ms-linear-gradient(bottom, #<?php echo $this->config->get('sale_ribbon_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('sale_ribbon_top_gradient'); ?> 60%) !important;

				background:url(catalog/view/theme/unistore/images/text-sale.png) top center no-repeat,  -webkit-gradient(
					linear,
					left bottom,
					left top,
					color-stop(0.2, #<?php echo $this->config->get('sale_ribbon_bottom_gradient'); ?>),
					color-stop(0.6, #<?php echo $this->config->get('sale_ribbon_top_gradient'); ?>)
				) !important;
			
			}
		
		<?php } ?>
		
		<?php if($this->config->get('hover_for_products') != '') { ?>
					
			.box-product > div:hover, div.product-grid > div .image:hover, div.product-list > div .image:hover, ul.bestsellers li .image:hover { border:2px solid #<?php echo $this->config->get('hover_for_products') ?> !important; }
			.tab-content { border:2px solid #<?php echo $this->config->get('hover_for_products') ?> !important; }
			
		<?php } ?>
		
		<?php if($this->config->get('content_color') != '') { ?>
					
			ul.list-items .image { border:2px solid #<?php echo $this->config->get('content_color') ?> !important; }
			
		<?php } ?>
		
		<?php if($this->config->get('categories_in_top_top_gradient') != '' && $this->config->get('categories_in_top_bottom_gradient') != '') { ?>
					
			#categories {
				background:#<?php echo $this->config->get('categories_in_top_bottom_gradient'); ?> !important;
				background-image: linear-gradient(bottom, #<?php echo $this->config->get('categories_in_top_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('categories_in_top_top_gradient'); ?> 60%) !important;
				background-image: -o-linear-gradient(bottom, #<?php echo $this->config->get('categories_in_top_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('categories_in_top_top_gradient'); ?> 60%) !important;
				background-image: -moz-linear-gradient(bottom, #<?php echo $this->config->get('categories_in_top_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('categories_in_top_top_gradient'); ?> 60%) !important;
				background-image: -webkit-linear-gradient(bottom, #<?php echo $this->config->get('categories_in_top_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('categories_in_top_top_gradient'); ?> 60%) !important;
				background-image: -ms-linear-gradient(bottom, #<?php echo $this->config->get('categories_in_top_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('categories_in_top_top_gradient'); ?> 60%) !important;

				background-image: -webkit-gradient(
					linear,
					left bottom,
					left top,
					color-stop(0.2, #<?php echo $this->config->get('categories_in_top_bottom_gradient'); ?>),
					color-stop(0.6, #<?php echo $this->config->get('categories_in_top_top_gradient'); ?>)
				) !important;
				height:49px !important;
			
			}
			#categories div > ul > li { background:none !important; }
					
		<?php } ?>		
		
		<?php if($this->config->get('top_top_gradient') != '' && $this->config->get('top_bottom_gradient') != '') { ?>
					
			#header {
				background:#<?php echo $this->config->get('top_bottom_gradient'); ?> !important;
				background-image: linear-gradient(bottom, #<?php echo $this->config->get('top_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('top_top_gradient'); ?> 60%) !important;
				background-image: -o-linear-gradient(bottom, #<?php echo $this->config->get('top_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('top_top_gradient'); ?> 60%) !important;
				background-image: -moz-linear-gradient(bottom, #<?php echo $this->config->get('top_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('top_top_gradient'); ?> 60%) !important;
				background-image: -webkit-linear-gradient(bottom, #<?php echo $this->config->get('top_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('top_top_gradient'); ?> 60%) !important;
				background-image: -ms-linear-gradient(bottom, #<?php echo $this->config->get('top_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('top_top_gradient'); ?> 60%) !important;

				background-image: -webkit-gradient(
					linear,
					left bottom,
					left top,
					color-stop(0.2, #<?php echo $this->config->get('top_bottom_gradient'); ?>),
					color-stop(0.6, #<?php echo $this->config->get('top_top_gradient'); ?>)
				) !important;
			
			}
					
		<?php } ?>		
		
		<?php if($this->config->get('bar_in_top_top_gradient') != '' && $this->config->get('bar_in_top_bottom_gradient') != '') { ?>
					
			div#header .header-bar-bg, div#header .header-bar, div#header .header-bar > div {
				background:#<?php echo $this->config->get('bar_in_top_bottom_gradient'); ?> !important;
				background-image: linear-gradient(bottom, #<?php echo $this->config->get('bar_in_top_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('bar_in_top_top_gradient'); ?> 60%) !important;
				background-image: -o-linear-gradient(bottom, #<?php echo $this->config->get('bar_in_top_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('bar_in_top_top_gradient'); ?> 60%) !important;
				background-image: -moz-linear-gradient(bottom, #<?php echo $this->config->get('bar_in_top_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('bar_in_top_top_gradient'); ?> 60%) !important;
				background-image: -webkit-linear-gradient(bottom, #<?php echo $this->config->get('bar_in_top_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('bar_in_top_top_gradient'); ?> 60%) !important;
				background-image: -ms-linear-gradient(bottom, #<?php echo $this->config->get('bar_in_top_bottom_gradient'); ?> 20%, #<?php echo $this->config->get('bar_in_top_top_gradient'); ?> 60%) !important;

				background-image: -webkit-gradient(
					linear,
					left bottom,
					left top,
					color-stop(0.2, #<?php echo $this->config->get('bar_in_top_bottom_gradient'); ?>),
					color-stop(0.6, #<?php echo $this->config->get('bar_in_top_top_gradient'); ?>)
				) !important;
			
			}
			div#header .header-bar { -webkit-border-radius:0px 0px 0px 10px;border-radius:0px 0px 0px 10px;-moz-border-radius:0px 0px 0px 10px; }
					
		<?php } ?>		
		
		<?php if($this->config->get('bg_general') != '') { ?>
					
			body { background: #<?php echo $this->config->get('bg_general') ?> !important; }
			
		<?php } ?>
		
		<?php if($this->config->get('bg_footer') != '') { ?>
					
			#footer { background: #<?php echo $this->config->get('bg_footer') ?> !important; }
			
		<?php } ?>
		
		<?php if($this->config->get('bg_content') != '') { ?>
					
			div.box { background: #<?php echo $this->config->get('bg_content') ?>; }
			#content-center, .jcarousel-skin-opencart { background: #<?php echo $this->config->get('bg_content') ?> !important; }
			.box-product > div { background: #<?php echo $this->config->get('bg_content') ?>;border:2px solid #<?php echo $this->config->get('bg_content') ?>;  }
			
		<?php } ?>
		
		<?php if($this->config->get('background_image') == '1') { ?>
					
			#wrap { background:none !important; }
			
		<?php } ?>
		
		<?php if($this->config->get('background_image') == '2') { ?>
					
			#wrap { background:url(image/<?php echo $this->config->get('own_image'); ?>) <?php echo $this->config->get('background_image_position'); ?> <?php echo $this->config->get('background_image_repeat'); ?> !important;background-attachment:<?php echo $this->config->get('background_image_attachment'); ?> !important; }
			
		<?php } ?>
		
		<?php if($this->config->get('body_pattern') == '0') { ?>
					
			body { background-image:none !important; }
			
		<?php } ?>
		
		<?php if($this->config->get('body_pattern') == '100') { ?>
					
			body { background-image:url(image/<?php echo $this->config->get('own_pattern'); ?>) !important; }
			<?php if($this->config->get('bg_general') != '') { ?>
						
				body { background-color: #<?php echo $this->config->get('bg_general') ?> !important; }
				
			<?php } ?>
			
		<?php } ?>
		
		<?php if($this->config->get('body_pattern') > 0 && $this->config->get('body_pattern') < 100) { ?>
					
			body { background-image:url(catalog/view/theme/unistore/images/pattern/pattern_<?php echo $this->config->get('body_pattern'); ?>.png) !important; }
			<?php if($this->config->get('bg_general') != '') { ?>
						
				body { background-color: #<?php echo $this->config->get('bg_general') ?> !important; }
				
			<?php } ?>
			
		<?php } ?>
				
		</style>
		
	<?php } ?>
	<!--[if IE 7]>
	<link rel="stylesheet" type="text/css" href="catalog/view/theme/unistore/stylesheet/ie7.css" />
	<![endif]-->
</head>
<body>

<div id="notification"></div>

<!-- Wrap -->

<div id="wrap">

	<!-- Header -->
	
	<div id="header">
	<div class="set-size">
	

		<?php if($logo) { ?>
		<!-- Logo -->
		
		<h1 class="float-left"><a href="<?php echo $home; ?>"><img src="<?php echo $logo; ?>" alt="<?php echo $name; ?>" title="<?php echo $name; ?>" /></a></h1>
		<?php } ?>
				
		<!-- Search, Basket and Menu -->
		
		<div class="header-right float-left">
		
			<!-- Header Bar -->
			
			<div class="header-bar-bg"></div>
			
			<div class="header-bar">
				<div>
				
					<!-- Currency -->
					
					<form action="<?php echo str_replace('&', '&amp;', $action); ?>" method="post" enctype="multipart/form-data" id="currency_form">
					
						<div class="switcher float-left">
						
							<?php foreach ($currencies as $currency) { ?>
							<?php if ($currency['code'] == $currency_code) { ?>
							<p><?php echo $currency['title']; ?></p>
							<?php } ?>
							<?php } ?>
							<ul class="option">
							
								<?php foreach ($currencies as $currency) { ?>
	            			<li><a href="javascript:;" onclick="$('input[name=\'currency_code\']').attr('value', '<?php echo $currency['code']; ?>'); $('#currency_form').submit();"><?php echo $currency['title']; ?></a></li>
								<?php } ?>
							
							</ul>
						
						</div>
      				<input type="hidden" name="currency_code" value="" />
      				<input type="hidden" name="redirect" value="<?php echo $redirect; ?>" />
					
					</form>
					
					<!-- End Currency -->
					
					<!-- Language -->
					
					<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="language_form">
					
						<div class="switcher float-left">
						
							<?php foreach ($languages as $language) { ?>
							<?php if ($language['code'] == $language_code) { ?>
							<p><?php echo $language['name']; ?></p>
							<?php } ?>
							<?php } ?>
							<ul class="option">
							
								<?php foreach ($languages as $language) { ?>
				            <li><a href="javascript:;" onclick="$('input[name=\'language_code\']').attr('value', '<?php echo $language['code']; ?>'); $('#language_form').submit();"><?php echo $language['name']; ?></a></li>
								<?php } ?>
							
							</ul>
						
						</div>
			      	<input type="hidden" name="language_code" value="" />
			      	<input type="hidden" name="redirect" value="<?php echo $redirect; ?>" />
					
					</form>
					
					<!-- End Language -->
					
					<!-- Menu -->
					
					<ul class="menu float-right">
					
						<li><a href="<?php echo $home; ?>">» <?php echo $text_home; ?></a></li>
						<li><a href="<?php echo $wishlist; ?>">» <?php echo $text_wishlist; ?></a></li>
						<li><a href="<?php echo $account; ?>">» <?php echo $text_account; ?></a></li>
						<li><a href="<?php echo $shopping_cart; ?>">» <?php echo $text_shopping_cart; ?></a></li>
						<li><a href="<?php echo $checkout; ?>">» <?php echo $text_checkout; ?></a></li>
					
					</ul>
					
					<!-- End Menu -->
				
				</div>
			</div>
			
			<!-- End Header Bar -->
			
			<!-- Search -->
			
			<div class="search float-left">

			    <?php if ($filter_name) { ?>
			    <input type="text" name="filter_name" class="enterkey" value="<?php echo $filter_name; ?>" />
			    <?php } else { ?>
			    <input type="text" name="filter_name" class="enterkey autoclear" value="<?php echo $text_search; ?>" />
			    <?php } ?>
				<div class="button-search"></div>
			
			</div>
			
			<!-- End Search -->
			
			<!-- Shopping Cart -->
			
			<div class="shopping-cart float-right">
				
				<!-- Welcome text -->
				
				<p class="welcome-text align-right"><?php if (!$logged) { echo $text_welcome; } else { echo $text_logged; } ?></p>
				
				<div>
						
						<h2 class="float-right"><span id="cart_total"><?php echo $text_items; ?></span></h2>
						
						<div class="content"></div>
						
				</div>
			
			</div>
			
			<!-- End Shopping Cart -->
		
		</div>
		
	</div>
	</div>
	
	<!-- End Header -->
	
	<?php if ($categories) { ?>
	
	<!-- Categories -->
	
	<div id="categories">
	<div class="set-size">
	
		<ul>
		
			<?php foreach ($categories as $category) { ?>
			<li>
			
				<a href="<?php echo $category['href']; ?>"><?php echo $category['name'];?></a>
				<?php if ($category['children']) { ?>
				<!-- SubMenu -->
				
				<ul class="sub-menu column-<?php echo $category['column']; ?>">
					
					<?php $i = 0; for (; $i < count($category['children']); $i++) { ?>
					<li><a href="<?php echo $category['children'][$i]['href']; ?>"><?php echo $category['children'][$i]['name']; ?></a>
					
						<?php $categories_2 = $this->model_catalog_category->getCategories($category['children'][$i]['category_id']);
						if($categories_2) { ?>
						<!-- SubMenu -->
						
						<ul class="sub-menu">
							
							<?php foreach ($categories_2 as $category_2) { ?>
							<li><a href="<?php echo $this->url->link('product/category', 'path='.$category['category_id'].'_' . $category['children'][$i]['category_id'] . '_' . $category_2['category_id']); ?>"><?php echo $category_2['name']; ?></a></li>			
							<?php } ?>		

						</ul>
						<?php } ?>
					</li>
					<?php } ?>
				
				</ul>
				<?php } ?>
			
			</li>			
			<?php } ?>
		
		</ul>
	
	</div>
	</div>
	
	<!-- End Categories -->
	
	<?php } ?>
	
	<!-- Content -->
	
	<div id="content" class="set-size">