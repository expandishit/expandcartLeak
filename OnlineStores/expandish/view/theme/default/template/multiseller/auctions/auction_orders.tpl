<?php echo $header; ?>
<div id="content" class="ms-account-product">
	<?php echo $content_top; ?>
	
	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>

    <h3><?=$ms_account_dashboard_aucation?></h3>
    	<div class="buttons">
    	<div class="left">
    <a  href="<?= $this->url->link('seller/account-auctions/add', '', 'SSL')?>" class="button btn btn-primary"><span> <?php echo $ms_account_aucation_add; ?> </span></a>
    </div>
    </div>
    <hr>
 <table class="list" id="list-products">
        <thead>
            <tr> 
                <td><?php echo $ms_account_orders_id; ?></td>
                <td><?php echo $column_auction_id; ?></td>
                <td><?php echo $ms_account_orders_customer; ?></td>
                <td><?php echo $column_amount; ?></td>
                <td><?php echo $column_paid_at; ?></td>
          
            </tr>
        </thead>
         <tbody>
                <?php if($auction_orders) {?>
                     <?php foreach($auction_orders as $order) { ?> 
                        <tr>  
                            <td><a href="<?php echo $this->url->link('seller/account-order/viewOrder','order_id=' . $order['order_id'] , 'SSL'); ?>" ># <?php echo $order[order_id]; ?></a> </td>
                            <td><a href="<?php echo $this->url->link('seller/account-auctions/edit','auction_id=' . $order['auction_id'] , 'SSL'); ?>" ># <?php echo $order[auction_id]; ?></a> </td>
                            <td><?php echo $order[customer_name]; ?></td>
                            <td><?php echo $order[amount].' '.$order[currency_code]; ?></td>
                            <td><?php echo $order[paid_at]; ?></td>
                        </tr>
                    <?php } ?>
                <?php } else {?>
                    <tr>
                        <td colspan="8">{{ lang('text_no_results') }}</td>
                    </tr>
               <?php }?>
            </tbody>
    </table>


    <?php echo $footer; ?>