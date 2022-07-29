<?php

use Facebook\Facebook;
use Facebook\Exceptions\FacebookSDKException;

class ModelModuleFacebookBusiness extends Model
{
	
	private static $MODULE_NAME  	 	  = 'facebook_business';
	private static $BASE_URL 	 	 	  = 'https://graph.facebook.com';
	private static $API_VERSION  	 	  = 'v11.0';
	
	//tables 
	private static $PRODUCTS_TABLE  	   = 'facebook_business_products';
	private static $PUSHED_PRODUCTS_TABLE  = 'facebook_business_pushed_products';
	private static $BATCHES_TABLE  	  	   = 'facebook_business_batches';
	private static $JOBS_TABLE 		  	   = 'facebook_business_catalog_jobs';
	
	//ectools table 
	private static $ECTOOLS_JOBS_TABLE 	   = 'stores_facebook_batches';
	
    private $facebook_app_id 		= '329928231042768';
    private $facebook_secret_key 	= '89b8ba250426527ac48bafeead4bb19c';
    private $access_token 			= '';
    private $pixel_id 				= '';
    private $business_manager_id 	= '';
    private $page_id 				= '';
    private $catalog_id 			= '';
    private $app_user_id 			= '';
    private $system_user_token 		= '';
    private $system_user_id 		= '';
	private $ad_account_id 			= '';
	private $isPixelsInstalled 		= '';
    private $isFbInstalled	 		= '';
	
	protected $logger ;
	protected $fb ;
	
	protected static $fb_locales = [
									'ar' => 'ar_AR' ,   //1-Arabic
									'en' => 'en_XX' ,   //2-English
									'fr' => 'fr_XX' ,	//3-French
									'es' => 'es_XX' ,   //4-Spanish
									'ja' => 'ja_XX' ,   //5-Japanese
									'ru' => 'ru_RU' ,   //6-Russian
									'tr' => 'tr_TR' ,   //7-Turkish
									'de' => 'de_DE' ,   //8-German
									'it' => 'it_IT' ,	//9-italian
									'ku' => 'ku_TR' ,   //10-kurdish
									'hi' => 'hi_IN' 	//11-hindi	
									];
	
	protected static $fb_default_locale = 'en_XX';
	
	protected static $notification_exportall_code = 'facebook_business_export_all_finished';
	//
	public function __construct($registry){
		parent::__construct($registry);
		
		$this->load->model('setting/setting');
		$fbe_settings = $this->model_setting_setting->getSetting(self::$MODULE_NAME);
		
		$this->system_user_token 	= $fbe_settings["system_user_token"]		  ?? '';		
		$this->system_user_id		= $fbe_settings["system_user_id"]			  ?? '';		
		$this->access_token 		= $fbe_settings['fbe_access_token']			  ?? '';		
		$this->business_manager_id 	= $fbe_settings['business_manager_id']		  ?? false;
		$this->catalog_id 			= $fbe_settings["catalog_id"]				  ?? false;
		$this->external_business_id = $fbe_settings['external_business_id']	  	  ?? "";
		$this->ad_account_id 		= $fbe_settings['ad_account_id']			  ?? false;
		$this->app_user_id 			= $fbe_settings['app_user_id']				  ?? false;
		$this->isPixelsInstalled    = isset($fbe_settings["isPixelsInstalled"])  ? 1 : 0;
		$this->isFbInstalled 		= isset($fbe_settings["isFacebookInstalled"])? 1 : 0;
		
		$this->fb = new Facebook([
            'app_id' 	 => $this->facebook_app_id,
            'app_secret' => $this->facebook_secret_key
        ]);
		
		$this->logger =  new \Log(self::$MODULE_NAME);
		
		
	}
	
	
	//--------- DB -----------------//
	
	/**
	 * Get Facebook products from temp table by Facebook product id
	 *
	 * @param string|array $product_ids Comma separated values of products
	 * @param boolean $already_imported Include the previously imported products
	 * @return array|void Product List
	 */
	public function getProductsByIds($product_ids, $already_imported = true){
		
		//validate ids
		if (is_array($product_ids)) {
			$product_ids = implode(',', $product_ids);
		}

		//validate second param
		$already_imported = is_bool($already_imported) ? $already_imported : false;

		//Get all non existed products in the temp table
		$sql = '
			SELECT * FROM `' . DB_PREFIX  . self::$PRODUCTS_TABLE . '` where facebook_product_id in (' . $product_ids . ')
		';
		
		if ($already_imported) {
			$sql .= ' AND imported = 0';
		}

		$products_in_db = $this->db->query($sql);

		return $products_in_db->rows;
	}

	/**
	 * Create or update a product based on the Facebook product id
	 *
	 * @param object|null $product
	 * @return int ExpandCart product id
	 */
	public function createOrUpdate($product = [], $manufacturer_id = 0, $image_name = null){
		
		//Select product from intermediary table
		$facebook_product_id = (int) $this->db->escape($product->id);
		$facebook_product_sql = 'SELECT * FROM ' . DB_PREFIX . self::$PRODUCTS_TABLE . ' WHERE facebook_product_id=' . $facebook_product_id . ' LIMIT 1';
		$facebook_product = $this->db->query($facebook_product_sql)->row;
        $check_product_sql = 'SELECT * FROM `' . DB_PREFIX . 'product` WHERE product_id=' . ($facebook_product['expand_product_id'] ?? 0) . ' LIMIT 1';
        $product_exist = $this->db->query($check_product_sql)->num_rows > 0 ? true : false;

        if ($facebook_product['expand_product_id'] && $product_exist) {
			//Update product
			return $this->updateProductInDB($facebook_product['expand_product_id'], $product, $manufacturer_id, $image_name);
        } else {
            //Create product
            return $this->insertProductToDB($product, $manufacturer_id);
        }
    }

