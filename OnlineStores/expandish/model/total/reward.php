<?php
class ModelTotalReward extends Model {
	public function getTotal(&$total_data, &$total, &$taxes) {
        $this->language->load_json('total/reward');

        if (isset($this->session->data['reward'])) {
			$this->load->model('rewardpoints/helper');
			$points = $this->customer->getRewardPoints();

			if ($points && $this->session->data['reward'] <= $points) {
				$discount_total = 0;
				
				$points_total = 0;
				
				//check if allow_no_product_points_spending option exists
				if(!$this->config->get('allow_no_product_points_spending')){
					foreach ($this->cart->getProducts() as $product) {
						if ($product['points']) {
							$points_total += $product['points'];
						}
					}
				}else{
					$rule = explode('/',$this->config->get('currency_exchange_rate'));

					foreach ($this->cart->getProducts() as $product) {
						$points_total += $this->model_rewardpoints_helper->exchangeMoneyToPoint($product['price']);
					}
				}
				
				$points = min($points, $points_total);
		
				if(!$this->config->get('allow_no_product_points_spending')){
					foreach ($this->cart->getProducts() as $product) {
						$discount = 0;
						
						if ($product['points']) {
							$discount = $product['price'] * ($this->session->data['reward'] / $points_total);
							
							if ($product['tax_class_id']) {
								$tax_rates = $this->tax->getRates($product['total'] - ($product['total'] - $discount), $product['tax_class_id']);
								
								foreach ($tax_rates as $tax_rate) {
									if ($tax_rate['type'] == 'P') {
										$taxes[$tax_rate['tax_rate_id']] -= $tax_rate['amount'];
									}
								}	
							}
						}

						$discount_total += $discount;
					}
				}else{
					foreach ($this->cart->getProducts() as $product) {
						$discount = 0;
						
						if (!$product['points']) {
							$discount = $product['price'] * ($this->session->data['reward'] / $points_total);
							if ($product['tax_class_id']) {
								$tax_rates = $this->tax->getRates($product['total'] - ($product['total'] - $discount), $product['tax_class_id']);
								
								foreach ($tax_rates as $tax_rate) {
									if ($tax_rate['type'] == 'P') {
										$taxes[$tax_rate['tax_rate_id']] -= $tax_rate['amount'];
									}
								}	
							}
						}
						
						$discount_total += $discount;
					}
				}
			
				$total_data[] = array(
					'code'       => 'reward',
        			'title'      => sprintf($this->language->get('text_reward'), $this->session->data['reward'] . '' . $this->config->get('text_points_' . $this->language->get('code') )),
	    			// 'text'       => $this->currency->format(-$discount_total),
	    			'text'		 =>  '- ' . $this->currency->format($discount_total),
        			'value'      => -$discount_total,
					'sort_order' => $this->config->get('reward_sort_order')
      			);

				$total -= $discount_total;
                $this->session->data['reward_point_discount'] = -$discount_total;
			} 
		}

		$earned_points = $this->cart->getRewardPoints();
		if ( $earned_points )
		{
			$this->session->data['earned_points'] = $earned_points;

			$total_data[] = array(
			'code'       => 'earn_reward',
			'title'      => sprintf($this->language->get('text_reward'), '+'),
			'text'		 =>  number_format( $earned_points ).' '.$this->config->get('text_points_'.$this->language->get('code')),
			'value'      => $earned_points,
			'sort_order' => $this->config->get('reward_sort_order')
			);
		}
	}
	
	public function confirm($order_info, $order_total) {
		$this->language->load_json('total/reward');
		
		$points = 0;
		
		$start = strpos($order_total['title'], '(') + 1;
		$end = strrpos($order_total['title'], ')');
		
		if ($start && $end) {  
			$points = substr($order_total['title'], $start, $end - $start);
		}	
		
		if ($points) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "customer_reward SET customer_id = '" . (int)$order_info['customer_id'] . "', description = '" . $this->db->escape(sprintf($this->language->get('text_order_id'), (int)$order_info['order_id'])) . "', points = '" . (float)-$points . "', date_added = NOW()");				
		}
	}		
}
