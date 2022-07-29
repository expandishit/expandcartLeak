<?php echo $header; ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</style>
<?php if ($direction == 'rtl') { ?>
<link rel="stylesheet" type="text/css" href="expandish/view/theme/default/css/RTL/anytime.min.css" />
<?php } else { ?>
<link rel="stylesheet" type="text/css" href="expandish/view/theme/default/css/LTR/anytime.min.css" />
<?php } ?>
<style type="text/css">

    #AnyTime--start_datetime,
    #AnyTime--close_datetime {
        border: 1px solid #a6c9e2;
        background: #fcfdfd url(images/ui-bg_inset-hard_100_fcfdfd_1x100.png) 50% bottom repeat-x;
        color: #222222;
        padding: 0!important;
        -moz-border-radius: 5px;
        -webkit-border-radius: 5px;
        border-radius: 5px;
        font-family: Lucida Grande, Lucida Sans, Arial, sans-serif;
        font-size: 1.1em;
        background-size: auto 100%!important;

    }

    #AnyTime--start_datetime .ui-state-disabled,
    #AnyTime--start_datetime .ui-widget-content .ui-state-disabled,
    #AnyTime--start_datetime .ui-widget-header .ui-state-disabled,

    #AnyTime--close_datetime .ui-state-disabled,
    #AnyTime--close_datetime .ui-widget-content .ui-state-disabled,
    #AnyTime--close_datetime .ui-widget-header .ui-state-disabled {
        opacity: .35;
        filter: Alpha(Opacity=35);
        background-image: none;
    }

    #AnyTime--start_datetime .ui-state-default,
    #AnyTime--start_datetime .ui-widget-content .ui-state-default,
    #AnyTime--start_datetime .ui-widget-header .ui-state-default,

    #AnyTime--close_datetime .ui-state-default,
    #AnyTime--close_datetime .ui-widget-content .ui-state-default,
    #AnyTime--close_datetime .ui-widget-header .ui-state-default {
        border: 1px solid #c5dbec;
        background: #dfeffc url(images/ui-bg_glass_85_dfeffc_1x400.png) 50% 50% repeat-x;
        font-weight: bold;
        color: #2e6e9e;
    }

    #AnyTime--start_datetime .ui-state-active,
    #AnyTime--start_datetime .ui-widget-content .ui-state-active,
    #AnyTime--start_datetime .ui-widget-header .ui-state-active,

    #AnyTime--close_datetime .ui-state-active,
    #AnyTime--close_datetime .ui-widget-content .ui-state-active,
    #AnyTime--close_datetime .ui-widget-header .ui-state-active {
        border: 1px solid #79b7e7;
        background: #f5f8f9 url(images/ui-bg_inset-hard_100_f5f8f9_1x100.png) 50% 50% repeat-x;
        font-weight: bold;
        color: #e17009;
    }
    #AnyTime--start_datetime .ui-state-disabled,
    #AnyTime--close_datetime .ui-state-disabled {
        cursor: default !important;
    }

    #AnyTime--start_datetime .ui-widget-header,
    #AnyTime--close_datetime .ui-widget-header {
        border: 1px solid #4297d7;
        background: #5c9ccc url(expandish/view/theme/default/image/ui-bg_gloss-wave_55_5c9ccc_500x100.png) 50% 50% repeat-x;
        color: #ffffff;
        font-weight: bold;
    }

    #AnyTime--start_datetime .AnyTime-hdr,
    #AnyTime--close_datetime .AnyTime-hdr{
        background-color: #D0D0D1;
        color: #606062;
        font-family: Arial,Helvetica,sans-serif;
        font-size: medium;
        font-weight: 400;
        -moz-border-radius: 2px;
        -webkit-border-radius: 2px;
        border-radius: 2px;
    }

    #AnyTime--start_datetime .ui-helper-reset,
    #AnyTime--close_datetime .ui-helper-reset{
        margin: 0;
        padding: 0;
        border: 0;
        outline: 0;
        line-height: 1.3;
        text-decoration: none;
        font-size: 100%;
        list-style: none;
    }
    .input-group .form-control{position: initial !important;}
</style>

