<?php
class ControllerModuleCardless extends Controller { 
	
	public function isCardlessProduct($product_id)
	{
		
		$this->load->model("module/cardless");

		return $this->model_module_cardless->isCardlessProduct($product_id);

	}


	public function handleCardlessRequest($order_id = null)
	{
		$this->load->model('checkout/order');
		$this->load->model('module/cardless');

		$order_products = $this->model_checkout_order->getOrderProducts($order_id);
		$prepared_products = $this->prepareProductsForCardless($order_products);

		if (empty($prepared_products)) {

			return false;
		}


		$this->load->model('setting/setting');

		$cardless_config = $this->model_setting_setting->getSetting('cardless');

		$data['username'] = $cardless_config['username'];
		$data['password'] = $cardless_config['password'];
		$data['method'] = 'purchaseproduct';
		$data['referencenumber'] = 'ORDER' . $order_id;
		$data['purchasedet'] = $prepared_products;

		// create cardless order
		$cardelss_order = $this->createCardlessOrder($data);
		if ($cardelss_order->result[0]->statusdiscription !== 'Success') {
			return false;
		}

		// confirm cardless order
		$data['transactionid'] = $cardelss_order->result[0]->transactionid;
		unset($data['purchasedet']);
		$data['method'] = 'confirmorder';

		$cardelss_order_confirmation = $this->confirmCardlessOrder($data);

		if ($cardelss_order_confirmation->result[0]->statusdiscription !== 'Success') {
			return false;
		}

		// save cardless data into DB
		$this->model_module_cardless->addNewCardlessPurchased(
			$cardelss_order_confirmation, 
			$order_id, 
			$prepared_products
		);

		$order_info =  $this->model_checkout_order->getOrder($order_id);

		$purchased_products = $cardelss_order_confirmation->result[0]->itemdet;
		$this->sendMail($order_id, $order_info, $purchased_products);
	}


	private function createCardlessOrder($data)
	{

		$encoded_data = json_encode($data);

		$headers = [
            "Content-Type:application/json",
            "Accept: application/json"
        ];

        $url = "https://giftcard.viaarabia.com/api/order/createorder";
        if ($this->config->get('cardless_test_mode')) {
            $url = "https://giftcard-test.viaarabia.com/api/order/createorder";
        }

		return $this->invokeCurlRequest($url, $encoded_data, $headers);
	}

	

	private function confirmCardlessOrder($data)
	{

		$encoded_data = json_encode($data);

		$headers = [
            "Content-Type:application/json",
            "Accept: application/json"
		];
        
        $url = "https://giftcard.viaarabia.com/api/order/confirmorder";
        if ($this->config->get('cardless_test_mode')) {
            $url = "https://giftcard-test.viaarabia.com/api/order/confirmorder";
        }

		return $this->invokeCurlRequest($url, $encoded_data, $headers);
	}


	private function invokeCurlRequest($url, $post_data, $headers = [], $type = 'POST')
	{

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if ($type == "POST" || $type == "PUT") {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
			if ($type == "PUT") {
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			}
		}

		$server_output = curl_exec($ch);
		return json_decode($server_output);

	}


	private function prepareProductsForCardless($order_products)
	{

		$this->load->model('module/cardless');
		$cardless_model = $this->model_module_cardless;
		$cardless_products = [];

		foreach ($order_products as $order_product) {

			$cardless_product = $cardless_model->isCardlessProduct($order_product['product_id']);
			if (!$cardless_product) {
				continue;
			}

			$cardless_products[] = [
				'sku' => $cardless_product['cardless_sku'],
				'qty' => $order_product['quantity'],
				'product_id' => $order_product['product_id']
			];
		}

		return $cardless_products;
	}

	

	private function sendMail($order_id, $order_info, $purchased_products)
	{
		$mail = new Mail(); 
		$mail->protocol = $this->config->get('config_mail_protocol');
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->hostname = $this->config->get('config_smtp_host');
		$mail->username = $this->config->get('config_smtp_username');
		$mail->password = $this->config->get('config_smtp_password');
		$mail->port = $this->config->get('config_smtp_port');
		$mail->timeout = $this->config->get('config_smtp_timeout');			
		$mail->setTo($order_info['email']);
		$mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
		$mail->setSender($order_info['store_name']);
		$mail->setSubject($this->prepareEmailSubject($order_id, $order_info['store_name']));
		$mail->setHtml($this->prepareEmailTemplate($order_id, $order_info, $purchased_products));
		$mail->send();

	}


	private function prepareEmailSubject($order_id, $store_name)
	{
		$this->language->load_json('module/cardless');
		$subject = sprintf($this->language->get('text_new_subject'), $store_name, $order_id);
		return html_entity_decode($subject, ENT_QUOTES, 'UTF-8');
	}


	private function prepareEmailTemplate($order_id, $order_info, $purchased_products)
	{
		$this->language->load_json('module/cardless');

		$template = new Template();

		$template->data['text_greeting'] = sprintf($this->language->get('text_new_greeting'), html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'));
		$template->data['text_link'] = $this->language->get('text_new_link');
		$template->data['link'] = $order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_id;
		$template->data['customer_id'] = $order_info['customer_id'];
		$template->data['text_footer'] = $this->language->get('text_new_footer');

		$template->data['text_cardless_purchases'] = $this->language->get('text_cardless_purchases');
		$template->data['text_cardless_transactionid'] = $this->language->get('text_cardless_transactionid');
		$template->data['text_cardless_code'] = $this->language->get('text_cardless_code');
		$template->data['text_cardless_serial'] = $this->language->get('text_cardless_serial');
		$template->data['text_cardless_skuname'] = $this->language->get('text_cardless_skuname');
		$template->data['text_cardless_sku'] = $this->language->get('text_cardless_sku');
		$template->data['text_cardless_cost'] = $this->language->get('text_cardless_cost');
		$template->data['text_cardless_price'] = $this->language->get('text_cardless_price');
		$template->data['cardless_purchases'] = $purchased_products;

		return $template->fetch('default/template/mail/cardless_purchases.tpl');
	}

}
