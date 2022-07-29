<?php

class ControllerExtensionPayment extends Controller
{
   
	private $_hidden_methods = ["paypal","expandpay"];

	public function index()
    {

        if ($this->request->get['card_name']){
            $this->session->data['card_name'] = $this->request->get['card_name'];
        }

        $this->language->load('extension/payment');

        $this->load->model('setting/setting');
        $gettingStarted = $this->model_setting_setting->getGuideValue("GETTING_STARTED");

        if($gettingStarted['PAYMENT'] != 1){
            $count = count($this->get_activated_payment_methods());
            if ($count > 1){
                $gettingStarted['PAYMENT'] = 1;
                $this->tracking->updateGuideValue('PAYMENT');
            }
        }

        $this->data['payment_enabled'] = $gettingStarted['PAYMENT'];

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['activated_gateways'] = array();
        $this->data['paypal'] = array();
        $this->data['credit_cards'] = array();
        $this->data['prepaid_cards'] = array();
        $this->data['offline_methods'] = array();

        $this->initializer([
            'extension/payment'
        ]);

        $page = 1;
        $offset = 0;
        $perPage = 9;
        if (isset($this->request->get['page']) && $this->request->get['page'] > 1) {
            $page = $this->request->get['page'];

            $offset = ($page - 1) * $perPage;
        }

        $this->data['filterTypes'] = [];

        $installed = $types = $lookup = null;

        if (isset($this->request->get['types'])) {
            $this->data['filterTypes'] = $types = array_filter($this->request->get['types']);
        }

        if (isset($this->request->get['installed'])) {
            $this->data['installed'] = $installed = $this->request->get['installed'];
        }

        if (isset($this->request->get['lookup'])) {
            $this->data['lookup'] = $lookup = $this->request->get['lookup'];
        }

        // @read hint #1
        $queryData = [
//            'start' => $offset,
//            'limit' => $perPage,
//            'installed' => $installed,
            'types' => $types,
            'lookup' => $lookup,
        ];

        // 1- get recommended methods.

        $recommendedPaymentMethodsData = $this->payment->getRecommendedPaymentMethods();

        foreach ($recommendedPaymentMethodsData['data'] as $key => $extension) {
            $settingGroup = $this->config->get($extension['code']);
            $status = null;
            if ($settingGroup && is_array($settingGroup) === true) {
                $status = $settingGroup['status'];
            } else {
//                $result = $this->db->query("SELECT * from setting where `key` = '" . $extension['code'] . '_status' . "'");
//                $status =  $result->row["value"];
                $status = $this->config->get($extension['code'] . '_status');
            }

            $recommendedPaymentMethodsData['data'][$key]['installed']=($status != null ?  : false);
            $recommendedPaymentMethodsData['data'][$key]['supported_countries']= $this->getCountries($extension['supported_countries']);
            $recommendedPaymentMethodsData['data'][$key]['description']=html_entity_decode($extension['description']);
        }

        $this->data['recommended_payment_methods'] = $recommendedPaymentMethodsData;

        $extensions = $this->payment->getPaymentMethodsGrid(
            $queryData,
            $this->config->get('config_language_id')
        );

        $this->data['types'] = $this->payment->getTypes();

        $paymentMethods = [];

        // 2- add getCountries / add special_rate-supported_countries-price

        foreach ($extensions['data'] as $key => $extension) {
            $supported_countries =  $this->getCountries($extension['supported_countries']);
            $settingGroup = $this->config->get($extension['code']);
            $status = null;
            
            if ($extension['code'] == "tabby") {
                $settingGroup = $this->config->get('tabby_pay_later');
            }
            
            if ($settingGroup && is_array($settingGroup) === true) {
                $status = $settingGroup['status'];
                $sortorder = $settingGroup['sort_order'];
            } else {
               // $result = $this->db->query("SELECT * from setting where `key` = '" . $extension['code'] . '_status' . "'");
                $status =  /*$result->row["value"];*/$this->config->get($extension['code'] . '_status');
                $sortorder = $this->config->get($extension['code'] . '_sort_order');
            }

            /*
             * @hint #1
             * a temperoray way to handle status and install fields, this way we will not rely on the LIMIT clause
             * in the mysql server side ( in extension/payment model), and rather we will filter the array in the PHP
             * side untill we seperate the payment settings in a separated table
             */

            if (isset($this->request->get['enabled']) || isset($this->request->get['disabled']) || isset($this->request->get['installed'])) {
                if (isset($this->request->get['enabled']) && $this->request->get['enabled'] == 1) {
                    $installedStat = ($status != null ? true : false);
                    $this->data['enabled'] = $this->request->get['enabled'];
                    if($extension['code'] == "paypal" || $extension['code'] == "باي بال") {

                        $result = $this->config->get("paypal_account_connected");
                        
                        if($result == 1) {
                            $installedStat = true;
                        }
                    }
                    if ($status == 1) {
                        $paymentMethods[$key] = [
                            'code' => $extension['code'],
                            'title' => $extension['title'],
                            'description' => substr(html_entity_decode($extension['description']), 0, 300),
                            'image' => $extension['image'],
                            'image_alt' => $extension['image_alt'],
                            'installed' => $installedStat,
                            'type' => $extension['type'],
                            'status' => $status,
                            'id' => $extension['pm_id'],
                            'special_rate'=>$extension['special_rate'],
                            'supported_countries'=>$supported_countries,
                            'price'=>$extension['price']
                        ];
                    }

                }

                if (isset($this->request->get['disabled']) && $this->request->get['disabled'] == 1) {

                    $this->data['disabled'] = $this->request->get['disabled'];
                    $installedStat = ($status != null ? true : false);

                    if($extension['code'] == "paypal" || $extension['code'] == "باي بال") {

                        $result = $this->config->get("paypal_account_connected");

                        if($result == 1) {
                            $installedStat = true;
                        }
                    }

                    if (is_null($status) == false && $status == 0) {
                        $paymentMethods[$key] = [
                            'code' => $extension['code'],
                            'title' => $extension['title'],
                            'description' => substr(html_entity_decode($extension['description']), 0, 300),
                            'image' => $extension['image'],
                            'image_alt' => $extension['image_alt'],
                            'installed' => $installedStat,
                            'type' => $extension['type'],
                            'status' => $status,
                            'id' => $extension['pm_id'],
                            'special_rate'=>$extension['special_rate'],
                            'supported_countries'=>$supported_countries,
                            'price'=>$extension['price']
                        ];
                    }

                }

                if (isset($this->request->get['installed']) && $this->request->get['installed'] == 1) {

                    $this->data['installed'] = $this->request->get['installed'];

                    $installedStat = ($status != null ? true : false);

                    if($extension['code'] == "paypal" || $extension['code'] == "باي بال") {

                        $result = $this->config->get("paypal_account_connected");

                        if($result == 1) {
                            $installedStat = true;
                        }
                    }

                    if (is_null($status) == false) {
                        $paymentMethods[$key] = [
                            'code' => $extension['code'],
                            'title' => $extension['title'],
                            'description' => substr(html_entity_decode($extension['description']), 0, 300),
                            'image' => $extension['image'],
                            'image_alt' => $extension['image_alt'],
                            'installed' => $installedStat,
                            'type' => $extension['type'],
                            'status' => $status,
                            'id' => $extension['pm_id'],
                            'special_rate'=>$extension['special_rate'],
                            'supported_countries'=>$supported_countries,
                            'price'=>$extension['price']
                        ];
                    }

                }

            } else {

                $installedStat = ($status != null ? true : false);

                if($extension['code'] == "paypal" || $extension['code'] == "باي بال") {

                    $result = $this->config->get("paypal_account_connected");

                    if($result == 1) {
                        $installedStat = true;
                    }
                }

                $paymentMethods[$key] = [
                    'code' => $extension['code'],
                    'title' => $extension['title'],
                    'description' => substr(html_entity_decode($extension['description']), 0, 300),
                    'image' => $extension['image'],
                    'image_alt' => $extension['image_alt'],
                    'installed' => $installedStat,
                    'type' => $extension['type'],
                    'status' => $status,
                    'id' => $extension['pm_id'],
                    'special_rate'=>$extension['special_rate'],
                    'supported_countries'=>$supported_countries,
                    'price'=>$extension['price']
                ];

            }
        }

        // @read hint #1
        //$paymentMethodsCount = count($paymentMethods);

        //$paymentMethods = array_slice($paymentMethods, $offset, $perPage);

        // 3- sort by installed-special_rate
        $sort_order=array();
        foreach ($paymentMethods as $key => $value) {
            $sort_order['installed'][$key] = $value['installed'];
            $sort_order['special_rate'][$key] = $value['special_rate'];
        }
        array_multisort($sort_order['installed'], SORT_DESC, $sort_order['special_rate'], SORT_DESC, $paymentMethods);

        $this->data['payment_methods'] = $paymentMethods;

        uasort($this->data['activated_gateways'], function ($a, $b) {
            return $a['list_sort_order'] - $b['list_sort_order'];
        });
        uasort($this->data['offline_methods'], function ($a, $b) {
            return $a['list_sort_order'] - $b['list_sort_order'];
        });
        uasort($this->data['prepaid_cards'], function ($a, $b) {
            return $a['list_sort_order'] - $b['list_sort_order'];
        });
        uasort($this->data['credit_cards'], function ($a, $b) {
            return $a['list_sort_order'] - $b['list_sort_order'];
        });
        uasort($this->data['paypal'], function ($a, $b) {
            return $a['list_sort_order'] - $b['list_sort_order'];
        });

        $get = $this->request->get;
        unset($get['page']);
        unset($get['ajaxish']);

        // 4- remove pagination

//        $pagination = new Pagination();
//        $pagination->total = $paymentMethodsCount;//$extensions['totalFiltered']; // @read hint #1
//        $pagination->page = $page;
//        $pagination->limit = $perPage;
//        $pagination->text = $this->language->get('text_pagination');
//        $pagination->url = $this->url->link('extension/payment', http_build_query($get) . '&page={page}', 'SSL');

       // $this->data['pagination'] = $pagination->render();

        if ( isset( $this->request->get['ajaxish'] ) )
        {
            $this->template = 'extension/payment_methods_snippet.expand';
        }
        else
        {
            $this->template = 'extension/payment.expand';
            $this->children = array(
                'common/header',
                'common/footer'
            );
        }


        $curLang = $this->config->get("config_admin_language");

        $supportLink = "";

        if($curLang == "en") {
            $supportLink = "https://support.expandcart.com/paypal";
        } else {
            $supportLink = "https://support.expandcart.com/hc/ar/articles/360020613040-%D8%A8%D8%A7%D9%8A-%D8%A8%D8%A7%D9%84";
        }

        $this->data["paypal_supportLink"] = $supportLink;

        //get paypal & expandpay show status
		$paypal_and_expandpay = $this->_paypalExpandPayStatus();
		$this->data['show_expandpay'] 	= $paypal_and_expandpay['show_expandpay'];
		$this->data['show_paypal'] 		= $paypal_and_expandpay['show_paypal'];
		$this->data['paypal_installed'] = $this->config->get("paypal_account_connected") == 1 ? true : false;

		if($this->data["paypal_installed"]) {
            $this->load->model('extension/payment');

            $this->data["paypalMethodData"] = $this->model_extension_payment->getPaymentMethodData("paypal");

        }

		$this->data['hidden_methods']   = $this->_hidden_methods;
		
        $this->response->setOutput($this->render_ecwig());
    }
	
