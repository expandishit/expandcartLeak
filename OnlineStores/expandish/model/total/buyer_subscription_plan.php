<?php
class ModelTotalBuyerSubscriptionPlan extends Model {

	public function getTotal(&$total_data, &$total, &$taxes) {

		if (!empty($this->session->data['subscription'])) {
			$this->language->load_json('total/subscription');
			
			$this->load->model('account/subscription');		
			$subscription_info = $this->model_account_subscription->get($this->session->data['subscription']['id']);

			$this->session->data["subscription_discount"] = $subscription_info['price'];

			if ($subscription_info) {						      
				$total_data[] = array(
					'code'       => 'buyer_subscription_plan',
        			'title'      => $subscription_info['title'],
	    			'text'       => $this->currency->format($subscription_info['price']),
        			'value'      => $subscription_info['price'],
					'sort_order' => $this->config->get('buyer_subscription_plan_sort_order')
      			);

				$total += $subscription_info['price'];
			} 
		}
	}
	
}
?>
