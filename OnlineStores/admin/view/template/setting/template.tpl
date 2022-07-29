<?php include_jsfile($header, $footer, 'view/template/setting/template.tpl.js'); ?>

<?php echo $header; ?>
<?php
    function GetTemplateImageURL($name) {
        if (file_exists('../image/templates/' . $name . '.png')) {
            $image = HTTP_CATALOG . 'image/templates/' . $name . '.png';
        } else if (file_exists('../image/templates/' . $name . '.jpg')) {
            $image = HTTP_CATALOG . 'image/templates/' . $name . '.jpg';
        } else {
            $image = HTTP_CATALOG . 'image/no_image.jpg';
        }

        return $image;
    }
?>

<script>
    var changeTemplateURL = 'index.php?route=setting/template/applytemplate&token=<?php echo $token; ?>';
    var txtApplyTemplate = '<?php echo $text_apply_template; ?>';
    var txtAlreadyApplied = '<?php echo $text_already_applied; ?>';
    var txtTemplateChanged = '<?php echo $text_template_changed; ?>';
    var text_reset_templ = '<?php echo $text_reset_templ; ?>';
    var text_template_reset = '<?php echo $text_template_reset; ?>';

    var selLang = '<?php echo $direction; ?>';
    var callout_title = '<?php echo $text_callout_title; ?>';
    var callout_content = '<?php echo $text_callout_content; ?>';
</script>

<div class="md-modal md-effect-11" id="modal-reset-def">
    <div class="md-content">
        <div class="modal-header red-bg">
            <button class="md-close close">×</button>
            <h4 class="modal-title"><?php echo $entry_reset_def_title; ?></h4>
        </div>
        <div class="modal-body">
            <p><?php echo $entry_reset_message; ?></p>

            <p><?php echo $entry_reset_message_conf; ?></p>
        </div>
        <div class="modal-footer">
            <span class="server-loading"><i class="fa fa-refresh fa-spin"></i></span>
            <button type="button" class="btn btn-danger" tempName="" id="reset-templ"><?php echo $entry_reset_button; ?></button>
            <button type="button" class="btn btn-default" onclick="$(this).parents('.md-show').removeClass('md-show');"><?php echo $entry_reset_cancel; ?></button>
        </div>
    </div>
</div>

<div class="row" id="templateSelection">
<div class="col-lg-12">

<div class="row">
    <div class="col-lg-12">
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

        <h1><?php echo $heading_title; ?></h1>
    </div>
</div>

<?php if($templates['nextgen']) { ?>
    <!-- Next Gen Templates-->
    <div class="row">
        <h2><?php echo $text_nextgen_templates; ?></h2>
        <?php foreach($templates['nextgen'] as $template) { ?>
        <div class="col-lg-4 col-md-6 col-sm-6 template-box" id="<?php echo $template['CodeName']; ?>">
            <div class="main-box clearfix project-box <?php echo $template['CodeName'] == $config_template ? 'emerald-bg' :  'green-box'; ?>">
                <div class="main-box-body clearfix">
                    <div class="project-box-header <?php echo $template['CodeName'] == $config_template ? 'emerald-bg' :  'green-bg'; ?>">
                        <div class="name">
                            <a>
                                <?php if ($template['CodeName'] == $config_template) { ?>
                                <button id="reset" type="submit" class="md-trigger btn btn-danger resetDefault pull-right"
                                        data-modal="modal-reset-def"
                                        data-container="body"
                                        tempName="<?php echo $template['CodeName']; ?>"><?php echo $text_reset_templ; ?></button>
                                <?php } ?>
                                <?php echo $template['Name']; ?>
                            </a>
                        </div>
                    </div>

                    <div class="project-box-content">
                        <div class="scroll-back-image" style="background-image: url(<?php echo GetTemplateImageURL($template['CodeName']); ?>);"></div>
                    </div>

                    <div class="project-box-ultrafooter clearfix">
                        <?php if ($template['CodeName'] == $config_template) { ?>
                        <div id="apply" class="label label-success label-large"><?php echo $text_already_applied; ?></div>
                        <?php } else { ?>
                        <div id="apply" class="btn btn-success" onclick="Pace.restart();changeTemplate(this);"><?php echo $text_apply_template; ?></div>
                        <?php } ?>

                    </div>
                </div>
            </div>
        </div>
        <?php } ?>

    </div>
<?php } ?>

<div class="row">
<?php if($templates['legacy']) { ?><h2><?php echo $text_legacy_templates; ?></h2><?php } ?>
<?php foreach($templates['legacy'] as $template) { ?>
    <div class="col-lg-4 col-md-6 col-sm-6 template-box" id="<?php echo $template['CodeName']; ?>">
        <div class="main-box clearfix project-box <?php echo $template['CodeName'] == $config_template ? 'emerald-bg' :  'green-box'; ?>">
            <div class="main-box-body clearfix">
                <div class="project-box-header <?php echo $template['CodeName'] == $config_template ? 'emerald-bg' :  'green-bg'; ?>">
                    <div class="name">
                        <a>
                            <?php if ($template['CodeName'] == $config_template) { ?>
                                <button id="reset" type="submit" class="md-trigger btn btn-danger resetDefault pull-right"
                                     data-modal="modal-reset-def"
                                     data-container="body"
                                     tempName="<?php echo $template['CodeName']; ?>"><?php echo $text_reset_templ; ?></button>
                            <?php } ?>
                            <?php echo $template['Name']; ?>
                        </a>
                    </div>
                </div>

                <div class="project-box-content">
                    <img src="<?php echo GetTemplateImageURL($template['CodeName']); ?>" alt="" style="height: 200px;">
                </div>

                <div class="project-box-ultrafooter clearfix">
                    <?php if ($template['CodeName'] == $config_template) { ?>
                        <div id="apply" class="label label-success label-large"><?php echo $text_already_applied; ?></div>
                    <?php } else { ?>
                        <div id="apply" class="btn btn-success" onclick="Pace.restart();changeTemplate(this);"><?php echo $text_apply_template; ?></div>
                    <?php } ?>

                </div>
            </div>
        </div>
    </div>
<?php } ?>

</div>

</div>
</div>



<div class="md-overlay"></div><!-- the overlay element -->
<?php echo $footer; ?>