<div id="content" class="ms-account-profile">
	<?php echo $content_top; ?>
	
	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>
	

  <div class="panel panel-default">
            <ul class="nav nav-tabs nav-tabs-highlight nav-justified nav-tabs-top top-divided" style="margin-bottom: 0">
                <li class="active"><a data-toggle="tab" href="#tab-auction-info" style="font-size: 17px;"><i class="fa fa-info"></i> {{ lang('tab_auction_info') }}</a></li>
               <li><a data-toggle="tab" href="#tab-auction-bids" style="font-size: 17px;"><i class="fa fa-gavel"></i> {{ lang('tab_auction_bids') }}</a></li>
            </ul>
        </div>
        <div class="tab-content">
            <!-- TAB 1 -->
        <div class="tab-pane active" id="tab-auction-info">
              <div class="alert alert-success" id="successfull">
         <strong><?php echo $ms_success; ?></strong> 
            </div>
        <div class="panel panel-flat">
        <div class="col-md-12" id="error-area"><div class="alert alert-dange">
        <button type="button" class="close" data-dismiss="alert"><span>×</span><span class="sr-only">Close</span></button>
        <p class="warning main"></p>
        </div>
        </div>
        
            <div class="panel-body">
            <form class="form" id="ms-auction">
            <input type="hidden" name="client_timezone" value="">
            <input type="hidden" name="auction_id" value="<?php echo $auction[auction_id]; ?>">
                <!--product_id-->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">{{ lang('entry_product') }}</label>
                        <select class="form-control" name="product_id" id="products">

                        <?php if($products){
                        foreach($products as $product) { ?>
                                <option value="<?php echo $product[product_id]; ?>" data-price="<?php echo $product[price]; ?>" <?php echo ($product[product_id] == $auction[product_id]) ? 'selected':''; ?>> <?php echo $product[name]; ?></option>
                        <?php } } ?>
                        </select>
                        <span class="help-block"></span>
                    </div>
                </div>
                <!--/product_id-->



                <!--Starting bid price-->
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="starting_bid_price" class="control-label">{{ lang('entry_starting_bid_price') }}</label>
                        <div class='input-group'>
                            <input class="form-control" id="starting_bid_price" type="number" placeholder="1" name="starting_bid_price" min="1.00" step="0.01" value="<?php echo ($auction[starting_bid_price]) ?: products[0]['price']; ?>">
                            <span class="input-group-addon">
                                    <span><?php echo $current_store_currency; ?></span>
                            </span>
                        </div>
                        <span class="help-block">{{ lang('text_starting_bid_price_help') }}</span>

                    </div>
                </div>
                <!--/Starting bid price-->

        <!--Close Datetime-->
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="close_datetime" class="control-label">{{ lang('entry_close_datetime') }}</label>
                        <div class='input-group'>
                            <input type='text' class="form-control" name="close_datetime" id="close_datetime" value="<?php echo ($auction[close_datetime]) ? ($auction[close_datetime]):''; ?>" />
                            <span class="input-group-addon">
                                <span class="fa fa-calendar"></span>
                            </span>
                        </div>
                    </div>
                </div>
                <!--/Close Datetime-->

                <!--Starting Datetime-->
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="start_datetime" class="control-label">{{ lang('entry_start_datetime') }}</label> 
                        <div class='input-group'>
                            <input type='text' class="form-control datetimepicker" name="start_datetime" id="start_datetime" value="<?php echo ($auction[start_datetime]) ? ($auction[start_datetime]):''; ?>" />
                            <span class="input-group-addon">
                                <span class="fa fa-calendar"></span>
                            </span>
                        </div>
                    </div>
                </div>
                <!--/Starting Datetime-->


                <!--min_deposit-->
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="min_deposit" class="control-label">{{ lang('entry_min_deposit') }}</label>
                        <div class='input-group'>
                            <input class="form-control" type="number" id="min_deposit" name="min_deposit" min="0.00" step="0.50" value="<?php echo $auction[min_deposit]?:0 ?>">
                            <span class="input-group-addon">
                                    <span><?php echo $current_store_currency; ?></span>
                            </span>
                        </div>
                    </div>
                </div>
                <!--/min_deposit-->

                <!--increment-->
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="increment" class="control-label">{{ lang('entry_increment') }}</label>
                        <div class='input-group'>
                            <input class="form-control" type="number" id="increment" name="increment" placeholder="0.25" min="0.1" step="0.01" value="<?php echo $auction[increment] ?:0 ?>">
                            <span class="input-group-addon">
                                    <span><?php echo $current_store_currency; ?></span>
                            </span>
                        </div>
                        <span class="help-block">{{ lang('text_increment_help') }}</span>
                    </div>
                </div>
                <!--/increment-->


                
                <!--quantity-->
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="quantity" class="control-label">{{ lang('entry_quantity') }}</label>
                        <div class='input-group'>
                            <input class="form-control" type="number" id="quantity" value="1" disabled>
                            <span class="input-group-addon">
                                    <span>{{lang('text_items')}}</span>
                            </span>
                        </div>
                        <span class="help-block"></span>                
                    </div>
                </div>
                <!--/quantity-->


                <!--purchase_valid_days-->
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="purchase_valid_days" class="control-label">{{ lang('entry_purchase_valid_days') }}</label> 
                        <div class='input-group'>
                            <input class="form-control" type="number" id="purchase_valid_days" name="purchase_valid_days" placeholder="3" min="1" step="1" max="99" value="<?php echo $auction[purchase_valid_days] ?:3 ?>">
                            <span class="input-group-addon">
                                    <span>{{ lang('text_days') }}</span>
                            </span>
                        </div>
                        <span class="help-block">{{ lang('text_purchase_valid_days_help') }}</span>
                    </div>
                </div>
                <!--/purchase_valid_days-->
                    <!--Auction Status-->
                <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label">{{ lang('entry_auction_status') }}: </label>
                    <label>
                            <input type="checkbox" onchange="changeStatus(this);" value="1" name="auction_status"  class="switchery"<?php echo ($auction[auction_status] == 1) ? 'checked':''; ?> >
                            <span class="switchery-status">  <?php echo ($auction[auction_status] == 1) ? $textenabl : $textdisabled ?></span>
                    </label>
                
                </div>
                </div>
                <!--/Auction Status-->

            </div>
        </div>


            <!-- Lower Buttons -->
            <div class="buttons">
                    <div class="left">
                        <a href="<?php echo $link_back; ?>" class="button">
                            <span><?php echo $button_back; ?></span>
                        </a>
                      
                    </div>
                        <div class="right">
                       <a class="button" id="auction-submit-button">
					<span><?php echo $ms_button_save; ?></span>
				</a>
                    </div>
            </div>

   </div>

        <!-- TAB 2 -->
            <div class="tab-pane" id="tab-auction-bids">
               <?php include('auction_bids.tpl'); ?> 
            </div>
