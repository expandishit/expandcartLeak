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
    <div class="right">
    <a  href="<?= $this->url->link('seller/account-auctions/auction_orders', '', 'SSL')?>" class="button btn btn-primary"><span> <?php echo $ms_account_aucation_orders; ?> </span></a>
    </div>
    </div>
    <hr>
     <table class="list" id="list-products">
        <thead>
            <tr>
                <td><?php echo $column_product_name; ?></td>
                <td><?php echo $column_starting_price; ?></td>
                <td><?php echo $column_increment; ?></td>
                <td><?php echo $column_start_datetime; ?></td>
                <td><?php echo $column_close_datetime; ?></td>
                <td><?php echo $column_auction_status; ?></td>
                <td><?php echo $column_biding_status; ?></td>
                <td><?php echo $column_min_deposit; ?></td>
                <td><?php echo $ms_account_aucation_update; ?></td>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
 
</div>
<script>
    $(function () {
        $('#list-products').DataTable({
            "sDom": '<"wrapper"irlfptip>',
            "sAjaxSource": $('base').attr('href') + "index.php?route=seller/account-auctions/getTableData<?=isset($_GET['search']) ? '&search=' . $_GET['search'] : ''?>",
            "aoColumns": [
                { "mData": "product_name" },
                { "mData": "starting_bid_price" },
                { "mData": "increment" },
                { "mData": "start_datetime" },
                { "mData": "close_datetime" },
                { "mData": "auction_status" },
                { "mData": "bidding_status" },
                { "mData": "min_deposit" },
                { "mData": "actions" }
            ],
            'iDisplayStart': '<?php echo $iDisplayStart; ?>',
        });
    });
</script>
<?php echo $footer; ?>