<?php
use SoapClient;

class ControllerSaleAramexCreateShipment extends Controller {
	
	private $error = array();

	public function index() {

		$this->language->load('sale/aramex');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('sale/order');
		
		$this->getForm();
	}
	
	public function getForm() {


		$this->load->model('sale/order');
		$this->load->model('sale/aramex');
		$this->load->model('shipping/aramex');
		$this->document->addScript('view/javascript/jquery.chained.js');
		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}
		
		$order_info = $this->model_sale_order->getOrder($order_id);
		//echo "<pre>";
		//print_r($order_info);
		//echo "</pre>";
		if ($order_info) {
			

			$this->document->setTitle($this->language->get('heading_title'));
			
			############### label #############
			$this->data['text_back_to_order'] = $this->language->get('text_back_to_order');
			$this->data['text_create_sipment'] = $this->language->get('text_create_sipment');
			$this->data['text_rate_calculator'] = $this->language->get('text_rate_calculator');
			$this->data['text_schedule_pickup'] = $this->language->get('text_schedule_pickup');
			$this->data['text_print_label'] = $this->language->get('text_print_label');
			$this->data['text_track'] = $this->language->get('text_track');

            $this->data['text_billing_account'] = $this->language->get('text_billing_account');
            $this->data['entry_account'] = $this->language->get('entry_account');
            $this->data['entry_payment'] = $this->language->get('entry_payment');
            $this->data['text_global_setting_msg'] = $this->language->get('text_global_setting_msg');
            $this->data['text_shipper_details'] = $this->language->get('text_shipper_details');
            $this->data['text_receiver_details'] = $this->language->get('text_receiver_details');
            $this->data['entry_reference'] = $this->language->get('entry_reference');
            $this->data['entry_name'] = $this->language->get('entry_name');
            $this->data['entry_email'] = $this->language->get('entry_email');
            $this->data['entry_company'] = $this->language->get('entry_company');
            $this->data['entry_address'] = $this->language->get('entry_address');
            $this->data['entry_country'] = $this->language->get('entry_country');
            $this->data['entry_city'] = $this->language->get('entry_city');
            $this->data['entry_postal_code'] = $this->language->get('entry_postal_code');
            $this->data['entry_state'] = $this->language->get('entry_state');
            $this->data['entry_phone'] = $this->language->get('entry_phone');
            $this->data['text_shipper_account'] = $this->language->get('text_shipper_account');
            $this->data['text_consignee_account'] = $this->language->get('text_consignee_account');
            $this->data['text_third_party'] = $this->language->get('text_third_party');
            $this->data['text_shipment_information'] = $this->language->get('text_shipment_information');
            $this->data['entry_total_weight'] = $this->language->get('entry_total_weight');
            $this->data['entry_product_group'] = $this->language->get('entry_product_group');
            $this->data['entry_service_type'] = $this->language->get('entry_service_type');
            $this->data['entry_additional_services'] = $this->language->get('entry_additional_services');
            $this->data['entry_payment_type'] = $this->language->get('entry_payment_type');
            $this->data['entry_payment_option'] = $this->language->get('entry_payment_option');
            $this->data['entry_cod_amount'] = $this->language->get('entry_cod_amount');
            $this->data['entry_custom_amount'] = $this->language->get('entry_custom_amount');
            $this->data['entry_currency'] = $this->language->get('entry_currency');
            $this->data['entry_comment'] = $this->language->get('entry_comment');
            $this->data['entry_foreign_shipment_no'] = $this->language->get('entry_foreign_shipment_no');
            $this->data['entry_filename_1'] = $this->language->get('entry_filename_1');
            $this->data['entry_filename_2'] = $this->language->get('entry_filename_2');
            $this->data['entry_filename_3'] = $this->language->get('entry_filename_3');
            $this->data['entry_description'] = $this->language->get('entry_description');
            $this->data['entry_items_price'] = $this->language->get('entry_items_price');
            $this->data['entry_notify_customer'] = $this->language->get('entry_notify_customer');
            $this->data['entry_items_not_shipped_yet'] = $this->language->get('entry_items_not_shipped_yet');
            $this->data['entry_number_of_items'] = $this->language->get('entry_number_of_items');
            $this->data['text_product_qty'] = $this->language->get('text_product_qty');
            $this->data['text_product_name'] = $this->language->get('text_product_name');
            $this->data['text_return_shipment'] = $this->language->get('text_return_shipment');
            $this->data['text_domestic'] = $this->language->get('text_domestic');
            $this->data['text_inter_express'] = $this->language->get('text_inter_express');
            $this->data['text_prepaid'] = $this->language->get('text_prepaid');
            $this->data['text_collect'] = $this->language->get('text_collect');
            $this->data['text_payment_shipper'] = $this->language->get('text_payment_shipper');
            $this->data['text_payment_consignee'] = $this->language->get('text_payment_consignee');
            $this->data['text_payment_cash'] = $this->language->get('text_payment_cash');
            $this->data['text_payment_account'] = $this->language->get('text_payment_account');
            $this->data['text_payment_prepaid'] = $this->language->get('text_payment_prepaid');
            $this->data['text_payment_credit'] = $this->language->get('text_payment_credit');
            $this->data['text_kg'] = $this->language->get('text_kg');
            $this->data['text_lb'] = $this->language->get('text_lb');
            $this->data['text_Reset'] = $this->language->get('text_Reset');
            $this->data['error_consignee_account'] = $this->language->get('error_consignee_account');
            $this->data['error_create_shipment'] = $this->language->get('error_create_shipment');
            $this->data['error_one_shipment'] = $this->language->get('error_one_shipment');
			
			$this->data['heading_title'] = $this->language->get('heading_title');
			
			
			$this->data['breadcrumbs'] = array();

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', '', 'SSL'),
				'separator' => false
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('sale/order', '', 'SSL'),
				'separator' => ' :: '
			);

			$this->data['order_id'] = $this->request->get['order_id'];

