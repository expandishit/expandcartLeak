<?php
class ControllerSaleAramexRateCalculator extends Controller {
	
	private $error = array();

	public function index() {

		$this->language->load('sale/aramex');
		$this->document->setTitle($this->language->get('heading_title_rate'));
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
			
			
			$this->document->setTitle($this->language->get('heading_title_rate'));
			
			############### label #############
			$this->data['text_back_to_order'] = $this->language->get('text_back_to_order');
			$this->data['text_create_sipment'] = $this->language->get('text_create_sipment');
			$this->data['text_rate_calculator'] = $this->language->get('text_rate_calculator');
			$this->data['text_schedule_pickup'] = $this->language->get('text_schedule_pickup');
			$this->data['text_print_label'] = $this->language->get('text_print_label');
			$this->data['text_track'] = $this->language->get('text_track');

            $this->data['text_return_shipment'] = $this->language->get('text_return_shipment');
            $this->data['text_calc_rates'] = $this->language->get('text_calc_rates');
            $this->data['text_ship_origin'] = $this->language->get('text_ship_origin');
            $this->data['entry_country'] = $this->language->get('entry_country');
            $this->data['entry_city'] = $this->language->get('entry_city');
            $this->data['entry_postal_code'] = $this->language->get('entry_postal_code');
            $this->data['entry_state'] = $this->language->get('entry_state');
            $this->data['entry_payment_type'] = $this->language->get('entry_payment_type');
            $this->data['entry_product_group'] = $this->language->get('entry_product_group');
            $this->data['entry_service_type'] = $this->language->get('entry_service_type');
            $this->data['entry_total_weight'] = $this->language->get('entry_total_weight');
            $this->data['entry_no_of_pieces'] = $this->language->get('entry_no_of_pieces');
            $this->data['text_prepaid'] = $this->language->get('text_prepaid');
            $this->data['text_collect'] = $this->language->get('text_collect');
            $this->data['text_third_party'] = $this->language->get('text_third_party');
            $this->data['text_domestic'] = $this->language->get('text_domestic');
            $this->data['text_inter_express'] = $this->language->get('text_inter_express');
            $this->data['entry_service_type'] = $this->language->get('entry_service_type');
            $this->data['text_kg'] = $this->language->get('text_kg');
            $this->data['text_lb'] = $this->language->get('text_lb');
            $this->data['entry_no_of_pieces'] = $this->language->get('entry_no_of_pieces');
            $this->data['text_calc_rates'] = $this->language->get('text_calc_rates');
			
			$this->data['heading_title'] = $this->language->get('heading_title_rate');
			
			
			$this->data['breadcrumbs'] = array();

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', '', 'SSL'),
				'separator' => false
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title_rate'),
				'href'      => $this->url->link('sale/order', '', 'SSL'),
				'separator' => ' :: '
			);

			$this->data['order_id'] = $this->request->get['order_id'];

	############ button ########## 
	$this->data['back_to_order'] = $this->url->link('sale/order/info', 'order_id=' . $order_id , 'SSL');
	$this->data['aramex_create_sipment'] = $this->url->link('sale/aramex_create_shipment', 'order_id=' . $order_id , 'SSL');
	$this->data['aramex_rate_calculator'] = $this->url->link('sale/aramex_rate_calculator', 'order_id=' . $order_id , 'SSL');
	$this->data['aramex_schedule_pickup'] = $this->url->link('sale/aramex_schedule_pickup', 'order_id=' . $order_id , 'SSL');
	$this->data['aramex_print_label'] = $this->url->link('sale/aramex_create_shipment/lable', 'order_id=' . $order_id , 'SSL');
	$this->data['aramex_traking'] = $this->url->link('sale/aramex_traking', 'order_id=' . $order_id , 'SSL');
	############ button ##########
						
	##################### config shipper details ################
	$this->data['origin_country'] = ($this->config->get('aramex_shipper_country_code'))?$this->config->get('aramex_shipper_country_code'):'';
	$this->data['origin_city'] = ($this->config->get('aramex_shipper_city'))?$this->config->get('aramex_shipper_city'):'';
	$this->data['origin_zipcode'] = ($this->config->get('aramex_shipper_postal_code'))?$this->config->get('aramex_shipper_postal_code'):'';
	$this->data['origin_state'] = ($this->config->get('aramex_shipper_state'))?$this->config->get('aramex_shipper_state'):'';
			
	##################### customer shipment details ################
	
	$shipment_receiver_name ='';
	$shipment_receiver_street ='';
	

