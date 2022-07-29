<?php

use \Firebase\JWT\JWT;
use ExpandCart\Foundation\Support\Hubspot;
class ControllerCommonBase extends Controller {
    private $settings_routes;

    private $settings_entry_route = 'setting/store_general';

    protected $navbar = array();

    private $errors =array();

    private $plan_id = PRODUCTID;

    private $products_limit = null;

    private $users_limit = null;

    public function __construct($registry) {
        parent::__construct($registry);
        $this->load->model('plan/trial');
        $trial = $this->model_plan_trial->getLastTrial();
        if ($trial){
            $this->plan_id = $trial['plan_id'];
        }

        $this->products_limit =  $this->genericConstants["plans_limits"][$this->plan_id]['products_limit'];

        $this->users_limit =  $this->genericConstants["plans_limits"][$this->plan_id]['users_limit'];

        $this->products_limit = $this->genericConstants["plans_limits"][$this->plan_id]['products_limit'];

        if ($this->config->get('plan_downgrade_id') != $this->plan_id){

            if ($this->users_limit && ($this->config->get('platform_version') > 1.2 || $this->plan_id == 3 )){
                $this->load->model("user/user");
                $this->model_user_user->disableTrialUsers($this->users_limit);
            }

            if ($this->products_limit){
                $this->load->model('catalog/product');
                $this->model_catalog_product->disableTrialProducts($this->products_limit);
            }

            $this->load->model('setting/setting');

            $data['plan_downgrade_id']=$this->plan_id;
            $this->model_setting_setting->insertUpdateSetting('plan', $data);

        }else{
            $this->load->model('setting/setting');
            $this->model_setting_setting->deleteSetting('plan');
        }
    }

    public function enforceSorting($arr1, $arr2) {
        if (array_key_exists('sorting', $arr1) && array_key_exists('sorting', $arr2)) {
            if (!empty($arr1['sorting']) && !empty($arr2['sorting'])) {
                if ($arr1['sorting'] == $arr2['sorting']) {
                    return 0;
                }
                return ($arr1['sorting'] <= $arr2['sorting']) ? -1 : 1;
            }
        }
    }

    private function queryNativeMobileApp() {
        $queryNativeMobileApp = $this->db->query("SELECT * FROM " . DB_PREFIX . "ectemplate WHERE `CodeName` = 'mobile-app'");
        if ($queryNativeMobileApp->num_rows) {
            $route = $this->url->link('meditor/meditor', '', 'SSL');
        } else {
            $route = $this->url->link('module/mobile_app', '', 'SSL');
        }
        return $route;
    }

    private function checkAbandonedCartModule()
    {
        $this->initializer([
            'AbandonedCart' => 'module/abandoned_cart/settings'
        ]);

        return $this->AbandonedCart->isActive();
    }

//    private function checkProductAttachmentsModule()
//    {
//        $this->initializer([
//            'ProductAttachments' => 'module/product_attachments'
//        ]);
//
//        return $this->ProductAttachments->isActive();
//    }

    private function checkAmazonModule()
    {
        return \Extension::isInstalled('wk_amazon_connector');
    }

    private function checkFacebookImportModule()
    {
        return \Extension::isInstalled('facebook_import');
    }

    private function checkAliexpressDropshippingModule()
    {
        $this->initializer([
            'aliexpress' => 'module/aliexpress_dropshipping/settings'
        ]);

        return $this->aliexpress->isActive();
    }

    private function affiliatesAppIsActive()
    {
        $this->initializer([
            'Affiliates' => 'module/affiliates'
        ]);

        return $this->Affiliates->isActive();
    }

    private function renderMenuObject($menu) {
        $menu['key'] = $menu['name'];
        $menu['name'] = $this->language->get(trim(str_replace(' ', '_', $menu['name'])));
        if (is_array($menu['route'])) {

            if (method_exists($this, $menu['route']['callback'])) {
                $route = $this->{$menu['route']['callback']}();
            }
        } else {
            $route = $this->url->link($menu['route'],null, 'SSL');
        }

        if (empty($menu['condition'])) {
            $condition = true;
        } else {
            if (method_exists($this, $menu['condition'])) {
                $condition = $this->{$menu['condition']}();
            }
        }

        if (isset($condition) && $condition == true) {
            $route .= !empty($menu['getPortion']) ? '&' . trim($menu['getPortion']) : '';
            $route = str_replace('amp;', '', $route);
            $menu['route'] = $route;
            $menu['stickerText'] = $this->language->get(trim(str_replace(' ', '_', $menu['stickerText'])));
            return $menu;
        }

        return false;
    }


    private function shouldIRender( $menu )
    {
        if ( in_array($menu['route'], ['', '#']) )
        {
            $is_display = false;

            foreach ( $menu['sub'] as $sub )
            {
                $is_display = $is_display || $this->shouldIRender($sub);
            }
            return $is_display;
        }
        else if ( $menu['route'] == $this->settings_entry_route )
        {
            $user_perms = $this->user->getPermissions( 'access' );

            foreach ( $user_perms as $perm )
            {
                if ( in_array($perm, $this->settings_routes) )
                {
                    return true;
                }
            }
        }
        else if(is_array($menu['route']))
        {
            return true;
        }
        else
        {
            $menuRoute = $menu['route'];
            $menuRouteParts = explode('/', $menu['route']);
            if(count($menuRouteParts) == 3) {
                $menuRoute = $menuRouteParts[0] . '/' . $menuRouteParts[1];
                $subRoute = $menuRouteParts[0] . '/' . $menuRouteParts[2];
                return ($this->user->hasPermission('access', $menuRoute) || $this->user->hasPermission('access', $subRoute));
            }
            return $this->user->hasPermission('access', $menuRoute);
        }
    }

    private function initiateNavbar() {
        $finalMenues = array();
        $this->settings_routes = Controller::getSettingsRoutes('all');

        if (file_exists('navbar.json')) {
            $this->language->load('navbar/navbar');
            $this->navbar = json_decode(file_get_contents('navbar.json'), true);

            foreach ($this->navbar as $index => $menu) {

                if ( ! $this->shouldIRender($menu) )
                {
                    continue;
                }
                
                //POS show settings
                //if( (POS_FLAG == 2 && $menu["name"] != 'mn_pos') || (POS_FLAG == 0 && $menu["name"] == 'mn_pos'))
                    //continue;
                if( (POS_FLAG == 2 && $menu["name"] == 'mn_design') ){
                    $menu['disabled'] = true;
                }
                ///////////////////

                if($menu["name"] == 'mn_multi_merchant' && !$this->data['ms_installed'])
                {
                    continue;
                }
                
                if($menu["name"] == 'mn_Payments' && !$this->config->get('expandpay_token'))
                {
                    continue;
                }

                if (!empty($menu['sub'])) {
                    $submenu = [];
                    foreach ($menu['sub'] as $index2 => $subMenu) {

                        //Check if abandoned carts is disabled and remove link
                        if($subMenu['name'] == 'mn_abandoned_cart'){
                            if(!$this->checkAbandonedCartModule()) {
                                $subMenu['route']="marketplace/app?id=44";
                            } else {
                                $subMenu['route']="sale/abandoned_cart/list";
                            }
                        }

//                        if($subMenu['name'] == 'mn_sub_downloads' && !$this->checkProductAttachmentsModule() ){
//                            continue;
//                        }

                        // todo remove this item from navbar.json after complete revoke

                        if($subMenu['name'] == 'mn_sub_attributes'){
                            continue;
                        }

                        if($subMenu['name'] == 'mn_sub_affiliates' && ! $this->affiliatesAppIsActive()){
                            continue;
                        }

                        if($subMenu['name'] == 'mn_sub_amazon' && $this->checkAmazonModule()){
                            $subMenu['route']="module/wk_amazon_connector";
                        }

                        if($subMenu['name'] == 'mn_sub_facebook_and_instagram' && $this->checkFacebookImportModule()){
                            $subMenu['route']="module/facebook_import";
                        }

                        if ( $this->shouldIRender($subMenu) )
                        {
                            if ($menuObject = $this->renderMenuObject($subMenu)) {
                                $submenu['sub'][$index2] = $menuObject;
                            }
                        }
                        else
                        {
                            unset($menu['sub'][$index2]);
                        }

                    }
                    uasort($submenu['sub'], array($this, 'enforceSorting'));
                    $menu['sub'] = array_values($submenu['sub']);
                }
                $finalMenues[] = $this->renderMenuObject($menu);
            }
            uasort($finalMenues, array($this, 'enforceSorting'));
            $finalMenues = array_values($finalMenues);

        } else {
            $finalMenues = array();
        }
        $this->navbar = $finalMenues;
    }

