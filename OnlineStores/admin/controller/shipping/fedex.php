<?php

class ControllerShippingFedex extends Controller
{
	private $error = array(); 
	
	public function index()
    {
		$this->language->load('shipping/fedex');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
		$this->load->model('localisation/geo_zone');
		$this->load->model('shipping/fedex');
        $this->load->model('localisation/country');

        $this->data['data'] = $this->model_shipping_fedex->getSettings();
        $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
        $this->data['countries'] = $this->model_localisation_country->getCountries();

        foreach ( $this->data['geo_zones'] as $geo_zone )
        {

            $this->data['fedex_weight_' . $geo_zone['geo_zone_id'] . '_rate'] = $this->config->get('fedex_weight_' . $geo_zone['geo_zone_id'] . '_rate');

            $this->data['fedex_weight_' . $geo_zone['geo_zone_id'] . '_status'] = $this->config->get('fedex_weight_' . $geo_zone['geo_zone_id'] . '_status');
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
            $fedex = $this->request->post['fedex'];

			$this->model_setting_setting->checkIfExtensionIsExists('shipping', 'fedex', true);

            
            $this->tracking->updateGuideValue('SHIPPING');

            $this->model_shipping_fedex->updateSettings($fedex);
					
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
			'href'      => $this->url->link('shipping/fedex', '', 'SSL'),
      		'separator' => ' :: '
   		);

        $this->data['links'] = [
            'submit' => $this->url->link('shipping/fedex', '', 'SSL'),
            'cacnel' => $this->url->link('extension/shipping', '', 'SSL'),
        ];
        $this->data['action'] = $this->url->link('shipping/fedex', '', 'SSL');

        $this->data['cancel'] = $this->url->link('shipping/fedex', '', 'SSL');

        // ======================== /breadcrumbs =======================



		$this->template = 'shipping/fedex/fedex.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
 		$this->response->setOutput($this->render_ecwig());
	}

    public function createShipment()
    {

        // load Language File
        $this->language->load('shipping/fedex');

        // set Page Title
        $this->document->setTitle($this->language->get('heading_title'));

        // get data from setting table
        $testingMode = $this->config->get('fedex')['test'];
        // check Test Mode
        if ($testingMode) {
            $url = 'https://wsbeta.fedex.com:443/web-services/ship';
        } else {
            $url = 'https://ws.fedex.com:443/web-services/ship';
        }

        // define allowed services
        $this->data['services'] = array();

        $this->data['services'][] = array(
            'text'  => $this->language->get('text_international_priority'),
            'value' => 'INTERNATIONAL_PRIORITY'
        );

        $this->data['services'][] = array(
            'text'  => $this->language->get('text_international_priority_freight'),
            'value' => 'INTERNATIONAL_PRIORITY_FREIGHT'
        );

        // define allowed drop off types
        $this->data['dropoff_types'] = [
            'REGULAR_PICKUP' => 'text_regular_pickup',
            'REQUEST_COURIER' => 'text_request_courier',
            'DROP_BOX' => 'text_drop_box',
            'BUSINESS_SERVICE_CENTER' => 'text_business_service_center',
            'STATION' => 'text_station',
        ];

        // define allowed packaging types

        $this->data['packing_types'] = [
            'FEDEX_ENVELOPE' => 'text_fedex_envelope',
            'FEDEX_PAK' => 'text_fedex_pak',
            'FEDEX_BOX' => 'text_fedex_box',
            'FEDEX_TUBE' => 'text_fedex_tube',
            'FEDEX_10KG_BOX' => 'text_fedex_10kg_box',
            'FEDEX_25KG_BOX' => 'text_fedex_25kg_box',
            'YOUR_PACKAGING' => 'text_your_packaging',
        ];

        // define allowed weight units
        $this->data['weight_units'] =
            [
                ['value' => 'LB', 'text'  => $this->language->get('text_weightUnits_pound')],
                ['value' => 'KG', 'text'  => $this->language->get('text_weightUnits_kilogram')],
            ];

        // define allowed dimension units
        $this->data['dimension_units'] =
            [
                ['value' => 'CM', 'text'  => $this->language->get('text_cm')],
                ['value' => 'IN', 'text'  => $this->language->get('text_in')],
            ];



        $this->load->model('sale/order');
        $this->load->model('setting/setting');
        $this->load->model('shipping/fedex');


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
            'href'      => $this->url->link('shipping/fedex', '', 'SSL'),
            'separator' => ' :: '
        );