    /**
     * Insert one product to the database
     *
     * @param null $product The product object
     * @param int $manufacturer_id Brand id
     * @return int Inserted product id
     */
	public function insertProductToDB($product = null, $manufacturer_id = 0){
		//Save image to file system
		//Insert new image

		

        $extension = pathinfo(substr($product->image_url, strrpos($product->image_url, '/') + 1), PATHINFO_EXTENSION);
        if (strlen($extension) > 4) {
            $extension = image_type_to_extension(getimagesize($product->image_url)[2]);
        }
        $image_name = uniqid() . '.' .$extension;
        $this->grab_image($product->image_url, 'image/data/facebook_products/' . $image_name);
        if(isset($product->custom_label_2) && !empty($product->custom_label_2)){
            $model = $product->custom_label_2;
        }else{
            $model = $product->retailer_id;
        }
        if(isset($product->custom_label_3) && !empty($product->custom_label_3)){
            $sku = $product->custom_label_3;
        }else{
            $sku =  $product->retailer_id;
        }
		//SQL to insert product

        $columns = [
              'price'
            , 'manufacturer_id'
            , 'image'
            , 'model'
            , 'sku'
            , 'stock_status_id'
            , 'date_available'
            , 'weight_class_id'
            , 'length_class_id'
            , 'sort_order'
            , 'status'
            , 'date_modified'
        ];
        $values = [
            $this->convertPriceInEnglish($product->price),
            $manufacturer_id,
            "data/facebook_products/$image_name" ,
            $model,
            $sku,
            1,
            date('Y-m-d H:i:s'),
            0,
            0,
            1,
            1,
            date('Y-m-d H:i:s')
        ];
            
		$columns[]='quantity';
        $values[]=(int)$product->inventory;

        $columnsStr = implode(",", $columns);
        $valuesStr = "'" . implode("','", $values) . "'";
        $sql = ("INSERT INTO " . DB_PREFIX . "product ($columnsStr)  VALUES ($valuesStr)");
		$this->db->query($sql);
		$inserted_product_id = $this->db->getLastId();
		
		//insert product description
		$langs = $this->db->query('SELECT language_id from ' . DB_PREFIX . 'language')->rows;

		foreach ($langs as $language) {
			$sql2 = '
				INSERT INTO ' . DB_PREFIX . 'product_description (
					product_id,
					language_id,
					name,
					description
				) VALUES (
					' . $inserted_product_id . ',
					' . $language['language_id'] . ',
					"' . $this->db->escape($product->name) . '",
					"' . $this->db->escape($product->description) . '"
				);
			';

			$this->db->query($sql2);
		}

		//After inserting new product
		//Update it in the facebook products table
		$sql_u = '
			UPDATE ' . self::$PRODUCTS_TABLE . ' 
				SET 
				expand_product_id=' . $inserted_product_id . ' 
			WHERE facebook_product_id=' . $product->id . ' 
			LIMIT 1;
		';
		$this->db->query($sql_u);

	

		//insert product to store
		$sql_x = 'INSERT INTO ' . DB_PREFIX . 'product_to_store VALUES (' . $inserted_product_id . ',0);';
		$this->db->query($sql_x);

		//If there is a sale price insert it
		$sale_price = (float) preg_replace('/[^0-9\.]/', '', $product->sale_price);
		if (!empty($sale_price) && $sale_price != 0.0) {
			$sql_y = '
				INSERT INTO ' . DB_PREFIX . 'product_special (
					product_id,
					customer_group_id,
					price
				) VALUES (
					' . $inserted_product_id . ',
					1,
					' . $sale_price . '
				)
			';
			$this->db->query($sql_y);
		}

		return $inserted_product_id;
	}

	/**
	 * Insert one product to the database
	 *
	 * @param int $expand_product_id The product id to update
	 * @param object $product The product object
	 * @param int $manufacturer_id Brand id
	 * @param string $image_name Image name in the filesystem
	 * @return int updated product id
	 */
	public function updateProductInDB($expand_product_id = 0, $product = null, $manufacturer_id = 0, $image_name = null){

        //Save image to file system
        if ($product->image_url && (!str_contains(STORECODE, $product->image_url) && \Filesystem::isExists($product->image_url))) {
            //Remove Image
            \Filesystem::deleteFile('image/data/facebook_products/' . $image_name);

            //Insert new image
            $extension = pathinfo(substr($product->image_url, strrpos($product->image_url, '/') + 1), PATHINFO_EXTENSION);
            if (strlen($extension) > 4) {
                $extension = image_type_to_extension(getimagesize($product->image_url)[2]);
            }
            $image_name = uniqid() . '.' . $extension;
            $this->grab_image($product->image_url, 'image/data/facebook_products/' . $image_name);
            $image_name = $this->db->escape("data/facebook_products/" . $image_name);
        }
        if(isset($product->custom_label_2) && !empty($product->custom_label_2)){
            $model = $product->custom_label_2;
        }else{
            $model = $product->retailer_id;
        }
        if(isset($product->custom_label_3) && !empty($product->custom_label_3)){
            $sku = $product->custom_label_3;
        }else{
            $sku =  $product->retailer_id;
        }

		$this->load->model('localisation/language');

		//SQL to update product
		$sql = '
			UPDATE ' . DB_PREFIX . 'product
			SET price="' . $this->db->escape($this->convertPriceInEnglish($product->price)) . '",
			manufacturer_id="' . $this->db->escape($manufacturer_id) . '",';
		if (!empty($image_name)) {
            $sql .='
			image="' . $image_name . '",';
        }
        $ignore_facebook_product_quntity= $this->config->get('ignore_facebook_product_quantity') ?? 0;
		$sql .='	model="' . $this->db->escape($model) . '",
			sku="' . $this->db->escape($sku) . '",
			stock_status_id="1",
			date_available=NOW(),
			weight_class_id="0",
			length_class_id="0",
			sort_order="1",
			status="1",
			date_modified=NOW() ';

		if(!$ignore_facebook_product_quntity)
		    $sql .=',quantity="' . $this->db->escape((int) $product->inventory) . '"';

		$sql .= ' WHERE product_id = ' . (int) $this->db->escape($expand_product_id) . ' LIMIT 1';


		$this->db->query($sql);

		//update product description
		//get language for operations

		$lang_code = $this->config->get('facebook_export_language') ? $this->config->get('facebook_export_language') : $this->config->get('config_admin_language');
		$language_id = $this->model_localisation_language->getLanguageByCode($lang_code)['language_id'];
        // custom_label_1 has the product language .
        if(isset($product->custom_label_1) &&  !empty(isset($product->custom_label_1))){
            $languageInfo = explode(":" , $product->custom_label_1);
            if ($languageInfo[1] && is_numeric($languageInfo[1]))
                $language_id = $languageInfo[1];
        }
		$sql2 = '
			UPDATE ' . DB_PREFIX . 'product_description
			SET 
			name="' . $this->db->escape($product->name) . '",
			description="' . $this->db->escape($product->description) . '"
			WHERE product_id=' . $this->db->escape($expand_product_id) . '
			AND language_id=' . $this->db->escape($language_id) . '
			LIMIT 1
		';

		$this->db->query($sql2);

		//If there is a sale price update it
		$sale_price = $this->convertPriceInEnglish($product->sale_price);

		if (!empty($sale_price) && $sale_price != 0.0) {
			$sql_y = '
			UPDATE ' . DB_PREFIX . 'product_special
			SET 
			product_id=' . $expand_product_id . ',
			customer_group_id=' . $this->config->get('config_customer_group_id') . ',
			price=' . $sale_price . '

			WHERE product_id=' . $expand_product_id . '
			LIMIT 1
			';


			$this->db->query($sql_y);
		}

		return $expand_product_id;
	}

	//
	public function getFbImportedProducs ($fb_ids =[]){
		$sql = 'select * from `' . DB_PREFIX  . self::$PRODUCTS_TABLE . '` where `imported` = 1';
		
		if(!empty($fb_ids)){
			$sql .= ' and `facebook_product_id` IN ('.implode(',', $fb_ids).')';
		}
		
		//return $sql; 
		$query = $this->db->query($sql);
		
		return $query->rows;		
		 
	}		
	
