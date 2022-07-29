<?php

class ModelModuleLableb extends Model
{
	protected $logger ;
    private $token 			= false;
    private $hostUrl 		= "https://api.lableb.com/";
	private $module_name 	= 'lableb';
	private static $codes 	= [
								'NOT_AUTHORIZED' => 401 , 
								'RECORD_EXISTS'  => 409 , 
								'SUCCESS'		 => 200
							  ];
							  
	private static $INDEXED_PRODUCT_TABLE_NAME = 'lableb_indexed_products';
	
	private $status, $project_name, $api_key , $email, $password;
	
	public function __construct ($registry){
		parent::__construct($registry);
		$this->logger =  new \Log('lableb');
		$setting_data 		=  $this->config->get($this->module_name);
		$this->status 		=  $setting_data['status'] ?? 0;
		$this->project_name =  $setting_data['project'] ?? "";
		$this->api_key 		=  $setting_data['index_apikey'] ?? '';
		$this->email 		=  $setting_data['email'] ?? '';
		$this->password 	=  $setting_data['password'] ?? '';
		
		//for already old installed users  | should be removed after a while 
		if(!isset( $setting_data['indexed_product_table_created'])){
			
			$this->createIndexedProductTable();
			$this->load->model('setting/setting');
			$setting_data['indexed_product_table_created'] = 1;
			$this->model_setting_setting->editSetting($this->module_name, [$this->module_name => $setting_data]);
		}
		//#for already old installed users 
	}
	
	public function register($data){
				
        $response = $this->_sendRequest([
									'type' => 'POST',
									'url'  => $this->hostUrl . "v2/auth/register", 
									'data' => $data 
									]);
									
		return json_decode($response);
    }

	public function createNewProject($project_name,$login_data=[]){
		
		$token 			= $this->_token($login_data);
		
		$response 		= $this->_sendRequest([
									'type'	  => 'POST',
									'url'     => $this->hostUrl."v2/projects" , 
									'headers' => ['Authorization: Bearer '.$token],
									'data'    => [
												  'name'	  => $project_name, 
												  'archetype' => "expandcart", 
												  'plan_id'	  => 6 ,
                                                  'is_free_trial' =>true
												]
									]);
									
		return json_decode($response);
    }
	
	public function projectApiKeys($project_name='',$login_data=[]){ 
		
		$token 		= $this->_token($login_data);
		$headers	= ['Authorization: Bearer '.$token];		
        $response   = $this->_sendRequest([
						'type'	  => 'GET',
						'url'     => $this->hostUrl."v2/projects/".$project_name .'/apikeys' , 
						'headers' => $headers
					]);
		
        return json_decode($response);
    }

    public function newProjectApiKey($project_name='',$login_data=[]){
		$project_name = $project_name?: $this->project_name;
		$token 		  = $this->_token($login_data);	
        $response     = $this->_sendRequest([
							'type'	  => 'POST',
							'url'     => $this->hostUrl."v2/projects/". $project_name ."/apikeys", 
							'headers' => ['Authorization: Bearer '.$token],
							'data'    => [
									'name' 			=> strtolower("expand_token_" . $project_name),
									'is_indexing'	=> true,
									'is_search'		=> true,
									'is_admin'		=> true,
									'is_active'		=> true
								]
							]);
		
		return json_decode($response);		
    }
	