        if ($this->request->server['REQUEST_METHOD'] == 'POST') {

            if ($this->validate_shipment()) {

                $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'fedex', true);

                $requestData['config'] =  $this->model_shipping_fedex->getSettings();
                $requestData['form_data'] = $this->request->post['fedex'];

                // send curl request
                $result = $this->sendCurlRequest($url, $requestData);

                $dom = new DOMDocument('1.0', 'UTF-8');
                $dom->loadXml($result);
                // check if error
                $error = $dom->getElementsByTagName('HighestSeverity')->item(0)->nodeValue;
                
                if ($error == 'FAILURE' || $error == 'WARNING' || $error == 'ERROR' ) {
                    $result_json['success'] = '0';
                    $result_json['errors'] = [$dom->getElementsByTagName('Message')->item(0)->nodeValue];
                    $result_json['errors']['warning'] = $this->language->get('fedex_error_warning');
                    $this->response->setOutput(json_encode($result_json));
                }else{
                    $fedex_data['TrackingNumber'] = $dom->getElementsByTagName('TrackingNumber')->item(0)->nodeValue;
                    $resultData['shipment_data'] = $requestData['form_data'];
                    $resultData['fedex_data'] = $fedex_data;

                    $this->load->model("shipping/fedex");
                    $this->model_shipping_fedex->addShipmentDetails($requestData['form_data']['order_id'], $resultData, "pre-pair");
                    $this->response->setOutput(json_encode([
                        'success' => 1,
                        'success_msg' => $this->language->get('fedex_success_message'),
                        'redirect' => 1,
                        'to' => $this->url->link('shipping/fedex/shipmentDetails', 'order_id=' . $requestData['form_data']['order_id'], 'SSL')->format()
                    ]));
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

            $this->load->model("shipping/fedex");

            // get shipping details for check if customer make shipment before
            $shipping_details = $this->model_shipping_fedex->getShipmentDetails($orderId);

            if(count($shipping_details) > 0)
            {
                $this->redirect($this->url->link('shipping/fedex/shipmentDetails', 'order_id=' . $orderId, 'SSL'));
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

                    $product_weight_query = $this->model_shipping_fedex->getProductWeight($order_product['product_id']);
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


            $this->template = 'shipping/fedex/create_shipment.expand';
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
        $this->language->load('shipping/fedex');

        // set Page Title
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('shipping/fedex');


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
            'href'      => $this->url->link('shipping/fedex', '', 'SSL'),
            'separator' => ' :: '
        );

        // get order id
        $orderId = $this->request->get['order_id'];

        $this->load->model("shipping/esnad");
        // get order data
        $orderData = $this->model_shipping_fedex->getShipmentDetails($orderId);

        $this->data['shipment_details'] = json_decode($orderData['details'],true, 512, JSON_UNESCAPED_UNICODE);


        $this->data['order_id'] = $orderId;


        $this->template = 'shipping/fedex/details.expand';
        $this->children = array(
            'common/footer',
            'common/header'
        );
        $this->response->setOutput($this->render_ecwig());
        return;
    }

    private function sendCurlRequest($_url,$data)
    {

        $date = time();

        $day = date('l', $date);

        if ($day == 'Saturday') {
            $date += 172800;
        } elseif ($day == 'Sunday') {
            $date += 86400;
        }


        $xml = "
        <soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:v19=\"http://fedex.com/ws/ship/v19\">
        <soapenv:Header />
        <soapenv:Body>
            <v19:ProcessShipmentRequest>
                <v19:WebAuthenticationDetail>
                    <v19:UserCredential>
                    <v19:Key>".$data['config']['key']."</v19:Key>
                    <v19:Password>".$data['config']['password']."</v19:Password>
                    </v19:UserCredential>
                </v19:WebAuthenticationDetail>
                <v19:ClientDetail>
                    <v19:AccountNumber>".$data['config']['account']."</v19:AccountNumber>
                    <v19:MeterNumber>".$data['config']['meter']."</v19:MeterNumber>
                </v19:ClientDetail>
                <v19:TransactionDetail>
                    <v19:CustomerTransactionId>ProcessShip_Basic</v19:CustomerTransactionId>
                </v19:TransactionDetail>
                <v19:Version>
                    <v19:ServiceId>ship</v19:ServiceId>
                    <v19:Major>19</v19:Major>
                    <v19:Intermediate>0</v19:Intermediate>
                    <v19:Minor>0</v19:Minor>
                </v19:Version>
                <v19:RequestedShipment>
                    <v19:ShipTimestamp>".date('c', $date)."</v19:ShipTimestamp>
                    <v19:DropoffType>".$data['form_data']['dropoff_type']."</v19:DropoffType>
                    <v19:ServiceType>".$data['form_data']['service']."</v19:ServiceType>
                    <v19:PackagingType>".$data['form_data']['packaging_type']."</v19:PackagingType>
                    <v19:Shipper>
                    <v19:AccountNumber>".$data['config']['account']."</v19:AccountNumber>
                    <v19:Contact>
                        <v19:PersonName>".$data['config']['name']."</v19:PersonName>
                        <v19:CompanyName />
                        <v19:PhoneNumber>".$data['config']['phone']."</v19:PhoneNumber>
                        <v19:EMailAddress />
                    </v19:Contact>
                    <v19:Address>
                        <v19:StreetLines>".$data['config']['address']."</v19:StreetLines>
                        <v19:City>".$data['config']['city']."</v19:City>
                        <v19:StateOrProvinceCode>".$data['config']['city']."</v19:StateOrProvinceCode>
                        <v19:PostalCode>". $data['config']['postcode'] ."</v19:PostalCode>
                        <v19:CountryCode>".$data['config']['country']."</v19:CountryCode>
                    </v19:Address>
                    </v19:Shipper>
                    <v19:Recipient>
                    <v19:Contact>
                        <v19:PersonName>".$data['form_data']['name']."</v19:PersonName>
                        <v19:CompanyName />
                        <v19:PhoneNumber>".$data['form_data']['phone']."</v19:PhoneNumber>
                    </v19:Contact>
                    <v19:Address>
                        <v19:StreetLines>".$data['form_data']['address']."</v19:StreetLines>
                        <v19:City>".$data['form_data']['city']."</v19:City>
                        <v19:StateOrProvinceCode>".$data['form_data']['city_code']."</v19:StateOrProvinceCode>
                        <v19:PostalCode>".$data['form_data']['postcode']."</v19:PostalCode>
                        <v19:CountryCode>".$data['form_data']['country']."</v19:CountryCode>
                    </v19:Address>
                    </v19:Recipient>
                    <v19:ShippingChargesPayment>
                    <v19:PaymentType>SENDER</v19:PaymentType>
                    <v19:Payor>
                        <v19:ResponsibleParty>
                            <v19:AccountNumber>".$data['config']['account']."</v19:AccountNumber>
                            <v19:Contact>
                                <v19:PersonName>".$data['config']['name']."</v19:PersonName>
                                <v19:CompanyName />
                                <v19:PhoneNumber>".$data['config']['phone']."</v19:PhoneNumber>
                                <v19:EMailAddress />
                            </v19:Contact>
                            <v19:Address>
                                <v19:StreetLines>".$data['config']['address']."</v19:StreetLines>
                                <v19:City>".$data['config']['city']."</v19:City>
                                <v19:CountryCode>".$data['config']['country']."</v19:CountryCode>
                            </v19:Address>
                        </v19:ResponsibleParty>
                    </v19:Payor>
                    </v19:ShippingChargesPayment>
                    <v19:CustomsClearanceDetail>
                    <v19:DutiesPayment>
                        <v19:PaymentType>RECIPIENT</v19:PaymentType>
                        <v19:Payor>
                            <v19:ResponsibleParty>
                                <v19:AccountNumber>".$data['config']['account']."</v19:AccountNumber>
                                <v19:Contact>
                                <v19:PersonName />
                                <v19:CompanyName />
                                <v19:PhoneNumber />
                                <v19:EMailAddress />
                                </v19:Contact>
                                <v19:Address>
                                <v19:StreetLines />
                                <v19:City />
                                <v19:CountryCode />
                                </v19:Address>
                            </v19:ResponsibleParty>
                        </v19:Payor>
                    </v19:DutiesPayment>
                    <v19:DocumentContent>DOCUMENTS_ONLY</v19:DocumentContent>
                    <v19:CustomsValue>
                        <v19:Currency>".$data['form_data']['currency']."</v19:Currency>
                        <v19:Amount>". number_format($data['form_data']['order_total'], 2) ."</v19:Amount>
                    </v19:CustomsValue>
                    <v19:CommercialInvoice>
                        <v19:Comments>".$data['form_data']['notes']."</v19:Comments>
                        <v19:PaymentTerms />
                        <v19:CustomerReferences>
                            <v19:CustomerReferenceType>CUSTOMER_REFERENCE</v19:CustomerReferenceType>
                            <v19:Value>".$data['form_data']['order_id']."</v19:Value>
                        </v19:CustomerReferences>
                        <v19:TermsOfSale>FOB</v19:TermsOfSale>
                    </v19:CommercialInvoice>
                    <v19:Commodities>
                        <v19:Name>".$data['form_data']['commodities_name']."</v19:Name>
                        <v19:NumberOfPieces>1</v19:NumberOfPieces>
                        <v19:Description>".$data['form_data']['commodities_description']."</v19:Description>
                        <v19:CountryOfManufacture>".$data['form_data']['commodities_CountryOfManufacture']."</v19:CountryOfManufacture>
                        <v19:Weight>
                            <v19:Units>LB</v19:Units>
                            <v19:Value>0</v19:Value>
                        </v19:Weight>
                        <v19:Quantity>".$data['form_data']['commodities_quantity']."</v19:Quantity>
                        <v19:QuantityUnits>EA</v19:QuantityUnits>
                        <v19:UnitPrice>
                            <v19:Currency>".$data['form_data']['currency']."</v19:Currency>
                            <v19:Amount>". number_format($data['form_data']['order_total'], 2) ."</v19:Amount>
                        </v19:UnitPrice>
                        <v19:CustomsValue>
                            <v19:Currency>".$data['form_data']['currency']."</v19:Currency>
                            <v19:Amount>". number_format($data['form_data']['order_total'], 2) ."</v19:Amount>
                        </v19:CustomsValue>
                    </v19:Commodities>
                    </v19:CustomsClearanceDetail>
                    <v19:LabelSpecification>
                    <v19:LabelFormatType>COMMON2D</v19:LabelFormatType>
                    <v19:ImageType>PDF</v19:ImageType>
                    <v19:LabelStockType>PAPER_8.5X11_BOTTOM_HALF_LABEL</v19:LabelStockType>
                    </v19:LabelSpecification>
                    <v19:RateRequestTypes>LIST</v19:RateRequestTypes>
                    <v19:PackageCount>1</v19:PackageCount>
                    <v19:RequestedPackageLineItems>
                    <v19:SequenceNumber>1</v19:SequenceNumber>
                    <v19:InsuredValue>
                        <v19:Currency>".$data['form_data']['currency']."</v19:Currency>
                        <v19:Amount>5</v19:Amount>
                    </v19:InsuredValue>
                    <v19:Weight>
                        <v19:Units>".$data['form_data']['weight_unit']."</v19:Units>
                        <v19:Value>".$data['form_data']['package_wight']."</v19:Value>
                    </v19:Weight>
                    <v19:Dimensions>
                        <v19:Length>".$data['form_data']['package_length']."</v19:Length>
                        <v19:Width>".$data['form_data']['package_width']."</v19:Width>
                        <v19:Height>".$data['form_data']['package_height']."</v19:Height>
                        <v19:Units>".$data['form_data']['dimensions_unit']."</v19:Units>
                    </v19:Dimensions>
                    <v19:CustomerReferences>
                        <v19:CustomerReferenceType>CUSTOMER_REFERENCE</v19:CustomerReferenceType>
                        <v19:Value>".$data['form_data']['order_id']."</v19:Value>
                    </v19:CustomerReferences>
                    </v19:RequestedPackageLineItems>
                </v19:RequestedShipment>
            </v19:ProcessShipmentRequest>
        </soapenv:Body>
        </soapenv:Envelope>
    ";


        $curl = curl_init($_url);

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);
        curl_close($curl);

        return (string) $response;

    }
	
