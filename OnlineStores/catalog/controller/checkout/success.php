<?php
class ControllerCheckoutSuccess extends Controller { 
	public function index() {

		if (isset($this->session->data['order_id'])) {
            $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

            if($queryRewardPointInstalled->num_rows) {
                $this->load->model('rewardpoints/observer');
                $this->model_rewardpoints_observer->afterPlaceOrder($this->session->data['order_id']);
            }

            //################### Webhook Call #####################################
            if ($this->config->get('config_webhook_url')) {
                if ($this->config->get('config_webhook_url') != '') {
                    $this->load->model('checkout/order');
                    $orderId = $this->session->data['order_id'];
                    $orderInfo = $this->model_checkout_order->getOrder($orderId);

                    try {
                        $url = $this->config->get('config_webhook_url');
                        $post = array(
                            'order_id' => $this->session->data['order_id'],
                            'total' => $orderInfo['total'],
                            'currency' => $orderInfo['currency_code'],

                            'account' => $this->session->data['account'],
                            'confirm' => $this->session->data['confirm'],
                            'addresses' => $this->session->data['addresses'],
                            'shipping_address' => $this->session->data['shipping_address'],
                            'payment_address' => $this->session->data['payment_address'],
                            'shipping_method' => $this->session->data['shipping_method'],
                            'payment_method' => $this->session->data['payment_method'],
                            'guest' => $this->session->data['guest'],
                            'comment' => $this->session->data['comment'],
                            'coupon' => $this->session->data['coupon'],
                            'reward' => $this->session->data['reward'],
                            'voucher' => $this->session->data['voucher'],
                            'vouchers' => $this->session->data['vouchers'],
                            'customer_id' => $orderInfo['customer_id'],
                            'email' => $orderInfo['email'],
                            'telephone' => $orderInfo['telephone'],

                            'products' => $this->cart->getProducts()
                        );
                        $options = array();
                        $json_data = json_encode($post);

                        $defaults = array(
                            CURLOPT_POST => 1,
                            CURLOPT_HEADER => 0,
                            CURLOPT_HTTPHEADER => array(
                                'Content-Type: application/json',
                                'Content-Length: ' . strlen($json_data)),
                            CURLOPT_URL => $url,
                            CURLOPT_FRESH_CONNECT => 1,
                            CURLOPT_RETURNTRANSFER => 1,
                            CURLOPT_FORBID_REUSE => 1,
                            CURLOPT_TIMEOUT => 4,
                            CURLOPT_POSTFIELDS => $json_data
                        );

                        $ch = curl_init();
                        curl_setopt_array($ch, ($options + $defaults));
                        if (!$result = curl_exec($ch)) {
                            //trigger_error(curl_error($ch));
                        }
                        curl_close($ch);
                    } catch (Exception $e) {
                    }
                }
            }
            //################### Webhook Call #######################################

			$this->cart->clear();

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['guest']);
			unset($this->session->data['comment']);
			unset($this->session->data['order_id']);	
			unset($this->session->data['coupon']);
			unset($this->session->data['reward']);
			unset($this->session->data['voucher']);
			unset($this->session->data['vouchers']);

            if (isset($this->session->data['mobile_api_token'])) {
                $mobileToken = $this->session->data['mobile_api_token'];
                //unset($this->session->data['mobile_api_token']);
                $this->load->model('account/api');
                $this->model_account_api->updateSession($mobileToken);
            }
		}	
									   
		$this->language->load('checkout/success');
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->data['breadcrumbs'] = array(); 

      	$this->data['breadcrumbs'][] = array(
        	'href'      => $this->url->link('common/home'),
        	'text'      => $this->language->get('text_home'),
        	'separator' => false
      	); 
		
      	$this->data['breadcrumbs'][] = array(
        	'href'      => $this->url->link('checkout/cart'),
        	'text'      => $this->language->get('text_basket'),
        	'separator' => $this->language->get('text_separator')
      	);
				
		$this->data['breadcrumbs'][] = array(
			'href'      => $this->url->link('checkout/checkout', '', 'SSL'),
			'text'      => $this->language->get('text_checkout'),
			'separator' => $this->language->get('text_separator')
		);	
					
      	$this->data['breadcrumbs'][] = array(
        	'href'      => $this->url->link('checkout/success'),
        	'text'      => $this->language->get('text_success'),
        	'separator' => $this->language->get('text_separator')
      	);

		$this->data['heading_title'] = $this->language->get('heading_title');
		
		if ($this->customer->isLogged()) {
    		$this->data['text_message'] = sprintf($this->language->get('text_customer'), $this->url->link('account/account', '', 'SSL'), $this->url->link('account/order', '', 'SSL'), $this->url->link('account/download', '', 'SSL'), $this->url->link('information/contact'));
		} else {
    		$this->data['text_message'] = sprintf($this->language->get('text_guest'), $this->url->link('information/contact'));
		}
		
    	$this->data['button_continue'] = $this->language->get('button_continue');

    	$this->data['continue'] = $this->url->link('common/home');

    	if($this->session->data['ismobile'] != "1") {
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/success.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/common/success.tpl';
		} else {
			$this->template = 'default/template/common/success.tpl';
		}

            $this->children = array(
                'common/column_left',
                'common/column_right',
                'common/content_top',
                'common/content_bottom',
                'common/footer',
                'common/header'
            );
        } else {
            $this->template = 'default/template/common/success.tpl';
        }
				
		$this->response->setOutput($this->render());
  	}
}
?>