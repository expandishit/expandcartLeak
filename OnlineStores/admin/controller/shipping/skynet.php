<?php

class ControllerShippingSkynet extends Controller{

    /**
  	* @var array the validation errors array.
  	*/
    private $error = [];

    public function index(){

        $this->_addViewData();

        $this->_renderView();
    }
    /**
    * Save form data and Enable Extension after data validation.
    *
    * @return void
    */
    public function save(){
      if( $this->_isAjax() && $this->request->server['REQUEST_METHOD'] == 'POST' ) {

          //Validate form fields
        if ( ! $this->_validateForm() ){
          $result_json['success'] = '0';
          $result_json['errors'] = $this->error;
        }
        else{
          $this->load->model('setting/setting');
          $this->load->language('shipping/skynet');
            //Insert a record of skynet gatway in the extension table in DB if not any
          $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'skynet', TRUE);
            //Save skynet config data in settings table
          $this->model_setting_setting->insertUpdateSetting('skynet', $this->request->post);//, 'skynet_cities' => $this->config->get('skynet_cities')]);

            
            $this->tracking->updateGuideValue('SHIPPING');

            $result_json['success']  = '1';
          $result_json['success_msg'] = $this->language->get('text_success');
        }

        $this->response->setOutput(json_encode($result_json));
      }
      else{
        $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
      }
    }



    private function _addViewData(){
        //Breadcrumbs
        $this->data['breadcrumbs'] = $this->_createBreadcrumbs();

        //Form Buttons
        //save button - Ajax post request
        $this->data['action'] = $this->url->link('shipping/skynet/save', '' , 'SSL');
        $this->data['cancel'] = $this->url->link('extension/shipping', 'type=shipping', true);

        /*Get form fields data*/
        $this->data['skynet_token']        = $this->config->get('skynet_token');
        $this->data['skynet_status']       = $this->config->get('skynet_status');
        $this->data['skynet_live_mode']    = $this->config->get('skynet_live_mode');
        $this->data['skynet_geo_zone_id']  = $this->config->get('skynet_geo_zone_id');
        $this->data['skynet_after_creation_status'] = $this->config->get('skynet_after_creation_status');

        $this->load->model('localisation/geo_zone');
        $this->data['geo_zones']  = $this->model_localisation_geo_zone->getGeoZones();

        $this->load->model('localisation/order_status');
        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
    }
    private function _renderView(){
        /*prepare skynet.expand view data*/
        $this->document->setTitle($this->language->get('heading_title'));
        $this->template = 'shipping/skynet.expand';
        $this->children = [ 'common/header', 'common/footer' ];
        $this->response->setOutput($this->render());
    }

    /**
  	* Form the breadcrumbs array.
  	*
  	* @return Array $breadcrumbs
  	*/
  	private function _createBreadcrumbs(){
        $this->load->language('shipping/skynet');

  		  return [
    			[
    				'text' => $this->language->get('text_home'),
    				'href' => $this->url->link('common/dashboard', '', 'SSL')
    			],
    			[
    				'text' => $this->language->get('text_extension'),
    				'href' => $this->url->link('extension/shipping', 'type=shipping', true)
    			],
    			[
    				'text' => $this->language->get('heading_title'),
    				'href' => $this->url->link('shipping/skynet', true)
    			]
  		  ];
  	}
    /**
    * Validate form fields.
    *
    * @return bool TRUE|FALSE
    */
    private function _validateForm(){

      $this->load->language('shipping/skynet');

      if (!$this->user->hasPermission('modify', 'shipping/skynet')) {
        $this->error['warning'] = $this->language->get('error_permission');
      }

      if ((utf8_strlen($this->request->post['skynet_token']) < 32) ) {
        $this->error['skynet_token'] = $this->language->get('error_skynet_token');
      }

      if($this->error && !isset($this->error['error']) ){
        $this->error['warning'] = $this->language->get('error_warning');
      }

      return !$this->error;
    }
    private function _isAjax() {

      return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }



    //Methods for creating shipping order
    public function create(){
    		 $order_id = $this->request->get['order_id'];

    		 $this->load->model('sale/order');
    		 $order = $this->model_sale_order->getOrder($order_id);

      		//Check if store order has shipping order already
      		if( !empty($order['tracking']) ){
      			$this->load->language('shipping/skynet');
      			$this->session->data['error'] = $this->language->get('text_order_already_exist');
      			$this->response->redirect($this->url->link('sale/order/info?order_id='.$order_id, '' , true));
      		}

    		  $this->data['order'] = $order;

    		  /*prepare skynet.expand view data*/
    	    $this->load->language('shipping/skynet');
    	    $this->document->setTitle($this->language->get('create_heading_title'));

     		  //Breadcrumbs
    	    $this->data['breadcrumbs'] = $this->_createBreadcrumbs();

    		  $this->template = 'shipping/skynet/shipment/create.expand';
    	    $this->children = [ 'common/header' , 'common/footer' ];

          $this->response->setOutput($this->render_ecwig());
    }

    public function store(){
    		if( $this->_isAjax() && $this->request->server['REQUEST_METHOD'] == 'POST' ) {

    			//Validate form fields
    			if ( empty($this->_validateShippingOrder()) ){
    				$result_json['success'] = '0';
    				$result_json['errors'] = $this->error;
    			}
    			else{
    				$order_id = $this->request->post['order_id'];
    				$this->load->model('sale/order');
    				
            $order    = $this->model_sale_order->getOrder($order_id);
            $products = $this->model_sale_order->getOrderProducts($order_id);

            $data = [
              [
                "ThirdPartyToken"=>"",
                "SenderDetails"=> [
                  "SenderName"=> $this->config->get('config_name')['en'],
                  "SenderCompanyName"=> $this->config->get('config_name')['en']?:"",
                  "SenderAdd1"=> $this->config->get('config_address')['en']?:"",
                  "SenderPhone"=> $this->config->get('config_telephone')?:"",
                  "SenderEmail"=> $this->config->get('config_email')?:""
                ],
                "ReceiverDetails"=> $this->request->post['ShippingOrder']['ReceiverDetails'],
                "PackageDetails"=> [
                  "WeightMeasurement"=> "KG",
                  "NoOfItems"=> count($products),
                  "ServiceTypeName"=> "EN",
                  "BookPickUP"=> false,
                  "AlternateRef"=> $order_id,
                  "CODAmount"=> $order['payment_method'] == 'COD' ? $order['total']:0.0,
                  "CODCurrencyCode"=> $order['currency_code']?:""
                ],
                "PickupDetails"=> [
                  "ReadyTime" => date('yy/m/d h:i:s', time())
                ]
              ]
            ];

    	    		$this->load->language('shipping/skynet');
    	    		$this->load->model('shipping/skynet');

    	    		$response = $this->model_shipping_skynet->createShipment($data)[0];

    	    		if(!empty($response['ShipmentNumber']) && empty($response['ErrMessage'])){//succeeded
    	    			//update status & add history record
    	    			if( !empty($this->config->get('skynet_after_creation_status')) ){
    				        $this->model_sale_order->addOrderHistory($order['order_id'], [
    				          'comment'          => 'skynet - accountcode: ' . $response['AcccountCode'],
    				          'order_status_id'  => $this->config->get('skynet_after_creation_status'),
    				        ]);
    					}

			        //Update Tracking Number & Tracking URL
			        $this->model_sale_order->updateShippingTrackingURL($order_id , 'https://www.skynetworldwide.net/ShipmentTrackSingle.aspx?textfield='.$response['ShipmentNumber']);
			        $this->model_sale_order->updateShippingTrackingNumber($order_id , $response['ShipmentNumber']);

  	    			//Returning to Order page
  	    			$result_json['success_msg'] = $this->language->get('text_creation_success');
  					  $result_json['success']  = '1';
    					//redirect
    					$result_json['redirect'] = '1';
    			    	$result_json['to'] = "sale/order/info?order_id=".$order_id;
    	    		}
    	    		else{
    	    			$result_json['success'] = '0';
                $result_json['errors']  = $response['ErrMessage'];
    	    		}
    			}
    			$this->response->setOutput(json_encode($result_json));
    		}
    		else{
    			$this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
    		}
    }
    private function _validateShippingOrder(){
      		$this->load->language('shipping/skynet');

      		if(utf8_strlen($this->request->post['receiver']['firstName']) < 3
      			|| utf8_strlen($this->request->post['receiver']['lastName']) < 3){
              	$this->error['firstName'] = $this->language->get('error_first_last_name');
      		}
      		if( !isset($this->request->post['receiver']['phone']) || !preg_match("/^01[0-5]{1}[0-9]{8}$/", $this->request->post['receiver']['phone']) ){
      		    $this->error['phone'] = $this->language->get('error_phone');
      		}
      		if( utf8_strlen($this->request->post['dropOffAddress']['firstLine']) < 5){
      		    $this->error['firstLine'] = $this->language->get('error_first_line');
      		}
      		if( $this->request->post['isSameDay'] == 1 && count(array_filter($this->request->post['dropOffAddress']['geoLocation']) ) < 2){
      		    $this->error['geoLocation'] = $this->language->get('error_geolocation');
      		}

      		if($this->error && !isset($this->error['error']) ){
      	      $this->error['warning'] = $this->language->get('errors_heading');
      	    }

      		return $this->error;
    }
}