	protected function validate()
    {
        $postData = $this->request->post['fedex'];

		if ( ! $this->user->hasPermission('modify', 'shipping/fedex') )
		{
			$this->error['error'] = $this->language->get('error_permission');
		}

		if (!$postData['key']) {
			$this->error['fedex_key'] = $this->language->get('error_key');
		}
		
		if (!$postData['password']) {
			$this->error['fedex_password'] = $this->language->get('error_password');
		}
						
		if (!$postData['account']) {
			$this->error['fedex_account'] = $this->language->get('error_account');
		}
		
		if (!$postData['meter']) {
			$this->error['fedex_meter'] = $this->language->get('error_meter');
		}

        if (!$postData['name']) {
            $this->error['fedex_name'] = $this->language->get('error_name');
        }

        if (!$postData['phone']) {
            $this->error['fedex_phone'] = $this->language->get('error_phone');
        }

        if (!$postData['address']) {
            $this->error['fedex_address'] = $this->language->get('error_address');
        }

        if (!$postData['country']) {
            $this->error['fedex_country'] = $this->language->get('error_country');
        }

        if (!$postData['city']) {
            $this->error['fedex_city'] = $this->language->get('error_city');
        }

		
		if (!$postData['postcode']) {
			$this->error['fedex_postcode'] = $this->language->get('error_postcode');
		}

		if ( $this->error && !isset($this->error['error']) )
		{
		    $this->error['warning'] = $this->language->get('error_warning');
		}
		
		return $this->error ? false : true;
	}