	protected function index() {
        if (PRODUCTID ==3 && ($this->options['routeString'] != "common/installation" && $this->options['routeString'] != "common/login")) {
            $this->load->model('setting/setting');
            $signup = $this->model_setting_setting->getGuideValue("SIGNUP");
            if (!isset($signup['QUESTIONER']) || $signup['QUESTIONER'] == 0){
                 $this->redirect($this->url->link('common/installation', '', 'SSL'));
            }
        }

        if (
            isset($_COOKIE['ec_whatsapp_redirect']) && $_COOKIE['ec_whatsapp_redirect'] == 'yes' &&
            ($this->options['routeString'] != "common/installation" && $this->options['routeString'] != "common/login")
        ) {
            setcookie('ec_whatsapp_redirect', null, -1, '/', '.expandcart.com', false, true);
            $this->redirect($this->url->link('marketplace/app/whatsapp', '', 'SSL'));
        }

        $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");
        $queryNativeMobileApp = $this->db->query("SELECT * FROM " . DB_PREFIX . "ectemplate WHERE `CodeName` = 'mobile-app'");

        if($queryMultiseller->num_rows) {
            $this->data['ms_installed'] = true;
            if(\Extension::isInstalled('trips')&&$this->config->get('trips')['status']==1){
                $this->data = array_merge($this->data, $this->load->language('module/trips'));  
             }
             else{$this->data = array_merge($this->data, $this->load->language('multiseller/multiseller'));}
            
            $lang = "view/javascript/multimerch/datatables/lang/" . $this->config->get('config_admin_language') . ".txt";
            $this->data['dt_language'] = file_exists(DIR_APPLICATION . $lang) ? "'$lang'" : "undefined";
        }

		$this->data['title'] = $this->document->getTitle(); 

        if(!is_null($this->document->getBasehref())) {
            $this->data['base'] = $this->document->getBasehref();
        }
		elseif (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			$this->data['base'] = HTTPS_SERVER;
		} else {
			$this->data['base'] = HTTP_SERVER;
		}

        $logoPath = 'logo_light.png';

        if (PARTNER_CODE != '') {
            $logoPath = 'partners/' . PARTNER_CODE . '/logo-backend.png';
        }

        $this->data['logoPath'] = $logoPath;
        $this->data['PARTNER_CODE'] = PARTNER_CODE;

		$this->data['description'] = $this->document->getDescription();
		$this->data['keywords'] = $this->document->getKeywords();
		$this->data['links'] = $this->document->getLinks();	
		$this->data['styles'] = $this->document->getStyles();
		$this->data['scripts'] = $this->document->getScripts();
		$this->data['lang'] = $this->language->get('code');
		$this->data['direction'] = $this->language->get('direction');
		
		$this->language->load('common/header');

		$this->data['heading_title'] = $this->language->get('heading_title');

        $this->data['text_lengthMenu'] = $this->language->get('text_lengthMenu');
        $this->data['text_zeroRecords'] = $this->language->get('text_zeroRecords');
        $this->data['text_info'] = $this->language->get('text_info');
        $this->data['text_infoEmpty'] = $this->language->get('text_infoEmpty');
        $this->data['text_infoFiltered'] = $this->language->get('text_infoFiltered');
        $this->data['text_search'] = $this->language->get('text_search');

		$this->data['text_affiliate'] = $this->language->get('text_affiliate');
		$this->data['text_attribute'] = $this->language->get('text_attribute');
		$this->data['text_attribute_group'] = $this->language->get('text_attribute_group');
		$this->data['text_backup'] = $this->language->get('text_backup');
		$this->data['text_banner'] = $this->language->get('text_banner');
		$this->data['text_catalog'] = $this->language->get('text_catalog');
		$this->data['text_category'] = $this->language->get('text_category');
		$this->data['text_confirm'] = $this->language->get('text_confirm');
		$this->data['text_country'] = $this->language->get('text_country');
		$this->data['text_coupon'] = $this->language->get('text_coupon');
		$this->data['text_currency'] = $this->language->get('text_currency');			
		$this->data['text_customer'] = $this->language->get('text_customer');
		$this->data['text_customer_group'] = $this->language->get('text_customer_group');
		$this->data['text_customer_field'] = $this->language->get('text_customer_field');
		$this->data['text_customer_ban_ip'] = $this->language->get('text_customer_ban_ip');
		$this->data['text_custom_field'] = $this->language->get('text_custom_field');
		$this->data['text_sale'] = $this->language->get('text_sale');
        $this->data['blog'] = $this->language->get('blog');
        $this->data['categories_name'] = $this->language->get('categories_name');

        $this->data['blog_categories'] = $this->language->get('blog_categories');
        $this->data['blog_post'] = $this->language->get('blog_post');


        $this->data['text_design'] = $this->language->get('text_design');
		$this->data['text_documentation'] = $this->language->get('text_documentation');
		$this->data['text_download'] = $this->language->get('text_download');
		$this->data['text_error_log'] = $this->language->get('text_error_log');
		$this->data['text_extension'] = $this->language->get('text_extension');
		$this->data['text_feed'] = $this->language->get('text_feed');
		$this->data['text_filter'] = $this->language->get('text_filter');
		$this->data['text_front'] = $this->language->get('text_front');
		$this->data['text_geo_zone'] = $this->language->get('text_geo_zone');
		$this->data['text_dashboard'] = $this->language->get('text_dashboard');
		$this->data['text_help'] = $this->language->get('text_help');
		$this->data['text_information'] = $this->language->get('text_information');
		$this->data['text_language'] = $this->language->get('text_language');
		$this->data['text_layout'] = $this->language->get('text_layout');
		$this->data['text_localisation'] = $this->language->get('text_localisation');
		$this->data['text_logout'] = $this->language->get('text_logout');
		$this->data['text_contact'] = $this->language->get('text_contact');
        $this->data['text_smshare_sms'] = $this->language->get('text_smshare_sms');
		$this->data['text_manager'] = $this->language->get('text_manager');
		$this->data['text_manufacturer'] = $this->language->get('text_manufacturer');
		$this->data['text_module'] = $this->language->get('text_module');
		$this->data['text_apps'] = $this->language->get('text_apps');
        $this->data['text_services'] = $this->language->get('text_services');
        $this->data['text_mobileapp'] = $this->language->get('text_mobileapp');
        $this->data['text_new'] = $this->language->get('text_new');
		$this->data['text_option'] = $this->language->get('text_option');
		$this->data['text_order'] = $this->language->get('text_order');
		$this->data['text_externalorder'] = $this->language->get('text_externalorder');
		$this->data['text_order_status'] = $this->language->get('text_order_status');
		$this->data['text_opencart'] = $this->language->get('text_opencart');
		$this->data['text_payment'] = $this->language->get('text_payment');
		$this->data['text_product'] = $this->language->get('text_product'); 
		$this->data['text_reports'] = $this->language->get('text_reports');
		$this->data['text_report_sale_order'] = $this->language->get('text_report_sale_order');
		$this->data['text_report_sale_tax'] = $this->language->get('text_report_sale_tax');
		$this->data['text_report_sale_shipping'] = $this->language->get('text_report_sale_shipping');
		$this->data['text_report_sale_return'] = $this->language->get('text_report_sale_return');
		$this->data['text_report_sale_coupon'] = $this->language->get('text_report_sale_coupon');
		$this->data['text_report_product_viewed'] = $this->language->get('text_report_product_viewed');
		$this->data['text_report_product_purchased'] = $this->language->get('text_report_product_purchased');
		$this->data['text_report_customer_online'] = $this->language->get('text_report_customer_online');
		$this->data['text_report_customer_order'] = $this->language->get('text_report_customer_order');
		$this->data['text_report_customer_reward'] = $this->language->get('text_report_customer_reward');
		$this->data['text_report_customer_credit'] = $this->language->get('text_report_customer_credit');
		$this->data['text_report_affiliate_commission'] = $this->language->get('text_report_affiliate_commission');
		$this->data['text_report_sale_return'] = $this->language->get('text_report_sale_return');
		$this->data['text_report_product_viewed'] = $this->language->get('text_report_product_viewed');
		$this->data['text_report_customer_order'] = $this->language->get('text_report_customer_order');
		$this->data['text_review'] = $this->language->get('text_review');
		$this->data['text_return'] = $this->language->get('text_return');
		$this->data['text_return_action'] = $this->language->get('text_return_action');
		$this->data['text_return_reason'] = $this->language->get('text_return_reason');
		$this->data['text_return_status'] = $this->language->get('text_return_status');
		$this->data['text_support'] = $this->language->get('text_support'); 
		$this->data['text_shipping'] = $this->language->get('text_shipping');		
		$this->data['text_setting'] = $this->language->get('text_setting');
		$this->data['text_stock_status'] = $this->language->get('text_stock_status');
		$this->data['text_system'] = $this->language->get('text_system');
		$this->data['text_tax'] = $this->language->get('text_tax');
		$this->data['text_tax_class'] = $this->language->get('text_tax_class');
		$this->data['text_tax_rate'] = $this->language->get('text_tax_rate');
		$this->data['text_total'] = $this->language->get('text_total');
		$this->data['text_user'] = $this->language->get('text_user');
		$this->data['text_user_group'] = $this->language->get('text_user_group');
		$this->data['text_users'] = $this->language->get('text_users');
		$this->data['text_voucher'] = $this->language->get('text_voucher');
		$this->data['text_voucher_theme'] = $this->language->get('text_voucher_theme');
		$this->data['text_weight_class'] = $this->language->get('text_weight_class');
		$this->data['text_length_class'] = $this->language->get('text_length_class');
        $this->data['text_profile'] = $this->language->get('text_profile');
        $this->data['text_online'] = $this->language->get('text_online');
        $this->data['text_templates'] = $this->language->get('text_templates');
        $this->data['text_myaccount'] = $this->language->get('text_myaccount');
        $this->data['text_billingaccount'] = $this->language->get('text_billingaccount');
        $this->data['text_supportticket'] = $this->language->get('text_supportticket');
        $this->data['text_knowledgebase'] = $this->language->get('text_knowledgebase');
        $this->data['text_trialperiod'] = $this->language->get('text_trialperiod');
        $this->data['text_upgradenow'] = $this->language->get('text_upgradenow');
        $this->data['text_customers'] = $this->language->get('text_customers');
        $this->data['text_marketing'] = $this->language->get('text_marketing');
        $this->data['text_advancedsettings'] = $this->language->get('text_advancedsettings');
        $this->data['text_domains'] = $this->language->get('text_domains');
        $this->data['text_productpromo'] = $this->language->get('text_productpromo');
        $this->data['text_customizeTheme'] = $this->language->get('text_customizeTheme');
        $this->data['text_aramexbulk'] = $this->language->get('text_aramexbulk');
        $this->data['text_personallassistant'] = $this->language->get('text_personallassistant');

        $this->load->model('setting/extension');
        $extensions = $this->model_setting_extension->getInstalled('module');
        $this->data['pavblog_installed'] = false;
        if(in_array("pavblog", $extensions)){
            $this->data['pavblog_installed'] = true;
        }

        $this->data['gazalblog_installed'] = false;
        if(in_array("news", $extensions)){
            $this->data['gazalblog_installed'] = true;
        }

        $this->data['text_pavblog_manage_cate'] = $this->language->get('text_pavblog_manage_cate');
        $this->data['text_pavblog_manage_blog'] = $this->language->get('text_pavblog_manage_blog');
        $this->data['text_pavblog_add_blog'] = $this->language->get('text_pavblog_add_blog');
        $this->data['text_pavblog_manage_comment'] = $this->language->get('text_pavblog_manage_comment');
        $this->data['text_pavblog_general_setting'] = $this->language->get('text_pavblog_general_setting');
        $this->data['text_pavblog_front_mods'] = $this->language->get('text_pavblog_front_mods');
        $this->data['text_pavblog_blog'] = $this->language->get('text_pavblog_blog');
        $this->data['text_pavblog_category'] = $this->language->get('text_pavblog_category');
        $this->data['text_pavblog_comment'] = $this->language->get('text_pavblog_comment');
        $this->data['text_pavblog_latest'] = $this->language->get('text_pavblog_latest');

		$this->data['text_zone'] = $this->language->get('text_zone');
		
		if (!$this->user->isLogged()) {
			$this->data['logged'] = '';
			
			$this->data['home'] = $this->url->link('common/login', '', 'SSL');
		}
        // if logged in
        else {
		    $this->initiateNavbar();
		    $this->data['navbar'] = $this->navbar;
		    //print_r($this->navbar);die();
			$this->data['logged'] = sprintf($this->language->get('text_logged'), $this->user->getUserName());

			$this->initiateAssistantGuide();

            $this->data['active_icon']= $this->getCurrentSelectedIcon();
            $this->data['active_menu_item_name'] = $this->getCurrentMenuItemName();
			$this->data['home'] = $this->url->link('common/home', '', 'SSL');
			$this->data['affiliate'] = $this->url->link('sale/affiliate', '', 'SSL');
			$this->data['attribute'] = $this->url->link('catalog/attribute', '', 'SSL');
			$this->data['attribute_group'] = $this->url->link('catalog/attribute_group', '', 'SSL');
			$this->data['backup'] = $this->url->link('tool/backup', '', 'SSL');
			$this->data['banner'] = $this->url->link('design/banner', '', 'SSL');
			$this->data['category'] = $this->url->link('catalog/category', '', 'SSL');
			$this->data['country'] = $this->url->link('localisation/country', '', 'SSL');
			$this->data['coupon'] = $this->url->link('sale/coupon', '', 'SSL');
			$this->data['currency'] = $this->url->link('localisation/currency', '', 'SSL');
			$this->data['customer'] = $this->url->link('sale/customer', '', 'SSL');
			$this->data['customer_fields'] = $this->url->link('sale/customer_field', '', 'SSL');
			$this->data['customer_group'] = $this->url->link('sale/customer_group', '', 'SSL');
			$this->data['customer_ban_ip'] = $this->url->link('sale/customer_ban_ip', '', 'SSL');
			$this->data['custom_field'] = $this->url->link('design/custom_field', '', 'SSL');
			$this->data['download'] = $this->url->link('catalog/download', '', 'SSL');
			$this->data['error_log'] = $this->url->link('tool/error_log', '', 'SSL');
			$this->data['feed'] = $this->url->link('extension/feed', '', 'SSL');
			$this->data['filter'] = $this->url->link('catalog/filter', '', 'SSL');
			$this->data['geo_zone'] = $this->url->link('localisation/geo_zone', '', 'SSL');
			$this->data['information'] = $this->url->link('catalog/information', '', 'SSL');
			$this->data['language'] = $this->url->link('localisation/language', '', 'SSL');
			$this->data['layout'] = $this->url->link('design/layout', '', 'SSL');
			$this->data['logout'] = $this->url->link('common/logout', '', 'SSL');
			$this->data['contact'] = $this->url->link('sale/contact', '', 'SSL');
            $this->data['smshare_sms'] = $this->url->link('sale/smshare_sms', '', 'SSL');
			$this->data['manager'] = $this->url->link('extension/manager', '', 'SSL');
			$this->data['manufacturer'] = $this->url->link('catalog/manufacturer', '', 'SSL');
			$this->data['module'] = $this->url->link('extension/module', '', 'SSL');
			$this->data['teditor'] = $this->url->link('teditor/designeditor', '', 'SSL');
			$this->data['apps'] = $this->url->link('marketplace/home', 'type=apps', 'SSL');
            $this->data['services'] = $this->url->link('marketplace/home', 'type=services', 'SSL');
            if($queryNativeMobileApp->num_rows) {
                $this->data['mobileapp'] = $this->url->link('meditor/meditor', '', 'SSL');
            } else {
                $this->data['mobileapp'] = $this->url->link('module/mobile_app', '', 'SSL');
            }

            $this->data['links'] = [
                'api' => $this->url->link(
                    'api/clients/browse',
                    '',
                    'SSL'
                )
            ];

            $this->data['option'] = $this->url->link('catalog/option', '', 'SSL');
			$this->data['order'] = $this->url->link('sale/order', '', 'SSL');
			$this->data['externalorder'] = $this->url->link('sale/externalorder', '', 'SSL');
            $this->data['bulk_shedule'] = $this->url->link('sale/aramex_bulk_schedule_pickup', '', 'SSL');
			$this->data['order_status'] = $this->url->link('localisation/order_status', '', 'SSL');
			$this->data['payment'] = $this->url->link('extension/payment', '', 'SSL');
			$this->data['product'] = $this->url->link('catalog/product', '', 'SSL');
			$this->data['report_sale_order'] = $this->url->link('report/sale_order', '', 'SSL');
			$this->data['report_sale_tax'] = $this->url->link('report/sale_tax', '', 'SSL');
			$this->data['report_sale_shipping'] = $this->url->link('report/sale_shipping', '', 'SSL');
			$this->data['report_sale_return'] = $this->url->link('report/sale_return', '', 'SSL');
			$this->data['report_sale_coupon'] = $this->url->link('report/sale_coupon', '', 'SSL');
			$this->data['report_product_viewed'] = $this->url->link('report/product_viewed', '', 'SSL');
			$this->data['report_product_purchased'] = $this->url->link('report/product_purchased', '', 'SSL');
			$this->data['report_customer_online'] = $this->url->link('report/customer_online', '', 'SSL');
			$this->data['report_customer_order'] = $this->url->link('report/customer_order', '', 'SSL');
			$this->data['report_customer_reward'] = $this->url->link('report/customer_reward', '', 'SSL');
			$this->data['report_customer_credit'] = $this->url->link('report/customer_credit', '', 'SSL');
			$this->data['report_affiliate_commission'] = $this->url->link('report/affiliate_commission', '', 'SSL');
			$this->data['review'] = $this->url->link('catalog/review', '', 'SSL');
			$this->data['return'] = $this->url->link('sale/return', '', 'SSL');
			$this->data['return_action'] = $this->url->link('localisation/return_action', '', 'SSL');
			$this->data['return_reason'] = $this->url->link('localisation/return_reason', '', 'SSL');
			$this->data['return_status'] = $this->url->link('localisation/return_status', '', 'SSL');
			$this->data['shipping'] = $this->url->link('extension/shipping', '', 'SSL');
			$this->data['setting'] = $this->url->link('setting/setting', '', 'SSL');
            $this->data['templates'] = $this->url->link('setting/template', '', 'SSL');

            $store = HTTP_CATALOG;
            if(POS_FLAG){
                $store = POS_FLAG == 2 ? $store . 'wkpos': $store;
            }
			$this->data['store'] = $store;
			$this->data['stock_status'] = $this->url->link('localisation/stock_status', '', 'SSL');
			$this->data['tax_class'] = $this->url->link('localisation/tax_class', '', 'SSL');
			$this->data['tax_rate'] = $this->url->link('localisation/tax_rate', '', 'SSL');
            $this->data['affiliate_s'] = $this->url->link('setting/store_affiliates', '', 'SSL');
			
            $this->data['total'] = $this->url->link('extension/total', '', 'SSL');
			$this->data['user'] = $this->url->link('user/user', '', 'SSL');
			$this->data['user_group'] = $this->url->link('user/user_permission', '', 'SSL');
			$this->data['voucher'] = $this->url->link('sale/voucher', '', 'SSL');
			$this->data['voucher_theme'] = $this->url->link('sale/voucher_theme', '', 'SSL');
			$this->data['weight_class'] = $this->url->link('localisation/weight_class', '', 'SSL');
			$this->data['length_class'] = $this->url->link('localisation/length_class', '', 'SSL');
            $this->data['flashBlog'] = $this->url->link('flash_blog/home', '', 'SSL');

            $this->data['pavblogs_category_mod'] = $this->url->link('module/pavblog/frontmodules', 'mod=pavblogcategory', 'SSL');
            $this->data['pavblogs_latest_comment_mod'] = $this->url->link('module/pavblog/frontmodules', 'mod=pavblogcomment', 'SSL');
            $this->data['pavblogs_latest_mod'] = $this->url->link('module/pavblog/frontmodules', 'mod=pavbloglatest', 'SSL');
            $this->data['pavblogs_category'] = $this->url->link('module/pavblog/category', '', 'SSL');
            $this->data['pavblogs_blogs'] = $this->url->link('module/pavblog/blogs', '', 'SSL');
            $this->data['pavblogs_add_blog'] = $this->url->link('module/pavblog/blog', '', 'SSL');
            $this->data['pavblogs_comments'] = $this->url->link('module/pavblog/comments', '', 'SSL');
            $this->data['pavblogs_general'] = $this->url->link('module/pavblog/modules', '', 'SSL');

            $this->data['zone'] = $this->url->link('localisation/zone', '', 'SSL');

            $queryAuctionModule = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'special_count_down'");
            if($queryAuctionModule->num_rows) {
                $this->data['auction'] = $this->url->link('catalog/auctionlist', '', 'SSL');
                $this->data['winner'] = $this->url->link('catalog/winner', '', 'SSL');
                $this->data['auction_block'] = $this->url->link('catalog/ipblock', '', 'SSL');
                $this->data['auction_blacklist'] = $this->url->link('catalog/blacklist', '', 'SSL');
                $this->data['text_auction'] = $this->language->get('text_auction');
                $this->data['text_auction_product'] = $this->language->get('text_auction_product');
                $this->data['text_auction_block'] = $this->language->get('text_auction_block');
                $this->data['text_auction_blacklist'] = $this->language->get('text_auction_blacklist');
                $this->data['text_winner'] = $this->language->get('text_winner');
            }

            $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

            if($queryMultiseller->num_rows) {
                $this->data['ms_link_sellers'] = $this->url->link('multiseller/seller', '', 'SSL');
                $this->data['ms_link_seller_groups'] = $this->url->link('multiseller/seller-group', '', 'SSL');
                $this->data['ms_link_attributes'] = $this->url->link('multiseller/attribute', '', 'SSL');
                $this->data['ms_link_products'] = $this->url->link('multiseller/product', '', 'SSL');
                $this->data['ms_link_payment'] = $this->url->link('multiseller/payment', '', 'SSL');
                $this->data['ms_link_transactions'] = $this->url->link('multiseller/transaction', '', 'SSL');
                $this->data['ms_link_settings'] = $this->url->link('module/multiseller', '', 'SSL');
                $this->data['ms_link_subscriptions'] = $this->url->link('multiseller/subscriptions', '', 'SSL');
            }

			$this->data['stores'] = array();
			
			$this->load->model('setting/store');
			
			$results = $this->model_setting_store->getStores();
			
			foreach ($results as $result) {
				$this->data['stores'][] = array(
					'name' => $result['name'],
					'href' => $result['url']
				);
			}

            $this->data['loggedUserProfile'] = $this->url->link('user/user/update', 'user_id=' . $this->user->getId(), 'SSL');
            $this->data['directStoreSettings'] = $this->url->link('setting/setting', '', 'SSL');
            $this->data['advancedsettings'] = $this->url->link('setting/advancedsetting', '', 'SSL');
            $this->data['domains'] = $this->url->link('setting/domainsetting', '', 'SSL');
            $this->data['reports'] = $this->url->link('report/reports', '', 'SSL');
            $this->data['productpromo'] = $this->url->link('extension/productpromo', '', 'SSL');
            $this->data['config_template'] = $this->config->get('config_template');

            $this->data['modbanner'] = $this->url->link('module/banner', '', 'SSL');
            $this->data['text_modbanner'] = $this->language->get('text_modbanner');

            $content_slider_templates = array("pav_asenti", "pav_bikestore", "pav_citymart", "pav_electronics", "pav_floral");
            $layer_slider_templates = array("pav_bestbuy", "pav_books", "pav_clothes", "pav_cosmetics", "pav_decor",
                "pav_digitalstore", "pav_dressstore", "pav_fashion", "pav_foodstore", "pav_furniture");

            if ($this->data['config_template'] == 'gazal') {
                $this->data['modthemecontrol'] = $this->url->link('module/gazal', '', 'SSL');
            }
            else {
                $this->data['modthemecontrol'] = $this->url->link('module/themecontrol', '', 'SSL');
            }

            if (in_array($this->data['config_template'], $content_slider_templates)) {
                $this->data['modslideshow'] = $this->url->link('module/pavcontentslider', '', 'SSL');
            }
            elseif (in_array($this->data['config_template'], $layer_slider_templates)) {
                $this->load->model('pavsliderlayer/slider');

                $sliderdata = $this->model_pavsliderlayer_slider->getFirstSlide();

                $this->data['modslideshow'] = $this->url->link('module/pavsliderlayer/layer&id=' . $sliderdata["id"] . '&group_id=' . $sliderdata["group_id"], '', 'SSL');
            }
            else {
                $this->data['modslideshow'] = $this->url->link('module/slideshow', '', 'SSL');
            }
            $this->data['text_modslideshow'] = $this->language->get('text_modslideshow');

            $this->data['modcustom'] = $this->url->link('module/pavcustom', '', 'SSL');
            $this->data['text_modcustom'] = $this->language->get('text_modcustom');

            $this->data['modblogpav'] = $this->url->link('module/pavblog', '', 'SSL');
            $this->data['text_modblogdashboard'] = $this->language->get('text_modblogdashboard');

            $this->data['modbloggazalnews'] = $this->url->link('catalog/news', '', 'SSL');
            $this->data['text_modbloggazalnews'] = $this->language->get('text_modbloggazalnews');

            $billingAccess = '0';

            if ($this->user->hasPermission('access', 'billingaccount/plans') && ENABLE_BILLING == '1') {
                $billingAccess = '1';
            }

            $this->data['billingAccess'] = $billingAccess;

            $this->load->model('billingaccount/common');

            # Define WHMCS URL & AutoAuth Key
            $whmcsurl = MEMBERS_LINK;
            $autoauthkey = MEMBERS_AUTHKEY;

            $langParam = '?language=English';

            if ($this->language->get('code') == 'ar') {
                $langParam = '?language=Arabic';
            }

            $timestamp = time(); # Get current timestamp
            $billingDetails = $this->model_billingaccount_common->getBillingDetails();
            $this->data['billingDetails'] = $billingDetails;
            $email = $billingDetails['email']; # Clients Email Address to Login
            $billingaccount = "clientarea.php" . $langParam;
            $supportticket = "submitticket.php" . $langParam;
            $knowledgebase = "knowledgebase.php" . $langParam;

            $hash = sha1($email.$timestamp.$autoauthkey); # Generate Hash

            $this->data['url_billingaccount'] = $whmcsurl."?email=$email&timestamp=$timestamp&hash=$hash&goto=".urlencode($billingaccount);
            //$this->data['url_supportticket'] = $whmcsurl."?email=$email&timestamp=$timestamp&hash=$hash&goto=".urlencode($supportticket);
            //$this->data['url_knowledgebase'] = $whmcsurl."?email=$email&timestamp=$timestamp&hash=$hash&goto=".urlencode($knowledgebase);
			
			/*
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
            $this->data['url_knowledgebase'] = $zd_location;

            $zd_location = "https://" . $zd_subdomain . ".zendesk.com/access/jwt?jwt=" . $zd_jwt . "&return_to=https://support.expandcart.com/hc/en-us/requests/new";
            $this->data['url_supportticket'] = $zd_location;
            */
			
			//we stop this block of code to generate URL only when click to avoid unique key issue 
			$this->data['url_supportticket'] = $this->url->link('account/support');
			$this->data['url_knowledgebase'] = $this->url->link('common/home/knowledgeBaseURL');

            $trialDaysLeft = $this->model_billingaccount_common->getTrialDaysLeft();

            $this->data['text_trialmessage'] = str_replace("%%noofDaysR%%", $trialDaysLeft, $this->language->get('text_trialmessage'));
            $this->data['trialDaysLeft'] = $trialDaysLeft;
            $this->data['currentplan'] = PRODUCTID;
			$this->data['widebot_username'] = BILLING_DETAILS_NAME ;
            $this->data['plan_trial_id'] = $this->plan_id;

            $this->data['client_suspended'] = (defined(CLIENT_SUSPEND));
            $this->data['ENABLE_MARKETING'] = ENABLE_MARKETING;
            $this->data['STORECODE'] = STORECODE;


//            if(
//                $this->options['routeString'] === 'catalog/product' ||
//                $this->options['routeString'] === 'catalog/product/insert' ||
//                $this->options['routeString'] === 'catalog/component/products'
//            )
//            {
//                $this->load->model('catalog/product');
//                $this->data['TOTAL_PRODUCTS_COUNT'] = $this->model_catalog_product->getTotalProductsCount();
//                $this->data['PRODUCTSLIMIT'] = $this->products_limit;
//                $this->data['KANAWAT_PRODUCTSLIMIT'] = KANAWAT_PRODUCTSLIMIT;
//
//                if (
//                    ($this->data['TOTAL_PRODUCTS_COUNT'] >= $this->products_limit && $this->plan_id == 3 ) ||
//                    (KANAWAT_PRODUCTSLIMIT != -1 && $this->data['TOTAL_PRODUCTS_COUNT'] >= $this->data['KANAWAT_PRODUCTSLIMIT'] && $this->plan_id  == 3)
//
//                ){
//                    $this->data['products_limit'] = $this->products_limit;
//                    $this->data['count_limit_reached'] = true;
//                }
//            }

            $this->data['url_upgradenow'] = $this->url->link('billingaccount/plans', '', 'SSL');

            $this->data['isCODCollector'] = $this->user->isCODCollector();

            $this->data['agentDetails'] = $this->model_billingaccount_common->getAgentDetails();

            $this->data['agentDetails']['displayname'] = $this->data['direction'] == 'rtl' ? $this->data['agentDetails']['displayname_ar'] : $this->data['agentDetails']['displayname_en'];
            $this->data['agentDetails']['coveragehtml'] = $this->data['direction'] == 'rtl' ? $this->data['agentDetails']['coveragehtml_ar'] : $this->data['agentDetails']['coveragehtml_en'];

            $this->data['platform_version'] = $this->config->get('platform_version');
            $this->data['current_version'] =  $this->genericConstants["platform_version"];

        }
		
		//$this->template = 'common/header.tpl';

        $this->load->model('setting/extension');
        $this->load->model('setting/setting');
        $extensions = $this->model_setting_extension->getInstalled('module');
        $pavblog_installed = false;
        if(in_array("pavblog", $extensions)){
            $pavblog_installed = true;
        }

        if($pavblog_installed) {
            if(isset($this->data['categories'])){
                $this->data['categories'][] = array(
                    'name'     => $this->language->get("text_blog"),
                    'children' => array(),
                    'column'   => 1,
                    'href'     => $this->url->link('pavblog/blogs', '')
                );
            }
        }
        $this->data["guide"] = $this->getGuide(
            isset($this->request->get['route']) ? $this->request->get['route'] : "common/home"
        );
        $this->disableGuide(
            isset($this->request->get['route']) ? $this->request->get['route'] : "common/home"
        );

        $this->load->model('setting/setting');
        $this->data['signupGuide'] = $this->model_setting_setting->getGuideValue("SIGNUP");
        //$this->render();

        //$this->data['notifications'] = $this->getNotifications();

        $this->load->model('marketplace/appservice');
        $inActiveTrials = $this->model_setting_extension->getInActiveTrials();
        foreach ($inActiveTrials as $inActiveTrial) {
            $this->model_setting_extension->removeTrial($inActiveTrial['extension_code']);
            if(!$this->model_marketplace_appservice->isAppCanBeInstalled($inActiveTrial['extension_code'])) {
                $this->model_setting_extension->uninstall('module', $inActiveTrial['extension_code']);
                $this->model_setting_setting->deleteSetting($inActiveTrial['extension_code']);

                // $this->getChild($content_url);
                try {
                    $this->getChild(sprintf('module/%s/uninstall', $inActiveTrial['extension_code']));
                } catch (\Error $e) {}

                // work around for aliexpress dropshipping application
                if ($inActiveTrial['extension_code'] == 'aliexpress_dropshipping') {
                    $this->model_setting_setting->deleteSetting('module_wk_dropship');
                    $this->model_setting_setting->deleteSetting('wk_dropship');
                }
            }
        }

        // top banner credit offers
        $this->load->model('account/store_credit');
        $this->load->model('account/invoice');
        $store_credits = $this->model_account_store_credit->getAvailableOffers([
            'banner_display' => true,
            'product_type' => 'plan'
        ]);
        $store_credit = false;
        if(count($store_credits)){

            // currency and amount
            $currency = "USD";
            $whmcs= new whmcs();
            $clientDetails = $whmcs->getClientDetails(WHMCS_USER_ID);
            $currenciesObject = $this->model_account_invoice->getCurrencies();
            $clientCurrencyId = $clientDetails['currency'];
            foreach($currenciesObject->currencies->currency as $c){
                if($c->id == $clientCurrencyId){
                    $currency = $c->code;
                    break;
                }
            }

            // plan id and plan name
            $plans = ['3', '53', '6', '8'];
            $plan_names = ['3' => 'free', '53' => 'professional', '6' => 'ultimate', '8' => 'enterprise'];
            $higher_plans = array_slice($plans, array_search(PRODUCTID, $plans));
            $store_credit_plan_ids = $store_credits[0]['plan_ids'] ?: $plans;

            // expiration time
            $now = new DateTime("now", new DateTimeZone('Africa/Cairo'));
            $end_date = new DateTime($store_credits[0]['expiration_date']);

            $store_credit = [];
            $store_credit['plan_id'] = array_values(array_intersect($higher_plans, $store_credit_plan_ids))[0];
            $store_credit['plan_name'] = $plan_names[$store_credit['plan_id']];
            $store_credit['cycle_name'] = $store_credits[0]['cycle'] == 1 ? 'monthly' : 'annually';
            $store_credit['amount'] = $store_credits[0]['amount'][$currency];
            $store_credit['currency'] = $currency;
            $store_credit['remaining_seconds'] = $end_date->getTimestamp() - $now->getTimestamp();
        }

        $this->data['store_credit'] = $store_credit;

        // When published a new login, force a custom template update for the first time.

        if (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList()) {
            $this->forceUpdateCurrentTemplate();
        }
    }

