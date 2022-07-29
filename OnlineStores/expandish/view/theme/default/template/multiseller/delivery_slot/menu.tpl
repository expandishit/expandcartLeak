<?php echo $header; ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
   .sidebar {
   margin: 0;
   padding: 0;
   width: 200px;
   height:50%;   
   background-color: #f1f1f1;
   position: absolute;
   overflow: auto;
   }
   .sidebar a {
   display: block;
   color: black;
   padding: 16px;
   text-decoration: none;
   }
   .sidebar a.active {
   background-color: #428bca;
   color: white;
   }
   .sidebar a:hover:not(.active) {
   background-color: #555;
   color: white;
   }
   div.contentleft {
   <?php if ($direction == 'rtl') { ?>
   margin-right: 200px;
   <?php }else{ ?>
      margin-left: 200px;
   <?php } ?>
   padding: 1px 16px;
   border: 1px solid #EEEEEE;
   }
   .switch {
   position: relative;
   display: inline-block;
   width: 60px;
   height: 34px;
   }
   .switch input { 
   opacity: 0;
   width: 0;
   height: 0;
   }
   .slider {
   position: absolute;
   cursor: pointer;
   top: 0;
   left: 0;
   right: 0;
   bottom: 0;
   background-color: #ccc;
   -webkit-transition: .4s;
   transition: .4s;
   }
   .slider:before {
   position: absolute;
   content: "";
   height: 26px;
   width: 26px;
   left: 4px;
   bottom: 4px;
   background-color: white;
   -webkit-transition: .4s;
   transition: .4s;
   }
   .paddingTop{ padding-top: 15px;}   
   input:checked + .slider {
   background-color: #2196F3;
   }
   input:focus + .slider {
   box-shadow: 0 0 1px #2196F3;
   }
   input:checked + .slider:before {
   -webkit-transform: translateX(26px);
   -ms-transform: translateX(26px);
   transform: translateX(26px);
   }
   /* Rounded sliders */
   .slider.round {
   border-radius: 34px;
   }
   .slider.round:before {
   border-radius: 50%;
   }
   @media screen and (max-width: 700px) {
   .sidebar {
   width: 100%;
   height: auto;
   position: relative;
   }
   .sidebar a {float: left;}
   div.contentleft {margin-left: 0;}
   }
   @media screen and (max-width: 400px) {
   .sidebar a {
   text-align: center;
   float: none;
   }
   }
  @media screen and (min-width: 768px){
   .modal-dialog {
    left: 0% !important;
   }}
     #AnyTime--slot_time_start,
    #AnyTime--slot_time_end {
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

    #AnyTime--slot_time_start .ui-state-disabled,
    #AnyTime--slot_time_start .ui-widget-content .ui-state-disabled,
    #AnyTime--slot_time_start .ui-widget-header .ui-state-disabled,

    #AnyTime--slot_time_end .ui-state-disabled,
    #AnyTime--slot_time_end .ui-widget-content .ui-state-disabled,
    #AnyTime--slot_time_end .ui-widget-header .ui-state-disabled {
        opacity: .35;
        filter: Alpha(Opacity=35);
        background-image: none;
    }

    #AnyTime--slot_time_start .ui-state-default,
    #AnyTime--slot_time_start .ui-widget-content .ui-state-default,
    #AnyTime--slot_time_start .ui-widget-header .ui-state-default,

    #AnyTime--slot_time_end .ui-state-default,
    #AnyTime--slot_time_end .ui-widget-content .ui-state-default,
    #AnyTime--slot_time_end .ui-widget-header .ui-state-default {
        border: 1px solid #c5dbec;
        background: #dfeffc url(images/ui-bg_glass_85_dfeffc_1x400.png) 50% 50% repeat-x;
        font-weight: bold;
        color: #2e6e9e;
    }

    #AnyTime--slot_time_start .ui-state-active,
    #AnyTime--slot_time_start .ui-widget-content .ui-state-active,
    #AnyTime--slot_time_start .ui-widget-header .ui-state-active,

    #AnyTime--slot_time_end .ui-state-active,
    #AnyTime--slot_time_end .ui-widget-content .ui-state-active,
    #AnyTime--slot_time_end .ui-widget-header .ui-state-active {
        border: 1px solid #79b7e7;
        background: #f5f8f9 url(images/ui-bg_inset-hard_100_f5f8f9_1x100.png) 50% 50% repeat-x;
        font-weight: bold;
        color: #e17009;
    }
    #AnyTime--slot_time_start .ui-state-disabled,
    #AnyTime--slot_time_end .ui-state-disabled {
        cursor: default !important;
    }

    #AnyTime--slot_time_start .ui-widget-header,
    #AnyTime--slot_time_end .ui-widget-header {
        border: 1px solid #4297d7;
        background: #5c9ccc url(expandish/view/theme/default/image/ui-bg_gloss-wave_55_5c9ccc_500x100.png) 50% 50% repeat-x;
        color: #ffffff;
        font-weight: bold;
    }

    #AnyTime--slot_time_start .AnyTime-hdr,
    #AnyTime--slot_time_end .AnyTime-hdr{
        background-color: #D0D0D1;
        color: #606062;
        font-family: Arial,Helvetica,sans-serif;
        font-size: medium;
        font-weight: 400;
        -moz-border-radius: 2px;
        -webkit-border-radius: 2px;
        border-radius: 2px;
    }

    #AnyTime--slot_time_start .ui-helper-reset,
    #AnyTime--slot_time_end .ui-helper-reset{
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
<?php if ($direction == 'rtl') { ?>
<link rel="stylesheet" type="text/css" href="expandish/view/theme/default/css/RTL/anytime.min.css" />
<?php } else { ?>
<link rel="stylesheet" type="text/css" href="expandish/view/theme/default/css/LTR/anytime.min.css" />
<?php } ?>

