<?php

class ControllerSellerAccountDashboard extends ControllerSellerAccount {
	public function index() {

		// Respond to any ajax request if it has a not-empty param called action.
		// Use the value of the key action in the post request to call a method of this class.
		// This allows dynamic method calling from ajax requests.
		if ( isset($this->request->post['action']) && !empty($this->request->post['action']) )
		{
			// invoke the corresponding method directly.
			$this->{$this->request->post['action']}();
			// Die because we don't need the rest of the page.
			die();
		}
		
		$this->_setAlertVariables();

		//Get Seller title from setting table
		$this->load->model('seller/seller');
		$seller_title = $this->model_seller_seller->getSellerTitle();
		$product_title = $this->model_seller_seller->getProductTitle();
		$products_title = $this->model_seller_seller->getProductsTitle();

		// paypal listing payment confirmation
		if (isset($this->request->post['payment_status']) && strtolower($this->request->post['payment_status']) == 'completed') {
			$this->session->data['success'] = sprintf( $this->language->get('ms_account_sellerinfo_saved') , $seller_title );
		}
		
		$this->load->model('catalog/product');
		$this->load->model('tool/image');
        $this->load->model('account/order');

		$seller_id = $this->customer->getId();
		$this->data['seller_id'] = $seller_id;
		$this->data['text_activate_products'] = sprintf( $this->language->get('text_activate_products') , $products_title );
		$this->data['ms_account_dashboard_nav_product'] = sprintf( $this->language->get('ms_account_dashboard_nav_product') , $product_title );
		$this->data['ms_account_dashboard_nav_products'] = sprintf( $this->language->get('ms_account_dashboard_nav_products') , $products_title );
		$this->data['ms_account_orders_products'] = sprintf( $this->language->get('ms_account_orders_products') , $products_title );

		$seller = $this->MsLoader->MsSeller->getSeller($seller_id);
		$seller_group_names = $this->MsLoader->MsSellerGroup->getSellerGroupDescriptions($seller['ms.seller_group']);
		$my_first_day = date('Y-m-d H:i:s', mktime(0, 0, 0, date("n"), 1));
		
		$this->data['products_state'] = $this->get_products_state($seller_id)['products_state'];

		$this->data['seller'] = array_merge(
			$seller,
			array('balance' => $this->currency->format($this->MsLoader->MsBalance->getSellerBalance($seller_id), $this->config->get('config_currency'))),
			array('commission_rates' => $this->MsLoader->MsCommission->calculateCommission(array('seller_id' => $seller_id))),
			array('total_earnings' => $this->currency->format($this->MsLoader->MsSeller->getTotalEarnings($seller_id), $this->config->get('config_currency'))),
			array('earnings_month' => $this->currency->format($this->MsLoader->MsSeller->getTotalEarnings($seller_id, array('period_start' => $my_first_day)), $this->config->get('config_currency'))),
			array('sales_month' => $this->MsLoader->MsOrderData->getTotalSales(array(
				'seller_id' => $seller_id,
				'period_start' => $my_first_day
			))),
			array('seller_group' => $seller_group_names[$this->config->get('config_language_id')]['name']),
			array('date_created' => date($this->language->get('date_format_short'), strtotime($seller['ms.date_created']))),
			['commission_type'   => $this->MsLoader->MsCommission->getSaleCommissionType($seller_id) ]
			//array('total_products' => $this->MsLoader->MsProduct->getTotalProducts(array(
				//'seller_id' => $seller_id,
				//'enabled' => ))
		);
		
		if ($seller['ms.avatar']) {
			$this->data['seller']['avatar'] = $this->MsLoader->MsFile->resizeImage($seller['ms.avatar'], $this->config->get('msconf_seller_avatar_dashboard_image_width'), $this->config->get('msconf_seller_avatar_dashboard_image_height'));
		} else {
			$this->data['seller']['avatar'] = $this->MsLoader->MsFile->resizeImage('ms_no_image.jpg', $this->config->get('msconf_seller_avatar_dashboard_image_width'), $this->config->get('msconf_seller_avatar_dashboard_image_height'));
		}		
		
		$payments = $this->MsLoader->MsPayment->getPayments(
			array(
				'seller_id' => $seller_id,
			),
			array(
				'order_by'  => 'mpay.date_created',
				'order_way' => 'DESC',
				'offset' => 0,
				'limit' => 5
			)
		);
		 
		$orders = $this->MsLoader->MsOrderData->getOrders(
			array(
				'seller_id' => $seller_id,
				'order_status' => $this->config->get('msconf_credit_order_statuses')
			),
			array(
				'order_by'  => 'date_added',
				'order_way' => 'DESC',
				'offset' => 0,
				'limit' => 5
			)
		);		
		
    	foreach ($orders as $order) {

            $products	=	$this->MsLoader->MsOrderData->getOrderProducts(array('order_id' => $order['order_id'], 'seller_id' => $seller_id));

            foreach($products as $key=>$p)
                $products[$key]['options']	=  $this->model_account_order->getOrderOptions($order['order_id'], $p['order_product_id']);

    		$this->data['orders'][] = array(
    			'order_id' => $order['order_id'],
    			'customer' => "{$order['firstname']} {$order['lastname']} ({$order['email']})",
    			'products' => $products,
    			'date_created' => date($this->language->get('date_format_short'), strtotime($order['date_added'])),
   				'total' => $this->currency->format($order['total'], $this->config->get('config_currency'))
   			);
   		}
		
		///// Warehouses
   		$this->data['warehouses'] = false;
		$this->load->model('module/warehouses');
		if($this->model_module_warehouses->is_installed()){
			$this->data['warehouses'] = true;
		}
		////////////////

		//Seller Gallery Images
		$this->data['gallery_status'] = $this->config->get('msconf_allow_seller_image_gallery');
		///////////////////
		
		$this->data['link_back'] = $this->url->link('account/account', '', 'SSL');
		
		$this->document->setTitle( sprintf( $this->language->get('ms_account_dashboard_heading') , $seller_title ) );
		$this->data['ms_account_dashboard_heading'] = sprintf( $this->language->get('ms_account_dashboard_heading') , $seller_title ) ;
		$this->data['ms_account_dashboard_seller_group'] = sprintf( $this->language->get('ms_account_dashboard_seller_group') , $seller_title ) ;
		$this->data['ms_account_dashboard_nav_profile'] = sprintf( $this->language->get('ms_account_dashboard_nav_profile') , $seller_title ) ;
		$this->data['commission_type'] = 'price_list';

		if (\Extension::isInstalled('your_service'))
		{
			$ysMsEnabled = $this->config->get('ys')['ms_view_requests'] ?? 0;
			if ($ysMsEnabled == 1)
			{
				$this->language->load_json('module/your_service');
				$this->data['ys_ms_show_service_requests'] = $this->language->get('ys_ms_show_service_requests');
				$this->data['ys_ms_service_settings'] = $this->language->get('ys_ms_service_settings');
			}
		}

		if (\Extension::isInstalled('printful'))
		{
			$printfullEnabled = $this->config->get('printful')['status'] ?? 0;
			if ($printfullEnabled == 1)
			{
				$this->language->load_json('module/printful');
				$this->data['printful_settings'] = $this->language->get('printful_settings');
				$this->data['visit_printful'] = $this->language->get('visit_printful');
			}
		}

		if (\Extension::isInstalled('seller_ads'))
		{
			$sellerAdsEnabled = $this->config->get('seller_ads_app_status') ?? 0;
			if ($sellerAdsEnabled == 1)
			{
				$this->language->load_json('module/seller_ads');
				$this->data['ms_seller_ads'] = $this->language->get('ms_seller_ads');
			}
		}
		///for Auctions
		if (\Extension::isInstalled('auctions'))
		{   $this->data['auctions']=true;
	    	$this->data['ms_account_dashboard_aucation'] = $this->language->get('ms_account_dashboard_aucation');
		}
		
		///for Delivery Slot App
		if ( \Extension::isInstalled('delivery_slot') && $this->config->get('delivery_slot')['status'] == 1
		     && $this->config->get('msconf_delivery_slots_to_sellers') )
		{   $this->data['delivery_slot']=true;
	    	$this->data['ms_account_delivery_slot'] = $this->language->get('ms_account_delivery_slot');
		}

		$this->data['breadcrumbs'] = $this->MsLoader->MsHelper->setBreadcrumbs(array(
			array(
				'text' => $this->language->get('text_account'),
				'href' => $this->url->link('account/account', '', 'SSL'),
			),
			array(
				'text' => sprintf( $this->language->get('ms_account_dashboard_breadcrumbs'), $seller_title ),
				'href' => $this->url->link('seller/account-dashboard', '', 'SSL'),
			)
		));
		
		$this->data['lang'] = $this->config->get('config_language');
		list($this->template, $this->children) = $this->MsLoader->MsHelper->loadTemplate('account-dashboard');
		$this->response->setOutput($this->render());
	}

