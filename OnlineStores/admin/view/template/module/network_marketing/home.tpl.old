<?php echo $header; ?>
<style>
    .links-list ul {
        list-style: none;
        margin: 0;
        padding: 0;
        text-align: center;
    }
    .links-list ul li {
        margin-bottom: 50px;
    }
    .links-list ul li a:hover {
        text-decoration: none;
    }
    .links-list ul li i {
        padding: 10px 10px 35px 10px;
        font-size: 120px;
        display: block;
        color: black;
    }
    .links-list ul li span {
        font-size: 16px;
        font-weight: 600;
    }
</style>
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

    <div id="content row" class="box">

        <div class="heading col-md-12">
            <h1>{{ lang('network_marketing_heading_title') }}</h1>
            <div class="buttons">
                <a onclick="$('#form').submit();"
                   class="button btn btn-primary tab_settings">
                    <span>{{ lang('button_save') }}</span>
                </a>
                <a href="<?= $links['cancel']; ?>"
                   class="button btn btn-primary">
                    <span>{{ lang('button_cancel') }}</span>
                </a>
            </div>
        </div>

        <div class="col-md-12 content">
            <div class="links-list">
                <ul class="list-none">
                    <li class="col-md-6">
                        <a href="<?= $links['settings']; ?>">
                            <i class="fa fa-cog fa-lg"></i>
                            <span>{{ lang('text_nm_settings') }}</span>
                        </a>
                    </li>
                    <li class="col-md-6">
                        <a href="<?= $links['levels']; ?>">
                            <i class="fa fa-bars fa-lg"></i>
                            <span>{{ lang('text_levels') }}</span>
                        </a>
                    </li>
                    <li class="col-md-6">
                        <a href="<?= $links['agencies']; ?>">
                            <i class="fa fa-sitemap fa-lg"></i>
                            <span>{{ lang('text_agencies') }}</span>
                        </a>
                    </li>
                    <!--<li class="col-md-6">
                        <a href="<?= $links['withdrawls']; ?>">
                            <i class="fa fa-money fa-lg"></i>
                            <span>{{ lang('text_withdrawls') }}</span>
                        </a>
                    </li>-->
                </ul>
            </div>
        </div>

    </div>
</div>
<?php echo $footer; ?>
