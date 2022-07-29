$(document).ready(function () {

    var invalidEmailError = $('#invalidEmailError').val();

    $('#newDomain').click(function (event) {
        event.originalEvent.defaultPrevented;

        $(this).toggleClass('newDomain');

        $('#newDomainForm').toggleClass('hide');

    });

    var domainValidator = function (domainName) {
        if (domainName.match(/^(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/g)) {
            return true;
        }

        return false;
    };

    $('#submitNewDomain').submit(function (event) {
        // event.originalEvent.defaultPrevented;
        event.preventDefault();

        var that = $(this);

        var domainName = $('#domainName').val();
        var currency = $('#currency').val();

        if (domainValidator(domainName)) {
            that.attr('disabled', true);
            $('#newDomainForm form').submit();
        } else {
            alert(""+invalidEmailError+"");
        }
    });

    var inlineCancel = function(that)
    {
        var _parent = that.parent().parent().parent();

        var anyCountryString = $('#anyCountryString').val();

        var domain = $('.newDomainName', _parent).val();
        var domain = $('.domainName', _parent).data('original');
        var currency = $('.newCurrency', _parent).val();
        var currency = $('.defaultCurrency', _parent).data('original');
        var countries = JSON.parse($('#countries').val());
        var country = $('.defaultCountry', _parent).data('original');
        if (country != 'WWW') {
            country = countries[country]['name'];
        } else {
            country = anyCountryString;
        }
        var domainStatus = $('.domainStatus', _parent).data('original');

        var text_enabled = $('.domainStatus', _parent).data('enabled');
        var text_disabled = $('.domainStatus', _parent).data('disabled');

        if (domainStatus == 1) {
            $('.domainStatus', _parent).html(text_enabled);
        } else {
            $('.domainStatus', _parent).html(text_disabled);
        }

        $('.domainName', _parent).html(domain);

        $('.defaultCurrency', _parent).html(currency);
        $('.defaultCountry', _parent).html(country);

        that.parent().hide('slide', {direction: 'up'}, function () {
            $('.domainOptions', _parent).show('slide', {direction: 'up'});
        });
    };

    $('.inlineEdit').click(function (event) {
        event.preventDefault();

        var that = $(this);
        var _parent = that.parent().parent().parent();

        var domain = $('.domainName', _parent).html();
        var currency = $('.defaultCurrency', _parent).html();
        var domainStatus = $('.domainStatus', _parent).data('original');

        var currencies = JSON.parse($('#currencies').val());
        var countries = JSON.parse($('#countries').val());
        var country = $('.defaultCountry', _parent).data('original');
        if (country != 'WWW') {
            country = countries[country]['iso_code_2'];
        }
        $('.domainName', _parent).html(
            '<div style="direction: ltr;">' +
            'http[s]://<input type="text" class="newDomainName" value="'+domain+'" style="width: 50%;" />' +
            '</div>'
        );

        var text_enabled = $('.domainStatus', _parent).data('enabled');
        var text_disabled = $('.domainStatus', _parent).data('disabled');

        $('.domainStatus', _parent).html(
            '<select class="newDomainStatus">' +
            '<option value="1" '+(domainStatus == 1 ? 'selected' : '')+'>'+text_enabled+'</option>' +
            '<option value="0" '+(domainStatus != 1 ? 'selected' : '')+'>'+text_disabled+'</option>' +
            '</select>'
        );

        var currenciesOptions = '';

        for (_currency in currencies) {
            currenciesOptions += '<option value="' + currencies[_currency]['code'] + '"' +
                (currency == _currency ? 'selected="true"' : '') +
                '>' +
                currencies[_currency]['title'] +
                '</option>';
        }

        $('.defaultCurrency', _parent).html(
            '<select class="newCurrency" value="'+currency+'">' +
            currenciesOptions +
            '</select>'
        );

        var countriesOptions = '';

        var anyCountryString = $('#anyCountryString').val();

        for (_country in countries) {
            countriesOptions += '<option value="' + countries[_country]['iso_code_2'] + '"' +
                (country == _country ? 'selected="true"' : '') +
                '>' +
                countries[_country]['name'] +
                '</option>';
        }

        $('.defaultCountry', _parent).html(
            '<select class="newCountry" value="'+country+'">' +
            '<option value="WWW">'+anyCountryString+'</option>' +
            countriesOptions +
            '</select>'
        );

        that.parent().hide('slide', {direction: 'up'}, function () {
            $('.saveOptions', _parent).show('slide', {direction: 'up'});
        });
    });

    $('.inlineCancel').click(function (event) {
        event.preventDefault();
        var that = $(this);
        inlineCancel(that);
    });

    $('.inlineSave').click(function (event) {
        event.preventDefault();

        var that = $(this);

        var _parent = that.parent().parent().parent();

        var domain = $('.newDomainName', _parent).val();
        var currency = $('.newCurrency', _parent).val();
        var country = $('.newCountry', _parent).val();
        var domainId = $('.domainId', _parent).data('val');
        var domainStatus = $('.newDomainStatus', _parent).val();

        var data = {
            domain: {
                name: domain,
                currency: currency,
                country: country,
                domain_status: domainStatus
            },
            method: 'updateExist',
            domainId: domainId
        };

        var updateDomainLink = $('#updateDomainLink').val();

        if (domainValidator(domain)) {

            $.ajax({
                url: updateDomainLink,
                data: data,
                method: 'POST',
                dataType: 'json',
                success: function (response) {
                    location.reload();
                }
            })

        } else {
            alert("" + invalidEmailError + "");
        }
    });
});