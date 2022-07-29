<?php 
	/******************************************************
	 * @package Pav Megamenu module for Opencart 1.5.x
	 * @version 1.0
	 * @author http://www.pavothemes.com
	 * @copyright	Copyright (C) Feb 2013 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
	 * @license		GNU General Public License version 2
	*******************************************************/
	// echo DIR_TEMPLATE;die;



	$themeName = $this->config->get('config_template');
	require_once(  DIR_TEMPLATE.$this->config->get('config_template')."/template/libs/module.php" );
	$themeConfig = $this->config->get('themecontrol');
	$helper = ThemeControlHelper::getInstance( $this->registry, $themeName );
	$fusername = isset($themeConfig['username_facebook_module'])? $themeConfig['username_facebook_module']:'http://www.facebook.com/PavoThemes';
    $LANGUAGE_ID = $this->config->get( 'config_language_id' );
?>
</div></div></section>
<?php
	/**
	 * Footer Top Position
	 * $ospans allow overrides width of columns base on thiers indexs. format array( 1=> 3 )[value from 1->12]
	 */
	$modules = $helper->getModulesByPosition( 'mass_bottom' ); 
	$ospans = array( );
	$cols   = 1;
	if( count($modules) ) { 
?>
<section id="pav-mass-bottom">
	<div class="container">
		<?php $j=1;foreach ($modules as $i =>  $module) {   ?>
			<?php if( $i++%$cols == 0 || count($modules)==1 ){  $j=1;?><div class="row-fluid"><?php } ?>	
			<div class="span<?php echo floor(12/$cols);?>"><?php echo $module; ?></div>
			<?php if( $i%$cols == 0 || $i==count($modules) ){ ?></div><?php } ?>	
		<?php  $j++;  } ?>
	</div>	
</section>
<?php } ?>
<section id="footer">
	<?php
	/**
	 * Footer Top Position
	 * $ospans allow overrides width of columns base on thiers indexs. format array( 1=> 3 )[value from 1->12]
	 */
	$modules = $helper->getModulesByPosition( 'footer_top' ); 
	$ospans = array(  );
	
	if( count($modules) ){
	$cols = isset($themeConfig['block_footer_top'])&& $themeConfig['block_footer_top']?(int)$themeConfig['block_footer_top']:count($modules);
	//if( $cols < count($modules) ){ $cols = count($modules); }
	$class = $helper->calculateSpans( $ospans, $cols );
	?>
	<div class="footer-top">
		<div class="container">
			<div class="wrap-ft">

			<?php foreach ($modules as $i =>  $module):?>
				<?php echo $module; ?>
			<?php endforeach; ?>
			</div>
		</div>
	</div>
	<?php } ?>
	<?php
	/**
	 * Footer Center Position
	 * $ospans allow overrides width of columns base on thiers indexs. format array( 1=> 3 )[value from 1->12]
	 */
	$modules = $helper->getModulesByPosition( 'footer_center' ); 
	$ospans = array();
	
	if( count($modules) ){
	$cols = isset($themeConfig['block_footer_center'])&& $themeConfig['block_footer_center']?(int)$themeConfig['block_footer_center']:count($modules);
	$class = $helper->calculateSpans( $ospans, $cols );
	?>
	<div class="footer-center">
		<div class="container">
		<?php $j=1;foreach ($modules as $i =>  $module) {   ?>
				<?php if( $i++%$cols == 0 ){  $j=1;?><div class="row-fluid"><?php } ?>	
				<div class="<?php echo $class[$j];?>"><?php echo $module; ?></div>
				<?php if( $i%$cols == 0 || $i==count($modules) ){ ?></div><?php } ?>	
		<?php  $j++;  } ?>	
		</div>
	</div>
<?php } elseif((isset($themeConfig['enable_footer_center'])&&$themeConfig['enable_footer_center'])) { 
	 
	 //print("<pre>"); print_r($themeConfig); die();
?>

<div class="footer-center">
		  <div class="container"><div class="row-fluid">
			<div class="column span3 facebook column1">
				<h3><?php echo $this->language->get('Like us on Facebook');?></h3>
			<div style="overflow:hidden; width: 220px"> 
			<div id="fb-root"></div>
			<script>(function(d, s, id) {
			  var js, fjs = d.getElementsByTagName(s)[0];
			  if (d.getElementById(id)) return;
			  js = d.createElement(s); js.id = id;
			  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
			  fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));</script>
			
			
			<div class="fb-like-box" data-href="<?php echo $fusername?>" data-width="220" data-height="200" data-show-faces="true" data-stream="false" data-show-border="false" data-border-color="#f6f6f6" data-header="false"></div>
			</div>
			</div>
			<?php if( isset($themeConfig['footer_columns_2']) ) { ?>
			<div class="column span3 noborder column2">
				 <?php echo html_entity_decode(is_array($themeConfig['footer_columns_2']) ? $themeConfig['footer_columns_2'][$LANGUAGE_ID] : $themeConfig['footer_columns_2'], ENT_QUOTES, 'UTF-8'); ?>
			</div>
			<?php } ?>
			<div class="column span2 social column3">
			<?php if( isset($themeConfig['footer_columns_3']) ){ ?>
				 <?php echo html_entity_decode(is_array($themeConfig['footer_columns_3']) ? $themeConfig['footer_columns_3'][$LANGUAGE_ID] : $themeConfig['footer_columns_3'], ENT_QUOTES, 'UTF-8'); ?>
			<?php }else{ ?>
				<h3>Stay Connected</h3>
				<ul>
					<li class="facebook"><span class="fa fa-facebook">&nbsp;</span><a href="#">Triads Facebook</a></li>
					<li class="twitter"><span class="fa fa-twitter">&nbsp;</span><a href="#">Triads Twitter</a></li>
					<li class="google"><span class="fa fa-google-plus">&nbsp;</span><a href="#">Triads Google</a></li>
					<li class="youtube"><span class="fa fa-youtube">&nbsp;</span><a href="#">Triads Youtube</a></li>
					<li class="rss"><span class="fa fa-rss">&nbsp;</span><a href="#">Triads RSS Feed</a></li>
				</ul>
			<?php }?>
			</div>
			
			<div class="column span2 column4">
				<h3><?php echo $text_information; ?></h3>
				<ul>
				  <?php foreach ($informations as $information) { ?>
				  <li><a href="<?php echo $information['href']; ?>"><?php echo $information['title']; ?></a></li>
				  <?php } ?>
				  <li><a href="<?php echo $contact; ?>"><?php echo $text_contact; ?></a></li>
				  <li><a href="<?php echo $return; ?>"><?php echo $text_return; ?></a></li>
				  <li><a href="<?php echo $sitemap; ?>"><?php echo $text_sitemap; ?></a></li>
				</ul>
			</div>
		  
		  <div class="column span2 noborder column5">
			<h3><?php echo $text_account; ?></h3>
			<ul>
			  <li><a href="<?php echo $account; ?>"><?php echo $text_account; ?></a></li>
			  <li><a href="<?php echo $order; ?>"><?php echo $text_order; ?></a></li>
			  <li><a href="<?php echo $wishlist; ?>"><?php echo $text_wishlist; ?></a></li>
			  <li><a href="<?php echo $newsletter; ?>"><?php echo $text_newsletter; ?></a></li>
			  <li><a href="<?php echo $manufacturer; ?>"><?php echo $text_manufacturer; ?></a></li>
			  <li><a href="<?php echo $voucher; ?>"><?php echo $text_voucher; ?></a></li>
              <?php $queryAffiliate = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'affiliate'"); if($queryAffiliate->num_rows) { ?>
			  <li><a href="<?php echo $affiliate; ?>"><?php echo $text_affiliate; ?></a></li>
              <?php } ?>
			  <li><a href="<?php echo $special; ?>"><?php echo $text_special; ?></a></li>
			</ul>
		  </div>
		  
		  
		  <?php /*if( isset($themeConfig['content_data_module']) ) { ?>
		  <div class="column span3 contact">
			<h3><?php echo $this->language->get('Contact Us'); ?></h3>
			<?php echo html_entity_decode($themeConfig['content_data_module'], ENT_QUOTES, 'UTF-8'); ?>
		  </div>
		  <?php }*/?>
		  
		 </div> 
	</div></div>
