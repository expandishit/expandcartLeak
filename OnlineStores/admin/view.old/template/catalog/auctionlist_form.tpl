<?php echo $header; ?><style>legend {    background: none repeat scroll 0 0 #EBEDF4;    border: 1px solid #CCCED7;    font-weight: 700;    margin: 0;    padding: 0.2em 0.5em;    text-align: left;}.margin-form {    color: #7F7F7F;    font-size: 0.85em;    padding: 0 0 1em 260px;}label {    color: #585A69;    text-shadow: 0 1px 0 #FFFFFF;}label:after {    clear: both;}fieldset {    background-color: #EBEDF4;    border: 1px solid #CCCED7;    font-size: 1.1em;    margin: 0;    padding: 1em;}

.fleft {
    float: left;

	}
	
	#auction_summary .auction_info {
    margin-left: 10px;
    width: 60%;
}</style>
<div id="content">
    <ol class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <?php if ($breadcrumb === end($breadcrumbs)) { ?>
        <li class="active">
            <?php } else { ?>
        <li>
            <?php } ?>
            <a href="<?php echo $breadcrumb['href']; ?>">
                <?php if ($breadcrumb === reset($breadcrumbs)) { ?>
                <?php echo $breadcrumb['text']; ?>
                <?php } else { ?>
                <span><?php echo $breadcrumb['text']; ?></span>
                <?php } ?>
            </a>
        </li>
        <?php } ?>
    </ol>
    <?php if ($error_warning) { ?>
    <script>
        var notificationString = '<?php echo $error_warning; ?>';
        var notificationType = 'warning';
    </script>
    <?php } ?>
    <?php if ($success) { ?>
    <script>
        var notificationString = '<?php echo $success; ?>';
        var notificationType = 'success';
    </script>
    <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button btn btn-primary"><?php echo $button_save; ?></a>
	  
	  
	  
	  <?php if($checkstatebid==1){ ?>
	  
	  <a id="button-bid" class="button btn btn-primary"><?php echo $button_restart; ?></a>
	  
	  <?php } else { ?>
	  
	  <a onclick="location = '<?php echo $finish; ?>';" class="button btn btn-primary"><?php echo $button_finish; ?></a>
	  
	  <?php } ?>
	  
	  <a onclick="location = '<?php echo $winner; ?>'" class="button btn btn-primary"><?php echo $button_auction; ?></a>
	  
	  
	  <a  onclick="window.open('<?php echo $path; ?>');" class="button btn btn-primary"><?php echo $button_view; ?></a>
	  
	   <a onclick="location = '<?php echo $bids; ?>'" class="button btn btn-primary"><?php echo $button_bids; ?></a>
	   
	    <a onclick="location = '<?php echo $autobids; ?>'" class="button btn btn-primary" style="display:none;"><?php echo $button_autobids; ?></a>
		
		 <a onclick="location = '<?php echo $blockip; ?>'" class="button btn btn-primary"><?php echo $button_block; ?></a>
		 
		  <a onclick="location = '<?php echo $blacklist; ?>'" class="button btn btn-primary"><?php echo $button_blacklist; ?></a>
		  
		   <a onclick="location = '<?php echo $payment; ?>'" class="button btn btn-primary"><?php echo $button_payment; ?></a>
		   
		 <a onclick="location = '<?php echo $subscription; ?>'" class="button btn btn-primary"><?php echo $button_subscription; ?></a>
		 
		 
	  
	  </div>
    </div>
    <div class="content">
         <form action="<?php echo $update; ?>" method="post" enctype="multipart/form-data" id="form">
       <div id="tab-auction">
     <fieldset id="fieldset_0">
												
	<legend>
	<?php echo $entry_name;?>
	</legend>
	
	<input type="hidden" value="<?php echo $product_bid_id; ?>" id="auction_id" name="auction_id">
	<input type="hidden" value="<?php echo $product_id; ?>" id="auction_id" name="product_id">
	<label> </label>							
	
	<div class="margin-form">
								
																	<div id="auction_summary">
    <div class="fleft">
        <img src="<?php echo $thumb; ?>">
    </div>
    <div class="fleft auction_info">
        <div class="product_name"><b><?php echo $name; ?></b></div>
        <div class="auction_info_row">
         <b><?php echo $text_state; ?></b>
		 <?php if($checkstatebid == 0){ ?>
		 
		 <span class="auction_state_2" style="color:green"><?php echo $text_progress; ?></span>
		 
		 <?php } else {?>
         <span class="auction_state_2"><?php echo $text_closed; ?></span>
		 
		 <?php } ?>
        </div>
        
		
        <div class="auction_info_row">
            <b><?php echo $text_winner; ?></b> 
			<?php if($countCustomerBids>0){ ?>
		<?php if($checkwinner) {?>
		<span id="winning_bidder"><?php echo $checkwinner[2]; ?></span>
		<?php } else {?>
		<span id="winning_bidder"><?php echo $text_bid_bids;?></span>
		<?php } ?>	
       <?php }else{ ?>
	   	<span id="winning_bidder"><?php echo $text_bid_bids;?></span>
		
      <?php } ?>
        </div>
        <div class="auction_info_row">
            <b><?php echo $text_current_price; ?></b> 
			
			
			
			<?php if($countCustomerBids>0){ ?>		
		      <?php echo $maxcustomerbids;?>			
			<?php } else { ?>			
			<?php echo $this->currency->format($start_price);?>
			
		<?php }  ?>
			
        </div>
        
    </div>
    <div class="clear"></div>
</div>
																								
								
																	
																</div>
								<div class="clear"></div>
							
					</fieldset>
					
					
					<br/>
	 


	   <fieldset id="fieldset_1">			
	   
	   <legend>			<?php echo $entry_auction; ?>						</legend>			
	   
	   
	  				
	   
	   <div class="margin-form">                  
   <?php if($use_max_price_on){?>										
   <input type="checkbox" value="1" class="" id="use_max_price_on" name="use_max_price_on" checked="checked">					
   
   <?php } else{?>										
   
   <input type="checkbox" value="1" class="" id="use_max_price_on" name="use_max_price_on">					
   
   
   <?php } ?>					<label class="t" for="use_max_price_on"><strong><?php echo $entry_max; ?></strong></label><br>						<p class="preference_description"><?php echo $entry_max_desc; ?></p>					</div>				<div class="clear"></div>				<div class="small"><span class="required">*</span><?php echo $this->language->get('auc_text_required'); ?></div>		</fieldset>				<br/>



	   <fieldset id="fieldset_2">			<legend><?php echo $auction_settings; ?></legend>				<div class="margin-form">				  <?php if($status_on){?>					<input type="checkbox" value="1" class="" id="status_on" name="status_on" checked="checked">									<?php } else{?>								<input type="checkbox" value="1" class="" id="status_on" name="status_on" >								<?php } ?>					<label class="t" for="status_on"><strong><?php echo $auction_status; ?></strong></label><br/>				</div>					<div class="clear"></div>											<label><?php echo $auction_start; ?></label>											<div class="margin-form">					<input type="text" maxlength="10" size="20" class="datetime" value="<?php echo $start_time; ?>" id="start_time" name="start_time">				    <span class="required">*</span><p class="preference_description"><?php echo $auction_start_desc; ?></p>					<?php if ($error_start) { ?>                <span class="error"><?php echo $error_start; ?></span>                <?php } ?>				</div>					<div class="clear"></div>									<label><?php echo $auction_end; ?></label>											<div class="margin-form">					<input type="text" maxlength="10" size="20" class="datetime" value="<?php echo $end_time; ?>" id="end_time" name="end_time">					<p class="preference_description"><?php echo $auction_end_desc; ?></p>					
	   
	   <?php if ($error_end_time) { ?>                <span class="error"><?php echo $error_end_time; ?></span>                <?php } ?>				
	   
	   </div>					
	   					<div class="clear"></div>											
						
						
						<label><?php echo $auction_starting; ?>	</label>											<div class="margin-form">								<input type="text" maxlength="10" size="20" name="start_price" value="<?php echo $start_price; ?>"> 								<span class="required">*</span><p class="preference_description"><?php echo $auction_starting_desc;?></p>				<?php if ($error_bid_price) { ?>                <span class="error"><?php echo $error_bid_price; ?></span>                <?php } ?>																</div>				<div class="clear"></div>											<label><?php echo $auction_max; ?></label>											<div class="margin-form">					
	   
	   <input type="text" maxlength="10" size="20" name="max_price" value="<?php echo $max_price; ?>">					
	   <p class="preference_description"><?php echo $auction_max_desc; ?></p>	<?php if ($error_max_price) { ?>                <span class="error"><?php echo $error_max_price; ?></span>                <?php } ?>			</div>			


	   <div class="clear"></div>											
	   
	   <label><?php echo $auction_min; ?></label>											<div class="margin-form">				<input type="text" maxlength="10" size="20" name="min_offer_step" value="<?php echo $min_offer_step; ?>">				<p class="preference_description"><?php echo $auction_min_desc; ?></p>				</div>				
	   
	   <div class="clear"></div>												
	   
	   											
	   
	   
	   </fieldset>        
	   
	   </div>	
      </form>
    </div>
  </div>
</div>
<script type="text/javascript" src="view/javascript/ckeditor/ckeditor.js"></script> 

<script type="text/javascript" src="view/javascript/jquery/ui/jquery-ui-timepicker-addon.js"></script> 
<script type="text/javascript"><!--
$('.date').datepicker({dateFormat: 'yy-mm-dd'});
$('.datetime').datetimepicker({
	dateFormat: 'yy-mm-dd',
	timeFormat: 'h:m'
});
$('.time').timepicker({timeFormat: 'h:m'});
//--></script>

<script type="text/javascript"><!--

$('#button-bid').click(function (event) {

if (confirm('<?php echo $this->language->get('Are you sure? All bids and auto bid will be removed and auction price will be reset to starting price'); ?>')) {
$.ajax({
	url: 'index.php?route=catalog/auctionlist/resetallbid&token=<?php echo $token; ?>&status=0&product_id=<?php echo $product_id; ?>&auction_id=<?php echo $product_bid_id; ?>',
	type: 'post',
	dataType: 'json',
	success: function(data) {
	  
	      $('.success, .warning, .attention, information, .error').remove();
		  
			if (data['error']) {
				$('#button-bid').after('<span class="error">' + data['error'] + '</span>');
			}
			
			if (data['success']) {
				$('.breadcrumb').after('<div class="success">' + data['success'] + '</div>');
				window.location.reload();
				}
		}
	});
  }
});
//--></script> 

 
<script type="text/javascript"><!--
$('#tabs a').tabs(); 
$('#languages a').tabs(); 
$('#vtab-option a').tabs();
//--></script> 
<?php echo $footer; ?>