	public function indexProducts($all_products,$project_name='',$api_key=''){

        $data 			= array_chunk($all_products, 50);
		$new_data 			= [];
		$new_data_insert 	= [];
		
		$project_name 	= $project_name?: $this->project_name;
		$api_key 		= $api_key?:  $this->api_key;
		
        foreach($data as $k=>$products_array){
			$new_products_arrays = [];
			$new_data_arrays 	 = [];
            foreach($products_array as $key => $product){
                $new_date = str_replace('+00:00', 'Z', gmdate('c', strtotime($product['date_available'])));
                $url = $this->fronturl->link('index.php?route=product/product&product_id=' . $product['product_id'], '', 'SSL')->format();
			    $new_product['id'] 				= $product['product_id'].'-'.$product['language_code'];
                $new_product['title'] 			= $product['name'];
                $new_product['product_id']  	= $product['product_id'];
                $new_product['description'] 	= !empty($product['description'])?$product['description'] :'no_desc_available';
                $new_product['date_available']  = $new_date;
                $new_product['price'] 			= (int)$product['price'];
                $new_product['status'] 			= $product['stock_status']; //why this data sent 
				$new_product['availability'] 	= $product['status'];  //new index to filter the search result with it 
                $new_product['quantity'] 		= $product['quantity']; //why this data sent 
                $new_product['language'] 		= $product['language_code'];
                $new_product['url'] 			= $url;
                $new_product['image'] 			= $product['image'];
      //          $new_product['image_swap'] 		= $product['image_swap'];
                $new_product['category']		= explode(",",  $product['categories_names']);
                $new_products_arrays[]			= $new_product;
				$new_index['lableb_id']			= $product['product_id'].'-'.$product['language_code'];
				$new_index['product_id']		= $product['product_id'];
				$new_index['language_id']		= $product['language_id'];
			   $new_data_arrays[]				= $new_index;
            }
            $new_data[$k] = $new_products_arrays; //final array with needed elements only
            $new_data_insert[$k] = $new_data_arrays; //array for our mapping table 
        }
		
        $request = [
					'type'	  => 'POST',
		            'url'     => $this->hostUrl."v2/projects/".$project_name."/indices/products/documents?apikey=".$api_key
					];
		
		$total_failed   = 0;
		$total_records  = count($new_data);
		
        foreach ($new_data as $key => $products_array){
			
			
			$request['data'] = $products_array;
			$response 		 = $this->_sendRequest($request);
			$response        = json_decode($response);
			if($response->code !=  self::$codes['SUCCESS']){
			  $total_failed += count($request['data']);
			}else {
			  $this->saveIndexedProducts($new_data_insert[$key]);
			}
        }
		
		return [
				'status' 		=> $total_failed == 0,
				'total_records' => $total_records,
				'total_failed'  => $total_failed 
			];
    }
	
	public function truncateProject($project_name='',$api_key=''){
		$project_name 	= $project_name?: $this->project_name;
		$api_key 		= $api_key?:  $this->api_key;
		
        $response 	= $this->_sendRequest([
						'type'	  => 'DELETE',
						'url'     => $this->hostUrl."v2/projects/". $project_name ."/indices/products/truncate?apikey=".$api_key
					]);
		
		$response	= json_decode($response);
		
		if($response->code == self::$codes['SUCCESS']){
			$this->_truncateIndexedProducts();
		}
		
		return $response;		
    }
	
	public function deleteIndexes($product_id){
		
		$project_name = $this->project_name;
		$api_key 	  = $this->api_key;

		$this-> _deleteIndexedProduct($product_id);
		
		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguages();
		$responses = [];
		foreach ($languages as $language ){
			
			$document_id = $product_id . '-' .$language['code']; 
			$response  = $this->_sendRequest([
							'type'	  => 'DELETE',
							'url'     => $this->hostUrl."v2/projects/". $project_name."/indices/products/documents/".$document_id."?apikey=".$api_key
						]);
			$responses[] = json_decode($response);
		}
		return $responses;
    }

	private function _token($data=[]){
			
		if(!$this->token){
			
			$email 		= $data['email']?? $this->email;
			$password 	= $data['password']?? $this->password;
		
			$response  = $this->_sendRequest([
											 'url'  => $this->hostUrl."v2/auth/login", 
											 'data' => [
													  'email' 	 => $email,
													  'password' => $password
													]
											 ]);
			$response = json_decode($response);
			if($response->code ==  self::$codes['SUCCESS']){
				$this->token = $response->response->token;
			}
		}
		
		return $this->token;
		
    }
	
