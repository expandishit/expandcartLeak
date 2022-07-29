
function limitOrderFromOneSeller(json){

    json['error']='';
    $('head').append("<style type='text/css'>.customAddtoCart .ui-dialog-titlebar.ui-widget-header.ui-corner-all.ui-helper-clearfix {display: none;} .ui-dialog{border-radius: 22px; top: 30% !important; background-color: #fff !important;  margin: 30px auto;} .ui-dialog.ui-corner-all.ui-widget.ui-widget-content.ui-front.ui-dialog-osx.customAddtoCart.ui-dialog-buttons {width: 600px !important;}  .cancelbtn{background: transparent !important;color: red !important;border: 1px solid red !important; width: 15% !important;}  .customAddtoCart div#add-to-cart-dialog {min-height: 1px !important; padding: 30px 40px !important; background-color: #fff !important;  } .ui-dialog .ui-dialog-buttonpane{ background-color: #fff !important;  border-top: 0 !important;} .ui-dialog .ui-dialog-buttonpane .ui-dialog-buttonset {  text-align: center; float: inherit !important;}[dir=rtl] .ui-widget {font-family: 'Droid Arabic Kufi', 'droid_serifregular' !important;}</style>");

    $('body').append('<div id="add-to-cart-dialog" style="display:none;"><div style="margin: 13px 0;">' + json['text_limit_order_from_one_Seller'] + '</div></div>');
    $('body').addClass('stop-scrolling');

    $("#add-to-cart-dialog").dialog({
        modal: true,
        draggable: false,
        resizable: false,
        position: { my: "center", at: "center", of: window },
        show: 'blind',
        hide: 'blind',
        width: 500,
        dialogClass: 'ui-dialog-osx customAddtoCart',
        buttons: [{
            text:json['btn_cancel_limit_order'],
            'class': 'dialog-btn-size cancelbtn', 
            click: function() {
                $(this).dialog("close");
            }
        },
            {
                text: json['btn_new_order_limit_order'],
                'class': 'dialog-btn-size',
                'id': 'button-newOrder',
                  click: function() {
                  addNewOrderToCart();
                   $(this).dialog("close");
            }

            }
        ],
        close: function() {
            $('body').removeClass('stop-scrolling');
        }

    });
}
function addNewOrderToCart() {
    $("<input />").attr("type", "hidden").attr("name", "new_order_from_new_seller") .attr("value", '1').appendTo(".product-add-form");
   $.ajax({
       url: 'index.php?route=checkout/cart/add',
       type: 'post',
       data: $('.product-add-form input[type=\'text\'], .product-add-form input[type=\'hidden\'], .product-add-form input[type=\'radio\']:checked, .product-add-form input[type=\'checkbox\']:checked, .product-add-form select, .product-add-form textarea'),
       dataType: 'json',
       success: function (json) {
           $('.alert-success, .alert-warning, .alert-attention, .alert-information').remove();

          if (json['success']) {
               $('#notification').html('<br><div class="alert alert-success alert-dismissible" style="display: ' +
                   'none;' +
                   '"' +
                      ' role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' + json['success'] + '</div>');

               $('.alert-success').fadeIn('slow');
               $('.alert-success').delay(3000).fadeOut('slow');
               $('span#counterLabel').html(json['product_count']);
               $('#minicounterLabel').html(json['product_count']);
                $('#myModalMinimumDeposit').modal('hide');

               $.get('index.php?route=common/cart', function(html) {
                   $('ul#cartDropList').html(html);
                   $('#minicartDropList').html(html);
               });
              
           }
       }
   });
}