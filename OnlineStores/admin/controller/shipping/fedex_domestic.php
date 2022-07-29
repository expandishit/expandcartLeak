<?php

class ControllerShippingFedexDomestic extends Controller
{
    protected $baseUrl = "http://82.129.197.84:8080/api/";
    protected $error;
    protected $fields = [
        'fedex_domestic_key', 'fedex_domestic_password', 'fedex_domestic_account', 'fedex_domestic_shipper_name', 'fedex_domestic_shipper_phone', 'fedex_domestic_shipper_address', 'fedex_domestic_shipper_city'
    ];
    
    public function index()
    {
        return $this->renderTemplate();
    }


    public function save()
    {
        $this->language->load('shipping/fedex');

        if ( ! $errors = $this->validate() )
        {
            $result_json['success'] = '0';
            $result_json['errors'] = $this->error;
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }

        $this->load->model('setting/setting');

        /**
         * inserting fedex_domestic to Extensions
         * option is Disabled
         */
//        $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'fedex_domestic', true);

        
            $this->tracking->updateGuideValue('SHIPPING');

        $this->model_setting_setting->editSetting('fedex_domestic', $this->request->post);

        $result_json = [
            "success_msg" => $this->language->get('text_success'),
            "success"     => 1
        ];

        $this->response->setOutput( json_encode( $result_json ) );

        return;
    }


    public function createShipmentView()
    {
        $orderId = $this->request->get['order_id'];

        if ( ! $orderId ) $this->redirect( $_SERVER['HTTP_REFERER'] );

        $this->load->model('sale/order');
        $this->load->model('catalog/product');
        $this->load->model('localisation/weight_class');

        $orderInfo = $this->model_sale_order->getOrder($orderId);
        $orderProducts = $this->model_sale_order->getOrderProducts($orderId);

        if ( array_key_exists('comment', $orderInfo) && !empty( $orderInfo['comment'] ) ) {
            $notes = $orderInfo['comment'];
        } else {
            $notes = "No Notes";
        }

        $no_of_pieces = $weight = 0;

        foreach( $orderProducts as $orderProduct ) {
            $no_of_pieces += $orderProduct['quantity'];

            $productInfo = $this->model_catalog_product->getProduct($orderProduct['product_id']);
            $weightClass = $this->model_localisation_weight_class->getWeightClass( $productInfo['weight_class_id'] );

            if ( $weightClass['unit'] == 'kg' ) {
                $weight += $productInfo['weight'];
            } else {
                $weight += $productInfo['weight'] * $weightClass['value'];
            }
        }

        if ( array_key_exists('shipping_address_2', $orderInfo) && ! empty( $orderInfo['shipping_address_2'] ) ) {
            $recipient_address2 = $orderInfo['shipping_address_2'];
        } else {
            $recipient_address2 = '';
        }

        if ( strtolower( $orderInfo['payment_code'] ) == 'cod' ) {
            $payment_method = "COD";
            $COD_amount = (float)$orderInfo['total'];
        } else {
            $COD_amount = 0;
            $payment_method = "prepaid";
        }

        $no_of_pieces = 0;

        foreach( $orderProducts as $orderProduct ) {
            $no_of_pieces += $orderProduct['quantity'];
        }

        $this->renderTemplate('shipping/fedex_domestic/create_shipment.expand', [
            'order'                             => $orderInfo,
            'fedex_domestic_notes'              => $notes,
            'fedex_domestic_weight'             => $weight,
            'fedex_domestic_recipient_name'     => $orderInfo['shipping_firstname'] . " " . $orderInfo['shipping_lastname'],
            'fedex_domestic_origin_country'     => "CHINA",
            'fedex_domestic_recipient_phone'    => $orderInfo['telephone'],
            'fedex_domestic_recipient_address1' => $orderInfo['shipping_address_1'],
            'fedex_domestic_recipient_address2' => $recipient_address2,
            'fedex_domestic_no_of_pieces'       => $no_of_pieces,
            'fedex_domestic_payment_method'     => $payment_method,
            'fedex_domestic_COD_amount'         => $COD_amount,
            'fedex_domestic_goods_description'  => "Awesome products sent with love",
            'all_cities'                        => $this->getAllCities()
        ]);
    }


    public function createShipment()
    {
        $this->language->load('shipping/fedex');

        $orderId = $this->request->get['order_id'];

        if ( ! $orderId ) return $this->fail( $this->language->get('error_invalid_order_id') );

        if ( ! $this->validateCreateShipmentForm() ) return $this->fail();

        $this->load->model('sale/order');

        $data = $this->makeShipmentData( $this->request->post );

        $result = $this->doRequest( $this->baseUrl . 'AWBcreate', $data );

        if ( $result->response_code == '200' ) {
            $result_json['success'] = '1';

            $result_json['success_msg'] = $this->language->get('success_shipment_created_successfully');

            $result_json['redirect'] = '1';
            $result_json['to'] = (string) $this->url->link("sale/order/info?order_id={$orderId}");

            $this->response->setOutput(json_encode($result_json));
        } else {
            return $this->fail( $result->response_message );
        }
    }


