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
  <div class="box">
    <div class="heading">
      <h1><?php echo $heading_title; ?></h1>
    </div>
    <div class="content">
      <table class="form">
        <tr>
          <td><?php echo $entry_date_start; ?>
            <input type="text" name="filter_date_start" value="<?php echo $filter_date_start; ?>" id="date-start" size="12" /></td>
          <td><?php echo $entry_date_end; ?>
            <input type="text" name="filter_date_end" value="<?php echo $filter_date_end; ?>" id="date-end" size="12" /></td>
          <td style="text-align: right;"><a onclick="filter();" class="button btn btn-primary"><?php echo $button_filter; ?></a></td>
        </tr>
      </table>
      <table class="table table-hover dataTable no-footer">
        <thead>
          <tr>
            <td class="left"><?php echo $column_customer; ?></td>
            <td class="left"><?php echo $column_email; ?></td>
            <td class="left"><?php echo $column_customer_group; ?></td>
            <td class="left"><?php echo $column_status; ?></td>
            <td class="right"><?php echo $column_points; ?></td>
            <td class="right"><?php echo $column_orders; ?></td>
            <td class="right"><?php echo $column_total; ?></td>
            <td class="right"><?php echo $column_action; ?></td>
          </tr>
        </thead>
        <tbody>
          <?php if ($customers) { ?>
          <?php foreach ($customers as $customer) { ?>
          <tr>
            <td class="left"><?php echo $customer['customer']; ?></td>
            <td class="left"><?php echo $customer['email']; ?></td>
            <td class="left"><?php echo $customer['customer_group']; ?></td>
            <td class="left"><?php echo $customer['status']; ?></td>
            <td class="right"><?php echo $customer['points']; ?></td>
            <td class="right"><?php echo $customer['orders']; ?></td>
            <td class="right"><?php echo $customer['total']; ?></td>
            <td class="right"><?php foreach ($customer['action'] as $action) { ?>
              [ <a href="<?php echo $action['href']; ?>"><?php echo $action['text']; ?></a> ]
              <?php } ?></td>
          </tr>
          <?php } ?>
          <?php } else { ?>
          <tr>
            <td class="center" colspan="8"><?php echo $text_no_results; ?></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
      <div class="pagination"><?php echo $pagination; ?></div>
    </div>
  </div>
</div>
<script type="text/javascript"><!--
function filter() {
	url = 'index.php?route=report/customer_reward&token=<?php echo $token; ?>';
	
	var filter_date_start = $('input[name=\'filter_date_start\']').val();
	
	if (filter_date_start) {
		url += '&filter_date_start=' + encodeURIComponent(filter_date_start);
	}

	var filter_date_end = $('input[name=\'filter_date_end\']').val();
	
	if (filter_date_end) {
		url += '&filter_date_end=' + encodeURIComponent(filter_date_end);
	}
	
	location = url;
}
//--></script> 
<script type="text/javascript"><!--
$(document).ready(function() {
	$('#date-start').datepicker({dateFormat: 'yy-mm-dd'});
	
	$('#date-end').datepicker({dateFormat: 'yy-mm-dd'});
});
//--></script> 
<?php echo $footer; ?>