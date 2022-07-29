<?php
class ModelModuleLableb extends Model {
    
    private $host_url = "https://api.lableb.com/"; //"https://api-demo.lableb.com/";
	
	protected $logger ;
	protected $api_key ;
	protected $project_name ;
	protected static $indice = 'products';
	
	public function __construct ($registry){
		parent::__construct($registry);
		$this->logger  =  new \Log('lableb_expandish');
		
		$settings_data  	= $this->config->get('lableb');
		$this->api_key 		= $settings_data['search_apikey'];
		$this->project_name = $settings_data['project'];
		$this->availabilty_indexed = $settings_data['fresh_indexing_v1_1']??0;
	}
	
	public function autoComplete($search_text){
        $this->load->model('catalog/product');
		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguages();
       // $search_text = trim($search_text);

        $query = http_build_query([
								"q" 	 => $search_text,
								"apikey" => $this->api_key
								]);

		if($this->availabilty_indexed){
			$query .= '&availability=1';
		}
		//check for arabic or english [for duplicates result issue when titles are same in en & ar ] 
		if($this->_isEnglish($search_text) && isset($languages['en'])){
			$query .= '&filter=language:en' ;
		}else if(!$this->_isEnglish($search_text) && isset($languages['ar'])){
			$query .= '&filter=language:ar' ;
		}

		

        $url  = $this->host_url . "v2/projects/" .  $this->project_name ;
		$url .= "/indices/".self::$indice."/autocomplete?". $query;

        $response   = $this->_sendRequest([
									'type'	  => 'GET',
									'url'     => $url
									]);
									
		$response	= json_decode($response);

        $counter = 1;
		$products = [];
		
        foreach($response->response->results as $key => $value){

            if($value->suggestion_type =='navigational'){
                $product_id = explode('-' , $value->id)[0];
				
                $product = $this->model_catalog_product->getProduct($product_id); 
				$price = (float)$value->price <= 0 ? $product['price'] : $value->price;


                $products[] = [
                    'link'          => $this->url->link('product/product&product_id=' . $product_id . "&query=" . $search_text . "&lableb_autoComplete=1&item_order=" . $counter, '', 'SSL'),
                    'name'          => $value->phrase,
                    'description'   => isset($product['description']) ? strip_tags($product['description']) : '',
                    'image'         => \Filesystem::getUrl('image/' . $product['image']),
                    'price'         =>  number_format($price, 2) . $this->config->get("config_currency")
                ];

                ++$counter;

            }
        }
       return $products;
    }

