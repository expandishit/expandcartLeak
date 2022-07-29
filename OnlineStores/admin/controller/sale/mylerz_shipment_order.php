
<?php

class ControllerSalemylerzShipmentOrder extends Controller {

	private $error = [];

 	public function __construct($registry){
    parent::__construct($registry);

    if (!$this->user->hasPermission('modify', 'shipping/mylerz')) {
      $this->response->redirect($this->url->link('error/permission', '', 'SSL'));
    }

    if( !\Extension::isInstalled('mylerz') || !$this->config->get('mylerz_status') ){
      $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
    }
  }

	/**
	* Display the creation form to fill in by the user.
	*/
	public function create(){
 		$this->load->language('shipping/mylerz');
    $this->document->addStyle('view/stylesheet/anytime_custome_theme.css');

		$this->load->model('sale/order');
		$order = $this->model_sale_order->getOrder($this->request->get['order_id']);

		//Check if store order has shipping order already
		if( !empty($order['tracking']) ){
			$this->session->data['error'] = $this->language->get('text_order_already_exist');
			$this->response->redirect($this->url->link('sale/order/info?order_id='. $this->request->get['order_id'] , '' , true));
		}

    $this->data['order']            = $order;
    $this->data['shipper_title']    = $this->config->get('config_title')[$this->config->get('config_admin_language')];
    $this->data['cancel']           = $this->url->link('sale/order/info?order_id='.$this->request->get['order_id']);

    //Get Countries in Enabled GeoZones in Mylerz Settings ONLY...
    $geozones_ids = [];
    array_walk($this->config->get('mylerz_price'), function($val, $key) use(&$geozones_ids){
        if(str_ends_with($key, '_status') && $val == '1'){
            $geozones_ids[] = rtrim(ltrim($key, 'mylerz_geo_zone_id_'), '_status');
        }
    });

    $this->load->model('localisation/country');
    $this->data['countries'] = $this->model_localisation_country->getCountriesInGeoZone($geozones_ids);

		/*prepare mylerz/create.expand view data*/
	  $this->document->setTitle($this->language->get('create_heading_title'));
 		//Breadcrumbs
	  $this->data['breadcrumbs'] = $this->_createBreadcrumbs();
		$this->template = 'shipping/mylerz/shipment/create.expand';
	  $this->children = ['common/header', 'common/footer'];
    $this->response->setOutput($this->render_ecwig());
	}


