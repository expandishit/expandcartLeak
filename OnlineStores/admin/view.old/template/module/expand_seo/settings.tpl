<?php echo $header; ?>

<link rel="stylesheet" type="text/css" href="view/stylesheet/modules/expand_seo/custom.css"/>

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
    <div class="box">
        <div class="heading">
            <h1>{{ lang('es_heading_title') }}</h1>
            <div class="buttons">
                <a onclick="$('#form').submit();"
                   class="button btn btn-primary tab_settings">
                    <span>{{ lang('button_save') }}</span>
                </a>
                <a onclick="$('#newSchemaForm').toggleClass('hide'); $(this).toggleClass('newSchema');"
                   data-close="{{ lang('button_new') }}"
                   class="button btn btn-primary newSchema hide tab_schema">
                    <span>{{ lang('button_cancel') }}</span>
                </a>
            </div>
        </div>
        <div class="content">
            <ul class="nav nav-tabs">
                <li class="tab active" id="tab_settings"><a href="#tab-settings" data-toggle="tab">{{ lang('es_settings') }}</a></li>
                <li><a href="<?= $schemaList ?>">{{ lang('es_alias') }}</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab-settings">
                    <form action="<?= $schemaAction; ?>" method="post" enctype="multipart/form-data" id="form">
                        <table class="table table-striped table-bordered">
                            <tr>
                                <td>{{ lang('entry_status') }} :</td>
                                <td>
                                    <select class="form-control" name='settings[es_status]'>
                                        <option value='1'
                                        <?= ($expand_seo['settings']['es_status'] == '1' ? 'selected' : ''); ?>
                                        >{{ lang('text_enable') }}</option>
                                        <option value='2'
                                        <?= ($expand_seo['settings']['es_status'] != '1' ? 'selected' : ''); ?>
                                        >{{ lang('text_disable') }}</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    {{ lang('es_entry_redirect_301') }}
                                    <span class="help">{{ lang('es_entry_redirect_301_note') }}</span>
                                </td>
                                <td>
                                    <select class="form-control" name='settings[es_redirect]'>
                                        <option value='1'
                                        <?= ($expand_seo['settings']['es_redirect'] == '1' ? 'selected' : ''); ?>
                                        >{{ lang('text_enable') }}</option>
                                        <option value='2'
                                        <?= ($expand_seo['settings']['es_redirect'] != '1' ? 'selected' : ''); ?>
                                        >{{ lang('text_disable') }}</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    {{ lang('es_entry_url_with_language') }}
                                    <span class="help">{{ lang('es_entry_url_with_language_note') }}</span>
                                </td>
                                <td>
                                    <select class="form-control" name='settings[es_append_language]'>
                                        <option value='1'
                                        <?= ($expand_seo['settings']['es_append_language'] == '1' ? 'selected' : ''); ?>
                                        >{{ lang('text_enable') }}</option>
                                        <option value='2'
                                        <?= ($expand_seo['settings']['es_append_language'] != '1' ? 'selected' : ''); ?>
                                        >{{ lang('text_disable') }}</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        <input type="hidden" name="formType" value="settings" />
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var removeElement = function (that) {
        var me = $(that);

        var hiddenId = $(document.getElementById(me.data('input')));

        var $span = '<span ' +
            'onclick="appendElement(this)" ' +
            'data-value="' + hiddenId.val() + '"' +
            'data-unique="' + me.data('removal') + '"' +
            'data-parent="' + me.data('parent') + '"' +
            'class="' + me.attr('class') + '"' +
        '>' + me.text() + '</span>';

//        $('.' + me.data('parent')).append($span);
        $('.' + me.data('parent')).show();

        me.parent().remove();
        hiddenId.remove();
    };

    var hiddenInputsIndex = 0;

    var appendElement = function (that) {
        var me = $(that);

        var value = '<input ' +
            'id="url_schema_' + me.data('value') + '" ' +
            'type="hidden" ' +
            'value="' + me.data('value') + '" ' +
            'name="schema[parts]['+me.data('parent')+']['+hiddenInputsIndex+']" ' +
            'class="' + me.attr('class') + '"' +
        '/>';

        hiddenInputsIndex++;

        var valueSpan = '<div><span ' +
            'id="url_schema_' + me.data('value') + '_div" ' +
            'onclick="removeElement(this)" ' +
            'data-input="url_schema_' + me.data('value') + '" ' +
            'data-parent="' + me.data('parent') + '" ' +
            'data-unique="' + me.data('removal') + '" ' +
            'class="' + me.attr('class') + '"' +
            '>' + me.text() + '</span></div>';

        var org_val = $('#url_schema_div').html();

        $('#hidden_url_schema_div').append(value);
        $('#url_schema_div').append(valueSpan);

        if (me.data('unique') == 'yes') {
            var parent = me.parent();

            parent.hide();
        }
    }

    $('#product_prefix').keyup(function () {
        var me = $(this);

        var domain = $('#domainName').data('value');

        $('.url_schema > span > div').html(
            (me.val() != '' ? me.val() + '/' : '')
        );
    });

    $('#seo_language').change(function () {
        return false;
        var me = $(this);
        var textVal = me.find('option:selected').text();

        $('.schema_elements .language').data('value', me.val());
        $('.schema_elements .language').text(me.find('option:selected').text());
        $('#url_schema_div .language').text(me.find('option:selected').text());
        $('#hidden_url_schema_div .language').val(textVal);
        $('#hidden_url_schema_div .language').attr('id', 'url_schema_' + textVal);
        $('#url_schema_div .language').attr('id', 'url_schema_' + textVal + '_div');
        $('#url_schema_' + textVal + '_div').data('input', 'url_schema_' + textVal);
    });

    /*$('.tab').click(function () {
        var activeTab = $('.active').attr('id');

        $('.tab_settings').toggleClass('hide');
        $('.tab_schema').toggleClass('hide');
    });*/

    $('#newSchemaForm').submit(function (event) {
        event.preventDefault();

        var me = $(this);

        var action = me.attr('action');

        $.ajax({
            url: action,
            data: me.serialize(),
            method: 'post',
            success: function (resp) {
                alert(resp);
            }
        });
    });
</script>
<?php echo $footer; ?>
