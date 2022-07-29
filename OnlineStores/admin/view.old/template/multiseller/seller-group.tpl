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
			<h1><?php echo $heading; ?></h1>
			<div class="buttons">
				<a onclick="location = '<?php echo $insert; ?>'" class="button btn btn-primary"><?php echo $button_insert; ?></a>
				<a onclick="$('form').submit();" class="button btn btn-primary"><?php echo $button_delete; ?></a>
			</div>
		</div>
		<div class="content">
		<form action="<?php echo $delete; ?>" method="post" enctype="multipart/form-data" id="form">
			<table class="table table-hover dataTable no-footer" style="text-align: center" id="list-seller-groups">
			<thead>
				<tr>
					<td width="1"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).is(':checked'));" /></td>
					<td><?php echo $ms_seller_groups_column_id; ?></td>
					<td style="width: 100px"><?php echo $ms_seller_groups_column_name; ?></td>
					<td><?php echo $ms_description; ?></td>
					<td style="width: 450px"><?php echo $ms_commission_actual; ?></td>
					<td><?php echo $ms_seller_groups_column_action; ?></td>
				</tr>
				<tr class="filter">
					<td></td>
					<td><input type="text"/></td>
					<td><input type="text"/></td>
					<td><input type="text"/></td>
					<td></td>
					<td></td>
				</tr>
			</thead>
			<tbody></tbody>
			</table>
		</form>
		</div>
	</div>
</div>

<script type="text/javascript">
$(document).ready(function() {
	$('#list-seller-groups').dataTable( {
		"sAjaxSource": "index.php?route=multiseller/seller-group/getTableData&token=<?php echo $token; ?>",
		"aoColumns": [
			{ "mData": "checkbox", "bSortable": false },
			{ "mData": "id" },
			{ "mData": "name" },
			{ "mData": "description" },
			{ "mData": "rates", "bSortable": false },
			{ "mData": "actions", "bSortable": false, "sClass": "right" }
		],
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