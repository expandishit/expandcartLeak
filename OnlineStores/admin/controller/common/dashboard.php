<?php

class ControllerCommonDashboard extends Controller
{
	public function index() {

	    //Check if the user is in trial, show the trial view
        //else, show the dashboard

        $this->load->model('setting/setting');
        $skip_trial = $this->model_setting_setting->getGuideValue("GETTING_STARTED")['SKIP_TRIAL'];
        $signup = $this->model_setting_setting->getGuideValue("SIGNUP");

        if ((!isset($signup['QUESTIONER']) || $signup['QUESTIONER'] == 0) && PRODUCTID ==3) {
            return $this->redirect($this->url->link('common/installation', '', 'SSL'));
        }

        if(PRODUCTID == 3 && $skip_trial != 1 && !isset($this->session->data['charge'])){
            return $this->redirect($this->url->link('common/home','', 'SSL'));
        }

        if (isset($this->session->data['redirect_route'])){
            $route = $this->session->data['redirect_route'];
            unset($this->session->data['redirect_route']);
            return $this->redirect($this->url->link($route));
        }

        else{
            if ($this->user->isCODCollector()) {
                return $this->redirect($this->url->link('sale/collection', '', 'SSL'));
            }
    
            $this->language->load('common/home');
         
            $this->document->setTitle($this->language->get('heading_title'));
            
            $this->data['heading_title'] = $this->language->get('heading_title');
    
            $this->data['direction'] = $this->language->get('direction');
    
            $this->data['text_overview'] = $this->language->get('text_overview');
            $this->data['text_statistics'] = $this->language->get('text_statistics');
            $this->data['text_latest_10_orders'] = $this->language->get('text_latest_10_orders');
            $this->data['text_total_sale'] = $this->language->get('text_total_sale');
            $this->data['text_total_sale_year'] = $this->language->get('text_total_sale_year');
            $this->data['text_total_order'] = $this->language->get('text_total_order');
            $this->data['text_total_customer'] = $this->language->get('text_total_customer');
            $this->data['text_total_customer_approval'] = $this->language->get('text_total_customer_approval');
            $this->data['text_total_review_approval'] = $this->language->get('text_total_review_approval');
            $this->data['text_total_affiliate'] = $this->language->get('text_total_affiliate');
            $this->data['text_total_affiliate_approval'] = $this->language->get('text_total_affiliate_approval');
            $this->data['text_day'] = $this->language->get('text_day');
            $this->data['text_week'] = $this->language->get('text_week');
            $this->data['text_month'] = $this->language->get('text_month');
            $this->data['text_year'] = $this->language->get('text_year');
            $this->data['text_no_results'] = $this->language->get('text_no_results');
            $this->data['text_order'] = $this->language->get('text_order');
            $this->data['text_sales_earnings'] = $this->language->get('text_sales_earnings');
            $this->data['text_orders'] = $this->language->get('text_orders');
            $this->data['text_customers'] = $this->language->get('text_customers');
            $this->data['text_earnings'] = $this->language->get('text_earnings');
            $this->data['text_view_all_orders'] = $this->language->get('text_view_all_orders');
            $this->data['text_orderID_grid'] = $this->language->get('text_orderID_grid');
            $this->data['text_Date_grid'] = $this->language->get('text_Date_grid');
            $this->data['text_customer_grid'] = $this->language->get('text_customer_grid');
            $this->data['text_status_grid'] = $this->language->get('text_status_grid');
            $this->data['text_price_grid'] = $this->language->get('text_price_grid');
            $this->data['text_welcome'] = $this->language->get('text_welcome');
            $this->data['text_welcome_sub'] = $this->language->get('text_welcome_sub');
            $this->data['text_edit_settings'] = $this->language->get('text_edit_settings');
            $this->data['text_edit_settings_sub'] = $this->language->get('text_edit_settings_sub');
            $this->data['text_edit_settings_btn'] = $this->language->get('text_edit_settings_btn');
            $this->data['text_customize_design'] = $this->language->get('text_customize_design');
            $this->data['text_customize_design_sub'] = $this->language->get('text_customize_design_sub');
            $this->data['text_customize_design_btn'] = $this->language->get('text_customize_design_btn');
            $this->data['text_add_product'] = $this->language->get('text_add_product');
            $this->data['text_add_product_sub'] = $this->language->get('text_add_product_sub');
            $this->data['text_add_product_btn'] = $this->language->get('text_add_product_btn');
            $this->data['text_skip_tutorial'] = $this->language->get('text_skip_tutorial');
            $this->data['text_upgrade_domain'] = $this->language->get('text_upgrade_domain');
            $this->data['text_upgrade_domain_sub'] = $this->language->get('text_upgrade_domain_sub');
            $this->data['text_upgrade_domain_btn'] = $this->language->get('text_upgrade_domain_btn');
    
            $this->data['column_order'] = $this->language->get('column_order');
            $this->data['column_customer'] = $this->language->get('column_customer');
            $this->data['column_status'] = $this->language->get('column_status');
            $this->data['column_date_added'] = $this->language->get('column_date_added');
            $this->data['column_total'] = $this->language->get('column_total');
            $this->data['column_firstname'] = $this->language->get('column_firstname');
            $this->data['column_lastname'] = $this->language->get('column_lastname');
            $this->data['column_action'] = $this->language->get('column_action');
            
            $this->data['entry_range'] = $this->language->get('entry_range');
            
            $this->data['title_no_customer_exists'] = $this->language->get('title_no_customer_exists');
    
            // Check install directory exists
             if (is_dir(dirname(DIR_APPLICATION) . '/install')) {
                $this->data['error_install'] = $this->language->get('error_install');
            } else {
                $this->data['error_install'] = '';
            }
    
            if (is_writable(DIR_IMAGE)) {
                // Check image directory is writable
                $file = DIR_IMAGE . 'test';
    
                $handle = fopen($file, 'a+');
    
                fwrite($handle, '');
    
                fclose($handle);
    
                if (!file_exists($file)) {
                    $this->data['error_image'] = sprintf($this->language->get('error_image'), DIR_IMAGE);
                } else {
                    $this->data['error_image'] = '';
    
                    unlink($file);
                }
            } else {
                $this->data['error_image'] = sprintf($this->language->get('error_image'), DIR_IMAGE);
            }
    
            if (is_writable(DIR_IMAGE . 'cache/')) {
                // Check image cache directory is writable
                $file = DIR_IMAGE . 'cache/test';
    
                $handle = fopen($file, 'a+');
    
                fwrite($handle, '');
    
                fclose($handle);
    
                if (!file_exists($file)) {
                    $this->data['error_image_cache'] = sprintf(
                        $this->language->get('error_image_cache'),
                        DIR_IMAGE . 'cache/'
                    );
                } else {
                    $this->data['error_image_cache'] = '';
    
                    unlink($file);
                }
            } else {
                $this->data['error_image_cache'] = sprintf($this->language->get('error_image_cache'), DIR_IMAGE . 'cache/');
            }
    
            if (is_writable(DIR_CACHE)) {
                // Check cache directory is writable
                $file = DIR_CACHE . 'test';
    
                $handle = fopen($file, 'a+');
    
                fwrite($handle, '');
    
                fclose($handle);
    
                if (!file_exists($file)) {
                    $this->data['error_cache'] = sprintf($this->language->get('error_image_cache'), DIR_CACHE);
                } else {
                    $this->data['error_cache'] = '';
    
                    unlink($file);
                }
            } else {
                $this->data['error_cache'] = sprintf($this->language->get('error_image_cache'), DIR_CACHE);
            }
    
            if (is_writable(DIR_DOWNLOAD)) {
                // Check download directory is writable
                $file = DIR_DOWNLOAD . 'test';
    
                $handle = fopen($file, 'a+');
    
                fwrite($handle, '');
    
                fclose($handle);
    
                if (!file_exists($file)) {
                    $this->data['error_download'] = sprintf($this->language->get('error_download'), DIR_DOWNLOAD);
                } else {
                    $this->data['error_download'] = '';
    
                    unlink($file);
                }
            } else {
                $this->data['error_download'] = sprintf($this->language->get('error_download'), DIR_DOWNLOAD);
            }
    
            if (is_writable(DIR_LOGS)) {
                // Check logs directory is writable
                $file = DIR_LOGS . 'test';
    
                $handle = fopen($file, 'a+');
    
                fwrite($handle, '');
    
                fclose($handle);
    
                if (!file_exists($file)) {
                    $this->data['error_logs'] = sprintf($this->language->get('error_logs'), DIR_LOGS);
                } else {
                    $this->data['error_logs'] = '';
    
                    unlink($file);
                }
            } else {
                $this->data['error_logs'] = sprintf($this->language->get('error_logs'), DIR_LOGS);
            }
                                            
            $this->data['breadcrumbs'] = array();
    
               $this->data['breadcrumbs'][] = array(
                   'text'      => $this->language->get('text_home'),
                'href'      => $this->url->link('common/home', '', 'SSL'),
                  'separator' => false
               );
    
            $this->data['breadcrumbs'][] = array(
                'text'      => $this->language->get('heading_title'),
                'href'      => false,
                'separator' => false
            );
    
    //		$this->data['token'] = $this->session->data['token'];
    
    //        $this->load->model('setting/setting');
    
    //        $this->load->model('billingaccount/common');
    
    //
    //        $this->data['orders_link'] = $this->url->link('sale/order', 'token=' . $this->session->data['token'], 'SSL');
    //
    //		$this->data['total_sale'] = $this->currency->format($this->model_sale_order->getTotalSales(), $this->config->get('config_currency'));
    //		$this->data['total_sale_year'] = $this->currency->format($this->model_sale_order->getTotalSalesByYear(date('Y')), $this->config->get('config_currency'));
    //		$this->data['total_order'] = $this->model_sale_order->getTotalOrders();
    //        $this->data['total_order_year'] = $this->model_sale_order->getTotalOrders(array('filter_date_added_greater_equal' => date('Y-01-01 00:00:00')));
    //
    //		$this->load->model('sale/customer');
    //
    //		$this->data['total_customer'] = $this->model_sale_customer->getTotalCustomers();
    //		$this->data['total_customer_approval'] = $this->model_sale_customer->getTotalCustomersAwaitingApproval();
    //        $this->data['total_customer_year'] = $this->model_sale_customer->getTotalCustomers(array('filter_date_added_greater_equal' => date('Y-01-01 00:00:00')));
    //
    //		$this->load->model('catalog/review');
    //
    //		$this->data['total_review'] = $this->model_catalog_review->getTotalReviews();
    //		$this->data['total_review_approval'] = $this->model_catalog_review->getTotalReviewsAwaitingApproval();
    //
    //		$this->load->model('sale/affiliate');
    //
    //		$this->data['total_affiliate'] = $this->model_sale_affiliate->getTotalAffiliates();
    //		$this->data['total_affiliate_approval'] = $this->model_sale_affiliate->getTotalAffiliatesAwaitingApproval();
    //
    //		$this->data['orders'] = array();
    //
    //		$data = array(
    //			'sort'  => 'o.date_added',
    //			'order' => 'DESC',
    //			'start' => 0,
    //			'limit' => 10
    //		);
    //
    //		$results = $this->model_sale_order->getOrders($data);
    //
    //    	foreach ($results as $result) {
    //
    //			$action = array(
    //				'text' => $this->language->get('text_view'),
    //				'href' => $this->url->link('sale/order/info', 'token=' . $this->session->data['token'] . '&order_id=' . $result['order_id'], 'SSL'),
    //			);
    //
    //            // check if customer exists in customers table.
    //            if ( !empty($this->model_sale_customer->getCustomer($result['customer_id'])) )
    //            {
    //                $action['customer_href'] = $this->url->link('sale/customer/update', 'token=' . $this->session->data['token'] . '&customer_id=' . $result['customer_id'], 'SSL');
    //            }
    //
    //			$this->data['orders'][] = array(
    //				'order_id'   => $result['order_id'],
    //				'customer'   => $result['customer'],
    //				'status'     => $result['status'],
    //				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
    //				'total'      => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
    //				'action'     => $action
    //			);
    //		}
    //
    //		if ($this->config->get('config_currency_auto')) {
    //			$this->load->model('localisation/currency');
    //
    //			$this->model_localisation_currency->updateCurrencies();
    //		}
    //
    //        $this->data['directStoreSettings'] = $this->url->link('setting/setting', 'token=' . $this->session->data['token'], 'SSL');
    //        $this->data['insertProductURL'] = $this->url->link('catalog/product/insert', 'token=' . $this->session->data['token'], 'SSL');
    //        $this->data['changeTemplateURL'] = $this->url->link('setting/template', 'token=' . $this->session->data['token'], 'SSL');
    //        $this->data['upgradeNowURL'] = $this->url->link('billingaccount/plans', 'token=' . $this->session->data['token'], 'SSL');
    
            $this->load->model('report/home');
            $this->load->model('setting/setting');
    
            /*$this->data['revenue'] = array(
                'today' => $this->currency->formatk($this->model_report_home->getSalesRevenueTotal('today'), $this->config->get('config_currency')),
                'yesterday' => $this->currency->formatk($this->model_report_home->getSalesRevenueTotal('yesterday'), $this->config->get('config_currency')),
                '7days' => $this->currency->formatk($this->model_report_home->getSalesRevenueTotal('7days'), $this->config->get('config_currency')),
                '30days' => $this->currency->formatk($this->model_report_home->getSalesRevenueTotal('30days'), $this->config->get('config_currency')),
                'thismonth' => $this->currency->formatk($this->model_report_home->getSalesRevenueTotal('thismonth'), $this->config->get('config_currency')),
                'lastmonth' => $this->currency->formatk($this->model_report_home->getSalesRevenueTotal('lastmonth'), $this->config->get('config_currency'))
            );
    
            $this->data['orders'] = array(
                'today' => $this->model_report_home->getOrdersTotal('today'),
                'yesterday' => $this->model_report_home->getOrdersTotal('yesterday'),
                '7days' => $this->model_report_home->getOrdersTotal('7days'),
                '30days' => $this->model_report_home->getOrdersTotal('30days'),
                'thismonth' => $this->model_report_home->getOrdersTotal('thismonth'),
                'lastmonth' => $this->model_report_home->getOrdersTotal('lastmonth')
            );*/
    
            $this->data['bestseller_products'] = $this->model_report_home->getBestSellerProducts(5);
    
            //$this->data['unhandled_orders_count'] = $this->model_report_home->unhandledOrdersCount();
			if (\Extension::isInstalled('whatsapp')) {
				$whatsapp_chat_connected = (bool)$this->config->get('whatsapp_chat_connected');
				
				if($whatsapp_chat_connected){
					$this->load->model('module/whatsapp');
					$unreaded_messages_count = $this->model_module_whatsapp->unreaded_messages();
					
					$this->data['whatsapp_show'] = true;
					$this->data['unreaded_messages_count'] = $unreaded_messages_count;
				}
			}

            $this->data['flash_message'] = null;
            if (isset($this->session->data['charge'])) {
			
                $this->data['flash_message'] = $this->session->data['charge'];
                unset($this->session->data['charge']);
            }

            $this->data['today_orders_count'] = $this->model_report_home->getOrdersTotal('today');
            $this->data['MENU_TOUR'] = $this->model_setting_setting->getGuideValue("MENU_TOUR")['MENU_TOUR'];

            if ( $this->data['MENU_TOUR'] != 1){
                $this->gettingStarted();
            }
            $this->load->model('catalog/product');
            $this->data['total_products'] = $this->model_catalog_product->getTotalProducts();
            $data['filter_quantity'] = 0;
            $this->data['total_out_of_stock_products'] = $this->model_catalog_product->getTotalProducts($data);
			
			if (\Extension::isInstalled('lableb')) {
				$lableb_settings 	= $this->config->get('lableb');
				$fresh_indexing_v1_1 = $lableb_settings['fresh_indexing_v1_1']??0;
				$this->data['show_lableb_alert'] = $fresh_indexing_v1_1== 0;
			}
			
            $this->template = 'common/dashboard.expand';
            $this->base = 'common/base';
			
            // $this->cardsData();
            $this->response->setOutput($this->render_ecwig());
        }
  	}