	private function _paypalExpandPayStatus(){
		$in_egypt 		= false;
		//$whitelist = ['fyyxdn0754','adxbop5046','cszdia8362','qaz123'];
		//$whitelist = ['fyyxdn0754','adxbop5046','cszdia8362','qaz123','joysox2315','bmsdrv3507','mohamedbelal'];
		
		//hide & show expandPay according to merchant country in whmcs
			$whmcs			= new whmcs();
			$clientDetails 	= $whmcs->getClientDetails(WHMCS_USER_ID);
			
			if(!empty($clientDetails)){
				$in_egypt  = (strtoupper($clientDetails['countrycode']) == 'EG');
			}
		
		
		if (STAGING_MODE != 1){

			$show_paypal = false;
			$show_expandpay = false;
			
			$expandpay_method = $this->payment->getPaymentMethodData("expandpay");
			$paypal_method = $this->payment->getPaymentMethodData("paypal");
			
			//show expandpay in published case 
		   if( $in_egypt && !empty( $expandpay_method) && isset($expandpay_method['published']) &&  $expandpay_method['published'] == '1') {
			   $show_expandpay = true;
		   }
			//show paypal in published case 
		   if(!empty( $paypal_method) && isset($paypal_method['published']) &&  $paypal_method['published'] == '1') {
			   $show_paypal = true;
		   }
        }else {

			//for local and staging servers 
			
			$show_paypal 	  = true;
			$show_expandpay   = false;
			
			if($in_egypt){
				$show_expandpay 	  = true;
			 }
		}
		/*
		//for testing on whitelist only 
		 if(!in_array(strtolower(STORECODE),$whitelist)){
			$show_expandpay = false;
		}*/
		
		return [
				'show_paypal' 	 => $show_paypal ,
				'show_expandpay' => $show_expandpay 
				];
		
	}

