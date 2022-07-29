<?php echo $header; ?>
<style>
    .newDomain span {
        display: none;
    }

    .newDomain:after {
        content: attr(data-text-cancel);
        display: block;
    }

    .ltr {
        direction: ltr;
    }

    .center {
        text-align: center;
    }

    .domains input[type=text] {
        /*width: 40%;*/
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
    <div class="box">
        <div class="heading">
            <h1>{{ lang('dedicated_domains_heading_title') }}</h1>
            <div class="buttons">
                <a data-text-cancel="{{ lang('button_cancel') }}"
                   class="button btn btn-primary" id="newDomain" >
                    <span>{{ lang('new_domain') }}</span>
                </a>
                <a href="<?= $links['settings']; ?>"
                   class="button btn btn-primary">
                    <span>{{ lang('text_app_settings') }}</span>
                </a>
            </div>
        </div>
        <div class="row">
            <div class="content col-md-12 hide" id="newDomainForm">
                <form action="<?= $links['newDomain']; ?>" method="POST">
                    <table class="table table-striped table-bordered table-hover">
                        <tr>
                            <td>
                                {{ lang('domain_name') }}
                                <span class="help">{{ lang('domain_name_note') }}</span>
                            </td>
                            <td><input class="ltr form-control" type="text"
                                       name="domain[name]" id="domainName"
                                /></td>
                        </tr>
                        <tr>
                            <td>{{ lang('default_currency') }}</td>
                            <td>
                                <select name="domain[currency]" id="currency">
                                    <?php foreach ($newDomain['currencies'] as $currency) { ?>
                                    <option value="<?= $currency['code']; ?>"><?= $currency['title']; ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>{{ lang('default_country') }}</td>
                            <td>
                                <select name="domain[country]" id="country">
                                    <option value="WWW">{{ lang('any_country') }}</option>
                                    <?php foreach ($newDomain['countries'] as $country) { ?>
                                    <option value="<?= $country['iso_code_2']; ?>"><?= $country['name']; ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>{{ lang('domain_status') }}</td>
                            <td>
                                <select name="domain[domain_status]">
                                    <option value="1">{{ lang('text_enabled') }}</option>
                                    <option value="0">{{ lang('text_disabled') }}</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" class="center">
                                <input type="submit"
                                       class="button btn btn-primary"
                                       id="submitNewDomain"
                                       value="{{ lang('button_insert') }}" />
                            </td>
                        </tr>
                    </table>
                    <input type="hidden" name="method" value="newDomain" />
                </form>
            </div>
            <div class="content col-md-12">
                <table class="table table-striped table-bordered domains">
                    <thead>
                    <tr>
                        <td class="col-md-1">#</td>
                        <td class="col-md-3">{{ lang('domain_name') }}</td>
                        <td class="col-md-2">{{ lang('default_currency') }}</td>
                        <td class="col-md-2">{{ lang('default_country') }}</td>
                        <td class="col-md-1">{{ lang('domain_status') }}</td>
                        <td class="col-md-3">{{ lang('text_options') }}</td>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($domains) { ?>
                    <?php foreach ($domains as $domain) { ?>
                    <tr id="domain_<?= $domain['domain_id']; ?>">
                        <td class="domainId" data-val="<?= $domain['domain_id']; ?>"><?= $domain['domain_id']; ?></td>
                        <td class="domainName"
                            data-original="<?= $domain['domain']; ?>"><?= $domain['domain']; ?></td>
                        <td class="defaultCurrency"
                            data-original="<?= $domain['currency']; ?>"><?= $domain['currency']; ?></td>
                        <td class="defaultCountry"
                            data-original="<?= $domain['country']; ?>">
                            <?php if ($domain['country'] != 'WWW') { ?>
                            <?= $newDomain['countries'][$domain['country']]['name']; ?>
                            <?php } else { ?>
                            {{ lang('any_country') }}
                            <?php } ?>
                        </td>
                        <td class="domainStatus"
                            data-original="<?= $domain['domain_status']; ?>"
                            data-enabled="{{ lang('domain_enabled') }}"
                            data-disabled="{{ lang('domain_disabled') }}">
                            <?php if ($domain['domain_status'] == 1) { ?>
                            {{ lang('domain_enabled') }}
                            <?php } else { ?>
                            {{ lang('domain_disabled') }}
                            <?php } ?>
                        </td>
                        <?php /*<td class="defaultCurrency">
                            <select name="domain[currency]" id="currency">
                                <?php foreach ($newDomain['currencies'] as $currency) { ?>
                                <option value="<?= $currency['code']; ?>"
                                        selected="<?= $domain['currency'] == $currency['code']; ? 'true' : 'false'; ?>"
                                ><?= $currency['title']; ?></option>
                                <?php } ?>
                            </select>
                        </td>*/ ;?>
                        <td>
                            <div style="overflow: hidden;" class="domainOptions">
                                <a class="button btn btn-primary inlineEdit">{{ lang('text_edit') }}</a>
                                <a class="button btn btn-primary"
                                   onclick="return confirm('{{ lang('remove_confirmation_message') }}');"
                                   href="<?= $links['removeDomain'] . '&domain_id=' . $domain['domain_id']; ?>"
                                >{{ lang('button_delete') }}</a>
                            </div>
                            <div class="saveOptions" style="display: none;overflow: hidden;">
                                <a class="button btn btn-primary inlineSave">{{ lang('button_save') }}</a>
                                <a class="button btn btn-primary inlineCancel">{{ lang('button_cancel') }}</a>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php } else { ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">{{ lang('text_no_results') }}</td>
                    </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="currencies" value="<?= htmlspecialchars(json_encode($newDomain['currencies'])); ?>" />
<input type="hidden" id="countries" value="<?= htmlspecialchars(json_encode($newDomain['countries'])); ?>" />
<input type="hidden" id="updateDomainLink" value="<?= $links['updateDomainLink']; ?>" />
<input type="hidden" id="invalidEmailError" value="{{ lang('error_invalid_domain_name') }}" />
<input type="hidden" id="anyCountryString" value="{{ lang('any_country') }}" />
<script src="view/javascript/modules/dedicated_domains/domains.js"></script>
<?php echo $footer; ?>