  	public function revenueChart() {
        if (isset($this->request->get['startdate']) && isset($this->request->get['enddate'])) {
            $startdate = $this->db->escape($this->request->get['startdate']);
            $enddate = $this->db->escape($this->request->get['enddate']);
        } else {
            $startdate = date("Y-m-d");
            $enddate = $startdate;
        }

        $data = array(
            'x' => array(),
            'y' => array()
        );


        $this->load->model('report/home');

        $begin    = new DateTime($startdate);
        $end      = new DateTime($enddate);
        $datediff = date_diff($begin, $end);

        if($datediff->days == 0) {
            $all = $this->model_report_home->getSalesRevenue($startdate, $enddate, 'hours');
            for ($i = 0; $i < 24; $i++) {
                $key = -1;
                $key = array_search($i, array_column($all, 'hour'));

                if($i == 0) $data['x'][] = "12 AM";
                elseif($i < 13) $data['x'][] = "$i AM";
                else $data['x'][] = ($i-12) . " PM";

                if($key > -1) {
                    $data['y'][] = $all[$key]['total'];
                } else {
                    $data['y'][] = 0;
                }
            }
        } elseif($datediff->days < 31) {
            $all = $this->model_report_home->getSalesRevenue($startdate, $enddate, 'days');
            $interval = DateInterval::createFromDateString('1 day');
            $days     = new DatePeriod($begin, $interval, $end);
            foreach ( $days as $day ) {
                $data['x'][] = $day->format('Y-m-d');
                $y = 0;
                foreach($all as $row) {
                    if($row['date'] == $day->format('Y-m-d')) {
                        $y += $row['total'];
                    }
                }
                $data['y'][] = $y;
            }
        } elseif($datediff->days < 365) {
            $all = $this->model_report_home->getSalesRevenue($startdate, $enddate, 'months');
            $interval = DateInterval::createFromDateString('1 month');
            $months     = new DatePeriod($begin, $interval, $end);
            foreach ( $months as $month ) {
                $data['x'][] = $month->format('Y-m-01');
                $y = 0;
                foreach($all as $row) {
                    if($row['date'] == $month->format('Y-m-01')) {
                        $y += $row['total'];
                    }
                }
                $data['y'][] = $y;
            }
        } else {
            $all = $this->model_report_home->getSalesRevenue($startdate, $enddate, 'years');
            $interval = DateInterval::createFromDateString('1 year');
            $years     = new DatePeriod($begin, $interval, $end);
            foreach ( $years as $year ) {
                $data['x'][] = $year->format('Y');
                $y = 0;
                foreach($all as $row) {
                    if($row['year'] == $year->format('Y')) {
                        $y += $row['total'];
                    }
                }
                $data['y'][] = $y;
            }
        }
        $data['total'] = $this->currency->formatk(array_sum($data['y']), $this->config->get('config_currency'));
        $this->response->setOutput(json_encode($data));
    }