	public function thirdparty(){
        $this->language->load('extension/payment');

        $this->load->model('setting/setting');
        $gettingStarted = $this->model_setting_setting->getGuideValue("GETTING_STARTED");

        if($gettingStarted['PAYMENT'] != 1){
            $count = count($this->get_activated_payment_methods());
            if ($count > 1){
                $gettingStarted['PAYMENT'] = 1;
                $this->tracking->updateGuideValue('PAYMENT');
            }
        }

        $this->data['payment_enabled'] = $gettingStarted['PAYMENT'];

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment', '', 'SSL'),
            'separator' => ' :: '
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('payments_providers'),
            'href' => $this->url->link('extension/payment/thirdparty', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['activated_gateways'] = array();
        $this->data['paypal'] = array();
        $this->data['credit_cards'] = array();
        $this->data['prepaid_cards'] = array();
        // $this->data['offline_methods'] = array();

        $this->initializer([
            'extension/payment'
        ]);

        $page = 1;
        $offset = 0;
        $perPage = 9;
        if (isset($this->request->get['page']) && $this->request->get['page'] > 1) {
            $page = $this->request->get['page'];

            $offset = ($page - 1) * $perPage;
        }

        $this->data['filterTypes'] = [];

        $installed = $types = $lookup = null;

        if (isset($this->request->get['types'])) {
            $this->data['filterTypes'] = $types = array_filter($this->request->get['types']);
        }

        if (isset($this->request->get['installed'])) {
            $this->data['installed'] = $installed = $this->request->get['installed'];
        }

        if (isset($this->request->get['lookup'])) {
            $this->data['lookup'] = $lookup = $this->request->get['lookup'];
        }

        // @read hint #1
        $queryData = [
//            'start' => $offset,
//            'limit' => $perPage,
//            'installed' => $installed,
            'types' => $types,
            'lookup' => $lookup,
        ];

        // 1- get recommended methods.

        $recommendedPaymentMethodsData = $this->payment->getRecommendedPaymentMethods();

        foreach ($recommendedPaymentMethodsData['data'] as $key => $extension) {
            $settingGroup = $this->config->get($extension['code']);
            if ($extension['code'] == "tabby") {
                $settingGroup = $this->config->get('tabby_pay_later');
            }
            $status = null;
            if ($settingGroup && is_array($settingGroup) === true) {
                $status = $settingGroup['status'];
            } else {
//                $result = $this->db->query("SELECT * from setting where `key` = '" . $extension['code'] . '_status' . "'");
//                $status =  $result->row["value"];
                $status = $this->config->get($extension['code'] . '_status');
            }

            $recommendedPaymentMethodsData['data'][$key]['installed']=($status != null ?  : false);
            $recommendedPaymentMethodsData['data'][$key]['supported_countries']= $this->getCountries($extension['supported_countries']);
            $recommendedPaymentMethodsData['data'][$key]['description']=html_entity_decode($extension['description']);
        }

        $this->data['recommended_payment_methods'] = $recommendedPaymentMethodsData;

        $extensions = $this->payment->getPaymentMethodsGrid(
            $queryData,
            $this->config->get('config_language_id')
        );

        $this->data['types'] = $this->payment->getTypes();

        $paymentMethods = [];

        // 2- add getCountries / add special_rate-supported_countries-price


        foreach ($extensions['data'] as $key => $extension) {
            $supported_countries =  $this->getCountries($extension['supported_countries']);
            $settingGroup = $this->config->get($extension['code']);
            if ($extension['code'] == "tabby") {
                $settingGroup = $this->config->get('tabby_pay_later');
            }
            $status = null;
            if ($settingGroup && is_array($settingGroup) === true) {
                $status = $settingGroup['status'];
                $sortorder = $settingGroup['sort_order'];
            } else {
               // $result = $this->db->query("SELECT * from setting where `key` = '" . $extension['code'] . '_status' . "'");
                $status =  /*$result->row["value"];*/$this->config->get($extension['code'] . '_status');
                $sortorder = $this->config->get($extension['code'] . '_sort_order');
            }

            /*
             * @hint #1
             * a temperoray way to handle status and install fields, this way we will not rely on the LIMIT clause
             * in the mysql server side ( in extension/payment model), and rather we will filter the array in the PHP
             * side untill we seperate the payment settings in a separated table
             */

            if (isset($this->request->get['enabled']) || isset($this->request->get['disabled']) || isset($this->request->get['installed'])) {
                if (isset($this->request->get['enabled']) && $this->request->get['enabled'] == 1) {
                    $installedStat = ($status != null ? true : false);
                    $this->data['enabled'] = $this->request->get['enabled'];
                    if($extension['code'] == "paypal" || $extension['code'] == "باي بال") {

                        $result = $this->config->get("paypal_account_connected");
                        
                        if($result == 1) {
                            $installedStat = true;
                        }
                    }
                    if ($status == 1) {
                        $paymentMethods[$key] = [
                            'code' => $extension['code'],
                            'title' => $extension['title'],
                            'description' => substr(html_entity_decode($extension['description']), 0, 300),
                            'image' => $extension['image'],
                            'image_alt' => $extension['image_alt'],
                            'installed' => $installedStat,
                            'type' => $extension['type'],
                            'status' => $status,
                            'id' => $extension['pm_id'],
                            'special_rate'=>$extension['special_rate'],
                            'supported_countries'=>$supported_countries,
                            'price'=>$extension['price']
                        ];
                    }

                }

                if (isset($this->request->get['disabled']) && $this->request->get['disabled'] == 1) {

                    $this->data['disabled'] = $this->request->get['disabled'];
                    $installedStat = ($status != null ? true : false);

                    if($extension['code'] == "paypal" || $extension['code'] == "باي بال") {

                        $result = $this->config->get("paypal_account_connected");

                        if($result == 1) {
                            $installedStat = true;
                        }
                    }

                    if (is_null($status) == false && $status == 0) {
                        $paymentMethods[$key] = [
                            'code' => $extension['code'],
                            'title' => $extension['title'],
                            'description' => substr(html_entity_decode($extension['description']), 0, 300),
                            'image' => $extension['image'],
                            'image_alt' => $extension['image_alt'],
                            'installed' => $installedStat,
                            'type' => $extension['type'],
                            'status' => $status,
                            'id' => $extension['pm_id'],
                            'special_rate'=>$extension['special_rate'],
                            'supported_countries'=>$supported_countries,
                            'price'=>$extension['price']
                        ];
                    }

                }

                if (isset($this->request->get['installed']) && $this->request->get['installed'] == 1) {

                    $this->data['installed'] = $this->request->get['installed'];

                    $installedStat = ($status != null ? true : false);

                    if($extension['code'] == "paypal" || $extension['code'] == "باي بال") {

                        $result = $this->config->get("paypal_account_connected");

                        if($result == 1) {
                            $installedStat = true;
                        }
                    }

                    if (is_null($status) == false) {
                        $paymentMethods[$key] = [
                            'code' => $extension['code'],
                            'title' => $extension['title'],
                            'description' => substr(html_entity_decode($extension['description']), 0, 300),
                            'image' => $extension['image'],
                            'image_alt' => $extension['image_alt'],
                            'installed' => $installedStat,
                            'type' => $extension['type'],
                            'status' => $status,
                            'id' => $extension['pm_id'],
                            'special_rate'=>$extension['special_rate'],
                            'supported_countries'=>$supported_countries,
                            'price'=>$extension['price']
                        ];
                    }

                }

            } else {

                $installedStat = ($status != null ? true : false);

                if($extension['code'] == "paypal" || $extension['code'] == "باي بال") {

                    $result = $this->config->get("paypal_account_connected");

                    if($result == 1) {
                        $installedStat = true;
                    }
                }

                $paymentMethods[$key] = [
                    'code' => $extension['code'],
                    'title' => $extension['title'],
                    'description' => substr(html_entity_decode($extension['description']), 0, 300),
                    'image' => $extension['image'],
                    'image_alt' => $extension['image_alt'],
                    'installed' => $installedStat,
                    'type' => $extension['type'],
                    'status' => $status,
                    'id' => $extension['pm_id'],
                    'special_rate'=>$extension['special_rate'],
                    'supported_countries'=>$supported_countries,
                    'price'=>$extension['price']
                ];

            }
        }

        // @read hint #1
        //$paymentMethodsCount = count($paymentMethods);

        //$paymentMethods = array_slice($paymentMethods, $offset, $perPage);

        // 3- sort by installed-special_rate
        $sort_order=array();
        foreach ($paymentMethods as $key => $value) {
            $sort_order['installed'][$key] = $value['installed'];
            $sort_order['special_rate'][$key] = $value['special_rate'];
        }
        array_multisort($sort_order['installed'], SORT_DESC, $sort_order['special_rate'], SORT_DESC, $paymentMethods);

        $this->data['payment_methods'] = $paymentMethods;

        uasort($this->data['activated_gateways'], function ($a, $b) {
            return $a['list_sort_order'] - $b['list_sort_order'];
        });
        uasort($this->data['offline_methods'], function ($a, $b) {
            return $a['list_sort_order'] - $b['list_sort_order'];
        });
        uasort($this->data['prepaid_cards'], function ($a, $b) {
            return $a['list_sort_order'] - $b['list_sort_order'];
        });
        uasort($this->data['credit_cards'], function ($a, $b) {
            return $a['list_sort_order'] - $b['list_sort_order'];
        });
        uasort($this->data['paypal'], function ($a, $b) {
            return $a['list_sort_order'] - $b['list_sort_order'];
        });

        $get = $this->request->get;
        unset($get['page']);
        unset($get['ajaxish']);

        // 4- remove pagination

//        $pagination = new Pagination();
//        $pagination->total = $paymentMethodsCount;//$extensions['totalFiltered']; // @read hint #1
//        $pagination->page = $page;
//        $pagination->limit = $perPage;
//        $pagination->text = $this->language->get('text_pagination');
//        $pagination->url = $this->url->link('extension/payment', http_build_query($get) . '&page={page}', 'SSL');

       // $this->data['pagination'] = $pagination->render();

        if ( isset( $this->request->get['ajaxish'] ) )
        {
            $this->template = 'extension/payment_methods_snippet.expand';
        }
        else
        {
            $this->template = 'extension/payment/thirdparty.expand';
            $this->children = array(
                'common/header',
                'common/footer'
            );
        }


        $curLang = $this->config->get("config_admin_language");

        $supportLink = "";

        if($curLang == "en") {
            $supportLink = "https://support.expandcart.com/paypal";
        } else {
            $supportLink = "https://support.expandcart.com/hc/ar/articles/360020613040-%D8%A8%D8%A7%D9%8A-%D8%A8%D8%A7%D9%84";
        }

        $this->data["paypal_supportLink"] = $supportLink;
		$this->data['hidden_methods'] = $this->_hidden_methods;
		
        $this->response->setOutput($this->render_ecwig());
		
	}
	
	public function manual(){
        $this->language->load('extension/payment');

        $this->load->model('setting/setting');
        $gettingStarted = $this->model_setting_setting->getGuideValue("GETTING_STARTED");

        if($gettingStarted['PAYMENT'] != 1){
            $count = count($this->get_activated_payment_methods());
            if ($count > 1){
                $gettingStarted['PAYMENT'] = 1;
                $this->tracking->updateGuideValue('PAYMENT');
            }
        }

        $this->data['payment_enabled'] = $gettingStarted['PAYMENT'];

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment', '', 'SSL'),
            'separator' => ' :: '
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_manual_payment'),
            'href' => $this->url->link('extension/payment/manual', '', 'SSL'),
            'separator' => ' :: '
        );

        // $this->data['activated_gateways'] = array();
        // $this->data['paypal'] = array();
        // $this->data['credit_cards'] = array();
        // $this->data['prepaid_cards'] = array();
        $this->data['offline_methods'] = array();

        $this->initializer([
            'extension/payment'
        ]);

        $types = ['offline_methods'];
        // @read hint #1
        $queryData = [
//            'start' => $offset,
//            'limit' => $perPage,
//            'installed' => $installed,
            'types' => $types,
            'lookup' => $lookup,
        ];

        $extensions = $this->payment->getPaymentMethodsGrid(
            $queryData,
            $this->config->get('config_language_id')
        );

        $this->data['types'] = $this->payment->getTypes();

        $paymentMethods = [];

        // 2- add getCountries / add special_rate-supported_countries-price

        foreach ($extensions['data'] as $key => $extension) {
            $supported_countries =  $this->getCountries($extension['supported_countries']);
            $settingGroup = $this->config->get($extension['code']);
            $status = null;
            if ($settingGroup && is_array($settingGroup) === true) {
                $status = $settingGroup['status'];
                $sortorder = $settingGroup['sort_order'];
            } else {
                $status = $this->config->get($extension['code'] . '_status');
                $sortorder = $this->config->get($extension['code'] . '_sort_order');
            }

            /*
             * @hint #1
             * a temperoray way to handle status and install fields, this way we will not rely on the LIMIT clause
             * in the mysql server side ( in extension/payment model), and rather we will filter the array in the PHP
             * side untill we seperate the payment settings in a separated table
             */

            if (isset($this->request->get['enabled']) || isset($this->request->get['disabled']) || isset($this->request->get['installed'])) {
                if (isset($this->request->get['enabled']) && $this->request->get['enabled'] == 1) {

                    $this->data['enabled'] = $this->request->get['enabled'];

                    if ($status == 1) {
                        $paymentMethods[$key] = [
                            'code' => $extension['code'],
                            'title' => $extension['title'],
                            'description' => substr(html_entity_decode($extension['description']), 0, 300),
                            'image' => $extension['image'],
                            'image_alt' => $extension['image_alt'],
                            'installed' => ($status != null ? true : false),
                            'type' => $extension['type'],
                            'status' => $status,
                            'id' => $extension['pm_id'],
                            'special_rate'=>$extension['special_rate'],
                            'supported_countries'=>$supported_countries,
                            'price'=>$extension['price']
                        ];
                    }

                }

                if (isset($this->request->get['disabled']) && $this->request->get['disabled'] == 1) {

                    $this->data['disabled'] = $this->request->get['disabled'];

                    if (is_null($status) == false && $status == 0) {
                        $paymentMethods[$key] = [
                            'code' => $extension['code'],
                            'title' => $extension['title'],
                            'description' => substr(html_entity_decode($extension['description']), 0, 300),
                            'image' => $extension['image'],
                            'image_alt' => $extension['image_alt'],
                            'installed' => ($status != null ? true : false),
                            'type' => $extension['type'],
                            'status' => $status,
                            'id' => $extension['pm_id'],
                            'special_rate'=>$extension['special_rate'],
                            'supported_countries'=>$supported_countries,
                            'price'=>$extension['price']
                        ];
                    }

                }

                if (isset($this->request->get['installed']) && $this->request->get['installed'] == 1) {

                    $this->data['installed'] = $this->request->get['installed'];

                    if (is_null($status) == false) {
                        $paymentMethods[$key] = [
                            'code' => $extension['code'],
                            'title' => $extension['title'],
                            'description' => substr(html_entity_decode($extension['description']), 0, 300),
                            'image' => $extension['image'],
                            'image_alt' => $extension['image_alt'],
                            'installed' => ($status != null ? true : false),
                            'type' => $extension['type'],
                            'status' => $status,
                            'id' => $extension['pm_id'],
                            'special_rate'=>$extension['special_rate'],
                            'supported_countries'=>$supported_countries,
                            'price'=>$extension['price']
                        ];
                    }

                }

            } else {
                $paymentMethods[$key] = [
                    'code' => $extension['code'],
                    'title' => $extension['title'],
                    'description' => substr(html_entity_decode($extension['description']), 0, 300),
                    'image' => $extension['image'],
                    'image_alt' => $extension['image_alt'],
                    'installed' => ($status != null ? true : false),
                    'type' => $extension['type'],
                    'status' => $status,
                    'id' => $extension['pm_id'],
                    'special_rate'=>$extension['special_rate'],
                    'supported_countries'=>$supported_countries,
                    'price'=>$extension['price']
                ];
            }
        }

        // @read hint #1
        //$paymentMethodsCount = count($paymentMethods);

        //$paymentMethods = array_slice($paymentMethods, $offset, $perPage);

        // 3- sort by installed-special_rate
        $sort_order=array();
        foreach ($paymentMethods as $key => $value) {
            $sort_order['installed'][$key] = $value['installed'];
            $sort_order['special_rate'][$key] = $value['special_rate'];
        }
        array_multisort($sort_order['installed'], SORT_DESC, $sort_order['special_rate'], SORT_DESC, $paymentMethods);

        $this->data['payment_methods'] = $paymentMethods;

        uasort($this->data['activated_gateways'], function ($a, $b) {
            return $a['list_sort_order'] - $b['list_sort_order'];
        });
        uasort($this->data['offline_methods'], function ($a, $b) {
            return $a['list_sort_order'] - $b['list_sort_order'];
        });
        uasort($this->data['prepaid_cards'], function ($a, $b) {
            return $a['list_sort_order'] - $b['list_sort_order'];
        });
        uasort($this->data['credit_cards'], function ($a, $b) {
            return $a['list_sort_order'] - $b['list_sort_order'];
        });
        uasort($this->data['paypal'], function ($a, $b) {
            return $a['list_sort_order'] - $b['list_sort_order'];
        });


        $get = $this->request->get;
        unset($get['page']);
        unset($get['ajaxish']);

        // 4- remove pagination

//        $pagination = new Pagination();
//        $pagination->total = $paymentMethodsCount;//$extensions['totalFiltered']; // @read hint #1
//        $pagination->page = $page;
//        $pagination->limit = $perPage;
//        $pagination->text = $this->language->get('text_pagination');
//        $pagination->url = $this->url->link('extension/payment', http_build_query($get) . '&page={page}', 'SSL');

       // $this->data['pagination'] = $pagination->render();

		 $this->template = 'extension/payment/manual.expand';
            $this->children = array(
                'common/header',
                'common/footer'
            );
        
        $this->response->setOutput($this->render_ecwig());
		
    }
    public function install()
    {
        $this->language->load('extension/payment');

        if (!$this->user->hasPermission('modify', 'extension/payment')) {
            $this->session->data['error'] = $this->language->get('error_permission');

            $this->redirect($this->url->link('extension/payment', '', 'SSL'));
        } else {
            $this->load->model('setting/extension');
            $this->initializer(['extensionPayment' => 'extension/payment']);

            if ($this->extensionPayment->isInstalled($this->request->get['extension'])) {
                $this->redirect(
                    $this->url->link(
                        'extension/payment/activate',
                        'code=' . $this->request->get['extension'].
                        '&activated=0&payment_company='.$this->request->get['payment_company'],
                        'SSL'
                    )
                );
            }

            $this->model_setting_extension->install('payment', $this->request->get['extension']);

            $this->load->model('user/user_group');

            $this->model_user_user_group->addPermission(
                $this->user->getId(),
                'access', 'payment/' . $this->request->get['extension']
            );
            $this->model_user_user_group->addPermission(
                $this->user->getId(),
                'modify', 'payment/' . $this->request->get['extension']
            );

            require_once(DIR_APPLICATION . 'controller/payment/' . $this->request->get['extension'] . '.php');

            $class = 'ControllerPayment' . str_replace('_', '', $this->request->get['extension']);
            $class = new $class($this->registry);

            if (method_exists($class, 'install')) {
                $class->install();
            }

            //$this->url->link('payment/' . $this->request->get['extension'], '', 'SSL'));
            // 5- remove old methods link
            $this->redirect(
                $this->url->link(
                    'extension/payment/activate',
                    'code=' . $this->request->get['extension'].
                    '&activated=0&payment_company='.$this->request->get['payment_company'],
                    'SSL'
                )
            );
        }
    }