	/**
     * Get all products from Facebook catalog and insert them in the Temp table
     *
     * @param int $catalog_id The facebook catablog ID
     * @param int $limit The number of products to limit
     * @param string|null $previous_cursor The previous cursor to paginate with
     * @return void
     */
    public function fetchAllFacebookProductsToDB($catalog_id = null, $limit = 1000, $job_id = 0){
        
        try {
           
            //Get catalog info
            $catalog_gql = $catalog_id . '/?fields=product_count,business';


            //Send the request and get products
            $catalog = $this->fb->get($catalog_gql, $this->system_user_token)->getDecodedBody();
			
            $number_of_pages = ceil($catalog['product_count'] / $limit);

			
            $products_gql = $catalog_id . '/products?fields=description,custom_label_0,sale_price,retailer_id,id,name,currency,price,brand,category,availability,image_url,inventory,custom_label_1,custom_label_2,custom_label_3,product_catalog{product_count}&limit=' . $limit;

            //Set queue job status to processing
            $sql_update_q = 'update `' . DB_PREFIX  . self::$JOBS_TABLE . '` set status="processing",product_count="' . $catalog['product_count'] . '",updated_at=NOW() WHERE job_id=' . $job_id;

            $this->db->query($sql_update_q);

            for ($i=1; $i <= $number_of_pages; $i++) {
                $new_req = $products_gql;

                if ($next_cursor != null) {
                    $new_req .= '&after=' . $next_cursor;
                }

                //Send the request and get products
                $request = $this->decodedBodyToObject($this->fb->get($new_req, $this->system_user_token)->getDecodedBody());

                //Loop to insert into the DB
                foreach ($request->data as $key => $product) {
                    $expand_product_id = (isset($product->custom_label_0) && $product->custom_label_0 == 'expandcart') ? $product->retailer_id : "null";
                    $insert_sql = '
                        INSERT INTO ' . DB_PREFIX . self::$PRODUCTS_TABLE . '
                            (id, expand_product_id, retailer_id, facebook_business_id, facebook_catalog_id, facebook_product_id, payload, imported, created_at, updated_at) 
                            VALUES 
                           (
                                null,
                                 ' . $expand_product_id . ',
                                "' . $this->db->escape($product->retailer_id) . '",
                                null,
                                ' . $this->db->escape($product->product_catalog->id) . ',
                                ' . $this->db->escape($product->id) . ',
                                \'' . $this->db->escape(json_encode($product)) . '\',
								1,
                                NOW(),
                                NOW()
                            )
                        ON DUPLICATE KEY UPDATE 
                        payload=\'' . $this->db->escape(json_encode($product)) . '\',
                        facebook_product_id = ' . $this->db->escape($product->id) . ',
                        expand_product_id = ' . $expand_product_id. ',
						imported = 1,
                        updated_at=NOW()
                    ';

                    $this->db->query($insert_sql);

                    //get brand or insert
                    $b = $this->db->query('SELECT * FROM ' . DB_PREFIX . 'manufacturer WHERE name="' . $this->db->escape($product->brand) . '"');
                    if ($b->num_rows > 0) {
                        $manufacturer_id = $b->row['manufacturer_id'];
                    } else {
                        $s = 'INSERT INTO ' . DB_PREFIX  .'manufacturer (name) VALUES ("' . $this->db->escape($product->brand) . '");';
                        $this->db->query($s);
                        $manufacturer_id = $this->db->getLastId();

                        $s = 'INSERT INTO `' . DB_PREFIX  .'manufacturer_to_store` VALUES (' . $manufacturer_id . ',0);';
                        $this->db->query($s);
                    }


                    $inserted_product_id = $this->facebook_business->createOrUpdate($product, $manufacturer_id);
                }
				
                $next_cursor = $request->paging->cursors->after;

                sleep(1);
            }

            //Set queue job status to completed
            $sql_update_q = 'update `' . DB_PREFIX  . self::$JOBS_TABLE . '` set status="completed",finished_at=NOW(),updated_at=NOW() WHERE job_id=' . $job_id;

            $this->db->query($sql_update_q);
			
			
			//TO:DO | handle admin notification step 

        } catch (FacebookSDKException $e) {
            $this->logger->write("method " . __function__ . "[exception] : " .$e->getMessage());

            //Set queue job status to completed
            $sql_update_q = 'update `' . DB_PREFIX  . self::$JOBS_TABLE . '` set status="failed",updated_at=NOW(),payload="' . $e . '" WHERE job_id=' . $job_id;

            $this->db->query($sql_update_q);
        }
    }
	
	public function handleProductLocalizeForExport($products,$language='ar_AR'){
		

		$catalog_id = $catalog_id ?? $this->catalog_id;
		
        $sql_p = '';
        $data  = [];
        $data['allow_upsert'] 	= true;
		
		$counter = 0;
        foreach ($products as $key => $product) {
            $sql_p = 'SELECT * FROM ' . DB_PREFIX . self::$PRODUCTS_TABLE . ' WHERE expand_product_id=' . $product['product_id'] . ' LIMIT 1';
            $p = $this->db->query($sql_p)->row;

             if(count($p) > 0 ) {
                $retailer_id = $p["retailer_id"];
            } else {
                $retailer_id = $product['product_id'];
            }
			
			$description = substr(strip_tags(html_entity_decode($product['description'])), 0, 4999);
			
            $data['requests'][$counter]['method'] = 'UPDATE';
            $data['requests'][$counter]['localization'] = ['type' => 'LANGUAGE' , 'value' => $language];
            $data['requests'][$counter]['data']['id'] = $retailer_id;
            $data['requests'][$counter]['data']['title'] = $product['name'];
			$data['requests'][$counter]['data']['description'] = $description;	
			$counter ++;
        }
		
		return $data ;
	}
	
	private function _handleProductForExport($products){
		
		//Load the models
        $this->load->model('catalog/manufacturer');
        $this->load->model('localisation/language');

        $data  = [];
        $data['allow_upsert'] 	= true;
		$products_ids = [];
        foreach ($products as $key => $product) {

            $sql_p = 'SELECT * FROM ' . DB_PREFIX . self::$PRODUCTS_TABLE . ' WHERE expand_product_id=' . $product['product_id'] . ' LIMIT 1';
            $p = $this->db->query($sql_p)->row;

            if(count($p) > 0 ) {
                $retailer_id = $p["retailer_id"];
            } else {
                if ($p['retailer_id'] == $product['product_id']) {
                    $retailer_id = $p['retailer_id'];
                } else {
                    $retailer_id = $product['product_id'];
                }
            }

            $brand = $this->model_catalog_manufacturer->getManufacturer($product['manufacturer_id'])['name'];
			$description = substr(strip_tags(html_entity_decode($product['description'])), 0, 4999);
			
			//if all product is in capital case change it to lower case 
			if(mb_strtoupper($description, 'utf-8') == $description){
				$description = strtolower($description);
			}
			
			$description = !empty($description) && !ctype_space($description) ? $description : 'desc';
			$imageUrl 	 = HTTPS_CATALOG . "/ecdata/stores/" . STORECODE . "/image/" . $product["image"];
			$products_ids[] = $product['product_id'];

            $data['requests'][$key]['method'] 				  = 'UPDATE';
            $data['requests'][$key]['retailer_id'] 			  = $retailer_id;
            $data['requests'][$key]['data']['name'] 		  = $product['name'];
            $data['requests'][$key]['data']['price'] 		  = (int) ($product['price'] * 100);
            $data['requests'][$key]['data']['sale_price'] 	  = (int) ($product['sale_price']?? $product['price'] * 100);
            $data['requests'][$key]['data']['description']    = $description;
            $data['requests'][$key]['data']['currency'] 	  = $this->config->get('config_currency');;
            $data['requests'][$key]['data']['inventory'] 	  = $product['quantity'];
            $data['requests'][$key]['data']['availability']   = $product['quantity'] > 0 ? 'in stock' : 'out of stock';
            $data['requests'][$key]['data']['condition'] 	  = 'new';
            $data['requests'][$key]['data']['url'] 			  = str_replace('admin/','',str_replace('&amp;','&',$this->url->frontUrl('product/product', 'product_id=' . $product['product_id'])));
            $data['requests'][$key]['data']['image_url'] 	  = $imageUrl;//$this->model_tool_image->resize($product["image"],800,800);
            $data['requests'][$key]['data']['brand'] 		  = $brand ? $brand : 'No Brand';
            $data['requests'][$key]['data']['custom_label_0'] = 'expandcart';
            $data['requests'][$key]['data']['custom_label_1'] = "language_id:".$export_lang_id;
            $data['requests'][$key]['data']['custom_label_4'] = "expand_product_id:".$product['product_id'];
        }
		
		return [ 'data' => $data ,'products_ids' => $products_ids ];
	}
	