    public function ordersChart() {
        if (isset($this->request->get['startdate']) && isset($this->request->get['enddate'])) {
            $startdate = $this->db->escape($this->request->get['startdate']);
            $enddate = $this->db->escape($this->request->get['enddate']);
        } else {
            $startdate = date("Y-m-d");
            $enddate = $startdate;
        }

        $data = array(
            'x' => array(),
            'y' => array()
        );

        $this->load->model('report/home');

        $begin    = new DateTime($startdate);
        $end      = new DateTime($enddate);
        $datediff = date_diff($begin, $end);

        if($datediff->days == 0) {
            $all = $this->model_report_home->getOrders($startdate, $enddate, 'hours');
            for ($i = 0; $i < 24; $i++) {
                $key = -1;
                $key = array_search($i, array_column($all, 'hour'));

                if($i == 0) $data['x'][] = "12 AM";
                elseif($i < 13) $data['x'][] = "$i AM";
                else $data['x'][] = ($i-12) . " PM";

                if($key > -1) {
                    $data['y'][] = $all[$key]['total'];
                } else {
                    $data['y'][] = 0;
                }
            }
        } elseif($datediff->days < 31) {
            $all = $this->model_report_home->getOrders($startdate, $enddate, 'days');
            $interval = DateInterval::createFromDateString('1 day');
            $days     = new DatePeriod($begin, $interval, $end);
            foreach ( $days as $day ) {
                $data['x'][] = $day->format('Y-m-d');
                $y = 0;
                foreach($all as $row) {
                    if($row['date'] == $day->format('Y-m-d')) {
                        $y += $row['total'];
                    }
                }
                $data['y'][] = $y;
            }
        } elseif($datediff->days < 365) {
            $all = $this->model_report_home->getOrders($startdate, $enddate, 'months');
            $interval = DateInterval::createFromDateString('1 month');
            $months     = new DatePeriod($begin, $interval, $end);
            foreach ( $months as $month ) {
                $data['x'][] = $month->format('Y-m-01');
                $y = 0;
                foreach($all as $row) {
                    if($row['date'] == $month->format('Y-m-01')) {
                        $y += $row['total'];
                    }
                }
                $data['y'][] = $y;
            }
        } else {
            $all = $this->model_report_home->getOrders($startdate, $enddate, 'years');
            $interval = DateInterval::createFromDateString('1 year');
            $years     = new DatePeriod($begin, $interval, $end);
            foreach ( $years as $year ) {
                $data['x'][] = $year->format('Y');
                $y = 0;
                foreach($all as $row) {
                    if($row['year'] == $year->format('Y')) {
                        $y += $row['total'];
                    }
                }
                $data['y'][] = $y;
            }
        }
        $data['total'] = array_sum($data['y']);
        $this->response->setOutput(json_encode($data));
    }

