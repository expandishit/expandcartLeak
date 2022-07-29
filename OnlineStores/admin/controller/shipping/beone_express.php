<?php

class ControllerShippingBeoneExpress extends Controller
{
	private $error = array(); 
	
	public function index()
    {
		$this->language->load('shipping/beone_express');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
		$this->load->model('localisation/geo_zone');
		$this->load->model('shipping/beone_express');
        $this->load->model('localisation/country');

        $this->data['data'] = $this->model_shipping_beone_express->getSettings();
        $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
        $this->data['countries'] = $this->model_localisation_country->getCountries();

        foreach ( $this->data['geo_zones'] as $geo_zone )
        {

            $this->data['beone_express_weight_' . $geo_zone['geo_zone_id'] . '_rate'] = $this->config->get('beone_express_weight_' . $geo_zone['geo_zone_id'] . '_rate');

            $this->data['beone_express_weight_' . $geo_zone['geo_zone_id'] . '_status'] = $this->config->get('beone_express_weight_' . $geo_zone['geo_zone_id'] . '_status');
        }

		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
		{

			if ( ! $this->validate() )
			{
				$result_json['success'] = '0';
				$result_json['errors'] = $this->error;
				
				$this->response->setOutput(json_encode($result_json));
				
				return;
			}
            $beone_express = $this->request->post['beone_express'];

			$this->model_setting_setting->checkIfExtensionIsExists('shipping', 'beone_express', true);

            
            $this->tracking->updateGuideValue('SHIPPING');

            $this->model_shipping_beone_express->updateSettings($beone_express);
					
			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
						
			$result_json['success'] = '1';
			
			$this->response->setOutput(json_encode($result_json));
			
			return;
		}

		// ======================== breadcrumbs =======================

  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_shipping'),
			'href'      => $this->url->link('extension/shipping', '', 'SSL'),
      		'separator' => ' :: '
   		);
		
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('shipping/beone_express', '', 'SSL'),
      		'separator' => ' :: '
   		);

        $this->data['links'] = [
            'submit' => $this->url->link('shipping/beone_express', '', 'SSL'),
            'cacnel' => $this->url->link('extension/shipping', '', 'SSL'),
        ];
        $this->data['action'] = $this->url->link('shipping/beone_express', '', 'SSL');

        $this->data['cancel'] = $this->url->link('shipping/beone_express', '', 'SSL');

        // ======================== /breadcrumbs =======================



		$this->template = 'shipping/beone_express/settings.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
 		$this->response->setOutput($this->render_ecwig());
	}

    public function createShipment()
    {

        // load Language File
        $this->language->load('shipping/beone_express');

        // set Page Title
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('sale/order');
        $this->load->model('setting/setting');
        $this->load->model('shipping/beone_express');


        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_shipping'),
            'href'      => $this->url->link('extension/shipping', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('shipping/beone_express', '', 'SSL'),
            'separator' => ' :: '
        );



        if ($this->request->server['REQUEST_METHOD'] == 'POST') {

            if ($this->validate_shipment()) {

                $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'beone_express', true);

                // get settings
                $config = $this->model_shipping_beone_express->getSettings();

                $orderData = $this->model_sale_order->getOrder($this->request->post['order_id']);

                $postData = $this->request->post['beone_express'];

                $curl_url = "https://api.beone-sa.com/API/orderCreate";

                // preparing curl data
                $curl_data = array(
                    "account_no" => $config['account'],
                    "secret_key" => $config['secret_key'],
                    "payment_mode" => $postData['payment_method'],
                    "cod_amount" => $postData['order_total'],
                    "reference_no" => $postData['order_id'],
                    "second_reference_no" => $postData['order_id'],
                    "origin_city" => $config['city'],
                    "destination_city" => $postData['city'],
                    "weight" => $postData['package_wight'],
                    "service" => "3",
                    "pieces" => $postData['pieces'],
                    "productType" => "parcel",
                    "sender_name" => $config['name'],
                    "sender_address" => $config['address'],
                    "sender_phone" => $config['phone'],
                    "sender_email" => $config['email'],
                    "receiver_name" => $postData['name'],
                    "receiver_address" => $postData['address'],
                    "receiver_phone" => $postData['phone'],
                    "receiver_email" => $postData['email'],
                    "description" => $postData['notes']
                );

                // send curl request
                $result = $this->sendCurlRequest($curl_url, json_encode($curl_data));

                if ($result['response']->errorCode == 0) {
                    $resultData['orderData'] = $curl_data;
                    $resultData['response'] = [
                        "label_url"=>$result['response']->pdf_label_url,
                        "awb_no"=>$result['response']->awb_no
                    ];

                    $this->model_shipping_beone_express->addShipmentDetails($postData['order_id'], $resultData, "pre-pair");

                    $this->response->setOutput(json_encode([
                        'success' => 1,
                        'success_msg' => $this->language->get('success_shipment_created_successfully'),
                        'redirect' => 1,
                        'to' => $this->url->link('shipping/beone_express/shipmentDetails', 'order_id=' . $postData['order_id'], 'SSL')->format()
                    ]));
                } else {
                    $response_error_message = array_values((array) $result['response']);
                    $result_json['success'] = '0';
                    $result_json['errors'] = [$response_error_message[1]];
                    $result_json['errors']['warning'] = $this->language->get('entry_error_warning');
                    $this->response->setOutput(json_encode($result_json));
                }
            } else {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                $this->response->setOutput(json_encode($result_json));
                return;
            }

        } else {
            // get order id
            $orderId = $this->data['order_id'] = $this->request->get['order_id'];

            $this->load->model("shipping/beone_express");

            // get shipping details for check if customer make shipment before
            $shipping_details = $this->model_shipping_beone_express->getShipmentDetails($orderId);

            if(count($shipping_details) > 0)
            {
                $this->redirect($this->url->link('shipping/beone_express/shipmentDetails', 'order_id=' . $orderId, 'SSL'));
            }

            $this->load->model('localisation/country');

            $this->data['countries'] = $this->model_localisation_country->getCountries();


            $this->data['orderData'] = $this->model_sale_order->getOrder($this->request->get['order_id']);
            // get order products for calculate order weight
            $order_products = $this->model_sale_order->getOrderProducts($this->request->get['order_id']);
            // define total wight
            $weighttot = 0;

            $config_weight_class_id = $this->config->get('config_weight_class_id');

            foreach ($order_products as $order_product) {
                if (isset($order_product['order_option'])) {
                    $order_option = $order_product['order_option'];
                } elseif (isset($this->request->get['order_id'])) {
                    $order_option = $this->model_sale_order->getOrderOptions($this->request->get['order_id'], $order_product['order_product_id']);
                    // get option value for add product option weight to product actual weight
                    if(count($order_option) > 0) {
                        $product_option_value = $this->model_sale_order->getProductOptionValue((int)$order_option[0]['product_option_value_id']);
                    }

                    $product_weight_query = $this->model_shipping_beone_express->getProductWeight($order_product['product_id']);
                } else {
                    $order_option = array();
                }
                $prodweight = $this->weight->convert($product_weight_query['weight'], $product_weight_query['weight_class_id'], $config_weight_class_id);
                // check if product has option
                if(isset($product_option_value) && is_array($product_option_value) && count($product_option_value) > 0)
                {
                    $prodOptionWeight = $this->weight->convert($product_option_value[0]['weight'], $product_weight_query['weight_class_id'], $config_weight_class_id);
                    if($product_option_value[0]['weight_prefix'] == '+'){
                        $prodweight = ($prodweight + $prodOptionWeight);
                    }else{
                        $prodweight = ($prodweight - $prodOptionWeight);
                    }
                }
                $prodweight = ($prodweight * $order_product['quantity']);

                $weighttot = ($weighttot + $prodweight);
            }

            // total weight
            $this->data['weighttot'] = number_format($weighttot,2);


            $this->template = 'shipping/beone_express/create_shipment.expand';
            $this->children = array(
                'common/footer',
                'common/header'
            );
            $this->response->setOutput($this->render_ecwig());
            return;
        }
    }

    public function shipmentDetails()
    {
        // load Language File
        $this->language->load('shipping/beone_express');

        // set Page Title
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('shipping/beone_express');


        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_shipping'),
            'href'      => $this->url->link('extension/shipping', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('shipping/beone_express', '', 'SSL'),
            'separator' => ' :: '
        );

        // get order id
        $orderId = $this->request->get['order_id'];

        $this->load->model("shipping/beone_express");
        // get order data
        $orderData = $this->model_shipping_beone_express->getShipmentDetails($orderId);

        $this->data['shipment_details'] = json_decode($orderData['details'],true, 512, JSON_UNESCAPED_UNICODE);


        $this->data['order_id'] = $orderId;


        $this->template = 'shipping/beone_express/details.expand';
        $this->children = array(
            'common/footer',
            'common/header'
        );
        $this->response->setOutput($this->render_ecwig());
        return;
    }

    function trackShipment()
    {
        // load Language File
        $this->language->load('shipping/beone_express');
        // set Page Title
        $this->document->setTitle($this->language->get('heading_title'));


        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_shipping'),
            'href'      => $this->url->link('extension/shipping', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('shipping/beone_express', '', 'SSL'),
            'separator' => ' :: '
        );


        // get order id
        $orderId = $this->data['order_id']  = $this->request->get['order_id'];


        $this->load->model("shipping/beone_express");

        // get shipment details
        $orderData = $this->model_shipping_beone_express->getShipmentDetails($orderId);

        $shipment_details = json_decode($orderData['details'],true, 512, JSON_UNESCAPED_UNICODE);

        // print label url
        $this->data['label_url'] = $shipment_details['response']['label_url'];

        // get settings
        $config = $this->model_shipping_beone_express->getSettings();

        $curl_url = "https://api.beone-sa.com/TrackOrderAPI/orderTrackAPI";

        // preparing curl data
        $curl_data = array(
            "account_no" => $config['account'],
            "secret_key" => $config['secret_key'],
            "orderNo" => $shipment_details['response']['awb_no']
        );

        // send curl request
        $result = $this->sendCurlRequest($curl_url, json_encode($curl_data));


        if($result['response']->errorCode == 0){

            $this->data['trackingDetails'] = (array)$result['response']->data;
        }else{
            $result_json['success'] = '0';
            $result_json['errors'] = [$result['response']->Error_Message];
            $result_json['errors']['warning'] = $this->language->get('error_warning');
            $this->response->setOutput(json_encode($result_json));
        }


        $this->template = 'shipping/beone_express/track.expand';
        $this->children = array(
            'common/footer',
            'common/header'
        );
        $this->response->setOutput($this->render_ecwig());
        return;

    }

    function cancelShipment()
    {
        // get order id
        $orderId = $this->request->get['order_id'];

        $this->load->model("shipping/beone_express");

        // load Language File
        $this->language->load('shipping/beone_express');

        $orderData = $this->model_shipping_beone_express->getShipmentDetails($orderId);

        $shipment_details = json_decode($orderData['details'],true, 512, JSON_UNESCAPED_UNICODE);

        $curl_url = "https://api.beone-sa.com/CancelBookedAPI/cancelBookedOrder";
        // get settings

        $config = $this->model_shipping_beone_express->getSettings();

        // preparing curl data
        $curl_data = array(
            "account_no" => $config['account'],
            "secret_key" => $config['secret_key'],
            "orderNo" => $shipment_details['response']['awb_no']
        );

        // send curl request
        $result = $this->sendCurlRequest($curl_url, json_encode($curl_data));

        if($result['response']->errorCode == 0){
            $this->model_shipping_beone_express->deleteShipment($orderId);
            $this->response->setOutput(json_encode([
                'success' => 1,
                'success_msg' => $result['response']->Success,
                'redirect'=>1,
                'to'=>(string)$this->url->link('sale/order/info', 'order_id=' . $orderId, 'SSL')->format()
            ]));
        }else{
            $this->response->setOutput(json_encode([
                'success' => 0,
                'success_msg' => $result['response']->Error_Message,
                'redirect'=> 0,
            ]));
        }
        return;
    }

    private function sendCurlRequest($_url, $data)
    {

        $curl = curl_init($_url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $options = array(
            CURLOPT_RETURNTRANSFER => true,   // return web page
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_FOLLOWLOCATION => true,   // follow redirects
            CURLOPT_MAXREDIRS => 10,     // stop after 10 redirects
            CURLOPT_ENCODING => "",     // handle compressed
            CURLOPT_AUTOREFERER => true,   // set referrer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
            CURLOPT_TIMEOUT => 120,    // time-out on response
            CURLOPT_CUSTOMREQUEST => 'POST',

        );
        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);
        $response = json_decode($response);
        curl_close($curl);
        $result = ['response' => $response];
        return $result;
    }
	
	protected function validate()
    {
        $postData = $this->request->post['beone_express'];

		if ( ! $this->user->hasPermission('modify', 'shipping/beone_express') )
		{
			$this->error['error'] = $this->language->get('error_permission');
		}

		if (!$postData['account']) {
			$this->error['beone_express_account'] = $this->language->get('error_account');
		}

		if (!$postData['secret_key']) {
			$this->error['beone_express_secret_key'] = $this->language->get('error_secret_key');
		}

        if (!$postData['name']) {
            $this->error['beone_express_name'] = $this->language->get('error_name');
        }

        if (!$postData['phone']) {
            $this->error['beone_express_phone'] = $this->language->get('error_phone');
        }

        if (!$postData['email']) {
            $this->error['beone_express_email'] = $this->language->get('error_email');
        }

        if (!$postData['address']) {
            $this->error['beone_express_address'] = $this->language->get('error_address');
        }

        if (!$postData['city']) {
            $this->error['beone_express_city'] = $this->language->get('error_city');
        }

        if ( ! $postData['beone_express_weight_rate_class_id'] || empty($postData['beone_express_weight_rate_class_id']) )
        {
            $this->error['beone_express_weight_rate_class_id'] = $this->language->get('error_entry_weight_rate_class_id_required');
        }


		if ( $this->error && !isset($this->error['error']) )
		{
		    $this->error['warning'] = $this->language->get('error_warning');
		}
		
		return $this->error ? false : true;
	}

    protected function validate_shipment()
    {
        $postData = $this->request->post['beone_express'];

        if ( ! $this->user->hasPermission('modify', 'shipping/beone_express') )
        {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if (!$postData['name']) {
            $this->error['name'] = $this->language->get('error_name');
        }

        if (!$postData['phone']) {
            $this->error['phone'] = $this->language->get('error_phone');
        }

        if (!$postData['city']) {
            $this->error['city'] = $this->language->get('error_city');
        }

        if (!$postData['address']) {
            $this->error['address'] = $this->language->get('error_address');
        }

        if (!$postData['order_total']) {
            $this->error['order_total'] = $this->language->get('error_order_total');
        }

        if (!$postData['payment_method']) {
            $this->error['payment_method'] = $this->language->get('error_payment_method');
        }

        if (!$postData['pieces']) {
            $this->error['pieces'] = $this->language->get('error_pieces');
        }

        if (!$postData['package_wight']) {
            $this->error['package_wight'] = $this->language->get('error_package_wight');
        }

        if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return $this->error ? false : true;
    }


}