    public function deactivate()
    {
        $this->language->load('extension/payment');

        if (!$this->user->hasPermission('modify', 'extension/payment')) {
            $this->session->data['error'] = $this->language->get('error_permission');

            $this->redirect($this->url->link('extension/payment', '', 'SSL'));
        } else {
            $this->load->model('setting/setting');

			/**
             *  switch case used if want to excute extra actions
             *  while deactivate the module
             */
            switch ($this->request->get['psid']){
                case "urway":
                    $this->load->model('payment/urway');
                    $this->model_payment_urway->uninstall();
                    break;
                case "iyzico_checkout_form":
                    $this->load->model('payment/iyzico_checkout_form');
                    $this->model_payment_iyzico_checkout_form->uninstall();
                    break;
                case "tamara":
                    $this->load->model('payment/tamara');
                    $this->model_payment_tamara->uninstall();
                    break;
                case "tabby":
                    $this->load->model('payment/tabby');
                    $this->model_payment_tabby->uninstall();
                    break;
                case "paypal":
                    $this->load->model('payment/paypal');
                    $this->model_payment_paypal->uninstall();
                    break;
                default:

                    break;
            }
			
            $modernPayment = $this->config->get($this->request->get['psid']);

            if ($modernPayment) {

                $this->load->model('extension/payment');

                $this->model_extension_payment->deleteSettings($this->request->get['psid']);
            } else {

                $this->model_setting_setting->deleteSetting($this->request->get['psid']);

            }



            $this->session->data['successDe'] = $this->language->get('entry_deactivate_success');

            

            $this->load->model('setting/extension');
            $this->model_setting_extension->uninstall('payment', $this->request->get['psid']);

            $this->redirect($this->url->link('extension/payment', '', 'SSL'));
        }
    }

