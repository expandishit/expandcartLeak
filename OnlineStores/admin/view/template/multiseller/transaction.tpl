<?php echo $header; ?>

<div id="content" class="ms-transaction-page">
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
			<h1><?php echo $ms_transactions_heading; ?></h1>
			<div class="buttons">
				<a onclick="location = '<?php echo $link_create_transaction; ?>'" class="button btn btn-primary"><?php echo $ms_transactions_new; ?></a>
			</div>			
		</div>
		
		<div class="content">
		<table class="table table-hover dataTable no-footer" style="text-align: center" id="list-transactions">
		<thead>
			<tr>
				<td class="tiny"><?php echo $ms_id; ?></td>
				<td class="medium"><?php echo $ms_seller; ?></a></td>
				<td class="small"><?php echo $ms_net_amount; ?></a></td>
				<td><?php echo $ms_description; ?></a></td>
				<td class="medium"><?php echo $ms_date; ?></a></td>
			</tr>
			<tr class="filter">
				<td><input type="text"/></td>
				<td><input type="text"/></td>
				<td><input type="text"/></td>
				<td><input type="text"/></td>
				<td><input type="text"/></td>
			</tr>
		</thead>
		<tbody></tbody>
		</table>
		</div>
	</div>
</div>

<script type="text/javascript">
$(document).ready(function() {
	$('#list-transactions').dataTable( {
		"sAjaxSource": "index.php?route=multiseller/transaction/getTableData&token=<?php echo $token; ?>",
		"aoColumns": [
			{ "mData": "id" },
			{ "mData": "seller" },
			{ "mData": "amount" },
			{ "mData": "description" },
			{ "mData": "date_created" },
		],
        "aaSorting":  [[4,'desc']],
        "oLanguage": {
            "sLengthMenu ": text_lengthMenu,
            "sZeroRecords": text_zeroRecords,
            "sInfo": text_info,
            "sInfoEmpty": text_infoEmpty,
            "sInfoFiltered": text_infoFiltered,
            "sSearch": text_search
        }
	});
});
</script>
<?php echo $footer; ?> 