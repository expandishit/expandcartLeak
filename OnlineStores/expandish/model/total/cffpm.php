<?php
class ModelTotalCffpm extends Model
{
    public function getTotal(&$total_data, &$total, &$taxes)
    {
        $sub_total = $this->cart->getSubTotal();

        // load custom fees for payment method.
        $this->load->model('module/custom_fees_for_payment_method');

        // cffpm
        if ($this->model_module_custom_fees_for_payment_method->is_module_installed()) {

            $total_sum = (float) $total;

            $current_payment_method_code = $this->session->data['payment_method']['code'];

            // EC-5451 this check to fix the requests coming from the manual checkout / admin panel checkout
            if (
                !isset($this->session->data['payment_method']['code']) &&
                isset($this->request->post['payment_code'])
            ) {
                $current_payment_method_code = $this->request->post['payment_code'];
            }

            $payment_settings = $this->model_module_custom_fees_for_payment_method->get_setting($current_payment_method_code);
           
            $fees = explode(':', $payment_settings['fees']);
            $fees_value = (float) $fees[0];
            $fees_pcg = 'no';

            if (isset($fees[1]) && $fees[1] === 'pcg') {
                $fees_pcg = 'yes';
            }

            $fees_range = explode(':', $payment_settings['fees_range']);
            $fees_range_min = (float) $fees_range[0];
            $fees_range_max = $fees_range[1];

            if ($fees_range_max !== 'no_max') {
                $fees_range_max = (float) $fees_range_max;
            }

            if ($total_sum >= $fees_range_min && ($total_sum <= $fees_range_max || $fees_range_max === 'no_max')) {

                if ($fees_pcg === 'no') {
                    if ($this->session->data['shipping_method']['code'] != "pickup.pickup") {
                        $fees_text = $this->currency->format($fees_value);
                        $total_fees_value = $fees_value;
                        $total += $fees_value;
                    }
                } elseif ($fees_pcg === 'yes') {

                    if ($this->session->data['shipping_method']['code'] != "pickup.pickup") {
                        $total_fees_value = (float)($fees_value / 100) * $sub_total;
                        $fees_text = $this->currency->format($total_fees_value);
                        $total += $total_fees_value;
                    }
                }

                // safety check to ensure the user doesn't pay more than he should.
                // should never happen but you never know.
                else {
                    return false;
                }
                // fun part.
                // add the correct values to the total data.
                $current_lang = $this->session->data['language'];
                $title = $this->model_module_custom_fees_for_payment_method->get_setting_from_setting('cffpm_total_row_name_' . $current_lang)['value'];


                $total_data[] = array(
                    'code' => 'cffpm',
                    'title' => $title ? $title : 'Payment Method Fees',
                    'text' => $fees_text,
                    'value' => (float)$total_fees_value,
                    'sort_order' => (int)$this->config->get('cffpm_sort_order'),
                );
                $this->session->data['cffpm'] = (float)$total_fees_value;
                if(!empty($payment_settings['tax_class_id']) && $payment_settings['tax_class_id']!=0)
                {
                    $tax_rates = $this->tax->getRates($this->session->data['cffpm'], $payment_settings['tax_class_id']);
                    
                    foreach ($tax_rates as $tax_rate) {
                        if (!isset($taxes[$tax_rate['tax_rate_id']])) {
                            $taxes[$tax_rate['tax_rate_id']] = $tax_rate['amount'];
                        } else {
                            $taxes[$tax_rate['tax_rate_id']] += $tax_rate['amount'];
                        }
                    }
                }
            }
        }
    }
}
