<?php require( DIR_TEMPLATE.$this->config->get('config_template')."/template/common/config.tpl" ); ?>
<?php echo $header; ?>
<?php if( $SPAN[0] ): ?>
<aside class="col-lg-<?php echo $SPAN[0];?> col-sm-<?php echo $SPAN[0];?> col-xs-12">
	<?php echo $column_left; ?>
</aside>
<?php endif; ?> 
<section class="col-lg-<?php echo $SPAN[1];?> col-sm-<?php echo $SPAN[1];?> col-xs-12">
   <?php require( DIR_TEMPLATE.$this->config->get('config_template')."/template/common/breadcrumb.tpl" );  ?>  
<div id="content"><?php echo $content_top; ?>
 
  <h1><?php echo $heading_title; ?></h1>
  <p><?php echo $text_balance_inc_pending; ?><b> <?php echo $balanceIncPending; ?></b></p>
  <p><?php echo $text_earning; ?><b> <?php echo $earning; ?></b></p>
  <p><?php echo $text_balance; ?><b> <?php echo $balance; ?></b></p>
  <table class="list">
    <thead>
      <tr>
        <td class="left"><?php echo $column_date_added; ?></td>
        <td class="left"><?php echo $text_order_id; ?></td>
        <td class="left"><?php echo $text_order_products; ?></td>
        <td class="left"><?php echo $text_customer_name; ?></td>
        <td class="left"><?php echo $text_order_status; ?></td>
        <td class="right"><?php echo $text_order_total; ?></td>
        <td class="right"><?php echo $text_commission; ?></td>
        <td class="left"><?php echo $text_commission_added; ?></td
      </tr>
    </thead>
    <tbody>
      <?php if ($transactions) { ?>
      <?php foreach ($transactions  as $transaction) { ?>
      <tr>
        <td class="left"><?php echo $transaction['date_added']; ?></td>
        <td class="left"><?php echo $transaction['order_id'] ? '#' . $transaction['order_id'] : ''; ?></td>
        <td class="left"><?php echo $transaction['order_products']; ?></td>
        <td class="left"><?php echo $transaction['customer_name']; ?></td>
        <td class="left"><?php echo $transaction['order_status_name']; ?></td>
        <td class="right"><?php echo $transaction['total']; ?></td>
        <td class="right"><?php echo $transaction['commission']; ?></td>
        <td class="left"><?php echo $transaction['commission_added'] ? $text_yes : $text_no; ?></td>
      </tr>
      <?php } ?>
      <?php } else { ?>
      <tr>
        <td class="center" colspan="5"><?php echo $text_empty; ?></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
  <div class="pagination"><?php echo $pagination; ?></div>
  <div class="buttons">
    <div class="right"><a href="<?php echo $continue; ?>" class="button"><?php echo $button_continue; ?></a></div>
  </div>
  <?php echo $content_bottom; ?></div>
</section> 
<?php if( $SPAN[2] ): ?>
<aside class="col-lg-<?php echo $SPAN[2];?> col-sm-<?php echo $SPAN[2];?> col-xs-12">	
	<?php echo $column_right; ?>
</aside>
<?php endif; ?>
<?php echo $footer; ?>