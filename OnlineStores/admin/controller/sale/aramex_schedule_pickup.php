<?php
class ControllerSaleAramexSchedulePickup extends Controller {
	
	private $error = array();

	public function index() {

		$this->language->load('sale/aramex');
		$this->document->setTitle($this->language->get('heading_title_shedule'));
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
			
			
			$this->document->setTitle($this->language->get('heading_title_shedule'));
			
			############### label #############
			$this->data['text_back_to_order'] = $this->language->get('text_back_to_order');
			$this->data['text_create_sipment'] = $this->language->get('text_create_sipment');
			$this->data['text_rate_calculator'] = $this->language->get('text_rate_calculator');
			$this->data['text_schedule_pickup'] = $this->language->get('text_schedule_pickup');
			$this->data['text_print_label'] = $this->language->get('text_print_label');
			$this->data['text_track'] = $this->language->get('text_track');

            $this->data['text_return_shipment'] = $this->language->get('text_return_shipment');
            $this->data['text_pickup_details'] = $this->language->get('text_pickup_details');
            $this->data['entry_location'] = $this->language->get('entry_location');
            $this->data['entry_vehicle_type'] = $this->language->get('entry_vehicle_type');
            $this->data['text_small_vehicle'] = $this->language->get('text_small_vehicle');
            $this->data['text_medim_vehicle'] = $this->language->get('text_medim_vehicle');
            $this->data['text_large_vehicle'] = $this->language->get('text_large_vehicle');
            $this->data['entry_date'] = $this->language->get('entry_date');
            $this->data['entry_ready_time'] = $this->language->get('entry_ready_time');
            $this->data['entry_closing_time'] = $this->language->get('entry_closing_time');
            $this->data['entry_shipment_dest'] = $this->language->get('entry_shipment_dest');
            $this->data['entry_company'] = $this->language->get('entry_company');
            $this->data['entry_contact'] = $this->language->get('entry_contact');
            $this->data['entry_phone'] = $this->language->get('entry_phone');
            $this->data['entry_mobile'] = $this->language->get('entry_mobile');
            $this->data['entry_address'] = $this->language->get('entry_address');
            $this->data['entry_country'] = $this->language->get('entry_country');
            $this->data['entry_state'] = $this->language->get('entry_state');
            $this->data['entry_city'] = $this->language->get('entry_city');
            $this->data['entry_postal_code'] = $this->language->get('entry_postal_code');
            $this->data['entry_email'] = $this->language->get('entry_email');
            $this->data['entry_comment'] = $this->language->get('entry_comment');

            $this->data['entry_reference1'] = $this->language->get('entry_reference1');
            $this->data['entry_status'] = $this->language->get('entry_status');
            $this->data['entry_ready'] = $this->language->get('entry_ready');
            $this->data['entry_pending'] = $this->language->get('entry_pending');
            $this->data['entry_product_group'] = $this->language->get('entry_product_group');
            $this->data['text_inter_express'] = $this->language->get('text_inter_express');
            $this->data['text_domestic'] = $this->language->get('text_domestic');
            $this->data['entry_product_type'] = $this->language->get('entry_product_type');
            $this->data['entry_payment_type'] = $this->language->get('entry_payment_type');
            $this->data['text_prepaid'] = $this->language->get('text_prepaid');
            $this->data['text_collect'] = $this->language->get('text_collect');
            $this->data['text_third_party'] = $this->language->get('text_third_party');
            $this->data['entry_total_weight'] = $this->language->get('entry_total_weight');
            $this->data['text_kg'] = $this->language->get('text_kg');
            $this->data['text_lb'] = $this->language->get('text_lb');
            $this->data['entry_no_of_pieces'] = $this->language->get('entry_no_of_pieces');
            $this->data['entry_no_of_shipments'] = $this->language->get('entry_no_of_shipments');

            $this->data['error_pickup_date'] = $this->language->get('error_pickup_date');
            $this->data['error_ready_time'] = $this->language->get('error_ready_time');
            $this->data['error_ready_time_pick'] = $this->language->get('error_ready_time_pick');
            $this->data['error_ready_time_close'] = $this->language->get('error_ready_time_close');
            $this->data['text_submit'] = $this->language->get('text_submit');
			
			$this->data['heading_title'] = $this->language->get('heading_title_shedule');
			
			
			$this->data['breadcrumbs'] = array();

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', '', 'SSL'),
				'separator' => false
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title_shedule'),
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
	if(isset($this->request->post['contact']) && !empty($this->request->post['contact'])) {
			$this->data['contact'] = $this->request->post['contact'];
	}else{
			$this->data['contact'] = ($this->config->get('aramex_shipper_name'))?$this->config->get('aramex_shipper_name'):'';
	}
	
	if(isset($this->request->post['company']) && !empty($this->request->post['company'])) {
			$this->data['company'] = $this->request->post['company'];
	}else{
			$this->data['company'] = ($this->config->get('aramex_shipper_company'))?$this->config->get('aramex_shipper_company'):'';
	}
	
	if(isset($this->request->post['phone']) && !empty($this->request->post['phone'])) {
			$this->data['phone'] = $this->request->post['phone'];
	}else{
			$this->data['phone']   = ($this->config->get('aramex_shipper_phone'))?$this->config->get('aramex_shipper_phone'):'';
	}
	
	if(isset($this->request->post['address']) && !empty($this->request->post['address'])) {
			$this->data['address'] = $this->request->post['address'];
	}else{
			$this->data['address'] = ($this->config->get('aramex_shipper_address'))?$this->config->get('aramex_shipper_address'):'';
	}
	
	if(isset($this->request->post['country']) && !empty($this->request->post['country'])) {
			$this->data['country'] = $this->request->post['country'];
	}else{
			$this->data['country'] = ($this->config->get('aramex_shipper_country_code'))?$this->config->get('aramex_shipper_country_code'):'';
	}
	
	if(isset($this->request->post['city']) && !empty($this->request->post['city'])) {
			$this->data['city'] = $this->request->post['city'];
	}else{
			$this->data['city']    = ($this->config->get('aramex_shipper_city'))?$this->config->get('aramex_shipper_city'):'';
	}
	
	if(isset($this->request->post['zip']) && !empty($this->request->post['zip'])) {
			$this->data['zip'] = $this->request->post['zip'];
	}else{
			$this->data['zip']     = ($this->config->get('aramex_shipper_postal_code'))?$this->config->get('aramex_shipper_postal_code'):'';
	}
	
	if(isset($this->request->post['state']) && !empty($this->request->post['state'])) {
			$this->data['state'] = $this->request->post['state'];
	}else{
			$this->data['state']   = ($this->config->get('aramex_shipper_state'))?$this->config->get('aramex_shipper_state'):'';
	}
	
	if(isset($this->request->post['email']) && !empty($this->request->post['email'])) {
			$this->data['email'] = $this->request->post['email'];
	}else{
			$this->data['email']   = ($this->config->get('aramex_shipper_email'))?$this->config->get('aramex_shipper_email'):'';
	}
	
	if(isset($this->request->post['date']) && !empty($this->request->post['date'])) {
			$this->data['date'] = $this->request->post['date'];
	}else{
			$this->data['date']   = date('Y-m-d');
	}
		##################### customer shipment details ################
	
	$shipment_receiver_name ='';
	$shipment_receiver_street ='';
	

	$this->data['destination_country'] = ($order_info['shipping_iso_code_2'])?$order_info['shipping_iso_code_2']:'';
	$this->data['destination_city']    = ($order_info['shipping_city'])?$order_info['shipping_city']:'';
	$this->data['destination_zipcode']  = ($order_info['shipping_postcode'])?$order_info['shipping_postcode']:'';
	$this->data['destination_state']   = ($order_info['shipping_zone'])?$order_info['shipping_zone']:'';
	
		
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
	
	
	
	if(isset($this->request->post['product_group']) && !empty($this->request->post['product_group'])) {
			$this->data['group'] = $this->request->post['product_group'];
	}else{
			$this->data['group'] = "";
	}
	if(isset($this->request->post['product_type']) && !empty($this->request->post['product_type'])) {
			$this->data['type'] = $this->request->post['product_type'];
	}else{
			$this->data['type'] = "";
	}
	if(isset($this->request->post['payment_type']) && !empty($this->request->post['payment_type'])) {
			$this->data['pay_type'] = $this->request->post['payment_type'];
	}else{
			$this->data['pay_type'] = '';
	}
	if(isset($this->request->post['comments']) && !empty($this->request->post['comments'])) {
			$this->data['comments'] = $this->request->post['comments'];
	}else{
			$this->data['comments'] = '';
	}
	if(isset($this->request->post['mobile']) && !empty($this->request->post['mobile'])) {
			$this->data['mobile'] = $this->request->post['mobile'];
	}else{
			$this->data['mobile'] = '';
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
	if ($this->request->post) 
	{ 


			$account=($this->config->get('aramex_account_number'))?$this->config->get('aramex_account_number'):'';	
			$country_code=($this->config->get('aramex_account_country_code'))?$this->config->get('aramex_account_country_code'):'';		        $response=array();
				
			$clientInfo = $this->model_sale_aramex->getClientInfo();	
		try {
				
		
		$date = ($this->request->post['date'])?$this->request->post['date']:date('Y-m-d');
		//$pickupDate=strtotime($date);

        date_default_timezone_set('UTC');
        $date = ($this->request->post['date'])?$this->request->post['date']:date('Y-m-d');
        //$pickupDate=strtotime($date);
        $systemTimezone = new DateTimeZone($this->config->get('config_timezone'));
        $utfTimezone = new DateTimeZone('UTC');

        $readyTime = new DateTime($date." ".$this->request->post['ready_time'], $systemTimezone);
        $readyTime = $readyTime->setTimezone($utfTimezone)->getTimestamp();
        $closingTime = new DateTime($date." ".$this->request->post['close_time'], $systemTimezone);
        $closingTime = $closingTime->setTimezone($utfTimezone)->getTimestamp();
        // $x = strtotime($readyTime->format('Y-m-d H:i'));
        // dd([$readyTime, date('c', $readyTime)]);
        // $x = strtotime(gmdate('c', strtotime($readyTime->format('c'))));
        // $x = date('c', $x);
        // dd([$x, $closingTime->format('c')]);
        // dd([date('c', $readyTime), date('c', $closingTime)]);
		
		// Use GMT time zone 
		// $readyTime = strtotime(gmdate('Y-m-d H:i:s',strtotime($date." ".$this->request->post['ready_time'])));
	
		//var_dump($this->request->post['ready_time']);die();	
		//$readyTimeH=($this->request->post['ready_hour'])?$this->request->post['ready_hour']:'';
		//$readyTimeM=($this->request->post['ready_minute'])?$this->request->post['ready_minute']:'';		
		//$readyTime=mktime(($readyTimeH-2),$readyTimeM,0,date("m",$pickupDate),date("d",$pickupDate),date("Y",$pickupDate));	
		//$readyTime=gmmktime(($readyTimeH),$readyTimeM,0,date("m",$pickupDate),date("d",$pickupDate),date("Y",$pickupDate));
		//	$readyTime = date('Y-m-d H:i:s',strtotime($date." ".$this->request->post['ready_time'])); 
		
		//$ready_time_parts = explode(":",$this->request->post['ready_time']);
		
		//$readyTime=mktime(($ready_time_parts[0]-2),$ready_time_parts[1],0,date("m",$pickupDate),date("d",$pickupDate),date("Y",$pickupDate));
		// $readyTime=gmmktime(($ready_time_parts[0]),$ready_time_parts[1],0,date("m",$pickupDate),date("d",$pickupDate),date("Y",$pickupDate));
		
		//$closingTimeH=($this->request->post['latest_hour'])?$this->request->post['latest_hour']:'';
		//$closingTimeM=($this->request->post['latest_minute'])?$this->request->post['latest_minute']:'';
		//$closingTime=mktime(($closingTimeH-2),$closingTimeM,0,date("m",$pickupDate),date("d",$pickupDate),date("Y",$pickupDate));
		//$closingTime=gmmktime(($closingTimeH),$closingTimeM,0,date("m",$pickupDate),date("d",$pickupDate),date("Y",$pickupDate));
		//$close_time_parts = explode(":",$this->request->post['close_time']);
		
		// Use GMT time zone
		
		$closingTime = strtotime(gmdate('Y-m-d H:i:s',strtotime($date." ".$this->request->post['close_time'])));
		
		//$closingTime=mktime(($close_time_parts[0]-2),$close_time_parts[1],0,date("m",$pickupDate),date("d",$pickupDate),date("Y",$pickupDate));

		//$closingTime =  date('Y-m-d H:i:s',strtotime($date." ".$this->request->post['close_time']));
		$text_weight = ($this->request->post['text_weight'])?$this->request->post['text_weight']:'';
		$weight_unit = ($this->request->post['weight_unit'])?$this->request->post['weight_unit']:'';
	
		$params = array(
		'ClientInfo'  	=> $clientInfo,
								
		'Transaction' 	=> array(
								'Reference1'			=> ($this->request->post['reference'])?$this->request->post['reference']:'',
								),
								
		'Pickup'		=>array(
								'PickupContact'				=>array(
									'PersonName'			=>($this->request->post['contact'])?html_entity_decode($this->request->post['contact']):'',
									'CompanyName'			=>($this->request->post['company'])?html_entity_decode($this->request->post['company']):'',
									'PhoneNumber1'			=>($this->request->post['phone'])?html_entity_decode($this->request->post['phone']):'',
									'PhoneNumber1Ext'		=>($this->request->post['ext'])?html_entity_decode($this->request->post['ext']):'',
									'CellPhone'				=>($this->request->post['mobile'])?html_entity_decode($this->request->post['mobile']):'',
									'EmailAddress'			=>($this->request->post['email'])?html_entity_decode($this->request->post['email']):''
								),
								'PickupAddress'				=>array(
									'Line1'					=>($this->request->post['address'])?html_entity_decode($this->request->post['address']):'',
									'City'					=>($this->request->post['city'])?html_entity_decode($this->request->post['city']):'',
									'StateOrProvinceCode'	=>($this->request->post['state'])?html_entity_decode($this->request->post['state']):'',
									'PostCode'				=>($this->request->post['zip'])?html_entity_decode($this->request->post['zip']):'',
									'CountryCode'			=>($this->request->post['country'])?$this->request->post['country']:'',
								),
								
								'PickupLocation'		=>($this->request->post['location'])?html_entity_decode($this->request->post['location']):'',
								'PickupDate'			=>date('Y-m-d',strtotime($date)),
								'ReadyTime'				=>$readyTime,
								'LastPickupTime'		=>$closingTime,
								'ClosingTime'			=>$closingTime,
								'Comments'				=>($this->request->post['comments'])?html_entity_decode($this->request->post['comments']):'',
								'Reference1'			=>($this->request->post['reference'])?$this->request->post['reference']:'',
								'Reference2'			=>'',
								'Vehicle'				=>($this->request->post['vehicle'])?$this->request->post['vehicle']:'',
								'Shipments'				=>array(
									'Shipment'					=>array()
								),
								'PickupItems'			=>array(
									'PickupItemDetail'=>array(
										'ProductGroup'	=>($this->request->post['product_group'])?$this->request->post['product_group']:'',
										'ProductType'	=>($this->request->post['product_type'])?$this->request->post['product_type']:'',
										'Payment'		=>($this->request->post['payment_type'])?$this->request->post['payment_type']:'',									
										'NumberOfShipments'=>($this->request->post['no_shipments'])?$this->request->post['no_shipments']:'',
										'NumberOfPieces'=>($this->request->post['total_count'])?$this->request->post['total_count']:'',									
										'ShipmentWeight'=>array('Value'=>$text_weight,'Unit'=>$weight_unit),
										
									),
								),
								'Status'				=>($this->request->post['status'])?$this->request->post['status']:'',
							)
	);
	
   
	
	$baseUrl = $this->model_sale_aramex->getWsdlPath();
	$soapClient = new SoapClient($baseUrl.'/shipping.wsdl', array('trace' => 1));

	try{
	$results = $soapClient->CreatePickup($params);		
	
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
		$notify = false;
        $visible = false;
		$comment="Pickup reference number ( <strong>".$results->ProcessedPickup->ID."</strong> ).";
		$message = array(
							'notify' => 0,
							'comment' => $comment
						);
						
		$this->model_sale_aramex->addOrderHistory($this->request->get['order_id'], $message);
		$shipmenthistory = "<p class='amount'>Pickup reference number ( <strong>".$results->ProcessedPickup->ID."</strong> ).</p>";
		$this->session->data['success_html'] = $shipmenthistory;
		$this->redirect($this->url->link('sale/aramex_schedule_pickup', 'order_id=' . $order_id , 'SSL'));

	}
	} catch (Exception $e) {
			
			$this->data['eRRORS'][] = $e->getMessage();			
	}
	}
	catch (Exception $e) {
			$this->data['eRRORS'][] = $e->getMessage();			
	}
				
	}
		
		
		
		################## create shipment end ###########
		
		$this->data['is_shipment'] = $this->model_sale_aramex->checkAWB($this->request->get['order_id']);
		$this->data['is_pickup_created'] = $this->model_sale_aramex->checkShedulePickup($this->request->get['order_id']);
		
		//echo '<pre>';
		//print_r($this->data['order_products']);
		if (isset($this->session->data['success_html'])) {
			$this->data['success_html'] = $this->session->data['success_html'];

			unset($this->session->data['success_html']);
		} else {
			$this->data['success_html'] = '';
		}
			$this->template = 'sale/aramex_schedule_pickup.expand';
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