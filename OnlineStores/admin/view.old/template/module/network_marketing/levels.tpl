<?php echo $header; ?>

<div id="">
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
                <a onclick="$('#form').submit();"
                   class="button btn btn-primary tab_settings">
                    <span>{{ lang('button_save') }}</span>
                </a>
                <a href="<?= $links['cancel']; ?>"
                   class="button btn btn-primary">
                    <span>{{ lang('button_back') }}</span>
                </a>
            </div>
        </div>
        <form action="<?= $links['submit']; ?>" method="post" id="form">
            <div class="row">
                <div class="col-md-6">
                    <table style="border: 1px solid #ccc;" class="table content col-md-12">
                        <tr>
                            <td>{{ lang('levels_count') }}</td>
                            <td>
                                <span class="currentCount"><?= $currentCount; ?></span> {{ lang('text_of') }} 10
                                <input type="hidden" name="levels[levelsCount]" id="levelsCount" />
                            </td>
                        </tr>
                        <tr>
                            <td>{{ lang('commision_type') }}</td>
                            <td>
                                <select class="form-control" name='levels[settings][commision_type]' id="commision_type">
                                    <option value='1'
                                    <?= ($settings['commision_type'] == '1' ? 'selected' : ''); ?>
                                    >{{ lang('text_percentage') }}</option>
                                    <option value='2'
                                    <?= ($settings['commision_type'] == '2' ? 'selected' : ''); ?>
                                    >{{ lang('text_fixed') }}</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <table style="border: 1px solid #ccc;" class="table content col-md-12" id="newLevelForm">
                        <tr>
                            <td colspan="2"><h1>{{ lang('add_new_level') }}</h1></td>
                        </tr>
                        <tr>
                            <td>{{ lang('level_percentage') }}</td>
                            <td><input type="text" id="level_percentage" /></td>
                        </tr>
                        <tr>
                            <td>{{ lang('level_fixed') }}</td>
                            <td><input type="text" id="level_fixed" /></td>
                        </tr>
                        <tr>
                            <td colspan="4">
                                <div class="buttons">
                                    <a class="button btn btn-primary tab_settings" id="newLevel">
                                        <span>{{ lang('button_add_level') }}</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="border: 1px solid #ccc;" class="content col-md-6">
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <td>#</td>
                            <td>{{ lang('level_percentage') }}</td>
                            <td>{{ lang('level_fixed') }}</td>
                            <td>{{ lang('level_order') }}</td>
                        </tr>
                        </thead>
                        <tbody id="levels_body" >
                        <?php foreach ($levels as $level) { ?>
                        <tr>
                            <td><?= $level['level_id']; ?></td>
                            <td>
                                <input type="text" name="levels[levels][<?= $level['level_id']; ?>][percentage]"
                                       value="<?= $level['percentage']; ?>" />
                            </td>
                            <td>
                                <input type="text" name="levels[levels][<?= $level['level_id']; ?>][fixed]"
                                       value="<?= $level['fixed']; ?>" />
                            </td>
                            <td>
                                <input type="text" name="levels[levels][<?= $level['level_id']; ?>][order]"
                                       value="<?= $level['level_order']; ?>" class="level_order" />
                                <input type="hidden" name="levels[old][<?= $level['level_id']; ?>]"
                                       value="<?= $level['level_id']; ?>" />
                            </td>
                        </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>
</div>

<script>



    $('#newLevel').click(function () {

        if ($(this).hasClass('disabled')) {
            return false;
        }

        var _form = $('#newLevelForm');

        var level_percentage = $('#level_percentage').val();
        var level_fixed      = $('#level_fixed').val();

        var last_order = $('.level_order', $('#levels_body')).last().val();

        if (typeof last_order == 'undefined') {
            last_order = 0;
        }

        var tableLength = $('#levels_body tr').length;
        if (typeof tableLength == 'undefined') {
            tableLength = 0;
        }

        last_order++;
        tableLength++;

        var template = $([
            '<tr>',
            '<td>' + tableLength + '</td>',
            '<td><input type="text" name="levels[levels][' + tableLength + '][percentage]" value="' + level_percentage + '" /></td>',
            '<td><input type="text" name="levels[levels][' + tableLength + '][fixed]" value="' + level_fixed + '" /></td>',
            '<td>',
            '<input type="text" name="levels[levels][' + tableLength + '][order]" class="level_order" value="' + last_order + '" />',
            '<input type="hidden" name="levels[new][' + tableLength + ']" value="' + tableLength + '" />',
            '</td>',
            '</tr>',
        ].join("\n"));

        $('#levels_body').append(template);

        $('.currentCount').html(tableLength);
        $('#levelsCount').val(tableLength);

        if (tableLength == 10) {
            $(this).addClass('disabled');
        }

        $('#level_percentage').val('');
        $('#level_fixed').val('');
    });

    $('#commision_type').change(function () {
        var _val = $(this).val();

        $('.percentage').toggleClass('hide');
        $('.fixed').toggleClass('hide');

        if (_val == 1) {
            $('.inputs').attr('name', 'levels[percentage][]');

        } else {
            $('.inputs').attr('name', 'levels[fixed][]');
        }
    });
</script>
<?php echo $footer; ?>