	/**
	* Store/save the order data in the shipping gateway DB via external APIs.
	*/
	public function store(){
		if( $this->_isAjax() && $this->request->server['REQUEST_METHOD'] == 'POST' ) {

        //Validate form fields
  			if ( !empty($this->_validateForm()) ){
  				$result_json['success'] = '0';
  				$result_json['errors']  = $this->error;
  			}
  			else{
          $this->load->language('shipping/mylerz');
          $this->load->model('shipping/mylerz');
          $this->load->model('sale/order');

  				$order_id = $this->request->post['order_id'];
  				$order    = $this->model_sale_order->getOrder($order_id);

          $account_currency_code = $this->config->get('mylerz_account_currency');
          $total    = $this->_convertAmountToAccountCurrency($order['total'], $order['currency_code'], $account_currency_code);
          $weight   = $this->model_sale_order->getOrderTotalWeight($order_id) ?: 1;

          $data = [
              [
              'WarehouseName'     => $this->request->post['warehouse_name'],//optional
              'PickupDueDate'     => $this->request->post['pickup_due_date'],
              'Package_Serial'    => $order_id.time(),
              'Reference'         => $order_id,//optional
              'Description'       => $this->request->post['shipment_description'],
              'Total_Weight'      => $weight,//optional
              'Service_Type'      => $this->request->post['service_type'],
              'Service'           => $this->request->post['service_name'],
              'Service_Category'  => $this->request->post['service_category'],
              'Payment_Type'      => $this->request->post['payment_type'],
              'COD_Value'         => strtolower($this->request->post['payment_type']) == 'cod' ? $total : $this->request->post['cod_amount'],
              'Customer_Name'     => $this->request->post['customer_name'],
              'Mobile_No'         => $this->request->post['customer_mobile_no'],
              'Street'            => $this->request->post['customer_street'],
              'Country'           => $this->request->post['country'],
              'Neighborhood'      => $this->request->post['neighborhood'],
              'City'              => $this->request->post['city'],
              'Address_Category'  => $this->request->post['address_category'],//optional
              'currency'          => $account_currency_code,//optional
              'Pieces'            => [
                  [
                    'PieceNo'           => 1,
                    'Weight'            => $weight,
                    'ItemCategory'      => $this->request->post['item_category'],
                    'Special_Notes'      => $this->request->post['special_notes']
                  ],
              ]
            ]
          ];

          $response = $this->model_shipping_mylerz->createShipment($data);

          if( in_array($response['status_code'], [401, 300, 400]) ){
            $result_json['success'] = '0';
            $result_json['errors']  = 'ERROR: ' . $response['result']['Message'];
          }
          else if( $response['status_code'] == 200 && !($response['result']['IsErrorState']) ){  //succeeded
          	//update status & add history record
	    			if( !empty($this->config->get('mylerz_after_creation_status')) ){
				        $this->model_sale_order->addOrderHistory($order_id, [
				          'comment'          => 'mylerz - PickupOrderCode: ' . $response['result']['Value']['Packages'][0]['BarCode'],
				          'order_status_id'  => $this->config->get('mylerz_after_creation_status'),
				        ]);
  				   }

  	        //Update Tracking Number & Tracking URL
  	        $this->model_sale_order->updateShippingTrackingURL($order_id , 'https://mylerz.net/trackShipment/' . $response['result']['Value']['Packages']['BarCode']);
  	        $this->model_sale_order->updateShippingTrackingNumber($order_id , $response['result']['Value']['Packages'][0]['BarCode']);

      			//Returning to Order page
      			$result_json['success_msg'] = $this->language->get('text_shipment_created');
      			$result_json['success']     = '1';

            //redirect
  					$result_json['redirect'] = '1';
            $result_json['to'] = "sale/order/info?order_id=".$order_id;
          }
	    		else{
	    		      $result_json['success'] = '0';
					  $result_json['errors']  = 'ERROR: ' . $response['result']['Value']['ErrorCode'] . ' - ' . $response['result']['Value']['ErrorMessage'];
                    if (isset($response['error']) && !empty($response['error']))
                        $result_json['errors']  = 'ERROR: ' . $response['error_description'];

                }
    		}

  			$this->response->setOutput(json_encode($result_json));
  		}
  		else{
  			$this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
  		}
	}


