<?php
class ModelShippingFds extends Model {

	private $shipping_key = 'fds';

	function getQuote($address) {
		$this->language->load_json('shipping/fds');
		$fds = $this->getSettings();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$fds['geo_zone_id'] . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
	
		if (!$fds['geo_zone_id']) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = array();
	
		if ($status) {
			$quote_data = array();
			
      		$quote_data['fds'] = array(
        		'code'         => 'fds.fds',
        		'title'        => $this->language->get('text_description'),
        		'cost'         => $fds['cost'],
        		'tax_class_id' => $fds['tax_class_id'],
				'text'         => $this->currency->format($this->tax->calculate($fds['cost'], $fds['tax_class_id'], $this->config->get('config_tax')))
      		);

            //Check FDS Required Data
            $error = false;
            if(!$fds['token'])
                $error .= '- FDS Token Missed! - <br/>';

            if(!$fds['lat'] || !$fds['lng'] || !$fds['location'])
                $error .= '- Store Location Missed! - <br/>';

            $this->load->model('checkout/order');
            $order_id = $this->session->data['order_id'];
            $order_info = $this->model_checkout_order->getOrder($order_id);
            $shipping_address_location = json_decode($order_info['shipping_address_location'], true);
            if(!$shipping_address_location){
                $payment_address_location = json_decode($order_info['payment_address_location'], true);
                if (!$payment_address_location){
                    $error .= '- Address Map Location Missed! - <br/>';
                }
            }
            //////////////////////////////

      		$method_data = array(
        		'code'       => 'fds',
        		'title'      => $this->language->get('text_title'),
        		'quote'      => $quote_data,
				'sort_order' => $this->config->get('flat_sort_order'),
        		'error'      => $error
      		);
		}
	
		return $method_data;
	}

	 /**
     * Return shipping settings group using the key string.
     *
     * @return array|bool
     */
    public function getSettings(){
        return $this->config->get($this->shipping_key);
    }

    //FDS Auth
    private function fds_login(){
    	$settings = $this->getSettings();
    	
        $curl = curl_init();
	    curl_setopt($curl, CURLOPT_URL,"http://www.alwalaatour.net/tours/WebServices/login");
	    curl_setopt($curl, CURLOPT_POST, 1);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(array('api_key' => '11','api_secret' => '11')));
	    $result = curl_exec($curl);
	    curl_close($curl);
	    $authresult = json_decode($result, true);
	    if($authresult['result'] == 0 && $authresult['message'] == 'Done' && $authresult['data']['token'])
	    	return $authresult['data']['token'];

	    return false;
    }

    public function create($order_id){
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($order_id);
        
        $is_cod = (stripos($order_info['payment_code'], 'cod') !== false) ? true : false;

        $settings = $this->getSettings();

        $fds_token = $settings['token'];
        /*$fds_token = $this->fds_login();*/

        $this->load->model('account/order');
        $order_products = $this->model_account_order->getOrderProducts($order_id, ['weight']);
        
        
        $products = array();
        $total_no_shipment = 0;
        $total_weight = 0;

        $product_list = '';
        $quantity = 0;
        $this->load->model('catalog/product');
        foreach ($order_products as $product) {
            $product_info = $this->model_catalog_product->getProduct($product['product_id']);

            $product_list .= $product['name']. ': ('.$product['quantity'].'),';
            $quantity += $product['quantity'];
            $total_weight += ($product['quantity'] * $product['weight']);
            $total_no_shipment += $product['total'];
        }

        if ($this->customer->isLogged()) {
			//$data['customer_id'] = $this->customer->getId();
			//$data['customer_group_id'] = $this->customer->getCustomerGroupId();
			//$data['firstname'] = $this->customer->getFirstName();
			//$data['lastname'] = $this->customer->getLastName();
			//$data['email'] = $this->customer->getEmail();
			$telephone = $this->customer->getTelephone();
            if (empty($data['telephone'])) {
                $telephone = $order_info['telephone'] ? $order_info['telephone']:  $this->session->data['payment_address']['telephone'];
            }
			//$data['fax'] = $this->customer->getFax();
		} else {
            $telephone = $order_info['telephone'] ? $order_info['telephone']:  $this->session->data['payment_address']['telephone'];
			
		}

        /*$checkout_date = explode(' ', $order_info['date_added']);

        $lang_id = 1;
        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();
        if($languages['en']['language_id'])
            $lang_id = $languages['en']['language_id'];

        $this->load->model('localisation/country');
        $order_info['payment_country']  = $this->model_localisation_country->getCountry($order_info['payment_country_id'], $lang_id);
        $order_info['shipping_country'] = $this->model_localisation_country->getCountry($order_info['shipping_country_id'], $lang_id);*/
        $payment_address_location = json_decode($order_info['payment_address_location'], true);
        
        $data = array(
            'token'         => $fds_token,
            'weight'        => $total_weight,
            'phone'         => $telephone,
            'price'         => ceil($total_no_shipment),
            'to_location'   => $order_info['shipping_address_1']. ', '.$order_info['shipping_city']. ', '.$order_info['shipping_country'],
            'to_lat'        => $payment_address_location['lat'],
            'to_lng'        => $payment_address_location['lng'],
            'delivery_date' => date('Y-m-d'),
            'order_number'  => $order_info['order_id'],
            'product'       => $product_list,
            'quantity'      => $quantity,
            'from_location' => $settings['location'],
            'from_lat'      => $settings['lat'],
            'from_lng'      => $settings['lng']
        );
        
        $postdata = http_build_query($data);
        
        $curl = curl_init();
	    curl_setopt($curl, CURLOPT_URL,"http://services.field-solution.co/backend/client/shipment/create");
	    curl_setopt($curl, CURLOPT_POST, 1);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	    curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
	    $result = curl_exec($curl);

	    curl_close($curl);
	    $shippresult = json_decode($result, true);

        if (isset($shippresult['result_code'])) {

        	$details['message']['en']   = $shippresult['message']['en'];
        	$details['message']['ar']   = $shippresult['message']['ar'];
        	$query = $fields = [];

        	//Success Creat Shipping
        	if($shippresult['result_code'] == 0){
        		$details = $shippresult['list'][0]['tracking_number'];	            
	            $query[] = 'INSERT INTO ' . DB_PREFIX . '`shipments_details` SET';
	            $fields[] = 'order_id="' . $order_id . '"';
	            $fields[] = 'details=\'' . $details . '\'';
	            $fields[] = 'shipment_status="1"';
	            $fields[] = 'shipment_operator="fds"';

                //Create tracking link
                $track_url = $this->url->link('shipping/fds/tracking&trackingnumber='.$details, '', 'SSL');
                $this->db->query("UPDATE " . DB_PREFIX . "`order` SET shipping_tracking_url = '" . $this->db->escape($track_url) . "' WHERE order_id = '" . (int)$order_id . "'");
        	}else{
        		//Fail Creat Shipping
        		$query[] = 'INSERT INTO ' . DB_PREFIX . '`shipments_details` SET';
	            $fields[] = 'order_id="' . $order_id . '"';
	            $fields[] = 'details=\'' . json_encode($details) . '\'';
	            $fields[] = 'shipment_status="0"';
	            $fields[] = 'shipment_operator="fds"';
        	}	
        	
            $query[] = implode(', ', $fields);
            $this->db->query(implode(' ', $query));
        }
    }
}
?>