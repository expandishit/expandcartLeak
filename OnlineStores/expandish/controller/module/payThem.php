<?php
class ControllerModulePayThem extends Controller { 
	
    public $module = "payThem";

	public function isPayThemProduct($product_id) {
		$this->load->model("module/{$this->module}");
		return $this->model_module_payThem->isPayThemProduct($product_id);

	}


	public function handlePayThemRequest($order_id = null)
	{
		$this->load->model("checkout/order");
		$this->load->model("module/{$this->module}");

		$order_products = $this->model_checkout_order->getOrderProducts($order_id);
		$payThem_vouchers = $this->getPayThemVouchers($order_products);

		if (empty($payThem_vouchers)) {
			return false;
		}

		$order_info =  $this->model_checkout_order->getOrder($order_id);

		$this->sendMail($order_id, $order_info, $payThem_vouchers);
	}


	private function getPayThemVouchers($order_products)
	{

		include "PayThemAPI/class.PTN_API_v2.php";
		//$app_id = $this->config->get("{$this->module}_app_id") ? $this->config->get("{$this->module}_app_id") : "2824";
		$app_mode = $this->config->get("payThem_test_mode") == 1 ? "demo" : "";
        $api = new PTN_API_v2($app_mode, "2824");
		$api->PUBLIC_KEY = $this->config->get("{$this->module}_public_key");
        $api->PRIVATE_KEY = $this->config->get("{$this->module}_private_key");
        $api->USERNAME = $this->config->get("{$this->module}_username");
        $api->PASSWORD = $this->config->get("{$this->module}_password");
		$api->FUNCTION = "get_Vouchers";
		$api->SERVER_TIMESTAMP = (new DateTime("now", new DateTimeZone($this->config->get("{$this->module}_timezone"))))->format("Y-m-d H:i:s");
        $api->SERVER_TIMEZONE = $this->config->get("{$this->module}_timezone");

		$this->load->model("module/{$this->module}");
		$payThem_vouchers = [];
		foreach ($order_products as $order_product) {

			$payThem_product = $this->model_module_payThem->isPayThemProduct($order_product["product_id"]);

			if (!$payThem_product) {
				continue;
			}
			
			$api->PARAMETERS = array(
				"PRODUCT_ID" => $payThem_product["OEM_PRODUCT_ID"],
				"QUANTITY" => $order_product["quantity"]
			);

			$res = $api->callAPI(false);	

			if (!$res || (isset($res["CONTENT"]) && $res["CONTENT"] == "ERROR")) {
				continue;
			}

			$payThem_vouchers[] = $res;
		}
		return $payThem_vouchers;
	}
	

	private function sendMail($order_id, $order_info, $payThem_vouchers)
	{
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
		$mail->setHtml($this->prepareEmailTemplate($order_id, $order_info, $payThem_vouchers));
		$mail->send();

	}


	private function prepareEmailSubject($order_id, $store_name)
	{
		$this->language->load_json("module/{$this->module}");
		$subject = sprintf($this->language->get("text_new_subject"), $store_name, $order_id);
		return html_entity_decode($subject, ENT_QUOTES, "UTF-8");
	}


	private function prepareEmailTemplate($order_id, $order_info, $payThem_vouchers)
	{
		$this->language->load_json("module/{$this->module}");

		$template = new Template();

		$template->data["text_greeting"] = sprintf($this->language->get("text_new_greeting"), html_entity_decode($order_info["store_name"], ENT_QUOTES, "UTF-8"));
		$template->data["text_link"] = $this->language->get("text_new_link");
		$template->data["link"] = $order_info["store_url"] . "index.php?route=account/order/info&order_id=" . $order_id;
		$template->data["customer_id"] = $order_info["customer_id"];
		$template->data["text_footer"] = $this->language->get("text_new_footer");

		$template->data["text_payThem_purchases"] = $this->language->get("text_payThem_purchases");
		$template->data["text_payThem_transactionid"] = $this->language->get("text_payThem_transactionid");
		$template->data["text_transaction_date"] = $this->language->get("text_transaction_date");
		$template->data["text_voucher_qty"] = $this->language->get("text_voucher_qty");
		$template->data["text_transaction_date"] = $this->language->get("text_transaction_date");
		$template->data["text_tranaction_value"] = $this->language->get("text_tranaction_value");
		$template->data["text_purchase_name"] = $this->language->get("text_purchase_name");
		$template->data["text_brand_name"] = $this->language->get("text_brand_name");
		$template->data["text_vvssku"] = $this->language->get("text_vvssku");
		$template->data["redemption_instructions"] = $this->language->get("redemption_instructions");
		$template->data["text_voucher_id"] = $this->language->get("text_voucher_id");
		$template->data["text_voucher_serial"] = $this->language->get("text_voucher_serial");
		$template->data["text_sales_ref"] = $this->language->get("text_sales_ref");
		$template->data["text_voucher_pin"] = $this->language->get("text_voucher_pin");
		$template->data["text_expire_date"] = $this->language->get("text_expire_date");
		$template->data["payThem_purchases"] = $payThem_vouchers;

		return $template->fetch("default/template/mail/payThem_purchases.tpl");
	}

}
