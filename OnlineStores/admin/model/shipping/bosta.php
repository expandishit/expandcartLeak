<?php
class ModelShippingBosta extends Model {

  	/**
  	 * @const strings API URLs.
  	 */
    const BASE_API_TESTING_URL    = 'https://staging-api.bosta.co/api/v0/';
    const BASE_API_PRODUCTION_URL = 'https://api.bosta.co/api/v0/';

    public function install(){
		  $this->load->model('setting/setting');

		  $this->model_setting_setting->insertUpdateSetting( 'bosta', ['bosta_callback' => HTTP_CATALOG . 'index.php?route=shipping/bosta/callback' ]);

   		$this->model_setting_setting->insertUpdateSetting( 'bosta', ['bosta_cities' =>
   			[
   				['code'=>'EG-01' , 'zone'=>['en'=>'Cairo & Giza' , 'ar'=>'القاهره و الجيزه']],
   				['code'=>'EG-02' , 'zone'=>['en'=>'Alexandria' , 'ar'=>'الاسكندريه']],
   				['code'=>'EG-03' , 'zone'=>['en'=>'Sahel' , 'ar'=>'الساحل']],
   				['code'=>'EG-04' , 'zone'=>['en'=>'Behira' , 'ar'=>'البحيره']],
   				['code'=>'EG-05' , 'zone'=>['en'=>'Dakahlia' , 'ar'=>'الدقهليه']],
   				['code'=>'EG-06' , 'zone'=>['en'=>'El Kalioubia' , 'ar'=>'القليوبيه']],
   				['code'=>'EG-07' , 'zone'=>['en'=>'Gharbia' , 'ar'=>'الغربيه']],
   				['code'=>'EG-08' , 'zone'=>['en'=>'Kafr Alsheikh' , 'ar'=>'كفر الشيخ']],
   				['code'=>'EG-09' , 'zone'=>['en'=>'Monufia' , 'ar'=>'المنوفيه']],
   				['code'=>'EG-10' , 'zone'=>['en'=>'Sharqia' , 'ar'=>'الشرقيه']],
   				['code'=>'EG-11' , 'zone'=>['en'=>'Isamilia' , 'ar'=>'الاسماعيليه']],
   				['code'=>'EG-12' , 'zone'=>['en'=>'Suez' , 'ar'=>'السويس']],
   				['code'=>'EG-13' , 'zone'=>['en'=>'Port Said' , 'ar'=>'بورسعيد']],
   				['code'=>'EG-14' , 'zone'=>['en'=>'Damietta' , 'ar'=>'دمياط']],
   				['code'=>'EG-15' , 'zone'=>['en'=>'Fayoum' , 'ar'=>'الفيوم']],
   				['code'=>'EG-16' , 'zone'=>['en'=>'Bani Suif' , 'ar'=>'بني سويف']],
   				['code'=>'EG-17' , 'zone'=>['en'=>'Asyut' , 'ar'=>'اسيوط']],
   				['code'=>'EG-18' , 'zone'=>['en'=>'Sohag' , 'ar'=>'سوهاج']],
   				['code'=>'EG-19' , 'zone'=>['en'=>'Menya' , 'ar'=>'المنيا']],
   				['code'=>'EG-20' , 'zone'=>['en'=>'Qena' , 'ar'=>'قنا']],
   				['code'=>'EG-21' , 'zone'=>['en'=>'Aswan' , 'ar'=>'اسوان']],
   				['code'=>'EG-22' , 'zone'=>['en'=>'Luxor' , 'ar'=>'الاقصر']],
                ['code'=>'EG-23' , 'zone'=>['en'=>'Red Sea' , 'ar'=>'البحر الأحمر']],
            ]
   		]);


   		//Add statuses
   		$this->model_setting_setting->insertUpdateSetting( 'bosta', [ 'bosta_statuses' => [
  				'10' => [
  					'name' => ['en' => 'pending' , 'ar' => 'معلق'],
  					'expandcartid' => ''
  				],
  				'15' => [
  					'name' => ['en' => 'in progress' , 'ar' => 'جاري التجهيز'],
  					'expandcartid' => ''
  				],
          '16' => [
            'name' => ['en' => 'Delivery on route' , 'ar' => 'عامل التوصيل في الطريق'],
            'expandcartid' => ''
          ],
          '20' => [
            'name' => ['en' => 'Picking up' , 'ar' => 'جاري التسلم'],
            'expandcartid' => ''
          ],
          '21' => [
            'name' => ['en' => 'Picking up from warehouse' , 'ar' => 'جاري الاستلام من المخزن'],
            'expandcartid' => ''
          ],
          '22' => [
            'name' => ['en' => 'Arrived at warehouse' , 'ar' => 'وصلت المخزن'],
            'expandcartid' => ''
          ],
          '23' => [
            'name' => ['en' => 'Received at warehouse' , 'ar' => 'تم الاستلام في المخرن'],
            'expandcartid' => ''
          ],
          '25' => [
            'name' => ['en' => 'Arrived at business' , 'ar' => 'تم الوصول الي البزنس'],
            'expandcartid' => ''
          ],
          '26' => [
            'name' => ['en' => 'Receiving' , 'ar' => 'جاري الاستلام'],
            'expandcartid' => ''
          ],
          '30' => [
            'name' => ['en' => 'Picked up' , 'ar' => 'تم التسلم'],
            'expandcartid' => ''
          ],
          '35' => [
            'name' => ['en' => 'Delivering' , 'ar' => 'جاري التوصيل'],
            'expandcartid' => ''
          ],
          '36' => [
            'name' => ['en' => 'En route to warehouse' , 'ar' => 'في الطريق الي المخزن'],
            'expandcartid' => ''
          ],
          '40' => [
            'name' => ['en' => 'Arrived at customer' , 'ar' => 'تم الوصول للعميل '],
            'expandcartid' => ''
          ],
          '45' => [
            'name' => ['en' => 'Delivered' , 'ar' => 'تم التسليم'],
            'expandcartid' => ''
          ],
          '50' => [
            'name' => ['en' => 'Canceled' , 'ar' => 'مغلي'],
            'expandcartid' => ''
          ],
          '55' => [
            'name' => ['en' => 'Failed' , 'ar' => 'فشل التسليم'],
            'expandcartid' => ''
          ],
          '80' => [
            'name' => ['en' => 'Pickup Failed' , 'ar' => 'فشل تسلم الشحنه'],
            'expandcartid' => ''
          ],
          '95' => [
            'name' => ['en' => 'Terminated' , 'ar' => 'منهي'],
            'expandcartid' => ''
          ],
   			]
   		]);

   		//Add Tracking column in Order table if it doesn't exist
  		$query = $this->db->query("SELECT COUNT(*) colcount
  		                          FROM INFORMATION_SCHEMA.COLUMNS
  		                          WHERE  table_name = 'order'
  		                          AND table_schema = DATABASE()
  		                          AND column_name = 'tracking'");
  		$result = $query->row;
  		$colcount = $result['colcount'];

  		if($colcount <=0 ) {
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

    	$api_key = $this->config->get('bosta_api_key');
    	$url     = $this->_getBaseUrl() . 'deliveries';

    	// Initializes a new cURL session
  		$curl = curl_init($url);

  		// Set the CURLOPT_RETURNTRANSFER option to true to return response in a variable
  		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

  		// Set the CURLOPT_RETURNTRANSFER option to true to return http header in response
  		curl_setopt($ch, CURLOPT_HEADER, true);

  		// Set the CURLOPT_POST option to true for POST request
  		curl_setopt($curl, CURLOPT_POST, true);

  		// Set the request data as JSON using json_encode function
  		curl_setopt($curl, CURLOPT_POSTFIELDS,  json_encode($order));

  		// Set custom headers for RapidAPI Auth and Content-Type header
  		curl_setopt($curl, CURLOPT_HTTPHEADER, [
  		  'Content-Type: application/json',
  		  "Authorization: $api_key"
  		]);

  		// Execute cURL request with all previous settings
  		$response = curl_exec($curl);
  		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

  		// Close cURL session
  		curl_close($curl);

  		return [
  			'status_code' => $httpcode,
  			'result'      => json_decode($response)
  		];
    }

    private function _getBaseUrl(){

    	$base_url = "";

    	//Check current API Mode..
    	$mode = $this->config->get('bosta_live_mode');

    	if( isset($mode) && $mode == 1 ){
    		$base_url = ModelShippingBosta::BASE_API_PRODUCTION_URL;
    	}
    	else{
    		$base_url = ModelShippingBosta::BASE_API_TESTING_URL;
    	}

    	return $base_url;
    }
}