	$this->data['destination_country'] = ($order_info['shipping_iso_code_2'])?$order_info['shipping_iso_code_2']:'';
	$this->data['destination_city']    = ($order_info['shipping_city'])?$order_info['shipping_city']:'';
	$this->data['destination_zipcode'] = ($order_info['shipping_postcode'])?$order_info['shipping_postcode']:'';
	$this->data['destination_state']   = ($order_info['shipping_zone'])?$order_info['shipping_zone']:'';
	$this->data['destination_state_id']   = ($order_info['shipping_zone_id'])?$order_info['shipping_zone_id']:'';
	
	
	##################  Additional ###########
	
	$this->load->model('localisation/country');
    $this->data['countries'] = $this->model_localisation_country->getCountries();
	$this->data['reference'] = $order_id;
	
	$this->data['aramex_shipment_shipper_account'] = ($this->config->get('aramex_account_number'))?$this->config->get('aramex_account_number'):'';
	
	
	$this->data['aramex_allowed_domestic_methods'] = ($this->config->get('aramex_allowed_domestic_methods'))?$this->config->get('aramex_allowed_domestic_methods'):'';	
    $this->data['aramex_allowed_domestic_additional_services'] = ($this->config->get('aramex_allowed_domestic_additional_services'))?$this->config->get('aramex_allowed_domestic_additional_services'):'';			
	$this->data['aramex_allowed_international_methods'] = ($this->config->get('aramex_allowed_international_methods'))?$this->config->get('aramex_allowed_international_methods'):'';					
	$this->data['aramex_allowed_international_additional_services'] = ($this->config->get('aramex_allowed_international_additional_services'))?$this->config->get('aramex_allowed_international_additional_services'):'';							
	
    	
	$this->data['all_allowed_domestic_methods'] = $this->model_shipping_aramex->domesticmethods();
	$this->data['all_allowed_domestic_additional_services'] = $this->model_shipping_aramex->domesticAdditionalServices();		
	$this->data['all_allowed_international_methods'] = $this->model_shipping_aramex->internationalmethods();
	$this->data['all_allowed_international_additional_services'] = $this->model_shipping_aramex->internationalAdditionalServices();	
	
	
	if(isset($this->request->post['payment_type']) && !empty($this->request->post['payment_type'])) {
			$this->data['pay_type'] = $this->request->post['payment_type'];
	}else{
			$this->data['pay_type'] = '';
	}
	if(isset($this->request->post['product_group']) && !empty($this->request->post['product_group'])) {
			$this->data['group'] = $this->request->post['product_group'];
	}else{
			$this->data['group'] = "";
	}
	if(isset($this->request->post['service_type']) && !empty($this->request->post['service_type'])) {
			$this->data['type'] = $this->request->post['service_type'];
	}else{
			$this->data['type'] = "";
	}
	/*
	if(isset($this->request->post['aramex_shipment_info_service_type']) && !empty($this->request->post['aramex_shipment_info_service_type'])) {
			$this->data['stype'] = $this->request->post['aramex_shipment_info_service_type'];
	}else{
			$this->data['stype'] = "";
	}
	*/
	if(isset($this->request->post['weight_unit']) && !empty($this->request->post['weight_unit'])) {
			$getunit_classid = $this->model_sale_aramex->getWeightClassId($this->request->post['weight_unit']);
			$this->data['weight_unit'] = $getunit_classid->row['unit'];
			$config_weight_class_id = $getunit_classid->row['weight_class_id'];
	}else{
			$this->data['weight_unit'] = $this->weight->getUnit($this->config->get('config_weight_class_id'));
			$config_weight_class_id = $this->config->get('config_weight_class_id');
	}
	##################       
			
