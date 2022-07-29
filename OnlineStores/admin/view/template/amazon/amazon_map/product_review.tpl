<div id="content">

        <div class="pull-right" style="margin-bottom: 10px;">
          <a href="javascript:;" onclick="updateFeedStatus();"  type="button" data-toggle="tooltip" title="" class="btn btn-primary"  <span class="col-sm-12"><?php echo $text_bulk_update; ?></span></a>
       </div>
       <button type="button" style="margin-right: 20px;" data-toggle="tooltip" title="<?php echo $text_delete_warning; ?>" class="btn btn-danger pull-right" onclick="confirm('<?php echo $text_confirm; ?>') ? $('#form-product-preview').submit() : false;"><i class="fa fa-trash-o"></i></button>
    <div class="container-fluid">
          <h3><?php echo $text_title; ?></h3>
          <hr>
    <div class="panel panel-default">
      <div class="panel-heading" style="display:inline-block;width:100%;">
        <h3 class="panel-title"><i class="fa fa-list"></i> <?php echo $text_product_review_list; ?></h3>
      </div>

      <div class="panel-body">

        <div class="well">
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label class="control-label" for="oc-product-id-review-product"><?php echo $text_oc_product_id; ?></label>
                  <input type="text" name="filter_oc_prod_id_review" value="<?php echo $filter_oc_prod_id_review; ?>" placeholder="<?php echo $text_oc_product_id; ?>" id="oc-product-id-review-product" class="form-control">
              </div>


            </div>

            <div class="col-sm-6">
                <div class="form-group">
                  <label class="control-label" for="input-oc-product-name"><?php echo $text_oc_product_name; ?></label>
                  <div class="input-group">
                    <input type="text" name="filter_oc_prod_name_review" value="<?php echo $filter_oc_prod_name_review; ?>" placeholder="<?php echo $text_oc_product_name; ?>" id="input-oc-product-name" class="form-control" autocomplete="off">
                    <span class="input-group-addon">
                      <span class="fa fa-angle-double-down"></span>
                    </span>
                  </div>
                </div>


            </div>


            <div class="col-sm-6" style="margin-top:38px;">
              <button type="button" onclick="filter_product_review();" class="btn btn-primary pull-left" style="border-radius:3px; margin-right: 8px;">
                <i class="fa fa-search"></i><?php echo $text_filter ?></button>

              <a href="javascript:;" onclick="filter_reset_product_review();" class="btn btn-default" style="border-radius:3px;"><i class="fa fa-eraser" aria-hidden="true"></i> <?php echo $text_clear ?></a>
            </div>
          </div>
        </div>

        <form method="post" action="<?php echo $delete_action; ?>" enctype="multipart/form-data" id="form-product-preview">
          <div class="table-responsive" id="product_review_list_section">
            <table class="table table-bordered table-hover">
              <thead>
                <tr>
                  <td style="width: 1px;" class="text-center"><input type="checkbox" onclick="$('#product_review_list_section input[name*=\'selected_feed\']').prop('checked', this.checked);" /></td>

                  <td class="text-left"><?php echo $column_product_id; ?></td>
                  <td class="text-left"><?php echo $column_name; ?></td>
                  <td class="text-left"><?php echo $column_price; ?></td>
                  <td class="text-left"><?php echo $column_quantity; ?></td>
                  <td class="text-left"><?php echo $column_feed; ?></td>
                </tr>
              </thead>
              <tbody>
                <input type="hidden" name="account_id" value="<?php echo $account_id; ?>" />
                <?php if (count($records)>0) { ?>
                <?php foreach ($records as $product) { ?>
                <tr class="product_id_<?php echo $product['product_id']; ?>">
                    <td class="text-center">
                       <input type="checkbox" name="selected_feed[]" class="<?php echo $product['feed_id']; ?>" value="<?php echo $product['product_id']; ?>" />
                   </td>

                  <td class="text-left"><?php echo $product['product_id']; ?></td>
                  <td class="text-left"><?php echo $product['product_name']; ?></td>

                  <td class="text-left"><?php echo $product['price']; ?></td>
                  <td class="text-left"><?php echo $product['quantity']; ?></td>
                  <td class="text-left" ><span class="btn btn-primary" onclick="checkFeedStatus('<?php echo $product['feed_id']; ?>','<?php echo $product['product_id']; ?>')">Check Feed Status</span> </td>
                </tr>
                <?php } ?>
                <?php } else { ?>
                <tr>
                  <td class="text-center" colspan="6"><?php echo $text_no_results; ?></td>
                </tr>
                <?php } ?>

              </tbody>
            </table>

          </div>
        </form>
        <div class="row">
          <div class="col-sm-6 text-left"><?php echo $pagination; ?></div>
          <div class="col-sm-6 text-right"><?php echo $results; ?></div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal fade feed_status_id" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="feedid_status"><strong><?php echo $column_feed; ?></strong></h4>
      </div>
      <div class="modal-body feed_html">

      </div>
      <div class="modal-footer">
        <span class="demo-spin" style="color: #1e91cf;position: relative;top: 6px;"><i class="fa  fa-spin fa-2x fa-fw"></i></span>

        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $button_close; ?></button>
      </div>

    </div>
  </div>
</div>
<script>
function filter_product_review() {
	url = 'index.php?route=amazon_map/account/edit&token=<?php echo $token; ?>&account_id=<?php echo $account_id; ?>&status=account_product_review';

  var filter_oc_prod_id_review = $('input[name=\'filter_oc_prod_id_review\']').val();

  if (filter_oc_prod_id_review) {
    url += '&filter_oc_prod_id_review=' + encodeURIComponent(filter_oc_prod_id_review);
  }

	var filter_oc_prod_name_review = $('input[name=\'filter_oc_prod_name_review\']').val();

	if (filter_oc_prod_name_review) {
		url += '&filter_oc_prod_name_review=' + encodeURIComponent(filter_oc_prod_name_review);
	}

	location = url;
}

