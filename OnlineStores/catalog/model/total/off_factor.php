<?php

/**
 * Class ModelTotalOffFactor
 * This class resonsable for payment method discount.
 * depending on payment method type and client country
 */
class ModelTotalOffFactor extends Model {
    /**
     * check if the payment method type and countries
     * includes in this discount
     *
     * @param $total_data 'user_Data'
     * @param $total 'Total_amount'
     * @param $taxes 'order_Taxes'
     */
    public function getTotal(&$total_data, &$total, &$taxes) {
        $this->language->load('total/off_factor');

        /**
         * Get the payment method type and conutry
         */
        $paymentDiscountStatus = $this->config->get('off_factor_status') ?? 0;
        $paymentDiscountCountries = $this->config->get('quickcheckout')['step']['payment_method']['off_factor_countries'] ?? [];
        $paymentDiscountPaymentMethods = $this->config->get('quickcheckout')['step']['payment_method']['off_factor_payment_methods'] ?? [];
        $paymentDiscountPercentage = (float) $this->config->get('quickcheckout')['step']['payment_method']['off_factor_percentage'] ?? 0;
        $paymentDiscountLimit = (float) $this->config->get('quickcheckout')['step']['payment_method']['off_factor_discount_limit'] ?? 0;

        /**
         * check if payment method and country includs in the discount
         */
        if (
            $paymentDiscountStatus == 1 &&
            in_array($this->session->data['payment_country_id'], $paymentDiscountCountries) &&
            in_array($this->session->data['payment_method']['code'], $paymentDiscountPaymentMethods) &&
            $paymentDiscountPercentage > 0
        )
        {
            /**
             * calculate the total amonut after discount
             */
            $price = $total;
            if ($this->config->get('quickcheckout')['step']['payment_method']['off_factor_apply_on_shipping'] == 0)
            {
                $price = $total - $this->session->data['shipping_method']['cost'];
            }

            $amount = ($price * $paymentDiscountPercentage) / 100;
            if ($paymentDiscountLimit !== 0.0 && $paymentDiscountLimit < $amount) {
                $amount = $paymentDiscountLimit;
            }

            $total -= $amount;
        }

        /**
         * return the new data
         */
        $total_data[] = array(
            'code'       => 'off_factor',
            'title'      => $this->language->get('text_off_factor'),
            'text'       => $this->currency->format(max(0, $amount)),
            'value'      => max(0, $total),
            'sort_order' => $this->config->get('off_factor_sort_order')
        );
    }
}
?>
