<?php
class ControllerModuleLike4card extends Controller { 
	
    public $module = "like4card";

	public function handleLike4cardRequest($order_id = null)
	{
		$this->load->model("checkout/order");
		$this->load->model("module/{$this->module}");
		$order_products = $this->model_checkout_order->getOrderProducts($order_id);
		$products=array();
		 foreach($order_products as $product)
		 {
			 $like4cardProductId= $this->model_module_like4card->getLike4cardProductId($product['product_id']);
			 $product_quantity=$product['quantity'];
			 $products[]=[
				"productId"=>$like4cardProductId,
				"quantity"=>$product_quantity,
				];
		 }	
		if($products)
			$data = $this->model_module_like4card->createOrder(json_encode($products), $order_id);
		else
			return false;

		$order_info =  $this->model_checkout_order->getOrder($order_id);
		
		if($data['response']){
			$this->model_module_like4card->saveSerials($data['orders'],$order_id);
			$this->session->data['like4card'] = $data['orders'];
			$this->sendMail($order_id, $order_info , $data['orders']);
		}
	}
	/*public function handleLike4cardRequest($order_id = null)
	{
		$this->load->model("checkout/order");
		$this->load->model("module/{$this->module}");
		$order_products = $this->model_checkout_order->getOrderProducts($order_id);
		// like4card order has only one item 
		$product_like4card_id = $this->model_module_like4card->getLike4cardProductId($order_products[0]['product_id']);
		if($product_like4card_id)
			$data = $this->model_module_like4card->createOrder($product_like4card_id, $order_id);
		else
			return false;

		$order_info =  $this->model_checkout_order->getOrder($order_id);
		if($data['response']){
			$this->model_module_like4card->saveSerials($data['orderId'],$data['serials'][0],$order_id);
			$this->session->data['like4card_serialCode'] = $data['serials'][0]['serialCode'];
			$this->session->data['like4card_validTo'] = $data['serials'][0]['validTo'];
			$this->sendMail($order_id, $order_info , $data['serials'][0]['serialCode'], $data['serials'][0]['validTo']);
		}
	}*/

	private function sendMail($order_id, $order_info ,$like4cardOrders)
	{
		$this->language->load_json("module/{$this->module}");
		$message =$this->language->get("text_new_greeting");
		foreach($like4cardOrders as $like4card )
		{ 
		$message .= sprintf($this->language->get('text_like4card'),$like4card['orderNumber'],$like4card['serials'][0]['serialCode'],$like4card['serials'][0]['validTo'],$like4card['productName']);
		}
		$mail = new Mail(); 
		$mail->protocol = $this->config->get("config_mail_protocol");
		$mail->parameter = $this->config->get("config_mail_parameter");
		$mail->hostname = $this->config->get("config_smtp_host");
		$mail->username = $this->config->get("config_smtp_username");
		$mail->password = $this->config->get("config_smtp_password");
		$mail->port = $this->config->get("config_smtp_port");
		$mail->timeout = $this->config->get("config_smtp_timeout");			
		$mail->setTo($order_info["email"]);
		$mail->setFrom((!empty($this->config->get("config_smtp_username")) ? $this->config->get("config_smtp_username") :  $this->config->get("config_email")));
		$mail->setSender($order_info["store_name"]);
		$mail->setSubject($this->prepareEmailSubject($order_id, $order_info["store_name"]));
    	$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
		$mail->send();
	}

	private function prepareEmailSubject($order_id, $store_name)
	{
		$this->language->load_json("module/{$this->module}");
		$subject = sprintf($this->language->get("text_new_subject"), $store_name, $order_id);
		return html_entity_decode($subject, ENT_QUOTES, "UTF-8");
	}
}
