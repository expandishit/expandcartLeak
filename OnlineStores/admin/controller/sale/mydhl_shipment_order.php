<?php

class ControllerSaleMydhlShipmentOrder extends Controller {

	private $error = [];

 	public function __construct($registry){
	    parent::__construct($registry);

	    if (!$this->user->hasPermission('modify', 'shipping/mydhl')) {
	      $this->response->redirect($this->url->link('error/permission', '', 'SSL'));
	    }

	    if( !\Extension::isInstalled('mydhl') || !$this->config->get('mydhl')['status'] ){
	      $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
	    }
	}

	/**
	* Display the creation form to fill in by the user.
	*/
	public function create(){
 		$this->load->language('shipping/mydhl');
    	$this->document->addStyle('view/stylesheet/anytime_custome_theme.css');

    	$order_id = $this->request->get['order_id'];

		$this->load->model('sale/order');
		$order = $this->model_sale_order->getOrder($order_id);

		//Check if store order has shipping order already
		if( !empty($order['tracking']) ){
			$this->session->data['error'] = $this->language->get('text_order_already_exist');
			$this->response->redirect($this->url->link('sale/order/info?order_id='. $order_id , '' , true));
		}

		$this->data['packages'] = $this->getPackages($order_id);

		$order['total'] = number_format((float)($order['total'] * $order['currency_value']), 2, '.', '');
		$langcode = $this->config->get('config_admin_language');
      	$this->data['store_name']  		 = $this->config->get('config_name')[$langcode];
	    $this->data['order']             = $order;
	    $this->data['cancel']            = $this->url->link('sale/order/info?order_id='.$order_id);
	    $this->data['planned_date_time'] = (new DateTime('tomorrow',new DateTimeZone($this->config->get('config_timezone'))))->format('Y-m-d H:i');
	    $this->data['shipment_description'] = $this->getShipmentDescription($order_id);
	    $this->data['config_address'] = $this->config->get('config_address')[$langcode];

	    $this->load->model('localisation/country');
	    $this->data['countries'] = $this->model_localisation_country->getCountries();

	    $this->load->model('localisation/zone');
	    $this->data['zones']  = $this->model_localisation_zone->getZonesByCountryId($this->config->get('config_country_id'));

	    $settings = $this->config->get('mydhl');
	    if($settings['unit_of_measurement'] == 'metric'){
		    $this->data['length_unit'] = $langcode == 'ar' ? 'سم' : 'CM';
		    $this->data['weight_unit'] = $langcode == 'ar' ? 'كجم' : 'KG';		    	
	    }else{
	    	$this->data['length_unit'] = $langcode == 'ar' ? 'انش' : 'INCH';
	        $this->data['weight_unit'] = $langcode == 'ar' ? 'باوند' : 'LB';
	    }

		/*prepare mydhl/create.expand view data*/
		$this->document->setTitle($this->language->get('create_heading_title'));
	 	
	 	//Breadcrumbs
		$this->data['breadcrumbs'] = $this->_createBreadcrumbs();
		$this->template = 'shipping/mydhl/shipment/create.expand';
		$this->children = ['common/header', 'common/footer'];
	    $this->response->setOutput($this->render_ecwig());
	}