	private function _sendRequest($request){
		
		$request_url     = $request['url']??"";
		$type 	 		 = $request['type']??"POST";
		$headers 		 = $request['headers']??[];
		$request_data 	 = $request['data']??[];

		$soap_do     = curl_init();
        curl_setopt($soap_do, CURLOPT_URL, $request_url);
        curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt($soap_do, CURLOPT_TIMEOUT, 120);
        curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($soap_do, CURLOPT_ENCODING, true);
        curl_setopt($soap_do, CURLOPT_CUSTOMREQUEST, $type);

		$headers[]		= "Content-Type: application/json";
		
		if(
			in_array(strtoupper($type),['POST','PUT','PATCH','DELETE'])
			&& !empty($request_data)
		){
			$request_data 	= json_encode($request_data);
			$length 	  	= strlen($request_data);
			$headers[]		= "Content-Length: $length";
			curl_setopt($soap_do, CURLOPT_POSTFIELDS, $request_data);
		}

		curl_setopt($soap_do, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($soap_do);

		//log here controlled by config 
		$this->_requestLog($request,$response);
		
		return $response;
	}
	
	private function _requestLog($request,$response){
		if(!defined('LOG_MODULE_LABLEB')){
			//at current time the default will be enabling it till the stability of the APP 
			define('LOG_MODULE_LABLEB',True); 
		}
		
		//control the enable and disable of logging via server config 
		if(LOG_MODULE_LABLEB){
			$log_text = "Request : ". json_encode($request);
			$log_text .= "\n => Response : ". $response;
			$this->logger->write($log_text);
		}
	}
	
	//------------ DB --------------//
	public function createIndexedProductTable(){
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . self::$INDEXED_PRODUCT_TABLE_NAME."` (
			`index_id` int(11) NOT NULL AUTO_INCREMENT,
			`lableb_id` varchar(100) NOT NULL,
			`product_id` int(11) NOT NULL,
			`language_id` int(11) NOT NULL,
			`date_added` datetime NOT NULL DEFAULT current_timestamp(),
			PRIMARY KEY (`index_id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
		
	}
	
	public function dropIndexedProductTable(){
		$this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX . self::$INDEXED_PRODUCT_TABLE_NAME. "`");
	}
	
	public function saveIndexedProducts($indexes){
		
		$query  = 'INSERT INTO `'. DB_PREFIX .self::$INDEXED_PRODUCT_TABLE_NAME .'` (' ;
		$query .= $this->db->escape(implode(' , ',array_keys( $indexes[0]))) . ') ';
		$query .= " VALUES ";
		
		$values = '';
		foreach ($indexes as $key => $index){
			if($key != 0 ){
				$values .=  ",( '".implode("' , '", $index)."' )";
			}else {
				$values .=  "( '".implode("' , '", $index)."' )";
			}
		}
		
		$query .= $values ; 
		
		$this->db->query($query);
	}

	private function _deleteIndexedProduct($product_id){
		$this->db->query("DELETE FROM `" . DB_PREFIX . self::$INDEXED_PRODUCT_TABLE_NAME."` WHERE product_id = " . (int)$product_id);
	}
	
	public function indexedProductsCounts(){
		
		$sql = "SELECT  count(DISTINCT ip.lableb_id) as total , l.code as language_code  from `".DB_PREFIX . self::$INDEXED_PRODUCT_TABLE_NAME."` ip
				  LEFT JOIN " . DB_PREFIX . "language l ON  (ip.language_id = l.language_id)   Group by ip.language_id";
				  
		$query = $this->db->query($sql);
		
		return $query->rows;		
	}

	private function _truncateIndexedProducts(){
		$this->db->query("TRUNCATE TABLE  `" . DB_PREFIX . self::$INDEXED_PRODUCT_TABLE_NAME."`;");
	}


    /**
     * @param string $projectName
     * @param array $loginData
     * @return false|null|mixed
     */
    public function getLablebProjectPlanUsage($projectName = "", $loginData = [])
    {
        $result = false;
        if (!empty($projectName)) {
            $token = $this->_token($loginData);
            $headers = ['Authorization: Bearer ' . $token];
            $response = $this->_sendRequest([
                'type' => 'GET',
                'url' => $this->hostUrl . "v2/projects/{$projectName}/subscriptions/current/usage",
                'headers' => $headers
            ]);

            $result = json_decode($response);

        }
        return $result;
    }


}