    public function disable()
    {
        $data = array();

        $this->language->load('extension/payment');

        if (!$this->user->hasPermission('modify', 'extension/payment')) {
            $data['error'] = $this->language->get('error_permission');
        } else {
            $this->load->model('setting/setting');

            $modernPayment = $this->config->get($this->request->get['psid']);

            if ($modernPayment) {
                $modernPayment['status'] = "0";
                $this->model_setting_setting->insertUpdateSetting(
                    'payment', [$this->request->get['psid'] => $modernPayment]
                );
            } else {
                $this->model_setting_setting->editSettingValue(
                    $this->request->get['psid'],
                    $this->request->get['psid'] . '_status',
                    '0'
                );
            }

            $data['success'] = "true";
        }

        $this->response->setOutput(json_encode($data));
    }

    public function enable()
    {
        $data = array();

        $this->language->load('extension/payment');

        if (!$this->user->hasPermission('modify', 'extension/payment')) {
            $data['error'] = $this->language->get('error_permission');
        } else {
            $this->load->model('setting/setting');

            $modernPayment = $this->config->get($this->request->get['psid']);

            if ($modernPayment) {
                $modernPayment['status'] = "1";
                $this->model_setting_setting->insertUpdateSetting(
                    'payment', [$this->request->get['psid'] => $modernPayment]
                );
            } else {
                $this->model_setting_setting->editSettingValue(
                    $this->request->get['psid'],
                    $this->request->get['psid'] . '_status',
                    '1'
                );
            }

            $data['success'] = "true";
        }

        $this->response->setOutput(json_encode($data));
    }

