<?php
class ControllerShippingFds extends Controller
{
	public function tracking()
	{
		$this->language->load_json('account/order');

		if (isset($this->request->get['trackingnumber'])) {
			$tracking_number = $this->request->get['trackingnumber'];
		} else {
			$tracking_number = 0;
		}

		$this->load->model('account/order');

		$order_id   = $this->model_account_order->getFdsOrderid($tracking_number);
		$order_info = $this->model_account_order->getOrder($order_id);

		if ($order_info && $tracking_number) {
			$this->document->setTitle($this->language->get('text_fds_tracking'));

			$this->data['breadcrumbs'] = array();

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home'),
				'separator' => false
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_account'),
				'href'      => $this->url->link('account/account', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			);

			$url = '';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('account/order', $url, 'SSL'),
				'separator' => $this->language->get('text_separator')
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_fds_tracking'),
				'href'      => $this->url->link('account/order/info', 'order_id=' . $order_id . $url, 'SSL'),
				'separator' => $this->language->get('text_separator')
			);


			if ($order_info['invoice_no']) {
				$this->data['invoice_no'] = $order_info['invoice_prefix'] . $order_info['invoice_no'];
			} else {
				$this->data['invoice_no'] = '';
			}

			$this->data['order_id'] = $order_id;
			$this->data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));

			if ($order_info['payment_address_format']) {
      			$format = $order_info['payment_address_format'];
    		} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}


			$this->data['payment_telephone'] = $order_info['payment_telephone'];

    		$find = array(
	  			'{firstname}',
	  			'{lastname}',
	  			'{company}',
      			'{address_1}',
      			'{address_2}',
     			'{city}',
      			'{postcode}',
      			'{zone}',
				'{zone_code}',
      			'{country}'
			);

			$replace = array(
	  			'firstname' => $order_info['payment_firstname'],
	  			'lastname'  => $order_info['payment_lastname'],
	  			'company'   => $order_info['payment_company'],
      			'address_1' => $order_info['payment_address_1'],
      			'address_2' => $order_info['payment_address_2'],
      			'city'      => $order_info['payment_city'],
      			'postcode'  => $order_info['payment_postcode'],
      			'zone'      => $order_info['payment_zone'],
				'zone_code' => $order_info['payment_zone_code'],
      			'country'   => $order_info['payment_country']
			);

			$this->data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

      		$this->data['payment_method'] = $order_info['payment_method'];

			if ($order_info['shipping_address_format']) {
      			$format = $order_info['shipping_address_format'];
    		} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}

    		$find = array(
	  			'{firstname}',
	  			'{lastname}',
	  			'{company}',
      			'{address_1}',
      			'{address_2}',
     			'{city}',
      			'{postcode}',
      			'{zone}',
				'{zone_code}',
      			'{country}'
			);

			$replace = array(
	  			'firstname' 		=> $order_info['shipping_firstname'],
	  			'lastname'  		=> $order_info['shipping_lastname'],
	  			'company'   		=> $order_info['shipping_company'],
      			'address_1' 		=> $order_info['shipping_address_1'],
      			'address_2' 		=> $order_info['shipping_address_2'],
      			'city'      		=> $order_info['shipping_city'],
      			'postcode'  		=> $order_info['shipping_postcode'],
      			'zone'      		=> $order_info['shipping_zone'],
				'zone_code' 		=> $order_info['shipping_zone_code'],
      			'country'   		=> $order_info['shipping_country']
			);

			$this->data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

			$this->data['shipping_method'] = $order_info['shipping_method'];

			$this->data['products'] = array();

			$products = $this->model_account_order->getOrderProducts($order_id);

            $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

            if($queryMultiseller->num_rows) {
                $this->load->model('localisation/order_status');
                $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
            }

      		foreach ($products as $product) {

                if($queryMultiseller->num_rows) {
                    $seller_id = $this->MsLoader->MsProduct->getSellerId($product['product_id']);
                }

				$option_data = array();

				$options = $this->model_account_order->getOrderOptions($order_id, $product['order_product_id']);

         		foreach ($options as $option) {
          			if ($option['type'] != 'file') {
						$value = $option['value'];
					} else {
						$value = utf8_substr($option['value'], 0, utf8_strrpos($option['value'], '.'));
					}

					$option_data[] = array(
						'name'  => $option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);
        		}

                if ($product['rent_data']) {
                    $product['rent_data'] = json_decode($product['rent_data'], true);
                    $product['rent_data']['range'] = array_map(function ($value) {
                        return date("Y-m-d", $value);
                    } , $product['rent_data']['range']);
                }

                if ($product['price_meter_data']) {
                    $product['price_meter_data'] = json_decode($product['price_meter_data'], true);
                }

        		$this->data['products'][] = array(
                    'product_id' => $product['product_id'],
                    'order_status_id' => $queryMultiseller->num_rows ? $this->model_localisation_order_status->getSuborderStatusId($order_id, $seller_id) : 0,
          			'name'     => $product['name'],
          			'model'    => $product['model'],
          			'option'   => $option_data,
          			'quantity' => $product['quantity'],
          			'price'    => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
					'total'    => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
					'return'   => $this->url->link('account/return/insert', 'order_id=' . $order_info['order_id'] . '&product_id=' . $product['product_id'], 'SSL'),
                    'rentData' => $product['rent_data'],
                    'price_meter_data' => $product['price_meter_data']
        		);
      		}
			
			// FDS Track api
			$fds_settings = $this->config->get('fds');
			$fds_data = array(
                'token'            => $fds_settings['token'],
                'tracking_number'  => $tracking_number,
                'order_number'     => $order_id
            );
     	
            $postdata = http_build_query($fds_data);
            
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL,"http://services.field-solution.co/backend/client/shipment/search-by-track-or-order");
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
            $fds_result = curl_exec($curl);

            curl_close($curl);
            $track_result = json_decode($fds_result, true);
            
          	$this->data['fds_traking_result'] = 'Tracking Error!';

 	        if (isset($track_result['result_code'])) {
                if($track_result['result_code'] == 0){
                	 if($this->config->get('config_language') == 'ar')
                     	$this->data['fds_traking_result'] = $track_result['message']['ar']; 
                     else
                     	$this->data['fds_traking_result'] = $track_result['message']['en'];
                }else{
                	$message = '';
                	if($this->config->get('config_language') == 'ar')
                     	$message .= $track_result['message']['ar']; 
                     else
                     	$message .= $track_result['message']['en'];

                    if($track_result['flags']){
                        foreach ($track_result['flags'] as $key => $value) {
                            $message .= $key.': '.$value[0].'<br/>';
                        }
                    }
                    $this->data['fds_traking_result'] = $message;
                }   
            }		
			//////////////////////////////////////////////////////////////////

			// Voucher
			$this->data['vouchers'] = array();

			$vouchers = $this->model_account_order->getOrderVouchers($order_id);

			foreach ($vouchers as $voucher) {
				$v_quantity = $voucher['quantity'];
				$this->data['vouchers'][] = array(
					'description' => $voucher['description'],
					'amount'      => $this->currency->format( ($voucher['amount'] * $v_quantity), $order_info['currency_code'], $order_info['currency_value'])
				);
			}

      		$this->data['totals'] = $this->model_account_order->getOrderTotals($order_id);

			$this->data['comment'] = nl2br($order_info['comment']);

			$this->data['histories'] = array();
	
			////////////////////////////////
			$results = $this->model_account_order->getOrderHistories($order_id);

      		foreach ($results as $result) {
        		$this->data['histories'][] = array(
          			'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
          			'status'     => $result['status'],
          			'comment'    => nl2br($result['comment'])
        		);
      		}


      		$this->data['continue'] = $this->url->link('account/order', '', 'SSL');

            if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/order_info.expand')) {
                $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/order_info.expand';
            }
            else {
                $this->template = 'default/template/shipping/fds_tracking.expand';
            }

			$this->children = array(
				'common/footer',
				'common/header'
			);

			$this->response->setOutput($this->render_ecwig());
    	} else {
			$this->document->setTitle($this->language->get('text_fds_tracking'));


			$this->data['breadcrumbs'] = array();

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home'),
				'separator' => false
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_account'),
				'href'      => $this->url->link('account/account', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('account/order', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_fds_tracking'),
				'href'      => $this->url->link('account/order/info', 'order_id=' . $order_id, 'SSL'),
				'separator' => $this->language->get('text_separator')
			);

      		$this->data['continue'] = $this->url->link('account/order', '', 'SSL');

            if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/error/not_found.expand')) {
                $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/error/not_found.expand';
            }
            else {
                $this->template = $this->config->get('config_template') . '/template/error/not_found.expand';
            }

			$this->children = array(
			'common/footer',
			'common/header'
		);

			$this->response->setOutput($this->render_ecwig());
    	}
	}   
}
?>
