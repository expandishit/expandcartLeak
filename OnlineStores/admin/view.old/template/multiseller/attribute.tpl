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
		<h1><?php echo $ms_attribute_heading; ?></h1>
		<div class="buttons">
			<a href="index.php?route=multiseller/attribute/create&token=<?php echo $token; ?>" class="button btn btn-primary"><?php echo $button_insert; ?></a>
			<a id="ms-delete-attribute" class="button btn btn-primary"><?php echo $button_delete; ?></a>
		</div>
	</div>
	<div class="content">
		<form action="" method="post" enctype="multipart/form-data" id="form">
		<table class="table table-hover dataTable no-footer" style="text-align: center" id="list-attributes">
			<thead>
			<tr>
				<td width="1" style="text-align: center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).is(':checked'));" /></td>
				<td><?php echo $ms_name; ?></a></td>
				<td><?php echo $ms_type; ?></a></td>
				<td><?php echo $ms_sort_order; ?></a></td>
				<td><?php echo $ms_status; ?></a></td>
				<td><?php echo $ms_action; ?></a></td>
			</tr>
			<tr class="filter">
				<td></td>
				<td><input type="text"/></td>
				<td></td>
				<td></td>
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
<?php echo $footer; ?>

<script type="text/javascript">
$(function() {
	$('#list-attributes').dataTable( {
		"sAjaxSource": "index.php?route=multiseller/attribute/getTableData&token=<?php echo $token; ?>",
		"aoColumns": [
			{ "mData": "checkbox", "bSortable": false },
			{ "mData": "name" },
			{ "mData": "type" },
			{ "mData": "sort_order" },
			{ "mData": "status" },
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

	$("#ms-delete-attribute").click(function() {
		var data  = $('#form').serialize();
		$('#ms-delete-attribute').before('<img src="view/image/loading.gif" alt="" />');
		$.ajax({
			type: "POST",
			//async: false,
			dataType: "json",
			url: 'index.php?route=multiseller/attribute/delete&token=<?php echo $token; ?>',
			data: data,
			success: function(jsonData) {
				window.location.reload();
			}
		});
	});
});
</script> 