				
		<p class="clear"></p>
	
	</div>
	
	<!-- End Content -->

</div>

<!-- End Wrap -->

<!-- Footer -->

<div id="footer">
	
	 <?php 
	$displayCustomFooter =  $this->config->get('customFooter_status');

	if($displayCustomFooter == 1) {

	echo $customFooter; 
	}
	?>
	
	<!-- Footer Navigation -->
	
	<div class="set-size footer-navigation">

	    <div class="grid-3 float-left">

	      <h3><?php echo $text_information; ?></h3>

	      <ul>
				
				<?php foreach ($informations as $information) { ?>
				<li><a href="<?php echo $information['href']; ?>">&#187; <?php echo $information['title']; ?></a></li>
				<?php } ?>

	      </ul>

	    </div>

	    <div class="grid-3 float-left">

	      <h3><?php echo $text_service; ?></h3>

	      <ul>

				<li><a href="<?php echo $contact; ?>">&#187; <?php echo $text_contact; ?></a></li>
				<li><a href="<?php echo $return; ?>">&#187; <?php echo $text_return; ?></a></li>
				<li><a href="<?php echo $sitemap; ?>">&#187; <?php echo $text_sitemap; ?></a></li>

	      </ul>

	    </div>

	    <div class="grid-3 float-left">

	      <h3><?php echo $text_extra; ?></h3>

	      <ul>

				<li><a href="<?php echo $manufacturer; ?>">&#187; <?php echo $text_manufacturer; ?></a></li>
				<li><a href="<?php echo $voucher; ?>">&#187; <?php echo $text_voucher; ?></a></li>
				<li><a href="<?php echo $affiliate; ?>">&#187; <?php echo $text_affiliate; ?></a></li>
				<li><a href="<?php echo $special; ?>">&#187; <?php echo $text_special; ?></a></li>

	      </ul>

	    </div>

	    <div class="grid-3 float-left">

			<h3><?php echo $text_account; ?></h3>

	    	<ul>

				<li><a href="<?php echo $account; ?>">&#187; <?php echo $text_account; ?></a></li>
				<li><a href="<?php echo $order; ?>">&#187; <?php echo $text_order; ?></a></li>
				<li><a href="<?php echo $wishlist; ?>">&#187; <?php echo $text_wishlist; ?></a></li>
				<li><a href="<?php echo $newsletter; ?>">&#187; <?php echo $text_newsletter; ?></a></li>

	    	</ul>

		</div>
		
		<p class="clear"></p>
	
	</div>
	
	<!-- End footer navigation -->
	
	<!-- Separator --><p class="separator"></p>
	
	<!-- Copyright -->
	
	<div class="copyright"><?php echo $powered; ?></div>
	
	<!-- End copyright -->

</div>

<!-- End footer -->

</body>
</html>