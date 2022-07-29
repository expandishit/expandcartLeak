<?php include_lib($header, $footer,'datatables'); ?>


<?php echo $header; ?>

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



<div class="row">
    <div class="col-lg-12">

        <!--breadcrumb Start-->
        <div class="row">
            <div class="col-lg-12">
                <ol class="breadcrumb">
                    <?php for($i=0; $i<count($breadcrumbs)-1; $i++) { ?>
                    <li><a href="<?php echo $breadcrumbs[$i]['href']; ?>"><?php echo $breadcrumbs[$i]['text']; ?></a></li>
                    <?php } ?>
                    <li class="active"><span><?php echo $breadcrumbs[count($breadcrumbs)-1]['text']; ?></span></li>
                </ol>

                <h1><?php echo $heading_title; ?></h1>
            </div>
        </div>
        <!--breadcrumb End-->

        <div class="row">
            <div class="col-lg-12">
                <div class="main-box clearfix">
                    <header class="main-box-header clearfix">
                        <h2>
                            <button type="button" class="btn btn-primary btn-lg" onclick="location.href='<?php echo $insert; ?>'" >
                                <span class="fa fa-folder-open-o"></span> <?php echo $button_insert; ?>
                            </button>

                            <button type="button" class="btn btn-danger btn-lg" onclick="$('#form').submit();" >
                                <span class="fa fa-times"></span> <?php echo $button_delete; ?>
                            </button>
                        </h2>
                    </header>

                    <div class="main-box-body clearfix">
                        <div class="table-responsive col-lg-12">
            <form action="" method="post" enctype="multipart/form-data" id="form">
                <table id="table-categories" class="table table-hover">
                    <thead>
                    <tr>
                        <th>
                            <div class="checkbox-nice">
                                <input type="checkbox" id="chkAllItems" onclick="$('input[name*=\'selected\']').prop('checked', $(this).is(':checked'));" />
                                <label for="chkAllItems"></label>
                            </div>
                        </th>
                        <td class="left"><?php if ($sort == 'name') { ?>
                            <a href="<?php echo $sort_name; ?>" class="<?php echo strtolower($order); ?>">إسم التصنيف</a>
                            <?php } else { ?>
                            <a href="<?php echo $sort_name; ?>"><?php echo $column_name; ?></a>
                            <?php } ?></td>
                        <td class="right"><?php echo $this->language->get('text_edit') ?></td>
                    </tr>
                    </thead>
                    <tbody>

                    <?php if ($categories) { ?>
                    <?php foreach ($categories as $customer) { ?>
                    <tr>
                        <td style="text-align: center;"><?php if ($customer['selected']) { ?>
                            <input type="checkbox" name="selected[]" value="<?php echo $customer['name']; ?>" checked="checked" />
                            <?php } else { ?>
                            <input type="checkbox" name="selected[]" value="<?php echo $customer['name']; ?>" />
                            <?php } ?></td>
                        <td class="left"><?php echo $customer['name']; ?></td>


                        <td class="right"><?php foreach ($customer['action'] as $action) { ?>
                            [ <a href="<?php echo $action['href']; ?>"><?php echo $action['text']; ?></a> ]
                            <?php } ?></td>
                    </tr>
                    <?php } ?>
                    <?php } else { ?>
                    <tr>
                        <td class="center" colspan="10"><?php echo $text_no_results; ?></td>
                    </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </form>
            <div class="pagination"><?php echo $pagination; ?></div>
        </div>
    </div>
</div>
<script type="text/javascript"><!--
    function filter() {
        url = 'index.php?route=sale/customer&token=<?php echo $token; ?>';

        var filter_name = $('input[name=\'filter_name\']').val();

        if (filter_name) {
            url += '&filter_name=' + encodeURIComponent(filter_name);
        }

        var filter_email = $('input[name=\'filter_email\']').val();

        if (filter_email) {
            url += '&filter_email=' + encodeURIComponent(filter_email);
        }

        var filter_customer_group_id = $('select[name=\'filter_customer_group_id\']').val();

        if (filter_customer_group_id != '*') {
            url += '&filter_customer_group_id=' + encodeURIComponent(filter_customer_group_id);
        }

        var filter_status = $('select[name=\'filter_status\']').val();

        if (filter_status != '*') {
            url += '&filter_status=' + encodeURIComponent(filter_status);
        }

        var filter_approved = $('select[name=\'filter_approved\']').val();

        if (filter_approved != '*') {
            url += '&filter_approved=' + encodeURIComponent(filter_approved);
        }

        var filter_ip = $('input[name=\'filter_ip\']').val();

        if (filter_ip) {
            url += '&filter_ip=' + encodeURIComponent(filter_ip);
        }

        var filter_date_added = $('input[name=\'filter_date_added\']').val();

        if (filter_date_added) {
            url += '&filter_date_added=' + encodeURIComponent(filter_date_added);
        }

        location = url;
    }
    //--></script>
<script type="text/javascript"><!--
    $(document).ready(function() {
        $('#date').datepicker({dateFormat: 'yy-mm-dd'});
    });
    //--></script>
<?php echo $footer; ?>