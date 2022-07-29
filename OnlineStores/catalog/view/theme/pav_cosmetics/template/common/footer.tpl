<?php 
	/******************************************************
	 * @package Pav Megamenu module for Opencart 1.5.x
	 * @version 1.1
	 * @author http://www.pavothemes.com
	 * @copyright	Copyright (C) Feb 2013 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
	 * @license		GNU General Public License version 2
	*******************************************************/

	require_once( DIR_TEMPLATE.$this->config->get('config_template')."/development/libs/framework.php" );
	$themeConfig = $this->config->get('themecontrol');
	$themeName =  $this->config->get('config_template');
	$helper = ThemeControlHelper::getInstance( $this->registry, $themeName );
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
			<?php if( $i++%$cols == 0 || count($modules)==1 ){  $j=1;?><div class="row"><?php } ?>	
			<div class="col-lg-<?php echo floor(12/$cols);?> col-md-<?php echo floor(12/$cols);?> col-sm-12 col-xs-12"><?php echo $module; ?></div>
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
	$ospans = array();
	
	if( count($modules) ){
	$cols = isset($themeConfig['block_footer_top'])&& $themeConfig['block_footer_top']?(int)$themeConfig['block_footer_top']:count($modules);
	//if( $cols < count($modules) ){ $cols = count($modules); }
	$class = $helper->calculateSpans( $ospans, $cols );
	?>
	<div class="footer-top">
		<div class="container">
			<?php $j=1;foreach ($modules as $i =>  $module) {   ?>
				<?php if( $i++%$cols == 0 || count($modules)==1 ){  $j=1;?><div class="row"><?php } ?>	
				<div class="<?php echo $class[$j];?>  col-md-6"><?php echo $module; ?></div>
				<?php if( $i%$cols == 0 || $i==count($modules) ){ ?></div><?php } ?>	
			<?php  $j++;  } ?>
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
		<?php $j=1;foreach ($modules as $i =>  $module) {  ?>
				<?php if( $i++%$cols == 0 || count($modules)==1 ){  $j=1;?><div class="row"><?php } ?>	
				<div class="<?php echo $class[$j];?>"><?php echo $module; ?></div>
				<?php if( $i%$cols == 0 || $i==count($modules) ){ ?></div><?php } ?>	
		<?php  $j++;  } ?>	
		</div>
	</div>
	<?php } elseif((isset($themeConfig['enable_footer_center'])&&$themeConfig['enable_footer_center'])) { ?>
	<div class="footer-center">
		<div class="container">
			<div class="row">
		  		<?php if( isset($themeConfig['widget_contactus_data'][$LANGUAGE_ID]) ) { ?>
				<div class="col-xs-12 col-sm-6 col-lg-6">
					<div class="box box-blog">

						<?php echo html_entity_decode( $themeConfig['widget_contactus_data'][$LANGUAGE_ID], ENT_QUOTES, 'UTF-8' ); ?>
					</div>
				</div>
				 <?php } ?>

                <?php if( isset($themeConfig['widget_newsletter_data'][$LANGUAGE_ID]) ) {

				 ?>
                <div class="column col-xs-12 col-sm-2 col-lg-2">
                    <div class="box folow">
                        <div class="box-heading"><span><?php echo $this->language->get('Social Network'); ?></span></div>
                        <?php echo html_entity_decode( $themeConfig['widget_newsletter_data'][$LANGUAGE_ID], ENT_QUOTES, 'UTF-8' ); ?>
                    </div>
                </div>
                <?php } ?>

				<div class="column col-xs-12 col-sm-2 col-lg-2">
					<div class="box">
						<div class="box-heading"><span><?php echo $text_service; ?></span></div>
						<ul class="list">
						  <li><a href="<?php echo $contact; ?>">&nbsp;&nbsp;<?php echo $text_contact; ?></a></li>
						  <li><a href="<?php echo $return; ?>">&nbsp;&nbsp;<?php echo $text_return; ?></a></li>
						  <li><a href="<?php echo $sitemap; ?>">&nbsp;&nbsp;<?php echo $text_sitemap; ?></a></li>
						   <li><a href="<?php echo $manufacturer; ?>">&nbsp;&nbsp;<?php echo $text_manufacturer; ?></a></li>
						  <li><a href="<?php echo $voucher; ?>">&nbsp;&nbsp;<?php echo $text_voucher; ?></a></li>
                            <li><a href="<?php echo $affiliate; ?>">&nbsp;&nbsp;<?php echo $text_affiliate; ?></a></li>
						</ul>
					</div>
				</div>


				<div class="column col-xs-12 col-sm-2 col-lg-2">
					<div class="box">
						<div class="box-heading"><span><?php echo $text_account; ?></span></div>
						<ul class="list">
						  <li><a href="<?php echo $account; ?>">&nbsp;&nbsp;<?php echo $text_account; ?></a></li>
						  <li><a href="<?php echo $order; ?>">&nbsp;&nbsp;<?php echo $text_order; ?></a></li>
						  <li><a href="<?php echo $wishlist; ?>">&nbsp;&nbsp;<?php echo $text_wishlist; ?></a></li>
						  <li><a href="<?php echo $newsletter; ?>">&nbsp;&nbsp;<?php echo $text_newsletter; ?></a></li>
						  <li><a href="<?php echo $special; ?>">&nbsp;&nbsp;<?php echo $text_special; ?></a></li>
						</ul>
					</div>
				</div>
				  


			 </div> 
		</div>
	</div>
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
				<?php if( $i++%$cols == 0 || count($modules)==1 ){  $j=1;?><div class="row"><?php } ?>	
				<div class="<?php echo $class[$j];?>"><?php echo $module; ?></div>
				<?php if( $i%$cols == 0 || $i==count($modules) ){ ?></div><?php } ?>	
		<?php  $j++;  } ?>	
		</div>
	</div>
	<?php } ?>

	<!--
	OpenCart is open source software and you are free to remove the powered by OpenCart if you want, but its generally accepted practise to make a small donation.
	Please donate via PayPal to donate@opencart.com
	//-->
	<div id="powered">
		<div class="container">
			<div class="copyright pull-left">
				<?php if( isset($themeConfig['enable_custom_copyright']) && $themeConfig['enable_custom_copyright'] ) { ?>
					<?php echo html_entity_decode( $themeConfig['copyright'], ENT_QUOTES, 'UTF-8' ); ?>
				<?php } else { ?>
					<?php echo $powered; ?>
				<?php } ?>
			</div>
			<?php if( isset($themeConfig['widget_paypal_data'][$LANGUAGE_ID]) ) {?>
				<div class="paypal pull-right">
					<?php echo html_entity_decode( $themeConfig['widget_paypal_data'][$LANGUAGE_ID], ENT_QUOTES, 'UTF-8' ); ?>
			</div>
			 <?php } ?>
		</div>
	</div>
</section>

<!--
OpenCart is open source software and you are free to remove the powered by OpenCart if you want, but its generally accepted practise to make a small donation.
Please donate via PayPal to donate@opencart.com
//-->
<?php if( isset($themeConfig['enable_paneltool']) && $themeConfig['enable_paneltool'] ){  ?>
	<?php  echo $helper->renderAddon( 'panel' );?>
<?php } ?>
</section> 
</body></html>