    public function cardsData() {
        if (isset($this->request->get['startdate']) && isset($this->request->get['enddate'])) {
            $startdate = $this->db->escape($this->request->get['startdate']);
            $enddate = $this->db->escape($this->request->get['enddate']);
        } else {
            $startdate = date("Y-m-d");
            $enddate = $startdate;
        }

        $this->load->model('report/home');

        $data = array(
            'recent_customers_count' => $this->model_report_home->recentCustomersCount($startdate, $enddate),
            'average_order_value' => $this->currency->formatk($this->model_report_home->averageOrderValue($startdate, $enddate), $this->config->get('config_currency')),
            'returning_customers_count' => $this->model_report_home->returningCustomersCount($startdate, $enddate),
            'returns_count' => $this->model_report_home->returnsCount($startdate, $enddate)
        );
        $this->response->setOutput(json_encode($data));
    }

    public function revenue_ordersData() {
        $this->load->model('report/home');

        $data[revenue] = array(
            'today' => $this->currency->formatk($this->model_report_home->getSalesRevenueTotal('today'), $this->config->get('config_currency')),
            'yesterday' => $this->currency->formatk($this->model_report_home->getSalesRevenueTotal('yesterday'), $this->config->get('config_currency')),
            '7days' => $this->currency->formatk($this->model_report_home->getSalesRevenueTotal('7days'), $this->config->get('config_currency')),
            '30days' => $this->currency->formatk($this->model_report_home->getSalesRevenueTotal('30days'), $this->config->get('config_currency')),
            'thismonth' => $this->currency->formatk($this->model_report_home->getSalesRevenueTotal('thismonth'), $this->config->get('config_currency')),
            'lastmonth' => $this->currency->formatk($this->model_report_home->getSalesRevenueTotal('lastmonth'), $this->config->get('config_currency'))
        );

        $data['orders'] = array(
            'today' => $this->model_report_home->getOrdersTotal('today'),
            'yesterday' => $this->model_report_home->getOrdersTotal('yesterday'),
            '7days' => $this->model_report_home->getOrdersTotal('7days'),
            '30days' => $this->model_report_home->getOrdersTotal('30days'),
            'thismonth' => $this->model_report_home->getOrdersTotal('thismonth'),
            'lastmonth' => $this->model_report_home->getOrdersTotal('lastmonth')
        );

        $this->response->setOutput(json_encode($data));
    }

//    public function recentCustomersCount() {
//        if (isset($this->request->get['startdate']) && isset($this->request->get['enddate'])) {
//            $startdate = $this->db->escape($this->request->get['startdate']);
//            $enddate = $this->db->escape($this->request->get['enddate']);
//        } else {
//            $startdate = date("Y-m-d");
//            $enddate = $startdate;
//        }
//
//        $this->load->model('report/home');
//        $data = array(
//            'count' => $this->model_report_home->recentCustomersCount($startdate, $enddate)
//        );
//        $this->response->setOutput(json_encode($data));
//    }

//    public function averageOrderValue() {
//        if (isset($this->request->get['startdate']) && isset($this->request->get['enddate'])) {
//            $startdate = $this->db->escape($this->request->get['startdate']);
//            $enddate = $this->db->escape($this->request->get['enddate']);
//        } else {
//            $startdate = date("Y-m-d");
//            $enddate = $startdate;
//        }
//
//        $this->load->model('report/home');
//        $data = array(
//            'average_order_value' => $this->model_report_home->averageOrderValue($startdate, $enddate)
//        );
//        $this->response->setOutput(json_encode($data));
//    }

//    public function returningCustomersCount() {
//        if (isset($this->request->get['startdate']) && isset($this->request->get['enddate'])) {
//            $startdate = $this->db->escape($this->request->get['startdate']);
//            $enddate = $this->db->escape($this->request->get['enddate']);
//        } else {
//            $startdate = date("Y-m-d");
//            $enddate = $startdate;
//        }
//
//        $this->load->model('report/home');
//        $data = array(
//            'returning_customers_count' => $this->model_report_home->returningCustomersCount($startdate, $enddate)
//        );
//        $this->response->setOutput(json_encode($data));
//    }

//    public function returnsCount() {
//        if (isset($this->request->get['startdate']) && isset($this->request->get['enddate'])) {
//            $startdate = $this->db->escape($this->request->get['startdate']);
//            $enddate = $this->db->escape($this->request->get['enddate']);
//        } else {
//            $startdate = date("Y-m-d");
//            $enddate = $startdate;
//        }
//
//        $this->load->model('report/home');
//        $data = array(
//            'returns_count' => $this->model_report_home->returnsCount($startdate, $enddate)
//        );
//        $this->response->setOutput(json_encode($data));
//    }


