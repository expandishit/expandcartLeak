<?php echo $header; ?>
<?php if ($success) { ?>
<div class="success"><?php echo $success; ?></div>
<?php } ?>
<?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content"><?php echo $content_top; ?>
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <h1><?php echo $heading_title; ?></h1>
  <h2><?php echo $text_my_account; ?></h2>
  
  <div class="content">
    <ul style="padding:10px;">
      <li style="padding:10px"><a href="<?php echo $activity; ?>"><?php echo $text_edit; ?></a>
	  
	  <?php if($activity_total){
	   
	   echo "(";
	  echo "$activity_total";
	  echo ")";
	  
	  } ?>
	  
	  </li>
	  
      <li style="padding:10px"><a href="<?php echo $payment; ?>"><?php echo $text_password; ?></a>
	  
	  
	    
	  <?php if($payment_total){
	   
	   echo "(";
	  echo "$payment_total";
	  echo ")";
	  
	  } ?>
	  
	  
	  </li>
      <li style="padding:10px"><a href="<?php echo $sub; ?>"><?php echo $text_address; ?></a>
	  <?php if($subscription_total){
	   
	   echo "(";
	  echo "$subscription_total";
	  echo ")";
	  
	  } ?>
	  </li>
      <li style="padding:10px"><a href="<?php echo $option; ?>"><?php echo $text_wishlist; ?></a></li>
    </ul>
  </div>
  
  
  
  <?php echo $content_bottom; ?></div>
<?php echo $footer; ?> 