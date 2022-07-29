<?php

class ModelModuleLike4card extends Model  {	
	
    public function getLike4cardProductId($product_id)
    {
        $query = $this->db->query("SELECT product_like4card_id FROM " . DB_PREFIX . "likeforcard_to_product  
         WHERE product_id ='".(int)$product_id."'");
        return $query->row['product_like4card_id'];

    }
   
    public function saveSerials($like4card_order,$order_id)
    {
      $dateNow = date('Y-m-d H:i:s');
      $order_data="Like4card Order Details: <br>";
      foreach($like4cardOrders as $like4card )
		{ 
	
      $order_data.= "Order Id = ".$like4card['orderNumber']."<br>"
                    ."serial Id: ".$like4card['serials'][0]['serialId']."<br>"
                    ."serial Code: ".$like4card['serials'][0]['serialCode']."<br>"
                    ."serial Number: ".$like4card['serials'][0]['serialNumber']."<br>"
                    ."valid To: ".$like4card['serials'][0]['validTo']."<br>";
    }

      $this->db->query("UPDATE `" . DB_PREFIX . "order` SET comment='".$order_data."', date_modified = "."'$dateNow'"." WHERE order_id = '" . (int)$order_id . "'");
    }

    public function createOrder($products, $order_id)
    {
    	$time = time(); 
    	$hash = $this->generateHash($time);
      $package_items=[];
      $this->load->model("checkout/order");
     
       $data = 'deviceId='.$this->config->get('like4card_device_id').
               '&email='.$this->config->get('like4card_email').
               '&password='. $this->config->get('like4card_password').
               '&securityCode='. $this->config->get('like4card_security_code').
               '&langId=1
                &products= '.$products.
                '&time='.$time.
               '&hash='.$hash.
               '&referenceId='.$order_id;

        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://taxes.like4app.com/online/create_order/bulk",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $data,
        ));
     
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response, true);
  
       $data = 'deviceId='.$this->config->get('like4card_device_id').
      '&email='.$this->config->get('like4card_email').
      '&password='. $this->config->get('like4card_password').
      '&securityCode='. $this->config->get('like4card_security_code').
      '&langId=1
       &bulkOrderId='.$response['bulkOrderId'];

        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://taxes.like4app.com/online/get_bulk_order",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $data,
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response, true);
        return $response;
    }

    private function generateHash($time){
	  	$email = strtolower($this->config->get('like4card_email'));
	  	$phone = $this->config->get('like4card_phone');
	  	$key = $this->config->get('like4card_hash_key');
	  	return hash('sha256',$time.$email.$phone.$key);
	 }

 
}