    public function skiptut() {
        $this->load->model('setting/setting');

        $data = array();

        if ($this->model_setting_setting->editGuideValue("MENU_TOUR", "MENU_TOUR", "1")) {
            $data['success'] = 'true';
        } else {
            $data['success'] = 'false';
        }

        $this->response->setOutput(json_encode($data));
    }

    public function skipTrial() {
        $this->load->model('setting/setting');

        $data = array();

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if ($this->model_setting_setting->editGuideValue("GETTING_STARTED", "SKIP_TRIAL", "1")) {
                $data['success'] = 'true';
            } else {
                $data['success'] = 'false';
            }

            $this->load->model('setting/amplitude');
            $this->model_setting_amplitude->trackEvent('Onboarding Dismissed');

            $this->response->setOutput(json_encode($data));
        }
	}

    public function chart() {
		$this->language->load('common/home');
		
		$data = array();
		
		$data['order'] = array();
		$data['customer'] = array();
		$data['xaxis'] = array();
		
		$data['order']['label'] = $this->language->get('text_order');
		$data['customer']['label'] = $this->language->get('text_customer');
		
		if (isset($this->request->get['range'])) {
			$range = $this->request->get['range'];
		} else {
			$range = 'month';
		}

        if(!$this->config->get("config_status_based_revenue")){
            $extraSqlCondition = "";
        }else{
            $extraSqlCondition = "AND `order_status_id` =". $this->config->get('config_complete_status_id');
        }

        switch ($range) {
			case 'day':
				for ($i = 0; $i < 24; $i++) {
					$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE order_status_id != 0 AND (DATE(date_added) = DATE(NOW()) AND HOUR(date_added) = '" . (int)$i . "') ".$extraSqlCondition." GROUP BY HOUR(date_added) ORDER BY date_added ASC");

					if ($query->num_rows) {
						$data['order']['data'][]  = array($i, (int)$query->row['total']);
					} else {
						$data['order']['data'][]  = array($i, 0);
					}

					$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer WHERE DATE(date_added) = DATE(NOW()) AND HOUR(date_added) = '" . (int)$i . "' GROUP BY HOUR(date_added) ORDER BY date_added ASC");

					if ($query->num_rows) {
						$data['customer']['data'][] = array($i, (int)$query->row['total']);
					} else {
						$data['customer']['data'][] = array($i, 0);
					}
			
					$data['xaxis'][] = array($i, date('H', mktime($i, 0, 0, date('n'), date('j'), date('Y'))));
				}					
				break;
			case 'week':
				$date_start = strtotime('-' . date('w') . ' days'); 
				
				for ($i = 0; $i < 7; $i++) {
					$date = date('Y-m-d', $date_start + ($i * 86400));

					$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE order_status_id != 0 AND DATE(date_added) = '" . $this->db->escape($date) . "' ".$extraSqlCondition." GROUP BY DATE(date_added)");
					if ($query->num_rows) {
						$data['order']['data'][] = array($i, (int)$query->row['total']);
					} else {
						$data['order']['data'][] = array($i, 0);
					}
				
					$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "customer` WHERE DATE(date_added) = '" . $this->db->escape($date) . "' GROUP BY DATE(date_added)");
			
					if ($query->num_rows) {
						$data['customer']['data'][] = array($i, (int)$query->row['total']);
					} else {
						$data['customer']['data'][] = array($i, 0);
					}
		
					$data['xaxis'][] = array($i, date('D', strtotime($date)));
				}
				
				break;
			default:
			case 'month':
				for ($i = 1; $i <= date('t'); $i++) {
					$date = date('Y') . '-' . date('m') . '-' . $i;

					$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE order_status_id != 0 AND (DATE(date_added) = '" . $this->db->escape($date) . "') ".$extraSqlCondition." GROUP BY DAY(date_added)");

					if ($query->num_rows) {
						$data['order']['data'][] = array($i, (int)$query->row['total']);
					} else {
						$data['order']['data'][] = array($i, 0);
					}

					$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer WHERE DATE(date_added) = '" . $this->db->escape($date) . "' GROUP BY DAY(date_added)");

					if ($query->num_rows) {
						$data['customer']['data'][] = array($i, (int)$query->row['total']);
					} else {
						$data['customer']['data'][] = array($i, 0);
					}

					$data['xaxis'][] = array($i, date('j', strtotime($date)));
				}
				break;
			case 'year':
				for ($i = 1; $i <= 12; $i++) {
					$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE order_status_id != 0 AND YEAR(date_added) = '" . date('Y') . "' AND MONTH(date_added) = '" . $i . "' ".$extraSqlCondition." GROUP BY MONTH(date_added)");

					if ($query->num_rows) {
						$data['order']['data'][] = array($i, (int)$query->row['total']);
					} else {
						$data['order']['data'][] = array($i, 0);
					}

					$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer WHERE YEAR(date_added) = '" . date('Y') . "' AND MONTH(date_added) = '" . $i . "' GROUP BY MONTH(date_added)");

					if ($query->num_rows) {
						$data['customer']['data'][] = array($i, (int)$query->row['total']);
					} else {
						$data['customer']['data'][] = array($i, 0);
					}

					$data['xaxis'][] = array($i, date('Y'));
				}
				break;
		}
		
		$this->response->setOutput(json_encode($data));
	}
	
	public function login() {
		$route = '';
		
		if (isset($this->request->get['route'])) {
			$part = explode('/', $this->request->get['route']);
			
			if (isset($part[0])) {
				$route .= $part[0];
			}
			
			if (isset($part[1])) {
				$route .= '/' . $part[1];
			}
		}
		
		$ignore = array(
			'common/login',
			'common/forgotten',
			'common/reset'
		);

		if (!$this->user->isLogged() && !in_array($this->options['routeString'], $ignore)) {
			return $this->redirect('/admin/common/login');
		}

        $ignore = array(
            'common/login',
            'common/logout',
            'common/forgotten',
            'common/reset',
            'error/not_found',
            'error/permission'
        );

        if (isset($this->request->get['redirect_route'])){
            $this->session->data['redirect_route'] = $this->request->get['redirect_route'];
        }

		/*
		// EC-3965
		if (isset($this->request->get['route'])) {
			$config_ignore = array();
			
			if ($this->config->get('config_token_ignore')) {
				$config_ignore = unserialize($this->config->get('config_token_ignore'));
			}
				
			$ignore = array_merge($ignore, $config_ignore);
						
			if (!in_array($route, $ignore) && (!isset($this->request->get['token']) || !isset($this->session->data['token']) || ($this->request->get['token'] != $this->session->data['token']))) {
				return $this->redirect('index.php?route=common/login');
			}
		} else {
			if (!isset($this->request->get['token']) || !isset($this->session->data['token']) || ($this->request->get['token'] != $this->session->data['token'])) {
				return $this->redirect('index.php?route=common/login');
			}
		}
		*/

        if (defined('CLIENT_SUSPEND') && $this->options['routeString'] != 'account/charge' && !in_array($this->options['routeString'], $ignore) ) {
            return $this->redirect($this->url->link('account/charge', '', 'SSL'));
        }
	}
	
	public function permission() {
		if (isset($this->request->get['route'])) {
			$route = '';
			
			$part = explode('/', $this->request->get['route']);
			
			if (isset($part[0])) {
				$route .= $part[0];
			}
			
			if (isset($part[1])) {
				$route .= '/' . $part[1];
			}
			
			$ignore = array(
				'common/home',
				'common/login',
				'common/logout',
				'common/forgotten',
				'common/reset',
				'error/not_found',
				'error/permission'		
			);			
						
			if (!in_array($route, $ignore) && !$this->user->hasPermission('access', $route)) {
				return $this->forward('error/permission');
			}
		}
	}

	public function liveVisits() {
        //$this->load->model('report/home');
        //$data = $this->model_report_home->liveVisits();
        //$this->response->setOutput(json_encode($data));
    }

    public function eview() {
        $expand_file_path = $this->request->get['path'];

        $this->template = $expand_file_path . '.expand';
        $this->base = 'common/base';

        $this->response->setOutput($this->render_ecwig());
    }

    private function gettingStarted()
    {
        $this->data['gettingStarted'] = $this->model_setting_setting->getGuideValue("GETTING_STARTED");
        if (  $this->data['gettingStarted']['ADD_DOMAIN'] != 1 ){
            $this->load->model('setting/domainsetting');
            $domain_count = $this->model_setting_domainsetting->countDomain();
            if ($domain_count > 0){
                $this->model_setting_setting->editGuideValue('GETTING_STARTED', 'ADD_DOMAIN', '1');
                $this->data['gettingStarted']['ADD_DOMAIN'] = 1;
            }
        }
        $this->data['gettingStarted_count'] = $this->model_setting_setting->getGuideValueCount("GETTING_STARTED");
    }
}
?>