<?php

class ModelPaymentBenefit extends Model{

	/**
	 * @const strings Gateway Name.
	 */
    public const GATEWAY_NAME = 'benefit';

	/**
	 * get method metadata code, title, and sort order
	 */
  	public function getMethod($address, $total) {
		$this->language->load_json("payment/" . self::GATEWAY_NAME);

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get("{$this->paymentName}_geo_zone_id") . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if (!$this->config->get(self::GATEWAY_NAME . "_geo_zone_id")) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = array();
		$current_lang_id = $this->config->get('config_language_id');
		if ($status) {
    		$method_data = array(
      		'code'       => self::GATEWAY_NAME,
      		'title'      => $this->config->get('benefit_gateway_display_name')[$current_lang_id] ?: $this->language->get('text_title'),
			'sort_order' => $this->config->get(self::GATEWAY_NAME . "_sort_order")
    		);
	  	}
    	return $method_data;
  	}


  	public function getResourcePath(){
        $path = DIR_ONLINESTORES . 'ecdata/stores/' . STORECODE . '/temp/';
        
        if( !is_dir($path) ){
          mkdir($path);
        }
        return $path;
  	}

    public function pay($order_id, $total){
        $resource_cgn_content = base64_decode($this->config->get('benefit_auth_files_resource'));
        $keystore_pooh_content = $this->config->get('benefit_auth_files_keystore_pooh');

        $benefit_alias_name = $this->config->get('benefit_alias_name');
        $init_files_path = $this->getResourcePath();

        //save files from db to temp dir
        file_put_contents($init_files_path.'resource.cgn', $resource_cgn_content);
        file_put_contents($init_files_path.'keystore.pooh', $keystore_pooh_content);

  		  $myObj = new iPayBenefitPipe();

        // Do NOT change the values of the following parameters at all.
    	  $myObj->setaction("1");
    	  $myObj->setcurrency("048");
    	  $myObj->setlanguage("USA");
    	  $myObj->settype("D");

        // modify the following to reflect your "Alias Name", "resource.cgn" file path, "keystore.pooh" file path.
      	$myObj->setalias($benefit_alias_name);
        
        //A Path to extract plugin files (.cgz, .xml)
        $myObj->setresourcePath($init_files_path); //only the path that contains the file; do not write the file name
        $myObj->setkeystorePath($init_files_path); //only the path that contains the file; do not write the file name
        //Load auth files content form DB
//        $myObj->setResourceContent($resource_cgn_content); //only the path that contains the file; do not write the file name
//        $myObj->setKeystoreContent($keystore_pooh_content); //only the path that contains the file; do not write the file name

        // modify the following to reflect your pages URLs
    		$myObj->setresponseURL(HTTPS_SERVER . 'index.php?route=payment/benefit/response');
    		$myObj->seterrorURL(HTTPS_SERVER . 'index.php?route=payment/benefit/error');

        // set a unique track ID for each transaction so you can use it later to match transaction response and identify transactions in your system and “BENEFIT Payment Gateway” portal.
        $track_id = $order_id . '-' . time();
      	$this->data->session['benefit_track_id'] = $track_id;
        $myObj->settrackId($track_id);

        // set transaction amount
      	$myObj->setamt($total);

      	// The following user-defined fields (UDF1, UDF2, UDF3, UDF4, UDF5) are optional fields.
      	// However, we recommend setting theses optional fields with invoice/product/customer identification information as they will be reflected in “BENEFIT Payment Gateway” portal where you will be able to link transactions to respective customers. This is helpful for dispute cases.
      	$myObj->setudf2(STORECODE);
      	$myObj->setudf2("set value 2");
      	$myObj->setudf3("set value 3");
      	$myObj->setudf4("set value 4");
      	$myObj->setudf5("set value 5");

        if(trim($myObj->performPaymentInitializationHTTP())!=0){
          $error = $myObj->geterror();
      	}
      	else {
          $url    = $myObj->getwebAddress();
      	}

    		return [
    			'payment_url'   => $url,
    			'error'         => $error
    		];
    }
}