    public function uninstall()
    {
        $this->language->load('extension/payment');

        if (!$this->user->hasPermission('modify', 'extension/payment')) {
            $this->session->data['error'] = $this->language->get('error_permission');

            $this->redirect($this->url->link('extension/payment', '', 'SSL'));
        } else {
            $this->load->model('setting/extension');
            $this->load->model('setting/setting');

            $this->model_setting_extension->uninstall('payment', $this->request->get['extension']);

            $this->model_setting_setting->deleteSetting($this->request->get['extension']);

            require_once(DIR_APPLICATION . 'controller/payment/' . $this->request->get['extension'] . '.php');

            $class = 'ControllerPayment' . str_replace('_', '', $this->request->get['extension']);
            $class = new $class($this->registry);

            if (method_exists($class, 'uninstall')) {
                $class->uninstall();
            }

            $this->redirect($this->url->link('extension/payment', '', 'SSL'));
        }
    }
    // 6- create activate link function
    public function activate() {

        //$this->request->get['page']
        if (!isset($this->request->get['code']) ||empty($this->request->get['code'])){
            return;
        }
        $this->load->language('payment/'.$this->request->get['code']);
        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('extension/payment', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'    => !isset($this->request->get['activated']) ?
                $this->language->get('breadcrumb_insert') :
                $this->language->get('breadcrumb_update'),
            'href'      =>"",
            'separator' => ' :: '
        );

        $this->getForm();
    }