	/**
	* Store/save the order data in the shipping gateway DB via external APIs.
	*/
	public function store(){
		if( $this->_isAjax() && $this->request->server['REQUEST_METHOD'] == 'POST' ) {
			$this->load->language('shipping/mydhl');

        	//Validate form fields
  			if ( !empty($this->_validateForm()) ){
  				$result_json['success'] = '0';
  				$result_json['errors']  = $this->error;
  			}
  			else{
				$this->load->model('shipping/mydhl');
				$this->load->model('sale/order');

				$settings = $this->config->get('mydhl');
  				$order_id = $this->request->post['order_id'];
  				$mydhl_shipment = $this->request->post['mydhl_order'];
  				$order    = $this->model_sale_order->getOrder($order_id);
				$weight   = $this->model_sale_order->getOrderTotalWeight($order_id) ?: 1;

				//Get store country iso2 code.
    			$this->load->model('localisation/country');
				$storeCountryISO2Code = strtolower($this->model_localisation_country->getCountry($this->config->get('config_country_id'))['iso_code_2']);
				
				$request = $this->request->post['mydhl_order'];

				$data = [
					"plannedShippingDateAndTime"  => (new DateTime($request['planned_date_time']))->format('Y-m-d\TH:i:s'. "\G\M\T" .'P'),
					"pickup"   => [ "isRequested" => false ],							
					"productCode" => $this->getDHLProductCode($order),
					"accounts" => [
						[
							"number"   => $settings['account_number'],
							"typeCode" => "shipper"
						]
					],
					"customerDetails" => [
						"shipperDetails" =>  [
							"postalAddress" =>  [
								"cityName"     =>  $request['shipper']['city'],
								"countryCode"  =>  $request['shipper']['country_code'],
								"postalCode"   =>  $request['shipper']['postalcode'],
								"addressLine1" =>  $request['shipper']['addressline1']
							],
							"contactInformation" =>  [
								"phone"        =>  $request['shipper']['phone'],
								"companyName"  =>  $request['shipper']['company_name'],
								"fullName"     =>  $request['shipper']['fullname']
							]
						],
						"receiverDetails" =>  [
							"postalAddress" =>  [
								"cityName"     =>  $request['receiver']['city'],
								"countryCode"  =>  $request['receiver']['country_code'],
								"postalCode"   =>  $request['receiver']['postalcode'],
								"addressLine1" =>  $request['receiver']['addressline1'],
							],
							"contactInformation" =>  [
								"phone"        =>  $request['receiver']['phone'],
								"companyName"  =>  $request['receiver']['company_name'],
								"fullName"     =>  $request['receiver']['fullname']
							]
						]
					],
					"content" =>  [
						"unitOfMeasurement"     =>  $settings['unit_of_measurement'],
						"incoterm"              =>  $request['content']['incoterm'],
						"exportDeclaration"     =>  [
							"lineItems" =>  $this->getLineItems($order_id),
							"invoice"   =>  [
								"date"          =>  $request['invoice']['date'],
								"number"        =>  $request['invoice']['number'],
								"signatureName" =>  $request['invoice']['signature_name'],
								"signatureTitle"=>  $request['invoice']['signature_title']
							]
						],
						"isCustomsDeclarable"   =>  (boolean)$settings['is_customs_declarable'],
						"description"           =>  htmlspecialchars_decode($request['content']['description']),
						"packages"              =>  $this->packagesFloatVal($request['content']['packages'], $settings),
						"declaredValue"         =>  (float)$request['content']['declared_value'],
						"declaredValueCurrency" =>  $order['currency_code']
					]
				];

				$response = $this->model_shipping_mydhl->createShipment($data);
				
				if( $response['status_code'] == 201 && !empty($response['result']) ){  //succeeded trackingnumber
          			$trackingnumber = $response['result']['shipmentTrackingNumber'];
          			$shippingLabelUrl = $this->saveLabel($response['result']['documents'], $trackingnumber);

          			//update status & add history record
	    			if( !empty($settings['after_creation_status']) ){
				        $this->model_sale_order->addOrderHistory($order_id, [
				          'comment'          => 'Mydhl - TrackingNumber: ' . $trackingnumber,
				          'order_status_id'  => $settings['after_creation_status'],
				        ]);
  				    }

		  	       //Update Tracking Number & Tracking URL
		  	       $this->model_sale_order->updateShippingTrackingURL($order_id , 'https://mydhl.express.dhl/'. $storeCountryISO2Code .'/en/tracking.html#/results?id=' . $trackingnumber);//$response['result']['trackingUrl']
		  	       $this->model_sale_order->updateShippingTrackingNumber($order_id , $trackingnumber);
            	   $this->model_sale_order->updateShippingLabelURL($order_id , $shippingLabelUrl);

	      			//Returning to Order page
	      			$result_json['success_msg'] = $this->language->get('text_shipment_created');
	      			$result_json['success']     = '1';

            		//redirect
  					$result_json['redirect'] = '1';
            		$result_json['to'] = "sale/order/info?order_id=".$order_id;
          		}
	    		else{
	    		    $result_json['success'] = '0';
					$result_json['errors']  = 'ERROR: ' . ($response['result']['additionalDetails'] ? var_export($response['result']['additionalDetails'], true) : var_export($response['result']['detail'], true));
                }
    		}

  			$this->response->setOutput(json_encode($result_json));
  		}
  		else{
  			$this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
  		}
	}

	/** Helper methods **/

	/**
	* Form the breadcrumbs array.
	*
	* @return Array $breadcrumbs
	*/
	private function _createBreadcrumbs(){

	    $breadcrumbs = [
	      [
	        'text' => $this->language->get('text_home'),
	        'href' => $this->url->link('common/dashboard', '', 'SSL')
	      ],
	      [
	        'text' => $this->language->get('text_extension'),
	        'href' => $this->url->link('extension/shipping', 'type=shipping', true)
	      ],
	      [
	        'text' => $this->language->get('create_heading_title'),
	        'href' => $this->url->link('shipping/mydhl', true)
	      ]
	    ];
	    return $breadcrumbs;
	}


