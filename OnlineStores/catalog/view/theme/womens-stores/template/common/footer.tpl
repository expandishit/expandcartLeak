	<!-- Footer Starts -->	
		<div id="footer">
          <?php if ($informations) { ?>
          <div class="column">
            <h3><?php echo $text_information; ?></h3>
            <ul>
              <?php foreach ($informations as $information) { ?>
              <li><a href="<?php echo $information['href']; ?>"><?php echo $information['title']; ?></a></li>
              <?php } ?>
            </ul>
          </div>
          <?php } ?>
		<!-- Column Ends -->
		<!-- Column Starts -->	
			<div class="column">
				<h3><?php echo $text_service; ?></h3>
				<ul>
					<li><a href="<?php echo $contact; ?>"><?php echo $text_contact; ?></a></li>
					<li><a href="<?php echo $return; ?>"><?php echo $text_return; ?></a></li>
					<li><a href="<?php echo $sitemap; ?>"><?php echo $text_sitemap; ?></a></li>
				</ul>
			</div>
		<!-- Column Ends -->
		<!-- Column Starts -->	
			<div class="column">
				<h3><?php echo $text_extra; ?></h3>
				<ul>
					<li><a href="<?php echo $manufacturer; ?>"><?php echo $text_manufacturer; ?></a></li>
					<li><a href="<?php echo $voucher; ?>"><?php echo $text_voucher; ?></a></li>
					<li><a href="<?php echo $affiliate; ?>"><?php echo $text_affiliate; ?></a></li>
					<li><a href="<?php echo $special; ?>"><?php echo $text_special; ?></a></li>
				</ul>
			</div>
		<!-- Column Ends -->
		<!-- Column Starts -->	
			<div class="column">
				<h3><?php echo $text_account; ?></h3>
				<ul>
					<li><a href="<?php echo $account; ?>"><?php echo $text_account; ?></a></li>
					<li><a href="<?php echo $order; ?>"><?php echo $text_order; ?></a></li>
					<li><a href="<?php echo $wishlist; ?>"><?php echo $text_wishlist; ?></a></li>
					<li><a href="<?php echo $newsletter; ?>"><?php echo $text_newsletter; ?></a></li>
				</ul>
			</div>
		<!-- Column Ends -->	
        <!-- Column Starts -->
            <div class="column2">
                <?php if($this->socfacebook || $this->soctwitter || $this->socmyspace ||$this->socflickr || $this->socrss || $this->socdescription) { ?>
                <h3>About Us</h3>
                <?php }?>
                <p><?php echo $this->socdescription;?></p>
                <?php if($this->socfacebook || $this->soctwitter || $this->socmyspace ||$this->socflickr || $this->socrss) { ?>
                <h3>Follow Us</h3>
                <?php }?>
                <ul>
                    <li>
                        <?php if($this->socfacebook) { ?>
                        <a href="<?php echo $this->socfacebook;?>"><img alt="facebook" src="http://www.sainathchillapuram.com/tf/opencart/diamond-rings/image/data/facebook.png" /></a>
                        <?php }?>
                        <?php if($this->soctwitter) { ?>
                        <a href="<?php echo $this->soctwitter;?>"><img alt="twitter" src="http://www.sainathchillapuram.com/tf/opencart/diamond-rings/image/data/twitter.png" /></a>
                        <?php }?>
                        <?php if($this->socmyspace) { ?>
                        <a href="<?php echo $this->socmyspace;?>"><img alt="myspace" src="http://www.sainathchillapuram.com/tf/opencart/diamond-rings/image/data/myspace.png" /></a>
                        <?php }?>
                        <?php if($this->socflickr) { ?>
                        <a href="<?php echo $this->socflickr;?>"><img alt="flickr" src="http://www.sainathchillapuram.com/tf/opencart/diamond-rings/image/data/flickr.png" /></a>
                        <?php }?>
                        <?php if($this->socrss) { ?>
                        <a href="<?php echo $this->socrss;?>"><img alt="RSS" src="http://www.sainathchillapuram.com/tf/opencart/diamond-rings/image/data/RSS.png" /></a>
                        <?php }?>
                    </li>
                </ul>
            </div>
		<!-- Column Ends -->
		</div>
	<!-- Footer Ends -->
	<!-- Powered Starts -->
		<div id="powered" class="clearfix">
			<p class="floatleft"><?php echo $powered; ?></p>
		</div>
	<!-- Powered Ends -->
	<!-- 
	OpenCart is open source software and you are free to remove the powered by OpenCart if you want, but its generally accepted practise to make a small donation.
	Please donate via PayPal to donate@opencart.com
	//-->
	</div>
<!-- Container Ends -->
</body>
</html>