    public function getForm()
    {
        $code = $this->request->get['code'];
        if (count($this->request->post) > 0) {
            $this->data = array_merge($this->data, $this->request->post);
        }

        $this->data['cancel'] = $this->url->link('extension/payment', '', 'SSL');

        $this->load->model('setting/store');

        $this->data['stores'] = $this->model_setting_store->getStores();

        $this->load->model('extension/payment');

        $payment_method_data = $this->model_extension_payment->getPaymentMethodData($code);

        $payment_method_data['supported_countries'] = $this->getCountries($payment_method_data['supported_countries']);
        $payment_method_data['description']=html_entity_decode($payment_method_data['description']);
        $payment_method_data['account_creation_steps']=html_entity_decode($payment_method_data['account_creation_steps']);
        $payment_method_data['company_requirements']=  html_entity_decode($payment_method_data['company_requirements']);
        $payment_method_data['individual_requirements']= html_entity_decode($payment_method_data['individual_requirements']);

        if($payment_method_data["code"] == "paypal") {

            $this->data["isPaypal"] = true;

            $isPaypalZero = (!empty($this->config->get("isPaypalZero"))) ? $this->config->get("isPaypalZero") : 0;

            $this->data["isPaypalZero"] = $isPaypalZero;

            $this->data["paypal_account_connected"] = (!empty($this->config->get("paypal_account_connected"))) ? 1 : 0;
        } else {
            $this->data["isPaypal"] = false;
        }

        $this->data['payment_method_data'] = $payment_method_data;

        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            $this->data['store_url'] = HTTPS_CATALOG;
        } else {
            $this->data['store_url'] = HTTP_CATALOG;
        }