  /**
  * Store/save the order data in the shipping gateway DB via external APIs.
  */
  public function storeMultiple(){
    if( $this->_isAjax() && $this->request->server['REQUEST_METHOD'] == 'POST' ) {

        //Validate Bulk request
        if ( !empty($this->_validateBeforeBulkRequest()) ){
          $result_json['success'] = '0';
          $result_json['errors']  = $this->error;
        }
        else{
          //Load required files
          $this->load->language('shipping/mylerz');
          $this->load->model('shipping/mylerz');
          $this->load->model('sale/order');

          //Get orders to be shipped
          $orders = $this->model_shipping_mylerz->getOrdersToPush($this->request->post['mylerz_orders']);

          if(count($orders) <= 0){
            $result_json['success'] = '0';
            $result_json['errors']  = 'ERROR: ' . $this->language->get('text_order_already_exist');
            $this->response->setOutput(json_encode($result_json));

            return;
          }

          $account_currency_code = $this->config->get('mylerz_account_currency');
          $data = [];

          foreach ($orders as $key => $order) {
              $order_id = $order['order_id'];
              $total    = $this->_convertAmountToAccountCurrency($order['total'], $order['currency_code'], $account_currency_code);
              $payment_type = $order['payment_code'] == 'cod' ? 'COD' : ($order['payment_code'] == 'ccod' ? 'CC' : 'PP');

              $data[] =
                  [
                  'PickupDueDate'     => $this->request->post['mylerz_pickup_due_date'],
                  'Package_Serial'    => $order_id.$key,
                  'Reference'         => $order_id,
                  'Description'       => 'Bulk Shipment Order',
                  'Service_Type'      => $this->request->post['mylerz_service_type'],
                  'Service'           => $this->request->post['mylerz_service_name'],
                  'Service_Category'  => $this->request->post['mylerz_service_category'],
                  'Payment_Type'      => $payment_type,
                  'COD_Value'         => strtolower($order['payment_code']) == 'cod' ? $total : 0,
                  'Customer_Name'     => $order['firstname'] . ' ' . $order['lastname'],
                  'Mobile_No'         => $order['telephone'],
                  'Street'            => $order['shipping_address'],
                  'Country'           => $order['shipping_country'],
                  'Neighborhood'      => $order['shipping_area_code'],
                  'City'              => $order['shipping_zone_code'],
                  'currency'          => $account_currency_code,
                  'Pieces'            => [
                      [
                        'PieceNo'           => 1,
                        'Special_Notes'      => ''
                      ],
                  ]
              ];
          }

          $response = $this->model_shipping_mylerz->createShipment($data);

          if( in_array($response['status_code'], [401, 300, 400]) ){
            $result_json['success'] = '0';
            $result_json['errors']  = 'ERROR: ' . $response['result']['Message'];
          }
          else if( $response['result']['IsErrorState'] != '1' && $response['status_code'] == 200 && !empty($response['result']['Value']['PickupOrderCode']) ){  //succeeded
            foreach ($response['result']['Value']['Packages'] as $package){
                $order_id = $package['Reference'];

                //update status & add history record
                if( !empty($this->config->get('mylerz_after_creation_status')) ){
                    $this->model_sale_order->addOrderHistory($order_id, [
                      'comment'          => 'mylerz - packageNo: ' . $package['packageNo'] . ' & BarCode:' . $package['BarCode'] . ' & Status:' . $package['Status'],
                      'order_status_id'  => $this->config->get('mylerz_after_creation_status'),
                    ]);
                 }

                //Update Tracking Number & Tracking URL
                $this->model_sale_order->updateShippingTrackingURL($order_id , 'https://mylerz.net/trackShipment/' . $response['result']['Value']['PickupOrderCode']);
                $this->model_sale_order->updateShippingTrackingNumber($order_id , $response['result']['Value']['PickupOrderCode']);
            }


            //Returning to Order page
            $result_json['success_msg'] = $this->language->get('text_shipment_created') . ' - ' . count($orders);
            $result_json['success']     = '1';
          }
          else{
            $result_json['success'] = '0';
            $result_json['errors']  = 'ERROR: ' . $response['result']['ErrorDescription'] . ' => ' .$response['result']['Value']['ErrorCode'] . ' - ' . str_replace('Package_Serial', 'Order#', $response['result']['Value']['ErrorMessage']);
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
	        'href' => $this->url->link('shipping/mylerz', true)
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
		$this->load->language('shipping/mylerz');

		if (!$this->user->hasPermission('modify', 'shipping/mylerz')) {
		    $this->error['warning'] = $this->language->get('error_permission');
		}

    if ((utf8_strlen($this->request->post['shipment_description']) < 3) ) {
      $this->error['shipment_description'] = $this->language->get('error_shipment_description');
    }

    if( $this->request->post['pickup_due_date'] <= 0 ) {
        $this->error['pickup_due_date'] = $this->language->get('error_pickup_due_date');
    }

    if((utf8_strlen($this->request->post['customer_name']) < 3) ) {
        $this->error['customer_name'] = $this->language->get('error_customer_name');
    }


    if( !preg_match('/^\d{7,}$/', $this->request->post['customer_mobile_no']) ) {
        $this->error['customer_mobile_no'] = $this->language->get('error_customer_mobile_no');
    }

    if ((utf8_strlen($this->request->post['customer_street']) < 3) ) {
      $this->error['customer_street'] = $this->language->get('error_customer_street');
    }

    if ((utf8_strlen($this->request->post['country']) <= 0) ) {
      $this->error['country'] = $this->language->get('error_country');
    }

    if ((utf8_strlen($this->request->post['city']) <= 0) ) {
      $this->error['city'] = $this->language->get('error_city');
    }
    if ((utf8_strlen($this->request->post['neighborhood']) <= 0) ) {
      $this->error['neighborhood'] = $this->language->get('error_neighborhood');
    }
		if($this->error && !isset($this->error['error']) ){
		  $this->error['warning'] = $this->language->get('error_warning');
		}

    return $this->error;
	}


  private function _validateBeforeBulkRequest(){
    $this->load->language('shipping/mylerz');
    $this->load->model('shipping/mylerz');

    if( count($this->request->post['mylerz_orders']) <= 0 ){
      $this->error['no_orders_to_push'] = $this->language->get('error_no_orders_to_push');
    }

    $pickup_due_date = $this->request->post['mylerz_pickup_due_date'];
    if( empty($pickup_due_date) || DateTime::createFromFormat('Y-m-d h:iA', $pickup_due_date) == FALSE ){
      $this->error['pickup_due_date'] = $this->language->get('error_pickup_due_date_missing');
    }

    if( empty($this->request->post['mylerz_service_type']) ||
      empty($this->request->post['mylerz_service_name']) ||
      empty($this->request->post['mylerz_service_category'])){
      $this->error['service'] = $this->language->get('error_some_service_properties_missing');
    }

    if($this->error && !isset($this->error['error']) ){
      $this->error['warning'] = $this->language->get('error_warning');
    }
    return $this->error;
  }

  private function _convertAmountToAccountCurrency($total, $currency_code, $account_currency_code){

    if( !$total || !$currency_code ) return 0;

    $currency_code = strtoupper($currency_code);

    if( $currency_code === $account_currency_code ){
      return round($total, 2);
    }
    elseif ( $currency_code !== 'USD' ) {
      $currenty_rate     = $this->currency->gatUSDRate($currency_code);
      $amount_in_dollars = $currenty_rate * $total;

      $target_currency_rate = $this->currency->gatUSDRate($account_currency_code);
      $final_amount         = $amount_in_dollars / $target_currency_rate;
      return round($final_amount, 2);
    }
    //If USD convert it directly to Account Currency
    else{
      $target_currency_rate = $this->currency->gatUSDRate($account_currency_code);
      $final_amount         = $total / $target_currency_rate;
      return round($final_amount, 2);
    }
  }

  private function _isAjax() {

    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
  }

  public function getNeighbourhoods(){
      if( empty($this->request->post['city_id']) ) {
        $this->response->setOutput(json_encode([]));
        return;
      }

      $this->load->model('localisation/area');
      $neighborhoods = $this->model_localisation_area->getAreasByZonesId([ $this->request->post['city_id'] ], $this->config->get('config_language_id'));
      $this->response->setOutput(json_encode($neighborhoods));
  }

  public function getCities(){
      //If Country id is missing return empty array...
      if( empty($this->request->post['country_id']) ) {
        $this->response->setOutput(json_encode([]));
        return;
      }

      $this->load->model('localisation/zone');
      $cities = $this->model_localisation_zone->getZonesByCountryId($this->request->post['country_id']);
      $this->response->setOutput(json_encode($cities));
  }
}
