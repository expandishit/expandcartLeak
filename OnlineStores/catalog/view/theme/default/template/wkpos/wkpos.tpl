<!DOCTYPE html>
<!--[if IE]><![endif]-->
<!--[if IE 8 ]><html dir="<?php echo $direction; ?>" lang="<?php echo $lang; ?>" class="ie8"><![endif]-->
<!--[if IE 9 ]><html dir="<?php echo $direction; ?>" lang="<?php echo $lang; ?>" class="ie9"><![endif]-->
<!--[if (gt IE 9)|!(IE)]><!-->
<!-- <html dir="<?php echo $direction; ?>" lang="<?php echo $lang; ?>" manifest="../manifest.appcache"> -->
<html dir="<?php echo $direction; ?>" lang="<?php echo $lang; ?>">
<!--<![endif]-->
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php echo $title; ?></title>
  <base href="<?php echo $base; ?>" />
  <?php if ($description) { ?>
  <meta name="description" content="<?php echo $description; ?>" />
  <?php } ?>
  <?php if ($keywords) { ?>
  <meta name="keywords" content= "<?php echo $keywords; ?>" />
  <?php } ?>
  <script src="catalog/view/javascript/jquery/jquery-2.1.1.min.js" type="text/javascript"></script>
  <?php if ($direction == 'rtl') { ?>
  <link href="wkpos/css/bootstrap-rtl.css" rel="stylesheet" media="screen" />
  <?php } else { ?>
  <link href="catalog/view/javascript/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen" />
  <?php } ?>
  <script src="catalog/view/javascript/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
  <link href="catalog/view/javascript/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
  <link href="//fonts.googleapis.com/css?family=Open+Sans:400,400i,300,700" rel="stylesheet" type="text/css" />
  <link href="catalog/view/theme/default/stylesheet/stylesheet-pos.css" rel="stylesheet">
  <link href="catalog/view/javascript/iti/css/intlTelInput.min.css" rel="stylesheet">
  <?php if ($direction == 'rtl') { ?>
  <link href="wkpos/css/wkpos-rtl.css" rel="stylesheet" media="screen">
  <?php } else { ?>
  <link href="wkpos/css/wkpos.css" rel="stylesheet" media="screen">
  <?php } ?>
  <?php foreach ($styles as $style) { ?>
  <link href="<?php echo $style['href']; ?>" type="text/css" rel="<?php echo $style['rel']; ?>" media="<?php echo $style['media']; ?>" />
  <?php } ?>
  <script src="catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js" type="text/javascript"></script>
  <script src="catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
  <link href="catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css" type="text/css" rel="stylesheet" media="screen" />
  <script src="catalog/view/javascript/iti/js/intlTelInput.min.js" type="text/javascript"></script>
  <script src="wkpos/js/localforage.js" type="text/javascript"></script>
  <script src="wkpos/js/common.js" type="text/javascript"></script>
  <script src="wkpos/js/wkpos.js?v=0.1" type="text/javascript"></script>
  <?php foreach ($links as $link) { ?>
  <link href="<?php echo $link['href']; ?>" rel="<?php echo $link['rel']; ?>" />
  <?php } ?>
  <?php foreach ($scripts as $script) { ?>
  <script src="<?php echo $script; ?>" type="text/javascript"></script>
  <?php } ?>
  <?php $font_store = '14px'; ?>
  <?php if ($paper_size == '80mm') { ?>
  <?php $font_store = '13px'; ?>
  <?php } ?>
  <?php if ($paper_size == '58mm') { ?>
  <?php $font_store = '11px'; ?>
  <?php } ?>
  <style type="text/css">
  .thermal {
    width: <?php echo $paper_size; ?>;
    font-weight: <?php echo $font_weight; ?>;
  }
  .font-store {
  font-size: <?php echo $font_store; ?>;
  }
  @media print {
    .modal{
      display: none;
    }
  }
  </style>