	//ectools - Add Request
	public function addEctoolsJob ($data){
		
		$handle 	 = isset($data['handle'])? $data['handle']:'';
		$token 		 = isset($data['token'])? $data['token']: '';
		$catalog_id  = isset($data['catalog_id'])? $data['catalog_id']: '';
		$fb_status   = isset($data['fb_status'])? $data['fb_status'] :'dispatched';
		$batch_id    = isset($data['batch_id'])? $data['batch_id'] :'';
		$storecode   = defined('STORECODE')? STORECODE:'';

		$query   = [];
        $query[] = 'INSERT INTO ' . self::$ECTOOLS_JOBS_TABLE . ' SET';
        $query[] = ' handle  = "' . $this->ecusersdb->escape($handle) . '",';
        $query[] = ' token   = "' . $this->ecusersdb->escape($token) . '",';
        $query[] = ' catalog_id  = "' . $this->ecusersdb->escape($catalog_id) . '",';
        $query[] = ' batch_id  = "' . $this->ecusersdb->escape($batch_id) . '",';
        $query[] = ' storecode  = "' . $this->ecusersdb->escape($storecode) . '",';
        $query[] = ' created_at  = NOW()';
		
        $this->ecusersdb->query(implode(' ', $query));
        $requestId = $this->ecusersdb->getLastId();
        return  $requestId;
	}
	
	//
	public function addpushedProduct ($data){
		$expand_product_id 	 = $data['expand_product_id'] ?? '';
		$retailer_id 		 = $data['retailer_id'] ??'';
		$catalog_id 		 = $data['catalog_id'] ?? '';
		$catalog_retailer_id = $data['catalog_id'] . "_" . $retailer_id ;
		$push_status    	 = 'pushed';
		$batch_id   		 = $data['batch_id'] ?? '';

		$query   = [];
        $query[] = 'INSERT INTO ' . self::$PUSHED_PRODUCTS_TABLE . ' SET';
        $query[] = ' expand_product_id  = "' . $this->db->escape($expand_product_id) . '",';
        $query[] = ' retailer_id   = "' . $this->db->escape($retailer_id) . '",';
        $query[] = ' facebook_catalog_id  = "' . $this->db->escape($catalog_id) . '",';
        $query[] = ' catalog_retailer_id  = "' . $this->db->escape($catalog_retailer_id) . '",';
        $query[] = ' push_status  = "' . $this->db->escape($push_status) . '",';
        $query[] = ' batch_id  = "' . $this->db->escape($batch_id) . '",';
        $query[] = ' created_at  = NOW()';
        $query[] = ' ON DUPLICATE KEY UPDATE  ';
		$query[] = ' retailer_id  = "' . $this->db->escape($retailer_id) . '",';
		$query[] = ' push_status  = "' . $this->db->escape($push_status) . '",';
		$query[] = ' updated_at  = NOW()';
        $this->db->query(implode(' ', $query));
        $pushed_product_id = $this->db->getLastId();
        return  $pushed_product_id;
	}
	
	//
	public function markProductImported($products_ids = []){
		
		if (is_array($products_ids)) {
			$products_ids = implode(',', $products_ids);
		}
		
		$sql = 'UPDATE `' . DB_PREFIX  . self::$PRODUCTS_TABLE .'`';
		$sql .= 'SET `imported` = 1';
		$sql .= ' where `expand_product_id` IN ('.$products_ids.')';
			
			
		$this->db->query($sql);
		
	}
	
	//
	public function getBatch($batch_id){
		$sql = 'SELECT  * FROM `' . DB_PREFIX  . self::$BATCHES_TABLE . '` WHERE `id` = '. $batch_id  ;
		$data = $this->db->query($sql);
		return $data->row;
	}
	
	//
	public function getBatchProducts($batch_id){
		$sql = 'SELECT * FROM `' . DB_PREFIX  . self::$PUSHED_PRODUCTS_TABLE . '` 
		
		WHERE `batch_id` = '.$batch_id;
		$data = $this->db->query($sql);
		return $data->rows;
	}
	
	//
	public function updatePushProductsStatus($retailer_ids=[],$catalog_id,$status="approved"){
		$sql  = "UPDATE `" . DB_PREFIX  . self::$PUSHED_PRODUCTS_TABLE . "`";
		$sql .= " SET push_status = '" . $status ."'";
		
		if($status == "approved" ){
			$sql .= " ,  rejection_reason = ''";
		}
		
		$sql .= ", updated_at=NOW()  WHERE `retailer_id` IN  ('". implode ( "', '", $retailer_ids ) ."') ";
		$sql .= " AND facebook_catalog_id = ". $catalog_id;
		
		$result = $this->db->query($sql);
		return  $result ? true : false ;
	}
	
	//
	public function updatePushProduct($data,$retailer_id,$catalog_id=null){
		
		$catalog_id = $catalog_id ?? $this->catalog_id;
		 
		$sql  = "UPDATE `" . DB_PREFIX  . self::$PUSHED_PRODUCTS_TABLE . "` SET";
		
		foreach ($data as $key => $val) {
		  $sql .= " $key='" . $this->db->escape($val) . "',";
		}
		
		$sql .= "updated_at=NOW()  WHERE `retailer_id` =  '".$this->db->escape($retailer_id) ."' ";
		$sql .= " AND facebook_catalog_id = ". $catalog_id;
		
		$result = $this->db->query($sql);
		
		return  $result ? true : false ;
	}
	