    protected function validate_shipment()
    {
        $postData = $this->request->post['fedex'];

        if ( ! $this->user->hasPermission('modify', 'shipping/fedex') )
        {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if (!$postData['name']) {
            $this->error['name'] = $this->language->get('error_name');
        }

        if (!$postData['phone']) {
            $this->error['phone'] = $this->language->get('error_phone');
        }

        if (!$postData['country']) {
            $this->error['country'] = $this->language->get('error_country');
        }

        if (!$postData['city']) {
            $this->error['city'] = $this->language->get('error_city');
        }

        if (!$postData['city_code']) {
            $this->error['city_code'] = $this->language->get('error_city_code');
        }

        if (!$postData['postcode']) {
            $this->error['postcode'] = $this->language->get('error_postcode');
        }


        if (!$postData['address']) {
            $this->error['address'] = $this->language->get('error_address');
        }

        if (!$postData['order_total']) {
            $this->error['order_total'] = $this->language->get('error_order_total');
        }

        if (!$postData['currency']) {
            $this->error['currency'] = $this->language->get('error_currency');
        }

        if (!$postData['commodities_name']) {
            $this->error['commodities_name'] = $this->language->get('error_commodities_name');
        }

        if (!$postData['commodities_description']) {
            $this->error['commodities_description'] = $this->language->get('error_commodities_description');
        }

        if (!$postData['commodities_CountryOfManufacture']) {
            $this->error['commodities_CountryOfManufacture'] = $this->language->get('error_commodities_CountryOfManufacture');
        }

        if (!$postData['commodities_quantity']) {
            $this->error['commodities_quantity'] = $this->language->get('error_commodities_quantity');
        }

        if (!$postData['package_wight']) {
            $this->error['package_wight'] = $this->language->get('error_package_wight');
        }

        if (!$postData['weight_unit']) {
            $this->error['weight_unit'] = $this->language->get('error_weight_unit');
        }

        if (!$postData['package_length']) {
            $this->error['package_length'] = $this->language->get('error_package_length');
        }

        if (!$postData['package_width']) {
            $this->error['package_width'] = $this->language->get('error_package_width');
        }

        if (!$postData['package_height']) {
            $this->error['package_height'] = $this->language->get('error_package_height');
        }

        if (!$postData['dimensions_unit']) {
            $this->error['dimensions_unit'] = $this->language->get('error_dimensions_unit');
        }

        if (!$postData['service']) {
            $this->error['service'] = $this->language->get('error_service');
        }

        if (!$postData['dropoff_type']) {
            $this->error['dropoff_type'] = $this->language->get('error_required_dropoff_type');
        }

        if (!$postData['packaging_type']) {
            $this->error['packaging_type'] = $this->language->get('error_packaging_type');
        }


        if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return $this->error ? false : true;
    }


}