</head>
<body>
  <div id="top-div">
    <div id="clockin">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
          <div class="modal-body">
            <h3><b><?php echo $text_resume_sess; ?></b></h3>
            <button class="btn buttons-sp" id="resumeSession" tabindex="5"><i class="fa fa-lg fa-hourglass-half"></i> <?php echo $text_resume; ?></button>
            <button class="btn buttons-sp" id="startSession" tabindex="6"><i class="fa fa-lg fa-hourglass-start"></i> <?php echo $text_start_sess; ?></button>
          </div>
        </div>
      </div>
    </div>
    <div id="postorder">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
          <div class="modal-body">
            <h3 style="margin-top: 0;"><b><?php echo $heading_invoice; ?></b></h3>
            <a class="buttons-sp width-100" id="normal-invoice2" data-href="<?php echo $base.'admin/sale/order/invoice?language_id='.$lang_id.'&language_directory='.$lang_directory.'&language_code='.$lang.'&order_id='; ?>" href="" style="display: block" target="_blank"><i class="fa fa-lg fa-file"></i> <?php echo $button_show_invoice; ?></a>
            <button class="btn buttons-sp width-100" onclick="printInvoice();"><i class="fa fa-lg fa-print"></i> <?php echo $text_print; ?></button>
            <br/><button class="btn buttons-sp width-100" onclick="$('#postorder, #loader').css('display', 'none');"><?php echo $button_skip; ?> <i class="fa fa-lg fa-arrow-right"></i></button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal" id="loginModalParent">
      <div class="col-sm-5 col-xs-12 text-center login-div">
        <div class="form-horizontal">
          <h3><?php echo $heading_login; ?></h3>
          <div class="form-group input-group">
            <span class="input-group-addon"><i class="fa fa-lg fa-envelope"></i></span>
            <input type="text" class="form-control" id="input-username" placeholder="<?php echo $entry_username; ?>" value="" name="username" tabindex="2">
          </div>
          <div class="form-group input-group">
            <span class="input-group-addon"><i class="fa fa-lg fa-key"></i></span>
            <input type="password" class="form-control" id="input-password" placeholder="<?php echo $entry_password; ?>" value="" name="password" tabindex="3">
          </div>
          <button class="buttons-sp" id="login-submit" onclick="loginUser();" tabindex="4"><?php echo $button_signin; ?></button>
        </div>
      </div>
      <div class="col-sm-7 col-xs-12 home-div">
        <div class="pos-intro" >
          <div class="row">
            <div class="col-md-12">
              <img src="image/home-pos.jpg" alt="ExpandCart POS (Point of Sale)" style="width:100%;border-radius: 4px;">
            </div>
          </div>
        </div>
        <!--<h2><b><?php if ($pos_heading1) { echo $pos_heading1; } else { ?>A Complete Retail Management Solution<?php } ?></b></h2>
        <div>
          <h3><b><?php if ($pos_heading2) { echo $pos_heading2; } else { ?>Branding<?php } ?></b></h3>
          <?php if ($pos_content) {
            echo $pos_content;
          } else { ?>
          Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
          <?php } ?>
        </div>-->
      </div>

    </div>
    <div class="col-xs-1" id="pos-side-panel">
      <div class="col-xs-12" id="left-panel">
        <div class="wksidepanel" style="padding: 5px; text-align:center;">
          <img src="<?php echo $image; ?>" class="img-responsive logger-img" width="40" height="40">
          <div style="margin-left: 5px;">
            <span class="logger-name"><?php echo $name; ?></span><br>
            <span class="logger-post hidden-xs">(<?php echo $group_name; ?>)</span><br>
            <div class="dropdown">
              <button class="btn label label-info dropdown-toggle" data-toggle="dropdown"><?php echo $text_more; ?> <i class="caret"></i></button>
              <ul class="dropdown-menu dropdown-menu-left cursor">
                <li>
                  <a onclick="accountSettings(this);"><?php echo $text_account_settings; ?></a>
                </li>
                <li>
                  <a onclick="logout();"><?php echo $text_logout; ?></a>
                </li>
              </ul>
            </div>
          </div>
        </div>
        <div class="wksidepanel button-payment" id="button-payment"><i class="fa fa-lg fa-credit-card"></i><br><span class="hidden-xs"><?php echo $text_checkout; ?></span></div>
        <div class="wksidepanel" id="button-order"><i class="fa fa-lg fa-circle-o"></i><br><span class="hidden-xs"><?php echo $text_orders; ?></span></div>
        <div class="wksidepanel" id="button-return"><i class="fa fa-lg fa-reply"></i><br><span class="hidden-xs"><?php echo $text_returns; ?></span></div>
        <div class="wksidepanel" id="button-expense"><i class="fa fa-lg fa-money"></i><br><span class="hidden-xs"><?php echo $text_expenses; ?></span></div>
        <div class="wksidepanel" id="button-account"><i class="fa fa-lg fa-wrench"></i><br><span class="hidden-xs"><?php echo $text_settings; ?></span></div>
        <div class="wksidepanel" id="button-other"><i class="fa fa-lg fa-gears"></i><br><span class="hidden-xs"><?php echo $text_others; ?></span></div>
      </div>
      <div class="mode-div">
        <button class="btn btn-success btn-labeled" id="mode">
        <span class="btn-label"><i class="fa fa-toggle-on"></i></span>
        <span class="hidden-xs"><?php echo $text_online; ?></span></button>
      </div>
    </div>
    <div class="col-xs-8 category-div">
      <div class="col-xs-12 upper-category">
        <div class="col-xs-9 col-md-8 lower-category">
          <div class="wkcategory categoryProduct" category-id="0"><span class="hidden-xs"><?php echo $text_populars; ?></span><span class="hidden-sm hidden-md hidden-lg"><i class="fa fa-lg fa-fire font-22"></i></span></div>
          <span class="hidden-xs">
          <?php foreach ($categories as $key => $category) { ?>
            <div class="wkcategory categoryProduct" category-id="<?php echo $category['category_id']; ?>"><?php echo $category['name']; ?></div>
            <?php if ($key == 2) {
              break;
            } ?>
          <?php } ?>
          </span>
          <span data-toggle="modal" data-target="#categoryList" class="wkcategory"><i class="fa fa-lg fa-list font-22"></i></span>
        </div>
        <div class="col-xs-3 col-md-4" id="search-div">
          <input type="text" name="search" id="search" class="form-control" placeholder="<?php echo $text_search; ?>">
        </div>
      </div>
      <div class="col-xs-12 text-center outer-product-panel">
        <!--<h4><?php echo $text_all_products; ?></h4>-->
        <div class="col-xs-12" id="product-panel">
        </div>
      </div>
      <div class="parents account-parent">
        <div class="payment-child" style="width: 35%;">
          <div class="wkpaymentmethod wkaccounts" type="basic"><i class="fa fa-lg fa-wrench"></i> <span class="margin-10"><?php echo $text_basic_settings; ?></span><i class="fa fa-lg fa-chevron-<?php if ($direction == 'ltr') { echo 'right'; } else { echo 'left'; } ?> hide"></i></div>
          <div class="wkpaymentmethod wkaccounts" type="other"><i class="fa fa-lg fa-cogs"></i> <span class="margin-10"><?php echo $text_other_settings; ?></span><i class="fa fa-lg fa-chevron-<?php if ($direction == 'ltr') { echo 'right'; } else { echo 'left'; } ?> hide"></i></div>
        </div>
        <div class="payment-child2 form-horizontal" style="width: 65%;">
          <span class="pull-right cursor close-it"><i class="fa fa-lg fa-times font-25"></i></span>
          <div class="basic-account setting-account hide">
            <div class="scroll-account">
              <div class="form-group required">
                <label class="col-sm-4 control-label" for="first-name"><?php echo $text_firstname; ?></label>
                <div class="col-sm-8">
                  <input class="form-control" id="first-name" name="firstname" placeholder="<?php echo $text_firstname; ?>" type="text" value="<?php echo $firstname; ?>">
                </div>
              </div>
              <div class="form-group required">
                <label class="col-sm-4 control-label" for="last-name"><?php echo $text_lastname; ?></label>
                <div class="col-sm-8">
                  <input class="form-control" id="last-name" name="lastname" placeholder="<?php echo $text_lastname; ?>" type="text" value="<?php echo $lastname; ?>">
                </div>
              </div>
              <div class="form-group required">
                <label class="col-sm-4 control-label" for="user-name"><?php echo $text_username; ?></label>
                <div class="col-sm-8">
                  <input class="form-control" id="user-name" name="username" placeholder="<?php echo $text_username; ?>" type="text" value="<?php echo $username; ?>">
                </div>
              </div>
              <div class="form-group required">
                <label class="col-sm-4 control-label" for="account-email"><?php echo $text_account_email; ?></label>
                <div class="col-sm-8">
                  <input class="form-control" id="account-email" name="account_email" placeholder="<?php echo $text_account_email; ?>" type="text" value="<?php echo $email; ?>">
                </div>
              </div>
              <div class="form-group required">
                <label class="col-sm-4 control-label" for="account-ppwd"><?php echo $text_ppassword; ?></label>
                <div class="col-sm-8">
                  <input class="form-control" id="account-ppwd" name="account_ppwd" placeholder="<?php echo $text_ppassword; ?>" type="password" value="">
                </div>
              </div>
              <div class="form-group required">
                <label class="col-sm-4 control-label" for="account-npwd"><?php echo $text_npassword; ?></label>
                <div class="col-sm-8">
                  <input class="form-control" id="account-npwd" name="account_npwd" placeholder="<?php echo $text_npassword; ?>" type="password" value="">
                </div>
              </div>
              <div class="form-group required">
                <label class="col-sm-4 control-label" for="account-cpwd"><?php echo $text_cpassword; ?></label>
                <div class="col-sm-8">
                  <input class="form-control" id="account-cpwd" name="account_cpwd" placeholder="<?php echo $text_cpassword; ?>" type="password" value="">
                </div>
              </div>
            </div>
            <button class="col-xs-12 btn buttons-sp button-accounts" stype="basic"><strong><?php echo $text_save_details; ?></strong></button>
          </div>
          <div class="other-account setting-account hide">
            <div class="form-group">
              <label class="col-sm-4 control-label" for="input-langauge"><?php echo $text_language; ?></label>
              <div class="col-sm-8">
                <select class="form-control" id="input-language">
                  <?php foreach ($languages as $language_value) { ?>
                  <?php if ($language == $language_value['code']) { ?>
                  <option value="<?php echo $language_value['code']; ?>" selected="selected"><?php echo $language_value['name']; ?></option>
                  <?php } else { ?>
                  <option value="<?php echo $language_value['code']; ?>"><?php echo $language_value['name']; ?></option>
                  <?php } ?>
                  <?php } ?>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-4 control-label" for="input-currency"><?php echo $text_currency; ?></label>
              <div class="col-sm-8">
                <select class="form-control" id="input-currency">
                  <?php foreach ($currencies as $currency_value) { ?>
                  <?php if ($currency == $currency_value['code']) { ?>
                    <option value="<?php echo $currency_value['code']; ?>" selected="selected"><?php echo $currency_value['title']; ?></option>
                  <?php } else { ?>
                    <option value="<?php echo $currency_value['code']; ?>"><?php echo $currency_value['title']; ?></option>
                  <?php } ?>
                  <?php } ?>
                </select>
              </div>
            </div>
            <button class="col-xs-12 btn buttons-sp button-accounts" stype="other"><strong><?php echo $text_save; ?></strong></button>
          </div>
        </div>
      </div>
      <div class="parents payment-parent">
        <div class="payment-child">
          <h3 class="payment-heading"><strong><?php echo $heading_payments; ?>:</strong></h3>
          <div id="payment-methods">
            <?php if ($cash_payment_status) { ?>
            <div class="wkpaymentmethod" type="cash-payment"><i class="fa fa-lg fa-lg fa-money"></i> <span class="margin-10"><?php echo $cash_payment_title; ?></span><i class="fa fa-lg fa-chevron-<?php if ($direction == 'ltr') { echo 'right'; } else { echo 'left'; } ?> hide"></i></div>
            <?php } ?>
            <?php if ($card_payment_status) { ?>
            <div class="wkpaymentmethod" type="card-payment"><i class="fa fa-lg fa-lg fa-credit-card"></i> <span class="margin-10"><?php echo $card_payment_title; ?></span><i class="fa fa-lg fa-chevron-<?php if ($direction == 'ltr') { echo 'right'; } else { echo 'left'; } ?> hide"></i></div>
            <?php } ?>
              <?php if ($custom_payment_title) { ?>
                  <?php
                  foreach ($custom_payment_title as $value){
                  ?>
                  <div class="wkpaymentmethod" type="<?php echo $value ?>"><i class="fa fa-lg fa-lg fa-credit-card"></i>
                      <span class="margin-10"><?php echo $value; ?></span><i class="fa fa-lg fa-chevron-<?php
                      if ($direction == 'ltr') { echo 'right'; } else { echo 'left'; } ?> hide"></i></div>
                      <?php } ?>
              <?php } ?>
          </div>
        </div>
        <div class="payment-child2 form-horizontal">
          <span class="pull-right cursor  close-it" style="margin: 20px;"><i class="fa fa-lg fa-lg fa-times font-25"></i></span>
          <div class="cash-payment hide">
            <div class="form-group">
              <div class="col-sm-12">
              <label class="control-label"><?php echo $text_balance_due; ?></label>
                <div class="form-control" id="balance-due"></div>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-12">
              <label class="control-label" for="amount-tendered"><?php echo $text_tendered; ?></label>
                <div class="input-group input-group1">
                  <?php if ($symbol_position == 'L') { ?>
                  <span class="input-group-addon currency"><strong><?php echo $currency_code; ?></strong></span>
                  <?php } else { ?>
                  <span class="input-group-addon currency" style="display: none;"><?php echo $currency_code; ?></span>
                  <?php } ?>
                  <input class="form-control" id="amount-tendered" placeholder="" type="text" onkeypress="return validate(event, this)">
                  <?php if ($symbol_position == 'R') { ?>
                  <span class="input-group-addon currency"><strong><?php echo $currency_code; ?></strong></span>
                  <?php } else { ?>
                  <span class="input-group-addon currency" style="display: none;"><?php echo $currency_code; ?></span>
                  <?php } ?>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-12">
              <label class="control-label"><?php echo $text_change; ?></label>
                <div class="form-control" id="change"></div>
              </div>
            </div>
          </div>
          <div class="all-payment hide">
            <?php if ($credit_status) { ?>
              <div class="form-group">
                <div class="col-sm-12">
                <label class="control-label"><?php echo $text_balance_credit; ?></label>
                  <div class="form-control input-group" id="balance-credit">
                    <?php echo $select_customer_first; ?>
                  </div>
                </div>
              </div>
            <?php } ?>
            <div class="form-group">
              <div class="col-sm-12">
                <textarea class="form-control" id="orderNote" placeholder="<?php echo $text_add_order_note; ?>"></textarea>
              </div>
            </div>
            <button class="btn btn-success btn-block accept-payment"><strong><?php echo $button_payment; ?></strong></button>
          </div>
        </div>
      </div>
      <!-- POS Update Code for Return  -->
      <div class="parents return-parent">
        <!-- <h3 class="pull-left" id="return-heading"><?php echo $text_returned_product; ?></h3> -->
          <span class="pull-right cursor  close-it" onclick="$('#returns').html('');"><i class="fa fa-lg fa-times font-25"></i></span>
        <div class="col-sm-12" id="return-container">
          <div class="row" id="returns"></div>
        </div>
      </div>
      <div class="parents return-form-parent">
        <div class="col-xs-12">
          <h3 class="text-center"><?php echo $return_heading; ?><span class="pull-right cursor  close-it"><i class="fa fa-lg fa-times font-25"></i></span></h3>
        </div>
        <div class="col-sm-12" style="overflow-y:auto;">
          <div class="row"  id="return-form">

          </div>
        </div>
      </div>
      <!-- POS Update Code for Return  -->
      <div class="parents order-parent">
        <div class="col-xs-12 order-child">
          <div class="wkcategory wkorder" otype="1"><?php echo $text_previous; ?></div>
          <div class="wkcategory wkorder" otype="2"><?php echo $text_onhold; ?></div>
          <div class="wkcategory wkorder" otype="3"><?php echo $text_offline; ?></div>
          <span class="pull-right cursor  close-it"><i class="fa fa-lg fa-times font-25"></i></span>
        </div>
        <div class="col-sm-12" id="order-container">
          <div class="row" id="orders"></div>
        </div>
      </div>
      <div class="parents other-parent">
        <div class="col-xs-12 other-child">
          <div class="wkcategory wkother" otype="1"><?php echo $text_low_stock; ?></div>
          <div class="wkcategory wkother" otype="2"><?php echo $text_request; ?></div>
          <div class="wkcategory wkother" otype="3"><?php echo $text_request_history; ?></div>
          <span class="pull-right cursor  close-it"><i class="fa fa-lg fa-times font-25"></i></span>
        </div>
        <div class="col-sm-12" id="other-container">
          <div class="row" id="others"></div>
        </div>
      </div>
      <div class="parents expense-parent">
            <div class="col-xs-12 expense-child">
                <div class="wkcategory wkexpense onfocus" otype="1"><?php echo $text_new_expense; ?></div>
                <div class="wkcategory wkexpense" otype="2"><?php echo $text_expenses_history; ?></div>
                <span class="pull-right cursor  close-it"><i class="fa fa-lg fa-times font-25"></i></span>
            </div>
            <div class="col-sm-12"  style="height: 285px;">
                <div class="" id="expenses">
                    <form id="expenses-form">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label class="control-label <?php if ($direction == "ltr") echo "pull-left"?>" for="title">
                                    <?php echo $text_expenses_title ?>
                                </label>
                                <input type="text" name="title" class="form-control" id="title" aria-describedby="titleHelp" placeholder="">
                                <small id="titleHelp" class="form-text text-muted"></small>
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label class="<?php if ($direction == "ltr") echo "pull-left"?>" for="description"><?php echo $text_expenses_desc ?></label>
                                <textarea class="form-control" id="description" placeholder=""></textarea>
                            </div>
                        </div>

                        <div class="col-sm-12">
                        <div class="form-check">
                            <label class="form-check-label <?php if ($direction == "ltr") echo "pull-left"?>" for="amount"><?php echo $text_expenses_amount ?></label>
                        <div class="col-sm-12 input-group input-group1 form-check">
                            <input class="form-control" id="amount" placeholder="" type="text" onkeypress="return validate(event, this)">
                            <?php if ($symbol_position == 'L') { ?>
                                <span class="input-group-addon currency"><strong><?php echo $currency_code; ?></strong></span>
                            <?php } else { ?>
                                <span class="input-group-addon currency"><strong><?php echo $currency_code; ?></strong></span>
                            <?php } ?>
                        </div>

                        </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <button type="submit" class="btn button-expenses btn-success btn-block expenses-button">
                                    <strong>Submit</strong>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="expenses-table"></div>
            </div>
      </div>
    </div>
    <div class="col-xs-3" id="cart-panel">
      <div id="logger-detail">
      </div>
      <div class="sidepanel ">
        <div class="upper-category"><i class="fa fa-lg fa-chevron-<?php if ($direction == 'ltr') { echo 'right'; } else { echo 'left'; } ?> pull-right close-it cursor"></i><span class="order-txn"><?php echo $text_order_id; ?></span> #<span class="oid"></span></div>
        <div class="table-responsive hide" id="return-section">

        </div>
        <div class="col-xs-12 hide" id="return-details">

        </div>
        <div id="sidepanel-inner" style="display: none;">
          <div class="col-xs-12" id="order-upper-div">
            <div class="pull-left">
              <span class="order-date"></span><br/>
              <span class="order-time"></span><br/>
              <span><?php echo $text_purchase; ?></span>
            </div>
            <div class="pull-right" id="order-address"></div>
          </div>
          <div class="col-xs-12" style="margin-top: 10px;">
            <span class="pull-left font-bold"><?php echo $text_shipping_mode; ?>:&nbsp;</span>
            <span class=""><?php echo $text_pickup; ?></span>
          </div>
          <h5 class="col-xs-12 font-bold"><?php echo $text_order_details; ?></h5>
          <div class="width-100 order-scroll">
            <div class="col-xs-12 table-responsive">
              <table class="table table-hover margin-bottom-0" id="oitem-table">
                <tbody id="oitem-body">
                </tbody>
              </table>
            </div>
            <hr class="hr-tag">
            <hr class="hr-tag">
            <div class="col-xs-12 table-responsive">
              <table class="table table-hover margin-bottom-0">
                <tbody id="oTotals">
                </tbody>
              </table>
            </div>
          </div>
          <hr class="hr-tag">
          <div class="col-xs-12 table-responsive table-total" style="margin: 0; width: 100%;">
            <table class="table table-hover margin-bottom-0">
              <tbody>
                <tr>
                  <td class="text-left"><?php echo $text_total; ?></td>
                  <td class="text-right oTotal">00.00</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="col-xs-12" style="margin-top: 5px;">
            <span class="pull-left font-bold"><?php echo $text_payment_mode; ?>:&nbsp;</span>
            <span class="opayment"></span>
          </div>
          <div class="col-xs-12" style="margin-top: 5px;">
            <span class="pull-left font-bold"><?php echo $text_note; ?>:&nbsp;</span>
            <span class="onote"></span>
          </div>
          <div class="col-xs-12" style="margin-top: 5px;">
            <button class="buttons-sp width-100" onclick="printBill();"><?php echo $text_print; ?></button>
          </div>
          <div class="col-xs-12" style="margin-top: 5px;">
            <a class="buttons-sp width-100" id="normal-invoice" data-href="<?php echo $base.'admin/sale/order/invoice?language_id='.$lang_id.'&language_directory='.$lang_directory.'&language_code='.$lang.'&order_id='; ?>" href="" style="display: block" target="_blank"><?php echo $button_show_invoice; ?></a>
          </div>
        </div>
        <div class="order-loader hide">
          <div class="cp-spinner cp-round"></div>
        </div>
      </div>
      <div class="col-xs-12 upper-cart-detail">
        <div class="col-xs-12 lower-cart-detail">
          <div class="pull-left"><i class="fa fa-lg fa-trash-o cursor font-22" title="<?php echo $text_delete_cart; ?>" data-toggle="tooltip" onclick="cart_list.delete(1)"></i></div>
          <b class="cursor" id="more-carts"><span class=""><?php echo $text_cart_details; ?></span> (<span class="cart-total">0</span>) <span class="caret"> </span></b>
          <div id="upper-cart">
          </div>
          <div class="pull-right"><i class="fa fa-lg fa-plus-circle cursor font-22" title="<?php echo $text_add_service; ?>" data-toggle="modal" data-target="#addProduct"></i></div>
        </div>
        <br>
        <div id="cart-detail">
          <div class="table-responsive">
            <table class="table table-hover margin-bottom-0" id="item-table">
              <tbody id="item-body">
                <tr><td class="text-center"><?php echo $text_empty_cart; ?></td></tr>
              </tbody>
            </table>
          </div>
          <hr class="hr-tag">
          <hr class="hr-tag">
          <div class="table-responsive div-totals">
            <table class="table table-hover margin-bottom-0">
              <tbody>
                <tr>
                  <td class="text-left"><?php echo $text_subtotal; ?></td>
                  <td class="text-right" id="subtotal">0.00</td>
                </tr>
                <?php if($tax_status) { ?>
                <tr>
                  <td class="text-left"><?php echo $button_tax; ?></td>
                  <td class="text-right" id="tax">$0.00</td>
                </tr>
                <?php } ?>
                <tr id="discrow" style="display: none;">
                  <td class="text-left" id="discname"><?php echo $button_discount; ?></td>
                  <td class="text-right" id="discount">$0.00</td>
                </tr>
                <tr id="couprow" style="display: none;">
                  <td class="text-left"><?php echo $button_coupon; ?></td>
                  <td class="text-right" id="coupondisc">$0.00</td>
                </tr>
                <?php if ($home_delivery_status) { ?>
                <tr>
                  <td>
                    <div class="checkbox checkbox-primary"><input id="checkbox-home-delivery" type="checkbox"><label for="checkbox-home-delivery"><?php echo $home_delivery_title; ?></label>
                    </div>
                  </td>
                  <td><input type="number" class="form-control pull-right" placeholder="<?php echo $entry_delivery_charge; ?>" id="delivery-charge" min="0" onkeypress="return validate(event, this)"></td>
                </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
        <div class="table-responsive table-total">
          <table class="table table-hover margin-bottom-0">
            <tbody>
              <tr>
                <td class="text-left"><?php echo $text_total; ?></td>
                <td class="text-right" id="cartTotal">00.00</td>
              </tr>
            </tbody>
          </table>
        </div>
 
        <button class="btn btn-block" id="button-customer" data-toggle="modal" data-target="#customerSearch"><i class="fa fa-lg fa-user font-22"></i> <strong class="" id="customer-name"><?php echo $button_customer; ?></strong></button>
        <div class="" >
          <?php if ($discount_status && $coupon_status) { ?>
          <div class="row">
            <div class="col-xs-6">
              <button class="btn-block btn buttons-sp" data-toggle="modal" data-target="#addDiscount" id="button-discount"><i class="fa fa-lg fa-minus-square"></i> <strong class=""><?php echo $button_discount; ?></strong></button>
            </div>
            <div class="col-xs-6">
              <button class="btn-block btn buttons-sp" data-toggle="modal" data-target="#addCoupon" id="button-coupon"><i class="fa fa-lg fa-ticket"></i> <strong class=""><?php echo $button_coupon; ?></strong></button>
            </div>
          </div>
          <?php } else { ?>
            <?php if ($discount_status) { ?>
            <button class="btn-block btn buttons-sp" data-toggle="modal" data-target="#addDiscount" id="button-discount"><i class="fa fa-lg fa-minus-square"></i> <strong class=""><?php echo $button_discount; ?></strong></button>
            <?php } ?>
            <?php if ($coupon_status) { ?>
            <button class="col-xs-12 btn buttons-sp" data-toggle="modal" data-target="#addCoupon" id="button-coupon"><i class="fa fa-lg fa-ticket"></i> <strong class=""><?php echo $button_coupon; ?></strong></button>
            <?php } ?>
          <?php } ?>
        </div>
        
        <div class="row">
          <div class="col-xs-6">
          <button class="btn btn-block buttons-sp btn-checkout" data-toggle="modal" data-target="#holdOrder" style="background-color: #f1b40f;"><i class="fa fa-lg fa-pause"></i> <strong class=""><?php echo $text_hold_order; ?></strong></button>
          </div>
          <div class="col-xs-6">
          <button class="btn btn-block buttons-sp btn-return" style="background-color: #5bc0de;"><i class="fa fa-lg fa-reply"></i> <strong><?php echo $button_return; ?></strong></button>
          </div>
          <!-- POS Update Code -->
        </div>
        <button class="btn btn-block buttons-sp button-payment btn-checkout"><i class="fa fa-lg fa-money"></i> <strong class=""><?php echo $text_checkout; ?></strong></button>
      </div>
    </div>
    <div id="return-order" class="modal fade" role="dialog" tabindex="-1">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
          <div class="product-option-modal" data-dismiss="modal"><i class="fa fa-lg fa-times"></i></div>
          <div class="modal-header">
            <h4 class="modal-title"></h4>
          </div>
          <div class="modal-body ">
            <div class="form-group">
              <input type="text" id="search-order-id" class="form-control" placeholder="<?php echo $placeholder_order_id; ?>" onkeypress="return isNumber(event);" ondrop="return false;" onpaste="return false;">
            </div>
          </div>
          <div class="table" id="search-order-result">

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-info" data-dismiss="modal"><?php echo $button_close; ?></button>
          </div>
        </div>
      </div>
    </div>
    <div class="pull-right color-white" id="fixed-div">
      <span id="show">
        <button class=" top-small-button" data-toggle="modal" data-target="#barcodeScan" title="<?php echo "Barcode Scan"; ?>" data-toggle="tooltip">
          <i class="fa fa-lg fa-barcode"></i>
        </button>
      </span>
      <span id="hold-carts">
        <button  class=" top-small-button" title="<?php echo $text_view_hold; ?>" data-toggle="tooltip">
          <span class="fa fa-lg fa-pause"></span>
        </button>
        <!--<div class="label label-danger cart-hold fix-label">0</div>-->
      </span>
      <span id="show-cart">
        <button  class=" top-small-button" title="<?php echo $text_cart_items; ?>" data-toggle="tooltip">
          <span class="fa fa-lg fa-shopping-cart"></span>
        </button>
        <!--<div class="label label-danger cart-total fix-label">0</div>-->
      </span>
    </div>

    <div id="customerSearch" class="modal fade" role="dialog" tabindex="-1">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
          <div class="product-option-modal" data-dismiss="modal"><i class="fa fa-lg fa-times"></i></div>
          <div class="modal-header">
            <h4 class="modal-title"><?php echo $text_customer_details; ?></h4>
          </div>
          <div class="modal-body">
            <div class="searchCustomer">
              <input type="text" id="searchCustomer" placeholder="<?php echo $entry_search_customer; ?>" class="form-control" autocomplete="off">
              <div id="putCustomer">
              </div>
            </div>
            <form class="addCustomer form-horizontal hide">

                <?php if ($enable_identity_login): ?>

                <?php $registration_fields = $customer_fields['registration']; ?>

                <?php if((int) $registration_fields['firstname'] > -1): ?>
                <div class="form-group <?php echo (int) $registration_fields['firstname'] === 1 ? 'required' : ''; ?>">
                    <label class="col-sm-2 control-label" for="input-customer-firstname">
                        <?php echo $entry_firstname; ?>
                    </label>
                    <div class="col-sm-10">
                        <input type="text" name="firstname" value="" placeholder="<?php echo $entry_firstname; ?>"
                            id="input-customer-firstname" class="form-control" autocomplete="off" />
                    </div>
                </div>
                <?php endif ?>

                <?php if((int) $registration_fields['email'] > -1): ?>
                <div class="form-group <?php echo (int) $registration_fields['email'] === 1 ? 'required' : ''; ?>">
                    <label class="col-sm-2 control-label" for="input-customer-email">
                        <?php echo $entry_email; ?>
                    </label>
                    <div class="col-sm-10">
                        <input type="email" name="email" value="" placeholder="<?php echo $entry_email; ?>" id="input-customer-email"
                            class="form-control" autocomplete="off" />
                    </div>
                </div>
                <?php endif ?>

                <?php if((int) $registration_fields['telephone'] > -1): ?>
                <div class="form-group <?php echo (int) $registration_fields['telephone'] === 1 ? 'required' : ''; ?>">
                    <label class="col-sm-2 control-label" for="input-customer-telephone">
                        <?php echo $entry_telephone; ?>
                    </label>
                    <div class="col-sm-10">
                        <input type="tel" name="telephone" value="" placeholder="<?php echo $entry_telephone; ?>"
                            id="input-customer-telephone" class="form-control" autocomplete="off" />
                    </div>
                </div>
                <?php endif ?>

                <?php if((int) $registration_fields['gender'] > -1): ?>
                <div class="form-group <?php echo (int) $registration_fields['gender'] === 1 ? 'required' : ''; ?>">
                    <label class="col-sm-2 control-label" for="input-customer-gender">
                        <?php echo $entry_gender; ?>
                    </label>
                    <div class="col-sm-10">
                        <select name="gender" id="input-customer-gender" class="form-control">
                            <option value="" selected disabled>
                                <?php echo $select_none;?>
                            </option>
                            <option value="m">
                                <?php echo $entry_gender_m;?>
                            </option>
                            <option value="f">
                                <?php echo $entry_gender_f;?>
                            </option>
                        </select>
                    </div>
                </div>
                <?php endif ?>

                <?php if((int) $registration_fields['dob'] > -1): ?>
                <div class="form-group <?php echo (int) $registration_fields['dob'] === 1 ? 'required' : ''; ?>">
                    <label class="col-sm-2 control-label" for="input-customer-dob">
                        <?php echo $entry_dob; ?>
                    </label>
                    <div class="col-sm-10">
                        <input type="text" name="dob" format="Y-m-d" data-format="Y-m-d" value=""
                            placeholder="<?php echo $entry_dob; ?>" id="input-customer-dob" class="form-control ar-date"
                            autocomplete="off" />
                    </div>
                </div>
                <?php endif ?>

                <?php if((int) $registration_fields['groups'] > -1 && !empty($customer_groups)): ?>
                <div class="form-group <?php echo (int) $registration_fields['groups'] === 1 ? 'required' : ''; ?>">
                    <label class="col-sm-2 control-label" for="input-customer-customer_group_id">
                        <?php echo $entry_customer_group; ?>
                    </label>
                    <div class="col-sm-10">
                        <select name="customer_group_id" id="input-customer-customer_group_id" class="form-control">
                            <option value="" selected disabled>
                                <?php echo $select_none;?>
                            </option>
                            <?php foreach($customer_groups as $group): ?>
                            <option value="<?php echo $group['customer_group_id'];?>" <?php echo $group['default'] ? 'selected' : '' ;
                                ?>>
                                <?php echo $group['name'];?>
                            </option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>
                <?php endif ?>

                <?php if((int) $registration_fields['company'] > -1): ?>
                <div class="form-group <?php echo (int) $registration_fields['company'] === 1 ? 'required' : ''; ?>">
                    <label class="col-sm-2 control-label" for="input-customer-company">
                        <?php echo $entry_company; ?>
                    </label>
                    <div class="col-sm-10">
                        <input type="text" name="company" value="" placeholder="<?php echo $entry_company; ?>"
                            id="input-customer-company" class="form-control" autocomplete="off" />
                    </div>
                </div>
                <?php endif ?>

                <?php if((int) $registration_fields['newsletter'] > -1): ?>
                <!-- <div class="form-group <?php echo (int) $registration_fields['newsletter'] === 1 ? 'required' : ''; ?>">
                                <label class="col-sm-2 control-label" for="input-customer-newsletter"><?php echo $entry_newsletter; ?></label>
                                <div class="col-sm-10">
                                <input type="checkbox" name="newsletter" value="1" placeholder="<?php echo $entry_newsletter; ?>" id="input-customer-newsletter" class="checkbox" autocomplete="off" />
                                </div>
                            </div> -->
                <?php endif ?>

                <legend>
                    <?php echo $text_address; ?>
                </legend>

                <?php $address_fields = $customer_fields['address'];?>

                <?php if((int) $address_fields['country_id'] > -1): ?>
                <div class="form-group <?php echo (int) $address_fields['country_id'] === 1 ? 'required' : ''; ?>">
                    <label class="col-sm-2 control-label" for="input-country">
                        <?php echo $entry_country; ?>
                    </label>
                    <div class="col-sm-10">
                        <select name="country_id" id="input-country" class="form-control" autocomplete="off">
                            <option value="" selected disabled>
                                <?php echo $text_select; ?>
                            </option>
                            <?php foreach ($countries as $country) { ?>
                            <option value="<?php echo $country['country_id']; ?>" data-code="<?php echo $country['iso_code_2']; ?>">
                                <?php echo $country['name']; ?>
                            </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <?php endif ?>

                <?php if((int) $address_fields['zone_id'] > -1): ?>
                <div class="form-group <?php echo (int) $address_fields['zone_id'] === 1 ? 'required' : ''; ?>">
                    <label class="col-sm-2 control-label" for="input-zone">
                        <?php echo $entry_zone; ?>
                    </label>
                    <div class="col-sm-10">
                        <select name="zone_id" id="input-zone" class="form-control" autocomplete="off">
                            <option value="" selected disabled>
                                <?php echo $text_select; ?>
                            </option>
                            <!-- Ajax filled -->
                        </select>
                    </div>
                </div>
                <?php endif ?>

                <?php if((int) $address_fields['area_id'] > -1): ?>
                <div class="form-group <?php echo (int) $address_fields['area_id'] === 1 ? 'required' : ''; ?>">
                    <label class="col-sm-2 control-label" for="input-area">
                        <?php echo $entry_area; ?>
                    </label>
                    <div class="col-sm-10">
                        <select name="area_id" id="input-area" class="form-control" autocomplete="off">
                            <option value="" selected disabled>
                                <?php echo $text_select; ?>
                            </option>
                            <!-- Ajax filled -->
                        </select>
                    </div>
                </div>
                <?php endif ?>

                <?php if((int) $address_fields['address_1'] > -1): ?>
                <div class="form-group <?php echo (int) $address_fields['address_1'] === 1 ? 'required' : ''; ?>">
                    <label class="col-sm-2 control-label" for="input-address_1">
                        <?php echo $entry_address_1; ?>
                    </label>
                    <div class="col-sm-10">
                        <input type="text" name="address_1" value="" placeholder="<?php echo $entry_address_1; ?>" id="input-address_1"
                            class="form-control" autocomplete="off" />
                    </div>
                </div>
                <?php endif ?>

                <?php if((int) $address_fields['address_2'] > -1): ?>
                <div class="form-group <?php echo (int) $address_fields['address_2'] === 1 ? 'required' : ''; ?>">
                    <label class="col-sm-2 control-label" for="input-address_2">
                        <?php echo $entry_address_2; ?>
                    </label>
                    <div class="col-sm-10">
                        <input type="text" name="address_2" value="" placeholder="<?php echo $entry_address_2; ?>" id="input-address_2"
                            class="form-control" autocomplete="off" />
                    </div>
                </div>
                <?php endif ?>

                <?php if((int) $address_fields['postcode'] > -1): ?>
                <div class="form-group <?php echo (int) $address_fields['postcode'] === 1 ? 'required' : ''; ?>">
                    <label class="col-sm-2 control-label" for="input-postcode">
                        <?php echo $entry_postcode; ?>
                    </label>
                    <div class="col-sm-10">
                        <input type="text" name="postcode" value="" placeholder="<?php echo $entry_postcode; ?>" id="input-postcode"
                            class="form-control" autocomplete="off" />
                    </div>
                </div>
                <?php endif ?>

                <?php if((int) $address_fields['telephone'] > -1): ?>
                <div class="form-group <?php echo (int) $address_fields['telephone'] === 1 ? 'required' : ''; ?>">
                    <label class="col-sm-2 control-label" for="input-shipping_telephone">
                        <?php echo $entry_shipping_telephone; ?>
                    </label>
                    <div class="col-sm-10">
                        <input type="tel" name="shipping_telephone" value="" placeholder="<?php echo $entry_shipping_telephone; ?>"
                            id="input-shipping_telephone" class="form-control" autocomplete="off" />
                    </div>
                </div>
                <?php endif ?>
                <?php else: ?>
                <!-- default form fields -->
                <div class="form-group required">
                    <label class="col-sm-2 control-label" for="input-customer-firstname">
                        <?php echo $entry_firstname; ?>
                    </label>
                    <div class="col-sm-10">
                        <input type="text" name="firstname" value="" placeholder="<?php echo $entry_firstname; ?>"
                            id="input-customer-firstname" class="form-control" autocomplete="off" />
                    </div>
                </div>
                <div class="form-group required">
                    <label class="col-sm-2 control-label" for="input-customer-lastname">
                        <?php echo $entry_lastname; ?>
                    </label>
                    <div class="col-sm-10">
                        <input type="text" name="lastname" value="" placeholder="<?php echo $entry_lastname; ?>"
                            id="input-customer-lastname" class="form-control" autocomplete="off" />
                    </div>
                </div>
                <div class="form-group required">
                    <label class="col-sm-2 control-label" for="input-email">
                        <?php echo $entry_email; ?>
                    </label>
                    <div class="col-sm-10">
                        <input type="email" name="email" value="" placeholder="<?php echo $entry_email; ?>" id="input-email"
                            class="form-control" autocomplete="off" />
                    </div>
                </div>
                <div class="form-group required">
                    <label class="col-sm-2 control-label" for="input-telephone">
                        <?php echo $entry_telephone; ?>
                    </label>
                    <div class="col-sm-10">
                        <input type="tel" name="telephone" value="" placeholder="<?php echo $entry_telephone; ?>" id="input-telephone"
                            class="form-control" autocomplete="off" />
                    </div>
                </div>
                <legend>
                    <?php echo $text_address; ?>
                </legend>
                <div class="form-group required">
                    <label class="col-sm-2 control-label" for="input-address-1">
                        <?php echo $entry_address_1; ?>
                    </label>
                    <div class="col-sm-10">
                        <input type="text" name="address_1" value="" placeholder="<?php echo $entry_address_1; ?>" id="input-address-1"
                            class="form-control" autocomplete="off" />
                    </div>
                </div>
                <div class="form-group required">
                    <label class="col-sm-2 control-label" for="input-city">
                        <?php echo $entry_city; ?>
                    </label>
                    <div class="col-sm-10">
                        <input type="text" name="city" value="" placeholder="<?php echo $entry_city; ?>" id="input-city"
                            class="form-control" autocomplete="off" />
                    </div>
                </div>
                <div class="form-group required">
                    <label class="col-sm-2 control-label" for="input-postcode">
                        <?php echo $entry_postcode; ?>
                    </label>
                    <div class="col-sm-10">
                        <input type="text" name="postcode" value="" placeholder="<?php echo $entry_postcode; ?>" id="input-postcode"
                            class="form-control" autocomplete="off" />
                    </div>
                </div>
                <div class="form-group required">
                    <label class="col-sm-2 control-label" for="input-country">
                        <?php echo $entry_country; ?>
                    </label>
                    <div class="col-sm-10">
                        <select name="country_id" id="input-country" class="form-control" autocomplete="off">
                            <option value="">
                                <?php echo $text_select; ?>
                            </option>
                            <?php foreach ($countries as $country) { ?>
                            <option value="<?php echo $country['country_id']; ?>">
                                <?php echo $country['name']; ?>
                            </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="form-group required">
                    <label class="col-sm-2 control-label" for="input-zone">
                        <?php echo $entry_zone; ?>
                    </label>
                    <div class="col-sm-10">
                        <select name="zone_id" id="input-zone" class="form-control" autocomplete="off">
                        </select>
                    </div>
                <?php endif ?>

              <div class="text-center">
                <button class="btn btn-default" type="button" onclick="registerCustomer($(this));"><strong><?php echo $text_register; ?></strong></button>
                <button class="btn btn-default" type="button" onclick="registerCustomer($(this), true);"><strong><?php echo $text_register_select; ?></strong></button>
              </div>
            </form> 
            <!-- end addCustomer -->
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-success pull-left" id="addCustomer"><?php echo $text_add_customer; ?></button>
            <button type="button" class="btn btn-danger pull-left" id="removeCustomer"><?php echo $text_remove; ?></button>
            <button type="button" class="btn btn-info" data-dismiss="modal"><?php echo $button_close; ?></button>
          </div>
        </div>
      </div>
    </div></div>

    <span data-toggle="modal" data-target="#productOptions" id="buttonModal"></span>
    <span data-toggle="modal" data-target="#productDetails" id="buttonProductDetails"></span>
    <div id="productDetails" class="modal fade" role="dialog" tabindex="-1">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
          <div class="product-option-modal" data-dismiss="modal"><i class="fa fa-lg fa-times"></i></div>
          <div class="modal-header">
            <h4 class="modal-title"><?php echo $heading_product_detail; ?></h4>
          </div>
          <div class="modal-body ">
            <img src="" class="img-responsive" style="margin: 0 auto" id="detail-image">
            <div class="table-responsive">
              <table class="table table-hover margin-bottom-0">
                <tbody>
                  <tr>
                    <td class="text-right"><?php echo $entry_name; ?></td>
                    <td class="text-left" id="productName"></td>
                  </tr>
                  <tr>
                    <td class="text-right"><?php echo $entry_price; ?></td>
                    <td class="text-left" id="productPrice"></td>
                  </tr>
                  <tr>
                    <td class="text-right"><?php echo $entry_item_left; ?></td>
                    <td class="text-left" id="productItem"></td>
                  </tr>
                  <tr id="supplier-info">
                    <td class="text-right"><?php echo $entry_suppliers; ?>:</td>
                    <td class="text-left" id="productSuppliers"></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-info" data-dismiss="modal"><?php echo $button_close; ?></button>
          </div>
        </div>
      </div>
    </div>
    <div id="categoryList" class="modal fade" role="dialog" tabindex="-1">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
          <div class="product-option-modal" data-dismiss="modal"><i class="fa fa-lg fa-times"></i></div>
          <div class="modal-header">
            <h4 class="modal-title"><?php echo $heading_category; ?></h4>
          </div>
          <div class="modal-body ">
            <button class="btn btn-default" onclick="showAllProducts()"><?php echo $button_show_product; ?></button>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-info" data-dismiss="modal"><?php echo $button_close; ?></button>
          </div>
        </div>
      </div>
    </div>
    <div id="barcodeScan" class="modal fade" role="dialog" tabindex="-1">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
          <div class="product-option-modal" data-dismiss="modal"><i class="fa fa-lg fa-times"></i></div>
          <div class="modal-header">
            <h4 class="modal-title"><?php echo $heading_scan_barcode; ?></h4>
          </div>
          <div class="modal-body ">
            <input type="text" id="bar-code" placeholder="<?php echo $entry_barcode; ?>" class="form-control">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-info" data-dismiss="modal"><?php echo $button_close; ?></button>
          </div>
        </div>
      </div>
    </div>
    <div id="updatePrice" class="modal fade" role="dialog" tabindex="-1">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
          <div class="product-option-modal" data-dismiss="modal"><i class="fa fa-lg fa-times"></i></div>
          <div class="modal-header">
            <h4 class="modal-title"><?php echo $heading_price_update; ?></h4>
          </div>
          <div class="modal-body ">
            <div class="form-group">
              <label id="update-label"></label>
              <input type="text" id="update-price" class="form-control" placeholder="<?php echo $entry_updated_price; ?>" onkeypress="return validate(event, this)">
              <input type="hidden" id="cart-index">
            </div>
            <div class="col-sm-12 text-center" style="float: none;">
              <button type="button" class="btn buttons-sp" id="updatePricebtn"><?php echo $button_apply; ?></button>
              <button type="button" class="btn buttons-sp" id="cancelPriceUp" style="background-color: #ff6666;"><?php echo $button_remove; ?></button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div id="addProduct" class="modal fade" role="dialog" tabindex="-1">
      <div class="modal-dialog modal-md">
        <div class="modal-content">
          <div class="product-option-modal" data-dismiss="modal"><i class="fa fa-lg fa-times"></i></div>
          <div class="modal-header">
            <h4 class="modal-title"><?php echo $heading_add_service; ?></h4>
          </div>
          <div class="modal-body  form-horizontal">
            <div class="form-group">
              <label class="col-sm-3 control-label"><?php echo $entry_service_name; ?></label>
              <div class="col-sm-9">
                <input type="text" name="product_name" class="form-control" placeholder="<?php echo $entry_service_name; ?>">
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label"><?php echo $entry_service_price; ?></label>
              <div class="col-sm-9">
                <input type="text" name="product_price" class="form-control" placeholder="<?php echo $entry_service_price; ?>" onkeypress="return validate(event, this)">
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label"><?php echo $entry_service_quantity; ?></label>
              <div class="col-sm-9">
                <input type="number" name="product_quantity" class="form-control" placeholder="<?php echo $entry_service_quantity; ?>" onkeypress="return validate(event, this, true)">
              </div>
            </div>
          </div>
          <div class="modal-footer ">
            <div class="col-sm-12 text-center" style="float: none;">
              <button type="button" class="btn buttons-sp" id="addProductbtn"><?php echo $button_add; ?></button>
              <button type="button" class="btn buttons-sp" data-dismiss="modal" style="background-color: #ff6666;"><?php echo $button_cancel; ?></button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="modal" id="loader">
      <div class="cp-spinner cp-eclipse"></div>
      <div class="progress"><div class="progress-bar progress-bar-dan"></div></div>
      <br>  
      <div id="loading-text"></div>
      <div id="error-text"></div>
    </div>
    <div class="posProductBlock"><img src="<?php echo $no_image; ?>" width="50" height="50"></div>
    <div id="screen-div" onclick="toggleFullScreen();" title="Toggle Screen" data-toggle="tooltip">
      <img src="<?php echo $screen_image; ?>">
    </div>
  </div>
  <div class="hide" id="printBill">
    <div class="container thermal" style="text-align: center; max-width: 630px;">
      <div style="margin: 0 auto;" class="thermal">
        <?php if ($show_store_logo) { ?>
        <img src="<?php echo $store_logo; ?>" title="<?php echo $store_name; ?>" alt="<?php echo $store_name; ?>" class="img-responsive" width="200" style="margin: 0 auto;"><br>
        <?php } ?>
        <?php if ($show_store_name) { ?>
        <span style="font-size: 15px; font-weight: bold;"><?php echo $localizedStoreName; ?></span><br>
        <?php } ?>
        <?php if ($show_store_address) { ?>
        <span style="font-size: 10px;"><?php echo $store_address; ?></span><br>
        <?php } ?>
        <?php if ($store_detail) { ?>
        <span style="font-size: 10px;"><?php echo $store_detail; ?></span><br>
        <?php } ?>
        <?php if ($show_order_date) { ?>
        <?php echo $text_date; ?>: <span class="order-date"></span>
        <?php } ?>
        <?php if ($show_order_time) { ?>
        <?php echo $text_time; ?>: <span class="order-time"></span><br>
        <?php } ?>
        <?php if ($show_order_id) { ?>
        <span class="order-txn"><?php echo $text_order_id; ?></span> #<span class="oid"></span>
        <?php } ?>
        <?php if ($show_cashier_name) { ?>
        <?php echo $text_cashier; ?>: <span id="cashier-name"></span>
        <?php } ?>
        <?php if ($show_customer_name) { ?>
        <br />
        <?php echo $text_customer; ?>: <span id="customer-name-in"></span>
        <?php } ?>
      </div>
      <br>
      <div class="thermal">
        <?php if ($show_shipping_mode) { ?>
        <span><?php echo $text_shipping_mode; ?>: </span><span class="oshipping">
        <?php if ($home_delivery_status) { ?>
          <?php echo $home_delivery_title; ?>
        <?php } else { ?>
          <?php echo $text_pickup; ?>
        <?php } ?>
        </span><br>
        <?php } ?>
        <?php if ($show_payment_mode) { ?>
        <span><?php echo $text_payment_mode; ?>: </span><span class="opayment"></span>
        <?php } ?>
      </div>
      <div class="table thermal">
        <table style="border-collapse: separate; border-spacing: 5px; margin: 0 auto; width: 100%;">
          <thead>
            <tr>
              <td style="text-align: left"><?php echo $column_product; ?></td>
              <td><?php echo $column_quantity; ?></td>
              <td><?php echo $column_price; ?></td>
              <td style="text-align: right;"><?php echo $column_amount; ?></td>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td style="text-align:left;">-------------<?php if ($paper_size == '80mm') { echo '-------'; } ?></td>
              <td>-----<?php if ($paper_size == '80mm') { echo '----' ; } ?></td>
              <td>-------------</td>
              <td style="text-align:right;">--------------</td>
            </tr>
          </tbody>
          <tbody id="receiptProducts">
          </tbody>
          <tbody>
            <tr>
              <td colspan="4">----------------------------------------------------<?php if ($paper_size == '80mm') { echo '------------------'; } ?><?php if($paper_size == '595px') { echo '-------------------------------------------------------------------------------------------'; } ?></td>
            </tr>
          </tbody>
          <tbody id="print-totals">
            <tr>
              <td></td>
              <td></td>
              <td><?php echo $text_subtotal; ?></td>
              <td style="text-align: right;">00.00</td>
            </tr>
          </tbody>
          <tbody>
            <tr>
              <td id="total-quantity-text"></td>
              <td id="total-quantity" style="font-weight: bold;"></td>
              <td><?php echo $text_grandtotal; ?></td>
              <td style="text-align: right;"><b class="oTotal">00.00</b></td>
            </tr>
          </tbody>
        </table>
        <?php if ($show_note) { ?>
        <div style="text-align: left;">
          <strong><?php echo $text_note; ?>: </strong><span class="onote"></span>
        </div>
        <?php } ?>
        <span><?php echo $text_nice_day; ?></span><br>
        <span><?php echo $text_thank_you; ?></span>
        <div style="text-align:center;" id="qr_code"></div>
      </div>
    </div>
  </div>
  <noscript>
    <div class="no-js"><?php echo $text_no_js; ?></div>
  </noscript>
  <div id="addDiscount" class="modal fade" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-md">
      <div class="modal-content">
        <div class="product-option-modal" data-dismiss="modal"><i class="fa fa-lg fa-times"></i></div>
        <div class="modal-header">
          <h4 class="modal-title"><?php echo $heading_discount; ?></h4>
        </div>
        <div class="modal-body ">
          <div class="row">
            <div class="col-sm-3 text-center">
              <strong>Fixed</strong>
              <div class="input-group input-group2">
                <?php if ($symbol_position == 'L') { ?>
                <span class="input-group-addon currency"><strong><?php echo $currency_code; ?></strong></span>
                <?php } else { ?>
                <span class="input-group-addon currency" style="display: none;"><strong><?php echo $currency_code; ?></strong></span>
                <?php } ?>
                <input type="text" id="fixedDisc" value="0" placeholder="<?php echo $entry_fixed; ?>" class="form-control" onkeypress="return validate(event, this)">
                <?php if ($symbol_position == 'R') { ?>
                <span class="input-group-addon currency"><strong><?php echo $currency_code; ?></strong></span>
                <?php } else { ?>
                <span class="input-group-addon currency" style="display: none;"><strong><?php echo $currency_code; ?></strong></span>
                <?php } ?>
              </div>
            </div>
            <div class="col-sm-1 text-center" style="margin: 28px 0;">
              <i class="fa fa-lg fa-plus"></i>
            </div>
            <div class="col-sm-3 text-center">
              <strong><?php echo $entry_percent; ?></strong>
              <div class="input-group">
                <input type="text" id="percentDisc" value="0" placeholder="<?php echo $entry_percent; ?>" class="form-control" onkeypress="return validate(event, this)" style="border-radius: 0;">
                <span class="input-group-addon"><strong>%</strong></span>
              </div>
            </div>
            <div class="col-sm-1 text-center" style="margin: 28px 0; font-size: 20px;">
              <strong>=</strong>
            </div>
            <div class="col-sm-4 text-center">
              <strong><?php echo $text_total; ?></strong>
              <div class="input-group input-group3" style="margin-top: 3px;">
                <?php if ($symbol_position == 'L') { ?>
                <span class="input-group-addon currency"><strong><?php echo $currency_code; ?></strong></span>
                <?php } else { ?>
                <span class="input-group-addon currency" style="display: none;"><strong><?php echo $currency_code; ?></strong></span>
                <?php } ?>
                <span class="input-group-addon" style="max-width: 140px; overflow: hidden;"><strong id="total-discount">0.00</strong></span>
                <?php if ($symbol_position == 'R') { ?>
                <span class="input-group-addon currency"><strong><?php echo $currency_code; ?></strong></span>
                <?php } else { ?>
                <span class="input-group-addon currency" style="display: none;"><strong><?php echo $currency_code; ?></strong></span>
                <?php } ?>
              </div>
            </div>
            <div class="col-sm-12">
              <label class="col-sm-3" for="input-discname"><?php echo $entry_discount; ?></label>
              <div class="col-sm-9">
                <input type="text" class="form-control" id="input-discname" placeholder="<?php echo $entry_discount; ?>" value="<?php echo $button_discount; ?>">
              </div>
            </div>
          </div>
          <div class="col-sm-12 text-center" style="float: none;">
            <button type="button" class="btn buttons-sp" id="addDiscountbtn"><?php echo $button_apply; ?></button>
            <button type="button" class="btn buttons-sp" id="removeDiscount" style="background-color: #ff6666;"><?php echo $button_remove; ?></button>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div id="addCoupon" class="modal fade" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="product-option-modal" data-dismiss="modal"><i class="fa fa-lg fa-times"></i></div>
        <div class="modal-header">
          <h4 class="modal-title"><?php echo $heading_coupon; ?></h4>
        </div>
        <div class="modal-body ">
          <div class="form-group">
            <input type="text" id="coupon-code" class="form-control" placeholder="<?php echo $entry_coupon; ?>">
          </div>
          <div class="col-sm-12 text-center" style="float: none;">
            <button type="button" class="btn buttons-sp" id="addCouponbtn"><?php echo $button_apply; ?></button>
            <button type="button" class="btn buttons-sp" id="removeCoupon" style="background-color: #ff6666;"><?php echo $button_remove; ?></button>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div id="holdOrder" class="modal fade" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="product-option-modal" data-dismiss="modal"><i class="fa fa-lg fa-times"></i></div>
        <div class="modal-header">
          <h4 class="modal-title"><?php echo $heading_hold_note; ?></h4>
        </div>
        <div class="modal-body">
          <textarea class="form-control" placeholder="<?php echo $entry_hold_note; ?>" id="holdNote" rows="4"></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success pull-left" onclick="holdOrder();"><?php echo $button_hold_order; ?></button>
          <button type="button" class="btn btn-info" data-dismiss="modal"><?php echo $button_cancel; ?></button>
        </div>
      </div>
    </div>
  </div>
  <div id="productOptions" class="modal fade" role="dialog" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="product-option-modal" data-dismiss="modal"><i class="fa fa-lg fa-times"></i></div>
        <div class="modal-header">
          <h4 class="modal-title" id="global-modal-title"><?php echo $text_product_options; ?></h4>
        </div>
        <div class="modal-body " id="posProductOptions">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-info" data-dismiss="modal"><?php echo $button_close; ?></button>
        </div>
      </div>
    </div>
  </div>
</body>

<script language="JavaScript">
var direction = '<?php echo $direction; ?>';
  window.onbeforeunload = confirmExit;
  function confirmExit() {
    return "<?php echo $text_close_warning; ?>";
  }
</script>
<script type="text/javascript">
var currency              = "<?php echo $currency; ?>",
currency_code             = "<?php echo $currency_code; ?>",
symbol_position           = "<?php echo $symbol_position; ?>",
user_login                = "<?php echo $user_login; ?>",
tax_status                = "<?php echo $tax_status; ?>",
message_error_credentials = "<?php echo $error_credentials; ?>",
entry_price               = "",
text_loading_products     = "<?php echo $text_loading_products; ?>",
error_load_products       = "<?php echo $error_load_products; ?>",
text_loading_populars     = "<?php echo $text_loading_populars; ?>",
error_load_populars       = "<?php echo $error_load_populars; ?>",
text_loading_orders       = "<?php echo $text_loading_orders; ?>",
error_load_orders         = "<?php echo $error_load_orders; ?>",
text_loading_customers    = "<?php echo $text_loading_customers; ?>",
error_load_customers      = "<?php echo $error_load_customers; ?>",
text_loading_categories   = "<?php echo $text_loading_categories; ?>",
error_load_categories     = "<?php echo $error_load_categories; ?>",
text_select               = "<?php echo $text_select; ?>",
text_none                 = "<?php echo $text_none; ?>",
text_loading              = "<?php echo $text_loading; ?>",
button_upload             = "<?php echo $button_upload; ?>",
button_cart               = "<?php echo $button_cart; ?>",
text_product_options      = "<?php echo $text_product_options; ?>",
error_keyword             = "<?php echo $error_keyword; ?>",
error_products            = "<?php echo $error_products; ?>",
text_online_mode          = "<?php echo $text_online_mode; ?>",
text_online               = "<?php echo $text_online; ?>",
error_enter_online        = "<?php echo $error_enter_online; ?>",
text_offline_mode         = "<?php echo $text_offline_mode; ?>",
text_offline              = "<?php echo $text_offline; ?>",
error_no_category_product = "<?php echo $error_no_category_product; ?>",
error_no_customer         = "<?php echo $error_no_customer; ?>",
text_select_customer      = "<?php echo $text_select_customer; ?>",
error_customer_add        = "<?php echo $error_add_customer; ?>",
text_remove_customer      = "<?php echo $text_remove_customer; ?>",
error_checkout            = "<?php echo $error_checkout; ?>",
text_balance_due          = "<?php echo $text_balance_due; ?>",
text_order_success        = "<?php echo $text_order_success; ?>",
text_customer_select      = "<?php echo $text_customer_select; ?>",
guest_name                = "<?php echo $guest_name; ?>",
text_item_detail          = "<?php echo $text_item_details; ?>",
text_sync_order           = "<?php echo $text_sync_orders; ?>",
text_no_orders            = "<?php echo $text_no_orders; ?>",
error_sync_orders         = "<?php echo $error_sync_orders; ?>",
text_another_cart         = "<?php echo $text_select_cart; ?>",
text_cart_deleted         = "<?php echo $text_cart_deleted; ?>",
text_current_deleted      = "<?php echo $text_current_deleted; ?>",
text_cart_empty           = "<?php echo $text_cart_empty; ?>",
text_empty_cart           = "<?php echo $text_empty_cart; ?>",
text_cart_add             = "<?php echo $text_cart_add; ?>",
text_option_required      = "<?php echo $text_option_required; ?>",
text_no_quantity          = "<?php echo $text_no_quantity; ?>",
text_product_added        = "<?php echo $text_product_added; ?>",
text_product_not_added    = "<?php echo $text_product_not_added; ?>",
text_product_removed      = "<?php echo $text_product_removed; ?>",
cash_payment_title        = "<?php echo $cash_payment_title; ?>",
text_order_id             = "<?php echo $text_order_id; ?>",
text_all_products         = "<?php echo $text_all_products; ?>",
text_search               = "<?php echo $text_search; ?>",
text_option_notifier      = "<?php echo $text_option_notifier; ?>",
show_lowstock_prod        = "<?php echo $show_lowstock_prod; ?>",
low_stock                 = "<?php echo $low_stock; ?>",
text_low_stock            = "<?php echo $text_low_stock; ?>",
text_special_price        = "<?php echo $text_special_price; ?>",
text_empty_hold           = "<?php echo $text_empty_hold; ?>",
text_success_logout       = "<?php echo $text_success_logout; ?>",
text_cust_add_select      = "<?php echo $text_cust_add_select; ?>",
text_register             = "<?php echo $text_register; ?>",
text_register_select      = "<?php echo $text_register_select; ?>",
error_save_setting        = "<?php echo $error_save_setting; ?>",
base_pos                  = "<?php echo $base_pos; ?>",
text_tendered_confirm     = "<?php echo $text_tendered_confirm; ?>",
text_card_confirm         = "<?php echo $text_card_confirm; ?>",
text_product_success      = "<?php echo $text_product_success; ?>",
error_product_name        = "<?php echo $error_product_name; ?>",
error_product_price       = "<?php echo $error_product_price; ?>",
error_product_quant       = "<?php echo $error_product_quant; ?>",
error_request_offline     = "<?php echo $error_request_offline; ?>",
error_supplier_range      = "<?php echo $error_supplier_range; ?>",
text_no_quantity_added    = "<?php echo $text_no_quantity_added; ?>",
error_select_product      = "<?php echo $error_select_product; ?>",
text_sure                 = "<?php echo $text_sure; ?>",
text_success_price_up     = "<?php echo $text_success_price_up; ?>",
text_price_remove         = "<?php echo $text_price_remove; ?>",
text_success              = "<?php echo $text_success; ?>",
text_warning              = "<?php echo $text_warning; ?>",
error_warning             = "<?php echo $error_warning; ?>",
text_coupon_remove        = "<?php echo $text_coupon_remove; ?>",
text_no_product           = "<?php echo $text_no_product; ?>",
text_success_add_disc     = "<?php echo $text_success_add_disc; ?>",
text_success_rem_disc     = "<?php echo $text_success_rem_disc; ?>",
error_cart_discount       = "<?php echo $error_cart_discount; ?>",
error_coupon_offline      = "<?php echo $error_coupon_offline; ?>",
error_supplier            = "<?php echo $error_supplier; ?>",
error_no_supplier         = "<?php echo $error_no_supplier; ?>",
heading_price_update      = "<?php echo $heading_price_update; ?>",
heading_quantity_update   = "<?php echo $heading_quantity_update; ?>",
button_product_info       = "<?php echo $button_product_info; ?>",
error_mobile_view         = "<?php echo $error_mobile_view; ?>",
cashier                   = "<?php echo $name; ?>",
error_script              = "<?php echo $error_script; ?>",
error_price               = "<?php echo $error_price; ?>",
error_quantity            = "<?php echo $error_quantity; ?>",
error_option_date         = "<?php echo $error_option_date; ?>",
// POS Update Code
home_delivery_title       = "<?php echo $home_delivery_title; ?>",
text_credit_applied       = "<?php echo $text_credit_applied; ?>",
text_credit_removed       = "<?php echo $text_credit_removed; ?>",
text_pickup               = "<?php echo $text_pickup; ?>",
credit_status             = "<?php echo $credit_status; ?>",
heading_return            = "<?php echo $heading_return; ?>",
delivery_max              = "<?php echo $delivery_max; ?>",
text_product_info         = "<?php echo $text_product_info; ?>",
text_order_info           = "<?php echo $text_order_info; ?>",
text_loading_returns      = "<?php echo $text_loading_returns; ?>",
text_total                = "<?php echo $text_total; ?>",
text_return_details       = "<?php echo $text_return_details; ?>",
text_product_detail       = "<?php echo $heading_product_detail; ?>",
text_return_id            = "<?php echo $text_return_id; ?>",
text_no                   = "<?php echo $text_no; ?>",
text_yes                  = "<?php echo $text_yes; ?>",
text_order_date           = "<?php echo $text_order_date; ?>",
text_no_credit            = "<?php echo $text_no_credit; ?>",
text_return_date          = "<?php echo $text_return_date; ?>",
text_return_status        = "<?php echo $text_return_status; ?>",
text_comment              = "<?php echo $text_comment; ?>",
text_return_action        = "<?php echo $text_return_action; ?>",
text_no_order             = "<?php echo $text_no_order; ?>",
pricelist_status          = "<?php echo $pricelist_status; ?>",
text_price_changed        = "<?php echo $text_price_changed; ?>",
select_customer_first     = "<?php echo $select_customer_first; ?>",
button_credit             = "<?php echo $button_credit; ?>",
entry_date_ordered        = "<?php echo $entry_date_ordered; ?>",
entry_model               = "<?php echo $entry_model; ?>",
entry_quantity            = "<?php echo $entry_quantity; ?>",
entry_reason              = "<?php echo $entry_reason; ?>",
entry_action              = "<?php echo $entry_action; ?>",
entry_opened              = "<?php echo $entry_opened; ?>",
entry_fault_detail        = "<?php echo $entry_fault_detail; ?>",
entry_name                = "<?php echo $entry_name; ?>",
entry_customer            = "<?php echo $entry_customer; ?>",
entry_firstname           = "<?php echo $entry_firstname; ?>",
entry_lastname            = "<?php echo $entry_lastname; ?>",
entry_status              = "<?php echo $entry_status; ?>",
entry_telephone           = "<?php echo $entry_telephone; ?>",
entry_email               = "<?php echo $entry_email; ?>",
entry_opened1             = "<?php echo $entry_opened1; ?>",

button_exchange           = "<?php echo $button_exchange; ?>",
button_return             = "<?php echo $button_return; ?>",
button_close              = "<?php echo $button_close; ?>",
button_submit             = "<?php echo $button_submit; ?>",
button_remove_credit      = "<?php echo $button_remove_credit; ?>",
return_heading            = "<?php echo $return_heading; ?>",

error_return_offline      = "<?php echo $error_return_offline; ?>",
error_check_qty           = "<?php echo $error_check_qty; ?>",
error_delivery_max        = "<?php echo $error_delivery_max; ?>",
error_return_product      = "<?php echo $error_return_product; ?>",
error_exchange_product    = "<?php echo $error_exchange_product; ?>",
error_load_returns        = "<?php echo $error_load_returns; ?>",
error_quantity_exceed     = "<?php echo $error_quantity_exceed; ?>",
error_credit_offline      = "<?php echo $error_credit_offline; ?>",
error_credit_amount       = "<?php echo $error_credit_amount; ?>",
error_offline_in_credit   = "<?php echo $error_offline_credit; ?>",
error_mobile_view_return  = "<?php echo $error_mobile_return; ?>",
error_offline_file        = "<?php echo $error_offline_file; ?>";
entry_product_name        = "<?php echo $entry_product_name; ?>";
column_quantity           = "<?php echo $column_quantity; ?>";
text_supplier             = "<?php echo $text_supplier; ?>";
text_comment              = "<?php echo $text_comment; ?>";
text_no_requests          = "<?php echo $text_no_requests; ?>";
text_date                 = "<?php echo $text_date; ?>";
text_order_details        = "<?php echo $text_order_details; ?>";
text_expense_success_message = "<?php echo $text_expense_success_message; ?>";
text_expenses_title = "<?php echo $text_expenses_title; ?>";
text_expenses_desc = "<?php echo $text_expenses_desc; ?>";
text_expenses_amount = "<?php echo $text_expenses_amount; ?>";
text_id = "<?php echo $text_id; ?>";
text_date = "<?php echo $text_date; ?>";
text_loading_expenses = "<?php echo $text_loading_expenses; ?>";
error_expenses = "<?php echo $error_expenses; ?>";
</script>
</html>
