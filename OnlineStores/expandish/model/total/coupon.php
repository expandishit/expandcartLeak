<?php
class ModelTotalCoupon extends Model {
	public function getTotal(&$total_data, &$total, &$taxes) {
        $this->session->data['coupon_discount'] = 0 ;
        $this->load->model('checkout/coupon');
        $coupons = $this->model_checkout_coupon->getAutomaticCoupons();

        if (isset($this->session->data['shipping_method']) && !is_array($this->session->data['shipping_method'])){
            $shipping = explode('.', $this->request->post['shipping_method']);
            $this->session->data['shipping_method'] = (isset($this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]])) ? $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]] : $this->session->data['default_shipping_method'];
        }
       
        $automaticCoupons = [];
        foreach ($coupons as $coupon){
            if(isset($coupon['code'])) {
                $automaticCoupons[] = $coupon['code'];
            }
            $this->applyCoupon($total_data, $total, $taxes,$coupon['code']);
        }

        $userSentCoupon = trim($this->session->data['coupon']," ");
       
        if (isset($userSentCoupon) && !in_array($userSentCoupon, $automaticCoupons)) {
            $this->applyCoupon($total_data, $total, $taxes, $userSentCoupon);
        }
	}

	private function applyCoupon(&$total_data,&$total,&$taxes,$coupon_code){
            $accepted_product_get_total = 0;
            $accepted_product_get_total_array = array();

            $this->language->load_json('total/coupon');

            $this->load->model('checkout/coupon');

            $coupon_info = $this->model_checkout_coupon->getCoupon($coupon_code);
            $details = json_decode($coupon_info['details'],true);
            if ($coupon_info['status']) {

                // check if the used promo coupon is used with its affiliate
                // to avoid users from using the code only as a regular coupon
                if (\Extension::isInstalled('affiliate_promo'))
                {
                    $this->load->model('module/affiliate_promo');
                    $this->load->model('affiliate/affiliate');

                    $moduleEnabled = $this->model_module_affiliate_promo->isEnabled();
                    $fromAffiliateCoupon = $this->model_module_affiliate_promo->fromAffiliateCoupon($coupon_info['coupon_id']);

                    if ($moduleEnabled && $fromAffiliateCoupon)
                    {
                        $trackingCode = $this->request->cookie['tracking'] ?? '';
                        $no_tracking_required = $this->config->get('affiliate_promo')['no_tracking'];
                        if (!$this->model_module_affiliate_promo->isUsedWithAffiliateCode($coupon_info['coupon_id'], $trackingCode))
                        {
                            if ($no_tracking_required && !$this->model_affiliate_affiliate->getAffiliateByCoupon($coupon_info['coupon_id'])){
                                return;
                            }
                        }
                    }
                }

                //check minimum acceptance
                //total of products in coupon
                $total_accepted_products = 0;
                if (is_array($coupon_info)) {
                    if(array_key_exists('product',$coupon_info)&&is_array($coupon_info['product'])){
                        foreach ($this->cart->getProducts() as $product) {
                            if (in_array($product['product_id'],$coupon_info['product'])){

                                //Skip Y Gifted products in case of "Buy X Get Y Free" coupons
                                if($coupon_info['type'] == 'B' && 
                                    in_array($product['product_id'],$coupon_info['product_get'])
                                ){            
                                    $product_total_without_gifted_product = $product['price'] * ($product['quantity'] - (int)$details['get_quantity']);                                
                                    $total_accepted_products += $product_total_without_gifted_product;                                    
                                }else{
                                    $total_accepted_products += $product['total'];
                                }
                                
                            }
                        }
                    }
                }

                if ($coupon_info['type'] == 'B') {

                    // get products total "buy x get y"
                    if (is_array($coupon_info)) {
                        if(array_key_exists('product_get',$coupon_info)&&is_array($coupon_info['product_get'])){
                           $i=0;
                            foreach ($this->cart->getProducts() as $product) {
                                if (in_array($product['product_id'],$coupon_info['product_get'])){
                                    $accepted_product_get_total_array[$i]['price'] = $product['price'];
                                    $accepted_product_get_total_array[$i]['quantity'] = $product['quantity'];
                                    $i++;
//                                    if ($accepted_product_get_total==0){
//                                        $accepted_product_get_total[] = $product['price'];
//                                    }
//                                    if ($accepted_product_get_total != 0 && $product['price'] < $accepted_product_get_total){
//
//                                        $accepted_product_get_total = $product['price'];
//                                    }
                                }
                            }
                        }
                    }
                }

                //check minimum
                if ($total_accepted_products < $coupon_info['minimum_to_apply']){
                    return;
                }

                //check minimum
                if ($coupon_info['type'] == 'B' && $details["buy_option"]=='purchase' && $details["buy_amount"] > $total_accepted_products
                    && $total_accepted_products != 0
                )
                {
                    return;
                }

                $discount_total = 0;

                if (!$coupon_info['product']) {
                    $sub_total = $this->cart->getSubTotal();
                } else {
                    $sub_total = 0;

                    foreach ($this->cart->getProducts() as $product) {
                        if (in_array($product['product_id'], $coupon_info['product'])) {
                            $sub_total += $product['total'];
                        }
                    }
                }

                if ($coupon_info['type'] == 'F') {
                    $coupon_info['discount'] = min($coupon_info['discount'], $sub_total);
                }

                foreach ($this->cart->getProducts() as $product) {
                    $discount = 0;

                    if (!$coupon_info['product']) {
                        $status = true;
                    } else {
                        if (in_array($product['product_id'], $coupon_info['product'])) {
                            $status = true;
                        } else {
                            $status = false;
                        }
                    }

                    if ($status) {
                        if ($coupon_info['type'] == 'F') {
                            $discount = $coupon_info['discount'] * ($product['total'] / $sub_total);
                        } elseif ($coupon_info['type'] == 'P') {
                            $discount = $product['total'] / 100 * $coupon_info['discount'];
                        }else if ($coupon_info['type'] == 'B'){
                            $accepted_product_get_total =0;
                            sort($accepted_product_get_total_array);
                            $get_quantity = $details['get_quantity'];
                            $j = 0;
                            $quantity=0;
                            for ($i=0;$i < $details['get_quantity'];$i++) {
                                $j = $j + $accepted_product_get_total_array[$i]['quantity'];

                                if ($accepted_product_get_total_array[$i]['quantity'] > $details['get_quantity'] && $i == 0){
                                    $accepted_product_get_total += $accepted_product_get_total_array[$i]['price'] * $details['get_quantity'];
                                    break;
                                }

                                if ($j < $get_quantity){
                                    $accepted_product_get_total += $accepted_product_get_total_array[$i]['price'] *  $accepted_product_get_total_array[$i]['quantity'];
                                    $quantity += $accepted_product_get_total_array[$i]['quantity'];
                                }else{
                                    $accepted_product_get_total += $accepted_product_get_total_array[$i]['price'] * ($get_quantity - $quantity);
                                    break;
                                }

                                if($j > $details['get_quantity']){
                                    break;
                                }
                            }
                            if ($details["get_discount_value_option"] == "free"){
                                $coupon_info['discount']= $accepted_product_get_total ;
                            }else if ($details["get_discount_value_option"] == "percentage"){
                                $accepted_product_get_total = $accepted_product_get_total;
                                $coupon_info['discount'] = $accepted_product_get_total / 100 * $details["get_percentage"];
                            }
                            $discount = $coupon_info['discount'] * ($product['total'] / $sub_total);
                        }


                        if ($product['tax_class_id'] && !$coupon_info['tax_excluded']) {
                            $tax_rates = $this->tax->getRates($product['total'] - ($product['total'] - $discount), $product['tax_class_id']);

                            foreach ($tax_rates as $tax_rate) {
                                if ($tax_rate['type'] == 'P' && $coupon_info['discount'] != 100) {
                                    $taxes[$tax_rate['tax_rate_id']] -= $tax_rate['amount'];
                                }else if($coupon_info['discount'] == 100){
                                    $taxes[$tax_rate['tax_rate_id']] += $tax_rate['amount'];
                                }
                            }
                        }
                    }

                    $discount_total += $discount;
                }

                if ($coupon_info['shipping'] == 1 && isset($this->session->data['shipping_method'])) {
                    if (!empty($this->session->data['shipping_method']['tax_class_id'])) {
                        $tax_rates = $this->tax->getRates($this->session->data['shipping_method']['cost'], $this->session->data['shipping_method']['tax_class_id']);

                        foreach ($tax_rates as $tax_rate) {
                            if ($tax_rate['type'] == 'P') {
                                $taxes[$tax_rate['tax_rate_id']] -= $tax_rate['amount'];
                            }
                        }
                    }

                    $discount_total += $this->session->data['shipping_method']['cost'];
                } else {
                    if ($coupon_info['shipping'] == 0) {
                        if ($coupon_info['type'] == 'P') {
                            if ($this->session->data['shipping_method']['cost'] != 0) {
                                $discount_total += ($this->session->data['shipping_method']['cost'] * $coupon_info['discount']) / 100;
                            }
                        }
                    }
                }

                if ($coupon_info['automatic_apply']==1){
                    $text_coupon = $this->language->get('text_coupon_auto') . "( {$coupon_info['name']} )";
                }else{
                    $text_coupon = $this->language->get('text_coupon') . "( {$coupon_code} )";
                }
                $title = sprintf($text_coupon,
                    $this->session->data['buyer_subscription_plan_coupon'] ?
                        $this->language->get('text_buyer_subscription_plan_coupon') : $coupon_code
                );
                if (
                    (float)$coupon_info['maximum_limit'] > 0 &&
//					$coupon_info['type'] == 'P' &&
                    $sub_total > $coupon_info['maximum_limit']
                ) {
//                    $title = $title . ' ' . sprintf(
//                            $this->language->get('text_coupon_limit'),
//                            $this->currency->format($coupon_info['maximum_limit'])
//                        );

                    return;
//                    $newDiscount = ($coupon_info['maximum_limit'] * $coupon_info['discount']) / 100;
//                    $newDiscount = $discount_total - $newDiscount;
//                    $discount_total = $discount_total - $newDiscount;
                }

                $total_data[] = array(
                    'code'       => 'coupon',
                    'title'      => $title,
                    'text'       => $this->currency->format(-$discount_total),
                    'value'      => -$discount_total,
                    'sort_order' => $this->config->get('coupon_sort_order'),
                    'automatic_apply' => $coupon_info['automatic_apply'],
                );
                $total -= $discount_total;
                $this->session->data['coupon_discount'] += $discount_total;
                $this->session->data['coupon'] = $coupon_info['code'];
            }
    }
	public function confirm($order_info, $order_total) {
        if(STORECODE == 'UPMXV084'){
            $arr= [
                'order_inf'=>$order_info ,
                'order_total'=>$order_total ,
            ];
            $this->log("ModelTotalCouponConfirm:" , json_encode($arr));
        }
		$code = '';

		$start = strpos($order_total['title'], '(') + 1;
		$end = strrpos($order_total['title'], ')');

		if ($start && $end) {
			$code = substr($order_total['title'], $start, $end - $start);
		}

		$this->load->model('checkout/coupon');

		$coupon_info = $this->model_checkout_coupon->getActiveCouponDetails($code);

        if(STORECODE == 'UPMXV084'){
            $arr= [
                'code'=>$code ,
                'coupon_info'=>$coupon_info ,
            ];
            $this->log("ModelTotalCouponConfirmResult:" , json_encode($arr));
        }

		if ($coupon_info) {
			$this->model_checkout_coupon->redeem($coupon_info['coupon_id'], $order_info['order_id'], $order_info['customer_id'], $order_total['value']);
		}
	}

    function log($type, $contents , $fileName=false)
    {
        if (!$fileName || empty($fileName))
            $fileName='coupon.log';

        $log = new Log($fileName);
        $log->write('[' . strtoupper($type) . '] ' . $contents);
    }
}
?>
