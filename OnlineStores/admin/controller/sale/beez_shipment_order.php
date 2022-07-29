
<?php

class ControllerSaleBeezShipmentOrder extends Controller {

	private $error = [];

 	public function __construct($registry){
	    parent::__construct($registry);

	    if (!$this->user->hasPermission('modify', 'shipping/beez')) {
	      $this->response->redirect($this->url->link('error/permission', '', 'SSL'));
	    }

	    if( !\Extension::isInstalled('beez') || !$this->config->get('beez')['status'] ){
	      $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
	    }
	}

	/**
	* Display the creation form to fill in by the user.
	*/
	public function create(){
 		$this->load->language('shipping/beez');
    	$this->document->addStyle('view/stylesheet/anytime_custome_theme.css');

		$this->load->model('sale/order');
		$order = $this->model_sale_order->getOrder($this->request->get['order_id']);

		//Check if store order has shipping order already
		if( !empty($order['tracking']) ){
			$this->session->data['error'] = $this->language->get('text_order_already_exist');
			$this->response->redirect($this->url->link('sale/order/info?order_id='. $this->request->get['order_id'] , '' , true));
		}
		
		$this->data['products']  = array_column( $this->model_sale_order->getOrderProducts($this->request->get['order_id']) ,
			'name', 
			'product_id');

      	$this->data['shipper_location']  = $this->config->get('config_location');
	    $this->data['order']             = $order;
	    $this->data['cancel']            = $this->url->link('sale/order/info?order_id='.$this->request->get['order_id']);

	    //Get Countries in Enabled GeoZones in Beez Settings ONLY...
	    $geozones_ids = [];
	    array_walk($this->config->get('beez')['price'], function($val, $key) use(&$geozones_ids){
	        if(str_ends_with($key, '_status') && $val == '1'){
	            $geozones_ids[] = rtrim(ltrim($key, 'beez_geo_zone_id_'), '_status');
	        }
	    });

	    $this->load->model('localisation/country');
	    $this->data['countries'] = $this->model_localisation_country->getCountriesInGeoZone($geozones_ids);

		/*prepare beez/create.expand view data*/
		$this->document->setTitle($this->language->get('create_heading_title'));
	 	
	 	//Breadcrumbs
		$this->data['breadcrumbs'] = $this->_createBreadcrumbs();
		$this->template = 'shipping/beez/shipment/create.expand';
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
				$this->load->language('shipping/beez');
				$this->load->model('shipping/beez');
				$this->load->model('sale/order');

				$settings = $this->config->get('beez');
  				$order_id = $this->request->post['order_id'];
  				$beez_shipment = $this->request->post['beez_order'];
  				$order    = $this->model_sale_order->getOrder($order_id);
				$weight   = $this->model_sale_order->getOrderTotalWeight($order_id) ?: 1;

				$data = [				
					'LineItems'       => $this->_getItems($beez_shipment['products'], $order_id),
					'Edit'     		  => false,
					'Payment'         => false,
					'PaymentAmount'   => 0.00,
					'TrackingNumber'  => "",
					'AccountNumber'   => $settings['account_number'],
					'ApiKey'          => $settings['api_key'],
					'RequestedBy'     => "Branch #9",
					'OrderNumber'     => ($settings['debugging_mode'] ? 'test' : '') . $order_id,
					'Shipping'        => TRUE,
					'ShipmentType'    => $beez_shipment['shipping_type'],
					'CustomerNote'    => $beez_shipment['customer_note'],
					'Description'     => $beez_shipment['description'],
					'COD'             => number_format($beez_shipment['cod'], 2, '.', ''),
					// 'PickupLocation'  => $beez_shipment['pickup_location'],
					'dimensions'      => [
						'weight' => ceil($weight), //round up to integer
					],
					'BillingAddress'  => [$beez_shipment['billing_address']],
					'ShippingAddress' => [$beez_shipment['shipping_address']],
				];

				$response = $this->model_shipping_beez->createShipment($data);

          		if( in_array($response['status_code'], [400, 404 , 300]) ){
            		$result_json['success'] = '0';
            		$result_json['errors']  = 'ERROR: ' . $response['result']['Message']; //ModelState

          		}elseif ($response['status_code'] == 500) {
					$result_json['success'] = '0';
            		$result_json['errors']  = 'ERROR: Unknown server error. Please report it to Beez when occurred.' ;//ModelState

                }
          		else if( in_array($response['status_code'], [200, 201]) && !empty($response['result']) ){  //succeeded trackingnumber
          			$trackingnumber = (!is_array($response['result']) && is_string($response['result'])) ? $response['result'] : $response['result']['trackingnumber'];
          			
          			//update status & add history record
	    			if( !empty($this->config->get('beez_after_creation_status')) ){
				        $this->model_sale_order->addOrderHistory($order_id, [
				          'comment'          => 'Beez - TrackingNumber: ' . $trackingnumber,
				          'order_status_id'  => $this->config->get('beez_after_creation_status'),
				        ]);
  				    }

		  	       //Update Tracking Number & Tracking URL
		  	       $this->model_sale_order->updateShippingTrackingURL($order_id , 'https://beezdash.com/Track/Index?tr=' . $trackingnumber);
		  	       $this->model_sale_order->updateShippingTrackingNumber($order_id , $trackingnumber);

	      			//Returning to Order page
	      			$result_json['success_msg'] = $this->language->get('text_shipment_created');
	      			$result_json['success']     = '1';

            		//redirect
  					$result_json['redirect'] = '1';
            		$result_json['to'] = "sale/order/info?order_id=".$order_id;
          		}
	    		else{
	    		    $result_json['success'] = '0';
					$result_json['errors']  = 'ERROR: ' . var_export($response, true);
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
	        'href' => $this->url->link('shipping/beez', true)
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
		$this->load->language('shipping/beez');

		if (!$this->user->hasPermission('modify', 'shipping/beez')) {
		    $this->error['warning'] = $this->language->get('error_permission');
		}

		$beez_order = $this->request->post['beez_order'];

		if( !preg_match('/^(\+\d{1,5}|0)(\d{10})$/', $beez_order['billing_address']['CustomerPhone1']) ) {
        	$this->error['error_billing_customer_phone1'] = $this->language->get('error_billing_customer_phone1');
    	}
		if( !preg_match('/^(\+\d{1,5}|0)(\d{10})$/', $beez_order['billing_address']['CustomerPhone2']) ) {
        	$this->error['error_billing_customer_phone2'] = $this->language->get('error_billing_customer_phone2');
    	}

		if( !preg_match('/^(\+\d{1,5}|0)(\d{10})$/', $beez_order['shipping_address']['CustomerPhone1']) ) {
        	$this->error['error_shipping_customer_phone1'] = $this->language->get('error_shipping_customer_phone1');
    	}
		if( !preg_match('/^(\+\d{1,5}|0)(\d{10})$/', $beez_order['billing_address']['CustomerPhone2']) ) {
        	$this->error['error_shipping_customer_phone2'] = $this->language->get('error_shipping_customer_phone2');
    	}

	    if( empty($beez_order['cod']) && $beez_order['cod'] <= 0) {
	        $this->error['error_cod'] = $this->language->get('error_cod');
	    }

	    if( !preg_match('/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/', $beez_order['shipping_address']['Lat']) ||
	    	empty($beez_order['shipping_address']['Lat']) ) {
	        $this->error['error_lat'] = $this->language->get('error_lat');
	    }

	    if( !preg_match('/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/', $beez_order['shipping_address']['Lng']) ||
	    	empty($beez_order['shipping_address']['Lng']) ) {
	        $this->error['error_lng'] = $this->language->get('error_lng');
	    }

		if($this->error && !isset($this->error['error']) ){
		  $this->error['warning'] = $this->language->get('error_warning');
		}


    	return $this->error;
	}
 
	private function _isAjax() {

		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
	}

	private function _getItems($products_ids, int $order_id){
		$items = $this->db->query("SELECT 
			pd.name AS ProductName,
			pd.description AS Description,
			op.`quantity` AS Quantity,
			p.`sku` AS SKU,
			p.`sku` AS UPC,
			o.`gift_product` AS GiftWrapping

			FROM `order` o
			JOIN order_product op ON o.order_id = op.order_id
			JOIN product p ON p.product_id = op.product_id
			JOIN product_description pd ON pd.product_id = p.product_id

			WHERE o.order_id = $order_id AND op.product_id IN( " . implode(',', $products_ids)  . " ) AND pd.language_id = " . (int)$this->config->get('config_language_id') . ";")->rows;
		
		array_walk($items, function(&$val, $key){
			$val['Description']  = strip_tags($val['Description']);
			$val['GiftWrapping'] = (bool)$val['GiftWrapping'];
	    });

		return $items;
	}
}