	//
	public function install(){
		
		$sql1 = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX  . self::$JOBS_TABLE . "` (
			`job_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			`user_id` INT(11) DEFAULT NULL,
			`catalog_id` VARCHAR(191) NOT NULL DEFAULT 0,
			`status` VARCHAR(50) DEFAULT NULL,
			`product_count` INT(11) DEFAULT 0,
			`payload` TEXT,
			`created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
			`updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
			`finished_at` DATETIME DEFAULT NULL,
			`operation` VARCHAR(50) NOT NULL DEFAULT \"import\",
			`operation_type` enum('runtime','queue') DEFAULT 'queue',
			PRIMARY KEY (`job_id`)
		) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";


		$sql2 = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX  . self::$BATCHES_TABLE . "` (
			`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			`user_id` INT(11) DEFAULT NULL,
			`catalog_id` VARCHAR(191) NOT NULL DEFAULT 0,
			`handle` VARCHAR(255) DEFAULT NULL,
			`fb_status` enum('dispatched','started','finished','canceled','error') DEFAULT 'dispatched',
			`products_total_count`  INT(11) NULL,
			`localize` VARCHAR(255) DEFAULT NULL,
			`products_ids` MEDIUMTEXT,
			`errors_total_count` INT(11) NULL,
			`warnings_total_count` INT(11) NULL,
			`ids_of_invalid_requests` TEXT NULL,
			`errors` TEXT NULL,
			`warnings` TEXT NULL,
			`job_id` INT(11) DEFAULT NULL, 
			`created_at` DATETIME  DEFAULT CURRENT_TIMESTAMP,
			`updated_at` DATETIME  DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			INDEX(`job_id`)
		) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";

		$sql3 = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX  . self::$PRODUCTS_TABLE . "` (
			`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			`expand_product_id` INT(11) DEFAULT NULL,
			`retailer_id` VARCHAR(50) DEFAULT NULL,
			`facebook_business_id` BIGINT(20) DEFAULT NULL,
			`facebook_catalog_id` BIGINT(20) DEFAULT NULL,
			`facebook_product_id` BIGINT(20) DEFAULT NULL ,
			`payload` MEDIUMTEXT,
			`imported` BOOLEAN NOT NULL DEFAULT FALSE,
			`created_at` DATETIME  DEFAULT CURRENT_TIMESTAMP,
			`updated_at` DATETIME  DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			UNIQUE KEY `facebook_product_id` (`facebook_product_id`),
			UNIQUE KEY `retailer_id` (`retailer_id`)
		) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
		
		$sql4 = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX  . self::$PUSHED_PRODUCTS_TABLE. "` (
			`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			`expand_product_id` INT(11) DEFAULT NULL,
			`retailer_id` VARCHAR(50) DEFAULT NULL,
			`catalog_retailer_id` VARCHAR(50) DEFAULT NULL,
			`facebook_catalog_id` BIGINT(20) DEFAULT NULL,
			`push_status` enum('pushed','push_failed','approved','rejected') DEFAULT 'pushed' ,
			`batch_id` INT(11) DEFAULT NULL ,
			`rejection_reason` VARCHAR(50) DEFAULT NULL,
			`created_at` DATETIME  DEFAULT CURRENT_TIMESTAMP,
			`updated_at` DATETIME  DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			INDEX(`expand_product_id`),
			UNIQUE KEY `catalog_retailer_id` (`catalog_retailer_id`)
		) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";	

		$this->db->query($sql1);
		$this->db->query($sql2);
		$this->db->query($sql3);
		$this->db->query($sql4);
	}
	
	public function uninstall(){
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX  . self::$PUSHED_PRODUCTS_TABLE . "`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX  . self::$BATCHES_TABLE . "`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX  . self::$JOBS_TABLE . "`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX  . self::$PRODUCTS_TABLE . "`");
		
		$this->deleteBusiness();
	}

    public function logout(){
		$this->deleteBusiness();
        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `group` = '" . $this->db->escape(self::$MODULE_NAME) . "'  ");
    }
	
	//-------- Endpoints  -------------//
	
	public function getLongLiveToken($token){
		
        if ($token) {

			$url    = self::$BASE_URL.'/'.self::$API_VERSION .'/oauth/access_token?grant_type=fb_exchange_token';
			$url  .= '&client_id=' . $this->facebook_app_id . '&client_secret=' . $this->facebook_secret_key ; 
			$url  .='&fb_exchange_token=' . $token;
			
			$request = [
					 'type' => 'GET',
					 'url'  => $url 
			];
				
			$response = $this->_sendRequest($request);
		
            $token = json_decode($response)->access_token ?? false;
            
			return $token;
        }
		
         return  false;
    }
	
	public function getInstallationIds($token="",$external_business_id=""){

        if ( !empty($token) && !empty($external_business_id)) {
            
			$url    = self::$BASE_URL.'/'.self::$API_VERSION .'/fbe_business/fbe_installs';
			$url   .= '?fbe_external_business_id='.$external_business_id;
			$url   .= '&access_token=' . $token ;
			
			$request = [
						'type' => 'GET',
						'url'  => $url 
					];
				
			$response = $this->_sendRequest($request);

            $result   = (array)(json_decode($response)->data[0]);
			
			return $result;

        }
		
		return false;
    }
	
	/**
	 * Grap an image with CURL and save it to the specified path
	 *
	 * @param string $url
	 * @param string $saveto
	 * @return void
	 */
	public function grab_image($url, $saveto): void
	{
		set_time_limit(0);
		$ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 400);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        if (str_contains('expandcart.com',  parse_url($url)['host'])) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        $raw = curl_exec($ch);
		curl_close($ch);
		if (\Filesystem::isExists($saveto)) {
			\Filesystem::deleteFile($saveto);
		}
		\Filesystem::setPath($saveto)->put($raw);
	}

	//
	public function getBusiness($token=null){
		
		$access_token = $token ?? $this->system_user_token;
		
		$fields = 'id,name,created_time,profile_picture_uri,primary_page,adspixels{id,name,creation_time}';
		$fields .= ',owned_pages{id,name,picture,access_token,fan_count},verification_status';
		$fields .=',owned_product_catalogs,owned_instagram_accounts{username,profile_pic,media_count,has_profile_picture}';
		$result  =[];
       try {
			  $result = $this->fb->get(
				$this->business_manager_id.'?fields='.$fields,
				$access_token,
				null, 
				"v11.0"
			  )->getDecodedBody();
			} catch(\Facebook\Exceptions\FacebookResponseException $e) {
				$result['error'] =  'FB:' . $e->getMessage();
			} catch(\Facebook\Exceptions\FacebookSDKException $e) {
				$result['error'] = 'FB:' . $e->getMessage();
			}
			
			return $result;
    }
	
	//
    public function deleteBusiness(){
		
		//$access_token = $this->system_user_token; for some reason it return permission error using the SU token
        $access_token = $this->access_token;

        if (!$access_token ) {
			$this->_requestLog(['cant send the request, #method'.__function__],'reason : no access token exists! ');
			return false ;
		}
		
		if(!$this->isFbInstalled && !$this->isPixelsInstalled ) {
			$this->_requestLog(['cant send the request, #method'.__function__],'reason : no feature installed! ');
			return false ;
		}

		$url      = self::$BASE_URL.'/'.self::$API_VERSION .'/fbe_business/fbe_installs';
		$url     .= '?fbe_external_business_id='.$this->external_business_id;
		$url     .= '&access_token='.$access_token;				
		
		$response = $this->_sendRequest([
										'type'	=> 'DELETE',
										'url' 	=> $url , 
										'data' 	=> [
													'fbe_external_business_id'  => $this->external_business_id,
													'accessToken' 				=> $access_token,
												]
										]);
		return $response;
    }

	//
    public function  getUserInfo(){

        $access_token = $this->access_token;
		
        if($access_token != "") {

            try {

                $result = $this->fb->get( '/me?fields=id,name,picture', $access_token)->getDecodedBody();
                return ["name" => $result["name"], "profile_image_url" => $result["picture"]["data"]["url"]];
            } catch (FacebookSDKException $e) {

                return [];
            }
        }

        return [];
    }

	//
    public function  getUserPages(){

        $access_token = $this->access_token;
		
        if($access_token != "") {

            try {
				$url = $this->app_user_id .'/accounts?fields=id,name,picture,access_token,fan_count';
                
				$result = $this->fb->get($url,$access_token)->getDecodedBody();
                return $result ;
            } catch (FacebookSDKException $e) {

                return [];
            }
        }

        return [];
    }

	//
    public function fetchFacebookProductsToDB($catalog_id = null, $limit = 100, $next_cursor = null, $previous_cursor = null): array{

        try {
            $return_data = [];

            $catalog_id = $this->request->get['catalog_id'];

            //Set the graphql request
            $request_ql = $catalog_id . '/products?fields=custom_label_0,retailer_id,id,name,description,currency,price,sale_price,brand,category,availability,image_url,inventory,custom_label_1,custom_label_2,custom_label_3,product_catalog{product_count}&limit=' . $limit;

            if ($next_cursor != null) {
                $request_ql .= '&after=' . $next_cursor;
            }

            if ($previous_cursor != null) {
                $request_ql .= '&before=' . $previous_cursor;
            }

            //Send the request and get products
            $request = $this->fb->get($request_ql, $this->system_user_token)->getDecodedBody();
			
			$data = [];
            //Loop to insert into the DB
			$facebook_product_ids = [];
            foreach ($request['data'] as $key => $product) {
                //check if product has custom_label_0
                //which means this product is exported from expandcart
                //then the expand product id is the retailer id
               
				
				$expand_product_id = (isset($product['custom_label_0']) && $product['custom_label_0'] == 'expandcart') ? $product['retailer_id'] : "null";

				$query   = [];
				$query[] = 'INSERT INTO ' . self::$PRODUCTS_TABLE . ' SET';
				$query[] = ' expand_product_id  = "' . $this->db->escape($expand_product_id) . '",';
				$query[] = ' retailer_id   = "' . $this->db->escape($product['retailer_id']) . '",';
				$query[] = ' facebook_catalog_id  = "' . $this->db->escape($product['product_catalog']['id']) . '",';
				$query[] = ' facebook_product_id  = "' . $this->db->escape($product['id']) . '",';
				$query[] = ' payload  = "' . $this->db->escape(json_encode($product))  . '",';
				$query[] = ' created_at  = NOW()';
				$query[] = ' ON DUPLICATE KEY UPDATE  ';
				$query[] = ' payload  = "' . $this->db->escape(json_encode($product)) . '",';
				if($expand_product_id != "null"){
					$query[] = ' expand_product_id  = "' . $this->db->escape($expand_product_id) . '",';
				}
				$query[] = ' facebook_product_id  = "' . $this->db->escape($product['id']) . '",';
				$query[] = ' updated_at  = NOW()';
				
				$this->db->query(implode(' ', $query));
				
				$facebook_product_ids[] = $product['id'];
				
				$product_id = $product['id'];
				$is_imported = false;
				$data[$product_id]['id'] 			 = $product['id'];
                $data[$product_id]['image_url'] 	 = $product['image_url'];
                $data[$product_id]['name'] 			 = $product['name'];
                $data[$product_id]['price'] 		 = $product['price'];
                $data[$product_id]['currency']  	 = $product['currency'];
                $data[$product_id]['brand'] 		 = $product['brand'];
                $data[$product_id]['expand_id'] 	 = $product['brand'];
                $data[$product_id]['custom_label_1'] = $product['custom_label_1'];
                $data[$product_id]['is_imported'] 	 =  $product['custom_label_0'] == 'expandcart'; //incase its already pushed from expandcart
				//the case of imported before is handled below 
            }

			$our_db_products = $this->getFbImportedProducs($facebook_product_ids);
			
			foreach ($our_db_products as $product ){
				if(isset($data[$product['facebook_product_id']])){
					$data[$product['facebook_product_id']]['is_imported'] 	 = true;
				}
			}
			
			$return_data['data']            = array_values($data);
            $return_data['next_cursor'] 	= $request['paging']['cursors']['after'];
            $return_data['previous_cursor'] = $request['paging']['cursors']['before'];
            $return_data['recordsTotal'] 	= $request['data'][0]['product_catalog']['product_count'] ?? $request['data'][0]['product_catalog']['product_count'];
            $return_data['recordsFiltered'] = $request['data'][0]['product_catalog']['product_count'] ?? $request['data'][0]['product_catalog']['product_count'];

            return $return_data;
			
        } catch (Exception $e) {
            //Log the error
            return ['error' => $e->getMessage()];
        }
    }
		
	//
	public function exportSelectedProducts($data=[]){
		
		$selected_products 	= $data['selected_products'];
		$main_lang_id		= $data['main_lang_id'];
		$localize_languages = $data['localize_languages_codes'];
		$catalog_id			= $data['catalog_id'];
		$job_id				= $data['job_id'];
		
		if(is_array($localize_languages)){
			$localize_languages = implode (",", $localize_languages);
		}
		
		$this->load->model("localisation/language");
        $this->load->model('catalog/product');
        $products = $this->model_catalog_product->getProductsByIds($selected_products,$main_lang_id);		
		$result 	  = $this->_handleProductForExport($products);
		$response = $this->_sendBatch($catalog_id,$result['data']);
		
		$request_result = json_decode($response,true);
		
		//TO:DO need to review this part 
		$errors = [];
		$failedProductIds = [];
        if(isset($request_result["validation_status"])) {
            foreach ($request_result["validation_status"] as $validationRecord) {
				if(isset($validationRecord["retailer_id"])){
					array_push($failedProductIds, $validationRecord["retailer_id"]);
				}
               $errors[$validationRecord["retailer_id"]] = $validationRecord["errors"][0]["message"];
            }
        }

		
		//add job here 		
		$handle = $request_result['handles'][0] ?? "";	
		
		//set job as completed 
		$sql = 'update `' . DB_PREFIX  . self::$JOBS_TABLE . '` set status="completed",finished_at=NOW(),updated_at=NOW() WHERE job_id=' . $job_id;
		$this->db->query($sql);
			  
		if(!empty($handle)){
			$query   = [];
			$query[] = 'INSERT INTO ' . DB_PREFIX  . self::$BATCHES_TABLE . ' SET';
			$query[] = ' user_id  = "' . $this->user->getId()   . '",';
			$query[] = ' catalog_id  = "' . $this->db->escape($catalog_id) . '",';
			$query[] = ' handle  = "' . $this->db->escape($handle) . '",';
			$query[] = ' fb_status  = "dispatched",';
			$query[] = ' products_total_count  = "' . $this->db->escape(count($products)) . '",';
			$query[] = ' localize  = "' . $this->db->escape($localize_languages) . '",';
			$query[] = ' products_ids  = "' . $this->db->escape(implode (",", $selected_products)) . '",';
			$query[] = ' job_id  = "' . $this->db->escape($job_id) .'"' ;
		
			$this->db->query(implode(' ', $query));

			$batch_id = $this->db->getLastId();
				
			$this->addEctoolsJob([
								  'handle' 	   => $handle,
								  'token'  	   => $this->system_user_token,
								  'catalog_id' => $catalog_id ,
								  'fb_status'  => 'dispatched',
								  'batch_id'   => $batch_id
								 ]);
				
				
			foreach ( $result['data']['requests'] as $request ){
				$push_data = [
							  'expand_product_id' => str_replace("expand_product_id:", "", $request['data']['custom_label_4']),
							  'retailer_id' 	  => $request['retailer_id'],
							  'catalog_id' 		  => $catalog_id,
							  'batch_id' 	      => $batch_id
							];	
							
				$this->addpushedProduct ($push_data);
			}
			
			$result  = [];
			
			if(count($errors) == 0){
				//$result = ['success' => '1', 'products_count' => count($products) ,'message' => $this->language->get('res_export_success')];
				$result = ['success' => '1', 'products_count' => count($products) ];
			}else {
				$failedCount = count($errors);
				$result = ['successs' => '1', 'products_count' => count($products) - count($errors), 'failed_count' => $failedCount, 'errors' => $errors];
			}
			
			return $result;
        } 
			
		$message = "something went wrong!";
		if(isset($request_result["validation_status"])) {
			$message = "";
			foreach ($request_result["validation_status"] as $validationRecord) {
				$message .= $validationRecord["errors"][0]["message"] . " | ";
			}
		}
		
		$result['success'] = '0';
		$result["error"]   = $message;
		return $result ;
	}
	
	//
	private function _sendBatch($catalog_id,$data){
		
		$url     = self::$BASE_URL.'/'.self::$API_VERSION .'/'.$catalog_id . '/batch?access_token='. $this->system_user_token;
		$request = [
					'type' 	=> 'POST',
					'url'  	=> $url ,
					'data'  => $data 
				];
				
		$response = $this->_sendRequest($request);
		
		return $response;
	}
	
	//
    public function exportAllFacebookProductsFromDB($data=[]){
 
		$main_lang_id		= $data['main_lang_id'];
		$localize_languages = $data['localize_languages_codes'];
		
		if(is_array($localize_languages)){
			$localize_languages = implode (",", $localize_languages);
		}
		
		$catalog_id			= $data['catalog_id'];
		$job_id				= $data['job_id'];
		$http_catalog		= $data['http_catalog'];
		
        //Get all products from database
        $this->load->model('catalog/product');
        $this->load->model('catalog/manufacturer');
        $this->load->model('localisation/language');
		
		//Get  products
        $products = $this->model_catalog_product->getProducts(['filter_status'=>1], $export_lang_id);
		$result   = $this->_handleProductForExport($products);
		$selected_products = $result['products_ids'];
		$response = $this->_sendBatch($catalog_id,$result['data']);
		
		$request_result = json_decode($response,true);
		
		//TO:DO need to review this part 
		$errors = [];
		$failedProductIds = [];
        if(isset($request_result["validation_status"])) {
            foreach ($request_result["validation_status"] as $validationRecord) {
				if(isset($validationRecord["retailer_id"])){
					array_push($failedProductIds, $validationRecord["retailer_id"]);
				}
               $errors[$validationRecord["retailer_id"]] = $validationRecord["errors"][0]["message"];
            }
        }
		
		//add job here 		
		$handle = $request_result['handles'][0] ?? "";	
		if(!empty($handle)){
			$query   = [];
			$query[] = 'INSERT INTO ' . DB_PREFIX  . self::$BATCHES_TABLE . ' SET';
			$query[] = ' user_id  = "' . $this->user->getId()   . '",';
			$query[] = ' catalog_id  = "' . $this->db->escape($catalog_id) . '",';
			$query[] = ' handle  = "' . $this->db->escape($handle) . '",';
			$query[] = ' fb_status  = "dispatched",';
			$query[] = ' products_total_count  = "' . $this->db->escape(count($products)) . '",';
			$query[] = ' localize  = "' . $this->db->escape($localize_languages) . '",';
			$query[] = ' products_ids  = "' . $this->db->escape(implode (",", $selected_products)) . '",';
			$query[] = ' job_id  = "' . $this->db->escape($job_id) .'"' ;
		
			$this->db->query(implode(' ', $query));

			$batch_id = $this->db->getLastId();
				
			$this->addEctoolsJob([
								  'handle' 	   => $handle,
								  'token'  	   => $this->system_user_token,
								  'catalog_id' => $catalog_id ,
								  'fb_status'  => 'dispatched',
								  'batch_id'   => $batch_id
								 ]);
				
				
			foreach ( $result['data']['requests'] as $request ){
				$push_data = [
							  'expand_product_id' => str_replace("expand_product_id:", "", $request['data']['custom_label_4']),
							  'retailer_id' 	  => $request['retailer_id'],
							  'catalog_id' 		  => $catalog_id,
							  'batch_id' 	      => $batch_id
							];	
							
				$this->addpushedProduct ($push_data);
			}
		
				$sql = 'update `' . DB_PREFIX  . self::$JOBS_TABLE . '` set status="completed",finished_at=NOW(),updated_at=NOW() WHERE job_id=' . $job_id;
				$this->db->query($sql);
			  
			//add notification here 
            $this->notifications->addAdminNotification([
														'notification_module' 		=> self::$MODULE_NAME , 
														'notification_module_code' 	=> self::$notification_exportall_code,
														'notification_module_id' 	=> $job_id
														]);
        } else {
			
			  $sql = 'update `' . DB_PREFIX  . self::$JOBS_TABLE . '` set status="failed",updated_at=NOW(),payload="' . json_encode($request_result) . '" WHERE job_id=' . $job_id;
			  $this->db->query($sql);
		}

    }

	//
	public function sendLocalizeProducts($products_ids,$localizes=[]){
		
		$this->load->model('catalog/product');
		$this->load->model('localisation/language');
		
		foreach ($localizes as $localize_code ){
		
			$language_id = $this->model_localisation_language->getLanguageByCode($localize_code)['language_id'];
			$products 	 = $this->model_catalog_product->getProductsByIds($products_ids,$language_id);
			//$this->logger->write("language_id " .$language_id);
			//$this->logger->write("products " . json_encode($products));
			
			$catalog_id  	= $catalog_id ?? $this->catalog_id;
			$fb_locale		= self::$fb_locales[strtolower($localize_code)]?? self::$fb_default_locale;
			$localized_data = $this->handleProductLocalizeForExport($products,$fb_locale);
			$requests 		= $localized_data['requests'];
			
			
			$localized_data['requests']  = json_encode($requests,JSON_UNESCAPED_UNICODE);
			$localized_data['item_type'] = "PRODUCT_ITEM";
					
			$url = $catalog_id . '/localized_items_batch';
			
			try {
			
				$gql = $this->fb->post($url, $localized_data, $this->system_user_token, null, 'v11.0');
						
				$localize_graphNode = $gql->getGraphNode()->asArray();
						
				//log data 
				$this->_requestLog(['url'=>$url,'data'=> $localized_data] , json_encode($localize_graphNode));
			
			} catch (FacebookSDKException $e) {
				$this->logger->write("#method " . __function__ . "[exception] : " .$e->getMessage() .'data:'.json_encode($localized_data) );
			}
		}		
	}
	
	//
	public function createSystemUserAccessToken($business_manager_id,$admin_access_token,$external_business_id){

        $url  = self::$BASE_URL.'/'.self::$API_VERSION .'/'.$business_manager_id .'/access_token?debug=all';
		
		$request = [
					 'type' => 'POST',
					 'url'  => $url ,
					 'data' => [
								'app_id' 					=> $this->facebook_app_id ,
								'scope' 					=> 'ads_read,business_management,ads_management,catalog_management,manage_business_extension',
								'access_token' 				=> $admin_access_token,
								'fbe_external_business_id'  => $external_business_id
								]
					];
				
		$response = $this->_sendRequest($request);
		
		return json_decode($response);
    }
	
	//
	public function getSystemUserId($system_user_token = false){
		
		$token = $system_user_token ?? $this->system_user_token;
		
        $url  = self::$BASE_URL.'/'.self::$API_VERSION .'/me?access_token='.$token;

		$request = [
					 'type' => 'GET',
					 'url'  => $url 
					];
				
		$response = $this->_sendRequest($request);
		
		return json_decode($response);
    }
	
	//
	public function assignAssetToSU($asset_id,$tasks='MANAGE'){

        $url    = self::$BASE_URL.'/'.self::$API_VERSION .'/'.$asset_id.'/assigned_users?user='.$this->system_user_id;
		$url   .= '&tasks='.$tasks.'&access_token='.$this->access_token;
		$request = [
					 'type' => 'POST',
					 'url'  => $url 
					];
				
		$response = $this->_sendRequest($request);
		
		return json_decode($response);
    }
	
	//
	public function createPage($data){

		$status = 'error';
		$error  = '';	
		
		 $parameters = [
			"access_token" 	=> $this->access_token,
			"name" 			=> $data['page_name'],
			"about" 		=> $data['page_about'],
			"category_enum" => "ECOMMERCE_WEBSITE",
			"picture" 		=> $data['pic_uri'],
			"cover_photo" 	=> json_encode(["url" => $data['cover_uri']], JSON_UNESCAPED_SLASHES)		
		];
		
        $url  = self::$BASE_URL.'/'.self::$API_VERSION .'/'. $this->app_user_id . "/accounts";
		$url .= '?access_token='.$this->access_token;
			
			
		$request = [
					'type' => 'POST',
					'url'  => $url,
					'data' => $parameters
					];
				
		$response = $this->_sendRequest($request);
		$result   =  json_decode($response,true);   
		
		if (isset($result['id']) && !empty($result['id'])) {
            $status = 'success';   
		} else {
			
			$status = 'error';
			$error = $result['error']['error_user_msg']??$result['error']['message'];
		}

		return [
				'status' => $status, 
				'error'  => $error
			];			
	}
	
	//
	public function createCatalog($catalog_name){
		
		$status = 'error';
		$error  = '';


		$parameters = ['name' => $catalog_name];
			
        $url  = self::$BASE_URL.'/'.self::$API_VERSION .'/'.$this->business_manager_id . '/owned_product_catalogs';
		$url .= '?access_token='.$this->access_token;
			
		$request = [
					'type' => 'POST',
					'url'  => $url,
					'data' => $parameters
					];
				
		$response = $this->_sendRequest($request);
		$result   =  json_decode($response,true);   
		
		if (isset($result['id']) && !empty($result['id'])) {
            $status = 'success';   
		} else {
			$status = 'error';
			$error = $result['error']['message'];
		}

		return [
				'status' => $status, 
				'error'  => $error
			];			
	}
	
	//
	public function createPixel($ad_account_id,$pixel_name){
		
		$status = 'error';
		$error  = '';

		$parameters = ['name' => $pixel_name];
			
        $url     = self::$BASE_URL.'/'.self::$API_VERSION .'/act_' .$ad_account_id . '/adspixels';
		$url    .= '?access_token='.$this->access_token;
			
		$request = [
					'type' => 'POST',
					'url'  => $url,
					'data' => $parameters
					];
				
		$response = $this->_sendRequest($request);
		$result   =  json_decode($response,true);   
		
		if (isset($result['id']) && !empty($result['id'])) {
            $status = 'success';   
		} else {
			$status = 'error';
			$error = $result['error']['message'];
		}

		return [
				'status' => $status, 
				'error'  => $error
			];
				
	}
	
	//we may use it later if we use cronjob | used at ectools we can ignore it here 
	public function batchStatus($catalog_id,$handle,$token){
			
		$url    = self::$BASE_URL.'/'.self::$API_VERSION .'/'.$catalog_id . '/check_batch_request_status';
		$url  .= '?handle='.$handle ; 
		$url  .= '&access_token='.$token ; 
			
			$request = [
					 'type' => 'GET',
					 'url'  => $url 
			];
				
			$response = $this->_sendRequest($request);
			
			return $response;
			
    }

	//---------- helpers ------------/
	/**
     *  validate webhook signature 
     */
	public function validateEctoolsSignature($body, $header_signature = ''): bool {
       
		if(!defined('ECTOOLS_ENC_KEY')){
			define ('ECTOOLS_ENC_KEY', '8ah3ww72bk4b9agddm2art1gy5h75zhaz4im9gd3');
		}
		
		// Signature matching
		$expected_signature = hash_hmac('sha1', $body , ECTOOLS_ENC_KEY );

		$signature = '';
		if(
			strlen($header_signature) == 45 &&
			substr($header_signature, 0, 5) == 'sha1='
		  ) {
		  $signature = substr($header_signature, 5);
		}
		
		//validate 
		if (hash_equals($signature, $expected_signature)) {
		 return true;
		}

		return false;
    }
	
	/**
     * Convert an array to object
     *
     * @param array $Array
     * @return object
     */
    private function decodedBodyToObject($Array) {

        // Create new stdClass object 
        $object = new stdClass();

        // Use loop to convert array into 
        // stdClass object 
        foreach ($Array as $key => $value) {
            if (is_array($value)) {
                $value = $this->decodedBodyToObject($value);
            }
            $object->$key = $value;
        }
        return $object;
    }
	
	/**
     * Convert numbers from Arabic or Persian numbers to English
     * @param $string
     * @return string|string[]
     */
    public function convertPriceInEnglish($string): string {
		
        $persian = array_reverse(['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹']);
        $arabic = array_reverse(['٩', '٨', '٧', '٦', '٥', '٤', '٣', '٢', '١', '٠']);

        $num = range(0, 9);
        $convertedPersianNums = str_replace($persian, $num, $string);
        $convertedArabicNums = str_replace($arabic, $num, $convertedPersianNums);
        $removeArabic = str_replace(['ج.م.','$','ر.س.','د.إ.' , ','], '', $convertedArabicNums);
        return $removeArabic;
    }
	
	//
	private function _sendRequest($request){
		
		$request_url  = $request['url']??"";
		$type 	 	  = $request['type']??"POST";
		$headers 	  = $request['headers']??[];
		$request_data = $request['data']??[];
		
		$soap_do      = curl_init();
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
	
	//
	private function _requestLog($request,$response){
		if(!defined('LOG_MODULE_FBE')){
			//at current time the default will be enabling it till the stability of the APP 
			define('LOG_MODULE_FBE',True); 
		}
		
		//control the enable and disable of logging via server config 
		if(LOG_MODULE_FBE){
			$log_text = "Request : ". json_encode($request);
			$log_text .= "\n => Response : ". $response;
			$this->logger->write($log_text);
		}
	}
	
	
}
