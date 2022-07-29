$(document).ready(function () {

    $('#newPriceForDomain1111').click(function (event) {
        var that = $(this);

        var _ids = $('#dedicatedDomainsPrices select').map(function(){
            return this.value
        }).get();

        var url = that.data('url');

        $.ajax({
            url: url,
            data: {domains: _ids},
            method: 'POST',
            dataType: 'json',
            success: function (response) {

            }
        });
    });

    $('.close').click(function (event) {
        var that = $(this);

        that.parent().remove();

        if ($('#dedicatedDomainsPrices > div').length == 0) {
            $('#dedicatedDomainsPrices').html('<input type="hidden" name="domainData" value="empty" />');
        }
    });

    $('#newPriceForDomain').click(function (event) {

        var that = $(this);

        var _ids = $('#dedicatedDomainsPrices select').map(function() {
            return this.value
        }).get();

        var domains     = that.data('domains');
        var translation = that.data('translation');

        var lastDomain = $('.domainInputs').last();
        lastDomain = lastDomain.data('index');

        if (lastDomain == null) {
            lastDomain = 0;
        } else {
            lastDomain = lastDomain + 1;
        }

        var domainsCount = Object.keys(domains).length;

        var domainsTemplate = '<div class="domainInputs" data-index="' + lastDomain + '">'
            + translation['name'] + ' <select id="domainName" class="domainName" ' +
            'name="domainData[' + lastDomain + '][domain_id]">';

        var tmpCurrency = null;

        for (domain in domains) {
            var dedicatedDomain = domains[domain];

            if ($.inArray(dedicatedDomain['domain_id'], _ids) == -1) {
                domainsTemplate += '<option data-currency="' + dedicatedDomain['currency'] + '" ' +
                    'value="' + dedicatedDomain['domain_id'] + '">' +
                    dedicatedDomain['domain'] +
                    '</option>';

                if (!tmpCurrency) {
                    tmpCurrency = dedicatedDomain['currency']
                }
            }
        }

        domainsTemplate += '</select>';
        domainsTemplate += translation['price'] + ' <input type="text" id="domainPrice" ' +
            'name="domainData[' + lastDomain + '][price]" />';
        domainsTemplate += ' <span class="dedicatedCurrency dedicatedCurrency_' + lastDomain + '">' + tmpCurrency + '</span>';
        domainsTemplate += '  <div class="close">X</div>';
        domainsTemplate += '</div>';

        if (domainsCount > lastDomain) {
            $('#dedicatedDomainsPrices').append(domainsTemplate);
        }

        $('.domainName').change(function (event) {

            var that = $(this);

            var parent = that.parent();

            $('.dedicatedCurrency', parent).text(that.find(':selected').attr('data-currency'));
        });

        $('.close').click(function (event) {
            var that = $(this);

            that.parent().remove();

            if ($('#dedicatedDomainsPrices > div').length == 0) {
                $('#dedicatedDomainsPrices').html('<input type="hidden" name="domainData" value="empty" />');
            }
        });

    });

});