			############ button ########## schedule_pickup
			$this->data['back_to_order'] = $this->url->link('sale/order/info', 'order_id=' . $order_id , 'SSL');
			$this->data['aramex_create_sipment'] = $this->url->link('sale/aramex_create_shipment', 'order_id=' . $order_id , 'SSL');
			$this->data['aramex_rate_calculator'] = $this->url->link('sale/aramex_rate_calculator', 'order_id=' . $order_id , 'SSL');
			$this->data['aramex_schedule_pickup'] = $this->url->link('sale/aramex_schedule_pickup', 'order_id=' . $order_id , 'SSL');
			$this->data['aramex_print_label'] = $this->url->link('sale/aramex_create_shipment/lable', 'order_id=' . $order_id , 'SSL');
			$this->data['aramex_traking'] = $this->url->link('sale/aramex_traking', 'order_id=' . $order_id , 'SSL');
			############ button ##########
			
			//$this->data['amazon_order_id'] = $order_info['amazon_order_id'];
			$this->data['store_name'] = $order_info['store_name'];
			$this->data['store_url'] = $order_info['store_url'];
			$this->data['firstname'] = $order_info['firstname'];
			$this->data['lastname'] = $order_info['lastname'];

			if ($order_info['customer_id']) {
				$this->data['customer'] = $this->url->link('sale/customer/update', 'customer_id=' . $order_info['customer_id'], 'SSL');
			} else {
				$this->data['customer'] = '';
			}

			$this->load->model('sale/customer_group');

			$customer_group_info = $this->model_sale_customer_group->getCustomerGroup($order_info['customer_group_id']);

			if ($customer_group_info) {
				$this->data['customer_group'] = $customer_group_info['name'];
			} else {
				$this->data['customer_group'] = '';
			}

			
	##################### config shipper details ################
	if(isset($this->request->post['aramex_shipment_shipper_name'])) {
			$this->data['aramex_shipment_shipper_name'] = $this->request->post['aramex_shipment_shipper_name'];
	}else{
			$this->data['aramex_shipment_shipper_name'] = ($this->config->get('aramex_shipper_name'))?$this->config->get('aramex_shipper_name'):'';
	}
	
	if(isset($this->request->post['aramex_shipment_shipper_email'])) {
			$this->data['aramex_shipment_shipper_email'] = $this->request->post['aramex_shipment_shipper_email'];
	}else{
			$this->data['aramex_shipment_shipper_email'] = ($this->config->get('aramex_shipper_email'))?$this->config->get('aramex_shipper_email'):'';
	}
	
	if(isset($this->request->post['aramex_shipment_shipper_company'])) {
			$this->data['aramex_shipment_shipper_company'] = $this->request->post['aramex_shipment_shipper_company'];
	}else{
			$this->data['aramex_shipment_shipper_company'] = ($this->config->get('aramex_shipper_company'))?$this->config->get('aramex_shipper_company'):'';
	}
	
	if(isset($this->request->post['aramex_shipment_shipper_street'])) {
			$this->data['aramex_shipment_shipper_street'] = $this->request->post['aramex_shipment_shipper_street'];
	}else{
			$this->data['aramex_shipment_shipper_street'] = ($this->config->get('aramex_shipper_address'))?$this->config->get('aramex_shipper_address'):'';
	}
	
	if(isset($this->request->post['aramex_shipment_shipper_country'])) {
			$this->data['aramex_shipment_shipper_country'] = $this->request->post['aramex_shipment_shipper_country'];
	}else{
			$this->data['aramex_shipment_shipper_country'] = ($this->config->get('aramex_shipper_country_code'))?$this->config->get('aramex_shipper_country_code'):'';
	}
	
	if(isset($this->request->post['aramex_shipment_shipper_city'])) {
			$this->data['aramex_shipment_shipper_city'] = $this->request->post['aramex_shipment_shipper_city'];
	}else{
			$this->data['aramex_shipment_shipper_city'] = ($this->config->get('aramex_shipper_city'))?$this->config->get('aramex_shipper_city'):'';
	}
	
	if(isset($this->request->post['aramex_shipment_shipper_postal'])) {
			$this->data['aramex_shipment_shipper_postal'] = $this->request->post['aramex_shipment_shipper_postal'];
	}else{
			$this->data['aramex_shipment_shipper_postal'] = ($this->config->get('aramex_shipper_postal_code'))?$this->config->get('aramex_shipper_postal_code'):'';
	}
	
	if(isset($this->request->post['aramex_shipment_shipper_state'])) {
			$this->data['aramex_shipment_shipper_state'] = $this->request->post['aramex_shipment_shipper_state'];
	}else{
			$this->data['aramex_shipment_shipper_state'] = ($this->config->get('aramex_shipper_state'))?$this->config->get('aramex_shipper_state'):'';
	}
	
	if(isset($this->request->post['aramex_shipment_shipper_phone'])) {
			$this->data['aramex_shipment_shipper_phone'] = $this->request->post['aramex_shipment_shipper_phone'];
	}else{
			$this->data['aramex_shipment_shipper_phone'] = ($this->config->get('aramex_shipper_phone'))?$this->config->get('aramex_shipper_phone'):'';
	}
				
	##################### customer shipment details ################
	
	$shipment_receiver_name ='';
	$shipment_receiver_street ='';
	if(isset($order_info['shipping_firstname']) && !empty($order_info['shipping_firstname']))
	{
		$shipment_receiver_name .= $order_info['shipping_firstname'];
	}
	if(isset($order_info['shipping_lastname']) && !empty($order_info['shipping_lastname']))
	{
		$shipment_receiver_name .= " ".$order_info['shipping_lastname'];
	}
	if(isset($order_info['shipping_address_1']) && !empty($order_info['shipping_address_1']))
	{
		$shipment_receiver_street .= $order_info['shipping_address_1'];
	}
	if(isset($order_info['shipping_address_2']) && !empty($order_info['shipping_address_2']))
	{
		$shipment_receiver_street .= " ".$order_info['shipping_address_2'];
	}
	
