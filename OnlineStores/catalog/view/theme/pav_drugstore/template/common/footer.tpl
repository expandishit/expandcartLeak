<?php 
	/******************************************************
	 * @package Pav Megamenu module for Opencart 1.5.x
	 * @version 1.0
	 * @author http://www.pavothemes.com
	 * @copyright	Copyright (C) Feb 2013 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
	 * @license		GNU General Public License version 2
	*******************************************************/

	require_once( DIR_TEMPLATE.$this->config->get('config_template')."/template/libs/module.php" );
	$themeConfig = $this->config->get('themecontrol');
	$themeName = $this->config->get('config_template') ;
	$helper = ThemeControlHelper::getInstance( $this->registry, $themeName );
	$LANGUAGE_ID = $this->config->get( 'config_language_id' );  

?>
</div></section></div>
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
<div class="aside footer">
    <aside id="footer">
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
            <?php $j=1;foreach ($modules as $i =>  $module) { $i=$i+1; ?>
            <?php if( $i%$cols == 1 || count($modules)==1 ){  $j=1;?><div class="row-fluid"><?php } ?>  
            <div class="<?php echo $class[$j];?>"><?php echo $module; ?></div>
            <?php if( $i%$cols == 0 || $i==count($modules) ){ ?></div><?php } ?>    
            <?php  $j++;  } ?>
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
            <?php $j=1;foreach ($modules as $i =>  $module) { $i=$i+1; ?>
            <?php if( $i%$cols == 1 || count($modules)==1 ){  $j=1;?><div class="row-fluid"><?php } ?>  
            <div class="<?php echo $class[$j];?>"><?php echo $module; ?></div>
            <?php if( $i%$cols == 0 || $i==count($modules) ){ ?></div><?php } ?>    
            <?php  $j++;  } ?>
        </div>
        <?php } elseif((isset($themeConfig['enable_footer_center'])&&$themeConfig['enable_footer_center'])) { ?>
        <div class="footer-center">
            <div class="row-fluid">			
			
				<?php if( isset($themeConfig['widget_contact_data'][$LANGUAGE_ID]) ) {
				?>
				<nav class="column span6">
					<h3 class="box-heading c-text"><span><?php echo $this->language->get('text_contact_us'); ?></span></h3>
					<?php echo html_entity_decode( $themeConfig['widget_contact_data'][$LANGUAGE_ID], ENT_QUOTES, 'UTF-8' ); ?>
				</nav>
				<?php } ?>
			
            
				<?php if ($informations) { ?>
				<nav class="column span2">
					<h3 class="box-heading c-text"><span><?php echo $text_information; ?></span></h3>
					<ul class="info">
						<?php foreach ($informations as $information) { ?>
						<li><a href="<?php echo $information['href']; ?>"><?php echo $information['title']; ?></a></li>
						<?php } ?>
					</ul>
				</nav>
				<?php } ?>       

            
				<nav class="column span2">
					<h3 class="box-heading c-text"><span><?php echo $text_extra; ?></span></h3>
					<ul class="extra">
						<li><a href="<?php echo $manufacturer; ?>"><?php echo $text_manufacturer; ?></a></li>
						<li><a href="<?php echo $voucher; ?>"><?php echo $text_voucher; ?></a></li>
						<li><a href="<?php echo $affiliate; ?>"><?php echo $text_affiliate; ?></a></li>
						<li><a href="<?php echo $special; ?>"><?php echo $text_special; ?></a></li>
					</ul>
				</nav>  
            
            
				<nav class="column span2">
					<h3 class="box-heading c-text"><span><?php echo $text_account; ?></span></h3>
					<ul class="account">
						<li><a href="<?php echo $account; ?>"><?php echo $text_account; ?></a></li>
						<li><a href="<?php echo $order; ?>"><?php echo $text_order; ?></a></li>
						<li><a href="<?php echo $wishlist; ?>"><?php echo $text_wishlist; ?></a></li>
						<li><a href="<?php echo $newsletter; ?>"><?php echo $text_newsletter; ?></a></li>
					</ul>
				</nav>
            
				<?php /*
				<nav class="column span2">
					<h3 class="box-heading c-text"><span><?php echo $text_service; ?></span></h3>
					<ul class="service">
						<li><a href="<?php echo $contact; ?>"><?php echo $text_contact; ?></a></li>
						<li><a href="<?php echo $return; ?>"><?php echo $text_return; ?></a></li>
						<li><a href="<?php echo $sitemap; ?>"><?php echo $text_sitemap; ?></a></li>
					</ul>
				</nav>
				*/ ?>			
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
            <?php $j=1;foreach ($modules as $i =>  $module) { $i=$i+1; ?>
            <?php if( $i%$cols == 1 || count($modules)==1 ){  $j=1;?><div class="row-fluid"><?php } ?>  
            <div class="<?php echo $class[$j];?>"><?php echo $module; ?></div>
            <?php if( $i%$cols == 0 || $i==count($modules) ){ ?></div><?php } ?>    
            <?php  $j++;  } ?>  
        </div>
        <?php } ?>
    </aside>
<!--
OpenCart is open source software and you are free to remove the powered by OpenCart if you want, but its generally accepted practise to make a small donation.
Please donate via PayPal to donate@opencart.com
//-->
<footer id="powered" class="highlight">
	<div class="row-fluid">
		<div class="span8">
			<address>
				<?php if( isset($themeConfig['enable_custom_copyright']) && $themeConfig['enable_custom_copyright'] ) { ?>
				<?php echo html_entity_decode( $themeConfig['copyright'], ENT_QUOTES, 'UTF-8' ); ?>
				<?php } else { ?>
				<?php echo $powered; ?>
				<?php } ?>
			</address>
		</div>
		<div class="span4">
			<div class="payment">
				<a href="#" title="payment">
					<img src="catalog/view/theme/<?php echo $themeName;?>/image/payment.png" alt="payment">
				</a>
			</div>
		</div>
	</div>    
</footer>
</div>
</section>
<!--
OpenCart is open source software and you are free to remove the powered by OpenCart if you want, but its generally accepted practise to make a small donation.
Please donate via PayPal to donate@opencart.com
//-->
<?php if( isset($themeConfig['enable_paneltool']) && $themeConfig['enable_paneltool'] ){  ?>
<?php include( DIR_TEMPLATE.$this->config->get('config_template').'/template/common/addon/panel.tpl' );?>
<?php } ?>
</div>
</body>
</html>