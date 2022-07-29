<?php
class ControllerAccountOrder extends Controller
{
	private $error = array();

	public function index()
	{
    	if (!$this->customer->isLogged()) {
      		$this->session->data['redirect'] = $this->url->link('account/order', '', 'SSL');

	  		$this->redirect($this->url->link('account/login', '', 'SSL'));
        }
        
        $this->data['taps'] = $this->getChild('account/taps/renderTaps', 'account-order');

		$this->language->load_json('account/order', $this->identityAllowed());

		$this->load->model('account/order');

		if (isset($this->request->get['order_id'])) {
			$order_info = $this->model_account_order->getOrder($this->request->get['order_id']);

			if ($order_info) {
				$order_products = $this->model_account_order->getOrderProducts($this->request->get['order_id']);

				foreach ($order_products as $order_product) {
					$option_data = array();

					$order_options = $this->model_account_order->getOrderOptions($this->request->get['order_id'], $order_product['order_product_id']);

					foreach ($order_options as $order_option) {
						if ($order_option['type'] == 'select' || $order_option['type'] == 'radio') {
							$option_data[$order_option['product_option_id']] = $order_option['product_option_value_id'];
						} elseif ($order_option['type'] == 'checkbox') {
							$option_data[$order_option['product_option_id']][] = $order_option['product_option_value_id'];
						} elseif ($order_option['type'] == 'text' || $order_option['type'] == 'textarea' || $order_option['type'] == 'date' || $order_option['type'] == 'datetime' || $order_option['type'] == 'time') {
							$option_data[$order_option['product_option_id']] = $order_option['value'];
						} elseif ($order_option['type'] == 'file') {
							$option_data[$order_option['product_option_id']] = $this->encryption->encrypt($order_option['value']);
						}
					}

					$this->session->data['success'] = sprintf($this->language->get('text_success'), $this->request->get['order_id']);

					$this->cart->add($order_product['product_id'], $order_product['quantity'], $option_data);
				}

				$this->redirect($this->url->link('checkout/cart'));
			}
		}

		if(isset($this->request->get['msg'])){
			if($this->request->get['msg'] == $this->language->get('text_cancellation_success')){
				$this->data['success'] = $this->request->get['msg'];
			}else if($this->request->get['msg'] == $this->language->get('text_cancellation_error')){
				$this->data['error'] = $this->request->get['msg'];
			}
		}

    	$this->document->setTitle($this->language->get('heading_title'));

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


		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		//Custome Invoice
		$this->load->model('multiseller/status');
        $multiseller = $this->model_multiseller_status->is_installed();

        $this->data['custom_invoice_ostatus'] = false;
        if($multiseller) 
        {
        	$this->data['custom_invoice_ostatus'] = $this->model_multiseller_status->is_custom_invice_installed(true);
        }
        ///////////////

		$this->load->model('localisation/order_status');
		//check if customer order flow installed 
		$customer_order_flow_settings = $this->config->get('customer_order_flow');
		$customer_order_flow_app_installed = \Extension::isInstalled('customer_order_flow') && $customer_order_flow_settings['status'];
		if($customer_order_flow_app_installed){
			$this->data['customer_order_flow_app_installed'] = $customer_order_flow_app_installed ;
			if($customer_order_flow_settings['reorder_orders_status']){
				$this->data['orders_reordring_statues'] = $customer_order_flow_settings['orders_reordring_statues'];
			}
			if($customer_order_flow_settings['cancel_orders_status']){
				$this->data['orders_cancellation_statues'] = $customer_order_flow_settings['orders_cancellation_statues'];
			}
			if($customer_order_flow_settings['archiving_orders_status']){
				$this->data['archiving_orders_status'] = $customer_order_flow_settings['archiving_orders_status'];
				$all_orders_statuses = $this->model_localisation_order_status->getOrderStatuses();
				$current_order_statues = array();
				foreach($all_orders_statuses as $k=>$v){
					if(!in_array($v['order_status_id'] , $customer_order_flow_settings['orders_archiving_statues'])){
						array_push($current_order_statues , $v['order_status_id']);
					}
				}
			}
		}
	
		$this->data['orders'] = array();

		$order_total = $this->model_account_order->getTotalOrders();

		$results = $this->model_account_order->getOrders(($page - 1) * 10, 10);
		
		foreach ($results as $result) {
			$product_total = $this->model_account_order->getTotalOrderProductsByOrderId($result['order_id']);
			$voucher_total = $this->model_account_order->getTotalOrderVouchersByOrderId($result['order_id']);

			$this->data['orders'][] = array(
				'order_id'   => $result['order_id'],
				'name'       => $result['firstname'] . ' ' . $result['lastname'],
				'order_status_id' => $result['order_status_id'],
				'status'     => $result['status'],
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'products'   => ($product_total + $voucher_total),
				'total'      => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
				'href'       => $this->url->link('account/order/info', 'order_id=' . $result['order_id'], 'SSL'),
				'reorder'    => $this->url->link('account/order', 'order_id=' . $result['order_id'], 'SSL'),
				'cancel'     => $this->url->link('account/order/cancel', 'order_id=' . $result['order_id'], 'SSL'), 
				'order_case' => in_array($result['order_status_id'],$current_order_statues) ? 'current': (in_array($result['order_status_id'], $customer_order_flow_settings['orders_archiving_statues']) ? 'completed' : ''),
				'order_custom_invoice' => $this->url->link('account/order/custom_order', 'order_id=' . $result['order_id'], 'SSL'),
				'is_returned' => $this->model_account_order->orderIsReturned($result['order_id'])
			);
		}

		if($customer_order_flow_app_installed && $customer_order_flow_settings['archiving_orders_status']){
			$completed_orders = array();
			$current_orders   = array();
			foreach($this->data['orders'] as $key => $value){
				if($value['order_case'] == 'current'){
					array_push($current_orders , $value);
				}else if($value['order_case'] == 'completed'){
					array_push($completed_orders , $value);
				}
			}
			$this->data['completed_orders'] = $completed_orders;
			$this->data['current_orders'] = $current_orders ;
		}

		//Get products custom title if any.
		$this->load->model('seller/seller');
		$products_title = $this->model_seller_seller->getProductsTitle();
		$this->data['text_products'] = sprintf( $this->language->get('text_products'), $products_title);


		$pagination = new Pagination();
		$pagination->total = $order_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('account/order', 'page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['continue'] = $this->url->link('account/account', '', 'SSL');

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/order_list.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/order_list.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/account/order_list.expand';
        }

		$this->children = array(
			'common/footer',
			'common/header'
        );
        
        if ($this->identityAllowed()) {            
            // This is to handle new template structure using extend
            $this->data['include_page'] = 'order_list.expand';
            if(USES_TWIG_EXTENDS == 1)
                $this->template = 'default/template/account/layout_extend.expand';
            else
                $this->template = 'default/template/account/layout_default.expand';
            ///////////
        }

        $this->response->setOutput($this->render_ecwig());
	}