	if(isset($this->request->post['aramex_shipment_receiver_name'])) {
			$this->data['aramex_shipment_receiver_name'] = $this->request->post['aramex_shipment_receiver_name'];
	}else{
			$this->data['aramex_shipment_receiver_name']    = $shipment_receiver_name;
	}
	
	if(isset($this->request->post['aramex_shipment_receiver_email'])) {
			$this->data['aramex_shipment_receiver_email'] = $this->request->post['aramex_shipment_receiver_email'];
	}else{
			$this->data['aramex_shipment_receiver_email']   = ($order_info['email'])?$order_info['email']:'';
	}
	
	if(isset($this->request->post['aramex_shipment_receiver_company'])) {
			$this->data['aramex_shipment_receiver_company'] = $this->request->post['aramex_shipment_receiver_company'];
	}else{
			$this->data['aramex_shipment_receiver_company'] = ($order_info['shipping_company'])?$order_info['shipping_company']:'';
	}
	
	if(isset($this->request->post['aramex_shipment_receiver_street'])) {
			$this->data['aramex_shipment_receiver_street'] = $this->request->post['aramex_shipment_receiver_street'];
	}else{
			$this->data['aramex_shipment_receiver_street']  = $shipment_receiver_street;
	}
	
	if(isset($this->request->post['aramex_shipment_receiver_country'])) {
			$this->data['aramex_shipment_receiver_country'] = $this->request->post['aramex_shipment_receiver_country'];
	}else{
			$this->data['aramex_shipment_receiver_country'] = ($order_info['shipping_iso_code_2'])?$order_info['shipping_iso_code_2']:'';
	}
	
	if(isset($this->request->post['aramex_shipment_receiver_city'])) {
			$this->data['aramex_shipment_receiver_city'] = $this->request->post['aramex_shipment_receiver_city'];
	}else{
		if($this->config->get('aramex_cities_table') && $this->config->get('aramex_cities_table') == 1){
			$this->data['aramex_shipment_receiver_city'] = ($order_info['shipping_area'])?$order_info['shipping_area']:'';
		}else{
			$this->data['aramex_shipment_receiver_city']  = ($order_info['shipping_city'])?$order_info['shipping_city']:'';
		}
	}
	
	if(isset($this->request->post['aramex_shipment_receiver_postal'])) {
			$this->data['aramex_shipment_receiver_postal'] = $this->request->post['aramex_shipment_receiver_postal'];
	}else{
			$this->data['aramex_shipment_receiver_postal']  = ($order_info['shipping_postcode'])?$order_info['shipping_postcode']:'';
	}
	
	if(isset($this->request->post['aramex_shipment_receiver_state'])) {
			$this->data['aramex_shipment_receiver_state'] = $this->request->post['aramex_shipment_receiver_state'];
	}else{
			$this->data['aramex_shipment_receiver_state']   = ($order_info['shipping_zone'])?$order_info['shipping_zone']:'';
	}
	if(isset($this->request->post['aramex_shipment_receiver_state_id'])) {
		$this->data['aramex_shipment_receiver_state_id'] = $this->request->post['aramex_shipment_receiver_state_id'];
	}else{
		$this->data['aramex_shipment_receiver_state_id']   = ($order_info['shipping_zone_id'])?$order_info['shipping_zone_id']:'';
	}
	if(isset($this->request->post['aramex_shipment_receiver_area_id'])) {
		$this->data['aramex_shipment_receiver_area_id'] = $this->request->post['aramex_shipment_receiver_area_id'];
	}else{
	    $this->data['aramex_shipment_receiver_area_id']   = ($order_info['shipping_area_id'])?$order_info['shipping_area_id']:'';
	}


	if(isset($this->request->post['aramex_shipment_receiver_phone'])) {
			$this->data['aramex_shipment_receiver_phone'] = $this->request->post['aramex_shipment_receiver_phone'];
	}else{
			$this->data['aramex_shipment_receiver_phone']   = ($order_info['telephone'])?$order_info['telephone']:'';
	}
	
	#################
	
	$this->load->model('localisation/country');
    $this->data['countries'] = $this->model_localisation_country->getCountries();
	$this->data['reference'] = $order_id;
	
	$this->data['aramex_shipment_shipper_account'] = ($this->config->get('aramex_account_number'))?$this->config->get('aramex_account_number'):'';
	$this->data['additional_account'] = ($this->config->get('additional_account'))?$this->config->get('additional_account'): NULL;

	
	$this->data['aramex_allowed_domestic_methods'] = ($this->config->get('aramex_allowed_domestic_methods'))?$this->config->get('aramex_allowed_domestic_methods'):'';	
    $this->data['aramex_allowed_domestic_additional_services'] = ($this->config->get('aramex_allowed_domestic_additional_services'))?$this->config->get('aramex_allowed_domestic_additional_services'):'';			
	$this->data['aramex_allowed_international_methods'] = ($this->config->get('aramex_allowed_international_methods'))?$this->config->get('aramex_allowed_international_methods'):'';					
	$this->data['aramex_allowed_international_additional_services'] = ($this->config->get('aramex_allowed_international_additional_services'))?$this->config->get('aramex_allowed_international_additional_services'):'';							
	
    	
	$this->data['all_allowed_domestic_methods'] = $this->model_shipping_aramex->domesticmethods();
	$this->data['all_allowed_domestic_additional_services'] = $this->model_shipping_aramex->domesticAdditionalServices();		
	$this->data['all_allowed_international_methods'] = $this->model_shipping_aramex->internationalmethods();
	$this->data['all_allowed_international_additional_services'] = $this->model_shipping_aramex->internationalAdditionalServices();	

	$this->data['aramex_custom_pieces'] = $this->config->get('aramex_custom_pieces') ? $this->config->get('aramex_custom_pieces') : 0;
			
			
	//print_r($this->request->post);
	