		$this->data['total'] = ($order_info['total'])?number_format($order_info['total'],2):'';

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
		$i = 1;
		foreach ($order_products as $order_product) {
			if (isset($order_product['order_option'])) {
				$order_option = $order_product['order_option'];
			} elseif (isset($this->request->get['order_id'])) {
				$order_option = $this->model_sale_order->getOrderOptions($this->request->get['order_id'], $order_product['order_product_id']);
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
			$prodweight = ($prodweight * $order_product['quantity']); 
			$weighttot = ($weighttot + $prodweight);
			$i = $i + 1;
		}
		$this->data['no_of_item'] = $i;
		$this->data['weighttot'] = number_format($weighttot,2);
		$this->data['total'] = number_format($order_info['total'],2);

		
		
		################## create shipment ###########
		if ($this->request->post) { 


				$account=($this->config->get('aramex_account_number'))?$this->config->get('aramex_account_number'):'';	
				$country_code=($this->config->get('aramex_account_country_code'))?$this->config->get('aramex_account_country_code'):'';		        $response=array();
				
				$clientInfo = $this->model_sale_aramex->getClientInfo();	
		try {
				
		// Get State/Zone name in English 
		$this->load->model('localisation/zone');
		$zone_name = $this->model_localisation_zone->getZoneInLanguage($this->request->post['destination_state_id'],1);
		
		$text_weight = ($this->request->post['text_weight'])?$this->request->post['text_weight']:'';
		$weight_unit = ($this->request->post['weight_unit'])?$this->request->post['weight_unit']:'';
		$params = array(
		'ClientInfo'  			=> $clientInfo,
								
		'Transaction' 			=> array(
									'Reference1'			=> ($this->request->post['reference'])?$this->request->post['reference']:'', 
								),
								
		'OriginAddress' 	 	=> array(  
									'StateOrProvinceCode'	=> ($this->request->post['origin_state'])?html_entity_decode($this->request->post['origin_state']):'',
									'City'					=> ($this->request->post['origin_city'])?html_entity_decode($this->request->post['origin_city']):'',
									'PostCode'				=> ($this->request->post['origin_zipcode'])?$this->request->post['origin_zipcode']:'', 
									'CountryCode'			=> ($this->request->post['origin_country'])?$this->request->post['origin_country']:''
								),
								
		'DestinationAddress' 	=> array(  
									'StateOrProvinceCode'	=> ($zone_name['name'])?html_entity_decode($zone_name['name']):$this->request->post['destination_state'],
									'City'					=> ($this->request->post['destination_city'])?html_entity_decode($this->request->post['destination_city']):'',
									'PostCode'				=> ($this->request->post['destination_zipcode'])?$this->request->post['destination_zipcode']:'', 
									'CountryCode'			=> ($this->request->post['destination_country'])?$this->request->post['destination_country']:''

								),
		'ShipmentDetails'		=> array(  
									'PaymentType'			 => ($this->request->post['payment_type'])?$this->request->post['payment_type']:'',
									'ProductGroup'			 => ($this->request->post['product_group'])?$this->request->post['product_group']:'',
									'ProductType'			 => ($this->request->post['service_type'])?$this->request->post['service_type']:'',
									'ActualWeight' 			 => array('Value' => $text_weight, 'Unit' => $weight_unit),
									'ChargeableWeight' 	     => array('Value' => $text_weight, 'Unit' => $weight_unit),
									'NumberOfPieces'		 => ($this->request->post['total_count'])?$this->request->post['total_count']:'',
								)
	);


	$baseUrl = $this->model_sale_aramex->getWsdlPath();
	$soapClient = new SoapClient($baseUrl.'/aramex-rates-calculator-wsdl.wsdl', array('trace' => 1));
	
	try{
	$results = $soapClient->CalculateRate($params);	

	if($results->HasErrors){
		if(count($results->Notifications->Notification) > 1){
			$error="";
			foreach($results->Notifications->Notification as $notify_error){
				$this->data['eRRORS'][] = 'Aramex: ' . $notify_error->Code .' - '. $notify_error->Message."<br>";				
			}
		}else{
			$this->data['eRRORS'][] = 'Aramex: ' . $results->Notifications->Notification->Code . ' - '. $results->Notifications->Notification->Message;
		}
		
	}else{

		$amount="<p class='amount'>".$results->TotalAmount->Value." ".$results->TotalAmount->CurrencyCode."</p>";
		$text="Local taxes - if any - are not included. Rate is based on account number $account in ".$country_code;
		$this->session->data['rate_html']=$amount.$text;	
		$this->redirect($this->url->link('sale/aramex_rate_calculator', 'order_id=' . $order_id , 'SSL'));
		
	}
	} catch (Exception $e) {
			$response['type']='error';
			$response['error']=$e->getMessage();			
	}
	}
	catch (Exception $e) {
			$response['type']='error';
			$response['error']=$e->getMessage();			
	}
				
	}
		
		
		
		################## create shipment end ###########
		
		
		$this->data['is_shipment'] = $this->model_sale_aramex->checkAWB($this->request->get['order_id']);
		//echo '<pre>';
		//print_r($this->data['order_products']);
		if (isset($this->session->data['rate_html'])) {
			$this->data['rate_html'] = $this->session->data['rate_html'];

			unset($this->session->data['rate_html']);
		} else {
			$this->data['rate_html'] = '';
		}
		
			$this->template = 'sale/aramex_rate_calculator.expand';
			$this->children = array(
				'common/header',
				'common/footer'
			);

			$this->response->setOutput($this->render());
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
	
	
}
?>