    /**
     * Set session alert variables in data array
     * @return void
     */
    private function _setAlertVariables():void{
        $this->data['success'] = $this->data['warning'] = $this->data['error'] = '';
        
        //success
        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];            
            unset($this->session->data['success']);
        }

        //warning
        if (isset($this->session->data['warning'])) {
            $this->data['warning'] = $this->session->data['warning'];            
            unset($this->session->data['warning']);
        }

        //error
        if (isset($this->session->data['error'])) {
            $this->data['error'] = $this->session->data['error'];            
            unset($this->session->data['error']);
        }
    }

	/**
	*	Get products state configuration.
	*
	*	@author Michael.
	*	@param integer $seller_id.
	*	@return integer products_state or false.
	*/
	private function get_products_state($seller_id)
	{
		$sql = "SELECT `products_state` FROM `". DB_PREFIX ."ms_seller` WHERE `seller_id` = '$seller_id' LIMIT 1;";
		$query = $this->db->query($sql);
		return $query->row;
	}

	/**
	*	Change the seller's products' state.
	*
	*	@author Michael
	*	@return boolean
	*/
	private function change_products_state()
	{
        $this->load->model('catalog/product');
		$product_state = $this->request->post['product_state'];
		if (isset($product_state) && in_array($product_state, ['yes', 'no']))
		{
			//  ALTER TABLE `ms_seller` ADD `products_state` VARCHAR(1) NOT NULL DEFAULT '1' AFTER `product_validation`;
			try
			{
				if ($product_state == 'no')
				{
					$product_state = 0;
				}
				else
				{
					$product_state = 1;
				}
				$seller_id = $this->customer->getId();
				// Update product status in ms_seller and ms_product
				$sql = "UPDATE `" . DB_PREFIX . "ms_seller`
						SET `products_state` = '$product_state'
						WHERE `seller_id` = '".$this->db->escape($seller_id)."'
				;";
				$query = $this->db->query($sql);
				$sql = "UPDATE `" . DB_PREFIX . "ms_product`
						SET `product_status` = $product_state
						WHERE `seller_id` = '".$this->db->escape($seller_id)."'
				;";
				$query = $this->db->query($sql);
				// sync  status column in product table
                $this->model_catalog_product->updateProductsStatusBySellerId($seller_id , $product_state);
				echo $this->language->get('products_state_changed_successfully');
			}
			catch (Exception $e)
			{
				echo $this->language->get('products_state_changed_unsuccessfully');
			}
		}
	}

	public function printful()
	{
		
		
		list($this->template, $this->children) = $this->MsLoader->MsHelper->loadTemplate('printful_iframe');

		$this->response->setOutput($this->render());
	}
}