	if(isset($this->request->post['aramex_shipment_info_billing_account']) && !empty($this->request->post['aramex_shipment_info_billing_account'])) {
			$this->data['aramex_shipment_info_billing_account'] = $this->request->post['aramex_shipment_info_billing_account'];
	}else{
			$this->data['aramex_shipment_info_billing_account'] = "";
	}
	if(isset($this->request->post['aramex_shipment_info_product_group'])) {
			$this->data['group'] = $this->request->post['aramex_shipment_info_product_group'];
	}else{
			$this->data['group'] = "";
	}
	if(isset($this->request->post['aramex_shipment_info_product_type'])) {
			$this->data['type'] = $this->request->post['aramex_shipment_info_product_type'];
	}else{
			$this->data['type'] = "";
	}
	if(isset($this->request->post['aramex_shipment_info_service_type'])) {
			$this->data['stype'] = $this->request->post['aramex_shipment_info_service_type'];
	}else{
			$this->data['stype'] = "";
	}
	if(isset($this->request->post['aramex_shipment_info_payment_type'])) {
			$this->data['pay_type'] = $this->request->post['aramex_shipment_info_payment_type'];
	}else{
			$this->data['pay_type'] = '';
	}
	if(isset($this->request->post['aramex_shipment_info_payment_option'])) {
			$this->data['pay_option'] = $this->request->post['aramex_shipment_info_payment_option'];
	}else{
			$this->data['pay_option'] = '';
	}
	
	if(isset($this->request->post['aramex_shipment_currency_code'])) {
			$this->data['currency_code'] = $this->request->post['aramex_shipment_currency_code'];
	}else{
			$this->data['currency_code'] = ($order_info['currency_code'])?$order_info['currency_code']:'';;
	}
	
	if(isset($this->request->post['aramex_shipment_info_cod_amount'])) {
			$this->data['cod_value'] = $this->request->post['aramex_shipment_info_cod_amount'];
	}else{
			$this->data['cod_value'] = $order_info['total'] ? $order_info['total'] : '';
	}
	
	if(isset($this->request->post['aramex_shipment_info_custom_amount'])) {
			$this->data['custom_amount'] = $this->request->post['aramex_shipment_info_custom_amount'];
	}else{
			$this->data['custom_amount'] = '';;
	}
	if(isset($this->request->post['aramex_shipment_info_comment'])) {
			$this->data['aramex_shipment_info_comment'] = $this->request->post['aramex_shipment_info_comment'];
	}else{
			$this->data['aramex_shipment_info_comment'] = '';;
	}
	if(isset($this->request->post['aramex_shipment_info_foreignhawb'])) {
			$this->data['aramex_shipment_info_foreignhawb'] = $this->request->post['aramex_shipment_info_foreignhawb'];
	}else{
			$this->data['aramex_shipment_info_foreignhawb'] = '';;
	}
	if(isset($this->request->post['weight_unit']) && !empty($this->request->post['weight_unit'])) {
			$getunit_classid = $this->model_sale_aramex->getWeightClassId($this->request->post['weight_unit']);
			$this->data['weight_unit'] = $getunit_classid->row['unit'];
			$config_weight_class_id = $getunit_classid->row['weight_class_id'];
	}else{
			$this->data['weight_unit'] = $this->weight->getUnit($this->config->get('config_weight_class_id'));
			$config_weight_class_id = $this->config->get('config_weight_class_id');
	}
	

	
		$this->data['total'] = ($order_info['total'])?number_format($order_info['total'],2):'';

		$this->data['payment_code'] = $order_info['payment_code'];
		
	    ########### product list ##########
		if (isset($this->request->post['order_product'])) {
			$order_products = $this->request->post['order_product'];
		} elseif (isset($this->request->get['order_id'])) {
			$order_products = $this->model_sale_order->getOrderProducts($this->request->get['order_id']);
		} else {
			$order_products = array();
		}
		$this->data['order_products'] = array();
		$weighttot = 0;
		foreach ($order_products as $order_product) {
			if (isset($order_product['order_option'])) {
				$order_option = $order_product['order_option'];
			} elseif (isset($this->request->get['order_id'])) {

				$order_option = $this->model_sale_order->getOrderOptions($this->request->get['order_id'], $order_product['order_product_id']);
				// get option value for add product option weight to product actual weight
				if(count($order_option) > 0) {
					$product_option_value = $this->model_sale_order->getProductOptionValue((int)$order_option[0]['product_option_value_id']);
				}
				$product_weight_query = $this->db->query("SELECT weight, weight_class_id FROM " . DB_PREFIX . "product WHERE product_id = '" . $order_product['product_id'] . "'");
				$weight_class_query = $this->db->query("SELECT wcd.unit FROM " . DB_PREFIX . "weight_class wc LEFT JOIN " . DB_PREFIX . "weight_class_description wcd ON (wc.weight_class_id = wcd.weight_class_id) WHERE wcd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND wc.weight_class_id = '" . $product_weight_query->row['weight_class_id'] . "'");
			} else {
				$order_option = array();
			}
			/*
			if (isset($order_product['order_download'])) {
				$order_download = $order_product['order_download'];
			} elseif (isset($this->request->get['order_id'])) {
				$order_download = $this->model_sale_order->getOrderDownloads($this->request->get['order_id'], $order_product['order_product_id']);
			} else {
				$order_download = array();
			}
			*/
			$prodweight = $this->weight->convert($product_weight_query->row['weight'], $product_weight_query->row['weight_class_id'], $config_weight_class_id);
			// check if product has option
			if(isset($product_option_value) && is_array($product_option_value) && count($product_option_value) > 0)
			{
				$prodOptionWeight = $this->weight->convert($product_option_value[0]['weight'], $product_weight_query->row['weight_class_id'], $config_weight_class_id);
				if($product_option_value[0]['weight_prefix'] == '+'){
					$prodweight = ($prodweight + $prodOptionWeight);
				}else{
					$prodweight = ($prodweight - $prodOptionWeight);
				}
			}
			$prodweight = ($prodweight * $order_product['quantity']);

			$weighttot = ($weighttot + $prodweight);


			$this->data['product_arr'][] = $order_product['name'];
			$this->data['order_products'][] = array(
				'order_product_id' => $order_product['order_product_id'],
				'product_id'       => $order_product['product_id'],
				'name'             => $order_product['name'],
				'model'            => $order_product['model'],
				'option'           => $order_option,
				//'download'         => $order_download,
				'quantity'         => $order_product['quantity'],
				'weight' 		   => number_format($this->weight->convert($product_weight_query->row['weight'], $product_weight_query->row['weight_class_id'], $config_weight_class_id),2),
				'weight_class'     => $weight_class_query->row['unit'],
				'price'            => number_format($order_product['price'],2),
				'total'            => $order_product['total'],
				'tax'              => $order_product['tax'],
				'reward'           => $order_product['reward']
			);
		}
		$this->data['weighttot'] = number_format($weighttot,2);
		$this->data['total'] = number_format($order_info['total'],2);

