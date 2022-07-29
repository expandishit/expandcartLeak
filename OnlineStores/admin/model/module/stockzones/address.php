<?php
require_once('entity.php');

class ModelModuleStockzonesAddress extends ModelModuleStockzonesEntity {


    /**
	 * [POST] Get stockzones countries list from their API
	 *
	 * @return 
	 */
    public function getStockzonesCountries(){    	
    	$data = [
    		"method_name" => "getCountryList",
    		"data" => [],
    		"language_code" => "en"
    	];
        //connect API to get categories list
        $response = $this->connectAPI($data);
        
        return  $response['data']['status'] == 'success' ?  $response['data']['result'] : [];
    }


    /**
     * [POST] Get stockzones states list from their API
     *
     * @return 
     */
    public function getStockzonesStates($country_id=''){      
        $data = [
            "method_name" => "getStateList",
            "data" => ["country_id" => $country_id],
            "language_code" => "en"
        ];

        //connect API to get categories list
        $response = $this->connectAPI($data);

        return  $response['data']['status'] == 'success' ?  $response['data']['result'] : [];
    }

    /**
     * [POST] Get stockzones cities list from their API
     *
     * @return 
     */
    public function getStockzonesCities($country_id='', $state_id){      
        $data = [
            "method_name" => "getCityList",
            "data" => ["country_id" => $country_id, "state_id" => $state_id],
            "language_code" => "en"
        ];
        //connect API to get categories list
        $response = $this->connectAPI($data);
        
        return  $response['data']['status'] == 'success' ?  $response['data']['result'] : [];
    }

    public function getOrderAddress($products_data, $delivery_data){
         $data = [
            "method_name" => "getOrderAddress",
            "data" => [
                "delivery_address_line_1" => $delivery_data['delivery_address_line_1'],
                "delivery_address_line_2" => $delivery_data['delivery_address_line_2'],
                "delivery_city"           => $delivery_data['delivery_city'],
                "delivery_country"        => $delivery_data['delivery_country'],
                "delivery_country_code"   => $delivery_data['delivery_country_code'],
                "delivery_latitude"       => $delivery_data['delivery_latitude'],
                "delivery_location"       => $delivery_data['delivery_location'],
                "delivery_longitude"      => $delivery_data['delivery_longitude'],
                "delivery_mobile_number"  => $delivery_data['delivery_mobile_number'],
                "delivery_phone"          => $delivery_data['delivery_phone'],
                "delivery_state"          => $delivery_data['delivery_state'],
                "delivery_zip_code"       => $delivery_data['delivery_zip_code'],
                "product_detail"          => $products_data['products']
            ],
            "language_code" => "en"
        ];
        //connect API to get categories list
        $response = $this->connectAPI($data);
        return  $response['data']['status'] == 'success' ?  $response['data']['result'] : [];   
    }
 
}
