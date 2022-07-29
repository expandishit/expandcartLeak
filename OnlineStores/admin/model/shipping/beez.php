<?php
class ModelShippingBeez extends Model {

    public function install(){
		$this->load->model('setting/setting');
		$this->model_setting_setting->insertUpdateSetting( 'beez', ['beez' => ['callback' => HTTP_CATALOG . 'index.php?route=shipping/beez/callback'] ]);

   		//Add statuses
   		$this->model_setting_setting->insertUpdateSetting( 'beez', [ 'beez' => ['statuses' => 
   			[
				'IT' => [
					'name' => ['en' => 'In Transit' , 'ar' => 'In Transit'],
					'expandcartid' => ''
				],
				'AG' => [
					'name' => ['en' => 'Mobile Number not answering' , 'ar' => 'Mobile Number not answering'],
					'expandcartid' => ''
				],
				'AG1' => [
					'name' => ['en' => 'Wrong/Incomplete mobile number' , 'ar' => 'Wrong/Incomplete mobile number'],
					'expandcartid' => ''
				],
				'K1' => [
					'name' => ['en' => 'Customer Refused to Pay' , 'ar' => 'Customer Refused to Pay'],
					'expandcartid' => ''
				],
				'DD' => [
					'name' => ['en' => 'DeliveryDone' , 'ar' => 'DeliveryDone'],
					'expandcartid' => ''
				],
				'DQ' => [
					'name' => ['en' => 'Remote Area' , 'ar' => 'Remote Area'],
					'expandcartid' => ''
				],
				'S7' => [
					'name' => ['en' => 'Shipment Hold for customer Pickup' , 'ar' => 'Shipment Hold for customer Pickup'],
					'expandcartid' => ''
				],
				'AC' => [
					'name' => ['en' => 'Incorrect City' , 'ar' => 'Incorrect City'],
					'expandcartid' => ''
				],																				
				'S1' => [
					'name' => ['en' => 'No money with customer for COD in 1st Attempt' , 'ar' => 'No money with customer for COD in 1st Attempt'],
					'expandcartid' => ''
				],	
				'KZ' => [
					'name' => ['en' => 'No money with customer for COD in 2nd Attempt' , 'ar' => 'No money with customer for COD in 2nd Attempt'],
					'expandcartid' => ''
				],	
				'MF' => [
					'name' => ['en' => 'Customer requested for future delivery' , 'ar' => 'Customer requested for future delivery'],
					'expandcartid' => ''
				],	
				'S46' => [
					'name' => ['en' => 'Out of Service Area' , 'ar' => 'Out of Service Area'],
					'expandcartid' => ''
				],	
				'NA' => [
					'name' => ['en' => 'Phone off' , 'ar' => 'Phone off'],
					'expandcartid' => ''
				],	
				'PT' => [
					'name' => ['en' => 'Postponed' , 'ar' => 'Postponed'],
					'expandcartid' => ''
				],	
				'OU' => [
					'name' => ['en' => 'Order Completed' , 'ar' => 'Order Completed'],
					'expandcartid' => ''
				],	
				'IA' => [
					'name' => ['en' => 'Incomplete Address' , 'ar' => 'Incomplete Address'],
					'expandcartid' => ''
				],
				'WA' => [
					'name' => ['en' => 'Wrong item' , 'ar' => 'Wrong item'],
					'expandcartid' => ''
				],	
				'OC' => [
					'name' => ['en' => 'Order Created' , 'ar' => 'Order Created'],
					'expandcartid' => ''
				],
				'OCANCEL' => [
					'name' => ['en' => 'Order Cancelled' , 'ar' => 'Order Cancelled'],
					'expandcartid' => ''
				],	
				'OWDAT' => [
					'name' => ['en' => 'Wrong data' , 'ar' => 'Wrong data'],
					'expandcartid' => ''
				],
				'UNK' => [
					'name' => ['en' => 'Unknown' , 'ar' => 'Unknown'],
					'expandcartid' => ''
				],	
				'OR' => [
					'name' => ['en' => 'Return' , 'ar' => 'Return'],
					'expandcartid' => ''
				],
				'ONH' => [
					'name' => ['en' => 'Onhold' , 'ar' => 'Onhold'],
					'expandcartid' => ''
				],
				'WO' => [
					'name' => ['en' => 'Wrong Order' , 'ar' => 'Wrong Order'],
					'expandcartid' => ''
				],	
				'CUNA' => [
					'name' => ['en' => 'Customer Unavailable' , 'ar' => 'Customer Unavailable'],
					'expandcartid' => ''
				],
				'OU' => [
					'name' => ['en' => 'Order Updated' , 'ar' => 'Order Updated'],
					'expandcartid' => ''
				],
				'COCN' => [
					'name' => ['en' => 'Collected by Consigned' , 'ar' => 'Collected by Consigned'],
					'expandcartid' => ''
				],	
				'INP' => [
					'name' => ['en' => 'INCOMPLETE_PARCEL' , 'ar' => 'INCOMPLETE_PARCEL'],
					'expandcartid' => ''
				],	
				'COAS' => [
					'name' => ['en' => 'Customer Ordered another Shipment' , 'ar' => 'Customer Ordered another Shipment'],
					'expandcartid' => ''
				],
				'WADDR' => [
					'name' => ['en' => 'Wrong Address' , 'ar' => 'Wrong Address'],
					'expandcartid' => ''
				],
				'OTHLOC' => [
					'name' => ['en' => 'Other Location' , 'ar' => 'Other Location'],
					'expandcartid' => ''
				],	
				'CACUS' => [
					'name' => ['en' => 'Cancel by Customer' , 'ar' => 'Cancel by Customer'],
					'expandcartid' => ''
				],	
				'ADDCGREQ' => [
					'name' => ['en' => 'RECIPIENT ADDRESS CHANGE REQUESTED' , 'ar' => 'RECIPIENT ADDRESS CHANGE REQUESTED'],
					'expandcartid' => ''
				],	
				'G3' => [
					'name' => ['en' => 'Customer location closed on 1st delivery Attempt' , 'ar' => 'Customer location closed on 1st delivery Attempt'],
					'expandcartid' => ''
				],												
   			]
   		]]);

   	    //Add Tracking column in Order table if it doesn't exist
		if( !$this->db->check(['order' => ['tracking']], 'column') ){
			$this->db->query("ALTER TABLE `order` ADD COLUMN `tracking`  varchar(3000) NULL");
		}

    }

    public function uninstall(){}

  	/**
  	* [POST]Create new shipment Order.
  	*
    * @param Array   $order data to be shipped.
  	*
  	* @return Response Object contains newly created order details
  	*/
    public function createShipment($order){
    	$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://beezlspwebapi.azurewebsites.net/api/Orders/PostOrder',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS => json_encode($order),
		  CURLOPT_HTTPHEADER => array(
		    'Content-Type: application/json'
		  ),
		));

		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      	$err      = curl_error($curl);

		curl_close($curl);
		// echo $response;

		return $err ? ['error' => $err] : ['status_code' => $httpcode, 'result' => json_decode($response, true)];
    }

}

