<?php
class ModelTotalTotal extends Model {
	public function getTotal(&$total_data, &$total, &$taxes) {
		$this->language->load_json('total/total');
	 
		// // load custom fees for payment method.
        // $this->load->model('module/custom_fees_for_payment_method');

      
        // // cffpm
        // if ( $this->model_module_custom_fees_for_payment_method->is_module_installed() )
        // {

        //     $total_sum = (float) $total;

        //     $current_payment_method_code = $this->session->data['payment_method']['code'];

        //     // EC-5451 this check to fix the requests coming from the manual checkout / admin panel checkout
        //     if (
        //         !isset($this->session->data['payment_method']['code']) &&
        //         isset($this->request->post['payment_code'])
        //     ) {
        //         $current_payment_method_code = $this->request->post['payment_code'];
        //     }

        //     $payment_settings = $this->model_module_custom_fees_for_payment_method->get_setting($current_payment_method_code);

        //     $fees = explode(':', $payment_settings['fees']);
        //     $fees_value = (float) $fees[0];
        //     $fees_pcg = 'no';

        //     if ( isset($fees[1]) && $fees[1] === 'pcg' )
        //     {
        //         $fees_pcg = 'yes';
        //     }

        //     $fees_range = explode(':', $payment_settings['fees_range']);
        //     $fees_range_min = (float) $fees_range[0];
        //     $fees_range_max = $fees_range[1];

        //     if ( $fees_range_max !== 'no_max' )
        //     {
        //         $fees_range_max = (float) $fees_range_max;
        //     }

        //     if ( $total_sum >= $fees_range_min && ( $total_sum <= $fees_range_max || $fees_range_max === 'no_max' ) )
        //     {

        //             if ($fees_pcg === 'no') {
        //                 if($this->session->data['shipping_method']['code'] != "pickup.pickup"){
        //                     $fees_text = $this->currency->format($fees_value);
        //                     $total_fees_value = $fees_value;
        //                     $total += $fees_value;
        //                 }
        //             } elseif ($fees_pcg === 'yes') {
        //                 if($this->session->data['shipping_method']['code'] != "pickup.pickup") {
        //                     $subtotal_index = array_search('sub_total', array_values($total_data));

        //                     $total_fees_value = (float)($fees_value / 100) * ( float )$total_data[$subtotal_index]['value'];
        //                     $fees_text = $this->currency->format($total_fees_value);
        //                     $total += $total_fees_value;
        //                 }
        //                 }

        //             // safety check to ensure the user doesn't pay more than he should.
        //             // should never happen but you never know.
        //             else {
        //                 return false;
        //             }
        //             // fun part.
        //             // add the correct values to the total data.
        //             $current_lang = $this->session->data['language'];
        //             $title = $this->model_module_custom_fees_for_payment_method->get_setting_from_setting('cffpm_total_row_name_' . $current_lang)['value'];


        //             $total_data[] = array(
        //                 'code' => 'cffpm',
        //                 'title' => $title ? $title : 'Payment Method Fees',
        //                 'text' => $fees_text,
        //                 'value' => (float)$total_fees_value,
        //                 'sort_order' => (int)$this->config->get('total_sort_order') - 1,
        //             );
        //             $this->session->data['cffpm'] = (float)$total_fees_value;
        //     }
        // }
        
        if (\Extension::isInstalled('multiseller')&& \Extension::isInstalled('seller_based')&& $this->config->get('seller_based_status') == 1){
            $this->load->model('shipping/seller_based');
            $sellerCartProductsData = $this->model_shipping_seller_based->getSellerCartProductsData();
            $total+=$sellerCartProductsData['totalShippingCost'];
        }

		$total_data[] = array(
			'code'       => 'total',
			'title'      => $this->language->get('text_total'),
			'text'       => $this->currency->format(max(0, $total)),
			'value'      => max(0, $total),
			'sort_order' => $this->config->get('total_sort_order')
		);
	}
}
?>