	public function search($search_options=[]){
		$this->load->model('catalog/product');
		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguages();
		
		$query_parameters = [
							"apikey" => $this->api_key,
							"q" 	 => $search_options['search_text']
							];
							
		$query = http_build_query($query_parameters);


		if(isset($search_options['category'])){
			$query .= '&filter=category:' . $search_options['category'];
        } else {
			//check for arabic or english [for duplicates result issue when titles are same in en & ar ] 
			if($this->_isEnglish($search_options['search_text']) && isset($languages['en'])){
				$query .= '&filter=language:en' ;
			}else if(!$this->_isEnglish($search_options['search_text']) && isset($languages['ar'])){
				$query .= '&filter=language:ar' ;
			}
		}

        if($this->availabilty_indexed){
            $query .= '&availability=1';
        }
		
		if(isset($search_options['limit'])){
			$query .= '&limit='.$search_options['limit'] ;
		}
		
		if(isset($search_options['page'])){
			$skip = ($search_options['page']-1)*$search_options['limit'];
			$query .= '&skip='.$skip ;
		}
		
		if(isset($search_options['sort'])){
			$query .= '&sort='.$search_options['sort'] ;
		}

		$url  = $this->host_url . "v2/projects/" .$this->project_name;
        $url .= "/indices/".self::$indice."/search?". $query;

		$response   = $this->_sendRequest([
									'type'	  => 'GET',
									'url'     => $url
									]);
									
		$response	= json_decode($response);
        //array to prevent showing product more than one time  in case product name is the same in all languages like kanawat products
		$lableb_products = array(); 
		$found_documents = $response->response->found_documents;

        foreach($response->response->results as $key => $value){
            $product = $this->model_catalog_product->getProduct($value->product_id);
            if (!$product) continue;
            
            // display the real price
            $price = false;
            $isCustomerAllowedToViewPrice = $this->customer->isCustomerAllowedToViewPrice();

            if ($isCustomerAllowedToViewPrice) {
                $price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')));
            }
            
            $special = false;
            if ((float)$product['special'] && $isCustomerAllowedToViewPrice) {
                $special = $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')));
            }

            if ($this->config->get('config_tax')) {
                $tax = $this->currency->format((float)$product['special'] ? $product['special'] : $product['price']);
            } else {
                $tax = false;
            }
            
            if ($product['price'] > 0) {
                $savingAmount = round((($product['price'] - $product['special'])/$product['price'])*100, 0);
            }
            else {
                $savingAmount = 0;
            }

            $stock_status = '';
            if ($product['quantity'] <= 0) {
                $stock_status = $product['stock_status'];
            }
            ///check permissions to view Add to Cart Button and view products
            $viewAddToCart = true;
            $hidCartConfig = $this->config->get('config_hide_add_to_cart');		
            if(($product['quantity'] <=0 && $hidCartConfig) || !$this->customer->isCustomerAllowedToAdd_cart ) {
                $viewAddToCart = false;
            }
                
            $url =  $this->url->link('product/product&product_id=' . $value->product_id, '', 'SSL');
            // $price = (float)$value->price <= 0 ? $product['price'] : $value->price;
            $item_order = $key+1;	
            //using product_id in index to avoid duplicate results 
            $lableb_products[] = [
                'product_id'    => $product['product_id'],
                'href'          => $url . "&lableb_search=1&query=" . $search_options['search_text'] . "&item_order=" . $item_order,
                'name'          => $value->title,
                'description'   => $value->description,
                'image'         => $product['image'] ?? 'no_image.jpg',
                // 'price'         => number_format($price, 2) . $this->config->get("config_currency"),
                'price'         => $price,
                'special'       => $special,
				'tax'           => $tax,
                // 'quantity'      => $value->quantity,
                'status'        => $value->status,
                'rating'        => $product['rating'] ?? 0,
                'reviews'       => sprintf($this->language->get('text_reviews'), (int)$product['reviews']),
                // for saving percentage
                'saving'	    => $savingAmount,
                'stock_status'  => $stock_status,
                'stock_status_id' => $product['stock_status_id'],
                'quantity'        => $product['quantity'],
                'manufacturer_id' => $product['manufacturer_id'],
                'manufacturer' => $product['manufacturer'],
                'manufacturerimg' => $product['manufacturerimg'],
                'manufacturer_href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $product['manufacturer_id']),
                'viewAddToCart' => $viewAddToCart,
                'general_use' => $product['general_use'] ?? '',
                'display_price' =>$product['display_price']
            ];
		}
		
		$lableb_returned_data['count'] = $found_documents;
		$lableb_returned_data['products'] = $lableb_products;
		return $lableb_returned_data;
		
	}
	
	public function feedback($clicked_product,$type='search'){
		$type = $type == 'search' ? 'search' : 'autocomplete';
        $url = $this->host_url . "v2/projects/" . $this->project_name; 
		$url .= "/indices/".self::$indice."/".$type."/feedback/hits?apikey=". $this->api_key;

        $response   = $this->_sendRequest([
									'type'	  => 'POST',
									'url'     => $url , 
									'data'	  => $clicked_product
									]);
									
		$response	= json_decode($response);
		return $response;
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
	
	private function _isEnglish($text){
		return strlen($text) == mb_strlen($text, 'utf-8');
	}



}
