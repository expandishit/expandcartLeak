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
            <div class="buttons">
                <a onclick="$('form').attr('action', '<?php echo $approve; ?>'); $('form').submit();" class="button btn btn-primary"><?php echo $button_approve; ?></a>
                <a href="<?php echo $insert; ?>" class="button btn btn-primary"><?php echo $button_insert; ?></a>

                <a onclick="$('form').attr('action', '<?php echo $delete; ?>'); $('form').submit();" class="button btn btn-danger"><?php echo $button_delete; ?></a>
            </div>
        </div>
        <div class="content">
            <form action="" method="post" enctype="multipart/form-data" id="form">
                <table class="table table-hover dataTable no-footer">
                    <thead>
                    <tr>
                        <td width="1" style="text-align: center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).is(':checked'));" /></td>
                        <td class="left"><?php if ($sort == 'title') { ?>
                            <a href="<?php echo $sort_name; ?>" class="<?php echo strtolower($order); ?>">العنوان</a>
                            <?php } else { ?>
                            <a href="<?php echo $sort_name; ?>">العنوان</a>
                            <?php } ?></td>
                        <td class="left"> الشرح</td>
                        <td class="left"> التصنيف</td>

                        <td class="right"><?php echo $this->language->get('text_edit') ?></td>
                    </tr>
                    </thead>
                    <tbody>

                    <?php if ($blogs) { ?>
                    <?php foreach ($blogs as $blog) { ?>
                    <tr>
                        <td style="text-align: center;"><?php if ($blog['selected']) { ?>
                            <input type="checkbox" name="selected[]" value="<?php echo $blog['title']; ?>" checked="checked" />
                            <?php } else { ?>
                            <input type="checkbox" name="selected[]" value="<?php echo $blog['title']; ?>" />
                            <?php } ?></td>
                        <td class="left"><?php echo $blog['title']; ?></td>
                        <td class="left"><?php echo $blog['description']; ?></td>
                        <td class="left"><?php echo $blog['category']; ?></td>
                        <td class="right"><?php foreach ($blog['action'] as $action) { ?>
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