    protected function makeShipmentData( array $formData )
    {
        $data = [];

        $prefix = "fedex_domestic_";

        $data['accountNo'] = $this->config->get("{$prefix}account");
        $data['password'] = md5( $this->config->get("{$prefix}password") );
        $data['shipper_name'] = $this->config->get("{$prefix}shipper_name");
        $data['shipper_phone'] = $this->config->get("{$prefix}shipper_phone");
        $data['shipper_city'] = $this->config->get("{$prefix}shipper_city");
        $data['shipper_address1'] = $this->config->get("{$prefix}shipper_address");

        $data['recipient_name'] = $formData['fedex_domestic_recipient_name'];
        $data['recipient_phone'] = $formData['fedex_domestic_recipient_phone'];
        $data['recipient_city'] = $formData['fedex_domestic_recipient_city'];
        $data['recipient_address1'] = $formData['fedex_domestic_recipient_address1'];

        if ( ! empty( $formData['fedex_domestic_recipient_address2'] ) ) {
            $data['recipient_address2'] = $formData['fedex_domestic_recipient_address2'];
        }

        $data['payment_method'] = $formData['fedex_domestic_payment_method'];

        if ( strtolower( $data['payment_method'] ) == 'cod' ) {
            $data['COD_amount'] = (float) $formData['fedex_domestic_COD_amount'];
        }

        $data['no_of_pieces'] = (int) $formData['fedex_domestic_no_of_pieces'];
        $data['weight'] = (float) $formData['fedex_domestic_weight'];
        $data['dimensions'] = $formData['fedex_domestic_shipment_dimensions_x']."X".$formData['fedex_domestic_shipment_dimensions_y']."X".$formData['fedex_domestic_shipment_dimensions_z'];
        $data['goods_description'] = $formData['fedex_domestic_goods_description'];
        $data['goods_origin_country'] = $formData['fedex_domestic_origin_country'];
        
        $keyPart1 = $data['no_of_pieces'] . $data['weight'] . $data['dimensions'];
        $keyPart2 = strrev( md5( $this->config->get( "{$prefix}key" ) ) );

        $data['hashkey'] = trim( sha1( $keyPart1 . $keyPart2 ) );

        return $data;
    }


    protected function validate()
    {

        if ( ! $this->user->hasPermission('modify', 'shipping/fedex_domestic') )
		{
			$this->error['error'] = $this->language->get('error_permission');
        }
        
        foreach( $this->fields as $field ) {
            if (!$this->request->post[ $field ]) {
                $this->error[$field] = $this->language->get('error_required');
            }
        }

        if ( $this->request->post['fedex_domestic_shipper_city'] == '0' ) {
            $this->error["fedex_domestic_shipper_city"] = $this->language->get('error_required');
        }

        return $this->error ? false : true;
    }


    protected function doRequest( string $url, array $data = [] )
    {
        $ch = curl_init();
        
        $curlOptions = [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url
        ];

        if ( ! empty($data) ) {
            $curlOptions[CURLOPT_HTTPHEADER] = [
                'Content-Type: application/x-www-form-urlencoded'
            ];
            $curlOptions[CURLOPT_POSTFIELDS] = http_build_query( $data );
            $curlOptions[CURLOPT_POST] = 1;
        }

        curl_setopt_array($ch, $curlOptions);

        $resp = curl_exec($ch);

        curl_close($ch);

        if ( ! $resp ) {
            file_put_contents(BASE_STORE_DIR . "logs/curl_errors.txt", curl_error($ch));

            return false;
        }

        return json_decode( $resp );
    }


    protected function renderTemplate( $template = 'shipping/fedex_domestic/form.expand', $data = [] )
    {
        $this->language->load('shipping/fedex');

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
			'href'      => $this->url->link('shipping/fedex_domestic', '', 'SSL'),
      		'separator' => ' :: '
   		);

        $this->data['links'] = [
            'submit' => $this->url->link('shipping/fedex_domestic/save', '', 'SSL'),
            'cacnel' => $this->url->link('extension/shipping', '', 'SSL'),
        ];

        $this->data['action'] = $this->url->link('shipping/fedex_domestic/save', '', 'SSL');

        $this->data['cancel'] = $this->url->link('shipping/fedex_domestic', '', 'SSL');

        $this->data['cities'] = $this->getCities();

        $this->getValues( $data );

        $this->template = $template;
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
 		return $this->response->setOutput($this->render_ecwig());
    }


    protected function validateCreateShipmentForm()
    {
        if ( ! $this->user->hasPermission('modify', 'catalog/product') )
        {
            $this->error['error'] = $this->language->get('error_permission');
        }

        $requiredFields = [
            'fedex_domestic_shipment_dimensions_x',
            'fedex_domestic_shipment_dimensions_y',
            'fedex_domestic_shipment_dimensions_z',
            'fedex_domestic_weight',
            'fedex_domestic_origin_country',
            'fedex_domestic_notes',
            'fedex_domestic_goods_description'
        ];

        foreach( $requiredFields as $field ) {
            if ( ! strlen( $this->request->post[ $field ] ) ) {
                $this->error[ $field ] = $this->language->get('error_field_is_required');
            }
        }
        
        return $this->error ? false : true;
    }


    protected function getAllCities()
    {
        $cities = $this->doRequest( $this->baseUrl . 'cities' );

        if ( ! property_exists( $cities, 'cities' ) ) {
            $cities = [];
        } else {
            $cities = $cities->cities;
        }

        return $cities;
    }


    protected function getCities()
    {
        $cities = $this->doRequest( $this->baseUrl . 'shippingCities' );

        if ( ! property_exists( $cities, 'cities' ) ) {
            $cities = [];
        } else {
            $cities = $cities->cities;
        }

        return $cities;
    }


    protected function getValues( $data = [] )
    {
        foreach( $this->fields as $field ) {
            $this->data[$field] = $this->config->get($field);
        }

        if ( ! empty($data) ) {
            foreach( $data as $key => $value ) {
                $this->data[$key] = $value;
            }
        }
    }


    protected function fail( string $msg = '')
    {
        $json_result['success'] = '0';

        if ( ! empty( $msg ) ) {
            $result_json['errors']['error'] = $msg;
        } else {
            $result_json['errors'] = $this->error;
        }

        $this->response->setOutput(json_encode($result_json));
    }
}
