<?php

use ExpandCart\Foundation\Support\Facades\Url;

class ControllerShippingUPS extends Controller
{
	private $error = array();

	public function index()
    {
		$this->language->load('shipping/ups');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( ! $this->validate() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

            $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'ups', true);

            
            $this->tracking->updateGuideValue('SHIPPING');

            $this->model_setting_setting->editSetting('ups', $this->request->post);

			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';
            
            $this->response->setOutput(json_encode($result_json));
            
            return;

		}

        $this->data['heading_title'] = $this->language->get('heading_title');

        // ========================== breadcrumbs ===========================

  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => Url::attachPath(['common', 'home'])->format(),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_shipping'),
            'href'      => Url::attachPath(['extension', 'shipping'])->format(),
      		'separator' => ' :: '
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => Url::attachPath(['shipping', 'ups'])->format(),
      		'separator' => ' :: '
   		);

		$this->data['links'] = [
		    'submit' => Url::attachPath(['shipping', 'ups'])->format(),
		    'cancel' => Url::attachPath(['extension', 'shipping'])->format(),
        ];

        $this->data['cancel'] = $this->url->link('shipping/ups', '', 'SSL');

        // ========================== /breadcrumbs ===========================

        $this->data['activeServices'] = [];

		$this->data['ups_key'] = $this->config->get('ups_key');

		$this->data['ups_username'] = $this->config->get('ups_username');

		$this->data['ups_password'] = $this->config->get('ups_password');

		$this->data['ups_pickup'] = $this->config->get('ups_pickup');

		$this->data['pickups'] =
        [
		    ['value' => '01', 'text'  => $this->language->get('text_daily_pickup')],
		    ['value' => '03', 'text'  => $this->language->get('text_customer_counter')],
		    ['value' => '06', 'text'  => $this->language->get('text_one_time_pickup')],
		    ['value' => '07', 'text'  => $this->language->get('text_on_call_air_pickup')],
		    ['value' => '19', 'text'  => $this->language->get('text_letter_center')],
		    ['value' => '20', 'text'  => $this->language->get('text_air_service_center')],
		    ['value' => '11', 'text'  => $this->language->get('text_suggested_retail_rates')],
        ];

		$this->data['ups_packaging'] = $this->config->get('ups_packaging');

		$this->data['packages'] =
        [
		    ['value' => '02', 'text'  => $this->language->get('text_package')],
		    ['value' => '01', 'text'  => $this->language->get('text_ups_letter')],
		    ['value' => '03', 'text'  => $this->language->get('text_ups_tube')],
		    ['value' => '04', 'text'  => $this->language->get('text_ups_pak')],
		    ['value' => '21', 'text'  => $this->language->get('text_ups_express_box')],
		    ['value' => '24', 'text'  => $this->language->get('text_ups_25kg_box')],
		    ['value' => '25', 'text'  => $this->language->get('text_ups_10kg_box')],
        ];
		
        $this->data['ups_classification'] = $this->config->get('ups_classification');

		$this->data['classifications'][] = array(
			'value' => '01',
			'text'  => '01'
		);

		$this->data['classifications'][] = array(
			'value' => '03',
			'text'  => '03'
		);

		$this->data['classifications'][] = array(
			'value' => '04',
			'text'  => '04'
		);

		$this->data['ups_origin'] = $this->config->get('ups_origin');

		$this->data['origins'] =
        [
		    ['value' => 'US', 'text'  => $this->language->get('text_us')],
		    ['value' => 'CA', 'text'  => $this->language->get('text_ca')],
		    ['value' => 'EU', 'text'  => $this->language->get('text_eu')],
		    ['value' => 'PR', 'text'  => $this->language->get('text_pr')],
		    ['value' => 'MX', 'text'  => $this->language->get('text_mx')],
		    ['value' => 'other', 'text'  => $this->language->get('text_other')],
        ];

		$this->data['ups_city'] = $this->config->get('ups_city');

		$this->data['ups_state'] = $this->config->get('ups_state');

		$this->data['ups_country'] = $this->config->get('ups_country');

		$this->data['ups_postcode'] = $this->config->get('ups_postcode');

		$this->data['ups_test'] = $this->config->get('ups_test');

		$this->data['ups_quote_type'] = $this->config->get('ups_quote_type');

		$this->data['quote_types'] = array();

		$this->data['quote_types'][] = array(
			'value' => 'residential',
			'text'  => $this->language->get('text_residential')
		);

		$this->data['quote_types'][] = array(
			'value' => 'commercial',
			'text'  => $this->language->get('text_commercial')
		);

        $this->data['activeServices']['ups_us_01'] = $this->data['ups_us_01'] = $this->config->get('ups_us_01');

        $this->data['activeServices']['ups_us_02'] = $this->data['ups_us_02'] = $this->config->get('ups_us_02');

        $this->data['activeServices']['ups_us_03'] = $this->data['ups_us_03'] = $this->config->get('ups_us_03');

        $this->data['activeServices']['ups_us_07'] = $this->data['ups_us_07'] = $this->config->get('ups_us_07');

        $this->data['activeServices']['ups_us_08'] = $this->data['ups_us_08'] = $this->config->get('ups_us_08');

        $this->data['activeServices']['ups_us_11'] = $this->data['ups_us_11'] = $this->config->get('ups_us_11');

        $this->data['activeServices']['ups_us_12'] = $this->data['ups_us_12'] = $this->config->get('ups_us_12');

        $this->data['activeServices']['ups_us_13'] = $this->data['ups_us_13'] = $this->config->get('ups_us_13');

        $this->data['activeServices']['ups_us_14'] = $this->data['ups_us_14'] = $this->config->get('ups_us_14');

        $this->data['activeServices']['ups_us_54'] = $this->data['ups_us_54'] = $this->config->get('ups_us_54');
        
        $this->data['activeServices']['ups_us_59'] = $this->data['ups_us_59'] = $this->config->get('ups_us_59');

        $this->data['activeServices']['ups_us_65'] = $this->data['ups_us_65'] = $this->config->get('ups_us_65');

        $this->data['activeServices']['ups_pr_01'] = $this->data['ups_pr_01'] = $this->config->get('ups_pr_01');

        $this->data['activeServices']['ups_pr_02'] = $this->data['ups_pr_02'] = $this->config->get('ups_pr_02');

        $this->data['activeServices']['ups_pr_03'] = $this->data['ups_pr_03'] = $this->config->get('ups_pr_03');

        $this->data['activeServices']['ups_pr_07'] = $this->data['ups_pr_07'] = $this->config->get('ups_pr_07');

        $this->data['activeServices']['ups_pr_08'] = $this->data['ups_pr_08'] = $this->config->get('ups_pr_08');

        $this->data['activeServices']['ups_pr_14'] = $this->data['ups_pr_14'] = $this->config->get('ups_pr_14');

        $this->data['activeServices']['ups_pr_54'] = $this->data['ups_pr_54'] = $this->config->get('ups_pr_54');

        $this->data['activeServices']['ups_pr_65'] = $this->data['ups_pr_65'] = $this->config->get('ups_pr_65');

		// Canada
        
        $this->data['activeServices']['ups_ca_01'] = $this->data['ups_ca_01'] = $this->config->get('ups_ca_01');

        $this->data['activeServices']['ups_ca_02'] = $this->data['ups_ca_02'] = $this->config->get('ups_ca_02');

        $this->data['activeServices']['ups_ca_07'] = $this->data['ups_ca_07'] = $this->config->get('ups_ca_07');

        $this->data['activeServices']['ups_ca_08'] = $this->data['ups_ca_08'] = $this->config->get('ups_ca_08');

        $this->data['activeServices']['ups_ca_11'] = $this->data['ups_ca_11'] = $this->config->get('ups_ca_11');

        $this->data['activeServices']['ups_ca_12'] = $this->data['ups_ca_12'] = $this->config->get('ups_ca_12');

        $this->data['activeServices']['ups_ca_13'] = $this->data['ups_ca_13'] = $this->config->get('ups_ca_13');

        $this->data['activeServices']['ups_ca_14'] = $this->data['ups_ca_14'] = $this->config->get('ups_ca_14');

        $this->data['activeServices']['ups_ca_54'] = $this->data['ups_ca_54'] = $this->config->get('ups_ca_54');

        $this->data['activeServices']['ups_ca_65'] = $this->data['ups_ca_65'] = $this->config->get('ups_ca_65');

		// Mexico
            $this->data['activeServices']['ups_mx_07'] = $this->data['ups_mx_07'] = $this->config->get('ups_mx_07');

            $this->data['activeServices']['ups_mx_08'] = $this->data['ups_mx_08'] = $this->config->get('ups_mx_08');

            $this->data['activeServices']['ups_mx_54'] = $this->data['ups_mx_54'] = $this->config->get('ups_mx_54');

            $this->data['activeServices']['ups_mx_65'] = $this->data['ups_mx_65'] = $this->config->get('ups_mx_65');

		// EU
            $this->data['activeServices']['ups_eu_07'] = $this->data['ups_eu_07'] = $this->config->get('ups_eu_07');

            $this->data['activeServices']['ups_eu_08'] = $this->data['ups_eu_08'] = $this->config->get('ups_eu_08');

            $this->data['activeServices']['ups_eu_11'] = $this->data['ups_eu_11'] = $this->config->get('ups_eu_11');

            $this->data['activeServices']['ups_eu_54'] = $this->data['ups_eu_54'] = $this->config->get('ups_eu_54');

            $this->data['activeServices']['ups_eu_65'] = $this->data['ups_eu_65'] = $this->config->get('ups_eu_65');

            $this->data['activeServices']['ups_eu_82'] = $this->data['ups_eu_82'] = $this->config->get('ups_eu_82');

            $this->data['activeServices']['ups_eu_83'] = $this->data['ups_eu_83'] = $this->config->get('ups_eu_83');

            $this->data['activeServices']['ups_eu_84'] = $this->data['ups_eu_84'] = $this->config->get('ups_eu_84');

            $this->data['activeServices']['ups_eu_85'] = $this->data['ups_eu_85'] = $this->config->get('ups_eu_85');

            $this->data['activeServices']['ups_eu_86'] = $this->data['ups_eu_86'] = $this->config->get('ups_eu_86');

		// Other
			$this->data['ups_other_07'] = $this->config->get('ups_other_07');
            $this->data['activeServices']['ups_other_07'] = $this->data['ups_other_07'];

			$this->data['ups_other_08'] = $this->config->get('ups_other_08');
            $this->data['activeServices']['ups_other_08'] = $this->data['ups_other_08'];

			$this->data['ups_other_11'] = $this->config->get('ups_other_11');
            $this->data['activeServices']['ups_other_11'] = $this->data['ups_other_11'];

			$this->data['ups_other_54'] = $this->config->get('ups_other_54');
            $this->data['activeServices']['ups_other_54'] = $this->data['ups_other_54'];

			$this->data['ups_other_65'] = $this->config->get('ups_other_65');
            $this->data['activeServices']['ups_other_65'] = $this->data['ups_other_65'];


        $this->data['services'] = [
            'US' => [
                'ups_us_01' => 'text_next_day_air',
                'ups_us_02' => 'text_2nd_day_air',
                'ups_us_03' => 'text_ground',
                'ups_us_07' => 'text_worldwide_express',
                'ups_us_08' => 'text_worldwide_expedited',
                'ups_us_11' => 'text_standard',
                'ups_us_12' => 'text_3_day_select',
                'ups_us_13' => 'text_next_day_air_saver',
                'ups_us_14' => 'text_next_day_air_early_am',
                'ups_us_54' => 'text_worldwide_express_plus',
                'ups_us_59' => 'text_2nd_day_air_am',
                'ups_us_65' => 'text_saver',
            ],
            'PR' => [
                'ups_pr_01' => 'text_next_day_air',
                'ups_pr_02' => 'text_2nd_day_air',
                'ups_pr_03' => 'text_ground',
                'ups_pr_07' => 'text_worldwide_express',
                'ups_pr_08' => 'text_worldwide_expedited',
                'ups_pr_14' => 'text_next_day_air_early_am',
                'ups_pr_54' => 'text_worldwide_express_plus',
                'ups_pr_65' => 'text_saver',
            ],
            'CA' => [
                'ups_ca_01' => 'text_next_day_air',
                'ups_ca_02' => 'text_2nd_day_air',
                'ups_ca_07' => 'text_worldwide_express',
                'ups_ca_08' => 'text_worldwide_expedited',
                'ups_ca_11' => 'text_standard',
                'ups_ca_12' => 'text_3_day_select',
                'ups_ca_13' => 'text_next_day_air_saver',
                'ups_ca_14' => 'text_next_day_air_early_am',
                'ups_ca_54' => 'text_worldwide_express_plus',
                'ups_ca_65' => 'text_saver',
            ],
            'MX' => [
                'ups_mx_07' => 'text_worldwide_express',
                'ups_mx_08' => 'text_worldwide_expedited',
                'ups_mx_54' => 'text_worldwide_express_plus',
                'ups_mx_65' => 'text_saver',
            ],
            'EU' => [
                'ups_eu_07' => 'text_worldwide_express',
                'ups_eu_08' => 'text_worldwide_expedited',
                'ups_eu_11' => 'text_standard',
                'ups_eu_54' => 'text_worldwide_express_plus',
                'ups_eu_65' => 'text_saver',
                'ups_eu_82' => 'text_today_standard',
                'ups_eu_83' => 'text_today_dedicated_courier',
                'ups_eu_84' => 'text_today_intercity',
                'ups_eu_85' => 'text_today_express',
                'ups_eu_86' => 'text_today_express_saver',
            ],
            'other' => [
                'ups_other_07' => 'text_worldwide_express',
                'ups_other_08' => 'text_worldwide_expedited',
                'ups_other_11' => 'text_standard',
                'ups_other_54' => 'text_worldwide_express_plus',
                'ups_other_65' => 'text_saver',
            ]
        ];


		$this->data['ups_display_weight'] = $this->config->get('ups_display_weight');

		$this->data['ups_insurance'] = $this->config->get('ups_insurance');

		$this->data['ups_weight_class_id'] = $this->config->get('ups_weight_class_id');

		$this->load->model('localisation/weight_class');

		$this->data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses();

		$this->data['ups_length_code'] = $this->config->get('ups_length_code');

		$this->data['ups_length_class_id'] = $this->config->get('ups_length_class_id');

		$this->load->model('localisation/length_class');

		$this->data['length_classes'] = $this->model_localisation_length_class->getLengthClasses();

		$this->data['ups_length'] = $this->config->get('ups_length');

		$this->data['ups_width'] = $this->config->get('ups_width');

		$this->data['ups_height'] = $this->config->get('ups_height');

		$this->data['ups_tax_class_id'] = $this->config->get('ups_tax_class_id');

		$this->load->model('localisation/tax_class');

		$this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		$this->data['ups_geo_zone_id'] = $this->config->get('ups_geo_zone_id');

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$this->data['ups_status'] = $this->config->get('ups_status');

		$this->data['ups_sort_order'] = $this->config->get('ups_sort_order');

		$this->data['ups_debug'] = $this->config->get('ups_debug');

		$this->template = 'shipping/ups.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);

 		$this->response->setOutput($this->render_ecwig());
	}

	protected function validate()
    {
		if ( ! $this->user->hasPermission('modify', 'shipping/ups') )
        {
			$this->error['error'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['ups_key']) {
			$this->error['ups_key'] = $this->language->get('error_key');
		}

		if (!$this->request->post['ups_username']) {
			$this->error['ups_username'] = $this->language->get('error_username');
		}

		if (!$this->request->post['ups_password']) {
			$this->error['ups_password'] = $this->language->get('error_password');
		}

		if (!$this->request->post['ups_city']) {
			$this->error['ups_city'] = $this->language->get('error_city');
		}

		if (!$this->request->post['ups_state']) {
			$this->error['ups_state'] = $this->language->get('error_state');
		}

		if (!$this->request->post['ups_country']) {
			$this->error['ups_country'] = $this->language->get('error_country');
		}

		if (empty($this->request->post['ups_length'])) {
			$this->error['ups_length'] = $this->language->get('error_dimension');
		}

		if (empty($this->request->post['ups_width'])) {
			$this->error['ups_width'] = $this->language->get('error_dimension');
		}

		if (empty($this->request->post['ups_height'])) {
			$this->error['ups_height'] = $this->language->get('error_dimension');
		}

		if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        
        return $this->error ? false : true;
	}

    public function create_shipment()
    {

        // load ups model
        $this->language->load('shipping/ups');
        // load order model
        $this->load->model('sale/order');
        // load country  model
        $this->load->model('localisation/country');
        // load zone  model
        $this->load->model('localisation/zone');

        // set title page
        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => Url::addPath(['common', 'home'])->format(),
                'separator' => false
            ],
            [
                'text' => $this->language->get('text_shipping'),
                'href' => Url::addPath(['extension', 'shipping'])->format(),
                'separator' => ' :: '
            ],
            [
                'text' => $this->language->get('heading_title'),
                'href' => Url::addPath(['shipping', 'ups'])->format(),
                'separator' => ' :: '
            ],
        ];

        // get order data
        $this->data['order_id'] = $this->request->get['order_id'];

        $this->data['order_data'] = $this->model_sale_order->getOrder($this->request->get['order_id']);
        // get all country and codes
        $this->data['countries'] = $this->model_localisation_country->getCountries();
        // get all zones and codes
        $this->data['zones'] = $this->model_localisation_zone->getZones();

        // all payments types
        $this->data['ups_payments'] = [
            '01' => 'American Express',
            '03' => 'Discover',
            '04' => 'MasterCard',
            '05' => 'Optima',
            '06' => 'VISA',
            '07' => 'Bravo',
            '08' => 'Diners Club',
            '13' => 'Dankort',
            '14' => 'Hipercard',
            '15' => 'JCB',
            '17' => 'Postepay',
            '18' => 'UnionPay/ExpressPay',
            '19' => 'Visa Electron',
            '20' => 'VPAY',
            '21' => 'Carte Bleue'
        ];
        // all services types
        $this->data['ups_services'] = [
            '01' => 'Next Day Air',
            '02' => '2nd Day Air',
            '03' => 'Ground',
            '07' => 'Express',
            '08' => 'Expedited',
            '11' => 'UPS Standard',
            '12' => '3 Day Select',
            '13' => 'Next Day Air Saver',
            '14' => 'UPS Next Day Air® Early',
            '17' => 'UPS Worldwide Economy DDU',
            '54' => 'Express Plus',
            '59' => '2nd Day Air A.M.',
            '65' => 'UPS Saver'
            ];
        $this->data['ups_packaging_code'] = [
            '01' => 'UPS Letter',
            '02' => 'Customer Supplied Package',
            '03' => 'Tube 04 = PAK',
            '21' => 'UPS Express Box',
            '24' => 'UPS 25KG Box',
            '25' => 'UPS 10KG Box',
            '30' => 'Pallet',
            '2a' => 'Small Express Box',
            '2b' => 'Medium Express Box 2c = Large Express Box',
            '56' => 'Flats',
            '57' => 'Parcels',
            '58' => 'BPM',
            '59' => 'First Class',
            '60' => 'Priority',
            '61' => 'Machineables',
            '62' => 'Irregulars',
            '63' => 'Parcel Post',
            '64' => 'BPM Parcel',
            '65' => 'Media Mail',
            '66' => 'BPM Flat',
            '67' => 'Standard Flat'
        ];

        $this->data['ups_weight_codes'] = [
            'IN'  => 'Inches',
            'CM'  => 'Centimeters',
            '00'  => 'Metric Units Of Measurement',
            '01'  => 'English Units of Measurement'
        ];


        $testingMode = $this->config->get('ups_test');

        if ($testingMode) {
            $curl_url = "https://onlinetools.ups.com/ship/v1/shipments";
        } else {
            $curl_url = "https://onlinetools.ups.com/ship/v1/shipments";
        }

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            // check validate
            if ($this->shipment_validate()) {
                // preparing curl data
                $curl_data = array(
                    "ShipmentRequest" => array(
                        "Shipment"=>array(
                            "Description"=>$this->request->post['shipment']['description'],
                            "Shipper"=>array(
                                "Name" => $this->request->post['shipper']['name'],
                                "AttentionName" => $this->request->post['shipper']['attention_name'],
                                "TaxIdentificationNumber" => $this->request->post['shipper']['tax_identification_number'],
                                "Phone" => array("Number"=>$this->request->post['shipper']['phone']['phone_number'] ),
                                "ShipperNumber" => $this->request->post['shipper']['shipper_number'],
                                "Address" =>array(
                                    "AddressLine" => $this->request->post['shipper']['address']['shipper_address_line'],
                                    "CountryCode" =>  $this->request->post['shipper']['address']['country_code'],
                                    "City" =>  $this->request->post['shipper']['address']['shipper_city'],
                                    "StateProvinceCode" =>  $this->request->post['shipper']['address']['shipper_state_province_code'],
                                    "PostalCode" =>  $this->request->post['shipper']['address']['postal_code'],
                                ),
                            ), // shipper

                            "ShipTo"=>array(
                                "Name" => $this->request->post['shipTo']['name'],
                                "AttentionName" => $this->request->post['shipTo']['attention_name'],
                                "TaxIdentificationNumber" => $this->request->post['shipTo']['tax_identification_number'],
                                "Phone" => array("Number"=>$this->request->post['shipTo']['phone']['phone_number'] ),
                                "FaxNumber" => $this->request->post['shipTo']['fax_number'],
                                "Address" =>array(
                                    "AddressLine" => $this->request->post['shipTo']['address']['address_line'],
                                    "CountryCode" =>  $this->request->post['shipTo']['address']['country_code'],
                                    "City" =>  $this->request->post['shipTo']['address']['shipper_city'],
                                    "StateProvinceCode" =>  $this->request->post['shipTo']['address']['state_province_code'],
                                    "PostalCode" =>  $this->request->post['shipTo']['address']['postal_code'],
                                ),
                            ), // ShipTo

                            "ShipFrom"=>array(
                                "Name" => $this->request->post['shipFrom']['name'],
                                "AttentionName" => $this->request->post['shipFrom']['attention_name'],
                                "TaxIdentificationNumber" => $this->request->post['shipFrom']['tax_identification_number'],
                                "Phone" => array("Number"=>$this->request->post['shipFrom']['phone']['phone_number'] ),
                                "FaxNumber" => $this->request->post['shipFrom']['fax_number'],
                                "Address" =>array(
                                    "AddressLine" => $this->request->post['shipFrom']['address']['address_line'],
                                    "CountryCode" =>  $this->request->post['shipFrom']['address']['country_code'],
                                    "City" =>  $this->request->post['shipFrom']['address']['shipper_city'],
                                    "StateProvinceCode" =>  $this->request->post['shipFrom']['address']['state_province_code'],
                                    "PostalCode" =>  $this->request->post['shipFrom']['address']['postal_code'],
                                ),
                            ), // ShipFrom
                            "PaymentInformation"=>array(
                                "ShipmentCharge" => array(
                                                          "Type" => $this->request->post['shipment_charge']['type'],
                                                          "BillShipper" => array("AccountNumber" => $this->request->post['shipment_charge']['account_number'])
                                                    ),

                            ), // PaymentInformation
                            "Service"=>array(
                                "Code" => $this->request->post['service']['code'],
                                "Description" => $this->request->post['service']['description'],

                            ), // Service

                            "Package"=>array(

                                "Description" => $this->request->post['package']['description'],
                                "Packaging"  => array("Code" => $this->request->post['package']['packaging_code']),
                                "PackageWeight"  => array(
                                    "UnitOfMeasurement" =>array("Code" => $this->request->post['package']['package_weight_code']),
                                    "Weight" => $this->request->post['package']['package_weight']
                                    ),

                            ),// Package

                            "LabelSpecification"=>array(
                                "LabelImageFormat"  => array("Code" => $this->request->post['label_image_format']['code']),
                            ), // LabelImageFormat

                        ),  //shipment details
                    )  // shipment request
                );

                // send curl request
                $result = $this->sendCurlRequest($curl_url, json_encode($curl_data));

                if (is_object($result['response']) && $result['response']->ShipmentResponse->Response->ResponseStatus->Code == 1) {
                    $resultData['shipment_data'] = $curl_data;
                    $this->load->model("shipping/ups");
                    $this->model_shipping_ups->addShipmentDetails($this->request->post['order_id'], $resultData, "pre-pair");
                    $this->response->setOutput(json_encode([
                        'success' => 1,
                        'success_msg' => $this->language->get('ups_success_message'),
                    ]));
                } else {
                    $result_json['success'] = '0';
                    $result_json['errors'] = [$result['response']->response->errors[0]->message];
                    $result_json['errors']['warning'] = $this->language->get('ups_error_warning');
                    $this->response->setOutput(json_encode($result_json));
                }


            }else{
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                $this->response->setOutput(json_encode($result_json));

                return;
            }


        }else{

            $this->template = 'shipping/ups/shipment/create.expand';
            $this->children = array(
                'common/footer',
                'common/header'
            );
            $this->response->setOutput($this->render_ecwig());
            return;
        }



    }

    private  function shipment_validate(){

        if (empty($this->request->post['shipment']['description'])  && empty($this->request->post['shipper']['name']) && empty($this->request->post['shipper']['attention_name'])
            && empty($this->request->post['shipper']['tax_identification_number']) && empty($this->request->post['shipper']['phone']['phone_number']) && empty($this->request->post['shipper']['shipper_number'])
            && empty($this->request->post['shipper']['address']['shipper_address_line']) && empty($this->request->post['shipper']['address']['country_code']) && empty($this->request->post['shipper']['address']['shipper_city'])
            && empty($this->request->post['shipper']['address']['shipper_state_province_code']) && empty($this->request->post['shipper']['address']['postal_code'])    )  {
            $this->error['error_shipper'] = $this->language->get('error_shipper');

        }
        if (empty($this->request->post['shipTo']['name'])  && empty($this->request->post['shipTo']['attention_name'])  && empty($this->request->post['shipTo']['tax_identification_number'])
            && empty($this->request->post['shipTo']['phone']['phone_number'])
            && empty($this->request->post['shipTo']['fax_number'])  && empty($this->request->post['shipTo']['address']['address_line'])  && empty($this->request->post['shipTo']['address']['country_code'])
            && empty($this->request->post['shipTo']['address']['shipper_city'])
            && empty($this->request->post['shipTo']['address']['state_province_code'])  && empty($this->request->post['shipTo']['address']['postal_code'])   )  {
            $this->error['error_shipTo'] = $this->language->get('error_customer');
        }
        if (empty($this->request->post['shipFrom']['name'])  && empty($this->request->post['shipFrom']['attention_name'])    && empty($this->request->post['shipFrom']['tax_identification_number'])
            && empty($this->request->post['shipFrom']['phone']['phone_number']) && empty($this->request->post['shipFrom']['fax_number'])  && empty($this->request->post['shipFrom']['address']['address_line'])
            && empty($this->request->post['shipFrom']['address']['country_code'])   && empty($this->request->post['shipFrom']['address']['shipper_city'])
            && empty($this->request->post['shipFrom']['address']['state_province_code'])  && empty($this->request->post['shipFrom']['address']['postal_code'])    )  {
            $this->error['error_shipFrom'] = $this->language->get('error_shipper_place');
        }

        if (empty($this->request->post['shipment_charge']['type'])  && empty($this->request->post['shipment_charge']['account_number'])     )  {
            $this->error['error_payment'] = $this->language->get('error_payment');
        }

        if (empty($this->request->post['service']['code'])  && empty($this->request->post['service']['description'])   && empty($this->request->post['package']['description'])
            && empty($this->request->post['package']['packaging_code'])     )  {
            $this->error['error_package'] = $this->language->get('error_package');
        }
        if (empty($this->request->post['package']['package_weight_code'])  && empty($this->request->post['package']['package_weight'])   && empty($this->request->post['label_image_format']['code'])     )  {
            $this->error['error_service'] = $this->language->get('error_service');
        }

        return $this->error ? false : true;
    }

    private function sendCurlRequest($_url, $data)
    {
        $curl = curl_init($_url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $options = array(
            CURLOPT_RETURNTRANSFER => true,   // return web page
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'AccessLicenseNumber: '.$this->config->get('ups_key'),
                'Password: '.$this->config->get('ups_password'),
                'Username: '.$this->config->get('ups_username'),
                'Accept: application/json',
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
        $result = ['response' => $response,
            'data' => $data
        ];
        return $result;
    }
}
