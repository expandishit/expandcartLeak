<?php

class ControllerSellerAccountOrder extends ControllerSellerAccount {
	public function getTableData() {
		$colMap = array(
			'customer_name' => 'firstname',
			'date_created' => 'o.date_added',
		);
		
		$sorts = array('order_id', 'customer_name', 'date_created', 'total_amount');
		$filters = array_merge($sorts, array('products'));
		
		list($sortCol, $sortDir) = $this->MsLoader->MsHelper->getSortParams($sorts, $colMap);
		$filterParams = $this->MsLoader->MsHelper->getFilterParams($filters, $colMap);
		
		$seller_id = $this->customer->getId();
		$this->load->model('account/order');

		$orders = $this->MsLoader->MsOrderData->getOrders(
			array(
				'seller_id' => $seller_id,
				// This was commented because we want all the orders to appear..
				// Not only the orders with eligible-for-payment statuses

				// 'order_status' => $this->config->get('msconf_credit_order_statuses')
			),
			array(
				'order_by'  => $sortCol,
				'order_way' => $sortDir,
				'offset' => $this->request->get['iDisplayStart'],
				'limit' => $this->request->get['iDisplayLength'],
				'filters' => $filterParams
			),
			array(
				'total_amount' => 1,
				'products' => 1,
			)
		);
		
		$total_orders = isset($orders[0]) ? $orders[0]['total_rows'] : 0;

		$columns = array();
		foreach ($orders as $order) {
			$order_products = $this->MsLoader->MsOrderData->getOrderProducts(array('order_id' => $order['order_id'], 'seller_id' => $seller_id));
			
			if ($this->config->get('msconf_hide_customer_email')) {
				$customer_name = "{$order['firstname']} {$order['lastname']}";
			} else {
				$customer_name = "{$order['firstname']} {$order['lastname']} ({$order['email']})";
			}

			$products = "";
			foreach ($order_products as $p) {
                $products .= "<p style='text-align:left'>";
				$products .= "<span class='name'>" . ($p['quantity'] > 1 ? "{$p['quantity']} x " : "") . "<a href='" . $this->url->link('product/product', 'product_id=' . $p['product_id'], 'SSL') . "'>{$p['name']}</a></span>";

                $options   = $this->model_account_order->getOrderOptions($order['order_id'], $p['order_product_id']);

                foreach ($options as $option)
                {
                    if ($option['type'] != 'file') {
                        $value = $option['value'];
                    } else {
                        $value = utf8_substr($option['value'], 0, utf8_strrpos($option['value'], '.'));
                    }

                    $option['value']	=  utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value;

                    $products .= "<br />";
                    $products .= "<small> - {$option['name']} : {$option['value']} </small>";
                }

                $products .= "<span class='total'>" . $this->currency->format($p['seller_net_amt'], $this->config->get('config_currency')) . "</span>";
				$products .= "</p>";
			}
			
			$this->load->model('localisation/order_status');
			$order_statuses = $this->model_localisation_order_status->getOrderStatuses();
			$order_status_id = $this->model_localisation_order_status->getSuborderStatusId($order['order_id'], $this->customer->getId());			
			
			$order_status_name = '';
			foreach ($order_statuses as $order_status) {
				if ($order_status['order_status_id'] == $order_status_id) {
					$order_status_name = $order_status['name'];
				}
			}
			if (\Extension::isInstalled('seller_based')&& $this->config->get('seller_based_status') == 1){
				$this->load->model('shipping/seller_based');
				$sellerCartProductsData = json_decode($this->model_shipping_seller_based->getShipmentDetails($order['order_id'])['details'], true);
				$order['total_amount']= $sellerCartProductsData['totalAfterShippingCost'][$seller_id];
			}
			
			$columns[] = array_merge(
				$order,
				array(
					'order_id' => $order['order_id'],
					'customer_name' => $customer_name,
					'products' => $products,
					'suborder_status' => $order_status_name,
					'date_created' => date($this->language->get('date_format_short'), strtotime($order['date_added'])),
					'total_amount' => $this->currency->format($order['total_amount'], $this->config->get('config_currency')),
					'view_order' => '<a href="' . $this->url->link('seller/account-order/viewOrder', 'order_id=' . $order['order_id']) . '" class="ms-button ms-button-view"></a>'
				)
			);
		}
		
		$this->response->setOutput(json_encode(array(
			'iTotalRecords' => $total_orders,
			'iTotalDisplayRecords' => $total_orders,
			'aaData' => $columns
		)));
	}