	/**
	* Validate form fields.
	*
	* @return bool TRUE|FALSE
	*/
	private function _validateForm(){
		if (!$this->user->hasPermission('modify', 'shipping/mydhl')) {
		    $this->error['warning'] = $this->language->get('error_permission');
		}

		$mydhl_order = $this->request->post['mydhl_order'];

		//Shipper
		if(empty($mydhl_order['planned_date_time']) || DateTime::createFromFormat('Y-m-d H:i', $mydhl_order['planned_date_time']) === false )
		    $this->error['planned_date_time'] = $this->language->get('error_invalid_planned_date');

		if($mydhl_order['content']['packages_count'] < 1)
        	$this->error['packages_count'] = $this->language->get('error_packages_count');

		if( utf8_strlen($mydhl_order['shipper']['fullname']) < 1 )
		    $this->error['shipper_fullname'] = $this->language->get('error_invalid_shipper_fullname');

		if( utf8_strlen($mydhl_order['shipper']['company_name']) < 1 )
		    $this->error['shipper_company_name'] = $this->language->get('error_invalid_shipper_company_name');

		if( utf8_strlen($mydhl_order['shipper']['phone']) < 3 )
		    $this->error['shipper_phone'] = $this->language->get('error_invalid_shipper_phone');

		if( utf8_strlen($mydhl_order['shipper']['addressline1']) < 1 )
		    $this->error['shipper_addressline1'] = $this->language->get('error_invalid_shipper_addressline1');

		// if( utf8_strlen($mydhl_order['shipper']['postalcode']) < 3 )
		//     $this->error['shipper_postalcode'] = $this->language->get('error_invalid_shipper_postalcode');


		//Receiver
		if( utf8_strlen($mydhl_order['receiver']['fullname']) < 1 )
		    $this->error['receiver_fullname'] = $this->language->get('error_invalid_receiver_fullname');

		if( utf8_strlen($mydhl_order['receiver']['company_name']) < 1 )
		    $this->error['receiver_company_name'] = $this->language->get('error_invalid_receiver_company_name');

		if( utf8_strlen($mydhl_order['receiver']['phone']) < 3 )
		    $this->error['receiver_phone'] = $this->language->get('error_invalid_receiver_phone');

		if( utf8_strlen($mydhl_order['receiver']['addressline1']) < 1 )
		    $this->error['receiver_addressline1'] = $this->language->get('error_invalid_receiver_addressline1');
		
		if( utf8_strlen($mydhl_order['content']['description']) < 1 )
		    $this->error['content_description'] = $this->language->get('error_invalid_content_description');

		if(empty($mydhl_order['invoice']['date']) || DateTime::createFromFormat('Y-m-d', $mydhl_order['invoice']['date']) === false )
		    $this->error['invoice_date'] = $this->language->get('error_invalid_invoice_date');

		// if( utf8_strlen($mydhl_order['shipper']['postalcode']) < 3 )
		//     $this->error['shipper_postalcode'] = $this->language->get('error_invalid_shipper_postalcode');

		foreach($mydhl_order['content']['packages'] as $key => $package){
			if($package['weight'] <= 0)
		    	$this->error['content_package_weight_'.$key] = sprintf($this->language->get('error_invalid_package_weight'), $key+1);
		    
		    if($package['dimensions']['width'] <= 0)
		    	$this->error['content_package_width_'.$key] = sprintf($this->language->get('error_invalid_package_width'), $key+1);

		    if($package['dimensions']['length'] <= 0)
		    	$this->error['content_package_length_'.$key] = sprintf($this->language->get('error_invalid_package_length'), $key+1);

		    if($package['dimensions']['height'] <= 0)
		    	$this->error['content_package_height_'.$key] = sprintf($this->language->get('error_invalid_package_height'), $key+1);
		}

		if($this->error && !isset($this->error['error']) ){
		  $this->error['warning'] = $this->language->get('error_warning');
		}


    	return $this->error;
	}
 
	private function _isAjax() 
	{

		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
	}

	public function getCities()
	{
	      //If Country id is missing return empty array...
	      if( empty($this->request->post['country_id']) ) {
	        $this->response->setOutput(json_encode([]));
	        return;
	      }

	      $this->load->model('localisation/zone');
	      $cities = $this->model_localisation_zone->getZonesByCountryId($this->request->post['country_id']);
	      $this->response->setOutput(json_encode($cities));
    }