    private function getGuide($Route)
    {
        return false;
        /*try {
            $postFields = array(
                "UserName" => GUIDE_USERNAME,
                "Password" => GUIDE_PASSWORD,
                "StoreCode" => STORECODE,
                "Route" => $Route
            );
            $url = HTTP_CATALOG . "api/v1/guide";
            $ch = curl_init();
            $timeout = 0;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, true);
            $fields_string = json_encode($postFields);

            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($fields_string))
            );
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

            $rawdata = curl_exec($ch);
            curl_close($ch);

            return is_array(json_decode($rawdata)) ? $rawdata : false;
        } catch (Exception $ex) {
            return false;
        }*/
    }

    private function disableGuide($Route)
    {
        return false;
        /*try {
            $postFields = array(
                "UserName" => GUIDE_USERNAME,
                "Password" => GUIDE_PASSWORD,
                "StoreCode" => STORECODE,
                "Route" => $Route
            );
            $url = HTTP_CATALOG . "api/v1/guide/disable";
            $ch = curl_init();
            $timeout = 0;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, true);
            $fields_string = json_encode($postFields);

            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($fields_string))
            );
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

            $rawdata = curl_exec($ch);
            curl_close($ch);

            return $rawdata;
        } catch (Exception $ex) {
            return false;
        }*/
    }