	public function viewOrder() {
		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}
		$seller_id=$this->customer->getId();
		$this->load->model('seller/seller');
		$seller_title = $this->model_seller_seller->getSellerTitle();
		$this->data['redirect'] = $this->url->link('seller/account-order/viewOrder', 'order_id=' . $order_id, 'SSL');
		if (!$this->customer->isLogged()) {
			$this->redirect($this->url->link('account/login', '', 'SSL'));
		}

		$this->data['breadcrumbs'] = $this->MsLoader->MsHelper->setBreadcrumbs(array(
			array(
				'text' => $this->language->get('text_account'),
				'href' => $this->url->link('account/account', '', 'SSL'),
			),
			array(
				'text' => sprintf( $this->language->get('ms_account_dashboard_breadcrumbs') , $seller_title ),
				'href' => $this->url->link('seller/account-dashboard', '', 'SSL'),
			),
			array(
				'text' => $this->language->get('ms_account_orders_breadcrumbs'),
				'href' => $this->url->link('seller/account-order', '', 'SSL'),
			)
		));
		
		$this->load->model('localisation/order_status');
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		$this->data['order_status_id'] = $this->model_localisation_order_status->getSuborderStatusId($order_id, $this->customer->getId());
		$suborder_id = $this->model_localisation_order_status->getSuborderId($order_id);

		if (isset($this->request->post['order_status_edit'])) {
			$this->model_localisation_order_status->editSuborderStatus($suborder_id, $this->request->post['order_status_edit']);
			$this->redirect($this->data['redirect']);
		}

		$this->load->model('account/order');

		$order_info = $this->model_account_order->getOrder($order_id, 'seller');

		$this->data['products'] = array();
		$products = $this->MsLoader->MsOrderData->getOrderProducts(array( 'order_id' => $order_id, 'seller_id' => $this->customer->getId() ));

