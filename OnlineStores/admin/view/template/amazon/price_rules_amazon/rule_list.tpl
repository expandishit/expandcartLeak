<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
      	<a href="<?php echo $add_rules; ?>" data-toggle="tooltip" title="<?php echo $btn_add_rule; ?>" class="btn btn-success"><i class="fa fa-plus" aria-hidden="true"></i> <?php echo $btn_add_rule; ?></a>
        <a href="<?php echo $add_csv; ?>" data-toggle="tooltip" title="<?php echo $btn_add_csv_tool; ?>" class="btn btn-warning"><i class="fa fa-plus" aria-hidden="true"></i> <?php echo $btn_add_csv; ?></a>
        <button type="button" data-toggle="tooltip" title="<?php echo $button_delete; ?>" class="btn btn-danger" onclick="confirm('<?php echo $text_confirm; ?>') ? $('#form-price-rule').submit() : false;"><i class="fa fa-trash-o"></i></button>
      </div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
    <div class="container-fluid">
      <?php if (isset($error_warning) && $error_warning) { ?>
      <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
      <?php } ?>
      <?php if (isset($success) &&  $success) { ?>
      <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
      <?php } ?>
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title"><i class="fa fa-list"></i> <?php echo $text_list; ?></h3>
        </div>
        <div class="panel-body">
          <div class="well">
            <div class="row">
              <div class="col-sm-3">
                <div class="form-group">
                  <label class="control-label" for="input-price-from"><?php echo $entry_price_from; ?></label>
                  <input type="text" name="filter_price_from" value="<?php echo $filter_price_from; ?>" placeholder="<?php echo $entry_price_from; ?>" id="input-price-from" class="form-control" />
                </div>
                <div class="form-group">
                  <label class="control-label" for="input-price-to"><?php echo $entry_price_to; ?></label>
                  <input type="text" name="filter_price_to" value="<?php echo $filter_price_to; ?>" placeholder="<?php echo $entry_price_to; ?>" id="input-price-to" class="form-control" />
                </div>
              </div>
              <div class="col-sm-3">
                <div class="form-group">
                  <label class="control-label" for="input-price-value"><?php echo $entry_price_value; ?></label>
                  <input type="text" name="filter_price_value" value="<?php echo $filter_price_value; ?>" placeholder="<?php echo $entry_price_value; ?>" id="input-price-value" class="form-control" />
                </div>
                <div class="form-group">
                  <label class="control-label" for="input-status"><?php echo $entry_status; ?></label>
                  <select name="filter_status" id="input-status" class="form-control">
                    <option value="*"></option>
                    <?php if ($filter_status) { ?>
                    <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                    <?php } else { ?>
                    <option value="1"><?php echo $text_enabled; ?></option>
                    <?php } ?>
                    <?php if (!$filter_status && !is_null($filter_status)) { ?>
                    <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                    <?php } else { ?>
                    <option value="0"><?php echo $text_disabled; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="col-sm-3">
                <div class="form-group">
                  <label class="control-label" for="input-type"><?php echo $entry_price_type; ?></label>
                  <select name="filter_price_type" id="input-type" class="form-control">
                    <option value="*"></option>
                    <?php if($filter_price_type) { ?>
                    <option value="1" selected="selected"><?php echo $text_price_type_inc; ?></option>

                <?php } else{ ?>
<option value="1" ><?php echo $text_price_type_inc; ?></option>
<?php } ?>
                  <?php if($filter_price_type=='0') { ?>
                    <option value="1" selected="selected" ><?php echo $text_price_type_dec; ?></option>
                  <?php } else{ ?>
  <option value="0" ><?php echo $text_price_type_dec; ?></option>
<?php } ?>
                  </select>
                </div>

                <div class="form-group">
                  <label class="control-label" for="input-price-opration"><?php echo $entry_price_opration; ?></label>
                  <select name="filter_price_opration" id="input-price-opration" class="form-control">
                    <option value="*"></option>
                    <option value="0" <?php if(isset($filter_price_opration) && !$filter_price_opration) { echo 'selected'; } ?>
                      ><?php echo $text_price_type_fixed; ?></option>
                    <option value="1" <?php if(isset($filter_price_opration) && $filter_price_opration) { echo 'selected'; } ?>><?php echo $text_price_type_percent; ?></option>
                  </select>
                </div>


              </div>
              <div class="col-sm-3">


                <div class="form-group ">
          <label class="control-label" for="input-rule-type"><?php echo $entry_rule_type; ?></label>
                  <div class="col-sm-12">
            <select name="filter_rule_type" id="input-rule-type" class="form-control">
                  <option value="*"></option>
              <option value="price" <?php if($filter_rule_type=='price'){ echo 'selected'; } ?>>Price </option>
              <option value="quantity" <?php if($filter_rule_type=='quantity'){ echo 'selected'; } ?>> Quantity </option>
           </select>

                  </div>
                </div>
                <div class="form-group " style="padding-top:58px;">

                </div>

                <a href="<?php echo $clear; ?>" type="button" id="button-filter-clear" class="btn btn-danger pull-right" style="margin-left:5px; "><i class="fa fa-eraser" aria-hidden="true"></i> <?php echo "Clear"; ?></a>
                <button type="button" id="button-filter" class="btn btn-primary pull-right"><i class="fa fa-filter"></i> <?php echo $button_filter; ?></button>
              </div>
            </div>
          </div>
          <form action="<?php echo $delete; ?>" method="post" enctype="multipart/form-data" id="form-price-rule">
            <div class="table-responsive">
              <table class="table table-bordered table-hover">
                <thead>
                  <tr>
                    <td style="width: 1px;" class="text-center"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" /></td>
                      <td class="text-left"> <?php echo $entry_rule_type;  ?></td>
                    <td class="text-left"><?php if ($sort == 'price_from') { ?>
                      <a href="<?php echo $sort_price_from; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_price_from; ?></a>
                      <?php } else { ?>
                      <a href="<?php echo $sort_price_from; ?>"><?php echo $column_price_from; ?></a>
                      <?php } ?>
                    </td>
                    <td class="text-left"><?php if ($sort == 'price_to') { ?>
                        <a href="<?php echo $sort_price_to; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_price_to; ?></a>
                        <?php } else { ?>
                        <a href="<?php echo $sort_price_to; ?>"><?php echo $column_price_to; ?></a>
                        <?php } ?>
                    </td>
                    <td class="text-left"><?php if ($sort == 'price_value') { ?>
                        <a href="<?php echo $sort_price_value; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_price_value; ?></a>
                        <?php } else { ?>
                        <a href="<?php echo $sort_price_value; ?>"><?php echo $column_price_value; ?></a>
                        <?php } ?>
                    </td>

                    <td class="text-left"><?php if ($sort == 'price_type') { ?>
                        <a href="<?php echo $sort_price_type; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_price_type; ?></a>
                        <?php } else { ?>
                        <a href="<?php echo $sort_price_type; ?>"><?php echo $column_price_type; ?></a>
                        <?php } ?>
                    </td>
                    <td class="text-left"><?php if ($sort == 'price_opration') { ?>
                        <a href="<?php echo $sort_price_opration; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_price_opration; ?></a>
                        <?php } else { ?>
                        <a href="<?php echo $sort_price_opration; ?>"><?php echo $column_price_opration; ?></a>
                        <?php } ?>
                    </td>
                    <td class="text-left"><?php if ($sort == 'price_status') { ?>
                        <a href="<?php echo $sort_price_status; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_price_status; ?></a>
                        <?php } else { ?>
                        <a href="<?php echo $sort_price_status; ?>"><?php echo $column_price_status; ?></a>
                        <?php } ?>
                    </td>

                    <td class="text-right"><?php echo $column_action; ?></td>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($rule_list) { ?>
                  <?php foreach ($rule_list as $result) { ?>
                  <tr>
                    <td class="text-center"><?php if (in_array($result['id'], $selected)) { ?>
                      <input type="checkbox" name="selected[]" value="<?php echo $result['id']; ?>" checked="checked" />
                      <?php } else { ?>
                      <input type="checkbox" name="selected[]" value="<?php echo $result['id']; ?>" />
                      <?php } ?></td>
                          <td class="text-left"><?php echo ucfirst($result['rule_type']); ?></td>
                    <td class="text-left"><?php echo $result['price_from']; ?></td>
                    <td class="text-left"><?php echo $result['price_to']; ?></td>
                    <td class="text-left"><?php echo $result['price_value']; ?></td>
                    <td class="text-right"><?php echo $result['price_type']; ?></td>
                    <td class="text-right"><?php echo $result['price_opration']; ?></td>
                    <td class="text-left"><?php echo $result['price_status']; ?></td>
                    <td class="text-right"><a href="<?php echo $result['edit']; ?>" data-toggle="tooltip" title="<?php echo $button_edit; ?>" class="btn btn-primary"><i class="fa fa-pencil"></i></a></td>
                  </tr>
                  <?php } ?>
                  <?php } else { ?>
                  <tr>
                    <td class="text-center" colspan="9"><?php echo $text_no_results; ?></td>
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
  </div>
<?php echo $footer; ?>
<script type="text/javascript"><!--
$('#button-filter').on('click', function() {
var url = 'index.php?route=price_rules_amazon/price_rules&token=<?php echo $token; ?>';

var filter_price_from = $('input[name=\'filter_price_from\']').val();

if (filter_price_from) {
  url += '&filter_price_from=' + encodeURIComponent(filter_price_from);
}

var filter_price_to = $('input[name=\'filter_price_to\']').val();

if (filter_price_to) {
  url += '&filter_price_to=' + encodeURIComponent(filter_price_to);
}

var filter_price_value = $('input[name=\'filter_price_value\']').val();

if (filter_price_value) {
  url += '&filter_price_value=' + encodeURIComponent(filter_price_value);
}

var filter_price_type = $('select[name=\'filter_price_type\']').val();

if (filter_price_type != '*') {
  url += '&filter_price_type=' + encodeURIComponent(filter_price_type);
}

var filter_rule_type = $('select[name=\'filter_rule_type\']').val();

if (filter_rule_type != '*') {
  url += '&filter_rule_type=' + encodeURIComponent(filter_rule_type);
}
var filter_price_opration = $('select[name=\'filter_price_opration\']').val();

if (filter_price_opration != '*') {
  url += '&filter_price_opration=' + encodeURIComponent(filter_price_opration);
}

var filter_price_status = $('select[name=\'filter_status\']').val();

if (filter_price_status != '*') {
  url += '&filter_price_status=' + encodeURIComponent(filter_price_status);
}

location = url;
});
//--></script>