        // installed = 0 or 1

        $settingGroup = $this->config->get($code);
        
        if ($code == "tabby") {
            $settingGroup = $this->config->get('tabby_pay_later');    
        }

        $status = null;
        if ($settingGroup && is_array($settingGroup) === true) {
            $status = $settingGroup['status'];
        } else {
//            $result = $this->db->query("SELECT * from setting where `key` = '" . $code . '_status' . "'");
//            $status =  $result->row["value"];
            $status = $this->config->get($code . '_status');
        }

        $activated= 0;
        if (!is_null($status) || $this->request->get['activated']){
            $activated= 1;
        }

        $this->data['activated'] = $activated;

        $this->data['status'] = $status ;

        // delivery_company = 0 or 1

        $this->data['payment_company'] = $this->request->get['payment_company'];

        // link ex : http://qaz123.expandcart.com/admin/extension/payment/activate?activated=1&delivery_company=0&code=pickup

        try {

            $this->data['payment_form_inputs'] = $this->getChild('payment/' . $code);
        }
        catch (\ExpandCart\Foundation\Exceptions\FileException $e) {
            $this->redirect($this->url->link('extension/payment', '', 'SSL'));
        }
      
        $this->data["code"] = $code;
        $this->template = 'extension/payment_form.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    /**
     *   Get all activated payment methods.
     *
     *   @return array $extensions.
     */
    private function get_activated_payment_methods()
    {
        $this->load->model('setting/extension');
        $extensions = $this->model_setting_extension->getInstalled('payment');

        foreach ($extensions as $key => $value) {
            // determine if the extension is installed or not.
            // same check is used on `admin/controller/extension/payment.php:112`.

            $settings = $this->config->get($value);
            if ($settings && is_array($settings) == true) {
                $status = $settings['status'];
            } else {
                $status = $this->config->get($value . '_status');
            }

            if (!$status) {
                unset($extensions[$key]);
            }
        }

        return $extensions;
    }
}

?>