function filter_reset_product_review() {
	url = 'index.php?route=amazon_map/account/edit&token=<?php echo $token; ?>&account_id=<?php echo $account_id; ?>&status=account_product_review';
  location = url;
}
$('input[name=\'filter_oc_prod_name_review\']').autocomplete({
  delay: 0,
  source: function(request, response) {
    $.ajax({
      url: 'index.php?route=amazon_map/product_review/autocomplete&token=<?php echo $token; ?>&account_id=<?php echo $account_id; ?>&filter_oc_prod_name_review=' +  encodeURIComponent(request),
      dataType: 'json',
      success: function(json) {
        response($.map(json, function(item) {
          return {
            label: item.product_name,
            value: item.item_id
          }
        }));
      }
    });
  },
  select: function(item) {
    $('input[name=\'filter_oc_prod_name_review\']').val(item.label);
    return false;
  },
  focus: function(item) {
      return false;
  }
});
function checkFeedStatus(feed_id,product_id) {
       var html = '';
      $.ajax({
          url     : 'index.php?route=amazon_map/product_review/checkFeedStatus&token=<?php echo $token; ?>',
          data: {
            'account_id' : '<?php echo $account_id; ?>',
            'feed_id'    : feed_id,
            'product_id' : product_id
          },
          dataType:'json',
          type:'POST',
          beforeSend: function() {

            $('.block_div, #profiler_product').css('display','block');
            $('.block_div').css('display','block');

          },
          complete:function() {
              $('.block_div').css('display','none');

          },
          success: function(response) {

            if(response.status){
              html +=createMessage(response);
            } else {
                html +='<div class="alert alert-danger"><i class="fa fa-exclamation-circle"><b>Warning Product Id ('+response.product_id+')</b>:'+response.comment+' </i><button type="button" class="close" data-dismiss="alert">&times;</button></div>';

            }
            $('.feed_html').append(html);
            $('.feed_status_id').modal('show');

        },
        error: function(xhr, ajaxOptions, thrownError) {
          alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
      });
  }
  function createMessage(data) {

    var html ='<br><b>Product Detail, Product Id </b>:'+data.product_id;
    html +='<br><div class="alert alert-danger"><i class="fa fa-exclamation-circle"><b>MessagesProcessed</b> : '+data.ProcessingSummary.MessagesProcessed+' <br><b>MessagesSuccessful</b> :'+data.ProcessingSummary.MessagesSuccessful+' <br><b>MessagesWithError</b> :'+data.ProcessingSummary.MessagesWithError+' <br><b>MessagesWithWarning</b> :'+data.ProcessingSummary.MessagesWithWarning+' </i><button type="button" class="close" data-dismiss="alert">&times;</button></div>';

    if(data.ProcessingSummary.MessagesWithError >1) {
       $.each( data.Result, function(field_data, value_data){
           html +='<div class="alert alert-danger"><i class="fa fa-exclamation-circle"><b>ResultMessageCode</b> '+value_data.ResultMessageCode+' <br><b>ResultDescription</b> :'+value_data.ResultDescription+' </i><button type="button" class="close" data-dismiss="alert">&times;</button></div>';
       });
      } else {
             html +='<div class="alert alert-danger"><i class="fa fa-exclamation-circle"><b>ResultMessageCode</b> '+data.Result.ResultMessageCode+' <br><b>ResultDescription</b> :'+data.Result.ResultDescription+' </i><button type="button" class="close" data-dismiss="alert">&times;</button></div>';
          }

        if(data.move_map){
                html +='<div class="alert alert-success"><i class="fa fa-exclamation-circle"><b>Success </b>: <?php echo $text_seccu ?> </i><button type="button" class="close" data-dismiss="alert">&times;</button></div>';
                $('.product_id_'+data.product_id).remove();
           }

           return html;


  }

  function updateFeedStatus() {

  var remain = 1;
  var total = $('input[name^="selected_feed"]:checked').length
if(total >0) {
  $('.block_div, #profiler_product').css('display','block');
  $('.block_div').css('display','block');
  $('input[name^="selected_feed"]:checked').each(function(i) {
    $('.gif').css('display','block');
  var final =  i+remain;
  var html = '';

  $.ajax({
      url     : 'index.php?route=amazon_map/product_review/checkFeedStatus&token=<?php echo $token; ?>',
      data: {
        'account_id' : '<?php echo $account_id; ?>',
        'feed_id'    : $(this).attr('class'),
        'product_id' : $(this).val()
      },
      dataType:'json',
      type:'POST',
      beforeSend: function() {
      },
      success: function(response) {
        if(response.status){
          html +=createMessage(response);
        } else {
            html +='<div class="alert alert-danger"><i class="fa fa-exclamation-circle"><b>Warning Product Id ('+response.product_id+')</b>:'+response.status+' </i><button type="button" class="close" data-dismiss="alert">&times;</button></div>';

        }
        $('.feed_html').append(html);

         if(total==final) {
            $('.block_div').css('display','none');
            $('.feed_status_id').modal('show');
         }
      },
      error: function(xhr, ajaxOptions, thrownError) {
        alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
      }
    });
   });
 } else {
   html ='<div class="alert alert-danger"><i class="fa fa-exclamation-circle"><b>Warning: Please Select Product. </i><button type="button" class="close" data-dismiss="alert">&times;</button></div>';
     $('.feed_html').append(html);
      $('.feed_status_id').modal('show');
 }

}

</script>
<style>
.feed_html {
  max-height: 400px;
    overflow-y: scroll;
}
</style>
