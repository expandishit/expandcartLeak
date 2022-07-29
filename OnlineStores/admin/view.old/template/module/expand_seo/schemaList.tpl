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
                <a onclick="$('#newSchemaForm').toggleClass('hide'); $(this).toggleClass('newSchema');"
                   data-close="{{ lang('button_new_rule') }}"
                   class="button btn btn-primary newSchema tab_schema">
                    <span>{{ lang('button_cancel') }}</span>
                </a>
            </div>
        </div>
        <div class="content">
            <ul class="nav nav-tabs">
                <li><a href="<?= $settingsLink; ?>">{{ lang('es_settings') }}</a></li>
                <li class="tab active"><a href="#tab-alias" data-toggle="tab">{{ lang('es_alias') }}</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab-alias">
                    <form id="newSchemaForm" method="post" action="<?= $schemaAction; ?>" class="hide">
                        <input type="hidden" name="schema[status]" value="1" />
                        <table class="table table-striped table-bordered table-hover">
                            <?php /* ?><tr>
                                <td>{{ lang('entry_status') }} :</td>
                                <td>
                                    <select class="form-control" name='schema[status]'>
                                        <option value='1'
                                        <?= ($expand_seo['settings']['es_products_status'] == '1' ? 'selected' : ''); ?>
                                        >{{ lang('text_enable') }}</option>
                                        <option value='2'
                                        <?= ($expand_seo['settings']['es_products_status'] != '1' ? 'selected' : ''); ?>
                                        >{{ lang('text_disable') }}</option>
                                    </select>
                                </td>
                            </tr><?php */ ?>
                            <tr>
                                <td>{{ lang('es_entry_schema_group') }}</td>
                                <td>
                                    <select class="form-control" id="schema_group" name="schema[group]">
                                        <?php foreach($expand_seo['fields'] as $key => $group) { ?>
                                        <option value="<?= $key; ?>"><?= $group['name']; ?></option>
                                        <?php } ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>{{ lang('es_entry_spesify_language') }}</td>
                                <td>
                                    <select class="form-control" id="seo_language" name="schema[language]">
                                        <option value="global">{{ lang('all_languages') }}</option>
                                        <?php foreach($expand_seo['languages'] as $key => $language) { ?>
                                        <option value="<?= $language; ?>"><?= $language; ?></option>
                                        <?php } ?>
                                    </select>
                                </td>
                            </tr>
                            <tr class="schema_language">
                                <td>{{ lang('es_entry_schema_language') }}</td>
                                <td>
                                    <select class="form-control" id="seo_schema_language" disabled name="schema[fields_language]">
                                        <?php foreach($expand_seo['languages'] as $key => $language) { ?>
                                        <option value="<?= $language; ?>"><?= $language; ?></option>
                                        <?php } ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>{{ lang('es_entry_prefix') }} :</td>
                                <td><input class="ltr form-control" id="product_prefix"
                                           value="<?= $expand_seo['products']['prefix']; ?>"
                                           type="text" name="schema[prefix]"/></td>
                            </tr>
                            <tr>
                                <td>
                                    <span>{{ lang('es_url_schema') }}</span>
                                    <span class="help">{{ lang('es_url_schema_note') }}</span>
                                </td>
                                <td width="70%">
                                    <div class="schema_elements schema_elements_style" style="display: none;">
                                        <div class="separators inline_block">
                                            <?php foreach ($expand_seo['separators'] as $key => $separator) { ?>
                                            <span class="separator" onclick="appendElement(this)"
                                                  data-parent="separators" data-unique="no"
                                                  data-value="<?= $separator; ?>"><?= $separator; ?></span>
                                            <?php } ?>
                                        </div>
                                        <div class="schema_parts inline_block">
                                            <?php /*foreach ($expand_seo['fields']['product/product']['fields'] as $key => $field) { ?>
                                            <span onclick="appendElement(this)"
                                                  data-parent="schema_parts" data-unique="no"
                                                  data-value="<?= $key; ?>"><?= $field; ?></span>
                                            <?php } */?>
                                        </div>
                                        <!--<div class="languages inline_block">
                                        <span class="language" onclick="appendElement(this)" data-value="<?= $expand_seo['languages'][0]; ?>"><?= $expand_seo['languages'][0]; ?></span>
                                        </div>-->
                                        <?php /* ?><div class="languages inline_block">
                                            <?php foreach ($expand_seo['languages'] as $key => $language) { ?>
                                            <span class="language" onclick="appendElement(this)"
                                                  data-parent="languages" data-unique="yes"
                                                  data-value="<?= $language; ?>"><?= $language; ?></span>
                                            <?php } ?>
                                        </div><?php */ ?>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    {{ lang('es_text_final_url') }}
                                    <span class="help">{{ lang('es_text_final_url_note') }}</span>
                                </td>
                                <td>
                                    <div class="url_schema_container ltr">
                                        <div class="url_schema inline_block schema_elements_style">
                                        <span>
                                            <?= $domainName; ?><div class="_languages inline_block">
                                                <?= $expand_seo['settings']['es_append_language'] == 1 ? '{language}/' : '' ?>
                                            </div><div class="prefix inline_block"></div>
                                        </span>
                                        </div>
                                        <div class="ltr schema_elements_style" id="url_schema_div"></div>
                                        <div class="ltr" id="hidden_url_schema_div"></div>
                                    </div>
                                </td>
                            </tr>
                            <tfoot>
                            <tr>
                                <td colspan="10">
                                    <div class="buttons">
                                        <a onclick="$('#newSchemaForm').submit();" class="button btn btn-primary">
                                            {{ lang('button_save') }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            </tfoot>
                            <input type="hidden" name="formType" value="schema" />
                        </table>
                    </form>
                    <table class="schemaTable table table-hover dataTable no-footer">
                        <thead>
                        <tr>
                            <td>#</td>
                            <td>{{ lang('es_language') }}</td>
                            <td>{{ lang('entry_status') }}</td>
                            <td style="text-align: center;">{{ lang('text_options') }}</td>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (count($expand_seo['schemas']) > 0) { ?>
                        <?php foreach ($expand_seo['schemas'] as $group => $schemas) { ?>
                        <tr>
                            <td colspan="4" class="group_name center">
                                <?= $group; ?>
                            </td>
                        </tr>
                        <?php foreach($schemas as $schema) { ?>
                        <tr>
                            <td><?= $schema['seo_id']; ?></td>
                            <td><?= $schema['language']; ?></td>
                            <td><?= ($schema['schema_status'] == 1 ? $text_enabled : $text_disabled); ?></td>
                            <td class="options_container">
                                <?php
                                $parameters = json_decode($schema['schema_parameters'], true);
                                ?>
                                <div class="ltr url_container schema_elements_style">
                                    <span>
                                        <?= trim($domainName . ($expand_seo['settings']['es_append_language'] == 1 ? '{language}/' : '') . $schema['schema_prefix'], '/'); ?>/
                                    </span>

                                    <?php foreach ($parameters as $parameter) { ?>
                                    <span><?= str_replace('_', ' ', $parameter); ?></span>
                                    <?php } ?>
                                </div>
                            </td>
                            <td>
                                <div class="buttons_container">
                                    <!--<a href="<?= $links['viewSchema']; ?>&id=<?= $schema['seo_id']; ?>"
                                       class="btn btn-primary"
                                       data-toggle="tooltip"
                                       data-original-title="{{ lang('es_view') }}">
                                        <i class="fa fa-eye"></i>
                                    </a>-->
                                    <a href="<?= $links['removeSchema']; ?>&id=<?= $schema['seo_id']; ?>"
                                       class="btn btn-primary"
                                       data-toggle="tooltip"
                                       data-original-title="{{ lang('es_delete') }}">
                                        <i class="fa fa-trash-o"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                        <tr class="tr_separator">
                            <td colspan="6"></td>
                        </tr>
                        <?php } ?>
                        <?php } else { ?>
                        <tr>
                            <td colspan="5">{{ lang('text_no_results') }}</td>
                        </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="schema_fields" value="<?= $expand_seo['json_fields']; ?>" />
<script>
    $('#schema_group').change(function () {
        var thisGroup = $(this).val();

        $('#url_schema_div').empty();
        $('#hidden_url_schema_div').empty();
        $('#product_prefix').val('');
        $('.url_schema .prefix').empty();

        var fields = JSON.parse($('#schema_fields').val());

        var schema_parts = $('.schema_parts');

        var spanHtml = '';

        var group = fields[thisGroup]['fields'];
        var name= fields[thisGroup]['name'];

        if (group) {
            $('.schema_elements').show();
            $('#seo_schema_language').attr('disabled', false);
            for (elementIndex in group) {
                spanHtml += '<span onclick="appendElement(this)"' +
                    ' data-parent="schema_parts" data-unique="no"' +
                    ' data-value="' + elementIndex + '">' + group[elementIndex] + '</span>';
            }
        } else {
            $('.schema_elements').hide();
            $('#seo_schema_language').attr('disabled', true);
        }

        schema_parts.html(spanHtml);
    });

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
            'id="url_schema_' + me.data('value') + '_' + hiddenInputsIndex + '" ' +
            'type="hidden" ' +
            'value="' + me.data('value') + '" ' +
            'name="schema[parts]['+me.data('parent')+'][' + hiddenInputsIndex + ']" ' +
            'class="' + me.attr('class') + '"' +
            '/>';

        var valueSpan = '<div class="inline_block"><span ' +
            'id="url_schema_' + me.data('value') + '_div" ' +
            'onclick="removeElement(this)" ' +
            'data-input="url_schema_' + me.data('value') + '_' + hiddenInputsIndex + '" ' +
            'data-parent="' + me.data('parent') + '" ' +
            'data-unique="' + me.data('removal') + '" ' +
            'class="' + me.attr('class') + '"' +
            '>' + me.text() + '</span></div>';

        hiddenInputsIndex++;

        $('#hidden_url_schema_div').append(value);


        if (me.data('unique') == 'yes') {
            var parent = me.parent();
            parent.hide();
            $('.url_schema ._languages').html(valueSpan);
        } else {
            $('#url_schema_div').append(valueSpan);
        }
    }

    $('#product_prefix').keyup(function () {
        var me = $(this);

        var domain = $('#domainName').data('value');

        var val = me.val();
        var newVal = '';
        var matches;

        if (matches = val.match(/^(?:[a-z]+[0-9a-z\_\-\/]*)?$/i)) {
            if (matches[0].length > 0) {
                newVal = val;
            }
        }

        $('.url_schema .prefix').html(newVal);
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
                window.location.reload();
            }
        });
    });
</script>
<?php echo $footer; ?>