		if ($order_info && !empty($products)) {
			$this->document->setTitle($this->language->get('text_order'));

			$this->data = array_merge($this->data, $this->language->load_json('account/order'));

			// get product word as found in seller app
			$products_title = $this->model_seller_seller->getProductsTitle();
			$this->data['column_name'] = sprintf($this->language->get('ms_account_orders_products') , $products_title );

			if ($order_info['invoice_no']) {
				$this->data['invoice_no'] = $order_info['invoice_prefix'] . $order_info['invoice_no'];
			} else {
				$this->data['invoice_no'] = '';
			}

			$this->data['order_id'] = $this->request->get['order_id'];
			$this->data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));
			$this->data['firstname'] = $order_info['firstname'];
			$this->data['lastname'] = $order_info['lastname'];

			$msconf_hide_orderinfo = $this->config->get('msconf_hide_orderinfo');
			$this->data['hide_orderinfo'] = $msconf_hide_orderinfo ? $msconf_hide_orderinfo : 0;

			$types = array("payment", "shipping");

			foreach ($types as $key => $type) {
				if ($order_info[$type . '_address_format']) {
					$format = $order_info[$type . '_address_format'];
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
					'firstname' => $order_info[$type . '_firstname'],
					'lastname'  => $order_info[$type . '_lastname'],
					'company'   => $order_info[$type . '_company'],
					'address_1' => $order_info[$type . '_address_1'],
					'address_2' => $order_info[$type . '_address_2'],
					'city'      => $order_info[$type . '_city'],
					'postcode'  => $order_info[$type . '_postcode'],
					'zone'      => $order_info[$type . '_zone'],
					'zone_code' => $order_info[$type . '_zone_code'],
					'country'   => $order_info[$type . '_country']
				);

				if(!$this->data['hide_orderinfo'])
					$this->data[$type . '_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

				$this->data[$type . '_method'] = $order_info[$type . '_method'];

                $this->data[$type . '_address_location']  = $order_info[$type . '_address_location'];
			}
			if (\Extension::isInstalled('seller_based')&& $this->config->get('seller_based_status') == 1){
				$this->load->model('shipping/seller_based');
				$sellerCartProductsData = json_decode($this->model_shipping_seller_based->getShipmentDetails($this->request->get['order_id'])['details'], true);
				$this->data['shipping_method']  = $sellerCartProductsData['selectedShippingMethodName'][$seller_id];			
		    	$this->data['selectedShippingMethodValue']  = $sellerCartProductsData['selectedShippingMethodValue'][$seller_id];	
			}
			$order_products_total = 0;
			foreach ($products as $product) {
				$options   = $this->model_account_order->getOrderOptions($order_id, $product['order_product_id']);
				foreach ($options as $option)
				{
					if ($option['type'] != 'file') {
						$value = $option['value'];
					} else {
						$value = utf8_substr($option['value'], 0,
							utf8_strrpos($option['value'], '.'));
					}
					$option['value'] = utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value;
					$product['name'] .= "<br />";
					$product['name'] .= "<small> - {$option['name']} : {$option['value']} </small>";
				}
				

				$this->data['products'][] = array(
					'product_id' => $product['product_id'],
					'name'     => $product['name'],
					'model'    => $product['model'],
					'quantity' => $product['quantity'],
					'price'    => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
					'total'    => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
					'return'   => $this->url->link('account/return/insert', 'order_id=' . $order_info['order_id'] . '&product_id=' . $product['product_id'], 'SSL')
				);
				
				if (\Extension::isInstalled('seller_based')&& $this->config->get('seller_based_status') == 1){
					$order_products_total= $sellerCartProductsData['totalAfterShippingCost'][$seller_id];
				}
				else{
				$order_products_total += $product['total']; // calcualte total of products for each seller
				}
			}

			$subordertotal = $this->currency->format($order_products_total, $this->config->get('config_currency'));
			//$this->data['totals'] = $this->model_account_order->getOrderTotals($this->request->get['order_id']);
			$this->data['totals'][0] = array('text' => $subordertotal, 'title' => 'Total');

			$this->data['continue'] = $this->url->link('account/order', '', 'SSL');
            $this->data['email'] = $order_info['email'];
            //$this->data['payment_address'] = $order_info['payment_address_1'];
            $this->data['telephone'] = $order_info['telephone'];
			if ( $this->config->get('msconf_hide_customer_info'))
		    {
				unset($this->data['email'] );
				unset($this->data['telephone'] );
				//unset($this->data['payment_address']  );
			}



			//echo('<pre>'); print_r($this->data);

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/multiseller/account-order-info.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/multiseller/account-order-info.tpl';
			} else {
				$this->template = 'default/template/multiseller/account-order-info.tpl';
			}

			$this->children = array(
				'seller/column_left',
				'seller/column_right',
				'seller/content_top',
				'seller/content_bottom',
				'seller/footer',
				'seller/header'
			);

			$this->response->setOutput($this->render());
		} else {
			$this->redirect($this->url->link('seller/account-order', '', 'SSL'));
		}
	}
		
	public function index() {
		$this->data['link_back'] = $this->url->link('seller/account-dashboard', '', 'SSL');
		
		//Get Seller title from setting table
		$this->load->model('seller/seller');
		$seller_title = $this->model_seller_seller->getSellerTitle();
		$product_title = $this->model_seller_seller->getProductTitle();

		$this->document->setTitle($this->language->get('ms_account_orders_heading'));
		$this->data['ms_account_orders_products'] = sprintf( $this->language->get('ms_account_orders_products') , $product_title );
		
		$this->data['breadcrumbs'] = $this->MsLoader->MsHelper->setBreadcrumbs(array(
			array(
				'text' => $this->language->get('text_account'),
				'href' => $this->url->link('account/account', '', 'SSL'),
			),
			array(
				'text' => sprintf( $this->language->get('ms_account_dashboard_breadcrumbs') , $seller_title ),
				'href' => $this->url->link('seller/account-dashboard', '', 'SSL'),
			),			
			array(
				'text' => $this->language->get('ms_account_orders_breadcrumbs'),
				'href' => $this->url->link('seller/account-order', '', 'SSL'),
			)
		));
		
		list($this->template, $this->children) = $this->MsLoader->MsHelper->loadTemplate('account-order');
		$this->response->setOutput($this->render());
	}
}

?>
