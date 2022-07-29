<?php echo $header; ?>
<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,300,600" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">

<div id="content" class="ms-account-profile">
    <?php echo $content_top; ?>

    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
        <?php } ?>
    </div>

    <h1 class="maintitle">{{ lang('ms_account_subscriptions_heading') }}</h1>

    <?php if($ms_subscriptions_error){ ?>
        <div class="alert alert-danger"><?php echo $ms_subscriptions_error ?></div>
    <?php }?>

    <?php if (isset($success) && ($success)) { ?>
    <div class="success"><?php echo $success; ?></div>
    <?php } ?>

    <?php if (isset($statustext) && ($statustext)) { ?>
    <div class="<?php echo $statusclass; ?>"><?php echo $statustext; ?></div>
    <?php } ?>

    <p class="warning main"></p>

    <form id="ms-subscriptions" class="ms-form">
        <div class="wrapper">
            <?php if ($subscriptions['plans']) { ?>
            <div class="packages">
                <?php foreach($subscriptions['plans'] as $key => $plan) { ?>
                <label class="package <?= $current_plan == $plan['plan_id'] ? 'brilliant' : ''; ?>">
                    <div class="name"><?= $plan['title']; ?></div>
                    <span class="price"><?= $plan['formatted_payment']; ?> / </span>
                    <span class="price" data-payment=""><?= $plan['formated_price']; ?></span>
                    <div class="discription"><?= html_entity_decode($plan['description']); ?></div>
                    <?php /* ?>
                    <div class="trial">Totally free</div>
                    <hr>
                    <ul>
                        <li>
                            <strong>5</strong>
                            team members
                        </li>
                        <li>
                            <strong>3</strong>
                            team playlists
                        </li>
                        <li>
                            <strong>Unlimited</strong>
                            public playlists
                        </li>
                    </ul>
                    <?php */ ?>
                    <div class="radio">
                        <input type="radio" name="seller[subscription_plan]"
                               value="<?= $plan['plan_id']; ?>"
                               data-value="<?= $plan['plan_id']; ?>"
                               data-name="<?= $plan['title']; ?>"
                               data-amount="<?= $plan['price']; ?>"
                               data-usdamount="<?= $plan['usd_price']; ?>"
                               data-quantity="1"
                               class="subscription_plan"
                               id="subscription_plan"
                               <?= $current_plan == $plan['plan_id'] ? 'checked="checked"' : ''; ?>
                        />
                    </div>
                </label>
                <?php } ?>
            </div>

            <div class="clear"></div>

            <div class="paymentMethod">
                <table class="paymentMethodTable">
                    <tr>
                        <td>{{ lang('payment_method') }}</td>
                        <td>
                            <select name="seller[payment_method]" id="payment_method">
                                <?php if ($payment_methods['paypal'] != '') { ?>
                                <option value="1">{{ lang('ms_payment_method_paypal') }}</option>
                                <?php } ?>
                                <?php if ($payment_methods['bank'] != '') { ?>
                                <option value="2">{{ lang('ms_payment_method_bank') }}</option>
                                <?php } ?>
                                <?php if ($payment_methods['mastercard']) { ?>
                                <option value="3">{{ lang('ms_payment_method_mastercard') }}</option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>

                    <tr class="bank_statement_details" style="display: none;">
                        <td>{{ lang('ms_bank_details') }}</td>
                        <td><?= $subscriptions['bank_details']; ?></td>
                    </tr>

                    <tr class="paypal_recurring_details">
                        <td>{{ lang('paypal_recurring_subscription') }}</td>
                        <td><input type="checkbox" name="isRecurring" id="isRecurring" value="1" /></td>
                    </tr>

                    <!--/// Mastercard  -->
                    <?php if ($payment_methods['mastercard']) { ?>
                        <tr class="mastercard_details" style="display: none;">
                            <td>{{ lang('mastercard_cardNumber') }}</td>
                            <td>
                                <input type="text" id="vpc_card_1" value="">
                            </td>
                        </tr>
                        <tr class="mastercard_details" style="display: none;">
                            <td>{{ lang('mastercard_expire') }}</td>
                            <td>
                                <select id="vpc_month_1">
                                      <?php foreach($months as $month) { ?>
                                        <option value="<?php echo $month['value']; ?>"><?php echo $month['text']; ?></option>
                                      <?php }?>
                                </select>
                                /
                                <select id="vpc_year_1">
                                      <?php foreach($years as $year) { ?>
                                        <option value="<?php echo $year['value']; ?>"><?php echo $year['text']; ?></option>
                                      <?php }?>
                                </select>
                            </td>
                        </tr>
                        <tr class="mastercard_details" style="display: none;">
                            <td>{{ lang('mastercard_cvv') }}</td>
                            <td>
                                <input type="text" id="vpc_cvv_1" value="">
                            </td>
                        </tr>
                    <?php }?>

                </table>
            </div>

            <?php } ?>
        </div>


    </form>

    <div id="warning" class="warning" style="display: none;"></div>

    <div class="buttons">
        <div class="left">
            <a href="<?php echo $link_back; ?>" class="button">
                <span><?php echo $button_back; ?></span>
            </a>
        </div>

        <?php if ($seller['ms.seller_status'] != MsSeller::STATUS_DISABLED && $seller['ms.seller_status'] != MsSeller::STATUS_DELETED) { ?>
        <div class="right">
            <a class="button" id="ms-submit-button">
                <span><?php echo $ms_button_save; ?></span>
            </a>
        </div>
        <?php } ?>
    </div>
    <?php echo $content_bottom; ?>
</div>

<form action="https://www.paypal.com/cgi-bin/webscr" id="pp_standard_form" method="post" style="display: none;">
    <input type="hidden" name="cmd" id="__cmd" value="_cart">
    <input type="hidden" name="upload" value="1" />
    <input type="hidden" name="business" value="<?= $business; ?>">
    <input type="hidden" name="item_name_1" value="Seller Subscription Plan">
    <input type="hidden" name="item_number_1" id="item_number_1" value="">
    <input type="hidden" name="amount_1" id="amount_pp" value="3">
    <input type="hidden" name="quantity" id="quantity" value="1">
    <input type="hidden" name="currency_code" value="USD">
    <input type="hidden" class="return_url" name="return" value="">
    <input type="image" name="submit"
           src="https://www.paypalobjects.com/en_US/i/btn/btn_buynow_LG.gif"
           alt="PayPal - The safer, easier way to pay online">
</form>

<form action="<?php echo $mastercard_action ?>" id="mastercard_form" method="post" style="display: none;">
    <input type="text" id="vpc_card" name="vpc_card" value="">
    <input type="text" id="vpc_year" name="vpc_year" value="">
    <input type="text" id="vpc_month" name="vpc_month" value="">
    <input type="text" id="vpc_cvv" name="vpc_cvv" value="">
    <input type="hidden" name="amount_1" id="amount_mc" value="">
    <input type="hidden" name="return" class="return_url" value="">
    <input type="hidden" name="errorurl" value="<?php echo $errorurl ?>">
</form>

<?php $timestamp = time(); ?>
<script>
    var msGlobals = {
        subscriptionPaymentMethod: '<?= $subscriptions["payment_method"] ?>',
        undefinedErrorMessage: '<?= $subscriptions["undefined_error_message"] ?>'
    };

    var paypalVariables = {
        business: '<?= $business; ?>',
        item_name: 'Seller Subscription Plan',
        currency_code: '<?= $currency; ?>',
        return_url: '<?= $return_url; ?>',
        cancel_url: '<?= $cancel_url; ?>'
    };
</script>

<script type="text/javascript">
    $('.package').click(function (e) {
        $('.package').removeClass('brilliant');
        $(this).addClass('brilliant');
    });

    var currentPlan = '<?= $current_plan; ?>';
    var expired     = '<?= $expired; ?>';

    $('#ms-submit-button').click(function (e) {
        var planDetails = $('#subscription_plan:checked');
        var isRecurring = $('#isRecurring').is(':checked');
        
        if (expired == 0 && planDetails.data('value') == currentPlan) {
            $('#warning').show();

            setTimeout(() => {
                $('#warning').hide()
            }, 10000);

            $('#warning').html('{{ lang('error_same_plan') }}');

            return;
        }

        if (typeof planDetails.data('amount') == 'undefined') {

            $('#warning').show();

            setTimeout(() => {
                $('#warning').hide()
            }, 10000);

            $('#warning').html('{{ lang('empty_plan') }}');

            return;
        }

        var data = $('#ms-subscriptions').serialize() + '&seller[price]=' + planDetails.data('amount') +
            '&seller[isRecurring]=' + (isRecurring ? '1' : '0');

        $.ajax({
            url: '<?= $location; ?>',
            data: data,
            dataType: "json",
            type: 'POST',
            success: function(response) {

                if (response.errors) {

                } else {
                    var paymentMethod = $('#payment_method').val();
                    var planDetails = $('#subscription_plan:checked');

                    if (paymentMethod === '1' && parseFloat(planDetails.data('usdamount')) > 0 ) {
                        $('#item_number_1').val(planDetails.data('value'));
                        $('#amount_pp').val(planDetails.data('usdamount'));
                        $('#quantity').val(planDetails.data('quantity'));
                        $('.return_url').val(response.redirect);
                        $('#__cmd').val(isRecurring ? '_xclick-subscriptions' : '_cart');

                        $('#pp_standard_form').submit();
                    }else if(paymentMethod === '3' && parseFloat(planDetails.data('amount')) > 0 ){
                        //Mastercard
                         $('#vpc_card').val($('#vpc_card_1').val());
                         $('#vpc_year').val($('#vpc_year_1').val());
                         $('#vpc_month').val($('#vpc_month_1').val());
                         $('#vpc_cvv').val($('#vpc_cvv_1').val());

                         $('#amount_mc').val(planDetails.data('amount'));
                         $('.return_url').val(response.redirect);
                         $('#mastercard_form').submit();
                    } else {
                        //Bank
                        window.location = response.redirect;
                    }
                }
            }
        });
    });

    $('#payment_method').change(function (e) {
        var val = $(this).val();

        if (val == '1') {
            $('.bank_statement_details').hide();
            $('.mastercard_details').hide();
            $('.paypal_recurring_details').show();
        } else if(val == '2'){
            $('.bank_statement_details').show();
            $('.paypal_recurring_details').hide();
            $('.mastercard_details').hide();
        }else if(val == '3'){
            $('.mastercard_details').show();
            $('.paypal_recurring_details').hide();
            $('.bank_statement_details').hide();
        }
    });
</script>
<?php echo $footer; ?>