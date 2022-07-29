<?php echo $header; ?>
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
      <!--<div class="buttons"><a onclick="$('#form').attr('action', '<?php echo $invoice; ?>'); $('#form').attr('target', '_blank'); $('#form').submit();" class="button btn btn-primary"><?php echo $button_invoice; ?></a><a href="<?php echo $insert; ?>" class="button btn btn-primary"><?php echo $button_insert; ?></a><a onclick="$('#form').attr('action', '<?php echo $delete; ?>'); $('#form').attr('target', '_self'); $('#form').submit();" class="button btn btn-primary"><?php echo $button_delete; ?></a></div>-->
    </div>
    <div class="content">
      <form action="" method="post" enctype="multipart/form-data" id="form">
        <table class="table table-hover dataTable no-footer">
          <thead>
            <tr>
              <td width="1" style="text-align: center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).is(':checked'));" /></td>
              <td class="right"><?php echo $column_order_id; ?></td>
                <td class="right"><?php echo $column_customer; ?></td>
                <td class="right"><?php echo $column_url; ?></td>
                <td class="right"><?php echo $column_name; ?></td>
                <td class="right"><?php echo $column_category; ?></td>
                <td class="right"><?php echo $column_quantity; ?></td>
                <td class="right"><?php echo $column_price; ?></td>
                <td class="right"><?php echo $column_notes; ?></td>
                <td class="right"><?php echo $column_status; ?></td>
                <td class="right"><?php echo $column_total; ?></td>
                <td class="right"><?php echo $column_createdon; ?></td>

            </tr>
          </thead>
          <tbody>
            <!--<tr class="filter">
              <td></td>
              <td align="right"><input type="text" name="filter_order_id" value="<?php echo $filter_order_id; ?>" size="4" style="text-align: right;" /></td>
              <td><input type="text" name="filter_customer" value="<?php echo $filter_customer; ?>" /></td>
              <td><select name="filter_order_status_id">
                  <option value="*"></option>
                  <?php if ($filter_order_status_id == '0') { ?>
                  <option value="0" selected="selected"><?php echo $text_missing; ?></option>
                  <?php } else { ?>
                  <option value="0"><?php echo $text_missing; ?></option>
                  <?php } ?>
                  <?php foreach ($order_statuses as $order_status) { ?>
                  <?php if ($order_status['order_status_id'] == $filter_order_status_id) { ?>
                  <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                  <?php } else { ?>
                  <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                  <?php } ?>
                  <?php } ?>
                </select></td>
              <td align="right"><input type="text" name="filter_total" value="<?php echo $filter_total; ?>" size="4" style="text-align: right;" /></td>
              <td><input type="text" name="filter_date_added" value="<?php echo $filter_date_added; ?>" size="12" class="date" /></td>
              <td><input type="text" name="filter_date_modified" value="<?php echo $filter_date_modified; ?>" size="12" class="date" /></td>
              <td align="right"><a onclick="filter();" class="button btn btn-primary"><?php echo $button_filter; ?></a></td>
            </tr>-->
            <?php if ($orders) { ?>
            <?php foreach ($orders as $order) { ?>
            <tr>
              <td style="text-align: center;"><?php if ($order['selected']) { ?>
                <input type="checkbox" name="selected[]" value="<?php echo $order['order_id']; ?>" checked="checked" />
                <?php } else { ?>
                <input type="checkbox" name="selected[]" value="<?php echo $order['order_id']; ?>" />
                <?php } ?></td>
              <td class="right" style="width: 10px;"><?php echo $order['order_id']; ?></td>
              <td class="left"><?php echo $order['customer']; ?></td>
              <td class="left" style="max-width: 80px;"><div style="overflow-x:auto"><?php echo $order['url']; ?></div></td>
              <td class="left"><?php echo $order['name']; ?></td>
              <td class="left"><?php echo $order['category']; ?></td>
                <td class="left"><?php echo $order['quantity']; ?></td>
                <td class="left"><?php echo $order['price']; ?></td>
                <td class="left"><?php echo $order['notes']; ?></td>
              <td class="left">
                      <select name="category" orderid="<?php echo $order['order_id']; ?>">
                          <option value=""><?php echo $text_select; ?></option>
                          <?php foreach ($order_statuses as $orderstatus) { ?>
                          <?php if ($orderstatus['order_status_id'] == $order['statusvalue']) { ?>
                          <option value="<?php echo $orderstatus['order_status_id']; ?>" selected="selected"><?php echo $orderstatus['name']; ?></option>
                          <?php } else { ?>
                          <option value="<?php echo $orderstatus['order_status_id']; ?>"><?php echo $orderstatus['name']; ?></option>
                          <?php } ?>

                          <?php } ?>
                      </select>
                  <!--<?php echo $order['status']; ?>-->
              </td>
              <td class="right"><?php echo $order['total']; ?></td>
              <td class="left"><?php echo $order['createdon']; ?></td>
            </tr>
            <?php } ?>
            <?php } else { ?>
            <tr>
              <td class="center" colspan="8"><?php echo $text_no_results; ?></td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </form>
      <!--<div class="pagination"><?php echo $pagination; ?></div>-->
    </div>
  </div>
</div>

<script type="text/javascript"><!--
$('#form input').keydown(function(e) {
	if (e.keyCode == 13) {
		filter();
	}
});
    $('select').on('change', function() {
        //alert( this.value );
        //alert($(this).attr('orderid'));

        $.ajax({
            url: 'index.php?route=sale/externalorder/updatestatus&token=<?php echo $token; ?>',
            //dataType: 'json',
            type: 'POST',
            data: { orderId: $(this).attr('orderid'), statusValue: this.value }
            /*success: function(json) {
                response($.map(json, function(item) {
                    return {
                        category: item.customer_group,
                        label: item.name,
                        value: item.customer_id
                    }
                }));
            }*/
        });
    })
//--></script> 

<?php echo $footer; ?>