<div id="content" class="ms-account-product">
<?php echo $content_top; ?>
<div class="breadcrumb">
   <?php foreach ($breadcrumbs as $breadcrumb) { ?>
   <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
   <?php } ?>
</div>
<h3><i class="fa fa-truck"></i> <?=$ms_account_delivery_slot?></h3>
<?php 
if (strpos($_SERVER['REQUEST_URI'], "delivery_slots") !== false || strpos($_SERVER['REQUEST_URI'], "addNewSlot") !== false){$delivery_slots_active="active";}else{$delivery_slots_active="";}
if (strpos($_SERVER['REQUEST_URI'], "delivery_slots") == false && strpos($_SERVER['REQUEST_URI'], "slot_orders") == false && strpos($_SERVER['REQUEST_URI'], "addNewSlot") == false){$settings_active="active";}else{$settings_active="";}
if (strpos($_SERVER['REQUEST_URI'], "slot_orders") !== false){$slot_orders_active="active";}else{$slot_orders_active="";}
?>
<div class="sidebar">
   <a class="<?=$settings_active?>" href="<?=$settings_link?>"><i class="fa fa-cogs"></i> <?=$settings?></a>
   <a class="<?=$delivery_slots_active?>" href="<?=$delivery_slots_link?>"><i class="fa fa-calendar"></i> <?=$ms_account_delivery_slot?></a>
   <a class="<?=$slot_orders_active?>" href="<?=$slots_order_link?>"><i class="fa fa-shopping-cart"></i> <?=$slot_orders?></a>
</div>

 <script type="text/javascript" src="expandish/view/theme/default/js/anytime.min.js"></script>
 <script>
  $(document).ready(function(){
        //Current Client timezone..
        $('input[name="client_timezone"]').val(Intl.DateTimeFormat().resolvedOptions().timeZone);

        $("#slot_time_start").AnyTime_picker({
            format: "%I:%i%p", 
            labelTitle: "{{ lang('text_select_datetime') }}",
            labelHour: "{{ lang('text_hour') }}",
            labelMinute: "{{ lang('text_minute') }}",
          
        });

        $("#slot_time_end").AnyTime_picker({
            format: "%I:%i%p",
            labelTitle: "{{ lang('text_select_datetime') }}",
            labelHour: "{{ lang('text_hour') }}",
            labelMinute: "{{ lang('text_minute') }}",
         
        });

        $('.input-group-addon').click(function(){
            $(this).closest('.input-group').find('input[type=text]').focus();
        });

    });

</script>