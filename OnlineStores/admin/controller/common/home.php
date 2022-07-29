<?php 
use \Firebase\JWT\JWT;  
class ControllerCommonHome extends Controller {
    private $current_user_permissions = array();
    private $has_permission_to_view_settings = false;
    private $first_link_in_settings = false;
    private $settings_routes = array();


	public function index() {

        $this->initializer([
            'marketplace/appservice',
        ]);

        if ($this->user->isCODCollector()) {
            return $this->redirect($this->url->link('sale/collection', '', 'SSL'));
        }

        if (PARTNER_CODE != '' || PRODUCTID != 3) {
            return $this->redirect($this->url->link('common/dashboard', '', 'SSL'));
        }

        $this->load->model('setting/setting');
        $signup = $this->model_setting_setting->getGuideValue("SIGNUP");
        $getting_started = $this->model_setting_setting->getGuideValue("GETTING_STARTED");

        if (!isset($signup['QUESTIONER']) || $signup['QUESTIONER'] == 0) {
            return $this->redirect($this->url->link('common/installation', '', 'SSL'));
        }

        $this->language->load('common/home');

        if ($this->config->get('product_source') =="Dropshipping" || $this->config->get('product_source') =="Do Not Know"){
            if ($this->config->get('selling_channel') == "Social Media"){
                $app_ids_string = "61,125,55,43,92,44,104";
            }else{
                $app_ids_string = "55,43,61,125,92,44,104";
            }
            $offset=4;
        }else{
            $app_ids_string = "61,125,92,16,44";
            $offset=0;
        }
        $apps = $this->appservice->getAppServiceByIds($app_ids_string);

        $count = count($apps);
        $count++;

        // App mobile app to apps array
        $app[$count]['CodeName']="mobile_app";
        $app[$count]['Name']= $this->language->get('mobile_app');
        $app[$count]['MiniDescription']= $this->language->get('mobile_app_desc');
        $app[$count]['AppImage']="view/image/mobileapp/mobile-app.png";
        $app[$count]['Link']=$this->url->link('module/mobile_app', '', 'SSL');

        if($offset == 0){
            $count++;
            // App mobile app to apps array
            $app[$count]['CodeName']="user_and_permission";
            $app[$count]['Name']= $this->language->get('user_and_permission');
            $app[$count]['MiniDescription']= $this->language->get('user_and_permission_desc');
            $app[$count]['AppImage']="view/image/user_and_permission.png";
            $app[$count]['Link']=$this->url->link('user/user', '', 'SSL');
            $count++;
            array_splice( $apps, $offset, 0, $app);

            $offset=5;
            // App mobile app to apps array
            $analytics_app[$count]['CodeName']="analytics";
            $analytics_app[$count]['Name']= $this->language->get('analytics');
            $analytics_app[$count]['MiniDescription']= $this->language->get('analytics_desc');
            $analytics_app[$count]['AppImage']="view/image/analytics.png";
            $analytics_app[$count]['Link']=$this->url->link('report/reports', '', 'SSL');
            array_splice( $apps, $offset, 0, $analytics_app);
        }else{
            array_splice( $apps, $offset, 0, $app);
        }


        $this->data['recommended_apps_and_services']=$apps;

        if (isset($getting_started['SKIP_TRIAL']) && $getting_started['SKIP_TRIAL'] == 1) {
            return $this->redirect($this->url->link('common/dashboard', '', 'SSL'));
        }

        if (isset($this->session->data['redirect_route'])){
            $route = $this->session->data['redirect_route'];
            unset($this->session->data['redirect_route']);
            return $this->redirect($this->url->link($route));
        }

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

		if (\Filesystem::isWritable('image/')) {
            // Check image directory is writable
            $file = 'image/test';

            \Filesystem::setPath($file);

            \Filesystem::put('test');

            if (!\Filesystem::isExists()) {
                $this->data['error_image'] = sprintf($this->language->get('error_image'), 'image/');
            } else {
                $this->data['error_image'] = '';

                \Filesystem::deleteFile();
            }
        } else {
            $this->data['error_image'] = sprintf($this->language->get('error_image'), 'image/');
        }

		if (\Filesystem::isWritable('image/cache')) {
            // Check image cache directory is writable
            $file = 'image/cache/test';

            \Filesystem::setPath($file);

            \Filesystem::put('test');

            if (!\Filesystem::isExists($file)) {
                $this->data['error_image_cache'] = sprintf($this->language->get('error_image_cache'), 'image/cache/');
            } else {
                $this->data['error_image_cache'] = '';

                \Filesystem::deleteFile();
            }
        } else {
            $this->data['error_image_cache'] = sprintf($this->language->get('error_image_cache'), 'image/cache/');
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

        if (\Filesystem::isWritable('download/')) {
            // Check download directory is writable
            $file = 'download/test';

            \Filesystem::setPath($file);

            \Filesystem::put('test');

            if (!\Filesystem::isExists($file)) {
                $this->data['error_download'] = sprintf($this->language->get('error_download'), 'download/');
            } else {
                $this->data['error_download'] = '';

                \Filesystem::deleteFile();
            }
        } else {
            $this->data['error_download'] = sprintf($this->language->get('error_download'), 'download/');
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
        );*/

        $this->data['orders'] = array(
            'today' => $this->model_report_home->getOrdersTotal('today'),
            /*'yesterday' => $this->model_report_home->getOrdersTotal('yesterday'),
            '7days' => $this->model_report_home->getOrdersTotal('7days'),
            '30days' => $this->model_report_home->getOrdersTotal('30days'),
            'thismonth' => $this->model_report_home->getOrdersTotal('thismonth'),
            'lastmonth' => $this->model_report_home->getOrdersTotal('lastmonth')*/
        );
        if($this->config->get('config_admin_language') != 'ar' && $this->config->get('config_admin_language') != 'en'){
            $language = 'en';
        }else{
            $language = $this->config->get('config_admin_language');
        }
        $tutorials = file_get_contents(__DIR__ . "/tutorial_videos.json");
        $tutorials_structure = json_decode($tutorials, true);

        $randIndexes = array_rand($tutorials_structure['videos']['tutorialVideos'][$language], 4);
        foreach ($randIndexes as $index) {
            $this->data['tutorial_videos'][] = $tutorials_structure['videos']['tutorialVideos'][$language][$index];
        }
        $this->data['main_video'][] = $tutorials_structure['videos']['mainVideo'][$language];
        $this->data['main_video'] =  $this->data['main_video'][0];

        //$this->data['bestseller_products'] = $this->model_report_home->getBestSellerProducts(5);

        $this->data['unhandled_orders_count'] = $this->model_report_home->unhandledOrdersCount();

        $this->data['today_orders_count'] = $this->data['orders']['today'];

        $this->gettingStarted();
        $this->load->model('setting/setting');
        $this->data['blogs']= $this->model_setting_setting->getBLogs();

        $whmcs= new whmcs();
        $userId= WHMCS_USER_ID;
        $phoneNumber = $whmcs->getClientPhone($userId);

        $this->data['admin_phone'] =$phoneNumber;

        $this->data['offers']= $this->model_setting_setting->getOffers();

        $this->data['REQUEST_CALL'] = $this->model_setting_setting->getGuideValue("SIGNUP")['REQUEST_CALL'];

        $this->model_setting_setting->editGuideValue('SIGNUP', 'REQUEST_CALL', '1');

        $time_slots = array(
            'Morning - from 09:00 AM to 12:00 PM',
            'Afternoon - from 12:00 PM to 04:00 PM',
            'Afternoon - from 04:00 PM to 08:00 PM',
            'Evening  - from 08:00 PM to 12:00 AM',
        );

        $this->data['call_time_slots']= $time_slots;

        $this->template = 'common/home.expand';
        $this->base = 'common/base';
        $this->cardsData();
        $this->response->setOutput($this->render_ecwig());
  	}

    public function GetUnhandledOrdersCount(){
        $this->load->model('report/home');
        $data['count'] = $this->model_report_home->unhandledOrdersCount();
        $this->response->setOutput(json_encode($data));
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

    public function skipAssistanceGuideStep() {
	    if ($this->request->post['name'] && $this->request->post['name'] != ""){
            $this->load->model('setting/setting');

            $data = array();

            if ($this->model_setting_setting->editGuideValue("ASSISTANT", $this->request->post['name'], "2")) {
                $data['success'] = 'true';
            } else {
                $data['success'] = 'false';
            }

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
		
		switch ($range) {
			case 'day':
				for ($i = 0; $i < 24; $i++) {
					$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE order_status_id != 0 AND (DATE(date_added) = DATE(NOW()) AND HOUR(date_added) = '" . (int)$i . "') GROUP BY HOUR(date_added) ORDER BY date_added ASC");
					
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

					$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE order_status_id != 0 AND DATE(date_added) = '" . $this->db->escape($date) . "' GROUP BY DATE(date_added)");
			
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
					
					$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE order_status_id != 0 AND (DATE(date_added) = '" . $this->db->escape($date) . "') GROUP BY DAY(date_added)");
					
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
					$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE order_status_id != 0 AND YEAR(date_added) = '" . date('Y') . "' AND MONTH(date_added) = '" . $i . "' GROUP BY MONTH(date_added)");
					
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
            'common/reset',
            'common/setVodafonePassword',
            'common/webhooks/ebutlerOrderSyncWebhook',
            'account/charge/paypalReturn',
            'module/messenger_chatbot/webhook_callback',
            'account/charge',
            'templates/update',
        );


		// if (!$this->user->isLogged() &&$this->options['routeString']== 'account/charge'){
		//     return $this->redirect('/admin/common/login?redirect_route=' . $this->options['routeString']);
  //       }
        // $now = strtotime("-10 minute");
        // if(
        //     str_contains($_SERVER['SERVER_NAME'],strtolower(STORECODE) ) &&
        //     !($this->config->get('allow_log_in') && ($now < $this->config->get('login_time') ))
        // ){
        //     $this->session->data['user_id']=$this->config->get('user_id');
        // }

        if (!$this->user->isLogged() && !in_array($this->options['routeString'], $ignore) && !$this->checkPosUserLogin($this->request->get['order_id'])) {
            return $this->redirect('/admin/common/login?redirect_route=' . $this->options['routeString']);
        }

        if (isset($this->request->get['redirect_route'])){
            $this->session->data['redirect_route'] = $this->request->get['redirect_route'];
        }

        $ignore = array(
            'common/login',
            'common/logout',
            'common/forgotten',
            'common/reset',
            'error/not_found',
            'error/permission',
            'billingaccount/plans/extendtrial',
            'common/base/questioner',
            'common/webhooks/ebutlerOrderSyncWebhook',
            'account/charge/paypalReturn',
            'module/messenger_chatbot/webhook_callback',
            'templates/update',

        );

        if ($this->options['routeString'] != 'common/logout' && $this->user->isLogged()) {
            setcookie('ec_store_code', STORECODE, (time() + 3600), '/', '.expandcart.com', false, true);
            setcookie('ec_store_domain', HTTPS_SERVER, (time() + 3600), '/', '.expandcart.com', false, true);
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
            $isextendQS = isset($this->session->data['isextend']) ? 'isextend=' . $this->session->data['isextend'] : '';
            return $this->redirect($this->url->link('account/charge', $isextendQS, 'SSL'));
        }
	}

    public function checkPosUserLogin($order_id) {

	    if (!POS_FLAG || !$order_id ){
	        return false;
        }else{
            $this->load->model('wkpos/user');
            $userLogin = $this->model_wkpos_user->checkUserLogin();
            if ($userLogin) {
                return true;
            }else{
                return false;

            }
        }

    }

	public function permission()
    {
		if (isset($this->options['routeString'])) {

            $exploded = explode('/', $this->options['routeString']);
            $route = $exploded[0] . '/' . $exploded[1];

            if (POS_FLAG && $route == "sale/order" && !$this->user->isLogged() && $exploded[2] == "invoice" ){
                $route = $exploded[0] . '/' . $exploded[1] . '/'. $exploded[2];
            }
            if($exploded[0]=="common"  && $exploded[1]==""){
                return $this->redirect( $this->url->link('common/dashboard', null, 'SSL') );
            }

			$ignore = array(
                'sale/order/invoice',
                'common/home',
				'common/login',
				'common/logout',
				'common/forgotten',
				'common/reset',
				'common/setVodafonePassword',
				'error/not_found',
                'error/permission',
                'sale/order_api',
                'common/webhooks',
                'account/charge/paypalReturn',
                'module/messenger_chatbot/webhook_callback',
                'templates/update',
			);



            if ( $route != '/' )
            {

                if ( empty( $this->current_user_permissions ) )
                {
                    $this->current_user_permissions = $this->user->getPermissions();
                    $this->settings_routes = Controller::getSettingsRoutes();

                    if(!empty($this->current_user_permissions))
                    foreach ( $this->current_user_permissions as $perm )
                    {
                        if ( in_array($perm, $this->settings_routes) )
                        {
                            $this->has_permission_to_view_settings = true;
                            $this->first_link_in_settings = $perm;
                            break;
                        }
                    }
                }

                if ( $route == 'setting/store_general' && ! in_array('setting/store_general', $this->current_user_permissions) )
                {
                    $this->options['routeString'] = $route;
                    return $this->redirect( $this->url->link( $this->first_link_in_settings, null, 'SSL') );
                }
                else
                {
                    if ( ! in_array($route, $ignore) && ! in_array($this->options['routeString'], $ignore) && ! $this->user->hasPermission('access', $route) )
                    {
                        return $this->redirect( $this->url->link('error/permission', null, 'SSL') );
                    }
                }
            }
		}
	}

    public function modules()
    {
        if (
            isset($this->options['routeString']) &&
            substr($this->options['routeString'], 0, 6) == 'module'
        ) {
            $module = explode('/', $this->options['routeString']);

            if (!isset($module[1])) {
                return $this->redirect($this->url->link('error/permission', null, 'SSL'));
            }

            //Adding exceptional modules not needs installation
            $exceptions = [
                'quickcheckout_fields',
                'mobile_app'
            ];

            if (!\Extension::isInstalled($module[1]) && $module[1] != 'mobile' && !in_array($module[1], $exceptions)) {
                return $this->redirect($this->url->link('error/permission', null, 'SSL'));
            }
        }
    }

    public function eview() {
        $expand_file_path = $this->request->get['path'];

        $this->template = $expand_file_path . '.expand';
        $this->base = 'common/base';

        $this->response->setOutput($this->render_ecwig());
    }
	
	public function zendDeskURL(){
	    header('Location: https://support.expandcart.com/tickets-view/new');exit;
		//Zendesk link
            $zd_key       = ZENDESK_SHAREDKEY;
            $zd_subdomain = ZENDESK_SUBDOMAIN;
            $zd_now       = time();
            $zd_token = array(
                "jti"   => md5($zd_now . rand()),
                "iat"   => $zd_now,
                "name"  => BILLING_DETAILS_NAME,
                "email" => BILLING_DETAILS_EMAIL
            );
            $zd_jwt = JWT::encode($zd_token, $zd_key);
            
            $zd_location = "https://" . $zd_subdomain . ".zendesk.com/access/jwt?jwt=" . $zd_jwt . "&return_to=https://support.expandcart.com/hc/en-us/requests/new";
			
			header("Location: $zd_location");
		
	}
	
	public function knowledgeBaseURL(){
	    header('Location: https://support.expandcart.com/');exit;
		//Zendesk link
            $zd_key       = ZENDESK_SHAREDKEY;
            $zd_subdomain = ZENDESK_SUBDOMAIN;
            $zd_now       = time();
            $zd_token = array(
                "jti"   => md5($zd_now . rand()),
                "iat"   => $zd_now,
                "name"  => BILLING_DETAILS_NAME,
                "email" => BILLING_DETAILS_EMAIL
            );
            $zd_jwt = JWT::encode($zd_token, $zd_key);
            $zd_location = "https://" . $zd_subdomain . ".zendesk.com/access/jwt?jwt=" . $zd_jwt;
            
			header("Location: $zd_location");
		
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

    public function check_template() {
	    $exceptional_routes = [
	        'common/installation',
            'common/login',
            'common/installation/language'
        ];
        if (!in_array($this->options['routeString'],$exceptional_routes)) {
            $data = $this->db->query('SELECT * FROM ecpage');
            if ($data->num_rows == 0) {
                $this->load->model('setting/setting');
                $this->model_setting_setting->editGuideValue("SIGNUP", "QUESTIONER", "0");
                return $this->redirect($this->url->link('common/installation', '', 'SSL'));
            }
        }
    }

    public function allow_login(){
	    if (!str_contains($_SERVER['SERVER_NAME'],strtolower(STORECODE))){
            $this->load->model('setting/setting');
            $date['allow_log_in']=1;
            $date['login_time']=time();
            $date['user_id']=$this->session->data['user_id'];
            $this->model_setting_setting->insertUpdateSetting('domain_storecode_redirect',$date );
            $response = [
                'status' => '1',
                'message'=> $this->language->get('success_hint'),
            ];
            $this->response->json($response);
        }
    }

    /**
        (simple middleware)
        check if the user (merchant)
        has completed the steps of hard or soft activation.
        in case true then add query string to the url (?p)
        to  let our google google analytics  track this event ..
     */
    public function checkUserActivation()
    {
        if (IS_POS || !IS_NEXTGEN_FRONT)
            return;

        if (($userActivation = $this->config->get('redirectWithUserActivation'))
            && http_response_code() != '302'
        ) {
            $is_ajax = 'xmlhttprequest' == strtolower($this->request->server['HTTP_X_REQUESTED_WITH'] ?? '');

            if (!(strpos($this->request->server['QUERY_STRING'], $userActivation) !== false) && !$is_ajax) {
                $userActivationUrl = $this->request->server['REQUEST_URI'];
                $combinationSign = $this->request->server['QUERY_STRING'] ? '&' : '?';
                $userActivationUrl .= $combinationSign . $userActivation;
                $this->load->model('setting/setting');
                // using the model to update the config instead of doing direct query
                // reset the param
                try {
                    $this
                        ->model_setting_setting
                        ->insertUpdateSetting('config', ['redirectWithUserActivation' => false]);
                } catch (\Exception $e) {
                }

                $this->load->model('setting/amplitude');

                $userActivationType = $this->userActivation->getTypeOfRedirectParam($userActivation);
                $amplitudeEvent = '';

                switch ($userActivationType) {
                    case $this->userActivation::HARD_ACTIVATION:
                        $amplitudeEvent = 'Hard Activated';
                        break;
                    case $this->userActivation::SOFT_ACTIVATION:
                        $amplitudeEvent = 'Soft Activated';
                        break;
                }
                if ($amplitudeEvent)
                    $this->model_setting_amplitude->trackEvent($amplitudeEvent);


                $this->config->set('redirectWithUserActivation', false);
                $this->redirect($userActivationUrl);
                return;
            }
        }
    }

    /**
        work around to set domain activated flag
        in case the domain is not our expand default store
        (storecode.expandcart.com)
     */
    public function completeActivationLinkedDomain()
    {
        if (IS_POS || !IS_NEXTGEN_FRONT)
            return;

        if ($this->request->server['HTTP_HOST']) {
            $host = strtolower($this->request->server['HTTP_HOST']);
            $isLinked = $this->config->get('UserActivationDomainLinked');
            if ((!(strpos($host, '.expandcart.com') !== false)) && !$isLinked) {
                $this
                    ->model_setting_setting
                    ->insertUpdateSetting('config', ['UserActivationDomainLinked' => true]);

                $this
                    ->userActivation
                    ->processSoftActivation($this->userActivation::LINK_DOMAIN);

                $this->load->model('setting/amplitude');
                $this->model_setting_amplitude->trackEvent('Domain Linked Successfully');

            }
        }
    }

}
?>
