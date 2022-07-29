<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content"><?php echo $content_top; ?>
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <h1><?php echo $heading_title; ?></h1>
  <p>Here you can view your recent bidding history</p>
  <table class="list">
  <script type="text/javascript" src="catalog/view/javascript/jquery/colorbox/jquery.colorbox-min.js"></script>
  <link rel="stylesheet" type="text/css" href="catalog/view/javascript/jquery/colorbox/colorbox.css" media="screen" />
    <thead>
      <tr>
        <td class="left"><?php echo $column_image; ?></td>
        <td class="left"><?php echo $column_description; ?></td>
		<td class="left"><?php echo $column_bid; ?></td>
		<td class="left"><?php echo $column_name; ?></td>
        <td class="left"><?php echo $column_amount; ?></td>
		<td class="left"><?php echo $column_date_added; ?></td>
		<td class="left">Bids History</td>
      </tr>
    </thead>
    <tbody>
      <?php if ($activitys) { ?>
      <?php foreach ($activitys  as $activity) { ?>
      <tr>
	     <td class="left"><img src="<?php echo $activity['image']; ?>"></td>
		  <td class="left"><?php echo $activity['description']; ?></td>
        <td class="left"><?php echo $activity['bids']; ?></td>
        <td class="left"><?php echo $activity['name']; ?></td>
        <td class="left"><?php echo $activity['amount']; ?></td>
		<td class="left"><?php echo $activity['date_added']; ?></td>
		<td class="center"><a id="btn<?php echo $activity['customer_bid_id']; ?>"><img src="catalog/view/theme/default/image/bids_history.png"/></a>
		<div style="display:none;">
    <div id="lightboxRegister<?php echo $activity['customer_bid_id']; ?>"> 
	<table class="list" style="margin-top:20px;">
	
	
	 <thead>
	 
      <tr>
        <td class="left">Date</td>
        <td class="left">Bidder</td>
		<td class="left">Price</td>
		</tr>
    </thead>
	
	<?php if ($activity['rbids']) { ?>
      <?php foreach ($activity['rbids']  as $ractivity) { ?>
	
	 <tr>
	     
		 <td class="left"><?php echo $ractivity['date_added']; ?></td>
	     <td class="left"><?php echo $ractivity['firstname']; ?> <?php echo $ractivity['lastname']; ?></td>
        <td class="left"><?php echo $this->currency->format($ractivity['price_bid']); ?></td>
		
		
		</tr>
		
	<?php } } else{?>
	
	<tr><td colspan="3"  style="text-align:center">There are no bid yet</td></tr>
	
	
	<?php } ?>
	
	
	
	</table>
	
	</div>
</div>
		</td>
      </tr>
	  
	  <script>
	  $('#btn<?php echo $activity['customer_bid_id']; ?>').click(function(){
    $.colorbox({inline:true, width:"50%",height:"60%", href: '#lightboxRegister<?php echo $activity['customer_bid_id']; ?>'});
});
	  </script>
      <?php } ?>
      <?php } else { ?>
      <tr>
        <td class="center" colspan="7"><?php echo $text_empty; ?></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
  <div class="pagination"><?php echo $pagination; ?></div>
  <div class="buttons">
    <div class="right"><a href="<?php echo $continue; ?>" class="button"><?php echo $button_continue; ?></a></div>
  </div>
  <?php echo $content_bottom; ?></div>
<?php echo $footer; ?>