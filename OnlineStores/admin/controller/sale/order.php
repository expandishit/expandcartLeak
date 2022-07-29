<?php

use ExpandCart\Foundation\String\Barcode\Generator as BarcodeGenerator;
use ExpandCart\Foundation\Filesystem\{Directory, File};
use Facebook\Facebook;

class ControllerSaleOrder extends Controller {
	private $error = array();

    private $plan_id = PRODUCTID;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('plan/trial');
        $trial = $this->model_plan_trial->getLastTrial();
        if ($trial){
            $this->plan_id = $trial['plan_id'];
        }

    }

    public function getCustomerInfo()
    {
        if ( $this->request->server['REQUEST_METHOD'] != 'POST' || !isset($this->request->post['customer_id']) )
        {
            return false;
        }

        $customer_id = (int) $this->request->post['customer_id'];

        $this->load->model('sale/customer');

        $customer_info = $this->model_sale_customer->getCustomer($customer_id);

        $this->response->setOutput(json_encode($customer_info));

        return;
    }

	public function orderHistory(){

        $this->language->load('sale/order');
        // load model of sale -> order
        $this->load->model('sale/order');
        // load order status
        $this->load->model('localisation/order_status');


        if (isset($this->request->get['order_id'])) {
            $order_id = $this->request->get['order_id'];
        } else {
            $order_id = 0;
        }
        $this->data['order_id'] = $order_id;

        // get total of this order
        $this->data['totals'] = $this->model_sale_order->getOrderTotals($order_id);

        // get order details //
        $order_info = $this->model_sale_order->getOrder($order_id);

        // get order status
        $order_status_info = $this->model_localisation_order_status->getOrderStatus($order_info['order_status_id']);

        if ($order_status_info) {
            $this->data['order_status'] = $order_status_info['name'];
            $this->data['order_status_color'] = $order_status_info['bk_color'];
        } else {
            $this->data['order_status'] = '';
            $this->data['order_status_color'] = '';
        }
        // get order added
        $this->data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));
        // get order history
        $this->data['order_histories'] = null;
        if($this->plan_id != 3) {
            $this->data['order_histories'] = $this->model_sale_order->getOrderHistories($order_id);
        }
        $order_modification = $this->model_sale_order->getOrderModifications($order_id);
        $order_history = $this->data['order_histories'];

        $order_history_modification = array_merge($order_modification,$order_history);
        array_multisort(array_column($order_history_modification, 'date_added'), SORT_DESC, $order_history_modification);

        $this->data['order_histories']  = $order_history_modification;
        $this->data['order_modifi_status'] = [
            1 => $this->language->get('text_order_updated'),
            2 => $this->language->get('text_order_added_to_archive'),
            3 => $this->language->get('text_order_removed_from_archive'),
        ];


        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->document->setTitle($this->data['heading_title']);
		$this->template = 'sale/order_history.expand';
            $this->children = array(
                'common/header',
                'common/footer'
            );

            $this->response->setOutput($this->render_ecwig());
	}
	public function orderHistoryFilter(){

        $this->load->model('sale/order');
        if ($this->request->server['REQUEST_METHOD'] != 'POST') {
            $this->response->setOutput(json_encode(['status' => 'fail']));
            return;
        }
        $start= date('Y-m-d', strtotime($this->request->post['start']));
        $end= date('Y-m-d', strtotime($this->request->post['end']));
        $order_id= (int)$this->request->post['order_id'];
        $data=null;
        if($this->plan_id != 3) {
            $data = $this->model_sale_order->getOrderHistoriesFilter($order_id,$start,$end);
        }
        if ($data != NULL){
            $response['status'] = 'success';
            $response['data'] = $data;
        }else{
            $response['status'] = 'fail';
        }
        $this->response->setOutput(json_encode($response));
        return;
	}

    public function getProductsByOrderId()
    {
        $json = array();

        if (
            isset($this->request->get['order_id'])
        ) {


            $this->load->model('sale/order');
            $this->load->model('catalog/product');
            $this->load->model('catalog/option');

            $orderId = (int) $this->request->get['order_id'];

            $results = $this->model_sale_order->getOrderProducts($orderId);

            foreach ($results as $result) {
                $option_data = array();

                $product_options = $this->model_catalog_product->getProductOptions($result['product_id']);

                // Get Product special prices
                $product_specials = $this->model_catalog_product->getProductSpecials($result['product_id']);

                foreach ($product_options as $product_option) {
                    $option_info = $this->model_catalog_option->getOption($product_option['option_id']);

                    if ($option_info) {
                        if (
                            $option_info['type'] == 'select' ||
                            $option_info['type'] == 'radio' ||
                            $option_info['type'] == 'checkbox' ||
                            $option_info['type'] == 'image'
                        ) {
                            $option_value_data = array();

                            foreach ($product_option['product_option_value'] as $product_option_value) {
                                $option_value_info = $this->model_catalog_option->getOptionValue(
                                    $product_option_value['option_value_id']
                                );

                                if ($option_value_info) {

                                    $price = (
                                    (float)$product_option_value['price'] ?
                                        $this->currency->format(
                                            $product_option_value['price'],
                                            $this->config->get('config_currency')
                                        ) :
                                        false
                                    );

                                    $option_value_data[] = array(
                                        'product_option_value_id' => $product_option_value['product_option_value_id'],
                                        'option_value_id' => $product_option_value['option_value_id'],
                                        'name' => $option_value_info['name'],
                                        'price' => $price,
                                        'price_prefix' => $product_option_value['price_prefix']
                                    );
                                }
                            }

                            $option_data[] = array(
                                'product_option_id' => $product_option['product_option_id'],
                                'option_id' => $product_option['option_id'],
                                'name' => $option_info['name'],
                                'type' => $option_info['type'],
                                'option_value' => $option_value_data,
                                'required' => $product_option['required']
                            );
                        } else {
                            $option_data[] = array(
                                'product_option_id' => $product_option['product_option_id'],
                                'option_id' => $product_option['option_id'],
                                'name' => $option_info['name'],
                                'type' => $option_info['type'],
                                'option_value' => $product_option['option_value'],
                                'required' => $product_option['required']
                            );
                        }
                    }
                }

                if(count($product_specials) > 0){
                    foreach ($product_specials as $special_price) {

                        $date_start = $special_price['date_start'];
                        $date_end = $special_price['date_end'];

                        if(
                            ($date_start == null || !$date_start || $date_start == "0000-00-00") &&
                            ($date_end == null || !$date_end || $date_end == "0000-00-00")
                        ) {
                            $result['price']=$special_price['price'];
                            break;
                        } else {
                            if ($special_price['date_end'] >= date("Y-m-d",time())) {
                                $result['price']=$special_price['price'];
                                break;
                            }
                        }
                    }
                }
                $json[] = array(
                    'product_id' => $result['product_id'],
                    'name' => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
                    'model' => $result['model'],
                    'option' => $option_data,
                    'price' => $result['price'],
                    'total' => $result['price'],
                    'image' => \Filesystem::getUrl('image/' . $result['image']),
                );
            }
        }

        $this->response->setOutput(json_encode($json));
    }

    public function getKanbanCards()
    {
        $this->load->model('sale/order');
        $this->load->model('localisation/order_status');

        $order_statuses = $this->model_localisation_order_status->getOrderStatuses();
        $orders = $this->model_sale_order->getOrders();

        foreach ( $order_statuses as $index => $order_status )
        {
            $order_statuses[$index]['orders'] = array();

            foreach ( $orders as $order )
            {
                if ( $order['order_status_id'] == $order_status['order_status_id'] )
                {
                    $order_statuses[$index]['orders'][] = $order;
                }
            }
        }

        $data['order_statuses'] = $order_statuses;
        $data['orders'] = $orders;

        $this->response->setOutput( json_encode( $data ) );
        return;
    }

    public function dtDelete()
    {
        $this->load->model('sale/order');
        $this->load->language('sale/order');

        if ( !isset($this->request->post['id']) || (!$this->user->hasPermission('modify', 'sale/order')) && !$this->user->hasPermission('custom', 'deleteOrder'))
        {
            return false;
        }

        $id_s   = $this->request->post['id'];
        $action = trim($this->request->post['action'] ? $this->request->post['action'] : 'archive');

        $this->load->model('module/zoho_inventory');

        $id_s = is_array($id_s) ? $id_s : [$id_s];

        $this->load->model('loghistory/histories');
        $this->load->model('setting/audit_trail');
        $pageStatus = $this->model_setting_audit_trail->getPageStatus("order");

        foreach ($id_s as $order_id)
        {

            if($pageStatus) {
                $old_value = $this->getAllOrderInfo($order_id);
                $log_history['action'] = $action;
                $log_history['reference_id'] = $order_id;
                $log_history['old_value'] = json_encode($old_value,JSON_UNESCAPED_UNICODE);
                $log_history['new_value'] = NULL;
                $log_history['type'] = 'order';
                $this->model_loghistory_histories->addHistory($log_history);
            }

            if ( $this->model_sale_order->deleteOrder( (int) $order_id, $action ) )
            {
                $this->model_module_zoho_inventory->deleteOrder( (int) $order_id, $action );


                $result_json['success'] = '1';
                $result_json['success_msg'] = $this->language->get('text_success');
            }
            else
            {
                $result_json['success'] = '0';
                break;
            }
        }

        $this->response->setOutput(json_encode($result_json));
        return;

    }

    public function dtHandler() {
        $this->load->model('sale/order');
        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            parse_str(html_entity_decode($this->request->post['filter']), $filterData);
            $filterData = $filterData['filter'];
           // print_r($filterData);
        }

        $filterData['search'] = null;
        if (isset($request['search']['value']) && strlen($request['search']['value']) > 0) {
            $filterData['search'] = $request['search']['value'];
        }

        ///adding status comment to filter data
        $filterData['status_comment'] = 1;

        $start = $request['start'] ?? 0;
        $length = $request['length'] ?? 25;

        $columns = array(
            0 => '',
            1 => 'order_id',
			2 => 'customer',
			3 => 'payment_country',
			4 => 'payment_zone',
			5 => 'payment_address_1',
			6 => 'address',
			7 => 'phone',
			8 => 'fax',
			9 => 'shipping_method',
            10 => 'status',
            11 => 'total',
            12 => 'date_added',
            13 => 'date_modified',
            14 => '',
        );

        $columns = $this->request->post['columns'];
        $columnsOrder = $this->request->post['order'][0]['column'];
        $orderType = $this->request->post['order'][0]['dir'];
        $orderColumn =  $columns[$columnsOrder]['data'];

        $wkpos_enabled = 0;
        $this->load->model('wkpos/wkpos');
        if($this->model_wkpos_wkpos->is_installed())
            $wkpos_enabled = 1;

		$deliverySlotInstalled = (\Extension::isInstalled('delivery_slot') && $this->config->get('delivery_slot')['status'] == 1) ? 1 : 0;

        //Warehouses check | new changes v.21.05.2020
        $warehouses = 0;
        $warehouse_setting = $this->config->get('warehouses');
        if($warehouse_setting && $warehouse_setting['status'] == 1){
            $this->load->model('module/warehouses');
            $wr_list = $this->load->model_module_warehouses->getWarehouses(['selections' => ['id', 'name']]);
            $wrs_names = [];
            foreach ($wr_list as $wr){
                $wrs_names[$wr['id']] = $wr['name'];
            }
            $warehouses = 1;
        }/////////////////////////////////////////////

        $data = array(
            'sort' => $orderColumn,
            'order' => $orderType,
            'start' => $start,
            'limit' => $length,
			'wkpos' => $wkpos_enabled,
			'delivery_slot' => $deliverySlotInstalled,
            'warehouses' => $warehouses,
        );

        $return = $this->model_sale_order->getOrdersToFilter($data, $filterData);
        $data = $return['data'];

        //special datatable columns for tap - version 01 payment method only
        $data = $this->_getTapPaymentFields($data);

        //Warehouses get names
        if($warehouses){
            foreach ($data as &$item){
                $item_wr_data = $item['wrs_data'] ?? '';
                $item_wr_list = $item['wrs_list'] ? json_decode($item['wrs_list']) : [];
                //Get warehouses names by ids list (default after new changes v.21.05.2020)
                if(count($item_wr_list)){
                    $item_wr_data = [];
                    foreach ($item_wr_list as $wri){
                        $item_wr_data[] = $wrs_names[$wri];
                    }
                    $item['wrs_data'] = '<label class="label label-default">'.implode('</label> <label class="label label-default">', $item_wr_data).'</label>';
                }
                //Get warehouses names by names list (for old data before new changes v.21.05.2020)
                else if($item_wr_data){
                    $item_wr_data     = json_decode($item_wr_data, true);
                    $item['wrs_data'] = '<label class="label label-default">'.implode('</label> <label class="label label-default">', $item_wr_data['wrs_name']).'</label>';
                }

            }
        }///////////////////

        if($deliverySlotInstalled)
        {
            $columns = $this->request->post['columns'];
            $columnsOrder = $this->request->post['order'][0]['column'];
            $columnsOrderDir = $this->request->post['order'][0]['dir'];
            if($columns[$columnsOrder]['data'] == 'ds_date')
            {
               $sort =  ($columnsOrderDir == 'asc') ? SORT_ASC : SORT_DESC;

               $dsDataArray = array_column($data, 'ds_date');

               array_multisort($dsDataArray, $sort, $data);

            }

        }

        $records = $data;

        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        $json_data = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($totalData), // total number of records
            "recordsFiltered" => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $records,   // total data array
        );
        $this->response->setOutput(json_encode($json_data));
        return;
    }

  	public function index() {
		$this->language->load('sale/order');
		$this->language->load('catalog/product_filter');

        $this->data['heading_title'] = $this->language->get('heading_title');
		$this->document->setTitle($this->data['heading_title']);


		$this->load->model('sale/order');

        $mylerzInstalled = \Extension::isInstalled('mylerz') && $this->config->get('mylerz_status');
        if ($mylerzInstalled){
            $this->data['shipping_mylerz'] = true;
        }

        $expandshipInstalled = \Extension::isInstalled('expandship') && $this->config->get('expandship');
        if ($expandshipInstalled){
            $this->data['shipping_expandship'] = true;
        }

    	$this->getList();
  	}

    public function massDelete()
    {
        if ( $this->request->server['REQUEST_METHOD'] != 'POST' || !isset( $this->request->post['ids'] ) )
        {
            return false;
        }

        $ids = $this->request->post['ids'];

        $this->load->model('sale/order');

        if ( is_array($ids) )
        {
            $this->load->model('loghistory/histories');
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("order");

            foreach ($ids as $id)
            {
                $old_value = $this->getAllOrderInfo($id);
                $this->model_sale_order->deleteOrder( $id );

                if($pageStatus) {
                    $log_history['action'] = 'delete';
                    $log_history['reference_id'] = $id;
                    $log_history['old_value'] = json_encode($old_value);
                    $log_history['new_value'] = NULL;
                    $log_history['type'] = 'order';
                    $this->model_loghistory_histories->addHistory($log_history);
                }
            }
        }
        else
        {
            $this->load->model('loghistory/histories');
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("order");
            $old_value = $this->getAllOrderInfo($ids);
            if($pageStatus) {
                $log_history['action'] = 'delete';
                $log_history['reference_id'] = $ids;
                $log_history['old_value'] = json_encode($old_value);
                $log_history['new_value'] = NULL;
                $log_history['type'] = 'order';
                $this->model_loghistory_histories->addHistory($log_history);
            }
            $this->model_sale_order->deleteOrder( $ids );
        }

        $result_json['success'] = '1';
        $result_json['success_msg'] = $this->language->get('text_success');

        $this->response->setOutput(json_encode($result_json));
        return;
    }

	/**
	 * Check if login and register by phone number is activated
	 */
	public function registerByPhoneNumberMode(){
		$this->load->model('module/signup');
		$custom_registration_app = $this->model_module_signup->isActiveMod();
		if($custom_registration_app)
			return $this->model_module_signup->isLoginRegisterByPhonenumber();
		return false;
	}
  	public function insert() {
        if($this->plan_id == 3){
            $this->redirect(
                $this->url->link('error/not_found', '', 'SSL')
            );
            exit();
        }
		$this->language->load('sale/order');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('sale/order');

		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( ! $this->validateForm() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;

                $this->response->setOutput(json_encode($result_json));

                return;
            }


            // VQMOD CODE SMS //
            $order = $this->model_sale_order->getOrder($this->request->get['order_id']);

            if($order['customer_id'] != 0){
                $this->load->model('textplode/textplode');
                if($this->model_textplode_textplode->getSMS($order['customer_id'])){
                    $old_status = $this->model_textplode_textplode->getStatusNameFromId($order['order_status_id'], $this->request->get['order_id']);
                    $new_status = $this->model_textplode_textplode->getStatusNameFromId($this->request->post['order_status_id'], $this->request->get['order_id']);
                    if($old_status != $new_status){
                        if($this->model_textplode_textplode->isActive($new_status)){
                            $this->load->model('sale/customer');
                            $customer = $this->model_sale_customer->getCustomer($order['customer_id']);
                            $template = $this->model_textplode_textplode->getTemplateFromStatusName($new_status);
                            if($this->model_textplode_textplode->isValidNumber($customer['telephone'])){
                                $this->model_textplode_textplode->sendMessage($customer['telephone'], $template['template_content'], $this->request->get['order_id'], $this->config->get('textplode_from_name'));
                            }
                        }
                    }
                }
            }
            // END VQMOD SMS //
			$this->load->model('setting/setting');

            $order_status=$this->request->post['order_status_id'];
            $order_id = $this->model_sale_order->addOrder($this->request->post);

			if($this->model_setting_setting->getSetting('knawat_dropshipping_status')['status']){

				if($this->checkKnawatProducts($this->request->post['order_product']) && !$this->validate_knawat_order_params()){
					$result_json['success'] = '0';
                	$result_json['errors'] = $this->error;
					$this->response->setOutput(json_encode($result_json));
					return;
				}

				$sending_knawat_order_status = $this->config->get('module_knawat_dropshipping_sending_order_status');

				if($order_status == $sending_knawat_order_status){
                    $data=array($order_id,$order_status);
					$this->checkKnawatOrder($data,true);
				}
				// 1 is pending, 2 is processing, 7 is cancel and that knawat app is accepting those only
				/*
				if($order_status != "1" && $order_status != "2" && $order_status != "7"){
					$order_status = "1";
				}
				$data=array($order_id,$order_status);
				$this->checkKnawatOrder($data,true);
				*/
			}

             // Odoo create order if app is installed
                if (\Extension::isInstalled('odoo') && $this->config->get('odoo')['status']
                && $this->config->get('odoo')['orders_integrate'])
                {
                $this->load->model('module/odoo/orders');
                $this->model_module_odoo_orders->createOrder($order_id  ,$this->request->post);
                }
			else{
                $this->load->model('setting/amplitude');
                if ($order_id && !$this->userActivation->isTestOrder($order_id))
                    $this->model_setting_amplitude->trackEvent('Order Received Successfully',['Order ID'=>$order_id]);
                // add data to log_history
                $this->load->model('setting/audit_trail');
                $pageStatus = $this->model_setting_audit_trail->getPageStatus("order");
                if($pageStatus){
                    $log_history['action'] = 'add';
                    $log_history['reference_id'] = $order_id;
                    $log_history['old_value'] = NULL;
                    $log_history['new_value'] = json_encode($this->getAllOrderInfo($order_id),JSON_UNESCAPED_UNICODE);
                    $log_history['type'] = 'order';
                    $this->load->model('loghistory/histories');
                    $this->model_loghistory_histories->addHistory($log_history);
                    // add order history record for the admin to avoid unhandled order statistic
                    $this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$order_status . "', user_id = '" . $this->user->getId() . "', date_added = NOW()");
                }
			}

			//
			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

			$result_json['success'] = '1';
            $result_json['redirect'] = '1';
            $result_json['to'] = (string) $this->url->link('sale/order/info', 'order_id=' . $order_id, 'SSL');

            $this->response->setOutput(json_encode($result_json));

            return;
		}

        $this->data['payment_country_id'] = $this->config->get('config_country_id');
        $this->data['payment_zone_id'] = $this->config->get('config_zone_id');
        $this->data['customer_group_id'] = $this->config->get('config_customer_group_id');



        $this->getForm();
  	}
    public function getAllOrderInfo($orderId){

        $responseData = array();

        $this->load->model('sale/order');
        $this->load->model('module/manual_shipping/settings');
        $responseData['orderInfo'] = $this->model_sale_order->getOrder($orderId);
        $products = $this->model_sale_order->getOrderProducts($orderId);

        $totals = $this->model_sale_order->getOrderTotals($orderId);


        $totalProductsAmount = 0 ;
        foreach ($products as $i => $product) {

            $options = $this->model_sale_order->getOrderOptions($orderId,$product['order_product_id']);
            $productsList[] = array(

                'order_product_id' => $product['order_product_id'],
                'product_id'       => $product['product_id'],
                'name'    	 	   => $product['product_name'],
                'model'    		   => $product['model'],
                'option'   		   => $options,
                'quantity'		   => $product['quantity'],
                'tax'		   => $product['tax'],
                'reward'		   => $product['reward'],
                'price'    		   => $this->currency->format($product['price'], $order_info['currency_code'], $order_info['currency_value'],false),
                'total'    		   => $this->currency->format($product['total'], $order_info['currency_code'], $order_info['currency_value'],false),

            );
            $totalProductsAmount += $productsList[$i]['total'];
        }
        foreach ($totals as $total) {

            $totalsList[] = array(

                'order_total_id' => $total['order_total_id'],
                'order_id'       => $total['order_id'],
                'code'      => $total['code'],
                'sort_order'      => $total['sort_order'],
                'title'    	 	   => $total['title'],
                'text'    		   => $total['text'],
                'value'    		   => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value'],false),

            );
        }

        $responseData['order_products'] = $productsList;
        $responseData['totals']  = $totalsList;
        $responseData['order_products_total_amount'] = $totalProductsAmount;
        if (\Extension::isInstalled('manual_shipping') && $this->config->get('manual_shipping')['status']) {
            $responseData['manual_Shipped_products'] =  $this->model_module_manual_shipping_settings->get_shipped_data_by_order_id($orderId);
        }
        $responseData['manual_Shipped_products'] = [];

        return $responseData;
    }

	private function checkKnawatProducts($products){

		$this->load->model('module/knawat_dropshipping');
		foreach ($products as $product) {
			if($this->model_module_knawat_dropshipping->check_knawat_product($product['product_id']))
				return true;
		}
		return false;
	}
	private function validate_knawat_order_params(){

    	if ($this->request->post['payment_firstname'] == "") {
      		$this->error['payment_firstname'] = $this->language->get('error_firstname');
    	}
    	if ($this->request->post['email'] == "") {
      		$this->error['email'] = $this->language->get('error_email');
    	}

    	if ($this->request->post['telephone'] == "") {
      		$this->error['telephone'] = $this->language->get('error_telephone');
    	}

    	if ( $this->request->post['payment_lastname'] == "") {
      		$this->error['payment_lastname'] = $this->language->get('error_lastname');
    	}

    	if ( $this->request->post['payment_address_1'] == "" ) {
      		$this->error['payment_address_1'] = $this->language->get('error_payment_address_1');
    	}

    	if ($this->request->post['payment_city'] == "") {
      		$this->error['payment_city'] = $this->language->get('error_payment_city');
    	}

    	if ($this->request->post['payment_country_id'] == '') {
      		$this->error['payment_country'] = $this->language->get('error_payment_country');
    	}

    	if ($this->request->post['payment_zone_id'] == '') {
      		$this->error['payment_zone'] = $this->language->get('error_payment_zone');
    	}

    	if ($this->request->post['payment_method'] == '') {
      		$this->error['payment_method'] = $this->language->get('error_payment');
    	}

		if ($this->request->post['shipping_firstname'] == "") {
			$this->error['shipping_firstname'] = $this->language->get('error_shipping_firstname');
		}

		if ($this->request->post['shipping_lastname'] == "") {
		 	$this->error['shipping_lastname'] = $this->language->get('error_shipping_lastname');
		}

		if ($this->request->post['shipping_address_1'] == "") {
		 	$this->error['shipping_address_1'] = $this->language->get('error_shipping_address_1');
		}

		if ($this->request->post['shipping_city'] == "") {
		 	$this->error['shipping_city'] = $this->language->get('error_shipping_city');
		}

		if ($this->request->post['shipping_country_id'] == '') {
			$this->error['shipping_country'] = $this->language->get('error_shipping_country');
		}

		if ($this->request->post['shipping_zone_id'] == "") {
			$this->error['shipping_zone'] = $this->language->get('error_shipping_zone');
		}

		if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_knawat_warning');
        }

        return $this->error ? false : true;
	}
  	public function update()
    {
        if($this->plan_id == 3){
            $this->redirect(
                $this->url->link('error/not_found', '', 'SSL')
            );
            exit();
        }
        $order_id = (int)$this->request->get['order_id'];
        if(!preg_match("/^[1-9][0-9]*$/", $order_id)) return false;

        if (!$this->user->hasPermission('modify', 'sale/order')) {
            $this->redirect($this->url->link('/admin/error/permission'));
            return;
        }

		$this->language->load('sale/order');
		$this->language->load('module/product_designer');
		$this->load->model('module/signup');


		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('sale/order');


		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( ! $this->validateForm() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;

                $this->response->setOutput(json_encode($result_json));

                return;
            }

            // VQMOD CODE SMS //
            $order = $this->model_sale_order->getOrder($this->request->get['order_id']);

            if($order['customer_id'] != 0){
                $this->load->model('textplode/textplode');
                if($this->model_textplode_textplode->getSMS($order['customer_id'])){
                    $old_status = $this->model_textplode_textplode->getStatusNameFromId($order['order_status_id'], $this->request->get['order_id']);
                    $new_status = $this->model_textplode_textplode->getStatusNameFromId($this->request->post['order_status_id'], $this->request->get['order_id']);
                    if($old_status != $new_status){
                        if($this->model_textplode_textplode->isActive($new_status)){
                            $this->load->model('sale/customer');
                            $customer = $this->model_sale_customer->getCustomer($order['customer_id']);
                            $template = $this->model_textplode_textplode->getTemplateFromStatusName($new_status);
                            if($this->model_textplode_textplode->isValidNumber($customer['telephone'])){
                                $this->model_textplode_textplode->sendMessage($customer['telephone'], $template['template_content'], $this->request->get['order_id'], $this->config->get('textplode_from_name'));
                            }
                        }
                    }
                }
            }
            // END VQMOD SMS //

            $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

            if($queryRewardPointInstalled->num_rows) {
                $this->load->model('promotions/reward_points_transactions');
                $this->model_promotions_reward_points_transactions->beforeUpdateOrder($this->request->get['order_id'], $this->request->post);
            }


            $old_value = $this->getAllOrderInfo($this->request->get['order_id']);

			$this->model_sale_order->editOrder($this->request->get['order_id'], $this->request->post);

            // add data to log_history
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("order");
            if($pageStatus){
                $log_history['action'] = 'update';
                $log_history['reference_id'] = $this->request->get['order_id'];
                $log_history['old_value'] = json_encode($old_value,JSON_UNESCAPED_UNICODE);
                $log_history['new_value'] = json_encode($this->getAllOrderInfo($this->request->get['order_id']),JSON_UNESCAPED_UNICODE);
                $log_history['type'] = 'order';
                $this->load->model('loghistory/histories');
                $this->model_loghistory_histories->addHistory($log_history);
            }

            // microsoft dynamics update order status
            $this->load->model('module/microsoft_dynamics');
            if ($this->model_module_microsoft_dynamics->isActive()) {
                $this->model_module_microsoft_dynamics->updateOrderStatus($this->request->get['order_id'], $this->request->post['order_status_id']);
            }

			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

			$result_json['success'] = '1';

            $result_json['redirect'] = '1';
            $result_json['to'] = (string) $this->url->link('sale/order/info', 'order_id=' . $this->request->get['order_id'], 'SSL');

            $this->response->setOutput(json_encode($result_json));
            $this->model_sale_order->sendEmailOnEditOrderOrder($this->request->post['order_id']);
            return;
		}


		$this->data['pd_submit_url'] = html_entity_decode(
		    $this->url->link(
		        'module/product_designer/updateOrderPage',
                '',
                'SSL'
            )
        );

        $this->data['pd_refresh_url'] = html_entity_decode(
            $this->url->link(
                'sale/order/update',
                'order_id=' . $this->request->get['order_id'],
                'SSL'
            )
		);
    	$this->getForm();
  	}

    public function quick_update()
    {
        if (isset($this->request->get['order_id'])) {
            $order_id = $this->request->get['order_id'];
        } else {
            $order_id = 0;
        }
        $this->data['order_id'] = $order_id;
        // load model
        $this->load->model('sale/order');
        // get order details //
        $order_info = $this->model_sale_order->getOrder($order_id);

        if($order_info){
            $this->language->load('sale/order');


            $this->document->setTitle($this->language->get('heading_title'));

            $this->data['breadcrumbs'] = array();

            $this->data['breadcrumbs'][] = array(
                'text'      => $this->language->get('text_home'),
                'href'      => $this->url->link('common/home', '', 'SSL'),
                'separator' => false
            );

            $this->data['breadcrumbs'][] = array(
                'text'      => $this->language->get('heading_title'),
                'href'      => $this->url->link('sale/order', '', 'SSL'),
                'separator' => ' :: '
            );

            $this->data['breadcrumbs'][] = array(
                'text'      => $this->language->get('breadcrumb_update'),
                'href'      => $this->url->link('sale/order', '', 'SSL'),
                'separator' => ' :: '
            );

            $this->data['order_total'] = $order_info['total'];
            $this->data['order_currency'] = $order_info['currency_code'];
            $this->data['payment_country_id'] = $order_info['payment_country_id'];
            $this->data['payment_zone_id'] = $order_info['payment_zone_id'];
            $this->data['payment_area_id'] = $order_info['payment_area_id'];
            $this->data['shipping_area_id'] = $order_info['shipping_area_id'];
            $products = $this->model_sale_order->getOrderProducts($this->request->get['order_id']);
            $totals = $this->model_sale_order->getOrderTotals($this->request->get['order_id']);

            $this->data['catalog_url'] = HTTP_CATALOG;

					$totalProductsAmount = 0 ;
            foreach ($products as $i => $product) {

							$options = $this->model_sale_order->getOrderOptions($this->request->get['order_id'],$product['order_product_id']);
                $productsList[] = array(

                    'order_product_id' => $product['order_product_id'],
                    'product_id'       => $product['product_id'],
                    'name'    	 	   => $product['product_name'],
                    'model'    		   => $product['model'],
                    'option'   		   => $options,
                    'quantity'		   => $product['quantity'],
                    'tax'		   => $product['tax'],
                    'reward'		   => $product['reward'],
                    'price'    		   => $this->currency->format($product['price'], $order_info['currency_code'], $order_info['currency_value'],false),
                    'total'    		   => $this->currency->format($product['total'], $order_info['currency_code'], $order_info['currency_value'],false),

                );
                $totalProductsAmount += $productsList[$i]['total'];
				}
            foreach ($totals as $total) {

                $totalsList[] = array(

                    'order_total_id' => $total['order_total_id'],
                    'order_id'       => $total['order_id'],
                    'code'      => $total['code'],
                    'sort_order'      => $total['sort_order'],
                    'title'    	 	   => $total['title'],
                    'text'    		   => $total['text'],
                    'value'    		   => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value'],false),

                );
            }

            $this->data['order_products'] = $productsList;
            $this->data['totals']  = $totalsList;
            $this->data['order_products_total_amount'] = $totalProductsAmount;

            if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
            {
                if ( !$this->user->hasPermission('modify', 'sale/order') )
                {
                    $result_json['success'] = '0';
                    $result_json['error'] = $this->language->get('error_permission');
                    $this->response->setOutput(json_encode($result_json));
                    return;
                }

                foreach ($totalsList as $item){
                    if($item['code'] == "tax"){
                        $order_info['tax_value'] =  $item['value'];
                    }
                }
                $old_value = $this->getAllOrderInfo($this->request->get['order_id']);

                $this->model_sale_order->quickedit($order_info, $this->request->post);

                // add data to log_history
                $this->load->model('setting/audit_trail');
                $pageStatus = $this->model_setting_audit_trail->getPageStatus("order");
                if($pageStatus){
                    $log_history['action'] = 'update';
                    $log_history['reference_id'] = $this->request->get['order_id'];
                    $log_history['old_value'] = json_encode($old_value,JSON_UNESCAPED_UNICODE);
                    $log_history['new_value'] = json_encode($this->getAllOrderInfo($this->request->get['order_id']),JSON_UNESCAPED_UNICODE);
                    $log_history['type'] = 'order';
                    $this->load->model('loghistory/histories');
                    $this->model_loghistory_histories->addHistory($log_history);
                }

                $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

                $result_json['success'] = '1';

                $result_json['redirect'] = '1';

                $result_json['to'] = (string) $this->url->link('sale/order/info', 'order_id=' . $this->request->get['order_id'], 'SSL');

                $this->response->setOutput(json_encode($result_json));

                return;
            }


            $this->data['action'] = $this->url->link('sale/order/quick_update', 'order_id=' . $this->request->get['order_id'] . $url, 'SSL');
            $this->data['cancel'] = $this->url->link('sale/order/info', 'order_id=' . $this->request->get['order_id'], 'SSL');

            $this->template = 'sale/order_quick_edit_form.expand';
            $this->children = array(
                'common/header',
                'common/footer'
            );

            $this->response->setOutput($this->render_ecwig());
        }else {
            $this->language->load('error/not_found');

            $this->document->setTitle($this->language->get('heading_title'));

            $this->data['heading_title'] = $this->language->get('heading_title');

            $this->data['text_not_found'] = $this->language->get('text_not_found');

            $this->data['breadcrumbs'] = array();

            $this->data['breadcrumbs'][] = array(
                'text'      => $this->language->get('text_home'),
                'href'      => $this->url->link('common/home', '', 'SSL'),
                'separator' => false
            );

            $this->data['breadcrumbs'][] = array(
                'text'      => $this->language->get('heading_title'),
                'href'      => $this->url->link('error/not_found', '', 'SSL'),
                'separator' => ' :: '
            );

            $this->template = 'error/not_found.expand';
            $this->base = "common/base";

            $this->response->setOutput($this->render_ecwig());
        }


    }

  	public function delete() {
		$this->language->load('sale/order');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('sale/order');

    	if (isset($this->request->post['selected']) && ($this->validateDelete())) {
            $this->load->model('loghistory/histories');
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("order");

			foreach ($this->request->post['selected'] as $order_id) {

                $old_value = $this->getAllOrderInfo($order_id);
                if($pageStatus) {
                    $log_history['action'] = 'delete';
                    $log_history['reference_id'] = $order_id;
                    $log_history['old_value'] = json_encode($old_value,JSON_UNESCAPED_UNICODE);
                    $log_history['new_value'] = NULL;
                    $log_history['type'] = 'order';
                    $this->model_loghistory_histories->addHistory($log_history);
                }
				$this->model_sale_order->deleteOrder($order_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_order_id'])) {
				$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
			}

			if (isset($this->request->get['filter_customer'])) {
				$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_order_status_id'])) {
				$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
			}

			if (isset($this->request->get['filter_total'])) {
				$url .= '&filter_total=' . $this->request->get['filter_total'];
			}

			if (isset($this->request->get['filter_date_added'])) {
				$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
			}

			if (isset($this->request->get['filter_date_modified'])) {
				$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->redirect($this->url->link('sale/order', '', 'SSL'));
    	}

    	$this->getList();
  	}

    public function mergeOrders() {
        $this->language->load('sale/order');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('sale/order');

        if (isset($this->request->post['selected']) && ($this->validateDelete()))
        {
            $order_ids = array();

            foreach ($this->request->post['selected'] as $order_id) {
                $order_ids[] = $order_id;
            }

            if ( sizeof( $order_ids ) > 1 )
            {
                $mergeResult = $this->model_sale_order->mergeOrders($order_ids);

                if ( $mergeResult == 'DIFF_CUST' )
                {
                    $this->error['warning'] = $result_json['msg'] = $this->language->get('error_diff_customers');
                    $result_json['success'] = '0';
                    $result_json['title'] = $this->language->get('general_error_text');
                    $result_json['type'] = 'warning';
                }
                else
                {
                    $result_json['success'] = '1';
                    $result_json['redirect'] = '1';
                    $result_json['to'] = (string) $this->url->link('sale/order/update', 'order_id='.$mergeResult.'&from_merge=yes', 'SSL');
                    $this->session->data['success'] = $result_json['msg'] = $this->language->get('text_success_merge_done');
                    $result_json['type'] = 'success';
                    $result_json['title'] = $this->language->get('general_success_text');
                }
            } else {
                $this->error['warning'] = $result_json['msg'] = $this->language->get('error_less_than_two_customers');
                $result_json['success'] = '0';
                $result_json['type'] = 'warning';
                $result_json['title'] = $this->language->get('general_error_text');
            }

            $this->response->setOutput( json_encode( $result_json ) );
            return;
        }
        else
        {
            if ( !$this->error )
            {
                $this->error['warning'] = $result_json['error'] = $this->language->get('error_less_than_two_customers');
                $result_json['success'] = '0';
                $this->response->setOutput( json_encode( $result_json ) );
                return;
            }
        }

        $this->getList();
    }

  	protected function getList() {

    	$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('sale/order', '', 'SSL'),
      		'separator' => ' :: '
   		);

		$this->data['invoice'] = $this->url->link('sale/order/invoice', '', 'SSL');
		$this->data['insert'] = $this->url->link('sale/order/insert', '', 'SSL');
		$this->data['delete'] = $this->url->link('sale/order/delete', '', 'SSL');
        $this->data['mergeOrders'] = $this->url->link('sale/order/mergeOrders', '', 'SSL');

		$this->data['orders'] = array();

        $sort = $this->request->get['sort'];
        $order = $this->request->get['order'];
        $page = $this->request->get['page'];

		$data = array(
			'sort'                   => $sort,
			'order'                  => $order,
			'start'                  => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit'                  => $this->config->get('config_admin_limit')
		);

        $this->load->model('localisation/order_status');

		$order_total = $this->model_sale_order->getTotalOrders($data);

		$results = $this->model_sale_order->getOrders($data);

        //check sms app
        $this->data['smsapp'] = $this->model_sale_order->checkSmsApp();

       //For Order Assignee App
        $this->load->model("module/order_assignee");
        $this->data['isOrderAssigneeAppInstalled']=$this->model_module_order_assignee->isOrderAssigneeAppInstalled();
        if($this->data['isOrderAssigneeAppInstalled'])
        {
        if($this->user->hasPermission('custom', 'assign_order'))
        $this->data['isAllowedToAssignOrder']=true;
        else $this->data['isAllowedToAssignOrder']=false;
        $this->load->model('user/user');
        $this->data['admins_list'] = $this->model_user_user->getUsers();
        $this->data['user_id'] = $this->user->getID();
        }

    	foreach ( $results as $result )
        {
			$action = array();

			$action[] = array(
				'text' => $this->language->get('text_view'),
				'href' => $this->url->link('sale/order/info', 'order_id=' . $result['order_id'] . $url, 'SSL'),
                'icon' => 'search',
			);

            $config_order_edit = $this->config->get('config_order_edit') ? (int) $this->config->get('config_order_edit') : 100;

			if ( strtotime($result['date_added']) > strtotime('-' . $config_order_edit . ' day') )
            {
				$action[] = array(
					'text' => $this->language->get('text_edit'),
					'href' => $this->url->link('sale/order/update', 'order_id=' . $result['order_id'] . $url, 'SSL'),
                    'icon' => 'edit'
				);
			}

			$this->data['orders'][] = array(
				'order_id'      => $result['order_id'],
				'customer'      => $result['customer'],
                'customer_id' => $result['customer_id'],
				'status'        => $result['status'],
                'order_status_id' => $result['order_status_id'],
				'total'         => $this->currency->format( $result['total'], $result['currency_code'], $result['currency_value'] ),
				'date_added'    => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'date_modified' => date($this->language->get('date_format_short'), strtotime($result['date_modified'])),
				'selected'      => isset($this->request->post['selected']) && in_array($result['order_id'], $this->request->post['selected']),
				'action'        => $action,
				'gift_product'  => $result['gift_product'],
			);
		}

        $order_statuses = $this->model_localisation_order_status->getOrderStatuses();

        foreach ( $order_statuses as $index => $order_status )
        {
            $order_statuses[$index]['orders'] = array();

            foreach ( $this->data['orders'] as $order )
            {
                $order_statuses[$index]['index'] = $i;

                if ( $order['order_status_id'] == $order_status['order_status_id'] )
                {
                    $order_statuses[$index]['orders'][] = $order;
                }
            }
        }

        $this->load->model('localisation/language');

        $languages = $this->model_localisation_language->getLanguages();

        $this->data['storeLagnuages'] = $languages;


        $this->data['order_statuses'] = $order_statuses;

        $this->data['heading_title'] = $this->language->get('heading_title');


		$this->data['token'] = null;

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else if (isset($this->session->data['errors'])) {
            $this->data['error_warning'] = implode('<br />', $this->session->data['errors']);

            unset($this->session->data['errors']);
        } else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		$url = '';

		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}

		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_order_status_id'])) {
			$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
		}

		if (isset($this->request->get['filter_total'])) {
			$url .= '&filter_total=' . $this->request->get['filter_total'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['filter_date_modified'])) {
			$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}


		$url = '';

		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}

		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_order_status_id'])) {
			$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
		}

		if (isset($this->request->get['filter_total'])) {
			$url .= '&filter_total=' . $this->request->get['filter_total'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['filter_date_modified'])) {
			$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $order_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('sale/order', 'page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['filter_order_id'] = $filter_order_id;
		$this->data['filter_customer'] = $filter_customer;
		$this->data['filter_order_status_id'] = $filter_order_status_id;
		$this->data['filter_total'] = $filter_total;
		$this->data['filter_date_added'] = $filter_date_added;
		$this->data['filter_date_modified'] = $filter_date_modified;


		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

        $this->load->model('sale/customer');

        $this->data['filterElements'] = [
            'statuses' => $order_statuses,
            'totalRange' => $this->model_sale_order->getOrderMinMaxTotal(),
        ];


        $this->data['filterElements']['unhandled'] = false;
        if (isset($this->request->get['unhandled']) && $this->request->get['unhandled'] == true) {
            $this->data['filterElements']['unhandled'] = true;
        }

        $this->data['wkpos'] = 0;
        $this->load->model('wkpos/wkpos');
        if($this->model_wkpos_wkpos->is_installed()) {
            $this->data['wkpos'] = 1;

            $this->load->model('wkpos/outlet');
            $this->data['filterElements']['outlets'] = $this->model_wkpos_outlet->getOutlets();
        }

        $this->load->model('localisation/country');
        $this->load->model('localisation/zone');

		$this->data['countries'] = $this->model_localisation_country->getCountries();

		// $this->data['zones'] = $this->model_localisation_zone->getZonesLocalized();


        // get data from localization app
        $this->language->load('sale/order');

        $this->load->model('setting/setting');
        $this->load->model('localisation/language');
        $languages = $this->data['languages'] = $this->model_localisation_language->getLanguages();

        $localizationSettings = $this->model_setting_setting->getSetting('localization');

        $suffix = '';
        if ( $this->config->get('config_admin_language') != 'en' )
        {
            $specifiedLang = $languages[$this->config->get('config_admin_language')];
            $suffix = "_{$specifiedLang['code']}";
        }
		$this->data['text_telephone'] = ! empty( $localizationSettings['entry_telephone' . $suffix] ) ? $localizationSettings['entry_telephone' . $suffix] : $this->language->get('text_telephone');
		$this->data['text_fax'] = ! empty( $localizationSettings['entry_fax' . $suffix] ) ? $localizationSettings['entry_fax' . $suffix] : $this->language->get('text_fax');

        //Warehouses check
        $this->data['warehouses'] = 0;
        $warehouse_setting = $this->config->get('warehouses');
        if($warehouse_setting && $warehouse_setting['status'] == 1){
            $this->data['warehouses'] = 1;
        }

        //POS check
        $this->data['wkpos'] = 0;
        $this->load->model('wkpos/wkpos');
        if($this->model_wkpos_wkpos->is_installed())
            $this->data['wkpos'] = 1;

		// delivery slot check
		if (\Extension::isInstalled('delivery_slot') && $this->config->get('delivery_slot')['status'] == 1) {
            $this->load->model('module/delivery_slot/slots');
            $this->data['delivery_slot'] = 1;
            //$this->data['ds_days'] = $this->model_module_delivery_slot_slots->getDaysLocalized();
            //$this->data['ds_get_slots_ajax_link'] = $this->url->link('sale/order/getSlots', '', 'SSL');
        }

		if ($order_total == 0){
		    $this->template = 'sale/order/empty.expand';
		}
		else {
            $this->template = 'sale/order_list.expand';
        }

		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render_ecwig());
  	}

  	public function getForm()
    {

		$this->load->model('sale/customer');
		$this->load->language('module/product_designer');

		$this->data['register_login_by_phone_number'] = $this->registerByPhoneNumberMode();

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('sale/order

            ', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => !isset($this->request->get['order_id']) ? $this->language->get('breadcrumb_insert') : $this->language->get('breadcrumb_update'),
            'href'      => $this->url->link('sale/order', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->load->model('sale/order');
        $this->load->model('catalog/product');
        $this->load->model('sale/customer_group');
        $this->load->model('sale/affiliate');

        //$this->data['all_customers'] = $this->model_sale_customer->getCustomers();

		$this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();
        $this->data['affiliates'] = [];
		if (\Extension::isInstalled('affiliates'))
            $this->data['affiliates'] = $this->model_sale_affiliate->getAffiliates();

		if (!isset($this->request->get['order_id'])) {
			$this->data['action'] = $this->url->link('sale/order/insert', '', 'SSL');
		} else {
			$this->data['action'] = $this->url->link('sale/order/update', 'order_id=' . $this->request->get['order_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('sale/order', '', 'SSL');

    	if (isset($this->request->get['order_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
      		$order_info = $this->model_sale_order->getOrder($this->request->get['order_id']);
    	}

        //this array is get payment methods based on default country and zone

        $zonePaymentMethodsArray = [
            'country_id' => (!empty($order_info['payment_country_id'])) ? $order_info['payment_country_id'] : $this->config->get('config_country_id'),
            'zone_id' => (!empty($order_info['payment_zone_id'])) ? $order_info['payment_zone_id'] : $this->config->get('config_zone_id')
        ];

        $this->data['payment_methods'] = $this->model_sale_order->getPaymentMethods($zonePaymentMethodsArray);

        $this->data['token'] = null;

		if (isset($this->request->get['order_id'])) {
			$this->data['order_id'] = $this->request->get['order_id'];
		} else {
			$this->data['order_id'] = 0;
		}

    	if (isset($this->request->post['store_id'])) {
      		$this->data['store_id'] = $this->request->post['store_id'];
    	} elseif (!empty($order_info)) {
			$this->data['store_id'] = $order_info['store_id'];
		} else {
      		$this->data['store_id'] = '';
    	}

    	$this->data['payment_telephone'] = $order_info['payment_telephone'];

		$this->load->model('setting/store');

		$this->data['stores'] = $this->model_setting_store->getStores();

		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			$this->data['store_url'] = HTTPS_CATALOG;
		} else {
			$this->data['store_url'] = HTTP_CATALOG;
		}

		if (isset($this->request->post['customer'])) {
			$this->data['customer'] = $this->request->post['customer'];
		} elseif (!empty($order_info)) {
			$this->data['customer'] = $order_info['customer'];
		} else {
			$this->data['customer'] = '';
		}

		if (isset($this->request->post['customer_id'])) {
			$this->data['customer_id'] = $this->request->post['customer_id'];
		} elseif (!empty($order_info)) {
			$this->data['customer_id'] = $order_info['customer_id'];
		} else {
			$this->data['customer_id'] = '';
		}

		if (isset($this->request->post['customer_group_id'])) {
			$this->data['customer_group_id'] = $this->request->post['customer_group_id'];
		} elseif (!empty($order_info)) {
			$this->data['customer_group_id'] = $order_info['customer_group_id'];
		} else {
			$this->data['customer_group_id'] = '';
		}

		$this->load->model('sale/customer_group');

		$this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();

    	if (isset($this->request->post['firstname'])) {
      		$this->data['firstname'] = $this->request->post['firstname'];
		} elseif (!empty($order_info)) {
			$this->data['firstname'] = $order_info['firstname'];
		} else {
      		$this->data['firstname'] = '';
    	}

    	if (isset($this->request->post['lastname'])) {
      		$this->data['lastname'] = $this->request->post['lastname'];
    	} elseif (!empty($order_info)) {
			$this->data['lastname'] = $order_info['lastname'];
		} else {
      		$this->data['lastname'] = '';
    	}

    	if (isset($this->request->post['email'])) {
      		$this->data['email'] = $this->request->post['email'];
    	} elseif (!empty($order_info)) {
			$this->data['email'] = $order_info['email'];
		} else {
      		$this->data['email'] = '';
    	}

    	if (isset($this->request->post['telephone'])) {
      		$this->data['telephone'] = $this->request->post['telephone'];
    	} elseif (!empty($order_info)) {
			$this->data['telephone'] = $order_info['telephone'];
		} else {
      		$this->data['telephone'] = '';
    	}

    	if (isset($this->request->post['fax'])) {
      		$this->data['fax'] = $this->request->post['fax'];
    	} elseif (!empty($order_info)) {
			$this->data['fax'] = $order_info['fax'];
		} else {
      		$this->data['fax'] = '';
    	}

        $this->data['gift_product'] = $order_info['gift_product'];

		if (isset($this->request->post['affiliate_id'])) {
      		$this->data['affiliate_id'] = $this->request->post['affiliate_id'];
    	} elseif (!empty($order_info)) {
			$this->data['affiliate_id'] = $order_info['affiliate_id'];
		} else {
      		$this->data['affiliate_id'] = '';
    	}

		if (isset($this->request->post['affiliate'])) {
      		$this->data['affiliate'] = $this->request->post['affiliate'];
    	} elseif (!empty($order_info)) {
			$this->data['affiliate'] = ($order_info['affiliate_id'] ? $order_info['affiliate_firstname'] . ' ' . $order_info['affiliate_lastname'] : '');
		} else {
      		$this->data['affiliate'] = '';
    	}

		if (isset($this->request->post['order_status_id'])) {
      		$this->data['order_status_id'] = $this->request->post['order_status_id'];
    	} elseif (!empty($order_info)) {
			$this->data['order_status_id'] = $order_info['order_status_id'];
		} else {
      		$this->data['order_status_id'] = '';
    	}

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

    	if (isset($this->request->post['comment'])) {
      		$this->data['comment'] = $this->request->post['comment'];
    	} elseif (!empty($order_info)) {
			$this->data['comment'] = $order_info['comment'];
		} else {
      		$this->data['comment'] = '';
    	}

		$this->load->model('sale/customer');

		if (isset($this->request->post['customer_id'])) {
			$this->data['addresses'] = $this->model_sale_customer->getAddresses($this->request->post['customer_id']);
		} elseif (!empty($order_info)) {
			$this->data['addresses'] = $this->model_sale_customer->getAddresses($order_info['customer_id']);
		} else {
			$this->data['addresses'] = array();
		}

    	if (isset($this->request->post['payment_firstname'])) {
      		$this->data['payment_firstname'] = $this->request->post['payment_firstname'];
		} elseif (!empty($order_info)) {
			$this->data['payment_firstname'] = $order_info['payment_firstname'];
		} else {
      		$this->data['payment_firstname'] = '';
    	}

    	if (isset($this->request->post['payment_lastname'])) {
      		$this->data['payment_lastname'] = $this->request->post['payment_lastname'];
    	} elseif (!empty($order_info)) {
			$this->data['payment_lastname'] = $order_info['payment_lastname'];
		} else {
      		$this->data['payment_lastname'] = '';
    	}

    	if (isset($this->request->post['payment_company'])) {
      		$this->data['payment_company'] = $this->request->post['payment_company'];
    	} elseif (!empty($order_info)) {
			$this->data['payment_company'] = $order_info['payment_company'];
		} else {
      		$this->data['payment_company'] = '';
    	}

    	if (isset($this->request->post['payment_company_id'])) {
      		$this->data['payment_company_id'] = $this->request->post['payment_company_id'];
    	} elseif (!empty($order_info)) {
			$this->data['payment_company_id'] = $order_info['payment_company_id'];
		} else {
      		$this->data['payment_company_id'] = '';
    	}

    	if (isset($this->request->post['payment_tax_id'])) {
      		$this->data['payment_tax_id'] = $this->request->post['payment_tax_id'];
    	} elseif (!empty($order_info)) {
			$this->data['payment_tax_id'] = $order_info['payment_tax_id'];
		} else {
      		$this->data['payment_tax_id'] = '';
    	}

    	if (isset($this->request->post['payment_address_1'])) {
      		$this->data['payment_address_1'] = $this->request->post['payment_address_1'];
    	} elseif (!empty($order_info)) {
			$this->data['payment_address_1'] = $order_info['payment_address_1'];
		} else {
      		$this->data['payment_address_1'] = '';
    	}

    	if (isset($this->request->post['payment_address_2'])) {
      		$this->data['payment_address_2'] = $this->request->post['payment_address_2'];
    	} elseif (!empty($order_info)) {
			$this->data['payment_address_2'] = $order_info['payment_address_2'];
		} else {
      		$this->data['payment_address_2'] = '';
    	}

    	if (isset($this->request->post['payment_city'])) {
      		$this->data['payment_city'] = $this->request->post['payment_city'];
    	} elseif (!empty($order_info)) {
			$this->data['payment_city'] = $order_info['payment_city'];
		} else {
      		$this->data['payment_city'] = '';
    	}

    	if (isset($this->request->post['payment_postcode'])) {
      		$this->data['payment_postcode'] = $this->request->post['payment_postcode'];
    	} elseif (!empty($order_info)) {
			$this->data['payment_postcode'] = $order_info['payment_postcode'];
		} else {
      		$this->data['payment_postcode'] = '';
    	}

    	if (isset($this->request->post['payment_country_id'])) {
      		$this->data['payment_country_id'] = $this->request->post['payment_country_id'];
    	} elseif (!empty($order_info)) {
			$this->data['payment_country_id'] = $order_info['payment_country_id'];
		}

		if (isset($this->request->post['payment_zone_id'])) {
      		$this->data['payment_zone_id'] = $this->request->post['payment_zone_id'];
    	} elseif (!empty($order_info)) {
			$this->data['payment_zone_id'] = $order_info['payment_zone_id'];
		}

	if (isset($this->request->post['payment_area_id'])) {
      		$this->data['payment_area_id'] = $this->request->post['payment_area_id'];
    	} elseif (!empty($order_info)) {
			$this->data['payment_area_id'] = $order_info['payment_area_id'];
		}

    	if (isset($this->request->post['payment_method'])) {
      		$this->data['payment_method'] = $this->request->post['payment_method'];
    	} elseif (!empty($order_info)) {
			$this->data['payment_method'] = $order_info['payment_method'];
		} else {
      		$this->data['payment_method'] = '';
    	}

    	if (isset($this->request->post['payment_code'])) {
      		$this->data['payment_code'] = $this->request->post['payment_code'];
    	} elseif (!empty($order_info)) {
			$this->data['payment_code'] = $order_info['payment_code'];
		} else {
      		$this->data['payment_code'] = '';
    	}

    	if (isset($this->request->post['shipping_firstname'])) {
      		$this->data['shipping_firstname'] = $this->request->post['shipping_firstname'];
		} elseif (!empty($order_info)) {
			$this->data['shipping_firstname'] = $order_info['shipping_firstname'];
		} else {
      		$this->data['shipping_firstname'] = '';
    	}

    	if (isset($this->request->post['shipping_lastname'])) {
      		$this->data['shipping_lastname'] = $this->request->post['shipping_lastname'];
    	} elseif (!empty($order_info)) {
			$this->data['shipping_lastname'] = $order_info['shipping_lastname'];
		} else {
      		$this->data['shipping_lastname'] = '';
    	}

    	if (isset($this->request->post['shipping_company'])) {
      		$this->data['shipping_company'] = $this->request->post['shipping_company'];
    	} elseif (!empty($order_info)) {
			$this->data['shipping_company'] = $order_info['shipping_company'];
		} else {
      		$this->data['shipping_company'] = '';
    	}

    	if (isset($this->request->post['shipping_address_1'])) {
      		$this->data['shipping_address_1'] = $this->request->post['shipping_address_1'];
    	} elseif (!empty($order_info)) {
			$this->data['shipping_address_1'] = $order_info['shipping_address_1'];
		} else {
      		$this->data['shipping_address_1'] = '';
    	}

    	if (isset($this->request->post['shipping_address_2'])) {
      		$this->data['shipping_address_2'] = $this->request->post['shipping_address_2'];
    	} elseif (!empty($order_info)) {
			$this->data['shipping_address_2'] = $order_info['shipping_address_2'];
		} else {
      		$this->data['shipping_address_2'] = '';
    	}

    	if (isset($this->request->post['shipping_city'])) {
      		$this->data['shipping_city'] = $this->request->post['shipping_city'];
    	} elseif (!empty($order_info)) {
			$this->data['shipping_city'] = $order_info['shipping_city'];
		} else {
      		$this->data['shipping_city'] = '';
    	}

    	if (isset($this->request->post['shipping_postcode'])) {
      		$this->data['shipping_postcode'] = $this->request->post['shipping_postcode'];
    	} elseif (!empty($order_info)) {
			$this->data['shipping_postcode'] = $order_info['shipping_postcode'];
		} else {
      		$this->data['shipping_postcode'] = '';
    	}

    	if (isset($this->request->post['shipping_country_id'])) {
      		$this->data['shipping_country_id'] = $this->request->post['shipping_country_id'];
    	} elseif (!empty($order_info)) {
			$this->data['shipping_country_id'] = $order_info['shipping_country_id'];
		} else {
      		$this->data['shipping_country_id'] = '';
    	}


		if (isset($this->request->post['shipping_zone_id'])) {
      		$this->data['shipping_zone_id'] = $this->request->post['shipping_zone_id'];
    	} elseif (!empty($order_info)) {
			$this->data['shipping_zone_id'] = $order_info['shipping_zone_id'];
		} else {
      		$this->data['shipping_zone_id'] = '';
    	}

		if (isset($this->request->post['shipping_area_id'])) {
      		$this->data['shipping_area_id'] = $this->request->post['shipping_area_id'];
    	} elseif (!empty($order_info)) {
			$this->data['shipping_area_id'] = $order_info['shipping_area_id'];
		} else {
      		$this->data['shipping_area_id'] = '';
    	}

		$this->load->model('localisation/country');

        $this->data['countries'] = $this->model_localisation_country->getCountries();

    	if (isset($this->request->post['shipping_method'])) {
      		$this->data['shipping_method'] = $this->request->post['shipping_method'];
    	} elseif (!empty($order_info)) {
			$this->data['shipping_method'] = $order_info['shipping_method'];
		} else {
      		$this->data['shipping_method'] = '';
    	}

    	if (isset($this->request->post['shipping_code'])) {
      		$this->data['shipping_code'] = $this->request->post['shipping_code'];
    	} elseif (!empty($order_info)) {
			$this->data['shipping_code'] = $order_info['shipping_code'];
		} else {
      		$this->data['shipping_code'] = '';
    	}

        if (isset($this->request->post['delivery_info'])) {
            $this->data['delivery_info'] = $this->request->post['delivery_info'];
        } elseif (!empty($order_info)) {
            $this->data['delivery_info'] = $order_info['delivery_info'];
        } else {
            $this->data['delivery_info'] = '';
        }

        $pdModuleSettings = $this->config->get('tshirt_module');

		if (isset($this->request->post['order_product'])) {
			$order_products = $this->request->post['order_product'];
		} elseif (isset($this->request->get['order_id'])) {
            if (isset($pdModuleSettings['pd_status']) && $pdModuleSettings['pd_status'] == 1) {
                $order_products = $this->model_sale_order->getOrderProductsForProductDesigner($this->request->get['order_id']);
            } else {
                $order_products = $this->model_sale_order->getOrderProducts($this->request->get['order_id']);
            }
		} else {
			$order_products = array();
		}

		$this->load->model('catalog/product');
        $this->load->model('tool/image');

		$this->document->addScript('view/javascript/jquery/ajaxupload.js');

		$this->data['order_products'] = array();

        $pd_has_custom_design = 0;

		foreach ($order_products as $order_product) {


            if (isset($pdModuleSettings['pd_status']) && $pdModuleSettings['pd_status'] == 1 && $order_product['pd_tshirt_id']) {
                $tshirtdesign = $this->db->query('select * from tshirtdesign where did=' . $order_product['pd_tshirt_id']);
                if ($tshirtdesign->num_rows > 0) {
                    $pd_has_custom_design = 1;
                    $this->data['pd_products'][] = array(
                        'product_id' => $order_product['product_id'],
                        'order_product_id' => $order_product['order_product_id'],
                        'name' => $order_product['name'],
                        'did' => $tshirtdesign->row['did'],
                        'design_id' => $tshirtdesign->row['design_id'],
                        'front_info' => json_decode(html_entity_decode($tshirtdesign->row['front_info']), true),
                        'back_info' => json_decode(html_entity_decode($tshirtdesign->row['back_info']), true),
                        'new_design_front_image' => "modules/pd_images/merge_image/" . $tshirtdesign->row['design_id'] . '__front.png',
                        'new_design_front_thumb' => $this->model_tool_image->resize(
                            "modules/pd_images/merge_image/" . $tshirtdesign->row['design_id'] . '__front.png', 100, 100
                        ),
                        'new_design_back_image' => "modules/pd_images/merge_image/" . $tshirtdesign->row['design_id'] . '__back.png',
                        'new_design_back_thumb' => $this->model_tool_image->resize(
                            "modules/pd_images/merge_image/" . $tshirtdesign->row['design_id'] . '__back.png', 100, 100
                        ),
                        'org_front_image' => $order_product['image'],
                        'org_back_image' => $order_product['pd_back_image'],
                        'org_front_thumb' => $this->model_tool_image->resize($order_product['image'], 150, 150),
                        'org_back_thumb' => $this->model_tool_image->resize($order_product['pd_back_image'], 150, 150),
                    );
                }
            }

			if (isset($order_product['order_option'])) {
				$order_option = $order_product['order_option'];
			} elseif (isset($this->request->get['order_id'])) {
				$order_option = $this->model_sale_order->getOrderOptions($this->request->get['order_id'], $order_product['order_product_id']);
			} else {
				$order_option = array();
			}

			if (isset($order_product['order_download'])) {
				$order_download = $order_product['order_download'];
			} elseif (isset($this->request->get['order_id'])) {
				$order_download = $this->model_sale_order->getOrderDownloads($this->request->get['order_id'], $order_product['order_product_id']);
			} else {
				$order_download = array();
			}

            $rentData = null;

            if ($order_product['rent_data']) {
                $rentData = json_decode($order_product['rent_data'], true);
                $rentData['range'] = array_map(function ($value) {
                    return date("Y-m-d", $value);
                } , $rentData['range']);
            }

			$pricePerMeterData = null;
            if ($order_product['price_meter_data']) {
                $pricePerMeterData = json_decode($order_product['price_meter_data'], true);
            }

            $printingDocument = null;
            if ($order_product['printing_document']) {
                $printingDocument = json_decode($order_product['printing_document'], true);
            }

			$this->data['order_products'][] = array(
				'order_product_id' => $order_product['order_product_id'],
				'product_id'       => $order_product['product_id'],
                'image'            => \Filesystem::getUrl('image/' . $order_product['image']),
				'name'             => $order_product['name'],
				'model'            => $order_product['model'],
				'option'           => $order_option,
				'download'         => $order_download,
				'quantity'         => $order_product['quantity'],
				'rentData'         => $rentData,
				'pricePerMeterData'=> $pricePerMeterData,
                'printingDocument' => $printingDocument,
				'product_status'   => $order_product['product_status'],
				'price'            => $order_product['price'],
				'total'            => $order_product['total'],
				'tax'              => $order_product['tax'],
				'reward'           => $order_product['reward'],
                'added_by_user_type' => $this->model_sale_order->getOrderProductAddByUserType($order_product['order_product_id'])
			);
		}

        $this->data['pd_has_custom_design'] = $pd_has_custom_design;
		$this->data['http_image'] = rtrim(\Filesystem::getUrl(), '/') . '/';
		if (isset($this->request->post['order_voucher'])) {
			$this->data['order_vouchers'] = $this->request->post['order_voucher'];
		} elseif (isset($this->request->get['order_id'])) {
			$this->data['order_vouchers'] = $this->model_sale_order->getOrderVouchers($this->request->get['order_id']);
		} else {
			$this->data['order_vouchers'] = array();
		}

		$this->load->model('sale/voucher_theme');

		$this->data['voucher_themes'] = $this->model_sale_voucher_theme->getVoucherThemes();

		if (isset($this->request->post['order_total'])) {
      		$this->data['order_totals'] = $this->request->post['order_total'];
    	} elseif (isset($this->request->get['order_id'])) {
			$this->data['order_totals'] = $this->model_sale_order->getOrderTotals($this->request->get['order_id']);
		} else {
      		$this->data['order_totals'] = array();
    	}


        // get coupon code
        $index = array_search('coupon', array_column($this->data['order_totals'], 'code'));
        if ($index) {
            $coupon_code = preg_replace('/(.*)\((.*)\)(.*)/sm', '\2', $this->data['order_totals'][$index]['title']);
            $this->data['coupon'] = trim($coupon_code);

        }

		$this->data['catalog_url'] = HTTP_CATALOG;

        foreach ($this->data['order_totals'] as &$total) {
            $total['title'] = $this->language->get($total['title']);
        }

                $this->data['rewardPointsPro'] = (\Extension::isInstalled('reward_points_pro'))  ? 1 : 0;

		$this->template = 'sale/order_form.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render_ecwig());
  	}


    private function validateForm()
    {
    	if ( ! $this->user->hasPermission('modify', 'sale/order') )
        {
      		$this->error['error'] = $this->language->get('error_permission');
    	}

    	if ((utf8_strlen($this->request->post['firstname']) < 1) || (utf8_strlen($this->request->post['firstname']) > 32)) {
      		$this->error['firstname'] = $this->language->get('error_firstname');
    	}

    	// if ((utf8_strlen($this->request->post['lastname']) < 1) || (utf8_strlen($this->request->post['lastname']) > 32)) {
     //  		$this->error['lastname'] = $this->language->get('error_lastname');
		// }

		$login_register_by_phone_number_mode = $this->registerByPhoneNumberMode();

		$this->load->model('sale/customer');

		if(($login_register_by_phone_number_mode && $this->request->post['email'] != "")
		|| !$login_register_by_phone_number_mode){
			if (empty($this->request->post['telephone']) && ((utf8_strlen($this->request->post['email']) > 96) || (!preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->request->post['email'])))) {
				$this->error['email'] = $this->language->get('error_email');
			}

			$this->load->model("sale/order");

			$order = $this->model_sale_order->getOrder($this->request->get['order_id']);

			if ($this->model_sale_customer->getTotalCustomersByEmail($this->request->post['email']) && $order && $this->request->post['email'] != $order['email'] ) {
				$this->error['email'] = $this->language->get('error_exist_email');
		  	}
		}

    	if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
      		$this->error['telephone'] = $this->language->get('error_telephone');
    	}

    	if ((utf8_strlen($this->request->post['payment_firstname']) < 1) || (utf8_strlen($this->request->post['payment_firstname']) > 32)) {
      		$this->error['payment_firstname'] = $this->language->get('error_firstname');
    	}

    	// if ((utf8_strlen($this->request->post['payment_lastname']) < 1) || (utf8_strlen($this->request->post['payment_lastname']) > 32)) {
     //  		$this->error['payment_lastname'] = $this->language->get('error_lastname');
    	// }

    	// if ((utf8_strlen($this->request->post['payment_address_1']) < 3) || (utf8_strlen($this->request->post['payment_address_1']) > 128)) {
     //  		$this->error['payment_address_1'] = $this->language->get('error_address_1');
    	// }

    	// if ((utf8_strlen($this->request->post['payment_city']) < 3) || (utf8_strlen($this->request->post['payment_city']) > 128)) {
     //  		$this->error['payment_city'] = $this->language->get('error_city');
    	// }

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->post['payment_country_id']);

		if ($country_info) {
			if ($country_info['postcode_required'] && (utf8_strlen($this->request->post['payment_postcode']) < 2) || (utf8_strlen($this->request->post['payment_postcode']) > 10)) {
				$this->error['payment_postcode'] = $this->language->get('error_postcode');
			}

			// VAT Validation
			$this->load->helper('vat');

			if ($this->config->get('config_vat') && $this->request->post['payment_tax_id'] && (vat_validation($country_info['iso_code_2'], $this->request->post['payment_tax_id']) == 'invalid')) {
				$this->error['payment_tax_id'] = $this->language->get('error_vat');
			}
		}

    	if ($this->request->post['payment_country_id'] == '') {
      		$this->error['payment_country'] = $this->language->get('error_country');
    	}

    	if (!isset($this->request->post['payment_zone_id']) || $this->request->post['payment_zone_id'] == '') {
      		$this->error['payment_zone'] = $this->language->get('error_zone');
    	}

    	if ($this->request->post['payment_method'] == '') {
      		$this->error['payment_zone'] = $this->language->get('error_payment');
    	}

		if (!$this->request->post['payment_method']) {
			$this->error['payment_method'] = $this->language->get('error_payment');
		}

		// Check if any products require shipping
		$shipping = false;

		if (isset($this->request->post['order_product'])) {
			$this->load->model('catalog/product');

			foreach ($this->request->post['order_product'] as $order_product) {
				$product_info = $this->model_catalog_product->getProduct($order_product['product_id']);

				if ($product_info && $product_info['shipping']) {
					$shipping = true;
				}
			}
		}

		if ($shipping) {
			if ((utf8_strlen($this->request->post['shipping_firstname']) < 1) || (utf8_strlen($this->request->post['shipping_firstname']) > 32)) {
				$this->error['shipping_firstname'] = $this->language->get('error_firstname');
			}

			// if ((utf8_strlen($this->request->post['shipping_lastname']) < 1) || (utf8_strlen($this->request->post['shipping_lastname']) > 32)) {
			// 	$this->error['shipping_lastname'] = $this->language->get('error_lastname');
			// }

			// if ((utf8_strlen($this->request->post['shipping_address_1']) < 3) || (utf8_strlen($this->request->post['shipping_address_1']) > 128)) {
			// 	$this->error['shipping_address_1'] = $this->language->get('error_address_1');
			// }

			// if ((utf8_strlen($this->request->post['shipping_city']) < 3) || (utf8_strlen($this->request->post['shipping_city']) > 128)) {
			// 	$this->error['shipping_city'] = $this->language->get('error_city');
			// }

			$this->load->model('localisation/country');

			$country_info = $this->model_localisation_country->getCountry($this->request->post['shipping_country_id']);

			if ($country_info && $country_info['postcode_required'] && (utf8_strlen($this->request->post['shipping_postcode']) < 2) || (utf8_strlen($this->request->post['shipping_postcode']) > 10)) {
				$this->error['shipping_postcode'] = $this->language->get('error_postcode');
			}

			if ($this->request->post['shipping_country_id'] == '') {
				$this->error['shipping_country'] = $this->language->get('error_country');
			}

			if (!isset($this->request->post['shipping_zone_id']) || $this->request->post['shipping_zone_id'] == '') {
				$this->error['shipping_zone'] = $this->language->get('error_zone');
			}

			if (!$this->request->post['shipping_method']) {
				$this->error['shipping_method'] = $this->language->get('error_shipping');
			}
		}


        // Check if the total products value exceeded the max limit of coupon
        if (isset($this->request->post['coupon']) && !empty($this->request->post['coupon']))
        {
            $this->load->model('sale/coupon');
            $coupon_info = $this->model_sale_coupon->getCouponByCode($this->request->post['coupon']);

            $products_total = "";
            foreach ($this->request->post['order_product'] as $order_product) {
                $products_total += $order_product["price"];
            }

            if ($coupon_info && ($products_total > $coupon_info['maximum_limit']) && $coupon_info['maximum_limit'] > 0 )
            {
                $this->error['coupon'] = $this->language->get("error_max_coupon") . $this->currency->format($coupon_info['maximum_limit']) ;
            }
        }

		if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return $this->error ? false : true;
  	}

   	protected function validateDelete() {
    	if (!$this->user->hasPermission('modify', 'sale/order')) {
			$this->error['error'] = $this->language->get('error_permission');
    	}

		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}
  	}

	public function country() {
		$json = array();

		$this->load->model('localisation/country');

    	$country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

		if ($country_info) {
			$this->load->model('localisation/zone');

			$json = array(
				'country_id'        => $country_info['country_id'],
				'name'              => $country_info['name'],
				'iso_code_2'        => $country_info['iso_code_2'],
				'iso_code_3'        => $country_info['iso_code_3'],
				'address_format'    => $country_info['address_format'],
				'postcode_required' => $country_info['postcode_required'],
				'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
				'status'            => $country_info['status']
			);
		}

		$this->response->setOutput(json_encode($json));
	}
	public function zone() {
		$json = array();

		$this->load->model('localisation/zone');

         	$zone_info = $this->model_localisation_zone->getZone($this->request->get['zone_id']);

		if ($zone_info) {
			$this->load->model('localisation/area');

			$json = array(
				'zone_id'        => $zone_info['country_id'],
				'name'              => $zone_info['name'],
				'area'              => $this->model_localisation_area->getAreasByZoneIdAndLanguageId($this->request->get['zone_id']),
				'status'            => $zone_info['status']
			);
		}

		$this->response->setOutput(json_encode($json));
	}

	public function setPaymentlatLng(){

		$this->data['google_map_api_key'] = $this->config->get('google_map_api_key');

		$latLng = json_decode($this->data['payment_address_location']);

		if($latLng)  {

			$this->data['payment_address_lat'] = $latLng->lat;

			$this->data['payment_address_lng'] = $latLng->lng;

		}

	}

	public function info() {

		$extra_details_isset = false;
		$products_with_extra_details = [];
		$this->load->model('sale/order');
        $this->load->model('tool/image');
        $this->load->language('module/product_designer');
        $this->load->language('module/minimum_deposit');
        $this->load->model("catalog/product");
        $this->load->model('setting/setting');
        $this->load->model('module/minimum_deposit/settings');
        $this->load->model('user/user');

		if (isset($this->request->get['order_id'])) {
			$order_id = (int)$this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

        $this->data['config_custom_product_order_fields_display'] = $this->config->get('config_custom_product_order_fields_display');
        $this->data['config_product_order_fields'] = $this->config->get('config_product_order_fields');

        $this->data['order_id'] = $order_id;

        $order_info = $this->model_sale_order->getOrder($order_id);
        $next_order = $this->model_sale_order->nextOrderId($order_id);
        $previous_order = $this->model_sale_order->previousOrderId($order_id);
        $provider_order = $this->model_sale_order->getProviderOrder($order_id);
        $this->data['expandship_bill'] = $this->url->link('extension/expandship/orderBill', 'order_id='.$order_id, 'SSL');

        if ($next_order){
            $this->data['next_order'] = $this->url->link('sale/order/info', 'order_id='.$next_order, 'SSL');
        }

        if ($previous_order){
            $this->data['previous_order'] = $this->url->link('sale/order/info', 'order_id='.$previous_order, 'SSL');
        }

        if ($order_info) {
			$this->language->load('sale/order');

			$this->document->setTitle($this->language->get('heading_title'));

			$this->data['token'] = null;
            if($this->plan_id != 3) {
                $order_histories = $this->model_sale_order->getOrderHistories($order_id, 0,-1);
            }

			foreach ($order_histories as $key => $order_history){
                if ( isset($order_history['status_color']) && $order_history['status_color'] != "" ){
                    list($r, $g, $b) = sscanf($order_history['status_color'], "#%02x%02x%02x");
                    $order_histories[$key]['status_color'] = "rgba($r,$g,$b,.3)";
                }
            }

            $this->data['order_histories'] = $order_histories;
            $this->data['provider_order_data'] = $provider_order;

			$this->data['breadcrumbs'] = array();

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', '', 'SSL'),
				'separator' => false
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('sale/order', '', 'SSL'),
				'separator' => ' :: '
			);

            $this->data['breadcrumbs'][] = array(
                'text'      => $this->language->get('breadcrumb_info'),
                'href'      => $this->url->link('sale/order', '', 'SSL'),
                'separator' => ' :: '
            );

            //set reverse order url in expandship
            $this->data['reverse_expandship_order'] = $this->url->link('extension/expandship/reverseOrder', '', 'SSL');

			if( !empty($order_info['shipping_label_url']) ){
			    $this->data['shipping_label_url'] = $order_info['shipping_label_url'];
			}

            //Check if Skynet shipping gatway installed
            $labaihInstalled = $this->config->get('labaih')['status'];
            if (isset($labaihInstalled) && $labaihInstalled == 1)
            {
                $this->data['shipping']['labaih'] = true;
            }

            //Check if Skynet shipping gatway installed
            $skynetInstalled = $this->config->get('skynet_status');
            if (isset($skynetInstalled) && $skynetInstalled == 1)
            {
                $this->data['shipping']['skynet'] = true;
            }


            if( $this->config->get('quick_ship_status') == 1 ) {
                $this->data['shipping']['quick'] = true;

                //Check if this is an already exist quick order
                $this->load->model('shipping/quick_ship');
                $quick_status_ids = $this->model_shipping_quick_ship->getStatusListIds(TRUE); //Don't include ready status in ids list

                if (in_array($order_info['order_status_id'], $quick_status_ids)) {
                    $this->data['quick_order'] = true;
                    $this->data['trancking_number'] = $order_info['tracking'];
                }
            }

            //Check if AyMakan shipping gatway installed
            $aymakanInstalled = \Extension::isInstalled('aymakan') && $this->config->get('aymakan_status');
            if ($aymakanInstalled){
                $this->data['shipping']['aymakan'] = true;

            }



            // check stockzones app for get order status
            if (\Extension::isInstalled('stockzones')){
                $this->load->language('module/stockzones');
                $this->load->model('module/stockzones/settings');
                $this->load->model('module/stockzones/order');
                $stockzones_order_data = $this->model_module_stockzones_order->getStockzonesOrderData($order_id);
                if( !empty($stockzones_order_data) ) $this->data['dropshipping_order_data']['stockzones'] = $stockzones_order_data;
                else $this->data['dropshipping']['stockzones'] = true;
            }

            // check tagerly app for get order status
            if (\Extension::isInstalled('tagerly'))
            {
                $this->load->model('module/tagerly/settings');
                // get app settings
                $tagerlyOrder = $this->model_module_tagerly_settings->getTagerlyOrder($order_id);
                if ( !empty($tagerlyOrder) ) {
                    require_once(DIR_APPLICATION . "controller/module/tagerly.php");
                    $tagerlyController = new ControllerModuleTagerly($this->registry);
                    $this->data['dropshipping_order_data']['tagerly'] = $tagerlyController->getTagerlyOrderData($tagerlyOrder['tagerly_order_id']);
                }
                else{
                    $this->data['dropshipping']['tagerly'] = true;
                }
            }

            // Check Buyer Subscription App
            if (\Extension::isInstalled('buyer_subscription_plan')){
                $this->load->model('module/buyer_subscription_plan/subscription');
                $this->data['buyer_subscription_data'] = $this->model_module_buyer_subscription_plan_subscription->getOrderPaymentLog($order_id);
            }

                //Check if Mylerz shipping gatway installed
            $mylerzInstalled = \Extension::isInstalled('mylerz');
            if ($mylerzInstalled){
                $this->data['shipping']['mylerz'] = true;
            }

						//Check if Parcel shipping gatway installed
            if ( \Extension::isInstalled('parcel')){
                $this->data['shipping']['parcel'] = true;
            }

            //Check if Beez shipping gatway installed
            if ( \Extension::isInstalled('beez') && $this->config->get('beez')['status'] ){
                $this->data['shipping']['beez'] = true;
            }

            //Check if asphalt shipping gatway installed
            if ( \Extension::isInstalled("asphalt") && $this->config->get("asphalt")["status"] ){
                $this->data["shipping"]["asphalt"] = true;
                $this->data['shipping_label_url']  = $order_info['shipping_label_url'];                
            }

            //Check if nyazik shipping gatway installed
            if ( \Extension::isInstalled("nyazik") && $this->config->get("nyazik")["status"] ){
                $this->data["shipping"]["nyazik"] = true;
                $this->data['shipping_label_url']  = $order_info['shipping_label_url'];
            }

            //Check if mydhl shipping gatway installed
            if ( \Extension::isInstalled("mydhl") && $this->config->get("mydhl")["status"] ){
                $this->data["shipping"]["mydhl"] = true;
                $this->data['shipping_label_url']  = $order_info['shipping_label_url'];
            }

            //Check if Wagon shipping gatway installed
            $wagonInstalled = \Extension::isInstalled('wagon') && $this->config->get('wagon_status');
            if ($wagonInstalled){
                $this->data['shipping']['wagon'] = true;
            }


            //Check if Naqel shipping gatway installed
            $naqelInstalled = \Extension::isInstalled('naqel');
            if ($naqelInstalled){
                $this->data['shipping']['naqel'] = true;
            }

            //Check if Barq shipping gatway installed
            $barqInstalled = \Extension::isInstalled('barq') && $this->config->get('barq_status');
            if ($barqInstalled){
                $this->data['shipping']['barq'] = true;
            }

            //Check if UPS shipping installed
            $upsInstalled = \Extension::isInstalled('ups');
            if ($upsInstalled){
                $this->data['shipping']['ups'] = true;
            }

            //Check if Bowsala shipping gatway installed
            $bowsalaInstalled = \Extension::isInstalled('bowsala') && $this->config->get('bowsala_status');
            if ($bowsalaInstalled){
                $this->data['shipping']['bowsala'] = true;
                if(!empty($order_info['tracking']))
                    $this->data['shipping']['isShipped'] = true;
            }


            //Check if Redbox shipping gatway installed
            $redboxInstalled = \Extension::isInstalled('redbox') && $this->config->get('redbox_status');
            if ($redboxInstalled){
                $this->data['shipping']['redbox'] = true;
                if(!empty($order_info['tracking']))
                    $this->data['shipping']['isShipped'] = true;
            }

            //Check if vanex shipping gatway installed
            $vanexInstalled = \Extension::isInstalled('vanex') && $this->config->get('vanex_status');
            if ($vanexInstalled){
                $this->data['shipping']['vanex'] = true;
            }


		   //Check if Absher shipping gatway installed
           $absherInstalled = \Extension::isInstalled('absher') && $this->config->get('absher_status');
           if ($absherInstalled){
               $this->data['shipping']['absher'] = true;
           }


            //Check if Postaplus shipping gatway installed
            $postaplusInstalled = \Extension::isInstalled('postaplus');
            if ($postaplusInstalled){
                $this->data['shipping']['postaplus'] = true;
            }

            //Check if ExpandShip is installed
            $expandShipInstalled = \Extension::isInstalled('expandship');
            $expandShipConfig =$this->config->get('expandship');
            if ($expandShipInstalled && $expandShipConfig && $expandShipConfig['status'] == 1){
                $this->data['shipping']['expandship'] = true;
            }



            //Check if Bosta shipping gatway installed
            $bostaInstalled = $this->config->get('bosta_status');
            if (isset($bostaInstalled))
            {
                $this->data['shipping']['bosta'] = true;
            }

			//Check if Ersal shipping gatway installed

            if ( $this->config->get('ersal_status') == 1 )
            {
                $this->data['shipping']['ersal'] = true;
            }

            //Check if DiggiPacks shipping gatway installed

            if ( $this->config->get('diggi_packs_status') == 1 )
            {
                $this->data['shipping']['diggi_packs'] = true;
            }

            $aramexInstalled = $this->config->get('aramex_status');
            if (isset($aramexInstalled))
            {
                $this->data['shipping']['aramex'] = true;
            }

			//Check if ShipA is installed
			$shipaInstalled = $this->config->get('shipa_status');
            if (isset($shipaInstalled))
            {
                $this->data['shipping']['shipa'] = true;
            }

            $smsaInstalled = $this->config->get('smsa_status');
            if (isset($smsaInstalled))
            {
                $this->data['shipping']['smsa'] = true;
			}

            $hitdhlexpressInstalled = $this->config->get('dhl_express_status');
            if (isset($hitdhlexpressInstalled))
            {
                $this->data['shipping']['hitdhlexpress'] = true;
			}

            $tookanInstalled = $this->config->get('tookan_status');
            if (isset($tookanInstalled))
            {
                $this->data['shipping']['tookan'] = true;
            }

            $fds_setting = $this->config->get('fds');
            if ( $fds_setting['status'] == '1' )
            {

                $this->data['shipping']['fds'] = true;
            }

            if (\Extension::isInstalled('oto')) {
              $this->data['shipping']['oto'] = true;
			}

			if ($this->config->get('armada_status') == '1') {
				$this->data['shipping']['armada'] = true;
			}

            if ($this->config->get('shipa_delivery')) {
                $this->data['shipping']['shipa_delivery'] = true;
			}

			if ($this->config->get('catalyst_status') == '1') {
				$this->data['shipping']['catalyst'] = true;
			}

            if ($this->config->get('r2s_logistics')) {
                $this->data['shipping']['r2s_logistics'] = true;
            }

            if ($this->config->get('fastcoo')) {
                $this->data['shipping']['fastcoo'] = true;
            }

            if ($this->config->get('beone_express')) {
                $this->data['shipping']['beone_express'] = true;
            }

            if ($this->config->get('esnad')) {
                $this->data['shipping']['esnad'] = true;
            }

            if ($this->config->get('beone_fulfillment')) {
                $this->data['shipping']['beone_fulfillment'] = true;
            }

            if ($this->config->get('fedex_domestic_account')) {
                $this->data['shipping']['fedex_domestic'] = true;
            }

            $bmDeliveryInstalled = (int)$this->config->get('bm_delivery')['status'] ?? 0;
            if ($bmDeliveryInstalled) {
                $this->data['shipping']['bm_delivery'] = true;
            }

            if (\Extension::isInstalled('manual_shipping')) {
                $manualShipping = $this->config->get('manual_shipping');

                if (isset($manualShipping['status']) && $manualShipping['status'] == 1) {
                    $this->data['shipping']['manual_shipping'] = true;
                }
            }

            if ($this->config->get('fedex')) {
                $this->data['shipping']['fedex'] = true;
            }

            $querySMSModule = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'smshare'");

            if ($querySMSModule->num_rows) {
                $this->data['smsapp'] = true;
            }

			if (\Extension::isInstalled('whatsapp')) {
				$queryWhatsModule = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'whatsapp'");

				if ($queryWhatsModule->num_rows) {
					$this->data['whatsapp'] = true;
				}
			}
			if (\Extension::isInstalled('whatsapp_cloud')) {
					$this->data['whatsapp'] = true;
			}
            if(\Extension::isInstalled('customer_notifications') &&$this->config->get('customer_notifications')['status']==1
            &&$this->config->get('customer_notifications')['order_status_notify']==1){
					$this->data['orderStatusNotify'] = true;

			}

            $this->load->model('localisation/language');

            $this->load->model('localisation/country');

            $this->data['countries'] = $this->model_localisation_country->getCountries();

            $languages = $this->model_localisation_language->getLanguages();

            $this->data['storeLagnuages'] = $languages;


			$this->data['invoice'] = $this->url->link('sale/order/invoice', 'order_id=' . (int)$this->request->get['order_id'], 'SSL');

			//ATH was here at 3:02 am 2-4-2019
			$this->data['zajil_shipment'] = $this->url->link('sale/zajil_create_shipment','order_id=' . (int)$this->request->get['order_id'], 'SSL');
			$this->data['zajil_status'] = $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'zajil', true);

            if ($this->data['zajil_status']) {
                $this->data['shipping']['zajil'] = true;
            }

            $this->data['bosta_shipment'] = $this->url->link('shipping/bosta/create', 'order_id=' . (int)$this->request->get['order_id'], 'SSL');


            $this->data['diggi_packs_shipment'] = $this->url->link('shipping/diggi_packs/create', 'order_id=' . (int)$this->request->get['order_id'], 'SSL');
            $this->data['naqel_shipment'] = $this->url->link('shipping/naqel/create', 'order_id=' . (int)$this->request->get['order_id'], 'SSL');
            $this->data['bowsala_shipment'] = $this->url->link('shipping/bowsala/create', 'order_id=' . (int)$this->request->get['order_id'], 'SSL');
            $this->data['redbox_shipment'] = $this->url->link('shipping/redbox/create', 'order_id=' . (int)$this->request->get['order_id'], 'SSL');

            $this->data['vanex_shipment'] = $this->url->link('shipping/vanex/create', 'order_id=' . (int)$this->request->get['order_id'], 'SSL');
            $this->data['ersal_shipment'] = $this->url->link('shipping/ersal/create', 'order_id=' . (int)$this->request->get['order_id'], 'SSL');
            $this->data['barq_shipment'] = $this->url->link('shipping/barq/create', 'order_id=' . (int)$this->request->get['order_id'], 'SSL');
            $this->data['wagon_shipment'] = $this->url->link('sale/wagon_shipment_order/create', 'order_id=' . (int)$this->request->get['order_id'], 'SSL');
			$this->data['labaih_shipment'] = $this->url->link('sale/labaih_shipment_order/create', 'order_id=' . (int)$this->request->get['order_id'], 'SSL');
			$this->data['mylerz_shipment'] = $this->url->link('sale/mylerz_shipment_order/create', 'order_id=' . (int)$this->request->get['order_id'], 'SSL');
            $this->data['aymakan_shipment'] = $this->url->link('sale/aymakan_shipment_order/create', 'order_id=' . (int)$this->request->get['order_id'], 'SSL');
            $this->data['absher_shipment'] = $this->url->link('sale/absher_shipment_order/create', 'order_id=' . (int)$this->request->get['order_id'], 'SSL');
	        $this->data['postaplus_shipment'] = $this->url->link('sale/postaplus_shipment_order/create', 'order_id=' . (int)$this->request->get['order_id'], 'SSL');
            $this->data['skynet_shipment']= $this->url->link('shipping/skynet/create', 'order_id=' . (int)$this->request->get['order_id'], 'SSL');
            $this->data['shipa_shipment'] = $this->url->link('shipping/shipa/shipa_create_shipment', 'order_id=' . (int)$this->request->get['order_id'], 'SSL');
            $this->data['shipping_ups'] = $this->url->link('shipping/ups/create_shipment', 'order_id=' . (int)$this->request->get['order_id'], 'SSL');
						$this->data['beez_shipment'] = $this->url->link('sale/beez_shipment_order/create', 'order_id=' . (int)$this->request->get['order_id'], 'SSL');
            $this->data["asphalt_shipment"] = $this->url->link("sale/asphalt_shipment_order/create", "order_id=" . (int)$this->request->get["order_id"], "SSL");
            $this->data["nyazik_shipment"] = $this->url->link("sale/nyazik_shipment_order/create", "order_id=" . (int)$this->request->get["order_id"], "SSL");
            $this->data["mydhl_shipment"] = $this->url->link("sale/mydhl_shipment_order/create", "order_id=" . (int)$this->request->get["order_id"], "SSL");
            $this->data['expandship'] = $this->url->link('extension/expandship/createShipment', 'order_id=' . (int)$this->request->get['order_id'], 'SSL');
            $this->data['parcel_shipment'] = $this->url->link('sale/parcel_shipment_order/create', 'order_id=' . (int)$this->request->get['order_id'], 'SSL');

			$this->data['aramex_shipment'] = $this->url->link('sale/aramex_create_shipment', 'order_id=' . (int)$this->request->get['order_id'], 'SSL');

            $this->data['smsaShipment'] = $this->url->link('shipping/smsa/createShipment','order_id=' . $order_info['order_id'],'SSL');

            $this->data['quick_shipment'] = $this->url->link('shipping/quick_ship/pushOneOrder','order_id=' . $order_info['order_id'],'SSL');

			$this->data['dhl_express_shipment'] = $this->url->link('shipping/dhl_express/createShipment', 'order_id=' . (int)$this->request->get['order_id'], 'SSL');

            $this->data['fds_shipment'] = $this->url->link('shipping/fds/createShipment', 'order_id=' . (int)$this->request->get['order_id'], 'SSL');

            $this->data['tookan_shipment'] = $this->url->link('shipping/tookan/createShipment', 'order_id=' . (int)$this->request->get['order_id'], 'SSL');

			$this->data['cancel'] = $this->url->link('sale/order', '', 'SSL');

			$this->data['order_id'] = $this->request->get['order_id'];

			if ($order_info['invoice_no']) {
				$this->data['invoice_no'] = $order_info['invoice_prefix'] . $order_info['invoice_no'];
			} else {
				$this->data['invoice_no'] = '';
			}

			$this->data['time_added'] = date('H:i', strtotime($this->getDateByCurrentTimeZone($order_info['date_added'])));

			$this->data['payment_telephone'] = $order_info['payment_telephone'];

			$this->data['store_name'] = $order_info['store_name'];
			$this->data['store_url'] = $order_info['store_url'];
			$this->data['firstname'] = $order_info['firstname'];
			$this->data['lastname'] = $order_info['lastname'];
			$this->data['customer_status'] = $order_info['customer_status'];
			$this->data['customer_id'] = $order_info['customer_id'];
            $this->data['shipping_tracking_url'] = $order_info['shipping_tracking_url'];
            $this->data['payment_code'] = $order_info['payment_code'];
            $this->data['payment_trackId'] = $order_info['payment_trackId'];

            /**
             * Enable Refund and Inquiry buttons if use has modification permission
             */
            $this->data['payment_actions_enable'] = false;
            if (($this->user->hasPermission('modify', 'sale/order')))
            {
                $this->data['payment_actions_enable'] = true;
            }

			if ($order_info['customer_id']) {
				$this->data['customer'] = $this->url->link('sale/customer/update', 'customer_id=' . $order_info['customer_id'], 'SSL');
			} else {
				$this->data['customer'] = '';
			}

			$this->load->model('sale/customer_group');

			$customer_group_info = $this->model_sale_customer_group->getCustomerGroup($order_info['customer_group_id']);

			if ($customer_group_info) {
				$this->data['customer_group'] = $customer_group_info['name'];
			} else {
				$this->data['customer_group'] = '';
			}

			$order_info['telephone'] = $this->hideCountryCode($order_info['telephone'], $order_info['payment_country_id'], $this->config->get('config_order_hide_country_code'));

			$this->data['email'] = $order_info['email'];
			$this->data['telephone'] = $order_info['telephone'];
			$this->data['fax'] = $order_info['fax'];
			$this->data['comment'] = nl2br($order_info['comment']);
			$this->data['shipping_method'] = $order_info['shipping_method'];
			$this->data['payment_method'] = $order_info['payment_method'];
			$this->data['total'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value']);


			if ($order_info['total'] < 0) {
				$this->data['credit'] = $order_info['total'];
			} else {
				$this->data['credit'] = 0;
			}

			$this->load->model('sale/customer');

			$this->data['credit_total'] = $this->model_sale_customer->getTotalTransactionsByOrderId($this->request->get['order_id']);

			$this->data['reward'] = $order_info['reward'];

			$this->data['reward_total'] = $this->model_sale_customer->getTotalCustomerRewardsByOrderId($this->request->get['order_id']);

			$this->data['affiliate_firstname'] = $order_info['affiliate_firstname'];
			$this->data['affiliate_lastname'] = $order_info['affiliate_lastname'];

			if ($order_info['affiliate_id']) {
				$this->data['affiliate'] = $this->url->link('sale/affiliate/update', 'affiliate_id=' . $order_info['affiliate_id'], 'SSL');
			} else {
				$this->data['affiliate'] = '';
			}

			$this->data['commission'] = $this->currency->format($order_info['commission'], $order_info['currency_code'], $order_info['currency_value']);

			$this->load->model('sale/affiliate');
            if (\Extension::isInstalled('affiliates')){
                $this->data['commission_total'] = $this->model_sale_affiliate->getTotalTransactionsByOrderId($this->request->get['order_id']);
            }

			$this->load->model('localisation/order_status');

			$order_status_info = $this->model_localisation_order_status->getOrderStatus($order_info['order_status_id']);

			if ($order_status_info) {
				$this->data['order_status'] = $order_status_info['name'];
                list($r, $g, $b) = sscanf($order_status_info['bk_color'], "#%02x%02x%02x");
                $order_status_info['bk_color'] = "rgba($r,$g,$b,.3)";
                $this->data['order_status_color'] = $order_status_info['bk_color'];
			} else {
				$this->data['order_status'] = '';
                $this->data['order_status_color'] = '';
			}

			$this->data['ip'] = $order_info['ip'];
			$this->data['forwarded_ip'] = $order_info['forwarded_ip'];
			$this->data['user_agent'] = $order_info['user_agent'];
			$this->data['accept_language'] = $order_info['accept_language'];
			$this->data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));
			$this->data['date_modified'] = date($this->language->get('date_format_short'), strtotime($order_info['date_modified']));
            $this->data['original_date_added'] = $order_info['date_added'];
			$this->data['gift_product'] = $order_info['gift_product'];
			$this->data['payment_firstname'] = $order_info['payment_firstname'];
			$this->data['payment_lastname'] = $order_info['payment_lastname'];
			$this->data['payment_company'] = $order_info['payment_company'];
			$this->data['payment_company_id'] = $order_info['payment_company_id'];
			$this->data['payment_tax_id'] = $order_info['payment_tax_id'];
			$this->data['payment_address_1'] = $order_info['payment_address_1'];
			$this->data['payment_address_2'] = $order_info['payment_address_2'];
			$this->data['payment_city'] = $order_info['payment_city'];
			$this->data['payment_area'] = $order_info['payment_area'];
			$this->data['payment_postcode'] = $order_info['payment_postcode'];
            $this->data['payment_zone'] = $order_info['payment_zone'];
			$this->data['payment_zone_id'] = $order_info['payment_zone_id'];
			$this->data['payment_zone_code'] = $order_info['payment_zone_code'];
            $this->data['payment_country'] = $order_info['payment_country'];
			$this->data['payment_country_id'] = $order_info['payment_country_id'];
            $this->data['payment_address_location'] = $order_info['payment_address_location'];
            $this->data['shipping_firstname'] = $order_info['shipping_firstname'];
			$this->data['shipping_lastname'] = $order_info['shipping_lastname'];
			$this->data['shipping_company'] = $order_info['shipping_company'];
			$this->data['shipping_address_1'] = $order_info['shipping_address_1'];
			$this->data['shipping_address_2'] = $order_info['shipping_address_2'];
			$this->data['shipping_city'] = $order_info['shipping_city'];
			$this->data['shipping_area'] = $order_info['shipping_area'];
          	$this->data['shipping_postcode']=$order_info['shipping_postcode'];
            $this->data['shipping_zone'] = $order_info['shipping_zone'];
			$this->data['shipping_zone_id'] = $order_info['shipping_zone_id'];
			$this->data['shipping_zone_code'] = $order_info['shipping_zone_code'];
            $this->data['shipping_country'] = $order_info['shipping_country'];
			$this->data['shipping_country_id'] = $order_info['shipping_country_id'];
            $this->data['shipping_address_location'] = $order_info['shipping_address_location'];
            $this->data['order_comment'] = $order_info['comment'];
            $this->data['archived'] = $order_info['archived'];
            $this->data['delivery_info'] = $order_info['delivery_info'];

            $this->setPaymentlatLng();

            if ($order_info['shipping_code'] == 'ups.65') {

                $this->language->load('shipping/ups');

                $this->data['shipping']['ups'] = 1;

            }
            if ($order_info['shipping_code'] == 'smsa.smsa') {

                $this->language->load('shipping/smsa');

                $this->data['shipping']['smsa']['status'] = 1;

                $this->data['smsaShipment'] = $this->url->link(
                    'shipping/smsa/createShipment',
                    'order_id=' . $order_info['order_id'],
                    'SSL'
                );

                $this->data['shipping']['smsa']['updateStatusLink'] = $this->url->link(
                    'shipping/smsa/updateStatus',
                    'order_id=' . $order_info['order_id'],
                    'SSL'
                );


                $this->load->model('shipping/smsa');

                $smsaShipmentInfo = $this->model_shipping_smsa->getShipmentInfo($order_info['order_id']);
                if($smsaShipmentInfo){
                    $this->data['shipmentDone'] = $this->url->link(
                        'shipping/smsa/shipmentDetails',
                        'order_id=' . $order_info['order_id'].'&awb=' . $smsaShipmentInfo['shipment_code'],
                        'SSL'
                    );
                }

                $this->data['shipping']['smsa']['details'] = $smsaShipmentInfo;
                $this->data['shipping']['smsa']['shipmentStatus'] = $this->language->get(
                    'smsa_shipment_status_' . $smsaShipmentInfo['shipment_status']
				 );

                $this->document->addScript('view/javascript/shipping/smsa_order.js');
            }

            if($order_info['shipping_code'] == 'ogo.ogo')
            {
                 $this->data['shipping']['ogo'] = 1;
            }


			$this->data['products'] = array();

            $pdModuleSettings = $this->config->get('tshirt_module');

            //warehouses App Check
            $warehouse_setting = $this->config->get('warehouses');
            $this->data['warehouses'] = false;

            $country_id = $order_info['shipping_country_id'] ? $order_info['shipping_country_id'] : $order_info['payment_country_id'];
            $zone_id    = $order_info['shipping_zone_id'] ? $order_info['shipping_zone_id'] : $order_info['payment_zone_id'];
            $area_id    = $order_info['shipping_area_id'] ? $order_info['shipping_area_id'] : $order_info['payment_area_id'];


            if(isset($warehouse_setting) && $warehouse_setting['status'] == 1 && $country_id)
            {
                $this->load->model('module/warehouses');

                $address = [ 'country_id' => $country_id, 'zone_id' => $zone_id];

                $wrProducts = $this->model_module_warehouses->getGroupProducts($address, $this->request->get['order_id']);

                if(count($wrProducts['products']) > 0){
                    $products = $wrProducts['products'];
                    $warehouses_products = $wrProducts['warehouses_products'];
                    $combined_wrs_costs       = $wrProducts['wrs_costs'];
                    $this->data['wrs_names']  = $wrProducts['wrs_name'];
                    $this->data['wrs_durations']  = $wrProducts['wrs_duration'];
                    $this->data['warehouses'] = true;
                    $this->data['warehouses_list'] = $this->model_module_warehouses->getWarehouses(['active' => 1]);
                }
            }
             $this->data['seller_based'] = false;

            if ($this->MsLoader->isInstalled() && \Extension::isInstalled('seller_based')&& $this->config->get('seller_based_status') == 1){
                $sellerCartProductsData = json_decode( preg_replace("/[\r\n]+/", " ", strip_tags($this->model_sale_order->getShipmentDetails($this->request->get['order_id'])['details'])), true );
                $products = $sellerCartProductsData['products'];
                $this->data['seller_names']=$sellerCartProductsData['seller_names'];
                $this->data['products_prices']  = $sellerCartProductsData['products_total_Price'];
                $this->data['selectedShippingMethodName']  = $sellerCartProductsData['selectedShippingMethodName'];
                $this->data['selectedShippingMethodValue']  = $sellerCartProductsData['selectedShippingMethodValue'];
                $this->data['totalAfterShippingCost']  = $sellerCartProductsData['totalAfterShippingCost'];
                $this->data['seller_based'] = true;
            }
            else{
            if(!$this->data['warehouses'])
            {

                if (isset($pdModuleSettings['pd_status']) && $pdModuleSettings['pd_status'] == 1) {
                    $products = $this->model_sale_order->getOrderProducts($this->request->get['order_id'],true);

                } else {

                    $products = $this->model_sale_order->getOrderProducts($this->request->get['order_id']);
                }

            }
        }

            /////////////////////
             ////Paytr Payment Geteway Refunding
             $paytrStatus_settings = $this->config->get('paytr_status');
             $isPaytrPaymentInstalled = (\Extension::isInstalled('paytr') && $paytrStatus_settings == 1) ? 1 : 0;
             $orderInfo = $this->model_sale_order->getOrder($this->request->get['order_id']);
              if($isPaytrPaymentInstalled && $orderInfo['payment_code']=='paytr')
              {
                  $paytrRefundStatus=$this->config->get('paytr_refund_order_status');
                  if($paytrRefundStatus == 1)
                  {
                     $this->data['showRefundButton']=true;
                     $this->load->model('extension/payment_transaction');
                     $paymentTransaction = $this->model_extension_payment_transaction->selectByOrderId($this->request->get['order_id']);
                     $paymentTransactionDetails = json_decode($paymentTransaction['details']);
                     if($paymentTransactionDetails->isRefunded==1)
                        $this->data['orderRefunded']=true;
                     else
                        $this->data['orderRefunded']=false;
                  }
              }

          ///////////////////// Get dropna order product's status
            $dropna_products_status = [];
            $dropna_settings = $this->config->get('dropna');
            if($dropna_settings['status'] == 1){
                $product_ids = [];
                foreach ($products as $product) {
                    $product_ids[] = $product['product_id'];
                }
                ////Check if the order has products mapped to dropna
                $this->load->model('module/dropna');
                //get dropna mapped ids
                $dropnaProduct_ids = $this->model_module_dropna->getMappedProducts($product_ids, true);
                if(count($dropnaProduct_ids)){
                    $this->data['dropna_status_badges'] = [
                                                          'pending' => '<span class="label label-warning">'.$this->language->get('status_pending').'</span>',
                                                          'inprogress' => '<span class="label label-info">'.$this->language->get('status_inprogress').'</span>',
                                                          'complete' => '<span class="label label-success">'.$this->language->get('status_complete').'</span>',
                                                          'unknowen class="label label-danger"' => '<span>'.$this->language->get('status_unknowen').'</span>'
                                                         ];
                    $this->data['has_dropna_products'] = true;
                    //Check dropna auth
                    $dropnaClient = $this->model_module_dropna->getDropnaClient();
                    if($dropnaClient){
                        //get dropna order ids of store order id
                        $dropnaOrder_ids = $this->model_module_dropna->getMappedOrder($order_id);
                        if(count($dropnaOrder_ids)){
                            $dropnaData['order_ids']       = $dropnaOrder_ids; // array
                            $dropnaData['apikey']          = DROPNA_APIKEY;
                            $dropnaData['client_api_id']   = $dropnaClient['id'];
                            $dropnaData['store_code']      = $dropnaClient['store_code'];
                            $dropnaData['client_id']       = $dropnaClient['client_id'];
                            $dropnaData['store_to_dropna'] = 1;

                            $soap_do     = curl_init();
                            curl_setopt($soap_do, CURLOPT_URL, DROPNA_DOMAIN."api/v1/getOrderSts");
                            curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
                            curl_setopt($soap_do, CURLOPT_TIMEOUT, 10);
                            curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
                            curl_setopt($soap_do, CURLOPT_POST, true);
                            curl_setopt($soap_do, CURLOPT_POSTFIELDS, http_build_query($dropnaData));
                            // curl_setopt($soap_do, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
                            $response = curl_exec($soap_do);
                            $responseArr = json_decode($response, true);
                            $err = curl_error();
                            curl_close();
                            if($responseArr['status'] == 'success' && $responseArr['data'])
                                $dropna_products_status = $responseArr['products_status'];
                        }
                    }/////////////////
                }
            }
            /////////////////////////////////////////////
            if($this->data['has_dropna_products'])
                $this->data['dropna_order_fullfil'] = true;

            $this->initializer(['sku' => 'module/product_variations']);

            $this->data['order_attributes'] = $order_info['order_attributes'];
            $this->data['storable_products'] = [];

            $pd_has_custom_design = 0;

			foreach ($products as $product) {
                if($this->data['has_dropna_products'] && $dropnaProduct_ids[$product['product_id']]){
                    $dropna_product_status = $dropna_products_status[ $dropnaProduct_ids[$product['product_id']] ];
                    if($dropna_product_status != 'complete')
                        $this->data['dropna_order_fullfil'] = false;
                }

				$option_data = array();

				if ( isset( $pdModuleSettings['pd_status'] ) && $pdModuleSettings['pd_status'] == 1 )
				{
					$product['pd_front_info'] = json_decode( html_entity_decode($product['front_info']), true )[0];
					$product['pd_back_info'] = json_decode( html_entity_decode($product['back_info']), true )[0];

					if ( isset( $product['pd_front_info']['image'] ) )
					{
						$imageExtension = end(explode(".",$product['pd_front_info']['image']));
                        $product['pd_front_info']['attached_image'] = \Filesystem::getUrl('image/modules/pd_images/upload_image/' . $product['pd_front_info']['image']);
                        $product['pd_front_info']['image'] = \Filesystem::getUrl('image/modules/pd_images/merge_image/' . $product['design_id'] . '__front.png');
					}

					if ( isset( $product['pd_back_info']['image'] ) )
					{
						$imageExtension = end(explode(".",$product['pd_back_info']['image']));
                        $product['pd_back_info']['attached_image'] = \Filesystem::getUrl('image/modules/pd_images/upload_image/' . $product['pd_back_info']['image']);
                        $product['pd_back_info']['image'] = \Filesystem::getUrl('image/modules/pd_images/merge_image/' . $product['design_id'] . '__back.png');
					}
				}
				// check if product has codes
                if($product['code_generator'] != null)
                {
                    $productCodeData = json_decode($product['code_generator'] ,true);
                }
                $this->load->model('module/product_bundles/settings');
                if ($this->model_module_product_bundles_settings->isActive()) {
                    //order_product_bundles if exist
                    $orderProductBundles = $this->model_sale_order->getOrderProductBundles($product['order_product_id'] , $this->request->get['order_id']);
                    foreach ($orderProductBundles as $key => $bundle) {
                        $orderProductBundles[$key]['thumb']  = \Filesystem::getUrl('image/' . ($bundle['product_image'] ?: 'no_image.jpg'));
                    }
                }
				$options = $this->model_sale_order->getOrderOptions($this->request->get['order_id'], $product['order_product_id']);

                $productOptionValueId = [];

				foreach ($options as $option) {
					if ($option['type'] != 'file') {
						$option_data[] = array(
							'name'  => $option['name'],
							'value' => $option['value'],
							'type'  => $option['type'],
                            'quantity' => $option['quantity']
						);
					} else {
						$option_data[] = array(
							'name'  => $option['name'],
							'value' => utf8_substr($option['value'], 0, utf8_strrpos($option['value'], '.')),
							'type'  => $option['type'],
							'href'  => $this->url->link('sale/order/download', 'order_id=' . $this->request->get['order_id'] . '&order_option_id=' . $option['order_option_id'], 'SSL')
						);
					}

                    $productOptionValueId[] = $option['product_option_value_id'];
				}

                $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

                $this->data['seller'] = null;

                if($queryMultiseller->num_rows) {
                    $this->language->load('multiseller/multiseller');
                    $this->data['column_seller'] = $this->language->get('ms_seller');
                    // todo check
                    $seller = $this->MsLoader->MsSeller->getSeller(
                        $this->MsLoader->MsProduct->getSellerId($product['product_id']),
                        array(
                            'product_id' => $product['product_id']
                        )
                    );
                    $sellerAddress = $this->model_sale_customer->getAddress(
                        $this->model_sale_customer->getDefaultAddressId($seller['seller_id'])
                    );

                    $this->data['seller'] = $seller;
                    $this->data['seller']['address'] = $sellerAddress;
                }

                if (!empty($this->session->data['uniqid'][$product['product_id']])) {
                    $product['pd_module_status'] = 1;

                    $product['custom_design'] = 'modules/pd_images/merge_image/' . $this->session->data['uniqid'][$product['product_id']] . '__front.png';
                    $product['image'] = $product['custom_design'];
                }

                $rentData = null;

                if ($product['rent_data']) {
                    $rentData = json_decode($product['rent_data'], true);
                    $rentData['range'] = array_map(function ($value) {
                        return date("Y-m-d", $value);
                    } , $rentData['range']);
                }
                $pricePerMeterData = null;
                if ($product['price_meter_data']) {
                    $pricePerMeterData = json_decode($product['price_meter_data'], true);
                }

                $remaining_amount = null;
                $main_price = null;

                if ($this->model_module_minimum_deposit_settings->isActive()) {

                    if ($product['main_price']) {
                        $main_price =$product['main_price'];
                    }

                    if ($product['remaining_amount']) {
                        $remaining_amount =$product['remaining_amount'];
                    }

                    $this->data['remaining_total'] = null;
                    $this->data['remaining_total'] = $this->model_sale_order->getOrderProductsRemainingTotal($order_id);

                }
                if ($product['printing_document']) {
                    $printingDocument = json_decode($product['printing_document'], true);
				}

				$extra_details = [];

				if (! empty( $product['extra_details'] )) {
					$extra_details = json_decode($product['extra_details'], true);
					$products_with_extra_details[$product['product_id']]['product'] = $product;
					$products_with_extra_details[$product['product_id']]['extra_details'] = $extra_details;
					$extra_details_isset = true;
					$curtain_seller = $extra_details['curtain_seller'];
				}

                if ((bool)$this->data['config_custom_product_order_fields_display'] && count($this->data['config_product_order_fields']) > 0) {
                    $skuInfo = null;
                    if ($this->sku->isActive()) {
                        $skuInfo = $this->sku->getProductVariationByValuesIds(
                            $product['product_id'], array_column(
                                $this->sku->getOptionValuesIds($productOptionValueId),
                                'option_value_id'
                            )
                        );
                        if ($skuInfo) {
                            $product['barcode'] = $skuInfo['product_barcode'];
                            $product['sku'] = $skuInfo['product_sku'];
                        }
                    } else {
                        $missingProductData = $this->model_catalog_product->getProductSpecificFields(
                            $product['product_id'], ['barcode', 'sku']
                        );
                        $product = array_merge($product, $missingProductData);
                    }

                    if ($product['barcode'] != '') {
                        $barcodeGenerator = (new BarcodeGenerator())
                            ->setType($this->config->get('config_barcode_type'))
                            ->setBarcode($product['barcode']);

                        $product['barcode_image'] = $barcodeGenerator->generate();
                    }
                }
                    if (\Extension::isInstalled('seller_based')&& $this->config->get('seller_based_status') == 1){
                        $productOrignialID=$product['product_id'];
                        $productName=$product['name'];
                    }else{
                        $productOrignialID=$product['original_id'];
                        $productName=$product['product_name'];
                    }

                // << Product Option Image PRO module
                if (isset($product['image'])) {
                    $this->load->model('module/product_option_image_pro');
                    $poip_installed = $this->model_module_product_option_image_pro->installed();
                    if ($poip_installed) {
                        $product['image'] = $this->model_module_product_option_image_pro->getProductOrderImage($product['product_id'], $options, $product['image']);
                    }
                }
                // >> Product Option Image PRO module

				$prData = [
                    'seller' => array(
                        'seller_id' => isset($seller['seller_id']) && $queryMultiseller->num_rows ? $seller['seller_id'] : '',
                        'address' => isset($seller['ms.nickname']) && $queryMultiseller->num_rows ? $sellerAddress : '',
                        'nickname' => isset($seller['ms.nickname']) && $queryMultiseller->num_rows ? $seller['ms.nickname'] : '',
                        'link'  => isset( $seller['seller_id'] ) && $queryMultiseller->num_rows ? $this->url->link('multiseller/seller/update?seller_id=') . $seller['seller_id'] : ''
                    ),
                    'order_product_id' => $product['order_product_id'],
                    'image'            => \Filesystem::getUrl('image/' . ($product['image'] ?: 'no_image.jpg')),
                    'product_id'       => $product['product_id'],
                    'original_id'      => $productOrignialID,
                    'name'    	 	   => $productName,
                    'model'    		   => $product['model'],
                    'option'           => $option_data,
                    'bundlesData'      => $orderProductBundles,
                    'quantity'		   => $product['quantity'],
                    'barcode'		   => $product['barcode'],
                    'barcode_image'	   => $product['barcode_image'],
                    'sku'		       => $product['sku'],
                    'rentData'		   => $rentData,
                    'pricePerMeterData'=> $pricePerMeterData,
                    'printingDocument' => $printingDocument,
                    'price'    		   => $this->currency->format($product['price'], $order_info['currency_code'], $order_info['currency_value']),
                    'total'    		   => $this->currency->format($product['total'], $order_info['currency_code'], $order_info['currency_value']),
                    'main_price'       => $main_price,
                    'remaining_amount' => $remaining_amount,
                    'href'     		   => $this->url->link('catalog/product/update', 'product_id=' . $product['product_id'], 'SSL'),
                    "pd_front_info"	   => $product['pd_front_info'],
                    "pd_back_info"	   => $product['pd_back_info'],
                    "dropna_status"    => $dropnaProduct_ids[$product['product_id']] ? ( $dropna_product_status ? $dropna_product_status : 'unknowen') : '',
                    'extra_details'		=> $extra_details,
                    'curtain_seller'	=> isset($curtain_seller) ? $curtain_seller : null,
                    'productCodeData'	=> isset($productCodeData) ? $productCodeData : null,
                    'warehouse'         => $product['warehouse'] ? $product['warehouse'] : '',
                    'skuInfo'           => $skuInfo,
                    'added_by_user_type'=> $this->model_sale_order->getOrderProductAddByUserType($product['order_product_id'])
                ];

                if( $product['product_delivery_date'] ){
                    $prData['delivery_date'] = $product['product_delivery_date'];
                }

                //Add product warehouse
                if($this->data['warehouses']){
                    //$prData['warehouse'] = $this->data['wrs_names'] ? $this->data['wrs_names'][$warehouses_products[$product['product_id']]] : '';
                    $prData['warehouse'] = $product['warehouse'] ? $product['warehouse'] : 0;
                }

                if (isset($product['storable']) && $product['storable'] == 1) {
                    $this->data['storable_products'][] = [
                        'name' => $product['product_name'],
                        'image' => HTTP_IMAGE . '/' . ($product['image'] ?: 'no_image.jpg'),
                    ];
                }

				$productsList[] = $prData;

			}
            //Group products by warehouse
            //Comment group products by warehouse - disable feature v.21.05.2020
            /*if($this->data['warehouses']){
                foreach ($productsList as $prd) {
                    $finalProducts[$prd['warehouse']][] = $prd;
                    //exploit this loop to formate combined warehouses coust
                    $warehouseCostValue = $combined_wrs_costs[$prd['warehouse']];
                    $combined_wrs_costs_format[$prd['warehouse']] = $this->currency->format($warehouseCostValue, $order_info['currency_code'], $order_info['currency_value']);
                    ///////////////////////////////////////////////////////
                }
                $this->data['combined_wrs_costs'] = $combined_wrs_costs_format;
            }
            else
            {
                $finalProducts['fakeKey'] = $productsList;
            }*/
            if($this->data['seller_based']){
                $finalProducts=$this->groupProducts($productsList,'seller');
            }
            else{
            $finalProducts['fakeKey'] = $productsList;
            }

            $this->data['products'] = $finalProducts;
            /////////////////////////

            $this->data['pd_has_custom_design'] = $pd_has_custom_design;
            $this->data['http_image'] = rtrim(\Filesystem::getUrl(), '/') . '/';;


			$this->data['vouchers'] = array();

			$vouchers = $this->model_sale_order->getOrderVouchers($this->request->get['order_id']);

			foreach ($vouchers as $voucher) {
				$this->data['vouchers'][] = array(
					'description' => $voucher['description'],
					'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value']),
					'href'        => $this->url->link('sale/voucher/update', 'voucher_id=' . $voucher['voucher_id'], 'SSL')
				);
			}

			$this->data['totals'] = $this->model_sale_order->getOrderTotals($this->request->get['order_id']);

			foreach ($this->data['totals'] as &$total) {
				$total['value'] = number_format($total['value'],2);
				$total['title'] = html_entity_decode($total['title']);
			}

			// die(json_encode($this->data['totals']));
            // foreach ($this->data['totals'] as &$total) {
            //     if ( $total['code'] == 'cffpm' )
            //         $total['code'] = 'custom_fees_for_payment_method';

            //     $this->language->load('total/' . $total['code']);

            //     $total['title'] = $this->language->get('heading_title');
            // }

			$this->data['downloads'] = array();

			foreach ($products as $product) {
				$results = $this->model_sale_order->getOrderDownloads($this->request->get['order_id'], $product['order_product_id']);

				foreach ($results as $result) {
					$this->data['downloads'][] = array(
						'name'      => $result['name'],
						'filename'  => $result['mask'],
						'remaining' => $result['remaining']
					);
				}
            }
            $this->data['order_statuses'] =$this->getUserOrderStatuses($order_info['order_status_id']);
			$this->data['order_status_id'] = $order_info['order_status_id'];

			// Fraud
			$this->load->model('sale/fraud');

			$fraud_info = $this->model_sale_fraud->getFraud($order_info['order_id']);

			if ($fraud_info) {
				$this->data['country_match'] = $fraud_info['country_match'];

				if ($fraud_info['country_code']) {
					$this->data['country_code'] = $fraud_info['country_code'];
				} else {
					$this->data['country_code'] = '';
				}

				$this->data['high_risk_country'] = $fraud_info['high_risk_country'];
				$this->data['distance'] = $fraud_info['distance'];

				if ($fraud_info['ip_region']) {
					$this->data['ip_region'] = $fraud_info['ip_region'];
				} else {
					$this->data['ip_region'] = '';
				}

				if ($fraud_info['ip_city']) {
					$this->data['ip_city'] = $fraud_info['ip_city'];
				} else {
					$this->data['ip_city'] = '';
				}

				$this->data['ip_latitude'] = $fraud_info['ip_latitude'];
				$this->data['ip_longitude'] = $fraud_info['ip_longitude'];

				if ($fraud_info['ip_isp']) {
					$this->data['ip_isp'] = $fraud_info['ip_isp'];
				} else {
					$this->data['ip_isp'] = '';
				}

				if ($fraud_info['ip_org']) {
					$this->data['ip_org'] = $fraud_info['ip_org'];
				} else {
					$this->data['ip_org'] = '';
				}

				$this->data['ip_asnum'] = $fraud_info['ip_asnum'];

				if ($fraud_info['ip_user_type']) {
					$this->data['ip_user_type'] = $fraud_info['ip_user_type'];
				} else {
					$this->data['ip_user_type'] = '';
				}

				if ($fraud_info['ip_country_confidence']) {
					$this->data['ip_country_confidence'] = $fraud_info['ip_country_confidence'];
				} else {
					$this->data['ip_country_confidence'] = '';
				}

				if ($fraud_info['ip_region_confidence']) {
					$this->data['ip_region_confidence'] = $fraud_info['ip_region_confidence'];
				} else {
					$this->data['ip_region_confidence'] = '';
				}

				if ($fraud_info['ip_city_confidence']) {
					$this->data['ip_city_confidence'] = $fraud_info['ip_city_confidence'];
				} else {
					$this->data['ip_city_confidence'] = '';
				}

				if ($fraud_info['ip_postal_confidence']) {
					$this->data['ip_postal_confidence'] = $fraud_info['ip_postal_confidence'];
				} else {
					$this->data['ip_postal_confidence'] = '';
				}

				if ($fraud_info['ip_postal_code']) {
					$this->data['ip_postal_code'] = $fraud_info['ip_postal_code'];
				} else {
					$this->data['ip_postal_code'] = '';
				}

				$this->data['ip_accuracy_radius'] = $fraud_info['ip_accuracy_radius'];

				if ($fraud_info['ip_net_speed_cell']) {
					$this->data['ip_net_speed_cell'] = $fraud_info['ip_net_speed_cell'];
				} else {
					$this->data['ip_net_speed_cell'] = '';
				}

				$this->data['ip_metro_code'] = $fraud_info['ip_metro_code'];
				$this->data['ip_area_code'] = $fraud_info['ip_area_code'];

				if ($fraud_info['ip_time_zone']) {
					$this->data['ip_time_zone'] = $fraud_info['ip_time_zone'];
				} else {
					$this->data['ip_time_zone'] = '';
				}

				if ($fraud_info['ip_region_name']) {
					$this->data['ip_region_name'] = $fraud_info['ip_region_name'];
				} else {
					$this->data['ip_region_name'] = '';
				}

				if ($fraud_info['ip_domain']) {
					$this->data['ip_domain'] = $fraud_info['ip_domain'];
				} else {
					$this->data['ip_domain'] = '';
				}

				if ($fraud_info['ip_country_name']) {
					$this->data['ip_country_name'] = $fraud_info['ip_country_name'];
				} else {
					$this->data['ip_country_name'] = '';
				}

				if ($fraud_info['ip_continent_code']) {
					$this->data['ip_continent_code'] = $fraud_info['ip_continent_code'];
				} else {
					$this->data['ip_continent_code'] = '';
				}

				if ($fraud_info['ip_corporate_proxy']) {
					$this->data['ip_corporate_proxy'] = $fraud_info['ip_corporate_proxy'];
				} else {
					$this->data['ip_corporate_proxy'] = '';
				}

				$this->data['anonymous_proxy'] = $fraud_info['anonymous_proxy'];
				$this->data['proxy_score'] = $fraud_info['proxy_score'];

				if ($fraud_info['is_trans_proxy']) {
					$this->data['is_trans_proxy'] = $fraud_info['is_trans_proxy'];
				} else {
					$this->data['is_trans_proxy'] = '';
				}

				$this->data['free_mail'] = $fraud_info['free_mail'];
				$this->data['carder_email'] = $fraud_info['carder_email'];

				if ($fraud_info['high_risk_username']) {
					$this->data['high_risk_username'] = $fraud_info['high_risk_username'];
				} else {
					$this->data['high_risk_username'] = '';
				}

				if ($fraud_info['high_risk_password']) {
					$this->data['high_risk_password'] = $fraud_info['high_risk_password'];
				} else {
					$this->data['high_risk_password'] = '';
				}

				$this->data['bin_match'] = $fraud_info['bin_match'];

				if ($fraud_info['bin_country']) {
					$this->data['bin_country'] = $fraud_info['bin_country'];
				} else {
					$this->data['bin_country'] = '';
				}

				$this->data['bin_name_match'] = $fraud_info['bin_name_match'];

				if ($fraud_info['bin_name']) {
					$this->data['bin_name'] = $fraud_info['bin_name'];
				} else {
					$this->data['bin_name'] = '';
				}

				$this->data['bin_phone_match'] = $fraud_info['bin_phone_match'];

				if ($fraud_info['bin_phone']) {
					$this->data['bin_phone'] = $fraud_info['bin_phone'];
				} else {
					$this->data['bin_phone'] = '';
				}

				if ($fraud_info['customer_phone_in_billing_location']) {
					$this->data['customer_phone_in_billing_location'] = $fraud_info['customer_phone_in_billing_location'];
				} else {
					$this->data['customer_phone_in_billing_location'] = '';
				}

				$this->data['ship_forward'] = $fraud_info['ship_forward'];

				if ($fraud_info['city_postal_match']) {
					$this->data['city_postal_match'] = $fraud_info['city_postal_match'];
				} else {
					$this->data['city_postal_match'] = '';
				}

				if ($fraud_info['ship_city_postal_match']) {
					$this->data['ship_city_postal_match'] = $fraud_info['ship_city_postal_match'];
				} else {
					$this->data['ship_city_postal_match'] = '';
				}

				$this->data['score'] = $fraud_info['score'];
				$this->data['explanation'] = $fraud_info['explanation'];
				$this->data['risk_score'] = $fraud_info['risk_score'];
				$this->data['queries_remaining'] = $fraud_info['queries_remaining'];
				$this->data['maxmind_id'] = $fraud_info['maxmind_id'];
				$this->data['error'] = $fraud_info['error'];
			} else {
				$this->data['maxmind_id'] = '';
			}

            $voucher_data = array();

            $vouchers = $this->model_sale_order->getOrderVouchers( $this->request->get['order_id'] );

            foreach ($vouchers as $voucher)
            {
                $voucher_data[] = array(
                    'description' => $voucher['description'] ?: $voucher['message'],
                    'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value'])
                );
            }

			$this->data['gift_vouchers'] = $voucher_data;

			if(\Extension::isInstalled('cardless')) {
				$this->load->model('module/cardless');
				$this->data['cardless_purchases'] = $this->model_module_cardless->getOrderCardlessPurchases($order_id);
			}

			/////////////////////////// custom_fields
            $this->load->model('module/quickcheckout_fields');
            $this->data['custom_fields'] = $this->model_module_quickcheckout_fields->getOrderCustomFields($this->request->get['order_id']);
            ///////////////////

			// DHL Express ********************************/

            /* Aliexpress : start */

            $this->data['aliexpress'] = null;

            if (\Extension::isInstalled('aliexpress_dropshipping') && $this->config->get('module_wk_dropship_status')) {
                $this->initializer(['aliexpress/warehouses']);

                if ($aliExpressOrder = $this->warehouses->getAliExpressOrder($this->request->get['order_id'])) {

                    $this->data['aliexpress']['status'] = 1;

                    if ((int) $aliExpressOrder['wl_order_id'] > 0) {
                        $this->data['aliexpress']['status'] = 2;
                    }
                }
            }
            /* Aliexpress : end*/


			$this->data['extra_details_isset'] = $extra_details;
			$this->data['products_with_extra_details'] = $products_with_extra_details;



            $localizationSettings = $this->model_setting_setting->getSetting('localization');

            $suffix = '';
            if ( $this->config->get('config_admin_language') != 'en' )
            {
                $specifiedLang = $languages[$this->config->get('config_admin_language')];
                $suffix = "_{$specifiedLang['code']}";
            }
            $this->data['text_address1'] = ! empty( $localizationSettings['entry_address_1' . $suffix] ) ? $localizationSettings['entry_address_1' . $suffix] : $this->language->get('text_address_1');

            // check if delivery slot app installed
            $delivery_slot = $this->config->get('delivery_slot');
            if(is_array($delivery_slot) && count($delivery_slot) > 0){
                $this->language->load('module/delivery_slot');

                $this->load->model('module/delivery_slot/slots');
                $this->load->model('module/delivery_slot/settings');

                $orderSlot = $this->model_module_delivery_slot_slots->getOrderDeliverySlot($order_id);
                $this->data['delivery_slot'] = true;
                $this->data['order_delivery_slot'] = $orderSlot;
                $slotDate = explode("-",$orderSlot['delivery_date']);
                $newSlotArray[] =  $slotDate[1];
                $newSlotArray[] =  $slotDate[0];
                $newSlotArray[] =  $slotDate[2];
                $this->data['order_delivery_slot_date'] = implode("-",$newSlotArray);

                if($this->model_module_delivery_slot_settings->isCutOff()){
                    $ds_settings = $this->config->get('delivery_slot');

                    $time_zone = $this->config->get('config_timezone')?:'UTC';

                    $dateTime = new DateTime('now', new DateTimeZone($time_zone));
                    $current_time =  $dateTime->format("h:i A");
                    if($current_time > $ds_settings['slot_time_start'] && $current_time < $ds_settings['slot_time_end']){
                        $this->data['slots_day_index'] = $ds_settings['slot_day_index'];
                    }else{
                        $this->data['slots_day_index'] = $ds_settings['slot_other_time'];
                    }

                }

                $this->data['slots_max_day'] = isset($ds_settings['slot_max_day']) ? $ds_settings['slot_max_day'] : 0;
            }

            $this->data['text_address2'] = ! empty( $localizationSettings['entry_address_2' . $suffix] ) ? $localizationSettings['entry_address_2' . $suffix] : $this->language->get('text_address_2');
            $this->data['text_city'] = ! empty( $localizationSettings['entry_city' . $suffix] ) ? $localizationSettings['entry_city' . $suffix] : $this->language->get('text_city');
            $this->data['text_zone'] = ! empty( $localizationSettings['entry_checkout_zone' . $suffix] ) ? $localizationSettings['entry_checkout_zone' . $suffix] : $this->language->get('text_zone');
            $this->data['text_area'] = ! empty( $localizationSettings['entry_checkout_area' . $suffix] ) ? $localizationSettings['entry_checkout_area' . $suffix] : $this->language->get('text_area');
            $this->data['text_postcode'] = ! empty( $localizationSettings['entry_postcode' . $suffix] ) ? $localizationSettings['entry_postcode' . $suffix] : $this->language->get('text_postcode');
            $this->data['text_country'] = ! empty( $localizationSettings['entry_country' . $suffix] ) ? $localizationSettings['entry_country' . $suffix] : $this->language->get('text_country');
            $this->data['text_company'] = ! empty( $localizationSettings['entry_company' . $suffix] ) ? $localizationSettings['entry_company' . $suffix] : $this->language->get('text_company');
            $this->data['text_company_id'] = ! empty( $localizationSettings['entry_company_id' . $suffix] ) ? $localizationSettings['entry_company_id' . $suffix] : $this->language->get('text_company_id');
            $this->data['text_tax_id'] = ! empty( $localizationSettings['entry_tax_id' . $suffix] ) ? $localizationSettings['entry_tax_id' . $suffix] : $this->language->get('text_tax_id');
            $this->data['text_telephone'] = ! empty( $localizationSettings['entry_telephone' . $suffix] ) ? $localizationSettings['entry_telephone' . $suffix] : $this->language->get('text_payment_telephone');
			$this->data['text_fax'] = ! empty( $localizationSettings['entry_fax' . $suffix] ) ? $localizationSettings['entry_fax' . $suffix] : $this->language->get('text_fax');
			$this->data['text_shipping_name'] = $this->language->get('text_customer');

			$this->data['hot_edit_link'] = $this->url->link('sale/order/hotEdit', '', 'SSL');
			$this->data['order_id'] = $this->request->request['order_id'];

            $this->load->model('extension/shipping');
            $bundledShippingMethods = $this->model_extension_shipping->getEnabled();

            $manualShippingInfo = $this->model_sale_order->getManualShippingGatewayId($order_info['order_id']);

            $this->data['manualShipping'] = null;

            $shippingMethods = [];
            foreach ($bundledShippingMethods as $bundledShippingMethod) {

                if (
                    $manualShippingInfo['is_manual'] == 0 &&
                    $manualShippingInfo['id'] == $bundledShippingMethod['id']
                ) {
                    $this->data['manualShipping']['gateway'] = $bundledShippingMethod;
                }

                $shippingMethods[] = [
                    'id' => $bundledShippingMethod['id'],
                    'title' => $bundledShippingMethod['title'],
                    'bundled' => $bundledShippingMethod['bundled'],
                    'code' => $bundledShippingMethod['code'],
                ];
            }

            if (\Extension::isInstalled('manual_shipping')) {
                $manualShipping = $this->config->get('manual_shipping');

                if (isset($manualShipping['status']) && $manualShipping['status'] == 1) {

                    $this->language->load('module/manual_shipping');

                    $this->initializer([
                        'msgGateways' => 'module/manual_shipping/gateways',
                        'msgOrder' => 'module/manual_shipping/order',
                    ]);

                    $manualShippingGateways = $this->msgGateways->getCompactShippingGateways([
                        'start' => -1,
                        'status' => 1,
                        'language_id' => $this->config->get('config_language_id'),
                    ]);

                    $this->data['manualShipping']['gateways'] = $manualShippingGateways['data'];

                    $msgId = $this->msgOrder->getManualShippingGatewayId($order_info['order_id']);

                    $manualShippingGateways = array_column($manualShippingGateways['data'], null, 'id');
                    if (isset($manualShippingGateways[$msgId])) {
                        $manualShippingGateway = $manualShippingGateways[$msgId];
                        $this->data['manualShipping']['gateway'] = $manualShippingGateway;
                    }

                    foreach ($this->data['manualShipping']['gateways'] as $msgGateway) {
                        $shippingMethods[] = [
                            'id' => $msgGateway['id'],
                            'title' => $msgGateway['title'],
                            'bundled' => 0,
                            'code' => 'manual',
                        ];
                    }
                }
            }

            $shippingCode = explode('.', $order_info['shipping_code']);
            $this->data['shipping_code'] = $shippingCode[0];
            $this->data['full_shipping_code'] = $order_info['shipping_code'];

            $this->data['shipping_methods'] = $shippingMethods;


            //For Order Assignee App
		    $this->load->model("module/order_assignee");
		    $this->data['isOrderAssigneeAppInstalled']=$this->model_module_order_assignee->isOrderAssigneeAppInstalled();
            if($this->data['isOrderAssigneeAppInstalled'])
            {
            $this->data['admins_list'] = $this->model_user_user->getUsers();
            $this->data['user_id'] = $this->user->getID();
            $this->data['order_assignee_id'] = $this->model_sale_order->getOrderAssignee($order_id);
            if($this->user->hasPermission('custom', 'assign_order'))
                 $this->data['isAllowedToAssignOrder']=true;
            else $this->data['isAllowedToAssignOrder']=false;
            }

            $this->initializer([
                'paymentTransaction' => 'extension/payment_transaction',
            ]);

            $this->data['payment_transaction'] = null;
            if ($paymentTransaction = $this->paymentTransaction->selectByOrderId($order_id)) {
                $this->data['payment_transaction'] = $paymentTransaction;
            }

            //Session messages
            if (isset($this->session->data['error'])) {
                $this->data['error_warning'] = $this->session->data['error'];

                unset($this->session->data['error']);
            }

            $this->template = 'sale/order_info.expand';
			$this->children = array(
				'common/header',
				'common/footer'
			);

			$this->response->setOutput($this->render_ecwig());
		} else {
			$this->language->load('error/not_found');

			$this->document->setTitle($this->language->get('heading_title'));

			$this->data['heading_title'] = $this->language->get('heading_title');

			$this->data['text_not_found'] = $this->language->get('text_not_found');

			$this->data['breadcrumbs'] = array();

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', '', 'SSL'),
				'separator' => false
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('error/not_found', '', 'SSL'),
				'separator' => ' :: '
			);

			$this->template = 'error/not_found.expand';
            $this->base = "common/base";


			$this->response->setOutput($this->render_ecwig());
		}
	}

	public function createInvoiceNo() {
		$this->language->load('sale/order');

		$json = array();

     	if (!$this->user->hasPermission('modify', 'sale/order')) {
      		$json['error'] = $this->language->get('error_permission');
		} elseif (isset($this->request->get['order_id'])) {
			$this->load->model('sale/order');

			$invoice_no = $this->model_sale_order->createInvoiceNo($this->request->get['order_id']);

			if ($invoice_no) {
				$json['invoice_no'] = $invoice_no;
			} else {
				$json['error'] = $this->language->get('error_action');
			}
		}

		$this->response->setOutput(json_encode($json));
  	}

	public function addCredit() {
		$this->language->load('sale/order');

		$json = array();

     	if (!$this->user->hasPermission('modify', 'sale/order')) {
      		$json['error'] = $this->language->get('error_permission');
    	} elseif (isset($this->request->get['order_id'])) {
			$this->load->model('sale/order');

			$order_info = $this->model_sale_order->getOrder($this->request->get['order_id']);

			if ($order_info && $order_info['customer_id']) {
				$this->load->model('sale/customer');

				$credit_total = $this->model_sale_customer->getTotalTransactionsByOrderId($this->request->get['order_id']);

				if (!$credit_total) {
					$this->model_sale_customer->addTransaction($order_info['customer_id'], $this->language->get('text_order_id') . ' #' . $this->request->get['order_id'], $order_info['total'], $this->request->get['order_id']);

					$json['success'] = $this->language->get('text_credit_added');
				} else {
					$json['error'] = $this->language->get('error_action');
				}
			}
		}

		$this->response->setOutput(json_encode($json));
  	}

	public function removeCredit() {
		$this->language->load('sale/order');

		$json = array();

     	if (!$this->user->hasPermission('modify', 'sale/order')) {
      		$json['error'] = $this->language->get('error_permission');
    	} elseif (isset($this->request->get['order_id'])) {
			$this->load->model('sale/order');

			$order_info = $this->model_sale_order->getOrder($this->request->get['order_id']);

			if ($order_info && $order_info['customer_id']) {
				$this->load->model('sale/customer');

				$this->model_sale_customer->deleteTransaction($this->request->get['order_id']);

				$json['success'] = $this->language->get('text_credit_removed');
			} else {
				$json['error'] = $this->language->get('error_action');
			}
		}

		$this->response->setOutput(json_encode($json));
  	}

	public function addReward() {
		$this->language->load('sale/order');

		$json = array();

     	if (!$this->user->hasPermission('modify', 'sale/order')) {
      		$json['error'] = $this->language->get('error_permission');
    	} elseif (isset($this->request->get['order_id'])) {
			$this->load->model('sale/order');

			$order_info = $this->model_sale_order->getOrder($this->request->get['order_id']);

			if ($order_info && $order_info['customer_id']) {
				$this->load->model('sale/customer');

				$reward_total = $this->model_sale_customer->getTotalCustomerRewardsByOrderId($this->request->get['order_id']);

				if (!$reward_total) {
					$this->model_sale_customer->addReward($order_info['customer_id'], $this->language->get('text_order_id') . ' #' . $this->request->get['order_id'], $order_info['reward'], $this->request->get['order_id']);

					$json['success'] = $this->language->get('text_reward_added');
				} else {
					$json['error'] = $this->language->get('error_action');
				}
			} else {
				$json['error'] = $this->language->get('error_action');
			}
		}

		$this->response->setOutput(json_encode($json));
  	}

	public function removeReward() {
		$this->language->load('sale/order');

		$json = array();

     	if (!$this->user->hasPermission('modify', 'sale/order')) {
      		$json['error'] = $this->language->get('error_permission');
    	} elseif (isset($this->request->get['order_id'])) {
			$this->load->model('sale/order');

			$order_info = $this->model_sale_order->getOrder($this->request->get['order_id']);

			if ($order_info && $order_info['customer_id']) {
				$this->load->model('sale/customer');

				$this->model_sale_customer->deleteReward($this->request->get['order_id']);

				$json['success'] = $this->language->get('text_reward_removed');
			} else {
				$json['error'] = $this->language->get('error_action');
			}
		}

		$this->response->setOutput(json_encode($json));
  	}

	public function addCommission() {
		$this->language->load('sale/order');

		$json = array();

     	if (!$this->user->hasPermission('modify', 'sale/order')) {
      		$json['error'] = $this->language->get('error_permission');
    	} elseif (isset($this->request->get['order_id'])) {
			$this->load->model('sale/order');

			$order_info = $this->model_sale_order->getOrder($this->request->get['order_id']);

			if ($order_info && $order_info['affiliate_id']) {
				$this->load->model('sale/affiliate');

				$affiliate_total = $this->model_sale_affiliate->getTotalTransactionsByOrderId($this->request->get['order_id']);

				if (!$affiliate_total) {
					$this->model_sale_affiliate->addTransaction($order_info['affiliate_id'], $this->language->get('text_order_id') . ' #' . $this->request->get['order_id'], $order_info['commission'], $this->request->get['order_id']);

					$json['success'] = "1";
					$json['success_msg'] = $this->language->get('text_commission_added');
				} else {

                    $json['success'] = "0";
					$json['errors'] = $this->language->get('error_action');
				}
			} else {
                $json['success'] = "0";
				$json['error'] = $this->language->get('error_action');
			}
		}

		$this->response->setOutput(json_encode($json));
  	}

	public function removeCommission() {
		$this->language->load('sale/order');

		$json = array();

     	if (!$this->user->hasPermission('modify', 'sale/order')) {
      		$json['error'] = $this->language->get('error_permission');
    	} elseif (isset($this->request->get['order_id'])) {
			$this->load->model('sale/order');

			$order_info = $this->model_sale_order->getOrder($this->request->get['order_id']);

			if ($order_info && $order_info['affiliate_id']) {
				$this->load->model('sale/affiliate');

				$this->model_sale_affiliate->deleteTransaction($this->request->get['order_id']);

                $json['success'] = "1";
                $json['success_msg'] = $this->language->get('text_commission_removed');
			} else {
                $json['success'] = "0";
                $json['errors'] = $this->language->get('error_action');
			}
		}

		$this->response->setOutput(json_encode($json));
  	}

	public function shipping_tracking() {
		$this->language->load('sale/order');

		$json = array();
    	$order_id = $this->request->post['order_id'];
     	if (!$this->user->hasPermission('modify', 'sale/order')) {
      		$json['error'] = $this->language->get('error_permission');
    	} elseif (isset($order_id)) {
			$this->load->model('sale/order');

			$order_info = $this->model_sale_order->getOrder($order_id);

            $url = $this->request->post['url'];
			if ($order_info && $url) {
				$this->load->model('sale/order');
				$this->model_sale_order->updateShippingTrackingURL($order_id, $url);

				$json['success'] = "1";
				$json['success_msg'] = $this->language->get('text_shipping_tracking_added');

			} else {
                $json['success'] = "0";
				$json['error'] = $this->language->get('error_action');
			}
		}

		$this->response->setOutput(json_encode($json));
	}

	private function checkKnawatOrder($order_data,$new_order){
		// Knawat Drop shippment api
		// Create order to knawat

		$app_dir = str_replace( 'system/', 'expandish/', DIR_SYSTEM );

		require_once $app_dir."controller/module/knawat_dropshipping.php";

		$this->controller_module_knawat_dropshipping = new ControllerModuleKnawatDropshipping( $this->registry );
		$this->controller_module_knawat_dropshipping->order_changed($order_data,true,$new_order);

	}

    public function addHistory()
    {
        $this->language->load('sale/order');

        $this->load->model('sale/order');
        $this->load->model('localisation/order_status');

        if (!isset($this->request->post['order_id'])) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_REQUEST',
                'errors' => ['1']
            ]));
            return;
        }

        if (!isset($this->request->post['order_history'])) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_REQUEST',
                'errors' => ['2']
            ]));
            return;
        }

        $historyData = $this->request->post['order_history'];

        if (!isset($historyData['order_status_id'])) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_REQUEST',
                'errors' => ['3']
            ]));
            return;
        }

        $orderIds = $this->request->post['order_id'];
        $orderStatusId = $historyData['order_status_id'];

        $orderStatus = $this->model_localisation_order_status->getOrderStatus($orderStatusId);

        if (isset($orderStatus['order_status_id']) == false) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNAUTHORIZED_ACTION',
                'errors' => [$this->language->get('error_permission')]
            ]));
            return;
        }

        if (is_array($orderIds) == false && (int)$orderIds < 1) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_REQUEST',
                'errors' => ['']
            ]));
            return;
        }

        if (is_array($orderIds) == false) {
            $orderIds = [$orderIds];
        }

        if (!$this->user->hasPermission('modify', 'sale/order')) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNAUTHORIZED_ACTION',
                'errors' => [$this->language->get('error_permission')]
            ]));
            return;
        }

        // microsoft dynamics update order status
        $this->load->model('module/microsoft_dynamics');

        foreach ($orderIds as $orderId) {
            if (\Extension::isInstalled('reward_points_pro')) {
                $this->load->model('promotions/reward_points_transactions');
                $this->model_promotions_reward_points_transactions->beforeUpdateOrder(
                    $orderId,
                    $historyData
                );
            }

            //Kanawat Shipping
            $kanawatSettings = $this->config->get('knawat_dropshipping_status');
            if (isset($kanawatSettings) && (int)$kanawatSettings['status'] == 1) {
                $sending_knawat_order_status = $this->config->get('module_knawat_dropshipping_sending_order_status');
                if ($orderStatusId == $sending_knawat_order_status) {
                    $data = array($orderId,$orderStatusId);
                    $this->checkKnawatOrder($data,false);
                }
            }

            //Salasa shipping
            $salasa_settings = $this->config->get('salasa');
            if(
                $orderStatusId == $salasa_settings['status_code'] &&
                $salasa_settings['status'] == 1 &&
                $salasa_settings['is_shipping'] != 1
            ) {

                $this->initializer([
                    'catalog/product',
                    'shipping/salasa/shipment',
                    'localisation/country',
                ]);

                $orderInfo = $this->model_sale_order->getOrder($orderId);
                $orderProducts = $this->model_sale_order->getOrderProducts($orderId);
                $totalShipment = 0;

                foreach ($orderProducts as &$orderProduct) {
                    $product_info = $this->product->getProduct($orderProduct['product_id']);
                    $orderProduct['sku'] = $product_info['sku'] ?: 'SKU-' . $product_info['product_id'];
                    $this->shipment->addProduct($orderProduct);
                    $totalShipment += $orderProduct['total'];
                }

                $this->shipment->setCredentials($salasa_settings);

                $lang_id = 1;
                $this->load->model('localisation/language');
                $languages = $this->model_localisation_language->getLanguages();
                if($languages['en']['language_id'])
                    $lang_id = $languages['en']['language_id'];

                $orderInfo['payment_country']  = $this->country->getCountryByLanguageId(
                    $orderInfo['payment_country_id'], $lang_id
                );
                $orderInfo['shipping_country'] = $this->country->getCountryByLanguageId(
                    $orderInfo['shipping_country_id'],
                    $lang_id
                );

                $this->shipment->setOrderInfo($orderInfo);
                $this->shipment->setTotalShipment($totalShipment);
                if(!$this->shipment->create()){
                    $response['salasa_errors']  = $this->shipment->getErrors();
                }else{
                    $response['salasa_success'] = 1;
                }
            }

            // microsoft-dynamics update order status
            if($this->model_module_microsoft_dynamics->isActive()) {
                $this->model_module_microsoft_dynamics->updateOrderStatus($orderId, $orderStatusId);
            }

                $this->model_sale_order->addOrderHistory($orderId, $historyData);


        }

        $this->response->setOutput(json_encode([
            'status' => 'OK'
        ]));
        return;
    }

    private function updateTracking($order_id, $orderStatus, $shipping_method, $trackId) {

        $this->load->model("payment/paypal");

         $expandClientId = PAYPAL_MERCHANT_CLIENTID;//'Acqck9HX5JN36kU-O63Opr1mrtcZib6i83Q3-GRM5U4a7_YY5AqUr8oftBSgExDcAfMiEuIF4VegrLW7';
            $expandSecret =   PAYPAL_MERCHANT_SECRET; // 'ECOpqQK1udkc6_TB4pksIq5JkZFCPyk3BWYdMY3-_msgtu5yrKGrbQOqavW-VuGBy-fndFpEuXXhkuo5',
            $expandPartnerCode = PAYPAL_MERCHANT_PARTNERCODE; //'ExpandCart_Cart_MEA',
            $baseUrl =  PAYPAL_MERCHANT_BASEURL; //'https://api.sandbox.paypal.com',
            $expandMerchantId =  PAYPAL_MERCHANT_MERCHANTID;//'6SMXGVHE3FCF4';

        if($this->model_payment_paypal->checkIfTrackingExist($order_id)) {

            $result = $this->model_payment_paypal->getTrackingAndTransactionId($order_id);

            $token = $this->model_payment_paypal->createTokent()['access_token'];

            $trackingStatuses = $this->getTrackingStatuses();

            $status = "ON_HOLD";



            if (in_array($orderStatus, array_keys($trackingStatuses))) {

                $status =  $this->model_payment->paypal->getPaypalSelectedStatus($orderStatus); //strtoupper($trackingStatuses[$orderStatus]);

                $curl = curl_init();

//        $token = $this->createToken()['access_token'];

                $bytes = random_bytes(20);

                $data["status"] = $status;

                $header = base64_encode(json_encode(['alg' => 'none']));
                $body = base64_encode(json_encode(['iss' => $expandClientId, 'email' => $this->config->get("paypal_email")]));
                $authHeader = $header . "." . $body . ".";

                curl_setopt_array($curl, array(
                    CURLOPT_URL => $baseUrl . "/v1/shipping/trackers/" . $result["transaction_id"] . "-" . $result["tracking_id"],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "PUT",
                    CURLOPT_POSTFIELDS => json_encode($data),
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                        "Authorization: Bearer $token",
                        "PayPal-Partner-Attribution-Id: ExpandCart_Cart_MEA",
                        "PayPal-Auth-Assertion: $authHeader"

                    ),
                ));

                $resposeJson = curl_exec($curl);
                curl_close($curl);

             }


        } else {

            $this->model_payment_paypal->createTracking($order_id, $orderStatus, $shipping_method, $trackId);
        }

    }

    public function getTrackingStatuses() {

        $query = $this->db->query("SELECT order_status_id, name FROM order_status where language_id = 1 AND order_status_id in (select value from setting where `key` in ('paypal_order_status_shipping', 'paypal_order_status_pending', 'paypal_order_status_failed', 'paypal_order_status_id'))");

        $statuses = $query->rows;

        $result = [];

        foreach ($statuses as $stat) {

            $result[$stat["order_status_id"]] = $stat["name"];

        }

        return $result;
    }

    public function history($status_id = null, $order_id = null, $from_ebutler_order = false) {

    	$this->language->load('sale/order');

		$this->data['error'] = '';
		$this->data['success'] = '';

		$this->load->model('sale/order');

		if ($this->request->server['REQUEST_METHOD'] == 'POST' || $from_ebutler_order)
        {
            $order_id = $order_id ? $order_id : $this->request->get['order_id'];
            $order_status = $status_id ? $status_id : $this->request->post['order_status_id'];

            if ($from_ebutler_order) {
                if (!$order_id && !$status_id) {
                    return false;
                }
            }

            if (!$order_id) {
                return false;
            }

			if ( !$from_ebutler_order && !$this->user->hasPermission('modify', 'sale/order') )
            {
                if (!$this->user->hasPermission('modify', 'sale/order')) {
                    $result_json['success'] = '0';
				    $result_json['error'] = $this->language->get('error_permission');
                }
			}
			else
            {

                if ($from_ebutler_order) {
                    $this->request->get['order_id'] = $order_id;
                    $this->request->post['order_status_id'] = $status_id;
                    $this->request->post['date_added'] = date("Y-m-d H:i:s");
                    // 2021-02-24 09:34:19
                }

                $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

                if ( $queryRewardPointInstalled->num_rows )
                {
                    $this->load->model('promotions/reward_points_transactions');
                    $this->model_promotions_reward_points_transactions->beforeUpdateOrder($order_id, $this->request->post);
                }

                $this->load->model('setting/setting');
                // knawat_dropshipping V1
				if($this->model_setting_setting->getSetting('knawat_dropshipping_status')['status']){
                	// 1 is pending, 2 is processing, 7 is cancel and that knawat app is accepting those only
					$sending_knawat_order_status = $this->config->get('module_knawat_dropshipping_sending_order_status');
					if($order_status == $sending_knawat_order_status){
						$data=array($order_id,$order_status);
						$this->checkKnawatOrder($data,false);
					}
                }

                if (\Extension::isinstalled('ebutler') && $this->config->get('ebutler_sending_order_status') == $order_status && !$from_ebutler_order) {
                    $this->load->model('sale/order');

                    $orderInfo = $this->model_sale_order->getOrder($order_id);
                    $orderProducts = $this->model_sale_order->getOrderProducts($order_id);

                    $app_dir = str_replace( 'system/', 'admin/', DIR_SYSTEM );
                    require_once $app_dir."controller/module/ebutler.php";

                    $ebutler_controller = new ControllerModuleEButler( $this->registry );
                    $response = $ebutler_controller->syncEbutlerOrder($orderInfo, $orderProducts);
                    $ebutler_controller->updateEbutlerOrderId($response->data->_id, $order_id);
                }

                // Salasa
                $salasa_settings = $this->config->get('salasa');
                if($order_status == $salasa_settings['status_code'] && $salasa_settings['status'] == 1 && $salasa_settings['is_shipping'] != 1){

                    $this->initializer([
                        'catalog/product',
                        'shipping/salasa/shipment',
                        'localisation/country',
                    ]);

                    $orderInfo = $this->model_sale_order->getOrder($order_id);
                    $orderProducts = $this->model_sale_order->getOrderProducts($order_id);
                    $totalShipment = 0;

                    foreach ($orderProducts as &$orderProduct) {
                        $product_info = $this->product->getProduct($orderProduct['product_id']);
                        $orderProduct['sku'] = $product_info['sku'] ?: 'SKU-' . $product_info['product_id'];
                        $this->shipment->addProduct($orderProduct);
                        $totalShipment += $orderProduct['total'];
                    }

                    $this->shipment->setCredentials($salasa_settings);

                    $lang_id = 1;
                    $this->load->model('localisation/language');
                    $languages = $this->model_localisation_language->getLanguages();
                    if($languages['en']['language_id'])
                        $lang_id = $languages['en']['language_id'];

                    $orderInfo['payment_country']  = $this->country->getCountryByLanguageId($orderInfo['payment_country_id'], $lang_id);
                    $orderInfo['shipping_country'] = $this->country->getCountryByLanguageId($orderInfo['shipping_country_id'], $lang_id);

                    $this->shipment->setOrderInfo($orderInfo);
                    $this->shipment->setTotalShipment($totalShipment);
                    if(!$this->shipment->create()){
                        $result_json['salasa_errors']  = $this->shipment->getErrors();
                    }else{
                        $result_json['salasa_success'] = 1;
                    }
                }
                /// End Salasa

				// Catalyst
				$orderInfo = $this->model_sale_order->getOrder($order_id);

				if ($orderInfo['shipping_code'] === 'catalyst.catalyst' && $this->config->get('catalyst_status') == 1) {
					$status = $this->request->post['order_status_id'];
					$this->load->model('shipping/catalyst');
					$shipmentDetails = $this->model_shipping_catalyst->getShipmentDetails($order_id);
					$catalystStatusId = 0;

					if (!empty($shipmentDetails)) {
                        require_once(DIR_APPLICATION . 'controller/sale/catalyst_create_shipment.php');
                        $catalyst = 'ControllerSaleCatalystCreateShipment';
                        $catalystObj = new $catalyst($this->registry);

						// catalyst statuses mapping
						for ($i = 0; $i < 7; $i++) {
							$catalystStatus = $this->config->get('catalyst_status_' . $i);
							if ($catalystStatus == $status) {
								$catalystStatusId = $i;
								break;
							}
						}
						$catalystId = $shipmentDetails['catalyst_id'];

                        $catalystObj->updateOrderStatus($order_id, $catalystStatusId, $catalystId);

					///////// Auto Create Catalyst Shipping - catalyst_status_6 ready status
					}else if($status == $this->config->get('catalyst_status_6') && $this->config->get('catalyst_autoshipping') == 1){

                        $order_address = [
                            $orderInfo['shipping_address_1'] ?? $orderInfo['payment_address_1'],
                            $orderInfo['shipping_address_2'] ?? $orderInfo['payment_address_2'],
                            $orderInfo['shipping_zone'] ?? $orderInfo['payment_zone'],
                            $orderInfo['shipping_area'] ?? $orderInfo['payment_area'],
                            $orderInfo['shipping_city'] ?? $orderInfo['payment_city'],
                            $orderInfo['shipping_country'] ?? $orderInfo['payment_country']
                        ];
                        $catalys_address = implode(', ' , array_filter($order_address));

					    $catalyst_data = [
                            'order_id' => $orderInfo['order_id'],
                            'catalyst_customer_language' => $orderInfo['language_code'],
                            'coordinates' => $orderInfo['shipping_address_location'] ?? '',
                            'catalyst_customer_name' => $orderInfo['firstname'] .' '. $orderInfo['lastname'],
                            'catalyst_customer_phone' => $orderInfo['telephone'],
                            'catalyst_payment_method' => $orderInfo['payment_code'] == 'cod' ? 0 : 1,
                            'catalyst_total_price' => $orderInfo['total'],
                            'catalyst_notes' => $orderInfo['comment'] ?? '',
                            'catalyst_address' => [
                                                    'name' => '',
                                                    'address' => $catalys_address,
                                                    'notes'   => '',
                                                  ],
                            'auto' => true
                        ];
                        $result_json['catalyst_data'] = $catalyst_data;
                    }
				}

                //Warehouses Shipping
                $warehouses = $this->config->get('warehouses');
                if (isset($warehouses) && (int)$warehouses['status'] == 1) {
                    $warehouses_order_status = $warehouses['subtract_status'];
                    if ($order_status == $warehouses_order_status) {
                        $subtractQtys = [];
                        $orderProducts = $this->model_sale_order->getOrderProducts($order_id);
                        foreach ($orderProducts as $orderProduct) {
                            $subtractQtys[$orderProduct['product_id']] = $orderProduct['quantity'];
                        }

                        if(count($subtractQtys)){
                            $this->load->model('module/warehouses');
                            $this->model_module_warehouses->subtractQuantities($subtractQtys, $order_id);
                        }
                    }
                }

                //delivery slots
                $deliverySlot_settings = $this->config->get('delivery_slot');
                $deliverySlotCheck = (\Extension::isInstalled('delivery_slot') && $deliverySlot_settings['status'] == 1) ? 1 : 0;
                if($deliverySlotCheck && $deliverySlot_settings['cancel_status_id'] == $order_status){
                    $this->model_sale_order->deleteSlot($order_id);
                }

                // microsoft dynamics update order status
                $this->load->model('module/microsoft_dynamics');
                if($this->model_module_microsoft_dynamics->isActive()) {
                    $this->model_module_microsoft_dynamics->updateOrderStatus($order_id, $order_status);
                }

                // Tabby map order statuses to tabby order statuses
                if (\Extension::isInstalled('tabby_pay_later') && in_array($orderInfo['payment_code'], ['tabby_installments', 'tabby_pay_later'])) {
                    $this->load->model('payment/tabby');
                    $this->model_payment_tabby->updateOrderStatus($order_id, $order_status);
                }

                ////////////////
                // add data to log_history updateOrderStatus action
                $this->load->model('setting/audit_trail');
                $this->load->model('loghistory/histories');
                $pageStatus = $this->model_setting_audit_trail->getPageStatus("order");
                $old_value = $this->getAllOrderInfo($order_id);
                $this->model_sale_order->addOrderHistory($order_id, $this->request->post);
                if($pageStatus){
                    $log_history['action'] = 'updateOrderStatus';
                    $log_history['reference_id'] = $order_id;
                    $log_history['old_value'] = json_encode($old_value,JSON_UNESCAPED_UNICODE);
                    $log_history['new_value'] = json_encode($this->getAllOrderInfo($order_id),JSON_UNESCAPED_UNICODE);
                    $log_history['type'] = 'order';
                    $this->model_loghistory_histories->addHistory($log_history);
                    }

                if (
                    !$this->_isHardActivationCompleted()
                    && $order_status != 1
                    && !$this->userActivation->isTestOrder((int)$this->request->get['order_id'])
                )$this->_setHardActivation();


                $result_json['success'] = '1';
				$result_json['success_msg'] = $this->language->get('text_success');
			}
            // knawat_dropshipping V2
            $this->load->model('module/knawat');
            if ($this->model_module_knawat->isInstalled()) {
                if ($this->model_module_knawat->checkOrderStatusForInsertToKnawat($order_id) && $this->model_module_knawat->checkIfKnawatOrder($order_id))
                    $this->model_module_knawat->insertOrderIntoKnawat($order_id);
            }

            // Qoyod Invoice
            $qoyod_invoice_status = array_key_exists("qoyod_invoice", $orderInfo) ? $orderInfo['qoyod_invoice'] : 0;
            $this->model_sale_order->qoyod_create_invoice($qoyod_invoice_status, $order_id, $order_status);

            if(\Extension::isInstalled('messenger_chatbot') && $orderInfo['psid']){
                try{
                    $this->load->model('module/messenger_chatbot/audience');
                    $psid = $orderInfo['psid'];
                    $audience = $this->model_module_messenger_chatbot_audience->getAudience($psid);

                    $locales = [
                        'en_US' => 'en',
                        'en_UK' => 'en',
                        'ar_AR' => 'ar'
                    ];

                    $fb = new Facebook([
                        'app_id' => '329928231042768',
                        'app_secret' => '89b8ba250426527ac48bafeead4bb19c',
                    ]);

                    try {
                        $localeResponse = $fb->get($user_id . '?fields=locale', $page['access_token'])->getDecodedBody();
                        $locale = isset($locales[$localeResponse['locale']]) ? $locales[$localeResponse['locale']] : 'en';
                    } catch (Exception $e) {
                        $locale = 'en';
                    }

                    $this->load->model('localisation/language');
                    $this->load->model('localisation/order_status');
                    $lang_id = $this->model_localisation_language->getLanguageByCode($locale)['language_id'];
                    $order_status_text = $this->model_localisation_order_status->getOrderStatusByLang($order_status, $lang_id)['name'];

                    $reply = [
                        'recipient' => [
                            'id' => $psid
                        ],
                        'message' => [
                            "text" => "Your order number " . $orderInfo['order_id'] . " is " . $order_status_text
                        ],
                        "messaging_type" => "MESSAGE_TAG",
                        "tag" => "POST_PURCHASE_UPDATE"
                    ];

                    $url = "https://graph.facebook.com/v11.0/" . $audience['page_id'] . "/messages?access_token=" . $audience['access_token'];
                    $headers = array("Content-type: application/json");
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode((object)$reply, JSON_FORCE_OBJECT));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $result = curl_exec($ch);
                    // $this->db->query("delete from messenger_chatbot_replies where page_id in ('11111', '22222')");
                    // $this->db->query("insert into messenger_chatbot_replies set attributes = '". $result . "', page_id = '11111', name = 'test', type = 'test', applied_on = 'test'");
                    // $this->db->query("insert into messenger_chatbot_replies set attributes = '". json_encode($reply) . "', page_id = '22222', name = 'test', type = 'test', applied_on = 'test'");
                }

                catch(Exception $e){
                }
            }


            if ($from_ebutler_order) {

                echo json_encode($result_json); exit();
            }

			//update tracking if order paid with paypal

            if(strtolower($orderInfo["payment_method"]) == "paypal" && !empty($orderInfo["shipping_trackId"])) {

                $this->updateTracking($order_id, $order_status, $orderInfo["shipping_method"], $orderInfo["shipping_trackId"]);
            }


            //$this->load->model('sale/order');

            if($this->plan_id != 3) {
                $this->data['order_histories'] = $this->model_sale_order->getOrderHistories($order_id, 0, 6);
            }
            $this->data['date_added'] = $this->request->post['date_added'];
            $this->template = 'sale/order_histories_snippet.expand';

            $result_json['histories'] = $this->render_ecwig();

            return $this->response->setOutput(json_encode($result_json));
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$this->data['histories'] = array();
        if($this->plan_id != 3) {
            $results = $this->model_sale_order->getOrderHistories($order_id, ($page - 1) * 10, 10);

            foreach ($results as $result) {
                $this->data['histories'][] = array(
                    'notify'     => $result['notify'] ? $this->language->get('text_yes') : $this->language->get('text_no'),
                    'status'     => $result['status'],
                    'status_color'     => $result['status_color'],
                    'comment'    => nl2br($result['comment']),
                    'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
                );
            }
        }

		$history_total = $this->model_sale_order->getTotalOrderHistories($order_id);

		$pagination = new Pagination();
		$pagination->total = $history_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('sale/order/history', 'order_id=' . $order_id . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->template = 'sale/order_history.tpl';

		$this->response->setOutput($this->render());
  	}

	public function download()
	{
		$this->load->model('sale/order');
		$order_id = "";
		if (isset($this->request->get['order_option_id']))
		{
			$order_option_id = $this->request->get['order_option_id'];
			$order_id = $this->request->get['order_id'];
		}
		else
		{
			$order_option_id = 0;
		}

		$option_info = $this->model_sale_order->getOrderOption($order_id, $order_option_id);

		if ($option_info && $option_info['type'] == 'file')
		{
            // $file = DIR_DOWNLOAD . $option_info['value'];
			$fileName = $option_info['value'];
            // dd($fileName);
            // $file = new File($fileName, ['adapter' => 'gcs', 'base' => STORECODE . '/downloads']);
            \Filesystem::setPath('downloads/' . $fileName);
			$mask = basename(utf8_substr($option_info['value'], 0, utf8_strrpos($option_info['value'], '.')));

			if (!headers_sent()) {
				if (\Filesystem::isExists()) {

                    $baseName = \Filesystem::getBasename();

					header('Content-Type: application/octet-stream');
					header('Content-Description: File Transfer');
					header('Content-Disposition: attachment; filename="' . ($mask ? $mask : $baseName) . '"');
					header('Content-Transfer-Encoding: binary');
					header('Expires: 0');
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header('Pragma: public');
                    // header('Content-Length: ' . filesize($file));
					header('Content-Length: ' . \Filesystem::getSize());
					// readfile($file, 'rb');
                    echo \Filesystem::read();
					exit;
				} else {
					exit('Error: Could not find file ' . $file . '!');
				}
			} else {
				exit('Error: Headers already sent out!');
			}
		} else {
			$this->language->load('error/not_found');

			$this->document->setTitle($this->language->get('heading_title'));

			$this->data['heading_title'] = $this->language->get('heading_title');

			$this->data['text_not_found'] = $this->language->get('text_not_found');

			$this->data['breadcrumbs'] = array();

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', '', 'SSL'),
				'separator' => false
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('error/not_found', '', 'SSL'),
				'separator' => ' :: '
			);

			$this->template = 'error/not_found.expand';
            $this->base = "common/base";

			$this->response->setOutput($this->render_ecwig());
		}
	}

	public function upload() {
		$this->language->load('sale/order');

		$json = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if (!empty($this->request->files['file']['name'])) {
				$filename = html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8');

				if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 128)) {
					$json['error'] = $this->language->get('error_filename');
				}

				$allowed = array();

				$filetypes = explode(',', $this->config->get('config_upload_allowed'));

				foreach ($filetypes as $filetype) {
					$allowed[] = trim($filetype);
				}

				if (!in_array(utf8_substr(strrchr($filename, '.'), 1), $allowed)) {
					$json['error'] = $this->language->get('error_filetype');
				}

				if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
					$json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
				}
			} else {
				$json['error'] = $this->language->get('error_upload');
			}

			if (!isset($json['error'])) {

                $file = basename($filename) . '.' . md5(mt_rand());

                $path = 'downloads/' . $file;
                \Filesystem::setPath($path)->upload($this->request->files['file']['tmp_name']);

                /*
				if (is_uploaded_file($this->request->files['file']['tmp_name']) && file_exists($this->request->files['file']['tmp_name'])) {
					$file = basename($filename) . '.' . md5(mt_rand());

					$json['file'] = $file;

					move_uploaded_file($this->request->files['file']['tmp_name'], DIR_DOWNLOAD . $file);
				}
                */

				$json['success'] = $this->language->get('text_upload');
			}
		}

		$this->response->setOutput(json_encode($json));
	}

  	public function invoice() {
        $this->data['language_id'] = $this->request->get['language_id'];
        $this->data['language_code'] = $this->request->get['language_code'];
        $this->data['language_directory'] = $this->request->get['language_directory'];

        $languageId = $this->config->get('config_language_id');
        if (isset($this->request->request['language_id'])) {
            $languageId = $this->request->request['language_id'];
        }

        $languageDirectory = 'english';
        if (isset($this->request->request['language_directory'])) {
            $languageDirectory = $this->request->request['language_directory'];
        }

        $this->language->setDirectory($languageDirectory)->load('sale/order');

		$this->data['title'] = $this->language->get('heading_title');

		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			$this->data['base'] = HTTPS_SERVER;
		} else {
			$this->data['base'] = HTTP_SERVER;
		}

		$this->data['direction'] = $this->language->get('direction');
		$this->data['language'] = $this->language->get('code');
		$this->data['config_invoice_width'] = $this->config->get('config_invoice_width');
		$this->data['text_invoice'] = $this->language->get('text_invoice');

		$this->data['text_order_id'] = $this->language->get('text_order_id');
		$this->data['text_invoice_no'] = $this->language->get('text_invoice_no');
		$this->data['text_invoice_date'] = $this->language->get('text_invoice_date');
		$this->data['text_date_added'] = $this->language->get('text_date_added');
		$this->data['text_to'] = $this->language->get('text_to');
		$this->data['text_ship_to'] = $this->language->get('text_ship_to');
        $this->data['delivery_info'] = $this->language->get('delivery_info');
		$this->data['text_payment_method'] = $this->language->get('text_payment_method');
		$this->data['text_shipping_method'] = $this->language->get('text_shipping_method');

		$this->load->model('setting/setting');
		$this->load->model('localisation/language');

		$localizationSettings = $this->model_setting_setting->getSetting('localization');

		$suffix = '';
		if ( $this->request->request['language_code'] != 'en' )
		{
			$specifiedLang = $this->request->request['language_code'];
			$suffix = "_{$specifiedLang}";
		}

		$this->data['column_product'] = $this->language->get('column_product');
		$this->data['column_model'] = ! empty( $localizationSettings['text_product_model' . $suffix] ) ? $localizationSettings['text_product_model' . $suffix] : $this->language->get('column_model');
        $this->data['text_address1'] = ! empty( $localizationSettings['entry_address_1' . $suffix] ) ? $localizationSettings['entry_address_1' . $suffix] : $this->language->get('text_address_1');
        $this->data['text_address2'] = ! empty( $localizationSettings['entry_address_2' . $suffix] ) ? $localizationSettings['entry_address_2' . $suffix] : $this->language->get('text_address_2');
        $this->data['text_city'] = ! empty( $localizationSettings['entry_city' . $suffix] ) ? $localizationSettings['entry_city' . $suffix] : $this->language->get('text_city');
        $this->data['text_zone'] = ! empty( $localizationSettings['entry_checkout_zone' . $suffix] ) ? $localizationSettings['entry_checkout_zone' . $suffix] : $this->language->get('text_zone');
        $this->data['text_area'] = ! empty( $localizationSettings['entry_checkout_area' . $suffix] ) ? $localizationSettings['entry_checkout_area' . $suffix] : $this->language->get('text_area');
        $this->data['text_postcode'] = ! empty( $localizationSettings['entry_postcode' . $suffix] ) ? $localizationSettings['entry_postcode' . $suffix] : $this->language->get('text_postcode');
        $this->data['text_country'] = ! empty( $localizationSettings['entry_country' . $suffix] ) ? $localizationSettings['entry_country' . $suffix] : $this->language->get('text_country');
        $this->data['text_telephone'] = ! empty( $localizationSettings['entry_telephone' . $suffix] ) ? $localizationSettings['entry_telephone' . $suffix] : $this->language->get('text_payment_telephone');
        $this->data['text_company'] = ! empty( $localizationSettings['entry_company' . $suffix] ) ? $localizationSettings['entry_company' . $suffix] : $this->language->get('text_company');
        $this->data['text_fax'] = ! empty( $localizationSettings['entry_fax' . $suffix] ) ? $localizationSettings['entry_fax' . $suffix] : $this->language->get('text_fax');
        $this->data['text_company_id'] = ! empty( $localizationSettings['entry_company_id' . $suffix] ) ? $localizationSettings['entry_company_id' . $suffix] : $this->language->get('text_company_id');
        $this->data['text_tax_id'] = ! empty( $localizationSettings['entry_tax_id' . $suffix] ) ? $localizationSettings['entry_tax_id' . $suffix] : $this->language->get('text_tax_id');
        $this->data['text_invoice_title'] = ! empty( $localizationSettings['entry_invoice' . $suffix] ) ? $localizationSettings['entry_invoice' . $suffix] : $this->language->get('text_invoice');
        $this->data['text_tax_invoice_title'] = ! empty( $localizationSettings['entry_tax_invoice' . $suffix] ) ? $localizationSettings['entry_tax_invoice' . $suffix] : $this->language->get('text_tax_invoice');

        //$this->data['invoice_sub_total'] =
		$this->data['column_quantity'] = $this->language->get('column_quantity');
		$this->data['column_price'] = $this->language->get('column_price');
		$this->data['column_sku'] = $this->language->get('column_sku');
		$this->data['column_total'] = $this->language->get('column_total');
		$this->data['column_comment'] = $this->language->get('column_comment');
        $this->data['column_warehouse'] = $this->language->get('column_warehouse');
        $this->data['text_ref'] = $this->language->get('text_ref');
        $this->data['text_driver'] = $this->language->get('text_driver');
        $this->data['text_delivery_attempts'] = $this->language->get('text_delivery_attempts');
        $this->data['text_note'] = $this->language->get('text_note');

		$this->load->model('sale/order');

		$this->load->model('sale/customer');

        $this->load->model('sale/customer_group');

		$this->load->model('setting/setting');

		$this->load->model('tool/image');

		$this->load->model('localisation/country');

		$this->load->model('localisation/zone');

		$this->load->model('localisation/area');

		$this->data['orders'] = array();

		$printLang = $this->model_localisation_language->getLanguage( $languageId )["code"];

		$this->data['print_lang'] = $printLang;

		$orders = array();

		if (isset($this->request->post['selected'])) {
			$orders = $this->request->post['selected'];
		} elseif (isset($this->request->get['order_id'])) {
			$orders[] = $this->request->get['order_id'];
		}

		//Warehouses check
        $this->data['warehouses'] = false;
        $warehouse_setting = $this->config->get('warehouses');
		if($warehouse_setting && $warehouse_setting['status'] == 1 && $warehouse_setting['invoice_display'] == 1){
            $this->data['warehouses'] = true;
        }

        $this->initializer(['sku' => 'module/product_variations']);

        //////////////////
		foreach ($orders as $order_id) {
            if ($this->config->get('config_invoice_no_barcode') == 1) {
                $barcodeGenerator = (new BarcodeGenerator())
                    ->setType($this->config->get('config_barcode_type'))
                    ->setBarcode($order_id);
                $invoiceBarcodeString = $barcodeGenerator->generate();
            } else {
                $invoiceBarcodeString = '';
            }

			$order_info = $this->model_sale_order->getOrder($order_id);
            $this->data['gift_product']=$order_info['gift_product'];
			$showTotalQuantity = $this->config->get('config_invoice_display_total_quantity') ?? 0;

			if ($order_info) {

				if (strpos($order_info['payment_method'], 'tap') !== false) {
					$order_info['payment_method'] = 'Tap';
				}

				if ($order_info['customer_id'] == 0) {
					$userType = 0;
				} else {
					$userType = 1;
				}
                $customer_group_info = $this->model_sale_customer_group->getCustomerGroup($order_info['customer_group_id']);

                if ($customer_group_info) {
                    $customer_group = $customer_group_info['name'];
                } else {
                    $customer_group = '';
                }

				$store_info = $this->model_setting_setting->getSetting('config', $order_info['store_id']);

				if ($store_info) {
					$store_address = $store_info['config_address'][$printLang];
					$store_email = $store_info['config_email'];
					$store_telephone = $store_info['config_telephone'];
					$store_fax = $store_info['config_fax'];
				} else {
					$store_address = $this->config->get('config_address');
					$store_email = (!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email'));
					$store_telephone = $this->config->get('config_telephone');
					$store_fax = $this->config->get('config_fax');
				}

				if ($order_info['invoice_no']) {
					$invoice_no = $order_info['invoice_prefix'] . $order_info['invoice_no'];
				} else {
					$invoice_no = '';
				}


				$order_info['telephone'] = $this->hideCountryCode($order_info['telephone'], $order_info['payment_country_id'], $this->config->get('config_invoice_hide_country_code'));


				//$format = $this->data['text_company']. ': {firstname} {lastname}' . "\n";
                $format = "";
				if ($order_info['shipping_company']) {
                    $format .= $this->data['text_company'] . ': {company}' . "\n";
                }

                if ($order_info['shipping_address_1'] || $order_info['payment_address_1']) {
                    $format .= $this->data['text_address1'] . ': {address_1}' . "\n";
                }

                if ($order_info['shipping_address_2'] || $order_info['payment_address_2']) {
                    $format .= $this->data['text_address2'] . ': {address_2}' . "\n";
                }

                if ($order_info['shipping_city'] || $order_info['payment_city']) {
                    $format .= $this->data['text_city'] . ': {city}' . "\n";
                }

                if ($order_info['shipping_postcode'] || $order_info['payment_postcode']) {
                    $format .= $this->data['text_postcode'] . ': {postcode}' . "\n";
                }

                if ($order_info['shipping_area'] || $order_info['payment_area_id']) {
                    $format .= $this->data['text_area'] . ': {area}' . "\n";
                }

                if ($order_info['shipping_zone'] || $order_info['payment_zone_id']) {
                    $format .= $this->data['text_zone'] . ': {zone}' . "\n";
                }

                if ($order_info['shipping_country'] || $order_info['payment_country_id']) {
                    $format .= $this->data['text_country'] . ': {country}';
                }
				$shippingCountryLocalised = $this->model_localisation_country->getCountryLocale($order_info["shipping_country_id"], $languageId)["name"];
				$paymentCountryLocalised = $this->model_localisation_country->getCountryLocale($order_info["payment_country_id"], $languageId)["name"];
				$shippingStateLocalised = $this->model_localisation_zone->getZoneLocale($order_info["shipping_zone_id"],$languageId)["name"];
				$paymentStateLocalised = $this->model_localisation_zone->getZoneLocale($order_info["payment_zone_id"],$languageId)["name"];
				$paymentAreaLocalised = $this->model_localisation_area->getAreaLocale($order_info["payment_area_id"],$languageId)["name"];
				$shippingAreaLocalised = $this->model_localisation_area->getAreaLocale($order_info["shipping_area_id"],$languageId)["name"];

				$find = array(
					'{firstname}',
					'{lastname}',
					'{company}',
					'{address_1}',
					'{address_2}',
					'{city}',
					'{postcode}',
					'{area}',
					'{zone}',
					'{zone_code}',
					'{country}'
				);

				$replace = array(
					'firstname' => $order_info['shipping_firstname'] ? $order_info['shipping_firstname'] : $order_info['payment_firstname'],
					'lastname'  => $order_info['shipping_lastname'] ? $order_info['shipping_lastname'] : $order_info['payment_lastname'],
					'company'   => $order_info['shipping_company'] ? $order_info['shipping_company'] : $order_info['payment_company'],
					'address_1' => $order_info['shipping_address_1'] ? $order_info['shipping_address_1'] : $order_info['payment_address_1'],
					'address_2' => $order_info['shipping_address_2'] ? $order_info['shipping_address_2'] : $order_info['payment_address_2'],
					'city'      => $order_info['shipping_city'] ? $order_info['shipping_city'] : $order_info['payment_city'],
					'postcode'  => $order_info['shipping_postcode'] ? $order_info['shipping_postcode'] : $order_info['payment_postcode'],
					'area'      => $shippingAreaLocalised ? $shippingAreaLocalised :($paymentAreaLocalised ? $paymentAreaLocalised : $order_info['shipping_area']) ,
					'zone'      => $shippingStateLocalised ? $shippingStateLocalised :($paymentStateLocalised ? $paymentStateLocalised : $order_info['shipping_zone']) ,
					'zone_code' => $order_info['shipping_zone_code'] ? $order_info['shipping_zone_code'] : $order_info['payment_zone_code'],
					'country'   => $shippingCountryLocalised ? $shippingCountryLocalised : $paymentCountryLocalised
				);
                $shipping_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

                //var_dump($shipping_address);
                //die();

                if ($order_info['payment_address_format']) {
					$format = $order_info['payment_address_format'];
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
					'{area}',
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
					'area'      => $paymentAreaLocalised,
					'zone'      => $paymentStateLocalised,
					'zone_code' => $order_info['payment_zone_code'],
					'country'   => $paymentCountryLocalised
				);

				$payment_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

				$product_data = array();

				$products = $this->model_sale_order->getOrderProductsForInvoice($order_id, $languageId);

				if ($showTotalQuantity == 1) {
					$totalQuantity = 0;
				}

                //Warehouses
                $country_id = $order_info['shipping_country_id'] ? $order_info['shipping_country_id'] : $order_info['payment_country_id'];
                $zone_id    = $order_info['shipping_zone_id'] ? $order_info['shipping_zone_id'] : $order_info['payment_zone_id'];
                $area_id    = $order_info['shipping_area_id'] ? $order_info['shipping_area_id'] : $order_info['payment_area_id'];

                if($this->data['warehouses'] && $country_id)
                {
                    $this->load->model('module/warehouses');
                    $address = [ 'country_id' => $country_id, 'zone_id' => $zone_id];
                    $wrProducts = $this->model_module_warehouses->getGroupProducts($address, $this->request->get['order_id']);

                    if(count($wrProducts['products']) > 0){
                        $warehouses_products = $wrProducts['warehouses_products'];
                        $this->data['wrs_names']  = $wrProducts['wrs_name'];
                    }
                }
                $this->load->model('module/product_bundles/settings');


				foreach ($products as $product) {
					if ($showTotalQuantity == 1) {
						$totalQuantity += $product['quantity'];
					}

					$option_data = array();

					$options = $this->model_sale_order->getOrderOptionsForInvoice($order_id, $product['order_product_id'], $languageId);

                    // Product Option Image PRO module <<

					if (isset($product['image'])) {
                        $this->load->model('module/product_option_image_pro');
                        $poip_installed = $this->model_module_product_option_image_pro->installed();
                        if ($poip_installed) {
                            $product['image'] = $this->model_module_product_option_image_pro->getProductOrderImage($product['product_id'], $options, $product['image']);
                        }
					}

                    // >> Product Option Image PRO module

                    $productOptionValueId = [];
					// define options array
					$optionsArr = [];

					foreach ($options as $option) {
						if ($option['type'] != 'file') {
							$value = $option['value'];
						} else {
							$value = utf8_substr($option['value'], 0, utf8_strrpos($option['value'], '.'));
						}
                        // check if options exists before add value to old values
						if(in_array($option['product_option_id'],$optionsArr)){
                            $option_data[$option['product_option_id']]['value'] .= ' , '.$value;
                        }else{
                            $option_data[$option['product_option_id']] = array(
                                'name'  => $option['name'],
                                'value' => $value
                            );
                        }

						$optionsArr[] = $option['product_option_id'];

//                        if ($option['price_prefix'] === '-') {
//                            $product['total'] -= $option['price'];
//                            $product['price'] -= $option['price'];
//                        } elseif ($option['price_prefix'] === '+') {
//                            $product['total'] += $option['price'];
//                            $product['price'] += $option['price'];
//                        }

                        $productOptionValueId[] = $option['product_option_value_id'];

					}

                    if ($product['rent_data']) {
                        $rentData = json_decode($product['rent_data'], true);
                        $rentData['range'] = array_map(function ($value) {
                            return date("Y-m-d", $value);
                        } , $rentData['range']);
                    }
                    // the new data of rental data comes from order_product_rental table instead of the old rent_data field in order_product table
                    elseif($product['from_date']){
                        $rentData['range']['from'] = $product['from_date'];
                        $rentData['range']['to'] = $product['to_date'];
                        $rentData['diff'] = $product['diff'];
                    }

	                if ($product['price_meter_data']) {
	                    $pricePerMeterData = json_decode($product['price_meter_data'], true);
	                }

                    if ($product['printing_document']) {
                        $printingDocument = json_decode($product['printing_document'], true);
                    }

                    if (isset($product['image'])) {
                        $thumb = $this->model_tool_image->resize($product['image'], 150, 150);
                    } else {
                        $thumb = '';
                    }

                    $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");
                    if ($queryMultiseller->num_rows) {
                        // check if product is sold by a seller.
                        $check = $this->db->query("SELECT `seller_id` FROM `ms_product` WHERE `product_id` = '{$product['product_id']}'");

                        if ($check->num_rows > 0) {
                            $seller_id = $check->row['seller_id'];
                            $seller = $this->db->query("SELECT * FROM `ms_seller` WHERE `seller_id` = '{$seller_id}' AND `seller_status` = '1' AND `seller_approved` = '1'");

                            $sellerAddress = $this->model_sale_customer->getAddress(
                                $this->model_sale_customer->getDefaultAddressId($seller->row['seller_id'])
                            );

                            if ($seller->num_rows > 0) {
                                $seller = (object)$seller->rows[0];
                                $this->data['there_is_a_seller'] = true;
                                $this->load->model('localisation/country');
                                $this->load->model('localisation/zone');

                                $lang_id = $this->config->get('config_language_id');
                                $seller->country = $this->model_localisation_country->getCountryLocale($seller->country_id, $lang_id)['name'];
                                $seller->zone = $this->model_localisation_zone->getZoneLocale($seller->zone_id, $lang_id)['name'];
                                $seller->address = $sellerAddress['address_1'];
                            } else {
                                $seller = null;
                            }
                        }
                    }

                    $this->data['remaining_total'] = null;
                    $this->load->model('module/minimum_deposit/settings');
                    if ($this->model_module_minimum_deposit_settings->isActive()) {
                        $this->data['remaining_total'] = $this->model_sale_order->getOrderProductsRemainingTotal($order_id);
                        $main_price = $this->currency->format($product['main_price'] , $order_info['currency_code'], $order_info['currency_value']);
                        $remaining_amount = $this->currency->format($product['remaining_amount'] , $order_info['remaining_amount'], $order_info['remaining_amount']);
                    }

                    $skuInfo = null;
                    if ($this->sku->isActive()) {
                        $skuInfo = $this->sku->getProductVariationByValuesIds(
                            $product['product_id'], array_column(
                                $this->sku->getOptionValuesIds($productOptionValueId),
                                'option_value_id'
                            )
                        );

                        if ($skuInfo) {
                            $product['sku'] = $skuInfo['product_sku'];
                            $product['barcode'] = $skuInfo['product_barcode']? $skuInfo['product_barcode'] : $product['barcode'] ;
                        }
                    }

                    if ($product['barcode'] != '') {
                        $barcodeGenerator = (new BarcodeGenerator())
                            ->setType($this->config->get('config_barcode_type'))
                            ->setBarcode($product['barcode']);

                        $barcodeImageString = $barcodeGenerator->generate();
                    } else {
                        $barcodeImageString = 0;
                    }
                    // resize image product  to height height and width
                    $config_invoice_product_image = $this->config->get('config_invoice_product_image');
                    if(!$config_invoice_product_image)
                        $invoice_product_image =  $this->model_tool_image->resize($product['image'], 150, 150);
                    else
                        $invoice_product_image = $this->model_tool_image->resize($product['image'], $config_invoice_product_image, $config_invoice_product_image);
                    $orderProductBundles = "";
                    if ($this->model_module_product_bundles_settings->isActive() && $product['order_product_id'] && $this->request->get['order_id']) {
                        //order_product_bundles if exist
                        //$languageId
                        $orderProductBundles = $this->model_sale_order->getOrderProductBundles($product['order_product_id'] , $this->request->get['order_id'] ,$languageId);
                        foreach ($orderProductBundles as $key => $bundle) {
                            $orderProductBundles[$key]['thumb']  = \Filesystem::getUrl('image/' . ($bundle['product_image'] ?: 'no_image.jpg'));
                        }
                    }

                    $prData =  array(
                        'product_id' => $product['product_id'],
                        'name'     => $product['name'],
                        'model'    => $product['model'],
                        'sku'	   => $product['sku'],
                        'option'   => $option_data,
                        'quantity' => $product['quantity'],
                        'barcode'  => $product['barcode'],
                        'barcode_image' => $barcodeImageString,
                        'rentData' => $rentData,
                        'pricePerMeterData'=> $pricePerMeterData,
                        'printingDocument'=> $printingDocument,
                        'image' => \Filesystem::getUrl('image/' . $product['image']),
                        'thumb' => $invoice_product_image,
                        'price'    => $this->currency->format($product['price'] , $order_info['currency_code'], $order_info['currency_value']),
                        'total'    => $this->currency->format($product['total'] , $order_info['currency_code'], $order_info['currency_value']),
                        'main_price'    => isset($main_price) ? $main_price : NULL ,
                        'remaining_amount'    => isset($remaining_amount) ? $remaining_amount : NULL,
                        'seller'    => $seller,
                        'skuInfo' => $skuInfo,
                        'is_soft_deleted' => 0 ,
                        'bundlesData'      => $orderProductBundles,
                    );
                    //Add product warehouse
                    if($this->data['warehouses']){
                        $prData['warehouse'] = $this->data['wrs_names'] ? $this->data['wrs_names'][$warehouses_products[$product['product_id']]] : '';
                    }

                    $product_data[] = $prData;
				}

                // soft deleted
                // check if soft delete is enabled
                $soft_products = [];
                if($this->config->get('config_soft_delete_status')){
                    $soft_deleted_products = $this->model_sale_order->getOrderSoftDeletedProductsForInvoice($order_id, $languageId);
                    foreach ($soft_deleted_products as $product) {
                        // dashed invoice element
                        $product['is_soft_deleted'] = 1 ;
                        $product['price'] = $this->currency->format($product['price'] , $order_info['currency_code'], $order_info['currency_value']);
                        $product['total'] = $this->currency->format($product['total'] , $order_info['currency_code'], $order_info['currency_value']);
                        $soft_products[] = $product;
                        // zero price invoice element
                        $product['price'] = 0;
                        $product['quantity'] = 0;
                        $product['total'] = 0;
                        $product['is_soft_deleted'] = 0;
                        $soft_products[] = $product;
                    }

                }
                $all_products = array_merge($product_data,$soft_products);

				$voucher_data = array();

				$vouchers = $this->model_sale_order->getOrderVouchers($order_id);

				foreach ($vouchers as $voucher) {
					$voucher_data[] = array(
						'description' => $voucher['description'],
						'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value'])
					);
				}

				$earnRewardsToMoney = '';

				$total_data = $this->model_sale_order->getOrderTotals($order_id);

                foreach ($total_data as &$total) {
                    $total['title'] = html_entity_decode($total['title']);
					if ($userType === 1 && $total['code'] === 'earn_reward') {
						$pointsValue = $total['value'];
						$convertValue = $this->model_sale_order->exchangePointToMoney($pointsValue);
						$earnRewardsToMoney = sprintf($this->language->get('text_reward_balance'), (int) $pointsValue, (int) $pointsValue, $convertValue . $order_info['currency_code']);
					}

					if ( $total['code'] != 'tax' ){
						$this->language->setDirectory($languageDirectory)->load('total/' . $total['code']);
                        if($total['code'] == 'sub_total')
                        {
                            $total['title'] = ! empty( $localizationSettings['text_invoice_sub_total' . $suffix] ) ? $localizationSettings['text_invoice_sub_total' . $suffix] : $this->language->get('heading_title');
                        }elseif ( $total['code'] == 'wkpos_discount' ){
							$total['title'] = $this->language->get('wkpos_discount_title');
						}
					}
                    $this->language->setDirectory($languageDirectory)->load('total/' . $total['code']);

					switch ($total['code']) {
                        case 'shipping':
                            if ($order_info['shipping_method'])
                                $total['title'] = $order_info['shipping_method'];
                            break;
                        case 'reward':
                            $replace = $this->getCodeFromTotalTitleString($total['title']);
                            $total['title'] = sprintf($this->language->get('heading_invoice') ,  $replace);
                            break;

                        case 'coupon':
                            $replace = $this->getCodeFromTotalTitleString($total['title']);
                            $automaticCouponWord = '';
                            if ($replace){
                                $this->load->model('sale/coupon');
                                $automaticCoupon= $this->model_sale_coupon->isAutomaticCoupon($replace);
                                if ($automaticCoupon)
                                    $automaticCouponWord = $this->language->get('automatic_coupon');
                            }
                            $total['title'] = sprintf($this->language->get('heading_invoice') ,$automaticCouponWord ,  $replace);
                            break;

                        case 'total':
                            $total['title'] = $this->language->get('heading_title');
                            break;
                    }

					if ($total['code'] == 'cffpm' ){
						$this->load->model('localisation/language');
						if(isset($this->request->get['language_id'])){
							$language_id=$this->request->get['language_id'];
						}
						else if(isset($this->request->post['language_id'])){
							$language_id=$this->request->post['language_id'];
						}
						else{
							$language_id = $this->config->get('config_language_id')?:1;
						}
						$language=$this->model_localisation_language->getLanguage($language_id);
						$total['title']=$this->config->get('cffpm_total_row_name_'.$language['code']);
					}
                }
                if (
                    $this->config->get('config_logo') &&
                    \Filesystem::getUrl('image/' . $this->config->get('config_logo'))
                ) {
                    $this->data['logo'] = \Filesystem::getUrl('image/' . $this->config->get('config_logo'));
                    //$this->model_tool_image->resize($this->config->get('config_logo'), 150, 150);
                } else {
                    $this->data['logo'] = \Filesystem::getUrl('image/no_image.jpg');
                    //$this->model_tool_image->resize('no_image.jpg', 150, 150);
                }

				if ($showTotalQuantity == 1) {
					$total_data[] = [
						'title' => $this->language->get('column_total_quantity'),
						'text' => $totalQuantity
					];
				}
				/////////////////////////// custom_fields
				$this->load->model('module/quickcheckout_fields');
				$this->data['custom_fields'][$order_id] = $this->model_module_quickcheckout_fields->getOrderCustomFields($order_id,$languageId);
				///////////////////
                $this->data['orders'][] = array(
					'order_id'	         => $order_id,
					'invoice_no'         => $invoice_no,
					'invoice_no_barcode' => $invoiceBarcodeString,
					'date_added'         => date($this->language->get('date_format_short'), strtotime($order_info['date_added'])),
                    'time_added'         => date('H:i A', strtotime($order_info['date_added'])),
					'store_name'         =>	$this->config->get('config_name')[$printLang],
					'store_url'          => rtrim($order_info['store_url'], '/'),
					'store_address'      => nl2br($store_address),
					'store_email'        => $store_email,
					'store_telephone'    => $store_telephone,
					'store_fax'          => $store_fax,
					'store_logo'		 => $this->data['logo'],
					'email'              => $order_info['email'],
					'telephone'          => $order_info['telephone'],
					'fax'                => $order_info['fax'],
					'shipping_address'   => $shipping_address,
                    'firstname' => $order_info['payment_firstname'],
                    'lastname'  => $order_info['payment_lastname'],
					'shipping_method'    => $order_info['shipping_method'],
					'payment_address'    => $payment_address,
                    'payment_country'    => $order_info['payment_country'],
                    'payment_zone'       => $order_info['payment_zone'],
					'payment_company_id' => $order_info['payment_company_id'],
					'payment_tax_id'     => $order_info['payment_tax_id'],
					'payment_method'     => $order_info['payment_method'],
                    'product'            => $all_products,
					'voucher'            => $voucher_data,
					'total'              => $total_data,
					'comment'            => nl2br($order_info['comment']),
					'delivery_info'      => nl2br($order_info['delivery_info']),
					'user_type'			 => $userType,
					'customer_group'	 => $customer_group,
					'reward_money'		 => $earnRewardsToMoney
				);
			}


		}



        $this->data['logo_height'] = $this->config->get('config_order_invoice_logo_height');

		// Display tax number in case of the admin set it from setting -> advanced ->tax options
		$tax_number=$this->config->get('config_tax_number');
		if($tax_number){
			$this->data['tax_number']=$tax_number;
		}

		// Display product sku in case of the admin set it from setting -> advanced -> products
		$show_sku_product_invoice=$this->config->get('config_show_sku_product_invoice');
		if($show_sku_product_invoice){
			$this->data['show_sku_product_invoice']=$show_sku_product_invoice;
		}

        // check if delivery slot app installed
        $delivery_slot = $this->config->get('delivery_slot');
        if(is_array($delivery_slot) && count($delivery_slot) > 0){
            $this->language->load('module/delivery_slot');

            $this->load->model('module/delivery_slot/slots');
            $orderSlot = $this->model_module_delivery_slot_slots->getOrderDeliverySlot($order_id);
            if($orderSlot['delivery_date']){
                //Convert m-d-Y to d-m-Y to be able to formate it
                $tempDate = explode('-', $orderSlot['delivery_date']);
                $newDate = $tempDate['1']."-".$tempDate['0']."-".$tempDate['2'];
                $orderSlot['delivery_date'] = date('d/m/Y', strtotime($newDate));
            }
            $this->data['delivery_slot'] = true;
            $this->data['order_delivery_slot'] = $orderSlot;
        }

        // /////////////////////////// custom_fields
        // $this->load->model('module/quickcheckout_fields');
        // $this->data['custom_fields'] = $this->model_module_quickcheckout_fields->getOrderCustomFields($order_id,$languageId);
        // ///////////////////
		$cit_config_status = 0;

		if (\Extension::isInstalled('custom_invoice_template')) {
			$cit_config_status = $this->config->get('cit')['status'] ?? 0;
		}

		$new_invoice = isset($this->request->request['new_invoice']);
		if($new_invoice){
			$this->template = 'sale/order_invoice2.expand';
		}
		else if ($cit_config_status == 1) {
			require_once(DIR_APPLICATION . 'controller/module/custom_invoice_template.php');
			$cit = 'ControllerModuleCustomInvoiceTemplate';
			$cit = new $cit($this->registry);
			$template = $cit->renderTemplate($this->data['orders'], $languageId);
			if (empty($template)) {
				if(file_exists(DIR_TEMPLATE . 'sale/custom_invoice/' . STORECODE . '.expand')) {
					$this->template = 'sale/custom_invoice/' . STORECODE . '.expand';
				} else {
					$this->template = 'sale/order_invoice.expand';
				}
			} else {
				$this->data['invoice'] = html_entity_decode($template);
				$this->template = 'module/custom_invoice_template/render.expand';
			}
		} else {

			if(file_exists(DIR_TEMPLATE . 'sale/custom_invoice/' . STORECODE . '.expand')) {
				$this->template = 'sale/custom_invoice/' . STORECODE . '.expand';
			}
			else {
				$this->template = 'sale/order_invoice.expand';
			}

			//$this->template = 'sale/order_invoice.expand';

		}

		$this->base="common/base";

		$this->response->setOutput( $this->render_ecwig() );
	}

  // this function is using to create a shipping lable can use in printing

    public function printShippingLable() {
        $languageTemp['language_id'] = $this->config->get('config_language_id');
        $languageTemp['language_code'] = $this->config->get('config_language');

        $languageId = $this->config->get('config_language_id');
        if (isset($this->request->request['language_id'])) {
            $languageId = $this->request->request['language_id'];
        }

        $languageDirectory = 'english';
        if (isset($this->request->request['language_directory'])) {
            $languageDirectory = $this->request->request['language_directory'];
        }

        $this->language->setDirectory($languageDirectory)->load('sale/order');

        $this->data['title'] = $this->language->get('heading_title');

        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            $this->data['base'] = HTTPS_SERVER;
        } else {
            $this->data['base'] = HTTP_SERVER;
        }

        $this->data['direction'] = $this->language->get('direction');
        $this->data['language'] = $this->language->get('code');
        $this->data['config_invoice_width'] = $this->config->get('config_invoice_width');
        $this->data['text_invoice'] = $this->language->get('text_invoice');

        $this->data['text_order_id'] = $this->language->get('text_order_id');
        $this->data['text_invoice_date'] = $this->language->get('text_invoice_date');
        $this->data['text_date_added'] = $this->language->get('text_date_added');
        $this->data['text_to'] = $this->language->get('text_to');
        $this->data['text_ship_to'] = $this->language->get('text_ship_to');
        $this->data['delivery_info'] = $this->language->get('delivery_info');
        $this->data['text_payment_method'] = $this->language->get('text_payment_method');
        $this->data['text_shipping_method'] = $this->language->get('text_shipping_method');

        $this->initializer([
            'saleOrder' => 'sale/order',
            'saleCustomer' => 'sale/customer',
            'saleCustomerGroup' => 'sale/customer_group',
            'setting' => 'setting/setting',
            'toolImage' => 'tool/image',
            'localisationCountry' => 'localisation/country',
            'localisationZone' => 'localisation/zone',
            'localisationArea' => 'localisation/area',
            'sku' => 'module/product_variations',
            'localisationLanguage' => 'localisation/language'
        ]);


        $localizationSettings = $this->model_setting_setting->getSetting('localization');

        $suffix = '';
        if ($this->request->request['language_code'] != 'en') {
            $specifiedLang = $this->request->request['language_code'];
            $suffix = "_{$specifiedLang}";
        }

        $this->data['text_address1'] = !empty($localizationSettings['entry_address_1' . $suffix]) ? $localizationSettings['entry_address_1' . $suffix] : $this->language->get('text_address_1');
        $this->data['text_address2'] = !empty($localizationSettings['entry_address_2' . $suffix]) ? $localizationSettings['entry_address_2' . $suffix] : $this->language->get('text_address_2');
        $this->data['text_city'] = !empty($localizationSettings['entry_city' . $suffix]) ? $localizationSettings['entry_city' . $suffix] : $this->language->get('text_city');
        $this->data['text_zone'] = !empty($localizationSettings['entry_checkout_zone' . $suffix]) ? $localizationSettings['entry_checkout_zone' . $suffix] : $this->language->get('text_zone');
        $this->data['text_area'] = !empty($localizationSettings['entry_checkout_area' . $suffix]) ? $localizationSettings['entry_checkout_area' . $suffix] : $this->language->get('text_area');
        $this->data['text_postcode'] = !empty($localizationSettings['entry_postcode' . $suffix]) ? $localizationSettings['entry_postcode' . $suffix] : $this->language->get('text_postcode');
        $this->data['text_country'] = !empty($localizationSettings['entry_country' . $suffix]) ? $localizationSettings['entry_country' . $suffix] : $this->language->get('text_country');
        $this->data['text_telephone'] = !empty($localizationSettings['entry_telephone' . $suffix]) ? $localizationSettings['entry_telephone' . $suffix] : $this->language->get('text_payment_telephone');
        $this->data['text_company'] = !empty($localizationSettings['entry_company' . $suffix]) ? $localizationSettings['entry_company' . $suffix] : $this->language->get('text_company');
        $this->data['text_fax'] = !empty($localizationSettings['entry_fax' . $suffix]) ? $localizationSettings['entry_fax' . $suffix] : $this->language->get('text_fax');
        $this->data['text_company_id'] = !empty($localizationSettings['entry_company_id' . $suffix]) ? $localizationSettings['entry_company_id' . $suffix] : $this->language->get('text_company_id');
        $this->data['text_tax_id'] = !empty($localizationSettings['entry_tax_id' . $suffix]) ? $localizationSettings['entry_tax_id' . $suffix] : $this->language->get('text_tax_id');

        $this->data['text_ref'] = $this->language->get('text_ref');
        $this->data['text_driver'] = $this->language->get('text_driver');
        $this->data['text_delivery_attempts'] = $this->language->get('text_delivery_attempts');



        $this->data['orders'] = array();

        $printLang = $this->model_localisation_language->getLanguage($languageId)["code"];

        $this->data['print_lang'] = $printLang;

        $orders = array();

        if (isset($this->request->post['selected'])) {
            $orders = $this->request->post['selected'];
        } elseif (isset($this->request->get['order_id'])) {
            $orders[] = $this->request->get['order_id'];
        }

        if (
                $this->config->get('config_logo') &&
                \Filesystem::getUrl('image/' . $this->config->get('config_logo'))
        ) {
            $this->data['logo'] = \Filesystem::getUrl('image/' . $this->config->get('config_logo'));
        } else {
            $this->data['logo'] = \Filesystem::getUrl('image/no_image.jpg');
        }

        //////////////////
        foreach ($orders as $order_id) {
            if ($this->config->get('config_invoice_no_barcode') == 1) {
                $barcodeGenerator = (new BarcodeGenerator())
                        ->setType($this->config->get('config_barcode_type'))
                        ->setBarcode($order_id);
                $invoiceBarcodeString = $barcodeGenerator->generate();
            } else {
                $invoiceBarcodeString = '';
            }

            $order_info = $this->model_sale_order->getOrder($order_id);

            if ($order_info) {

                if (strpos($order_info['payment_method'], 'tap') !== false) {
                    $order_info['payment_method'] = 'Tap';
                }

                if ($order_info['customer_id'] == 0) {
                    $userType = 0;
                } else {
                    $userType = 1;
                }
                $customer_group_info = $this->model_sale_customer_group->getCustomerGroup($order_info['customer_group_id']);

                if ($customer_group_info) {
                    $customer_group = $customer_group_info['name'];
                } else {
                    $customer_group = '';
                }

                $store_info = $this->model_setting_setting->getSetting('config', $order_info['store_id']);

                if ($store_info) {
                    $store_address = $store_info['config_address'][$printLang];
                    $store_email = $store_info['config_email'];
                    $store_telephone = $store_info['config_telephone'];
                    $store_fax = $store_info['config_fax'];
                } else {
                    $store_address = $this->config->get('config_address');
                    $store_email = (!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') : $this->config->get('config_email'));
                    $store_telephone = $this->config->get('config_telephone');
                    $store_fax = $this->config->get('config_fax');
                }


                $order_info['telephone'] = $this->hideCountryCode($order_info['telephone'], $order_info['payment_country_id'], $this->config->get('config_invoice_hide_country_code'));

                $format = "";
                if ($order_info['shipping_company']) {
                    $format .= $this->data['text_company'] . ': {company}' . "\n";
                }

                if ($order_info['shipping_address_1'] || $order_info['payment_address_1']) {
                    $format .= $this->data['text_address1'] . ': {address_1}' . "\n";
                }

                if ($order_info['shipping_address_2'] || $order_info['payment_address_2']) {
                    $format .= $this->data['text_address2'] . ': {address_2}' . "\n";
                }

                if ($order_info['shipping_city'] || $order_info['payment_city']) {
                    $format .= $this->data['text_city'] . ': {city}' . "\n";
                }

                if ($order_info['shipping_postcode'] || $order_info['payment_postcode']) {
                    $format .= $this->data['text_postcode'] . ': {postcode}' . "\n";
                }

                if ($order_info['shipping_area'] || $order_info['payment_area_id']) {
                    $format .= $this->data['text_area'] . ': {area}' . "\n";
                }

                if ($order_info['shipping_zone'] || $order_info['payment_zone_id']) {
                    $format .= $this->data['text_zone'] . ': {zone}' . "\n";
                }

                if ($order_info['shipping_country'] || $order_info['payment_country_id']) {
                    $format .= $this->data['text_country'] . ': {country}';
                }
                $shippingCountryLocalised = $this->model_localisation_country->getCountryLocale($order_info["shipping_country_id"], $languageId)["name"];
                $paymentCountryLocalised = $this->model_localisation_country->getCountryLocale($order_info["payment_country_id"], $languageId)["name"];
                $shippingStateLocalised = $this->model_localisation_zone->getZoneLocale($order_info["shipping_zone_id"], $languageId)["name"];
                $paymentStateLocalised = $this->model_localisation_zone->getZoneLocale($order_info["payment_zone_id"], $languageId)["name"];
                $paymentAreaLocalised = $this->model_localisation_area->getAreaLocale($order_info["payment_area_id"], $languageId)["name"];
                $shippingAreaLocalised = $this->model_localisation_area->getAreaLocale($order_info["shipping_area_id"], $languageId)["name"];

                $find = array(
                    '{firstname}',
                    '{lastname}',
                    '{company}',
                    '{address_1}',
                    '{address_2}',
                    '{city}',
                    '{postcode}',
                    '{area}',
                    '{zone}',
                    '{zone_code}',
                    '{country}'
                );

                $replace = array(
                    'firstname' => $order_info['shipping_firstname'] ? $order_info['shipping_firstname'] : $order_info['payment_firstname'],
                    'lastname' => $order_info['shipping_lastname'] ? $order_info['shipping_lastname'] : $order_info['payment_lastname'],
                    'company' => $order_info['shipping_company'] ? $order_info['shipping_company'] : $order_info['payment_company'],
                    'address_1' => $order_info['shipping_address_1'] ? $order_info['shipping_address_1'] : $order_info['payment_address_1'],
                    'address_2' => $order_info['shipping_address_2'] ? $order_info['shipping_address_2'] : $order_info['payment_address_2'],
                    'city' => $order_info['shipping_city'] ? $order_info['shipping_city'] : $order_info['payment_city'],
                    'postcode' => $order_info['shipping_postcode'] ? $order_info['shipping_postcode'] : $order_info['payment_postcode'],
                    'area' => $shippingAreaLocalised ? $shippingAreaLocalised : ($paymentAreaLocalised ? $paymentAreaLocalised : $order_info['shipping_area']),
                    'zone' => $shippingStateLocalised ? $shippingStateLocalised : ($paymentStateLocalised ? $paymentStateLocalised : $order_info['shipping_zone']),
                    'zone_code' => $order_info['shipping_zone_code'] ? $order_info['shipping_zone_code'] : $order_info['payment_zone_code'],
                    'country' => $shippingCountryLocalised ? $shippingCountryLocalised : $paymentCountryLocalised
                );
                $shipping_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

                if ($order_info['payment_address_format']) {
                    $format = $order_info['payment_address_format'];
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
                    '{area}',
                    '{zone}',
                    '{zone_code}',
                    '{country}'
                );

                $replace = array(
                    'firstname' => $order_info['payment_firstname'],
                    'lastname' => $order_info['payment_lastname'],
                    'company' => $order_info['payment_company'],
                    'address_1' => $order_info['payment_address_1'],
                    'address_2' => $order_info['payment_address_2'],
                    'city' => $order_info['payment_city'],
                    'postcode' => $order_info['payment_postcode'],
                    'area' => $paymentAreaLocalised,
                    'zone' => $paymentStateLocalised,
                    'zone_code' => $order_info['payment_zone_code'],
                    'country' => $paymentCountryLocalised
                );

                $payment_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));


                $this->data['orders'][] = [
                'order_id' => $order_id,
                'invoice_no_barcode' => $invoiceBarcodeString,
                'date_added' => date($this->language->get('date_format_short'), strtotime($order_info['date_added'])),
                'time_added' => date('H:i', strtotime($this->getDateByCurrentTimeZone($order_info['date_added']))),
                'store_name' => $this->config->get('config_name')[$printLang],
                'store_url' => rtrim($order_info['store_url'], '/'),
                'store_address' => nl2br($store_address),
                'store_email' => $store_email,
                'store_telephone' => $store_telephone,
                'store_fax' => $store_fax,
                'store_logo' => $this->data['logo'],
                'email' => $order_info['email'],
                'telephone' => $order_info['telephone'],
                'fax' => $order_info['fax'],
                'shipping_address' => $shipping_address,
                'firstname' => $order_info['payment_firstname'],
                'lastname' => $order_info['payment_lastname'],
                'shipping_method' => $order_info['shipping_method'],
                'payment_address' => $payment_address,
                'payment_company_id' => $order_info['payment_company_id'],
                'payment_tax_id' => $order_info['payment_tax_id'],
                'payment_method' => $order_info['payment_method'],
                'delivery_info' => nl2br($order_info['delivery_info'])
            ];

                   // check if delivery slot app installed
                $delivery_slot = $this->config->get('delivery_slot');
                if (is_array($delivery_slot) && count($delivery_slot) > 0) {
                    $this->language->load('module/delivery_slot');

                    $this->load->model('module/delivery_slot/slots');
                    $orderSlot = $this->model_module_delivery_slot_slots->getOrderDeliverySlot($order_id);
                    $this->data['delivery_slot'] = true;
                    $this->data['order_delivery_slot'] = $orderSlot;
                }
            }
        }

        $this->template = 'sale/order_shipping_lable.expand';
        $this->base = "common/base";
        $this->response->setOutput($this->render_ecwig());
    }

    public function hit_two_get_pack_type($selected) {
		$pack_type = 'OD';
		if ($selected == 'FLY') {
		$pack_type = 'DF';
		} elseif ($selected == 'BOX') {
		$pack_type = 'OD';
		}
		elseif ($selected == 'YP') {
		$pack_type = 'YP';
		}
		return $pack_type;
	}
	public function hit_dhl_is_eu_country ($countrycode, $destinationcode) {
		$eu_countrycodes = array(
		'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE',
		'ES', 'FI', 'FR', 'GB', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV',
		'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK',
		'HR', 'GR'

		);
		return(in_array($countrycode, $eu_countrycodes) && in_array($destinationcode, $eu_countrycodes));
	}
	public function weight_based_shipping($package,$orderCurrency,$weight_unit,$diam_unit,$maximum_weight)
	{
		if ( ! class_exists( 'WeightPack' ) ) {

		include_once DIR_CATALOG.'model/extension/shipping/class-hit-weight-packing.php';
		}

		$weight_pack=new WeightPack('simple');
		$weight_pack->set_max_weight($maximum_weight);

		$package_total_weight = 0;
		$insured_value = 0;
		$this->load->model('catalog/product');
		$ctr = 0;
		foreach ($package as $item_id => $values) {
		$ctr++;

		$item = $this->model_catalog_product->getProduct($values['product_id']);
		if(isset($item['shipping']) && $item['shipping'] == 0)
		{
		continue;
		}
		if (!$item['weight']) {
		// $this->debug(sprintf(__('Product #%d is missing weight.', 'wf-shipping-dhl'), $ctr), 'error');
		return;
		}

		$chk_qty = $values['quantity'];

		$weight_pack->add_item($item['weight'], $values, 1);
		}

		$pack = $weight_pack->pack_items();
		$errors = $pack->get_errors();
		if( !empty($errors) ){
		//do nothing
		return;
		} else {
		$boxes = $pack->get_packed_boxes();
		$unpacked_items = $pack->get_unpacked_items();

		$insured_value = 0;

		$packages = array_merge( $boxes, $unpacked_items ); // merge items if unpacked are allowed
		$package_count = sizeof($packages);
		// get all items to pass if item info in box is not distinguished
		$packable_items = $weight_pack->get_packable_items();
		$all_items = array();
		if(is_array($packable_items)){
		foreach($packable_items as $packable_item){
		$all_items[] = $packable_item['data'];
		}
		}
		//pre($packable_items);
		$order_total = '';

		$to_ship = array();
		$group_id = 1;
		foreach($packages as $package){//pre($package);
		$packed_products = array();

		if(($package_count == 1) && isset($order_total)){
		$insured_value = $item['price'] * $chk_qty;
		}else{
		$insured_value = 0;
		if(!empty($package['items'])){
		foreach($package['items'] as $item){

		$insured_value = $insured_value; //+ $item->price;
		}
		}else{
		if( isset($order_total) && $package_count){
		$insured_value = $order_total/$package_count;
		}
		}
		}
		$packed_products = isset($package['items']) ? $package['items'] : $all_items;
		// Creating package request
		$package_total_weight = $package['weight'];

		$insurance_array = array(
		'Amount' => $insured_value,
		'Currency' => $orderCurrency
		);

		$group = array(
		'GroupNumber' => $group_id,
		'GroupPackageCount' => 1,
		'Weight' => array(
		'Value' => round($package_total_weight, 3),
		'Units' => $weight_unit
		),
		'packed_products' => $packed_products,
		);
		$group['InsuredValue'] = $insurance_array;
		$group['packtype'] = 'OD';

		$to_ship[] = $group;
		$group_id++;
		}
		}
		return $to_ship;
	}
	public function per_item_shipping($package,$orderCurrency,$weight_unit,$diam_unit,$pack_type) {
		$to_ship = array();
		$group_id = 1;

		$this->load->model('catalog/product');
		foreach ($package as $item_id => $values) {


		$item = $this->model_catalog_product->getProduct($values['product_id']);
		if(isset($item['shipping']) && $item['shipping'] == 0)
		{
		continue;
		}

		$group = array();
		$insurance_array = array(
		'Amount' => round($item['price']),
		'Currency' => $orderCurrency
		);

		if($item['weight'] < 0.001){
		$dhl_per_item_weight = 0.001;
		}else{
		$dhl_per_item_weight = round($item['weight'], 3);
		}
		$group = array(
		'GroupNumber' => $group_id,
		'GroupPackageCount' => 1,
		'Weight' => array(
		'Value' => $dhl_per_item_weight,
		'Units' => $weight_unit
		),
		'packed_products' => $values
		);

		if ($item['width'] && $item['height'] && $item['length']) {

		$group['Dimensions'] = array(
		'Length' => max(1, round($item['length'],3)),
		'Width' => max(1, round($item['width'],3)),
		'Height' => max(1, round($item['height'],3)),
		'Units' => $diam_unit
		);
		}
		$group['packtype'] = $pack_type;
		$group['InsuredValue'] = $insurance_array;

		$chk_qty = $values['quantity'];

		for ($i = 0; $i < $chk_qty; $i++)
		$to_ship[] = $group;

		$group_id++;

		}

		return $to_ship;
	}
	public function hit_get_local_product_code( $global_product_code, $origin_country='', $destination_country='' ){

		$countrywise_local_product_code = array(
		'SA' => 'global_product_code',
		'ZA' => 'global_product_code',
		'CH' => 'global_product_code'
		);

		if( array_key_exists($origin_country, $countrywise_local_product_code) ){
		return ($countrywise_local_product_code[$origin_country] == 'global_product_code') ? $global_product_code : $countrywise_local_product_code[$origin_country];

		}
		return $global_product_code;
	}
	public function hit_dhl_get_currency_name()
	{
		return array(
		'AF' => 'Afghanistan',
		'AX' => 'Aland Islands',
		'AL' => 'Albania',
		'DZ' => 'Algeria',
		'AS' => 'American Samoa',
		'AD' => 'Andorra',
		'AO' => 'Angola',
		'AI' => 'Anguilla',
		'AQ' => 'Antarctica',
		'AG' => 'Antigua and Barbuda',
		'AR' => 'Argentina',
		'AM' => 'Armenia',
		'AW' => 'Aruba',
		'AU' => 'Australia',
		'AT' => 'Austria',
		'AZ' => 'Azerbaijan',
		'BS' => 'Bahamas',
		'BH' => 'Bahrain',
		'BD' => 'Bangladesh',
		'BB' => 'Barbados',
		'BY' => 'Belarus',
		'BE' => 'Belgium',
		'BZ' => 'Belize',
		'BJ' => 'Benin',
		'BM' => 'Bermuda',
		'BT' => 'Bhutan',
		'BO' => 'Bolivia',
		'BQ' => 'Bonaire, Saint Eustatius and Saba',
		'BA' => 'Bosnia and Herzegovina',
		'BW' => 'Botswana',
		'BV' => 'Bouvet Island',
		'BR' => 'Brazil',
		'IO' => 'British Indian Ocean Territory',
		'VG' => 'British Virgin Islands',
		'BN' => 'Brunei',
		'BG' => 'Bulgaria',
		'BF' => 'Burkina Faso',
		'BI' => 'Burundi',
		'KH' => 'Cambodia',
		'CM' => 'Cameroon',
		'CA' => 'Canada',
		'CV' => 'Cape Verde',
		'KY' => 'Cayman Islands',
		'CF' => 'Central African Republic',
		'TD' => 'Chad',
		'CL' => 'Chile',
		'CN' => 'China',
		'CX' => 'Christmas Island',
		'CC' => 'Cocos Islands',
		'CO' => 'Colombia',
		'KM' => 'Comoros',
		'CK' => 'Cook Islands',
		'CR' => 'Costa Rica',
		'HR' => 'Croatia',
		'CU' => 'Cuba',
		'CW' => 'Curacao',
		'CY' => 'Cyprus',
		'CZ' => 'Czech Republic',
		'CD' => 'Democratic Republic of the Congo',
		'DK' => 'Denmark',
		'DJ' => 'Djibouti',
		'DM' => 'Dominica',
		'DO' => 'Dominican Republic',
		'TL' => 'East Timor',
		'EC' => 'Ecuador',
		'EG' => 'Egypt',
		'SV' => 'El Salvador',
		'GQ' => 'Equatorial Guinea',
		'ER' => 'Eritrea',
		'EE' => 'Estonia',
		'ET' => 'Ethiopia',
		'FK' => 'Falkland Islands',
		'FO' => 'Faroe Islands',
		'FJ' => 'Fiji',
		'FI' => 'Finland',
		'FR' => 'France',
		'GF' => 'French Guiana',
		'PF' => 'French Polynesia',
		'TF' => 'French Southern Territories',
		'GA' => 'Gabon',
		'GM' => 'Gambia',
		'GE' => 'Georgia',
		'DE' => 'Germany',
		'GH' => 'Ghana',
		'GI' => 'Gibraltar',
		'GR' => 'Greece',
		'GL' => 'Greenland',
		'GD' => 'Grenada',
		'GP' => 'Guadeloupe',
		'GU' => 'Guam',
		'GT' => 'Guatemala',
		'GG' => 'Guernsey',
		'GN' => 'Guinea',
		'GW' => 'Guinea-Bissau',
		'GY' => 'Guyana',
		'HT' => 'Haiti',
		'HM' => 'Heard Island and McDonald Islands',
		'HN' => 'Honduras',
		'HK' => 'Hong Kong',
		'HU' => 'Hungary',
		'IS' => 'Iceland',
		'IN' => 'India',
		'ID' => 'Indonesia',
		'IR' => 'Iran',
		'IQ' => 'Iraq',
		'IE' => 'Ireland',
		'IM' => 'Isle of Man',
		'IL' => 'Israel',
		'IT' => 'Italy',
		'CI' => 'Ivory Coast',
		'JM' => 'Jamaica',
		'JP' => 'Japan',
		'JE' => 'Jersey',
		'JO' => 'Jordan',
		'KZ' => 'Kazakhstan',
		'KE' => 'Kenya',
		'KI' => 'Kiribati',
		'XK' => 'Kosovo',
		'KW' => 'Kuwait',
		'KG' => 'Kyrgyzstan',
		'LA' => 'Laos',
		'LV' => 'Latvia',
		'LB' => 'Lebanon',
		'LS' => 'Lesotho',
		'LR' => 'Liberia',
		'LY' => 'Libya',
		'LI' => 'Liechtenstein',
		'LT' => 'Lithuania',
		'LU' => 'Luxembourg',
		'MO' => 'Macao',
		'MK' => 'Macedonia',
		'MG' => 'Madagascar',
		'MW' => 'Malawi',
		'MY' => 'Malaysia',
		'MV' => 'Maldives',
		'ML' => 'Mali',
		'MT' => 'Malta',
		'MH' => 'Marshall Islands',
		'MQ' => 'Martinique',
		'MR' => 'Mauritania',
		'MU' => 'Mauritius',
		'YT' => 'Mayotte',
		'MX' => 'Mexico',
		'FM' => 'Micronesia',
		'MD' => 'Moldova',
		'MC' => 'Monaco',
		'MN' => 'Mongolia',
		'ME' => 'Montenegro',
		'MS' => 'Montserrat',
		'MA' => 'Morocco',
		'MZ' => 'Mozambique',
		'MM' => 'Myanmar',
		'NA' => 'Namibia',
		'NR' => 'Nauru',
		'NP' => 'Nepal',
		'NL' => 'Netherlands',
		'NC' => 'New Caledonia',
		'NZ' => 'New Zealand',
		'NI' => 'Nicaragua',
		'NE' => 'Niger',
		'NG' => 'Nigeria',
		'NU' => 'Niue',
		'NF' => 'Norfolk Island',
		'KP' => 'North Korea',
		'MP' => 'Northern Mariana Islands',
		'NO' => 'Norway',
		'OM' => 'Oman',
		'PK' => 'Pakistan',
		'PW' => 'Palau',
		'PS' => 'Palestinian Territory',
		'PA' => 'Panama',
		'PG' => 'Papua New Guinea',
		'PY' => 'Paraguay',
		'PE' => 'Peru',
		'PH' => 'Philippines',
		'PN' => 'Pitcairn',
		'PL' => 'Poland',
		'PT' => 'Portugal',
		'PR' => 'Puerto Rico',
		'QA' => 'Qatar',
		'CG' => 'Republic of the Congo',
		'RE' => 'Reunion',
		'RO' => 'Romania',
		'RU' => 'Russia',
		'RW' => 'Rwanda',
		'BL' => 'Saint Barthelemy',
		'SH' => 'Saint Helena',
		'KN' => 'Saint Kitts and Nevis',
		'LC' => 'Saint Lucia',
		'MF' => 'Saint Martin',
		'PM' => 'Saint Pierre and Miquelon',
		'VC' => 'Saint Vincent and the Grenadines',
		'WS' => 'Samoa',
		'SM' => 'San Marino',
		'ST' => 'Sao Tome and Principe',
		'SA' => 'Saudi Arabia',
		'SN' => 'Senegal',
		'RS' => 'Serbia',
		'SC' => 'Seychelles',
		'SL' => 'Sierra Leone',
		'SG' => 'Singapore',
		'SX' => 'Sint Maarten',
		'SK' => 'Slovakia',
		'SI' => 'Slovenia',
		'SB' => 'Solomon Islands',
		'SO' => 'Somalia',
		'ZA' => 'South Africa',
		'GS' => 'South Georgia and the South Sandwich Islands',
		'KR' => 'South Korea',
		'SS' => 'South Sudan',
		'ES' => 'Spain',
		'LK' => 'Sri Lanka',
		'SD' => 'Sudan',
		'SR' => 'Suriname',
		'SJ' => 'Svalbard and Jan Mayen',
		'SZ' => 'Swaziland',
		'SE' => 'Sweden',
		'CH' => 'Switzerland',
		'SY' => 'Syria',
		'TW' => 'Taiwan',
		'TJ' => 'Tajikistan',
		'TZ' => 'Tanzania',
		'TH' => 'Thailand',
		'TG' => 'Togo',
		'TK' => 'Tokelau',
		'TO' => 'Tonga',
		'TT' => 'Trinidad and Tobago',
		'TN' => 'Tunisia',
		'TR' => 'Turkey',
		'TM' => 'Turkmenistan',
		'TC' => 'Turks and Caicos Islands',
		'TV' => 'Tuvalu',
		'VI' => 'U.S. Virgin Islands',
		'UG' => 'Uganda',
		'UA' => 'Ukraine',
		'AE' => 'United Arab Emirates',
		'GB' => 'United Kingdom',
		'US' => 'United States',
		'UM' => 'United States Minor Outlying Islands',
		'UY' => 'Uruguay',
		'UZ' => 'Uzbekistan',
		'VU' => 'Vanuatu',
		'VA' => 'Vatican',
		'VE' => 'Venezuela',
		'VN' => 'Vietnam',
		'WF' => 'Wallis and Futuna',
		'EH' => 'Western Sahara',
		'YE' => 'Yemen',
		'ZM' => 'Zambia',
		'ZW' => 'Zimbabwe',
		);

	}
	public function hit_dhl_get_currency()
	{

		$value = array();
		$value['AD'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['AE'] = array('region' => 'AP', 'currency' =>'AED', 'weight' => 'KG_CM');
		$value['AF'] = array('region' => 'AP', 'currency' =>'AFN', 'weight' => 'KG_CM');
		$value['AG'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'LB_IN');
		$value['AI'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'LB_IN');
		$value['AL'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['AM'] = array('region' => 'AP', 'currency' =>'AMD', 'weight' => 'KG_CM');
		$value['AN'] = array('region' => 'AM', 'currency' =>'ANG', 'weight' => 'KG_CM');
		$value['AO'] = array('region' => 'AP', 'currency' =>'AOA', 'weight' => 'KG_CM');
		$value['AR'] = array('region' => 'AM', 'currency' =>'ARS', 'weight' => 'KG_CM');
		$value['AS'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['AT'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['AU'] = array('region' => 'AP', 'currency' =>'AUD', 'weight' => 'KG_CM');
		$value['AW'] = array('region' => 'AM', 'currency' =>'AWG', 'weight' => 'LB_IN');
		$value['AZ'] = array('region' => 'AM', 'currency' =>'AZN', 'weight' => 'KG_CM');
		$value['AZ'] = array('region' => 'AM', 'currency' =>'AZN', 'weight' => 'KG_CM');
		$value['GB'] = array('region' => 'EU', 'currency' =>'GBP', 'weight' => 'KG_CM');
		$value['BA'] = array('region' => 'AP', 'currency' =>'BAM', 'weight' => 'KG_CM');
		$value['BB'] = array('region' => 'AM', 'currency' =>'BBD', 'weight' => 'LB_IN');
		$value['BD'] = array('region' => 'AP', 'currency' =>'BDT', 'weight' => 'KG_CM');
		$value['BE'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['BF'] = array('region' => 'AP', 'currency' =>'XOF', 'weight' => 'KG_CM');
		$value['BG'] = array('region' => 'EU', 'currency' =>'BGN', 'weight' => 'KG_CM');
		$value['BH'] = array('region' => 'AP', 'currency' =>'BHD', 'weight' => 'KG_CM');
		$value['BI'] = array('region' => 'AP', 'currency' =>'BIF', 'weight' => 'KG_CM');
		$value['BJ'] = array('region' => 'AP', 'currency' =>'XOF', 'weight' => 'KG_CM');
		$value['BM'] = array('region' => 'AM', 'currency' =>'BMD', 'weight' => 'LB_IN');
		$value['BN'] = array('region' => 'AP', 'currency' =>'BND', 'weight' => 'KG_CM');
		$value['BO'] = array('region' => 'AM', 'currency' =>'BOB', 'weight' => 'KG_CM');
		$value['BR'] = array('region' => 'AM', 'currency' =>'BRL', 'weight' => 'KG_CM');
		$value['BS'] = array('region' => 'AM', 'currency' =>'BSD', 'weight' => 'LB_IN');
		$value['BT'] = array('region' => 'AP', 'currency' =>'BTN', 'weight' => 'KG_CM');
		$value['BW'] = array('region' => 'AP', 'currency' =>'BWP', 'weight' => 'KG_CM');
		$value['BY'] = array('region' => 'AP', 'currency' =>'BYR', 'weight' => 'KG_CM');
		$value['BZ'] = array('region' => 'AM', 'currency' =>'BZD', 'weight' => 'KG_CM');
		$value['CA'] = array('region' => 'AM', 'currency' =>'CAD', 'weight' => 'LB_IN');
		$value['CF'] = array('region' => 'AP', 'currency' =>'XAF', 'weight' => 'KG_CM');
		$value['CG'] = array('region' => 'AP', 'currency' =>'XAF', 'weight' => 'KG_CM');
		$value['CH'] = array('region' => 'EU', 'currency' =>'CHF', 'weight' => 'KG_CM');
		$value['CI'] = array('region' => 'AP', 'currency' =>'XOF', 'weight' => 'KG_CM');
		$value['CK'] = array('region' => 'AP', 'currency' =>'NZD', 'weight' => 'KG_CM');
		$value['CL'] = array('region' => 'AM', 'currency' =>'CLP', 'weight' => 'KG_CM');
		$value['CM'] = array('region' => 'AP', 'currency' =>'XAF', 'weight' => 'KG_CM');
		$value['CN'] = array('region' => 'AP', 'currency' =>'CNY', 'weight' => 'KG_CM');
		$value['CO'] = array('region' => 'AM', 'currency' =>'COP', 'weight' => 'KG_CM');
		$value['CR'] = array('region' => 'AM', 'currency' =>'CRC', 'weight' => 'KG_CM');
		$value['CU'] = array('region' => 'AM', 'currency' =>'CUC', 'weight' => 'KG_CM');
		$value['CV'] = array('region' => 'AP', 'currency' =>'CVE', 'weight' => 'KG_CM');
		$value['CY'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['CZ'] = array('region' => 'EU', 'currency' =>'CZF', 'weight' => 'KG_CM');
		$value['DE'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['DJ'] = array('region' => 'EU', 'currency' =>'DJF', 'weight' => 'KG_CM');
		$value['DK'] = array('region' => 'AM', 'currency' =>'DKK', 'weight' => 'KG_CM');
		$value['DM'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'LB_IN');
		$value['DO'] = array('region' => 'AP', 'currency' =>'DOP', 'weight' => 'LB_IN');
		$value['DZ'] = array('region' => 'AM', 'currency' =>'DZD', 'weight' => 'KG_CM');
		$value['EC'] = array('region' => 'EU', 'currency' =>'USD', 'weight' => 'KG_CM');
		$value['EE'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['EG'] = array('region' => 'AP', 'currency' =>'EGP', 'weight' => 'KG_CM');
		$value['ER'] = array('region' => 'EU', 'currency' =>'ERN', 'weight' => 'KG_CM');
		$value['ES'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['ET'] = array('region' => 'AU', 'currency' =>'ETB', 'weight' => 'KG_CM');
		$value['FI'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['FJ'] = array('region' => 'AP', 'currency' =>'FJD', 'weight' => 'KG_CM');
		$value['FK'] = array('region' => 'AM', 'currency' =>'GBP', 'weight' => 'KG_CM');
		$value['FM'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['FO'] = array('region' => 'AM', 'currency' =>'DKK', 'weight' => 'KG_CM');
		$value['FR'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['GA'] = array('region' => 'AP', 'currency' =>'XAF', 'weight' => 'KG_CM');
		$value['GB'] = array('region' => 'EU', 'currency' =>'GBP', 'weight' => 'KG_CM');
		$value['GD'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'LB_IN');
		$value['GE'] = array('region' => 'AM', 'currency' =>'GEL', 'weight' => 'KG_CM');
		$value['GF'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['GG'] = array('region' => 'AM', 'currency' =>'GBP', 'weight' => 'KG_CM');
		$value['GH'] = array('region' => 'AP', 'currency' =>'GBS', 'weight' => 'KG_CM');
		$value['GI'] = array('region' => 'AM', 'currency' =>'GBP', 'weight' => 'KG_CM');
		$value['GL'] = array('region' => 'AM', 'currency' =>'DKK', 'weight' => 'KG_CM');
		$value['GM'] = array('region' => 'AP', 'currency' =>'GMD', 'weight' => 'KG_CM');
		$value['GN'] = array('region' => 'AP', 'currency' =>'GNF', 'weight' => 'KG_CM');
		$value['GP'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['GQ'] = array('region' => 'AP', 'currency' =>'XAF', 'weight' => 'KG_CM');
		$value['GR'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['GT'] = array('region' => 'AM', 'currency' =>'GTQ', 'weight' => 'KG_CM');
		$value['GU'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['GW'] = array('region' => 'AP', 'currency' =>'XOF', 'weight' => 'KG_CM');
		$value['GY'] = array('region' => 'AP', 'currency' =>'GYD', 'weight' => 'LB_IN');
		$value['HK'] = array('region' => 'AM', 'currency' =>'HKD', 'weight' => 'KG_CM');
		$value['HN'] = array('region' => 'AM', 'currency' =>'HNL', 'weight' => 'KG_CM');
		$value['HR'] = array('region' => 'AP', 'currency' =>'HRK', 'weight' => 'KG_CM');
		$value['HT'] = array('region' => 'AM', 'currency' =>'HTG', 'weight' => 'LB_IN');
		$value['HU'] = array('region' => 'EU', 'currency' =>'HUF', 'weight' => 'KG_CM');
		$value['IC'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['ID'] = array('region' => 'AP', 'currency' =>'IDR', 'weight' => 'KG_CM');
		$value['IE'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['IL'] = array('region' => 'AP', 'currency' =>'ILS', 'weight' => 'KG_CM');
		$value['IN'] = array('region' => 'AP', 'currency' =>'INR', 'weight' => 'KG_CM');
		$value['IQ'] = array('region' => 'AP', 'currency' =>'IQD', 'weight' => 'KG_CM');
		$value['IR'] = array('region' => 'AP', 'currency' =>'IRR', 'weight' => 'KG_CM');
		$value['IS'] = array('region' => 'EU', 'currency' =>'ISK', 'weight' => 'KG_CM');
		$value['IT'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['JE'] = array('region' => 'AM', 'currency' =>'GBP', 'weight' => 'KG_CM');
		$value['JM'] = array('region' => 'AM', 'currency' =>'JMD', 'weight' => 'KG_CM');
		$value['JO'] = array('region' => 'AP', 'currency' =>'JOD', 'weight' => 'KG_CM');
		$value['JP'] = array('region' => 'AP', 'currency' =>'JPY', 'weight' => 'KG_CM');
		$value['KE'] = array('region' => 'AP', 'currency' =>'KES', 'weight' => 'KG_CM');
		$value['KG'] = array('region' => 'AP', 'currency' =>'KGS', 'weight' => 'KG_CM');
		$value['KH'] = array('region' => 'AP', 'currency' =>'KHR', 'weight' => 'KG_CM');
		$value['KI'] = array('region' => 'AP', 'currency' =>'AUD', 'weight' => 'KG_CM');
		$value['KM'] = array('region' => 'AP', 'currency' =>'KMF', 'weight' => 'KG_CM');
		$value['KN'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'LB_IN');
		$value['KP'] = array('region' => 'AP', 'currency' =>'KPW', 'weight' => 'LB_IN');
		$value['KR'] = array('region' => 'AP', 'currency' =>'KRW', 'weight' => 'KG_CM');
		$value['KV'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['KW'] = array('region' => 'AP', 'currency' =>'KWD', 'weight' => 'KG_CM');
		$value['KY'] = array('region' => 'AM', 'currency' =>'KYD', 'weight' => 'KG_CM');
		$value['KZ'] = array('region' => 'AP', 'currency' =>'KZF', 'weight' => 'LB_IN');
		$value['LA'] = array('region' => 'AP', 'currency' =>'LAK', 'weight' => 'KG_CM');
		$value['LB'] = array('region' => 'AP', 'currency' =>'USD', 'weight' => 'KG_CM');
		$value['LC'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'KG_CM');
		$value['LI'] = array('region' => 'AM', 'currency' =>'CHF', 'weight' => 'LB_IN');
		$value['LK'] = array('region' => 'AP', 'currency' =>'LKR', 'weight' => 'KG_CM');
		$value['LR'] = array('region' => 'AP', 'currency' =>'LRD', 'weight' => 'KG_CM');
		$value['LS'] = array('region' => 'AP', 'currency' =>'LSL', 'weight' => 'KG_CM');
		$value['LT'] = array('region' => 'EU', 'currency' =>'LTL', 'weight' => 'KG_CM');
		$value['LU'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['LV'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['LY'] = array('region' => 'AP', 'currency' =>'LYD', 'weight' => 'KG_CM');
		$value['MA'] = array('region' => 'AP', 'currency' =>'MAD', 'weight' => 'KG_CM');
		$value['MC'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['MD'] = array('region' => 'AP', 'currency' =>'MDL', 'weight' => 'KG_CM');
		$value['ME'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['MG'] = array('region' => 'AP', 'currency' =>'MGA', 'weight' => 'KG_CM');
		$value['MH'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['MK'] = array('region' => 'AP', 'currency' =>'MKD', 'weight' => 'KG_CM');
		$value['ML'] = array('region' => 'AP', 'currency' =>'COF', 'weight' => 'KG_CM');
		$value['MM'] = array('region' => 'AP', 'currency' =>'USD', 'weight' => 'KG_CM');
		$value['MN'] = array('region' => 'AP', 'currency' =>'MNT', 'weight' => 'KG_CM');
		$value['MO'] = array('region' => 'AP', 'currency' =>'MOP', 'weight' => 'KG_CM');
		$value['MP'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['MQ'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['MR'] = array('region' => 'AP', 'currency' =>'MRO', 'weight' => 'KG_CM');
		$value['MS'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'LB_IN');
		$value['MT'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['MU'] = array('region' => 'AP', 'currency' =>'MUR', 'weight' => 'KG_CM');
		$value['MV'] = array('region' => 'AP', 'currency' =>'MVR', 'weight' => 'KG_CM');
		$value['MW'] = array('region' => 'AP', 'currency' =>'MWK', 'weight' => 'KG_CM');
		$value['MX'] = array('region' => 'AM', 'currency' =>'MXN', 'weight' => 'KG_CM');
		$value['MY'] = array('region' => 'AP', 'currency' =>'MYR', 'weight' => 'KG_CM');
		$value['MZ'] = array('region' => 'AP', 'currency' =>'MZN', 'weight' => 'KG_CM');
		$value['NA'] = array('region' => 'AP', 'currency' =>'NAD', 'weight' => 'KG_CM');
		$value['NC'] = array('region' => 'AP', 'currency' =>'XPF', 'weight' => 'KG_CM');
		$value['NE'] = array('region' => 'AP', 'currency' =>'XOF', 'weight' => 'KG_CM');
		$value['NG'] = array('region' => 'AP', 'currency' =>'NGN', 'weight' => 'KG_CM');
		$value['NI'] = array('region' => 'AM', 'currency' =>'NIO', 'weight' => 'KG_CM');
		$value['NL'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['NO'] = array('region' => 'EU', 'currency' =>'NOK', 'weight' => 'KG_CM');
		$value['NP'] = array('region' => 'AP', 'currency' =>'NPR', 'weight' => 'KG_CM');
		$value['NR'] = array('region' => 'AP', 'currency' =>'AUD', 'weight' => 'KG_CM');
		$value['NU'] = array('region' => 'AP', 'currency' =>'NZD', 'weight' => 'KG_CM');
		$value['NZ'] = array('region' => 'AP', 'currency' =>'NZD', 'weight' => 'KG_CM');
		$value['OM'] = array('region' => 'AP', 'currency' =>'OMR', 'weight' => 'KG_CM');
		$value['PA'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'KG_CM');
		$value['PE'] = array('region' => 'AM', 'currency' =>'PEN', 'weight' => 'KG_CM');
		$value['PF'] = array('region' => 'AP', 'currency' =>'XPF', 'weight' => 'KG_CM');
		$value['PG'] = array('region' => 'AP', 'currency' =>'PGK', 'weight' => 'KG_CM');
		$value['PH'] = array('region' => 'AP', 'currency' =>'PHP', 'weight' => 'KG_CM');
		$value['PK'] = array('region' => 'AP', 'currency' =>'PKR', 'weight' => 'KG_CM');
		$value['PL'] = array('region' => 'EU', 'currency' =>'PLN', 'weight' => 'KG_CM');
		$value['PR'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['PT'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['PW'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'KG_CM');
		$value['PY'] = array('region' => 'AM', 'currency' =>'PYG', 'weight' => 'KG_CM');
		$value['QA'] = array('region' => 'AP', 'currency' =>'QAR', 'weight' => 'KG_CM');
		$value['RE'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['RO'] = array('region' => 'EU', 'currency' =>'RON', 'weight' => 'KG_CM');
		$value['RS'] = array('region' => 'AP', 'currency' =>'RSD', 'weight' => 'KG_CM');
		$value['RU'] = array('region' => 'AP', 'currency' =>'RUB', 'weight' => 'KG_CM');
		$value['RW'] = array('region' => 'AP', 'currency' =>'RWF', 'weight' => 'KG_CM');
		$value['SA'] = array('region' => 'AP', 'currency' =>'SAR', 'weight' => 'KG_CM');
		$value['SB'] = array('region' => 'AP', 'currency' =>'SBD', 'weight' => 'KG_CM');
		$value['SC'] = array('region' => 'AP', 'currency' =>'SCR', 'weight' => 'KG_CM');
		$value['SD'] = array('region' => 'AP', 'currency' =>'SDG', 'weight' => 'KG_CM');
		$value['SE'] = array('region' => 'EU', 'currency' =>'SEK', 'weight' => 'KG_CM');
		$value['SG'] = array('region' => 'AP', 'currency' =>'SGD', 'weight' => 'KG_CM');
		$value['SH'] = array('region' => 'AP', 'currency' =>'SHP', 'weight' => 'KG_CM');
		$value['SI'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['SK'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['SL'] = array('region' => 'AP', 'currency' =>'SLL', 'weight' => 'KG_CM');
		$value['SM'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['SN'] = array('region' => 'AP', 'currency' =>'XOF', 'weight' => 'KG_CM');
		$value['SO'] = array('region' => 'AM', 'currency' =>'SOS', 'weight' => 'KG_CM');
		$value['SR'] = array('region' => 'AM', 'currency' =>'SRD', 'weight' => 'KG_CM');
		$value['SS'] = array('region' => 'AP', 'currency' =>'SSP', 'weight' => 'KG_CM');
		$value['ST'] = array('region' => 'AP', 'currency' =>'STD', 'weight' => 'KG_CM');
		$value['SV'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'KG_CM');
		$value['SY'] = array('region' => 'AP', 'currency' =>'SYP', 'weight' => 'KG_CM');
		$value['SZ'] = array('region' => 'AP', 'currency' =>'SZL', 'weight' => 'KG_CM');
		$value['TC'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['TD'] = array('region' => 'AP', 'currency' =>'XAF', 'weight' => 'KG_CM');
		$value['TG'] = array('region' => 'AP', 'currency' =>'XOF', 'weight' => 'KG_CM');
		$value['TH'] = array('region' => 'AP', 'currency' =>'THB', 'weight' => 'KG_CM');
		$value['TJ'] = array('region' => 'AP', 'currency' =>'TJS', 'weight' => 'KG_CM');
		$value['TL'] = array('region' => 'AP', 'currency' =>'USD', 'weight' => 'KG_CM');
		$value['TN'] = array('region' => 'AP', 'currency' =>'TND', 'weight' => 'KG_CM');
		$value['TO'] = array('region' => 'AP', 'currency' =>'TOP', 'weight' => 'KG_CM');
		$value['TR'] = array('region' => 'AP', 'currency' =>'TRY', 'weight' => 'KG_CM');
		$value['TT'] = array('region' => 'AM', 'currency' =>'TTD', 'weight' => 'LB_IN');
		$value['TV'] = array('region' => 'AP', 'currency' =>'AUD', 'weight' => 'KG_CM');
		$value['TW'] = array('region' => 'AP', 'currency' =>'TWD', 'weight' => 'KG_CM');
		$value['TZ'] = array('region' => 'AP', 'currency' =>'TZS', 'weight' => 'KG_CM');
		$value['UA'] = array('region' => 'AP', 'currency' =>'UAH', 'weight' => 'KG_CM');
		$value['UG'] = array('region' => 'AP', 'currency' =>'USD', 'weight' => 'KG_CM');
		$value['US'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['UY'] = array('region' => 'AM', 'currency' =>'UYU', 'weight' => 'KG_CM');
		$value['UZ'] = array('region' => 'AP', 'currency' =>'UZS', 'weight' => 'KG_CM');
		$value['VC'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'LB_IN');
		$value['VE'] = array('region' => 'AM', 'currency' =>'VEF', 'weight' => 'KG_CM');
		$value['VG'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['VI'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['VN'] = array('region' => 'AP', 'currency' =>'VND', 'weight' => 'KG_CM');
		$value['VU'] = array('region' => 'AP', 'currency' =>'VUV', 'weight' => 'KG_CM');
		$value['WS'] = array('region' => 'AP', 'currency' =>'WST', 'weight' => 'KG_CM');
		$value['XB'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'LB_IN');
		$value['XC'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'LB_IN');
		$value['XE'] = array('region' => 'AM', 'currency' =>'ANG', 'weight' => 'LB_IN');
		$value['XM'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'LB_IN');
		$value['XN'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'LB_IN');
		$value['XS'] = array('region' => 'AP', 'currency' =>'SIS', 'weight' => 'KG_CM');
		$value['XY'] = array('region' => 'AM', 'currency' =>'ANG', 'weight' => 'LB_IN');
		$value['YE'] = array('region' => 'AP', 'currency' =>'YER', 'weight' => 'KG_CM');
		$value['YT'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['ZA'] = array('region' => 'AP', 'currency' =>'ZAR', 'weight' => 'KG_CM');
		$value['ZM'] = array('region' => 'AP', 'currency' =>'ZMW', 'weight' => 'KG_CM');
		$value['ZW'] = array('region' => 'AP', 'currency' =>'USD', 'weight' => 'KG_CM');

		return $value;
	}

	public function validate()
    {
        if (!isset($this->request->get['target'])) {
            echo '{error: "Error"}';
            return;
        }

        $target = $this->request->get['target'];

        $postData = $this->request->post;

        $this->initializer([
			'sale/order',
			'module/signup'
		]);

		$custom_registration_app = $this->model_module_signup->isActiveMod();

		if($custom_registration_app)
			$postData['register_login_by_phone_number'] =$this->model_module_signup->isLoginRegisterByPhonenumber();

        $response = [
            'hasErrors' => false,
        ];

        if ($target == 'personal') {
			$order = $this->model_sale_order->getOrder( $this->request->get['order_id'] );
            $response = $this->order->validatePersonal($postData, $order);
        } else if ($target == 'payment') {
            $response = $this->order->validatePayment($postData);
        } else if ($target == 'shipping') {
            $response = $this->order->validateShipping($postData);
        } else if ($target == 'product') {
            $response = $this->order->validateProduct($postData);
        } else if ($target == 'vouchers') {
            $response =  ['hasErrors' => false];
        }

        $this->response->setOutput(json_encode($response));
        return;
    }

    function getAllCustomers(){
        if (isset($this->request->get['filter_name'])) {
            if (isset($this->request->get['filter_name'])) {
                $filter_name = $this->request->get['filter_name'];
            } else {
                $filter_name = '';
            }

            if (isset($this->request->get['limit'])) {
                $limit = $this->request->get['limit'];
            } else {
                $limit = 20;
            }

            $data = array(
                'filter_name' => $filter_name,
                'start' => 0,
                'limit' => $limit
            );

            $json = array();
            $this->load->model('sale/customer');
            $customers = $this->model_sale_customer->getCustomerByName($data);

            foreach ($customers as $customer) {
                $json[] = array(
                    'customer_id' => $customer['customer_id'],
                    'name' => strip_tags(html_entity_decode($customer['name'], ENT_QUOTES, 'UTF-8')),
                );
            }
            $this->response->setOutput(json_encode($json));
        }
	}

	private function hideCountryCode($telephone, $countryId, $optionEnabled) {
		if ($optionEnabled == 1) {
			$this->load->model('localisation/country');
			$countryCode = $this->model_localisation_country->getCountryData($countryId)['phonecode'];
			$replaceStr = '+' . $countryCode;
			if (strpos($telephone, $replaceStr) !== false) {
				$telephone = str_replace($replaceStr, '', $telephone);
			} elseif (substr($telephone, 0, 2) === '00') {
				$telephone = substr_replace($telephone, '', 0, 2);
			}
		}
		return $telephone;
	}

	public function getSlots()
	{
		$this->load->model('module/delivery_slot/slots');
		$slots =  $this->model_module_delivery_slot_slots->getSlotsByDayId( $this->request->post['day_id'] );
		$this->response->setOutput(json_encode( $slots ));
	}

	public function hotEdit()
	{
		$this->load->model('sale/order');
		$order_id = $this->request->post['order_id'];
		unset($this->request->post['order_id']);
		$this->model_sale_order->hotEdit($this->request->post, $order_id);
	}

	/**
	 * Change order product warehouse
	 *  */
	public function changeProductWarehouse()
    {
        $status = 0;
        if(isset($this->request->post) && $this->request->post['wr_id'] && $this->request->post['wr_prid'] && $this->request->post['wr_orid']){
            $this->load->model('module/warehouses');
            $status = $this->model_module_warehouses->changeProductWarehouse($this->request->post);
        }

        $json = ['success' => $status];
        $this->response->setOutput(json_encode($json));
    }

    public function updateCustomerInfo()
    {
        if (!isset($this->request->post['order_id'])) {
            return $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_REQUEST',
                'errors' => ['Invalid request']
            ]));
        }

        $orderId = $this->request->post['order_id'];

        if (preg_match('#^[0-9]+$#', $orderId) == false) {
            return $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNAUTHORIZED_ACTION',
                'errors' => ['Unauthorized action #2']
            ]));
        }

        $this->initializer(['sale/order']);

        $order = $this->order->getOrder($orderId);

        if (!$order) {
            return $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNAUTHORIZED_ACTION',
                'errors' => ['Unauthorized action #2']
            ]));
        }

        $update = $this->order->updateCustomerInfo($orderId, $this->request->post['customer']);

        if ($update) {
            return $this->response->setOutput(json_encode([
                'status' => 'OK',
            ]));
        }

        return $this->response->setOutput(json_encode([
            'status' => 'ERR',
            'error' => 'UNEXPECTED_ERROR',
            'errors' => ['Unexpected error']
        ]));
    }

    public function updateCustomerAddresses()
    {
        if (!isset($this->request->post['order_id'])) {
            return $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_REQUEST',
                'errors' => ['Invalid request']
            ]));
        }

        $orderId = $this->request->post['order_id'];

        if (preg_match('#^[0-9]+$#', $orderId) == false) {
            return $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNAUTHORIZED_ACTION',
                'errors' => ['Unauthorized action #2']
            ]));
        }

        $this->initializer(['sale/order']);

        $order = $this->order->getOrder($orderId);

        if (!$order) {
            return $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNAUTHORIZED_ACTION',
                'errors' => ['Unauthorized action #2']
            ]));
        }

        $update = $this->order->updateCustomerAddresses($orderId, $this->request->post['customer']);

        if ($update) {
            return $this->response->setOutput(json_encode([
                'status' => 'OK',
            ]));
        }

        return $this->response->setOutput(json_encode([
            'status' => 'ERR',
            'error' => 'UNEXPECTED_ERROR',
            'errors' => ['Unexpected error']
        ]));
    }

    public function updateOrderGateway()
    {
        if (
            isset($this->request->post['id']) == false ||
            preg_match('#^[0-9]+$#', $this->request->post['id']) == false
        ) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_REQUEST',
                'errors' => ['Invalid request'],
            ]));

            return;
        }

        if (
            isset($this->request->post['order_id']) == false ||
            preg_match('#^[0-9]+$#', $this->request->post['order_id']) == false
        ) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_REQUEST',
                'errors' => ['Invalid request'],
            ]));

            return;
        }

        $id = $this->request->post['id'];
        $orderId = $this->request->post['order_id'];
        $isBundled = $this->request->post['bundled'];

        $this->initializer(['msgOrder' => 'module/manual_shipping/order']);

        /*if ($isBundled == 1) {
            $gatewayTitle = $this->request->post['gateway_title'];
            $gateWaycode = $this->request->post['code'];
            $update = $this->msgOrder->updateOrderBundledGateway($orderId, $gatewayTitle, $gateWaycode);
        } else {
            $update = $this->msgOrder->updateOrderManualGateway($orderId, $id);
        }*/

        $update = $this->msgOrder->updateOrderManualGateway($orderId, $id, $isBundled);

        if ($update) {
            $this->response->setOutput(json_encode([
                'status' => 'OK',
            ]));

            return;
        }

        $this->response->setOutput(json_encode([
            'status' => 'ERR',
            'error' => 'UNEXPECTED_ERROR',
            'errors' => ['Unexpected error'],
        ]));
    }
    public function assignOrder()
    {
        $orderId = $this->request->post['order_id'];
        $orderId_s = is_array($orderId) ? $orderId : [$orderId];
        $user_id = $this->request->post['user_id'];
        $this->load->model('sale/order');

        if ( isset($user_id) == false || preg_match('#^[0-9]+$#', $user_id) == false)
            {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_REQUEST',
                'errors' => ['Invalid request'],
            ]));
            return;
            }

        if (isset($orderId) == false ) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_REQUEST',
                'errors' => ['Invalid request'],
            ]));
            return;
        }
        foreach ($orderId_s as $order_id)
        {
        $this->model_sale_order->assignOrder($user_id,$order_id);
        }

       return $this->response->setOutput(json_encode([
        'status' => 'OK',
    ]));
    }

    /**
     * Update order delivery slot data
     * @return mixed
     *
     */
    public function updateOrderDeliverySlot()
    {
        if (
                (!isset($this->request->post['order_id']) || ($this->request->post['order_id'] && preg_match('#^[0-9]+$#', $this->request->post['order_id']) == false))
                || !$this->request->post['delivery_slot_date']
                || !$this->request->post['delivery_slot']
                || !$this->request->post['slot_order_id']
          ) {
            return $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_REQUEST',
                'errors' => ['Missing data']
            ]));
        }

        $orderId = $this->request->post['order_id'];

        $this->initializer(['sale/order']);

        $order = $this->order->getOrder($orderId);

        if (!$order) {
            return $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNAUTHORIZED_ACTION',
                'errors' => ['Order not found']
            ]));
        }

        $this->language->load('module/delivery_slot');

        $data = [
                  'order_id' => $orderId,
                  'slot_date' => $this->request->post['delivery_slot_date'],
                  'slot_date_dmy_format' => DateTime::createFromFormat('m-d-Y', $this->request->post['delivery_slot_date'], new DateTimeZone($this->config->get('config_timezone') ?? 'UTC'))->format('Y-m-d'),
                  'slot_id' => $this->request->post['delivery_slot'],
                  'slot_order_id' => $this->request->post['slot_order_id'],
                  'days' => [
                      $this->language->get('entry_sunday'),
                      $this->language->get('entry_monday'),
                      $this->language->get('entry_tuesday'),
                      $this->language->get('entry_wednesday'),
                      $this->language->get('entry_thursday'),
                      $this->language->get('entry_friday'),
                      $this->language->get('entry_saturday')
                  ]
                ];
        $this->load->model('module/delivery_slot/slots');
        $update = $this->model_module_delivery_slot_slots->updateOrderSlot($data);

        if ($update) {
            return $this->response->setOutput(json_encode([
                'status' => 'OK',
            ]));
        }

        return $this->response->setOutput(json_encode([
            'status' => 'ERR',
            'error' => 'UNEXPECTED_ERROR',
            'errors' => ['Unexpected error']
        ]));
    }

    /**
     * delete order delivery slot
     * @return mixed
     *
     */
    public function deleteOrderslot()
    {
        if (!isset($this->request->post['order_id']) || !isset($this->request->post['slot_order_id'])) {
            return $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_REQUEST',
                'errors' => ['Invalid request']
            ]));
        }

        $data = [
                    'order_id' => $this->request->post['order_id'],
                    'slot_order_id' => $this->request->post['slot_order_id'],
                ];

        $this->load->model('module/delivery_slot/slots');
        $delete = $this->model_module_delivery_slot_slots->deleteOrderSlot($data);

        if ($delete) {
            return $this->response->setOutput(json_encode([
                'status' => 'OK',
            ]));
        }

        return $this->response->setOutput(json_encode([
            'status' => 'ERR',
            'error' => 'UNEXPECTED_ERROR',
            'errors' => ['Unexpected error']
        ]));
    }




    private function _getTapPaymentFields($orders){
        foreach ($orders as &$order){
            $order['receipt_id']     = '';
            $order['payment_amount'] = '';
            if(!empty($order['transaction_id']) && $order['payment_code'] == 'tap'){

                $payment_row = $this->db->query("SELECT details
                    FROM `" . DB_PREFIX . "payment_transactions`
                    WHERE order_id=".(int)$order['order_id']."
                    AND transaction_id=".(int)$order['transaction_id']."
                    AND status='Success'")->row['details'];

                if(!empty($payment_row)){
                    //Decode json response as array
                    $payment_row = json_decode($payment_row, true);
                    $order['receipt_id']     = $payment_row['payid'];
                    $order['payment_amount'] = $payment_row['amt'];
                }
            }
        }
        return $orders;
    }

    public function updatePaymentMethodsList() {

        $this->load->model('sale/order');
        //this array is get payment methods based on default country and zone
        $zonePaymentMethodsArray = [
            'country_id' => $this->request->post['country_id'],
            'zone_id' => $this->request->post['zone_id']
        ];

        $paymentMethods = $this->model_sale_order->getPaymentMethods($zonePaymentMethodsArray);


        return $this->response->setOutput(json_encode(array_values($paymentMethods)));
    }

    public function getUserOrderStatuses($currentOrderStatusID)
    {
        //User Group Order Statuses
        $this->load->model('user/user');
        $this->load->model('user/user_group');
        $this->load->model('localisation/order_status');
        $user_info = $this->model_user_user->getUser($this->user->getID());
        $user_group_id=$user_info['user_group_id'];
        // In Case the group has Permission to modify or to change order status
            if($this->user->hasPermission('modify', 'sale/order') || $this->user->hasPermission('custom', 'order_change_status') )
            {
                $userGroupOrderStatuses = $this->model_user_user_group->getUserGroupOrderStatuses($user_group_id);
                /// Get custom order statuses allowed for this group
                if($userGroupOrderStatuses)
                {
                    $order_statusesResults = $this->model_user_user_group->getCustomGroupOrderStatuses($user_group_id,$currentOrderStatusID);
                }
                //// if there are no cusom statuses get all order_statuses
                else
                {
                    $order_statusesResults = $this->model_localisation_order_status->getOrderStatuses();
                }
            }
            return $order_statusesResults;
    }

    /**
     *  return the new statuses list after update order
     *  will return custom or all order statues
     *
     *  @return string
     */
    public function userOrderStatuses()
    {

        $currentOrderStatusId = (int) ($this->request->post['current_order_status_id'] ?? 0);

        if (!empty($currentOrderStatusId) && is_int($currentOrderStatusId)){
            $newStatuses = $this->getUserOrderStatuses($this->request->post['current_order_status_id']);


            return $this->response->json($newStatuses);
        }

        return $this->response->setOutput(json_encode([
            'status' => 'ERR',
            'error' => 'INVALID_REQUEST',
            'errors' => ['Missing data']
        ]));

    }
    public function groupProducts($products, $groupBy)
    {
        foreach ($products as $prd) {
            if(is_array($prd[$groupBy])){
                $finalProducts[$prd[$groupBy][$groupBy . '_id']][] = $prd;
                continue;
            }
            $finalProducts[$prd[$groupBy]][] = $prd;
        }
        return $finalProducts;
    }

    /**
     * @param $str
     * @return string
     */
    public function getCodeFromTotalTitleString($str){
        preg_match('#\((.*?)\)#',  $str, $match);
        $replace = '';
        if ($match[1])
            $replace =  str_replace(['(' , ')'] ,'' ,trim($match[1]));
        return $replace;
    }

    private function _isHardActivationCompleted(): ?bool
    {
        return ($this->config->get($this->userActivation::HARD_ACTIVATION));
    }

    private function _setHardActivation(?bool $val = true, ?bool $redirectParam = true): void
    {
        $this->userActivation->setHardActivation($val, $redirectParam);
    }


}