    public function questioner() {
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $sellingChannel = $this->request->post['selling-channel'];
            $productSource = $this->request->post['product-source'];
            $previousWebsite = $this->request->post['previous-website'];
            $registeredCompany = $this->request->post['registered-company'];

            /*//################### AutoPilot Start #####################################
            try {
                $fields = array();
                $fields["string--Selling--Channel"] = $sellingChannel;
                $fields["string--Product--Source"] = $productSource;
                $fields["string--Previous--Website"] = $previousWebsite;
                $fields["string--Registered--Company"] = $registeredCompany;
                autopilot_UpdateContactCustomFields(BILLING_DETAILS_EMAIL, $fields);
            }
            catch (Exception $e) {  }
            //################### AutoPilot End #####################################*/

            //################### Freshsales Start #####################################
            try {
                FreshsalesAnalytics::init(array('domain' => 'https://' . FRESHSALES_SUBDOMAIN . '.freshsales.io', 'app_token' => FRESHSALES_TOKEN));

                FreshsalesAnalytics::identify(array(
                    'identifier' => BILLING_DETAILS_EMAIL,
                    'Selling Channel' => $sellingChannel,
                    'Product Source' => $productSource,
                    'Previous Website' => $previousWebsite,
                    'Registered Company' => $registeredCompany
                ));
            }
            catch (Exception $e) {  }
            //################### Freshsales End #####################################

            //################### Hubspot Start #####################################
            $productSource_MAP = [
                'Own Products' => 'ec_os_qui_ps_own_products',
                'Retail'=>'ec_os_qui_ps_retail',
                'Dropshipping' =>'ec_os_qui_ps_dropshipping',
                'Multi Merchant'=>'ec_os_qui_ps_multi_merchant',
                'Do Not Know'=>'ec_os_qui_ps_do_not_know'
            ];

            $selling_channel_MAP = [
                'Website'=>'ec_os_qui_sc_website',
                'Social Media' =>'ec_os_qui_sc_social_media',
                'Marketplaces'=>'ec_os_qui_sc_marketplaces',
                'Retail Store'=>'ec_os_qui_sc_retail_store',
                'All Channels'=>'ec_os_qui_sc_all_channels',
                'Not Selling'=>'ec_os_qui_sc_not_selling',
                'Building for Client'=>'ec_os_qui_sc_building_for_client',
                'Not Selling'=>'ec_os_qui_sc_research_purposes',
            ];

            $registeredCompany_MAP = [
                'Yes' => 'ec_os_qui_yes',
                'No'=>'ec_os_qui_no',
                'Not Yet' =>'ec_os_qui_not_yet ',
            ];

            Hubspot::tracking('pe25199511_os_questioneer_updated', [
                "ec_os_qui_product_source" => $productSource,
                "ec_os_qui_registered_company" => $registeredCompany,
                "ec_os_qui_selling_channel" => $sellingChannel,
            ]);

            Hubspot::updateContact([
                'ec_product_source' => $productSource_MAP[$productSource] ?? 'ec_ob_ps_own_products',
                'ec_selling_channel' => $selling_channel_MAP[$sellingChannel] ?? 'ec_ob_sc_website',
                'ec_ob_registered_company' => $registeredCompany_MAP[$registeredCompany] ?? 'ec_ob_rc_yes',
                'primary_email' => BILLING_DETAILS_EMAIL,
            ]);

           //################### Hubspot End #####################################

            $this->applyDefaultTemplate();

            $this->load->model('setting/setting');
            $this->model_setting_setting->editGuideValue("SIGNUP", "QUESTIONER", "1");
            $this->redirect($this->url->link('common/home'));
        }
    }

    private function validate()
    {
        $phone = $this->request->post['phone'];
        $time = $this->request->post['time'];

        if (!isset($this->request->post['no_call']) && $this->request->post['no_call'] !=1) {
            if (empty($phone) || utf8_strlen($phone) < 5 ) {
                $this->errors['phone'] = $this->language->get('error_entry_phone');;
            }

            if (empty($time)) {
                $this->errors['time'] = $this->language->get('error_entry_time');
            }

            if ($this->errors && !isset($this->errors['error'])) {
                $this->errors['warning'] = $this->language->get('error_warning');
            }
        }

        return $this->errors ? false : true;
    }

    //validateDemoForm
    private function validateDemoForm()
    {
        $phone = $this->request->post['phone'];

        if (empty($phone) || utf8_strlen($phone) < 5 ) {
            $this->errors['phone'] = $this->language->get('error_entry_phone');;
        }

        if ($this->errors && !isset($this->errors['error'])) {
            $this->errors['warning'] = $this->language->get('error_warning');
        }

        return $this->errors ? false : true;
    }

    public function requestCall() {
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->model('setting/setting');

            if(!$this->validate()){
                $result_json['success'] = '0';
                $result_json['errors'] = $this->errors;
                $this->response->setOutput(json_encode($result_json));
                return;
            }

            $this->model_setting_setting->editGuideValue('SIGNUP', 'REQUEST_CALL', '1');

            //no_request_call_needed=1
            if (isset($this->request->post['no_call']) && $this->request->post['no_call']==1){
                $no_call_needed = $this->request->post['no_call'];
            }else{
                $no_call_needed= null;
                $config['config_telephone']= $this->request->post['clientphone'];
                $this->model_setting_setting->insertUpdateSetting('config',$config);

                $whmcs= new whmcs();
                $userId= WHMCS_USER_ID;
                $whmcs->updateClientPhone($userId,$config['config_telephone']);
            }

            $phone = $this->request->post['clientphone'];
            $time = $this->request->post['time'];
            //$previousWebsite = $this->request->post['previous-website'];
            //$registeredCompany = $this->request->post['registered-company'];

            /***************** Start ExpandCartTracking #347690  ****************/

            // send mixpanel consultation booked event
            $this->load->model('setting/mixpanel');
            $this->model_setting_mixpanel->trackEvent('Consultation Booked',['Phone'=>$phone, 'Consultation Time' => $time]);
            $this->model_setting_mixpanel->updateUser([
                '$preferred call time'      => $time,
                '$phone'                    => $this->config->get('config_telephone')
            ]);
            // send amplitude consultation booked event
            $this->load->model('setting/amplitude');
            $this->model_setting_amplitude->trackEvent('Consultation Booked',['Phone'=>$phone, 'Consultation Time' => $time]);
            $this->model_setting_amplitude->updateUser([
                'preferred call time'       => $time,
                'phone'                     => $this->config->get('config_telephone')
            ]);
            /***************** End ExpandCartTracking #347690  ****************/

            /*//################### AutoPilot Start #####################################
            $fields = array();

            if ($no_call_needed == null){
                  $fields["string--Phone"] = $phone;
                  $fields["string--Preferred--Call--Time"] = $time;
                  if(!isset($this->request->post['no_call'])) { //already installed
                      $fields["string--Consultation--Booked"] = "Yes";
                      $leadData = array(
                          'identifier' => BILLING_DETAILS_EMAIL,
                          'Phone' => $phone,
                          'Preferred Call Time'=>$time,
                          'Consultation Booked'=>"Yes"
                      );
                  } else { //in installation
                      $fields["string--Do--Not--Call"] = "No";
                      $leadData = array(
                          'identifier' => BILLING_DETAILS_EMAIL,
                          'Phone' => $phone,
                          'Preferred Call Time'=>$time,
                          'Do Not Call'=>"No"
                      );
                  }

            }else{
                $fields["string--Do--Not--Call"] = "Yes";
                $leadData = array(
                    'identifier' => BILLING_DETAILS_EMAIL,
                    "Do Not Call" => "Yes",
                );
            }
            try {

                autopilot_UpdateContactCustomFields(BILLING_DETAILS_EMAIL, $fields, $phone);
            }
            catch (Exception $e) {  }
            //################### AutoPilot End  #####################################*/

            //################### Freshsales Start #####################################
            try {
                FreshsalesAnalytics::init(
                    array(
                        'domain' => 'https://' . FRESHSALES_SUBDOMAIN . '.freshsales.io',
                        'app_token' => FRESHSALES_TOKEN
                    )
                );

                FreshsalesAnalytics::identify(
                    $leadData
                );

            }
            catch (Exception $e) {}
            //################### Freshsales End #####################################

            //################### Hubspot Start #####################################
            
            Hubspot ::tracking('pe25199511_os_consultant_call_requested',
            ["os_ccri_phone_number"=>$phone,
            "os_ccri_preferred_time" =>$time
            ]);

           //################### Hubspot End #####################################

            $result_json['success'] = '1';
            $this->response->setOutput(json_encode($result_json));
            return;
        }
    }

    public function requestEnterpriseDemo() {
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            //$this->load->model('setting/setting');

            if(!$this->validateDemoForm()){
                $result_json['success'] = '0';
                $result_json['errors'] = $this->errors;
                $this->response->setOutput(json_encode($result_json));
                return;
            }

            //$this->model_setting_setting->editGuideValue('SIGNUP', 'REQUEST_DEMO', '1');

            $phone = $this->request->post['clientphone'];

            /*//################### AutoPilot Start #####################################
            $fields = array();
            $fields["string--Enterprise--Demo--Request"] = "Yes";
            $leadData = array(
                'identifier' => BILLING_DETAILS_EMAIL,
                'Phone' => $phone,
                'Enterprise Demo Request'=>"Yes",
                'Enterprise Demo Request Date' =>date('Y-m-d H:i:s')
            );
            try {

                autopilot_UpdateContactCustomFields(BILLING_DETAILS_EMAIL, $fields, $phone);
            }
            catch (Exception $e) {  }
            //################### AutoPilot End  #####################################*/

            //################### Freshsales Start #####################################
            try {
                FreshsalesAnalytics::init(
                    array(
                        'domain' => 'https://' . FRESHSALES_SUBDOMAIN . '.freshsales.io',
                        'app_token' => FRESHSALES_TOKEN
                    )
                );

                FreshsalesAnalytics::identify(
                    $leadData
                );

            }
            catch (Exception $e) {}
            //################### Freshsales End #####################################

            //################### Hubspot Start #####################################

            Hubspot ::tracking('pe25199511_os_enterprise_demo_requested',
            ["ec_os_edri_demo_phone"=>$phone,
             "ec_os_edri_demo_request_date" =>date('m/d/Y')
            ]);
           //################### Hubspot End #####################################

            $result_json['success'] = '1';
            $this->response->setOutput(json_encode($result_json));
            return;
        }
    }

    /**
     * Return the current selected icon base on the route string
     * @return mixed
     */
    private function getCurrentSelectedIcon()
    {
        $found_key= 0;

        foreach( $this->navbar as $key=> $arrayItem){
            if( stristr( $arrayItem['route'],  $this->options['routeString'] ) ){
                $found_key = $key;
            }
        }
        if (!$found_key){

            $routeString = $this->options['routeString'];
            $parts = explode('/',$routeString);
            if (count($parts) > 2){
                $routeString = $parts[0].'/'.$parts[1];
            }

            $routStringArray = explode("/",$routeString);
            $foundItems = $this->navbar;
            foreach($routStringArray as $string){
                $recentlyFound = [];
                foreach($foundItems as $key => $arrayItem){
                    if(stristr( $arrayItem['route'], $string ) || (isset($arrayItem['route_keywords']) && in_array($string, $arrayItem['route_keywords']))){
                        $recentlyFound[] = $key;
                    }
                }
                if(count($recentlyFound) == 1){
                    $found_key = $recentlyFound[0];
                    break;
                }
            }

            foreach ($this->navbar as $key => $item){
                if (count($item['sub']) > 0){
                    foreach ($item['sub'] as $value){
                        if ($value['route'] == $this->url->link($routeString)->format()){
                            $found_key = $key;
                        }
                    }
                }
            }
        }
        return  $this->navbar[$found_key]['icon'];
    }

    /**
     * get the name of the active menu item base on the url string
     * @return string|null
     */
    private function getCurrentMenuItemName(): ?string
    {
        $name= null;

        $routeString = $this->options['routeString'];
        $parts = explode('/',$routeString);
        if (count($parts) > 2){
            $routeString = $parts[0].'/'.$parts[1];
        }

        foreach( $this->navbar as $key=> $arrayItem){
            if( stristr( $arrayItem['route'],  $this->options['routeString'] ) ){
                $name = $arrayItem['name'];
            }
        }
        if (!$name){
            foreach ($this->navbar as $key => $item){
                if (count($item['sub']) > 0){
                    foreach ($item['sub'] as $value){
                        if (stristr($value['route'], $this->options['routeString'])){
                            $name = $value['name'];
                        }
                    }
                }
            }
        }

        return  $name;
    }

    private function applyDefaultTemplate(){
        $this->initializer([
            'templates/template',
            'archive' => 'templates/archive',
        ]);
        $codeName = $this->config->get('config_template');
        $template = $this->template->getTemplateByConfigName($codeName);

        if($template->external_template_id){
            if (file_exists(DIR_CUSTOM_TEMPLATE) == false || is_writable(DIR_CUSTOM_TEMPLATE) == false) {
                mkdir(DIR_CUSTOM_TEMPLATE, 0777);
                chmod(DIR_CUSTOM_TEMPLATE, 0777);
            }
            $theme = EXTERNAL_THEMES_PATH . $codeName . '.zip';
            $this->archive->open($theme);
            $this->archive->extract(DIR_CUSTOM_TEMPLATE)->close();
            rename(DIR_CUSTOM_TEMPLATE . $codeName . '/schema.json', DIR_CUSTOM_TEMPLATE . $codeName . '/' . $codeName .'.json');

            $this->template->applyTemplate($template);
        }
        else{
            $this->load->model('setting/setting');
            $this->model_setting_setting->changeTemplate($codeName);
        }
    }

    private function getNotifications(): array
    {

        $this->load->model('user/notifications');

        $notifications = $this->model_user_notifications->getLatestAdminNotifications(-1);

        if ($notifications == false) {
            return  [
                'alert' => 'No Notifications'
            ];
        }

        $unread = 0;

        foreach ($notifications as &$notification) {

            $notification['base_url']= $this->url->link('','', 'SSL')->format();

            if ($notification['notification_module']=="orders"){
                $notification['url']= $this->url->link('sale/component/orders', '', 'SSL')->format();
            }

            if ($notification['notification_module_code']=="orders_new"){
                $this->load->model('sale/order');
                $orderInfo = $this->model_sale_order->getOrder($notification['notification_module_id']);
                $notification['customer']=$orderInfo['firstname'] ." " .$orderInfo['lastname'];
                $notification['total']=$orderInfo['total'];
                $notification['currency_code']=$orderInfo['currency_code'];
                $notification['url']= $this->url->link('sale/order/info', 'order_id='.$notification['notification_module_id'], 'SSL')->format();
            }

            if ($notification['notification_module_code']=="return_new"){
                $this->load->model('sale/return');
                $return_info = $this->model_sale_return->getReturn($notification['notification_module_id']);
                $notification['customer']=$return_info['customer'];
                $notification['currency_code']=$return_info['currency_code'];
                $notification['url']= $this->url->link('sale/order/info', 'order_id='.$return_info['order_id'], 'SSL')->format();
                $notification['return_url']= $this->url->link('sale/return/info', 'return_id='.$notification['notification_module_id'], 'SSL')->format();
            }

            if ($notification['notification_module_code']=="customers_one"){
                $notification['url']= $this->url->link('sale/customer/update', 'customer_id=1', 'SSL')->format();
            }

            if ($notification['notification_module_code']=="customers_ten"
            || $notification['notification_module_code']=="orders_ten"
            ){
                $notification['url']= $this->url->link('marketplace/home', 'category[]=marketing', 'SSL')->format();
            }

            if ($notification['notification_module_code']=="customers_registered"){

                $this->load->model('sale/customer');
                $customer_info = $this->model_sale_customer->getCustomer($notification['notification_module_id']);
                $notification['customer']=$customer_info['firstname']." ".$customer_info['lastname'];
                $notification['url']= $this->url->link(
                    'sale/customer/update', 'customer_id='.$notification['notification_module_id'],
                    'SSL'
                )->format();
            }

            if ($notification['read_status'] == '0') {
                $unread++;
            }
        }

        return [
            'notifications' => $notifications,
            'unread' => $unread,
        ];

    }
    
    /**
     * Force update custom template
     * for the first time
     *
     * @return void
     */
    private function forceUpdateCurrentTemplate()
    {
        $configKey = 'force_update_custom_template';
        
        // Template already updated or not a custom template!
        if ((int) $this->config->get($configKey) || !IS_CUSTOM_TEMPLATE) return false; 

        $currentTemplate = $this->config->get('config_template');
        
        // Initialize Models
        $settingModel  = $this->load->model('setting/setting', ['return' => true]);
        $templateModel = $this->load->model('templates/template', ['return' => true]);
        
        $setConfig = function ($value = 1) use ($settingModel, $configKey) {
            $settingModel->insertUpdateSetting($configKey, [$configKey => $value]);
            $this->config->set($configKey, $value);
            return true;
        };
        
                                    /** Validate template */

        // Current template not a custom template
        if (!$template = $templateModel->getCustomTemplateByConfigName($currentTemplate)) {
            return $setConfig();
        }
        
        $log = new Log("update_templates_logs.txt");
        $log->write('Start force update ' . $currentTemplate . ' template from admin.');
        
        $url = (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) ? 'https://' : 'http://';
        $url.= rtrim(DOMAINNAME) . '/api/v1/force_update_template';
        
        $curlClient = $this->registry->get('curl_client');
        $response = $curlClient->request('POST', $url, [], ['template_id' => $template['id'],]);
        $content = $response->getContent();
        
        if ($response->ok() && $content['status'] === 'OK') {
            $log->write('Template ' . $currentTemplate . ' has been successfully updated.');
            return $setConfig();
        }
        
        $log->write('Failed to update the '. $currentTemplate .' template: ' . json_encode($content));
        return $setConfig(0);
    }

    private function initiateAssistantGuide()
    {
        $guide_json =null;
        $this->load->model('setting/setting');
        $assistant_guides = $this->model_setting_setting->getGuideValue("ASSISTANT");
        $status=false;

        if (is_array($this->config->get("tracking"))){
            $data['tracking'] = $this->config->get("tracking");
        }

        // track if all steps is done
        if (!$this->config->get("all_steps_is_done") && count($assistant_guides) == 8){

            /** Start ExpandCartTracking #347741 */

            $this->load->model('setting/mixpanel');
            $this->model_setting_mixpanel->trackEvent('Assistant - All Steps Done');

            $this->load->model('setting/amplitude');
            $this->model_setting_amplitude->trackEvent('Assistant - All Steps Done');

            /** End */

            $data_steps_is_done['all_steps_is_done']=1;
            $this->model_setting_setting->insertUpdateSetting('tracking',$data_steps_is_done);

        }

        $guids_not_done = array_keys($assistant_guides,'0');
        if (count($guids_not_done)){
            $this->data['first_name'] = $this->config->get('admin_first_name');
            if (!$this->config->get('admin_first_name') || $this->config->get('admin_first_name') == ""){
                $whmcs = new whmcs();
                $clientDetails 	= $whmcs->getClientDetails(WHMCS_USER_ID);
                $this->data['first_name'] = $clientDetails['firstname'];
                $data['admin_first_name']=$this->data['first_name'];
                $this->model_setting_setting->insertUpdateSetting('admin',$data);
            }

            $guide_json = json_decode(file_get_contents('json/assistant_guide.json'), true);
            foreach ($guide_json as $key => $item){
                $guide_json[$key]['status']=$assistant_guides[$item['name']];

                if ($item['name'] == 'ADD_DOMAIN' ){
                    if (  $guide_json[$key]['status'] != 1 ){
                        $this->load->model('setting/domainsetting');
                        $domain_count = $this->model_setting_domainsetting->countDomain();
                        if ($domain_count > 0){
                            $this->tracking->updateGuideValue('ADD_DOMAIN');
                            $guide_json[$key]['status'] = "1";
                        }
                    }
                }

                if ( $guide_json[$key]['status'] == 1){
                    $guide_json[$key]['sort']="3";
                }else{
                    $guide_json[$key]['sort']= $guide_json[$key]['status'];
                }
            }
        }

        $this->data['config_name'] = !is_array($this->config->get('config_name')) ?
            $this->config->get('config_name')
            :
            $this->config->get('config_name')[$this->config->get('config_admin_language')]
        ;

        $sort_order = array();
        foreach ($guide_json as $key => $value)
        {
            $sort_order['sort'][$key] = $value['sort'];
            $sort_order['order'][$key] = $key;
        }

        array_multisort($sort_order['sort'], $sort_order['order'], SORT_ASC, $guide_json);
        $this->data['assistance_data']= $guide_json;
    }
}
?>