</div>

 <script type="text/javascript" src="expandish/view/theme/default/js/anytime.min.js"></script>
<script type="text/javascript">
        $('#error-area').hide();
        $('#successfull').hide();
		$("#auction-submit-button").click(function() {
		$('.success').remove();
		var button = $(this);
		var id = $(this).attr('id');
		
		$.ajax({
			type: "POST",
			dataType: "json",
			url: $('base').attr('href') + 'index.php?route=seller/account-auctions/store',
			data: $("form#ms-auction").serialize(),
			beforeSend: function() {
				button.hide().before('<span class="wait">&nbsp;<img src="catalog/view/theme/default/image/loading.gif" alt="" /></span>');
				$('p.error').remove();
			},
			complete: function(jqXHR, textStatus) {
				if (textStatus != 'success') {
					button.show().prev('span.wait').remove();
					$(".warning.main").text(msGlobals.formError).show();
					window.scrollTo(0,0);
				}
			},  
			success: function(jsonData) {	
                if(jsonData.success){
                     $('#error-area').hide();
                    $('#auction-submit-button').show().prev('span.wait').remove();
                     $("#successfull").show().delay(5000).fadeOut();
                     $('#auction-submit-button').hide();
                    }
                else{
				if (!jQuery.isEmptyObject(jsonData.errors)) {
					$('#auction-submit-button').show().prev('span.wait').remove();
                     $('#successfull').hide();
                    $('#error-area').show();
                    $(".warning.main").show();    
					for (error in jsonData.errors) {
                     $( ".warning.main" ).append('<p class="error">' + jsonData.errors[error] + '</p>');
                    }
					window.scrollTo(0,0);
				} else {
					window.location = jsonData.redirect;
				}
                }
	       	}
		});
	});



    //To display the placeholder value in google chrome, There is a bug in chrome https://issuetracker.google.com/issues/36939094
    $("input[type='number']").each(function(i, el) {
      el.type = "text";
      el.onfocus = function(){this.type="number";};
      el.onblur = function(){this.type="text";};
    });

    //set starting bid with product price..
    $("select#products").change(function () {
        var price = 1;
        $("select#products option:selected").each(function() {
            price = $(this).data('price');
        });
        //Round price to 2 decimal points instead of 00.0000
        $("#starting_bid_price").val(parseFloat(price).toFixed(2));
    });

</script>
<script type="text/javascript">
    $(document).ready(function(){
        //Current Client timezone..
        $('input[name="client_timezone"]').val(Intl.DateTimeFormat().resolvedOptions().timeZone);

        $("#start_datetime").AnyTime_picker({
            format: "%Y-%m-%d %I:%i%p", 
            labelTitle: "{{ lang('text_select_datetime') }}",
            labelHour: "{{ lang('text_hour') }}",
            labelMinute: "{{ lang('text_minute') }}",
            labelYear: "{{ lang('text_year') }}",
            labelMonth: "{{ lang('text_month') }}",
            labelDayOfMonth: "{{ lang('text_day_of_month') }}",
        });

        $("#close_datetime").AnyTime_picker({
            format: "%Y-%m-%d %I:%i%p",
            labelTitle: "{{ lang('text_select_datetime') }}",
            labelHour: "{{ lang('text_hour') }}",
            labelMinute: "{{ lang('text_minute') }}",
            labelYear: "{{ lang('text_year') }}",
            labelMonth: "{{ lang('text_month') }}",
            labelDayOfMonth: "{{ lang('text_day_of_month') }}",
        });

        $('.input-group-addon').click(function(){
            $(this).closest('.input-group').find('input[type=text]').focus();
        });

    });

    function changeStatus(checkbox){

        var self = $(checkbox);
        var switch_status = self.siblings('.switchery-status');

        if ( self.is(':checked') ){
            switch_status.html("{{ lang('text_enabl') }}");

        }
        else {
            switch_status.html("{{ lang('text_disable') }}");

        }
    }

</script>
<?php echo $footer; ?>