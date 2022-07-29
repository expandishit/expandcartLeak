<?php echo $header; ?>

<div id=content">
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
            <h1>{{ lang('network_marketing_heading_title') }}</h1>
            <div class="buttons">
                <a href="<?= $links['downline'] . '&agency_id=' . $agencyId; ?>"
                   class="button btn btn-primary">
                    <span>{{ lang('text_downlines') }}</span>
                </a>
                <a href="<?= $links['cancel']; ?>"
                       class="button btn btn-primary">
                    <span>{{ lang('button_back') }}</span>
                </a>
            </div>
        </div>
        <div class="content col-md-12">
            <table class="table table-striped table-bordered">
                <tr>
                    <td>{{ lang('text_ref_id') }}</td>
                    <td><?= $agency['ref_id']; ?></td>
                </tr>
                <tr>
                    <td>{{ lang('created_at') }}</td>
                    <td><?= $agency['created_at']; ?></td>
                </tr>
            </table>
        </div>
        <?php if ($subAgencies) { ?>
        <div class="heading">
            <h1>{{ lang('text_sub_agencies') }}</h1>
        </div>
        <div class="content col-md-12">
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{ lang('agency_user') }}</th>
                    <th>{{ lang('text_options') }}</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($subAgencies['data'] as $agency) { ?>
                <tr>
                    <td><?= $agency['agency_id']; ?></td>
                    <td><?= $agency['firstname'] . ' ' . $agency['lastname']; ?></td>
                    <td>
                        <a href="<?= $links['viewAgency'] . '&agency_id=' . $agency['agency_id']; ?>"
                           class="btn btn-primary" title="{{ lang('text_view') }}" data-toggle="tooltip">
                            <i class="fa fa-eye"></i>
                        </a>
                        <a href="<?= $links['downline'] . '&agency_id=' . $agency['agency_id']; ?>"
                           class="btn btn-primary" title="{{ lang('text_downlines') }}" data-toggle="tooltip">
                            <i class="fa fa-sitemap"></i>
                        </a>
                    </td>
                </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
        <?php } ?>
    </div>
</div>

<?php echo $footer; ?>
