<!-- 
{% if moduleData['PopupWindow'] %}
    {% for popup in moduleData['PopupWindow'] %}
    {% set popup_name = 'PopupWindow' ~ '[PopupWindow][' ~ popup['id'] ~ ']' %}
    {% if moduleData['PopupWindow'][popup['id']] %}
        {% set popup_data = moduleData['PopupWindow'][popup['id']] %}
    {% else %}
        {% set popup_data = [] %}
    {% endif %}



    % endfor %}
    {% endif %} -->
<?php echo $header;?>
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

    <?php if (!empty($this->session->data['success'])) { ?>
    <script>
        var notificationString = '<?php echo $this->session->data['success']; ?>';
        var notificationType = 'success';
    </script>
    <?php $this->session->data['success'] = null; } ?>

    <div class="box">
        <div class="heading">
            <h1><?php echo $heading_title; ?></h1>
            <div class="buttons">
                <a onclick="$('#form').submit();" class="button btn btn-primary"><?php echo $save_changes; ?></a>
                <a href="<?php echo $cancel; ?>" class="button btn btn-danger"><?php echo $button_cancel; ?></a>
            </div>
        </div>
        <div class="content">
            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
                <div id="control_panel"><?php require_once(DIR_APPLICATION.'view/template/module/popupwindow/tab_controlpanel.php'); ?></div>
            </form>
        </div>
    </div>
</div>

<?php echo $footer; ?>