    public function getShipmentDescription($order_id)
    {
    	return $this->db->query('SELECT group_concat(`name`) AS description
    		FROM category_description 
    		WHERE category_id = 
    		(
    			SELECT category_id 
    			FROM `product_to_category`
    			WHERE product_id IN (SELECT product_id FROM order_product WHERE order_id = ' .(int)$order_id . ') 
    			limit 1
    		) AND language_id = '. (int)$this->config->get('config_language_id'))->row['description'];
    }

    public function getLineItems($order_id)
    {
    	$settings = $this->config->get('mydhl');
    	$to_weight_class_id = $settings['unit_of_measurement'] == 'metric' ? $settings['kg_class_id'] : $settings['lb_class_id'];

    	$this->load->model('sale/order');
    	$products = $this->model_sale_order->getOrderProducts($order_id);
    	$lineItems = [];

    	$this->load->model('localisation/country');
	    $manufacturerCountryCode = $this->model_localisation_country->getCountryData($this->config->get('config_country_id'))['iso_code_2'];

    	foreach($products as $key => $product){
    		$lineItems[$key]['number'] = $key+1;
    		$lineItems[$key]['description'] = $product['name'];
    		$lineItems[$key]['price'] = (float)$product['price'];
    		$lineItems[$key]['quantity']['value'] = (int)$product['quantity'];
    		$lineItems[$key]['quantity']['unitOfMeasurement'] = 'BOX';
    		$lineItems[$key]['manufacturerCountry'] = $manufacturerCountryCode;
    		$lineItems[$key]['weight']['netValue'] = $lineItems[$key]['weight']['grossValue'] = (float)$this->weight->convert($product['weight'], $product['weight_class_id'], $to_weight_class_id);
    	}
    	
    	return $lineItems;
    }

    public function packagesFloatVal($packages, $settings)
    {
    	$packagesFloatVal = [];

    	foreach($packages as $key => $package){
    		$packagesFloatVal[$key]['weight'] = (float)$package['weight'] ?: 1;
    		$packagesFloatVal[$key]['dimensions']['width']  = (float)$package['dimensions']['width'];
    		$packagesFloatVal[$key]['dimensions']['length'] = (float)$package['dimensions']['length'];
    		$packagesFloatVal[$key]['dimensions']['height'] = (float)$package['dimensions']['height'];
    	}
    	return $packagesFloatVal;
    }

    public function saveLabel($documents, $trackingnumber)
    {
    	foreach($documents as $document){
    		if($document['typeCode'] == 'label'){
    			$fileName = '/data/'.$trackingnumber.'.'.$document['imageFormat'];
				if( file_put_contents(BASE_STORE_DIR.'image'.$fileName, base64_decode($document['content'])) )
					return HTTPS_CATALOG.'ecdata/stores/'.STORECODE.'/image'.$fileName;
    		}
    	}
    }

    public function getPackages($order_id)
    {
    	$packages = [];

    	$settings = $this->config->get('mydhl');
    	
    	if($settings['unit_of_measurement'] == 'metric'){
    		$to_weight_class_id = $settings['kg_class_id'];
    		$to_length_class_id = $settings['cm_class_id'];
    	}else{//imperial
    		$to_weight_class_id = $settings['lb_class_id'];
    		$to_length_class_id = $settings['in_class_id'];
    	}
    	
    	$this->load->model('sale/order');
    	$products = $this->model_sale_order->getOrderProducts($order_id);

    	foreach($products as $product){
			$packages[] = [
				'name'   => $product['name'],
				'weight' => number_format($this->weight->convert($product['weight'], $product['weight_class_id'], $to_weight_class_id), 2, '.',''),
				'width'  => number_format(((float)$this->length->convert($product['width'],  $product['length_class_id'], $to_length_class_id))?:$settings['packaging']['min_width'], 2, '.',''),
				'length' => number_format(((float)$this->length->convert($product['length'], $product['length_class_id'], $to_length_class_id))?:$settings['packaging']['min_length'], 2, '.',''),
				'height' => number_format(((float)$this->length->convert($product['height'], $product['length_class_id'], $to_length_class_id))?:$settings['packaging']['min_height'], 2, '.',''),
			];
    	}

    	return $packages;
    }

    private function getDHLProductCode($order)
    {
    	return substr($order['shipping_code'], strpos($order['shipping_code'], '.') + 1, strlen($order['shipping_code']));    	
    }
}