		################## create shipment ###########
		if ($this->request->post) {


			$baseUrl = $this->model_sale_aramex->getWsdlPath();
            //var_dump($baseUrl);
            //die();
			//SOAP object
			$soapClient = new SoapClient($baseUrl . '/shipping.wsdl');
			$aramex_errors = false;


			$flag = true;
			$error = "";
			try {
				
	

				$totalWeight 	= 0;
				$totalItems 	= 0;

				$aramex_items_counter = 0;
				foreach($this->request->post['aramex_items'] as $key => $value){
					$aramex_items_counter++;
					if($value != 0){
						//itrating order items
						foreach($order_products as $item){
							if($item['order_product_id'] == $key){
								//get weight
								$weight = $this->weight->convert($product_weight_query->row['weight'], $product_weight_query->row['weight_class_id'], $config_weight_class_id);
								$weight = ($weight * $item['quantity']);


								$order_product_id = $item['order_product_id'];
								// collect items for aramex
								$aramex_items[]	= array(
									'PackageType'	=> 'Box',
									'Quantity'		=> $this->request->post[$order_product_id],
									'Weight'		=> array(
										'Value'	    => $weight,
										'Unit'	    => 'Kg'
									),
									'Comments'		=> $item['order_product_id'], //'',
									'Reference'		=> ''
								);

								$totalWeight 	+= $weight;
								$totalItems 	+= $this->request->post[$order_product_id];
							}
						}
					}
				}

				if ($this->data['aramex_custom_pieces'] == 1) {
					if (!empty($this->request->post['aramex_custom_product_pieces']) && is_numeric($this->request->post['aramex_custom_product_pieces'])) {
						$totalItems = (int) $this->request->post['aramex_custom_product_pieces'];
					}
				}

                $aramex_atachments=array();
				//attachment
                for($i=1;$i<=3;$i++){
    				if(isset($fileName)!=''){
    					$fileName = explode('.', $fileName);
    					$fileName = $fileName[0]; //filename without extension
						$fileData ='';
						if($_FILES['file'.$i]['tmp_name']!='')
						$fileData = file_get_contents($_FILES['file'.$i]['tmp_name']);
    					$ext = pathinfo($_FILES['file'.$i]['name'], PATHINFO_EXTENSION); //file extension
                        if($fileName&&$ext&&$fileData)
    					$aramex_atachments[] = array(
    								'FileName'				=> $fileName,
    								'FileExtension'			=> $ext,
    								'FileContents'			=> $fileData
    					);
    				}
                }
                // allow admin to edit shipment weight
				//$totalWeight = number_format($this->request->post['weighttot'],2);

				$params = array();

				//shipper parameters
				$params['Shipper'] = array(
					'Reference1' 		=> ($this->request->post['aramex_shipment_shipper_reference'])?$this->request->post['aramex_shipment_shipper_reference']:'', //'ref11111',
					'Reference2' 		=> '',
					'AccountNumber' 	=> ($this->request->post['aramex_shipment_shipper_account'])?$this->request->post['aramex_shipment_shipper_account']:'', //'43871',

					//Party Address
					
					
					'PartyAddress'		=> array(
								'Line1'					=> ($this->request->post['aramex_shipment_shipper_street'])?addslashes($this->request->post['aramex_shipment_shipper_street']):'', //'13 Mecca St',
								'Line2'					=> '',
								'Line3'					=> '',
								'City'					=> ($this->request->post['aramex_shipment_shipper_city'])?$this->request->post['aramex_shipment_shipper_city']:'', //'Dubai',
								'StateOrProvinceCode'	=> ($this->request->post['aramex_shipment_shipper_state'])?$this->request->post['aramex_shipment_shipper_state']:'', //'',
								'PostCode'				=> ($this->request->post['aramex_shipment_shipper_postal'])?$this->request->post['aramex_shipment_shipper_postal']:'',
								'CountryCode'			=> ($this->request->post['aramex_shipment_shipper_country'])?$this->request->post['aramex_shipment_shipper_country']:'', //'AE'
					),

					//Contact Info 
					'Contact' 			=> array(
								'Department'			=> '',
								'PersonName'			=> ($this->request->post['aramex_shipment_shipper_name'])?$this->request->post['aramex_shipment_shipper_name']:'', //'Suheir',
								'Title'					=> '',
								'CompanyName'			=> ($this->request->post['aramex_shipment_shipper_company'])?$this->request->post['aramex_shipment_shipper_company']:'', //'Aramex',
								'PhoneNumber1'			=> ($this->request->post['aramex_shipment_shipper_phone'])?$this->request->post['aramex_shipment_shipper_phone']:'', //'55555555',
								'PhoneNumber1Ext'		=> '',
								'PhoneNumber2'			=> '',
								'PhoneNumber2Ext'		=> '',
								'FaxNumber'				=> '',
								'CellPhone'				=> ($this->request->post['aramex_shipment_shipper_phone'])?$this->request->post['aramex_shipment_shipper_phone']:'',
								'EmailAddress'			=> ($this->request->post['aramex_shipment_shipper_email'])?$this->request->post['aramex_shipment_shipper_email']:'', //'',
								'Type'					=> ''
					),
				);

				// Get State/Zone name in English
				if($this->config->get('aramex_cities_table')&&$this->config->get('aramex_cities_table') == 1)
				{
					$this->load->model('localisation/area');
					$aramexCityName = $this->model_localisation_area->getAreaInLanguage($this->request->post['aramex_shipment_receiver_area_id'],1);
				}else{
					$this->load->model('localisation/zone');
					$aramexCityName = $this->model_localisation_zone->getZoneInLanguage($this->request->post['aramex_shipment_receiver_state_id'],1);
				}

				//consinee parameters
				$params['Consignee'] = array(
					'Reference1' 		=> ($this->request->post['aramex_shipment_receiver_reference'])?$this->request->post['aramex_shipment_receiver_reference']:'', //'',
					'Reference2'		=> '',
					'AccountNumber'		=> ($this->request->post['aramex_shipment_info_billing_account'] == 2 || $this->request->post['aramex_shipment_info_payment_type'] == 'C') ? $this->request->post['aramex_shipment_shipper_account'] : '',

					//Party Address 
					

					'PartyAddress'		=> array(
								'Line1'					=> ($this->request->post['aramex_shipment_receiver_street'])?$this->request->post['aramex_shipment_receiver_street']:'', //'15 ABC St',
								'Line2'					=> '',
								'Line3'					=> '',
								'City'					=> ($aramexCityName['name'])?$aramexCityName['name']:$this->request->post['aramex_shipment_receiver_state'], //'Amman',
								'StateOrProvinceCode'	=> '',
								'PostCode'				=> ($this->request->post['aramex_shipment_receiver_postal'])?$this->request->post['aramex_shipment_receiver_postal']:'',
								'CountryCode'			=> ($this->request->post['aramex_shipment_receiver_country'])?$this->request->post['aramex_shipment_receiver_country']:'', //'JO'
					),
					
					//Contact Info
					'Contact' 			=> array(
								'Department'			=> '',
								'PersonName'			=> ($this->request->post['aramex_shipment_receiver_name'])?$this->request->post['aramex_shipment_receiver_name']:'', //'Mazen',
								'Title'					=> '',
								'CompanyName'			=> ($this->request->post['aramex_shipment_receiver_company'])?$this->request->post['aramex_shipment_receiver_company']:'individual', //'Aramex',
								'PhoneNumber1'			=> ($this->request->post['aramex_shipment_receiver_phone'])?$this->request->post['aramex_shipment_receiver_phone']:'', //'6666666',
								'PhoneNumber1Ext'		=> '',
								'PhoneNumber2'			=> '',
								'PhoneNumber2Ext'		=> '',
								'FaxNumber'				=> '',
								'CellPhone'				=> ($this->request->post['aramex_shipment_receiver_phone'])?$this->request->post['aramex_shipment_receiver_phone']:'',
								'EmailAddress'			=> ($this->request->post['aramex_shipment_receiver_email'])?$this->request->post['aramex_shipment_receiver_email']:'', //'mazen@aramex.com',
								'Type'					=> ''
					)
				);

				//new

				if($this->request->post['aramex_shipment_info_billing_account'] == 3){
					$params['ThirdParty'] = array(
						'Reference1' 		=> ($this->request->post['aramex_shipment_third_party_reference'])?$this->request->post['aramex_shipment_third_party_reference']:'', //'ref11111',
						'Reference2' 		=> '',
						'AccountNumber' 	=> ($this->request->post['aramex_shipment_shipper_account'])?$this->request->post['aramex_shipment_shipper_account']:'', //'43871',

						//Party Address
						'PartyAddress'		=> array(
									'Line1'					=> ($this->request->post['aramex_shipment_third_party_street'])?$this->request->post['aramex_shipment_third_party_street']:'', //'13 Mecca St',
									'Line2'					=> '',
									'Line3'					=> '',
									'City'					=> ($this->request->post['aramex_shipment_third_party_city'])?$this->request->post['aramex_shipment_third_party_city']:'', //'Dubai',
									'StateOrProvinceCode'	=> ($this->request->post['aramex_shipment_third_party_state'])?$this->request->post['aramex_shipment_third_party_state']:'', //'',
									'PostCode'				=> ($this->request->post['aramex_shipment_third_party_postal'])?$this->request->post['aramex_shipment_third_party_postal']:'',
									'CountryCode'			=> ($this->request->post['aramex_shipment_third_party_country'])?$this->request->post['aramex_shipment_third_party_country']:'', //'AE'
						),

						//Contact Info
						'Contact' 			=> array(
									'Department'			=> '',
									'PersonName'			=> ($this->request->post['aramex_shipment_third_party_name'])?$this->request->post['aramex_shipment_third_party_name']:'', //'Suheir',
									'Title'					=> '',
									'CompanyName'			=> ($this->request->post['aramex_shipment_third_party_company'])?$this->request->post['aramex_shipment_third_party_company']:'', //'Aramex',
									'PhoneNumber1'			=> ($this->request->post['aramex_shipment_third_party_phone'])?$this->request->post['aramex_shipment_third_party_phone']:'', //'55555555',
									'PhoneNumber1Ext'		=> '',
									'PhoneNumber2'			=> '',
									'PhoneNumber2Ext'		=> '',
									'FaxNumber'				=> '',
									'CellPhone'				=> ($this->request->post['aramex_shipment_third_party_phone'])?$this->request->post['aramex_shipment_third_party_phone']:'',
									'EmailAddress'			=> ($this->request->post['aramex_shipment_third_party_email'])?$this->request->post['aramex_shipment_third_party_email']:'', //'',
									'Type'					=> ''
						),
					);

				}

				// Other Main Shipment Parameters
				$params['Reference1'] 				= ($this->request->post['aramex_shipment_info_reference'])?$this->request->post['aramex_shipment_info_reference']:''; //'Shpt0001';
				$params['Reference2'] 				= '';
				$params['Reference3'] 				= '';
				$params['ForeignHAWB'] 				= ($this->request->post['aramex_shipment_info_foreignhawb'])?$this->request->post['aramex_shipment_info_foreignhawb']:'';

				$params['TransportType'] 			= 0;
				$params['ShippingDateTime'] 		= time(); //date('m/d/Y g:i:sA');
				$params['DueDate'] 					= time() + (7 * 24 * 60 * 60); //date('m/d/Y g:i:sA');
				$params['PickupLocation'] 			= 'Reception';
				$params['PickupGUID'] 				= '';				
				$params['Comments'] 				= ($this->request->post['aramex_shipment_info_comment'])?$this->request->post['aramex_shipment_info_comment']:'';
				$params['AccountingInstrcutions'] 	= '';
				$params['OperationsInstructions'] 	= '';
				$params['Details'] = array(
								'Dimensions'			=> array(
									'Length'	=> '0',
									'Width'		=> '0',
									'Height'	=> '0',
									'Unit'		=> 'cm'
								),

								'ActualWeight'=> array(
									'Value'		=> $totalWeight,
									'Unit'		=> $this->request->post['weight_unit']
								),
								'ProductGroup'			=> ($this->request->post['aramex_shipment_info_product_group'])?$this->request->post['aramex_shipment_info_product_group']:'', //'EXP',
								'ProductType'			=> $this->request->post['aramex_shipment_info_product_type'], //,'PDX'


								'PaymentType'			=> ($this->request->post['aramex_shipment_info_payment_type'])?$this->request->post['aramex_shipment_info_payment_type']:'',


								'PaymentOptions'		=> ($this->request->post['aramex_shipment_info_payment_option'])?$this->request->post['aramex_shipment_info_payment_option']:'', //$post['aramex_shipment_info_payment_option']


								'Services'				=> ($this->request->post['aramex_shipment_info_service_type'])?$this->request->post['aramex_shipment_info_service_type']:'',

								'NumberOfPieces'		=> $totalItems,
								'DescriptionOfGoods'	=> ($this->request->post['aramex_shipment_description'])?$this->request->post['aramex_shipment_description']:'',
								'GoodsOriginCountry'	=> ($this->request->post['aramex_shipment_shipper_country'])?$this->request->post['aramex_shipment_shipper_country']:'', //'JO',
								'Items'					=> $aramex_items,
				);

                if(count($aramex_atachments)){
                  $params['Attachments'] = $aramex_atachments;
                } 

                if($this->request->post['aramex_shipment_info_service_type'] == 'CODS'){
					$params['Details']['CashOnDeliveryAmount'] = array(
							'Value' 		=> ($this->request->post['aramex_shipment_info_cod_amount'])?$this->request->post['aramex_shipment_info_cod_amount']:'', 
							'CurrencyCode' 	=>  ($this->request->post['aramex_shipment_currency_code'])?$this->request->post['aramex_shipment_currency_code']:''
					);
				}

				$params['Details']['CustomsValueAmount'] = array(
						'Value' 		=> ($this->request->post['aramex_shipment_info_custom_amount'])?$this->request->post['aramex_shipment_info_custom_amount']:'', 
						'CurrencyCode' 	=>  ($this->request->post['aramex_shipment_currency_code'])?$this->request->post['aramex_shipment_currency_code']:''
				);

				$major_par['Shipments'][] 	= $params;
				$clientInfo = $this->model_sale_aramex->getClientInfo();
				$major_par['ClientInfo'] 	=$clientInfo;
				

				$report_id = ($this->config->get('aramex_report_id'))?$this->config->get('aramex_report_id'):'9729';
			
  			    $major_par['LabelInfo'] = array(
						'ReportID'		=> $report_id, //'9201',
						'ReportType'	=> 'URL'
				);
				try {
					//create shipment call
					
					$auth_call = $soapClient->CreateShipments($major_par);
                    if($auth_call->HasErrors){
						if(!isset($auth_call->Shipments->ProcessedShipment)){

                            if(count($auth_call->Notifications->Notification) > 1){
                                foreach($auth_call->Notifications->Notification as $notify_error){

                                    $this->data['eRRORS'][] = 'Aramex: ' . $notify_error->Code .' - '. $notify_error->Message;
                                }
                            } else {
                                $this->data['eRRORS'][] = 'Aramex: ' . $auth_call->Notifications->Notification->Code . ' - '. $auth_call->Notifications->Notification->Message;
                            }
                            if(count($this->data['eRRORS']) > 0){

                                $result['success']='0';
                                $this->data['eRRORS']['warning']=$this->language->get('error_api');
                                $result['errors']= $this->data['eRRORS'];
                                return $this->response->setOutput(json_encode($result));
                            }

						} else {
						    if(isset($auth_call->Shipments->ProcessedShipment->Notifications->Notification)){
                                if(count($auth_call->Shipments->ProcessedShipment->Notifications->Notification) > 1){
                                    $notification_string = '';
                                    foreach($auth_call->Shipments->ProcessedShipment->Notifications->Notification as $notification_error){
                                        $notification_string .= $notification_error->Code .' - '. $notification_error->Message . ' <br />';
                                    }
                                    $this->data['eRRORS'][] = $notification_string;
                                } else {

                                    $this->data['eRRORS'][] = 'Aramex: ' . $auth_call->Shipments->ProcessedShipment->Notifications->Notification->Code .' - '. $auth_call->Shipments->ProcessedShipment->Notifications->Notification->Message;
                                }
                                if(count($this->data['eRRORS']) > 0){
                                    $result['success']='0';
                                    $this->data['eRRORS']['warning']=$this->language->get('error_api');
                                    $result['errors']= $this->data['eRRORS'];
                                    return $this->response->setOutput(json_encode($result));
                                }
                            }
						}

					} else {


						$shipmenthistory = "AWB No. ".$auth_call->Shipments->ProcessedShipment->ID." - Order No. ".$auth_call->Shipments->ProcessedShipment->Reference1;
						if(isset($this->request->post['aramex_email_customer']) && $this->request->post['aramex_email_customer'] == 'yes')
						{
						
								$is_email = 1;
						}else{
								$is_email = 0;
						}
						$message = array(
							'notify' => $is_email,
							'comment' => $shipmenthistory
						);
						$this->model_sale_aramex->addOrderHistory($this->request->get['order_id'], $message);
						$this->session->data['success_html'] = $shipmenthistory;


                        $result['success'] = '1';
						$result['redirect'] = '1';
						$result['to'] =  $_SERVER['HTTP_REFERER'];
                        $result['success_msg'] = $this->language->get("text_shippment_placed_successfully");
                        return $this->response->setOutput(json_encode($result));

					}
				} catch (Exception $e) {

					$aramex_errors = true;
					$this->data['eRRORS'][] = $e->getMessage();

					$result['success']='0';
                    $result['errors']['warning'] = $this->language->get('error_api');
					$result['errors']['API']= $e->getMessage();
                    return $this->response->setOutput(json_encode($result));

                }
                
			} catch (Exception $e) {
				$this->data['eRRORS'][] =  $e->getMessage();

                $result['success']='0';
                $result['errors']['warning'] = $this->language->get('error_api');
				$result['errors']['API']= $e->getMessage();
                return $this->response->setOutput(json_encode($result));

            }
        }


