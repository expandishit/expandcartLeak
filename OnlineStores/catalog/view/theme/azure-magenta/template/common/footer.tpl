<div id="footer">

  <div class="column">
    <h3><?php echo $text_information; ?></h3>
    <ul>
      <?php foreach ($informations as $information) { ?>
      <li><a href="<?php echo $information['href']; ?>"><?php echo $information['title']; ?></a></li>
      <?php } ?>
	  <li><a href="<?php echo $newsletter; ?>"><?php echo $text_newsletter; ?></a></li>
    </ul>
  </div>
  <div class="column">
    <h3><?php echo $text_service; ?></h3>
    <ul>
      <li><a href="<?php echo $contact; ?>"><?php echo $text_contact; ?></a></li>
      <li><a href="<?php echo $return; ?>"><?php echo $text_return; ?></a></li>
      <li><a href="<?php echo $sitemap; ?>"><?php echo $text_sitemap; ?></a></li>
      <li><a href="<?php echo $account; ?>"><?php echo $text_account; ?></a></li>
      <li><a href="<?php echo $order; ?>"><?php echo $text_order; ?></a></li>
    </ul>
  </div>
  <div class="column">
    <h3><?php echo $text_extra; ?></h3>
    <ul>
      <li><a href="<?php echo $manufacturer; ?>"><?php echo $text_manufacturer; ?></a></li>
      <li><a href="<?php echo $voucher; ?>"><?php echo $text_voucher; ?></a></li>
      <li><a href="<?php echo $affiliate; ?>"><?php echo $text_affiliate; ?></a></li>
      <li><a href="<?php echo $special; ?>"><?php echo $text_special; ?></a></li>
      <li><a href="<?php echo $wishlist; ?>"><?php echo $text_wishlist; ?></a></li>
      
    </ul>
  </div>
  <div class="column2">
    <?php if($this->socfacebook || $this->soctwitter || $this->socmyspace ||$this->socflickr || $this->socrss || $this->socdescription) { ?>
        <h3>Our Shopping Cart</h3>
    <?php }?>
    <p><?php echo $this->socdescription;?></p>
    <?php if($this->socfacebook || $this->soctwitter || $this->socmyspace ||$this->socflickr || $this->socrss) { ?>
        <h3><span style="color:#767676;">Connect With Us</span></h3>
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
  <!--<div class="column">
    <h3><?php echo $text_account; ?></h3>
    <ul>
      <li><a href="<?php echo $account; ?>"><?php echo $text_account; ?></a></li>
      <li><a href="<?php echo $order; ?>"><?php echo $text_order; ?></a></li>
      <li><a href="<?php echo $wishlist; ?>"><?php echo $text_wishlist; ?></a></li>
      <li><a href="<?php echo $newsletter; ?>"><?php echo $text_newsletter; ?></a></li>
    </ul>
  </div>-->
</div>
<!-- 

//-->
<div id="powered"><?php echo $powered; ?></div>
<!-- 

//-->
</div>
</div>
</body></html>