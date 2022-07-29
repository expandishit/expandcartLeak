<?php
class ModelTotalOffFactor extends Model {
	public function getTotal(&$total_data, &$total, &$taxes) {
        $this->language->load_json('total/off_factor');
        
        $paymentDiscountStatus = $this->config->get('off_factor_status') ?? 0;
        $paymentDiscountCountries = $this->config->get('quickcheckout')['step']['payment_method']['off_factor_countries'] ?? [];
        $paymentDiscountPaymentMethods = $this->config->get('quickcheckout')['step']['payment_method']['off_factor_payment_methods'] ?? [];
        $paymentDiscountPercentage = (float) $this->config->get('quickcheckout')['step']['payment_method']['off_factor_percentage'] ?? 0;
        $paymentDiscountLimit = (float) $this->config->get('quickcheckout')['step']['payment_method']['off_factor_discount_limit'] ?? 0;

        if (
                $paymentDiscountStatus == 1 &&
                in_array($this->session->data['payment_country_id'], $paymentDiscountCountries) &&
                in_array($this->session->data['payment_method']['code'], $paymentDiscountPaymentMethods) &&
                $paymentDiscountPercentage > 0
           )
        {
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
        if(isset($amount) && $amount>0)
        {
        $this->session->data['off_factor_amount']=$amount;
        }
		$total_data[] = array(
			'code'       => 'off_factor',
			'title'      => $this->language->get('text_off_factor'),
			'text'       => $this->currency->format(max(0, $amount)),
//			'value'      => max(0, $total),
            'value'      => max(0, $amount),
			'sort_order' => $this->config->get('off_factor_sort_order')
		);
        //var_dump($amount);
        //var_dump($total);die;
	}
}
?>