		################## create shipment end ###########
		
		
		$this->data['is_shipment'] = $this->model_sale_aramex->checkAWB($this->request->get['order_id']);

		if ( isset($this->session->data['success_html']) )
        {
			$this->data['success_html'] = $this->session->data['success_html'];
			unset($this->session->data['success_html']);
		}
        else
        {
			$this->data['success_html'] = '';
		}
        
		$this->base = 'common/base';
        $this->template = 'sale/aramex_create_shipment.expand';

			$this->response->setOutput($this->render_ecwig());
            return;
	} else {
			$this->language->load('error/not_found');

			$this->document->setTitle($this->language->get('heading_title'));

			$this->data['heading_title'] = $this->language->get('heading_title');

			$this->data['text_not_found'] = $this->language->get('text_not_found');

			$this->data['breadcrumbs'] = array();

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', '', 'SSL'),
				'separator' => false
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('error/not_found', '', 'SSL'),
				'separator' => ' :: '
			);

			$this->template = 'error/not_found.expand';
            $this->base = "common/base";

			$this->response->setOutput($this->render_ecwig());
		}
	}
	
	public function lable()
	{
		$this->load->model('sale/order');
		$this->load->model('sale/aramex');
		$this->load->model('shipping/aramex');
		
		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}
		
		
		if($order_id)
		{
		    $shipments = $this->model_sale_aramex->getOrderHistoriesAWB($this->request->get['order_id'], 0, 100);
			$baseUrl = $this->model_sale_aramex->getWsdlPath();
            $soapClient = new SoapClient($baseUrl . '/shipping.wsdl');
            $clientInfo = $this->model_sale_aramex->getClientInfo();
				
			if($shipments)
			{
			
						foreach($shipments as $key=>$comment)
						{
							$cmnt_txt = ($comment['comment'])?$comment['comment']:'';
							if (version_compare(PHP_VERSION, '5.3.0') <= 0) {
								$awbno = substr($cmnt_txt,0, strpos($cmnt_txt,"- Order No")); 
							}
							else{				
								$awbno = strstr($cmnt_txt,"- Order No",true);
							}
								$awbno=trim($awbno,"AWB No.");					
								break;
						}
						
						
						$report_id = ($this->config->get('aramex_report_id'))?$this->config->get('aramex_report_id'):0;
						if(!$report_id){
							$report_id =9729;
						}
						
						$params = array(		
					
						'ClientInfo'  			=> $clientInfo,

						'Transaction' 			=> array(
													'Reference1'			=> $order_id,
													'Reference2'			=> '', 
													'Reference3'			=> '', 
													'Reference4'			=> '', 
													'Reference5'			=> '',									
												),
						'LabelInfo'				=> array(
													'ReportID' 				=> $report_id,
													'ReportType'			=> 'URL',
						),
						);
						$params['ShipmentNumber'] = $awbno;
				
						try {
							$auth_call = $soapClient->PrintLabel($params);
							/* bof  PDF demaged Fixes debug */				
							if($auth_call->HasErrors){
							  if(count($auth_call->Notifications->Notification) > 1){
										foreach($auth_call->Notifications->Notification as $notify_error){
											$this->data['eRRORS'][] = 'Aramex: ' . $notify_error->Code .' - '. $notify_error->Message;
										}
									} else {
										$this->data['eRRORS'][] = 'Aramex: ' . $auth_call->Notifications->Notification->Code . ' - '. $auth_call->Notifications->Notification->Message;
										
									}					
							}
							/* eof  PDF demaged Fixes */					
							$filepath=$auth_call->ShipmentLabel->LabelURL;

                            $agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13';
                            $filepath = str_replace("http://","https://",$filepath);
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_HEADER, 0);
                            curl_setopt($ch, CURLOPT_URL, $filepath);
                            curl_setopt($ch,CURLOPT_USERAGENT,$agent);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                            $name="{$order_id}-shipment-label.pdf";
							header('Content-type: application/pdf');
							header('Content-Disposition: attachment; filename="'.$name.'"');

                            $result = curl_exec($ch);
                            if($result==false)
                            { readfile($filepath); }
                            else
                            { echo $result; }

							exit();					
						} catch (SoapFault $fault) {					
							$this->data['eRRORS'][] = 'Error : ' . $fault->faultstring;
							
						}
						catch (Exception $e) {
							
							$this->data['eRRORS'][] = $e->getMessage();
						
						}
			}
			else
			{
					$this->data['eRRORS'][] = 'Shipment is empty or not created yet.';
			}
		}else
		{
				    $this->data['eRRORS'][] = 'This order no longer exists.';
		}
	}
}
?>