<?php  } ?>	
	<?php
	/**
	 * Footer Bottom
	 * $ospans allow overrides width of columns base on thiers indexs. format array( 1=> 3 )[value from 1->12]
	 */
	$modules = $helper->getModulesByPosition( 'footer_bottom' ); 
	$ospans = array();
	
	if( count($modules) ){
	$cols = isset($themeConfig['block_footer_bottom'])&& $themeConfig['block_footer_bottom']?(int)$themeConfig['block_footer_bottom']:count($modules);	
	$class = $helper->calculateSpans( $ospans, $cols );
	?>
	<div class="footer-bottom">
		<div class="container">
		<?php $j=1;foreach ($modules as $i =>  $module) {  ?>
				<?php if( $i++%$cols == 0 ){  $j=1;?><div class="row-fluid"><?php } ?>	
				<div class="<?php echo $class[$j];?>"><?php echo $module; ?></div>
				<?php if( $i%$cols == 0 || $i==count($modules) ){ ?></div><?php } ?>	
		<?php  $j++;  } ?>	
		</div>
	</div>
	<?php } ?>
</section>
<!--
OpenCart is open source software and you are free to remove the powered by OpenCart if you want, but its generally accepted practise to make a small donation.
Please donate via PayPal to donate@opencart.com
//-->
<section id="powered">
	<div class="container">

	<div class="pull-left">
		<?php if( isset($themeConfig['enable_custom_copyright']) && $themeConfig['enable_custom_copyright'] ) { ?>
			<?php echo html_entity_decode( $themeConfig['copyright'], ENT_QUOTES, 'UTF-8' ); ?>
		<?php } else { ?>
			<?php echo $powered; ?>
		<?php } ?>
	</div>


		<div class="pull-right">
            <?php if( isset($themeConfig['widget_paypal_data']) ) { ?>
            <?php echo html_entity_decode( is_array($themeConfig['widget_paypal_data']) ? $themeConfig['widget_paypal_data'][$LANGUAGE_ID] : $themeConfig['widget_paypal_data'], ENT_QUOTES, 'UTF-8' ); ?>
            <?php } else { ?>
                <img src="catalog/view/theme/pav_citymart/image/icon/payment.png" alt="paymethods">
            <?php } ?>
		</div> 
	</div>
</section>
<!--
OpenCart is open source software and you are free to remove the powered by OpenCart if you want, but its generally accepted practise to make a small donation.
Please donate via PayPal to donate@opencart.com
//-->
<?php if( isset($themeConfig['enable_paneltool']) && $themeConfig['enable_paneltool'] ){ ?>
	<?php include( DIR_TEMPLATE.$this->config->get('config_template').'/template/common/addon/panel.tpl' );?>
<?php } ?>
</section>


<script type="text/javascript">
	$('#mainmenu input[name=\'search\']').bind('keydown', function(e) {
		if (e.keyCode == 13) {
			url = $('base').attr('href') + 'index.php?route=product/search';
			 
			var search = $(this).val();
			
			if (search) {
				url += '&search=' + encodeURIComponent(search);
			}
			
			location = url;
		}
	});
</script>


</body></html>