	public function info()
	{
		$this->language->load_json('account/order', $this->identityAllowed());

		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/order/info', 'order_id=' . $order_id, 'SSL');

			$this->redirect($this->url->link('account/login', '', 'SSL'));
    	}

		$this->load->model('account/order');

		$order_info = $this->model_account_order->getOrder($order_id);

		if ($order_info) {
			$this->document->setTitle($this->language->get('text_order'));

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
				'text'      => $this->language->get('text_order'),
				'href'      => $this->url->link('account/order/info', 'order_id=' . $this->request->get['order_id'] . $url, 'SSL'),
				'separator' => $this->language->get('text_separator')
			);


			if ($order_info['invoice_no']) {
				$this->data['invoice_no'] = $order_info['invoice_prefix'] . $order_info['invoice_no'];
			} else {
				$this->data['invoice_no'] = '';
			}

			$this->data['order_id'] = $this->request->get['order_id'];
			$this->data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));

			// Return errors
			if (isset($this->session->data['return_errors'])) {
				$this->data['return_errors'] = $this->session->data['return_errors'];
			}

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
				///////seller_based
			$this->data['seller_based'] = false;
		if ($this->MsLoader->isInstalled() && \Extension::isInstalled('seller_based') && $this->config->get('seller_based_status') == 1){
			$this->load->model('shipping/seller_based');
			$sellerCartProductsData = json_decode($this->model_shipping_seller_based->getShipmentDetails($this->request->get['order_id'])['details'], true);
			$products = $sellerCartProductsData['products'];
			$this->data['seller_names']=$sellerCartProductsData['seller_names'];
			$this->data['products_prices']  = $sellerCartProductsData['products_total_Price'];
			$this->data['selectedShippingMethodName']  = $sellerCartProductsData['selectedShippingMethodName'];			
			$this->data['selectedShippingMethodValue']  = $sellerCartProductsData['selectedShippingMethodValue'];			
			$this->data['totalAfterShippingCost']  = $sellerCartProductsData['totalAfterShippingCost'];
			$this->data['seller_based'] = true;
		}else{
			$products = $this->model_account_order->getOrderProducts($this->request->get['order_id']);
		}
            $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

            if($queryMultiseller->num_rows) {
                $this->load->model('localisation/order_status');
                $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
            }



			//Check if Product Video Links App installed, Then get Video information to form
			$this->load->model('module/product_video_links');
			$this->load->model('tool/image');

		    $product_video_links_installed = $this->model_module_product_video_links->isInstalled();
		    $product_video_links_Order_status = $this->model_module_product_video_links->getOrderStatus();

		    $show_videos_links = $product_video_links_installed && $product_video_links_Order_status === $order_info['order_status_id'];
		    $this->data['show_videos_links'] = $show_videos_links;

            $this->data['return_freeze_status'] = $this->config->get('config_return_freeze_statuses');

            // check if allow returning order
            if (in_array($order_info['order_status_id'], $this->data['return_freeze_status'])) {
                $allowReturn = false;
            } else {
                $allowReturn = true;
            }

      		foreach ($products as $product) {

                if($queryMultiseller->num_rows) {
                    $seller_id = $this->MsLoader->MsProduct->getSellerId($product['product_id']);
                }

				$option_data = array();

				$options = $this->model_account_order->getOrderOptions($this->request->get['order_id'], $product['order_product_id']);

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

				//Set the product price into a temp var
				$productPrice = $product['price'];
				
				//Check the config for the tax settings
				switch($this->config->get('config_tax')){
					case 'price':
						$productPrice = $product['price'];
					break;
					case 'price_with_tax_value':
						$productPrice = $product['price'] + $product['tax'];
					break;
					case 'price_with_tax_text':
						$productPrice = $product['price'] + $product['tax'];
					break;
					default:
						$productPrice = $product['price'];
					break;
				}

                $returnUrl = 'javascript:void(0);';

                if ($allowReturn == true) {
                    $returnUrl = $this->url->link(
                        'account/return/insert',
                        'order_id=' . $order_info['order_id'] . '&product_id=' . $product['product_id'],
                        'SSL'
                    );
                }
				
				$product_data = [
				    'product_id' => $product['product_id'],
                    'order_status_id' => $queryMultiseller->num_rows ? $this->model_localisation_order_status->getSuborderStatusId($order_id, $seller_id) : 0,
          			'name'     => $product['name'],
          			'model'    => $product['model'],
          			'option'   => $option_data,
          			'quantity' => $product['quantity'],
					'price'    => $this->currency->format($productPrice, $order_info['currency_code'], $order_info['currency_value']),
					'total'    => $this->currency->format($product['total'], $order_info['currency_code'], $order_info['currency_value']),
					'return'   => $returnUrl,
					'rentData' => $product['rent_data'],
					'seller' => $product['seller'] ? $product['seller'] : '',
					'price_meter_data'   => $product['price_meter_data'],
					'image'		=>	$this->model_tool_image->resize($product['image'],80,80)
                ];

				if($show_videos_links){
		            $this->load->model('catalog/product');
		            $video_info = $this->model_catalog_product->getVideoURLInfo($product['product_id']);

                  	$product_data = array_merge($product_data, ['external_video_url' =>  $video_info['external_video_url']]);
				}

				$this->data['products'][] = $product_data;

			  }

            $this->data['allow_return'] = $allowReturn;
			
			// Check if there is products are returned from customer before.
			$this->load->model('account/return');
			$returned_products = $this->model_account_return->getOrderProductsReturn($this->request->get['order_id']);

			if(count($returned_products) > 0){
				foreach ($returned_products as $returned_product) {
					$this->data['returned_products'][$returned_product['product_id']]=$returned_product['status'];	
				}
			}
			
			//////////////////////////////////////////////////////////////////
			// Voucher
			$this->data['vouchers'] = array();

			$vouchers = $this->model_account_order->getOrderVouchers($this->request->get['order_id']);

			foreach ($vouchers as $voucher) {
				$v_quantity = $voucher['quantity'];

				$this->data['vouchers'][] = array(
					'description' => $voucher['description'],
					'quantity' => $v_quantity,
					'amount'      => $this->currency->format( ($voucher['amount'] * $v_quantity) , $order_info['currency_code'], $order_info['currency_value'])
				);
			}

      		$this->data['totals'] = $this->model_account_order->getOrderTotals($this->request->get['order_id']);

			$this->data['comment'] = nl2br($order_info['comment']);

			$this->data['histories'] = array();

			
			// ATH was here at 16:15 2-4-2019

			$this->data['awb_no'] = $this->handleZajil_awb_info($order_id);

			$this->load->model('shipping/smsa');
			
			if($this->model_shipping_smsa->checkIfSMSAIsInstalled()){
				$smsa_shippment_info = $this->model_shipping_smsa->getShipmentInfo($this->request->get['order_id']);

				if($shippment_info !== false && !empty($shippment_info['shipment_code'])){
					$this->data['awb_url']	=	$this->url->link(
						'account/order/smsa_label', 
						'order_id=' . $this->request->get['order_id'], 
						'SSL');
				}	
			}
			
            $this->load->model('aramex/aramex');

			$checkAWB = $this->model_aramex_aramex->checkAWB($this->request->get['order_id']);

			if($checkAWB)
            {
                $this->data['track_url'] = $this->url->link('account/aramex_traking', 'order_id=' . $this->request->get['order_id'], 'SSL');
				$this->data['awb_url']	=	$this->url->link(
					'account/order/aramex_label', 
					'order_id=' . $this->request->get['order_id'], 
					'SSL');
			}else{
                $this->data['track_url'] = "";
            }
			
			// Manual shipping tracking url
			
			if($order_info['shipping_tracking_url'])
				$this->data['shipping_tracking_url'] = $order_info['shipping_tracking_url'];
				
			////////////////////////////////
			$results = $this->model_account_order->getOrderHistories($this->request->get['order_id']);

      		foreach ($results as $result) {
        		$this->data['histories'][] = array(
          			'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
          			'status'     => $result['status'],
          			'comment'    => nl2br($result['comment'])
        		);
      		}

			// Get the last order history
			$return_type = $this->data['return_type'] = $this->config->get('config_return_type') ? $this->config->get('config_return_type') : "return";
			$complete_status_id = $this->config->get('config_return_status_id');
			
			if( 
				($return_type == 'return' && $result['order_status_id'] == $complete_status_id)
				|| 
				($return_type == 'cancel' && $result['order_status_id'] != $complete_status_id)
			){

                // $return_limit = $this->config->get('config_return_limit') ? $this->config->get('config_return_limit') : "";
				$return_limit = (int)$this->config->get('config_return_limit');

				if ($return_limit !== 0) {
					$return_unit = $this->config->get('config_return_limit_unit') ? $this->config->get('config_return_limit_unit') : "day";

					$return_limit_date = strtotime(
                        '+' . $return_limit . ' ' . $return_unit,
                        strtotime($result['date_added'])
                    );
					$today = time();
					if($today > $return_limit_date) {
						$this->data['return_limit'] = true;
                    }
				} else {
                    $this->data['return_limit'] = true;
                }
			}

			/////////////////////////// custom_fields
			$this->load->model('module/quickcheckout_fields');
			$this->data['custom_fields'] = $this->model_module_quickcheckout_fields->getOrderCustomFields($order_id);
			///////////////////

			// check if delivery slot app installed
			$delivery_slot = $this->config->get('delivery_slot');
			if(is_array($delivery_slot) && count($delivery_slot) > 0){
				$this->language->load('module/delivery_slot');

				$this->load->model('module/delivery_slot/slots');
				$orderSlot = $this->model_module_delivery_slot_slots->getOrderDeliverySlot($order_id);
				$this->data['delivery_slot'] = true;
				$this->data['order_delivery_slot'] = $orderSlot;
			}

      		$this->data['continue'] = $this->url->link('account/order', '', 'SSL');

            if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/order_info.expand')) {
                $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/order_info.expand';
            }
            else {
                $this->template = $this->config->get('config_template') . '/template/account/order_info.expand';
            }

			$this->children = array(
			'common/footer',
			'common/header'
		);

 			//get seller title
            $this->load->model('seller/seller');
            $product_title = $this->model_seller_seller->getProductTitle();
            $this->data['column_name'] = sprintf( $this->language->get('column_name') , $product_title );

			$this->response->setOutput($this->render_ecwig());
    	} else {
			$this->document->setTitle($this->language->get('text_order'));


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
				'text'      => $this->language->get('text_order'),
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
	  

	function handleZajil_awb_info($order_id){

		if(! (bool) $this->config->get('zajil_status') ){

			return  "";

		}

		$awb_info= $this->db->query("SELECT awb FROM ".DB_PREFIX."zajil WHERE order_id='". $order_id."'")->row;

		if($awb_info){
			
			return  $awb_info['awb'];
			 
		}else{
				
			return  "";
		
		}
	}

  // Place Custom Invoice Order
	public function custom_order(){
		$order_id = $this->request->get['order_id'];

		if(!$order_id)
			$this->redirect($this->url->link('account/order', '', 'SSL'));
		
		$this->load->model('account/voucher');
		$vouchers = $this->model_account_voucher->getVouchersByOrderId($order_id);

		if(count($vouchers)){
			$this->session->data['order_id'] = $order_id;
			$this->session->data['vouchers'] = [];
			
			foreach ($vouchers as $voucher) {
				$this->session->data['vouchers'][mt_rand()] = array(
					'description'      => $voucher['description'],
					'to_name'          => $voucher['to_name'],
					'to_email'         => $voucher['to_email'],
					'from_name'        => $voucher['from_name'],
					'from_email'       => $voucher['from_email'],
					'voucher_theme_id' => $voucher['voucher_theme_id'],
					'message'          => $voucher['message'],
					'amount'           => $this->currency->convert($voucher['amount'], $this->currency->getCode(), $this->config->get('config_currency')),
					'quantity' => $voucher['quantity']
				);
			}
			
			$this->redirect($this->url->link('checkout/cart', '', 'SSL'));
		}

		$this->redirect($this->url->link('account/order', '', 'SSL'));		
	} 

	public function aramex_label()
	{
		$this->load->model('aramex/aramex');

		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}
		
		
		if($order_id)
		{
			$awbno = $this->model_aramex_aramex->getAWB($order_id, 0, 100);
			$baseUrl = $this->model_aramex_aramex->getWsdlPath();
			$soapClient = new SoapClient($baseUrl . '/shipping.wsdl');
			$clientInfo = $this->model_aramex_aramex->getClientInfo();
				
			if($awbno)
			{		
				$report_id = ($this->config->get('aramex_report_id'))?$this->config->get('aramex_report_id'):0;
				if(!$report_id){
					$report_id =9729;
				}
				
				$params = array(		
			
					'ClientInfo'  			=> $clientInfo,

					'Transaction' 			=> array(
												'Reference1'			=> $order_id,
												'Reference2'			=> '', 
												'Reference3'			=> '', 
												'Reference4'			=> '', 
												'Reference5'			=> '',									
											),
					'LabelInfo'				=> array(
												'ReportID' 				=> $report_id,
												'ReportType'			=> 'URL',
					),
				);
				$params['ShipmentNumber'] = $awbno;
			
				try {
					$auth_call = $soapClient->PrintLabel($params);
					
					/* bof  PDF demaged Fixes debug */				
					if($auth_call->HasErrors){
						if(count($auth_call->Notifications->Notification) > 1){
							foreach($auth_call->Notifications->Notification as $notify_error){
								$this->data['eRRORS'][] = 'Aramex: ' . $notify_error->Code .' - '. $notify_error->Message;
							}
						} else {
							$this->data['eRRORS'][] = 'Aramex: ' . $auth_call->Notifications->Notification->Code . ' - '. $auth_call->Notifications->Notification->Message;
							
						}					
					}
					/* eof  PDF demaged Fixes */					
					$filepath=$auth_call->ShipmentLabel->LabelURL;

					$agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13';
					$filepath = str_replace("http://","https://",$filepath);
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_URL, $filepath);
					curl_setopt($ch,CURLOPT_USERAGENT,$agent);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

					$name="{$order_id}-shipment-label.pdf";
					header('Content-type: application/pdf');
					header('Content-Disposition: attachment; filename="'.$name.'"');

					$result = curl_exec($ch);
					if($result==false)
					{ readfile($filepath); }
					else
					{ echo $result; }

					exit();					
				} catch (SoapFault $fault) {					
					$this->data['eRRORS'][] = 'Error : ' . $fault->faultstring;
					
				}
				catch (Exception $e) {
					
					$this->data['eRRORS'][] = $e->getMessage();
				
				}
			}
			else{
					$this->data['eRRORS'][] = 'Shipment is empty or not created yet.';
			}
		}
		else{
					$this->data['eRRORS'][] = 'This order no longer exists.';
		}
	}

	public function smsa_label()
	{
		$this->init([
            'shipping/smsa',
        ]);

        $settings = $this->smsa->getSettings();

		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

        if ( isset($settings['smsa_status']) && $settings['smsa_status'] == 1 ){
            //$orderInfo = $this->smsa->getOrderInfo($orderId);
            $this->smsa->setWsdl($settings['smsa_wsdl']);

            if ( $order_id != 0 ){

                if ( $this->smsa->wsdl )
                {
					$shippment_info = $this->smsa->getShipmentInfo($order_id);
					
					$this->smsa->setAWB($shippment_info['shipment_code']);
					$this->smsa->setPassKey($settings['smsa_passkey']);

					$base64_file = $this->smsa->getPDF();
					
					$decoded = base64_decode($base64_file);
					$file = 'smsa_shippment_label.pdf';
					file_put_contents($file, $decoded);

					if (file_exists($file)) {
						header('Content-Description: File Transfer');
						header('Content-Type: application/octet-stream');
						header('Content-Disposition: attachment; filename="'.basename($file).'"');
						header('Expires: 0');
						header('Cache-Control: must-revalidate');
						header('Pragma: public');
						header('Content-Length: ' . filesize($file));
						readfile($file);
						exit;
					}
					
					$result_json['error'] = $this->language->get('error_no_order_exists');
                    $this->response->setOutput( json_encode($result_json) );
                    return;

                }
                else
                {
                    $result_json['error'] = $this->language->get('error_invalid_wsdl_or_not_exists');
                    $this->response->setOutput( json_encode($result_json) );
                    return;
                }

            }
            else
            {
                $result_json['error'] = $this->language->get('error_no_order_exists');
                $this->response->setOutput( json_encode($result_json) );
                return;
            }

        }
        else
        {
            $result_json['error'] = $this->language->get('error_enable_the_app');
            $this->response->setOutput( json_encode($result_json) );
            return;
        }

        return;
	}
    
    public function identityAllowed()
    {
        return defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList();
    }

	public function cancel(){
		$order_id  = $this->request->get['order_id'];
		$customer_order_flow_settings = $this->config->get('customer_order_flow');
		$cancel_status_id = $customer_order_flow_settings['cof_cancelled_order_status_id'];
		$this->load->model('account/order');
		$this->language->load_json('account/order');
		$update =  $this->model_account_order->updateOrderStatus($order_id , $cancel_status_id); 
		if($update){
			$msg = $this->language->get('text_cancellation_success');
		}else{
			$msg= $this->language->get('text_cancellation_error');
		}
		$this->redirect($this->url->link('account/order' , 'msg=' . $msg, 'SSL'));
	}
}
?>
