<?php

class ControllerModuleMultiseller extends ControllerMultisellerBase
{
    private $_controllers = array(
        "multiseller/base",
        "multiseller/product",
        "multiseller/attribute",
        "multiseller/payment",
        "multiseller/seller",
        "multiseller/seller_transactions",
        "multiseller/transaction",
        "multiseller/seller-group",
        "multiseller/subscriptions",
    );

    private $settings = array(
        "msconf_seller_validation" => MsSeller::MS_SELLER_VALIDATION_NONE,
        "msconf_product_validation" => MsProduct::MS_PRODUCT_VALIDATION_NONE,
        "msconf_allow_inactive_seller_products" => 0,
        "msconf_nickname_rules" => 0, // 0 - alnum, 1 - latin extended, 2 - utf
        "msconf_credit_order_statuses" => array(5),
        "msconf_debit_order_statuses" => array(8),
        "msconf_minimum_withdrawal_amount" => "50",
        "msconf_allow_partial_withdrawal" => 1,

        "msconf_paypal_sandbox" => 1,
        "msconf_paypal_address" => "",

        "msconf_allow_withdrawal_requests" => 1,
        "msconf_allowed_image_types" => 'png,jpg,jpeg',
        "msconfig_allow_download" => 0,
        "msconf_allowed_download_types" => 'zip,rar,pdf',
        "msconf_minimum_product_price" => 0,
        "msconf_maximum_product_price" => 0,
        "msconf_notification_email" => "",
        "msconf_payment_method" => 1,
        "msconf_allow_free_products" => 0,

        "msconf_allow_multiple_categories" => 0,
        "msconf_additional_category_restrictions" => 0, // 0 - none, 1 - topmost, 2 - all parents
        "msconf_restrict_categories" => array(),
        "msconf_product_included_fields" => array(),

        "msconf_product_mandatory_fields" => array(),

        "msconf_images_limits" => array(0, 0),
        "msconf_downloads_limits" => array(0, 0),

        "msconf_enable_shipping" => 0, // 0 - no, 1 - yes, 2 - seller select
        "msconf_provide_buyerinfo" => 0, // 0 - no, 1 - yes, 2 - shipping dependent
        "msconf_enable_non_en_lang" => 0, // 0 - no, 1 - yes
        "msconf_enable_quantities" => 0, // 0 - no, 1 - yes, 2 - shipping dependent
        "msconf_enable_min_quantities" => 0, // 0 - no, 1 - yes
        "msconf_enable_categories" => 0, // 0 - no, 1 - yes
        "msconf_physical_product_categories" => array(),
        "msconf_digital_product_categories" => array(),

        "msconf_disable_product_after_quantity_depleted" => 0,
        "msconf_allow_relisting" => 0,

        "msconf_enable_seo_urls_seller" => 0,
        "msconf_enable_seo_urls_product" => 0,
        "msconf_enable_update_seo_urls" => 0,
        "msconf_enable_non_alphanumeric_seo" => 0,
        "msconf_product_image_path" => 'sellers/',
        "msconf_predefined_avatars_path" => 'avatars/',
        "msconf_temp_image_path" => 'tmp/',
        "msconf_temp_download_path" => 'tmp/',
        "msconf_seller_terms_page" => "",
        "msconf_default_seller_group_id" => 1,
        "msconf_allow_specials" => 1,
        "msconf_allow_discounts" => 1,
        "msconf_withdrawal_waiting_period" => 0,
        "msconf_graphical_sellermenu" => 1,

        "msconf_enable_rte" => 0,
        "msconf_rte_whitelist" => "",
        "msconf_search_by_city" => 0,
        "msconf_seller_required_fields" => array('nickname'),
        "msconf_seller_show_fields" => array('nickname'),
        "msconf_hide_orderinfo" => 0,

        "msconf_seller_avatar_seller_profile_image_width" => 100,
        "msconf_seller_avatar_seller_profile_image_height" => 100,
        "msconf_seller_avatar_seller_list_image_width" => 100,
        "msconf_seller_avatar_seller_list_image_height" => 100,
        "msconf_seller_avatar_product_page_image_width" => 100,
        "msconf_seller_avatar_product_page_image_height" => 100,
        "msconf_seller_avatar_dashboard_image_width" => 100,
        "msconf_seller_avatar_dashboard_image_height" => 100,
        "msconf_preview_seller_avatar_image_width" => 100,
        "msconf_preview_seller_avatar_image_height" => 100,
        "msconf_preview_product_image_width" => 100,
        "msconf_preview_product_image_height" => 100,
        "msconf_product_seller_profile_image_width" => 100,
        "msconf_product_seller_profile_image_height" => 100,
        "msconf_product_seller_products_image_width" => 100,
        "msconf_product_seller_products_image_height" => 100,
        "msconf_product_seller_product_list_seller_area_image_width" => 40,
        "msconf_product_seller_product_list_seller_area_image_height" => 40,

        "msconf_min_uploaded_image_width" => 0,
        "msconf_min_uploaded_image_height" => 0,
        "msconf_max_uploaded_image_width" => 0,
        "msconf_max_uploaded_image_height" => 0,

        "msconf_sellers_slug" => "sellers",

        "msconf_attribute_display" => 0, // 0 - MM, 1 - OC, 2 - both
        "msconf_hide_customer_info" => 0,
        "msconf_hide_customer_email" => 0,
        "msconf_hide_emails_in_emails" => 0,
        "msconf_hide_sellers_product_count" => 1,
        "msconf_avatars_for_sellers" => 0, // 0 - Uploaded manually by seller, 1 - Both, uploaded by seller and pre-defined, 2 - Only pre-defined
        "msconf_change_seller_nickname" => 1,

        "msconf_enable_private_messaging" => 1, // 0 - no, 2 - yes (email only)
        "msconf_enable_one_page_seller_registration" => 0, // 0 - no, 1 - yes

        "msconf_enable_subscriptions_plans_system" => 0,
        "msconf_subscriptions_paypal_email" => '',
        "msconf_subscriptions_bank_details" => '',

        "msconf_subscriptions_mastercard_accesscode" => '',
        "msconf_subscriptions_mastercard_merchant" => '',
        "msconf_subscriptions_mastercard_secret" => '',

        "msconf_enable_seller_independent_payments" => 0,
        "msconf_enable_search_by_seller" => 0,
        "msconf_enable_seller_name_in_cart_view" => 0,
        "msconf_enable_contact_seller" => 0,
        "msconf_seller_title" => 'Seller',
        "msconf_product_title" => 'Products',
        "msconf_seller_paragraph" => '',
        'msconf_sellers_per_row' => 3,
        'msconf_sellers_totals' => 'yes_show',
        'msconf_default_url' => 'profile',
        'msconf_show_seller_company' => 1,

        "msconf_seller_data_custom" => [],

        "msconf_allow_seller_image_gallery" => 0,
        "msconf_allow_seller_review" => 0,
        'msconf_address_info' => 1,
        'msconf_responsive' => 0,
        'msconf_allowed_payment_methods' => 0,
        'msconf_show_country' => 0,
        'msconf_show_city' => 0,
        'msconf_allow_seller_to_contact_seller' => 0,
        'msconf_disable_sending_emails_with_the_product_submission'=> 0,
        'msconf_delivery_slots_to_sellers'=> 0,
        'msconf_seller_google_api_key' => '',
        'msconf_allowed_product_file_download_types' => '',
        'msconf_seller_allowed_files_types' => ''
    );

    private $seller_fields=array(
                                    'description',
                                    'mobile', 
                                    'company',
                                    'tax card',
                                    'commercial register', 
                                    'record expiration date', 
                                    'industrial license number', 
                                    'license expiration date', 
                                    'personal id',
                                    'website',
                                    'country',
                                    'region',
                                    'paypal',
                                    'bank transfer', 
                                    'bank name', 
                                    'bank iban', 
                                    'payment methods', 
                                    'avatar', 
                                    'commercial record image', 
                                    'industrial license image', 
                                    'tax card image',
                                    'image id',
                                    'google map location'
                                );

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->registry = $registry;
    }

    private function _editSettings()
    {
        if(isset($this->request->post['msconf_sellers_slug']) && !empty($this->request->post['msconf_sellers_slug']))
            $this->request->post['msconf_sellers_slug'] = $this->db->escape($this->request->post['msconf_sellers_slug']);


        $this->load->model('setting/setting');
        $this->load->model('setting/extension');
        $set = $this->model_setting_setting->getSetting('multiseller');
        $installed_extensions = $this->model_setting_extension->getInstalled('module');

        $extensions_to_be_installed = array();
        foreach ($this->settings as $name => $value) {
            if (!array_key_exists($name, $set))
                $set[$name] = $value;

            if (
                (strpos($name, '_module') !== FALSE) &&
                (!in_array(str_replace('_module', '', $name), $installed_extensions))
            ) {
                $extensions_to_be_installed[] = str_replace('_module', '', $name);
            }
        }
        foreach ($set as $s => $v) {
            if ((strpos($s, '_module') !== FALSE)) {
                if (!isset($this->request->post[$s])) {
                    $set[$s] = '';
                } else {
                    unset($this->request->post[$s][0]);
                    $set[$s] = $this->request->post[$s];
                }
                continue;
            }

            if (isset($this->request->post[$s])) {
                $set[$s] = $this->request->post[$s];
                $this->data[$s] = $this->request->post[$s];
            } elseif ($this->config->get($s)) {
                $this->data[$s] = $this->config->get($s);
            } else {
                if (isset($this->settings[$s]))
                    $this->data[$s] = $this->settings[$s];
            }
        }
        if ($set['msconf_subscriptions_paypal_email'] == '' && $set['msconf_subscriptions_bank_details'] == '' && ($set['msconf_subscriptions_mastercard_accesscode'] == '' || $set['msconf_subscriptions_mastercard_merchant'] == '' || $set['msconf_subscriptions_mastercard_secret'] == '') ) {
            $set['msconf_enable_subscriptions_plans_system'] = 0;
            $this->session->data['error'] = $this->language->get('ms_error_empty_fields');
        }
        $this->model_setting_setting->editSetting('multiseller', $set);

        foreach ($extensions_to_be_installed as $ext) {
            $this->model_setting_extension->install('module', $ext);
        }
    }

    public function install()
    {
        $this->validate(__FUNCTION__);
        $this->load->model("multiseller/install");
        $this->load->model('setting/setting');

        $this->model_multiseller_install->createSchema();
        $this->model_multiseller_install->createData();
        $this->model_setting_setting->editSetting('multiseller', $this->settings);

        $this->load->model('user/user_group');

        foreach ($this->_controllers as $c) {
            $this->model_user_user_group->addPermission($this->user->getId(), 'access', $c);
            $this->model_user_user_group->addPermission($this->user->getId(), 'modify', $c);
        }

        $dirs = array(
            'image/' . $this->settings['msconf_product_image_path'],
            'image/' . $this->settings['msconf_temp_image_path'],
            'download/' . $this->settings['msconf_temp_download_path']
        );

        $this->session->data['success'] = $this->language->get('ms_success_installed');
        $this->session->data['error'] = "";

        foreach ($dirs as $dir) {
            if (\Filesystem::isDirExists($dir) != false) {
                \Filesystem::createDir($dir);
            }
            /*if (!file_exists($dir)) {
                if (!mkdir($dir, 0755)) {
                    $this->session->data['error'] .= sprintf($this->language->get('ms_error_directory'), $dir);
                }
            } else {
                if (!is_writable($dir)) {
                    $this->session->data['error'] .= sprintf(
                        $this->language->get('ms_error_directory_notwritable'),
                        $dir
                    );
                } else {
                    $this->session->data['error'] .= sprintf(
                        $this->language->get('ms_error_directory_exists'),
                        $dir
                    );
                }
            }*/
        }

        // ckeditor
        /*if (!copy(DIR_APPLICATION . 'view/javascript/ckeditor/', DIR_CATALOG . 'view/javascript/multimerch/')) {
            $this->session->data['error'] .= sprintf(
                $this->language->get('ms_error_ckeditor'),
                DIR_APPLICATION . 'view/javascript/ckeditor/',
                DIR_CATALOG . 'view/javascript/multimerch/'
            );
        }*/
        $this->session->data['error'] .= sprintf(
            $this->language->get('ms_notice_ckeditor'),
            DIR_APPLICATION . 'view/javascript/ckeditor/',
            DIR_CATALOG . 'view/javascript/multimerch/ckeditor/'
        );
    }

    public function uninstall()
    {
        $this->validate(__FUNCTION__);
        $this->load->model("multiseller/install");
        $this->model_multiseller_install->deleteSchema();
        $this->model_multiseller_install->deleteData();
    }

    public function saveSettings()
    {
        if (!$this->user->hasPermission('modify', 'module/multiseller')) {
            return $this->response->setOutput(json_encode([
                'success' => 0,
                'error' => $this->language->get('error_permission')
            ]));
        }
        $this->validate(__FUNCTION__);

        /*magic
        $this->request->post['msconf_allowed_image_types'] = 'png,jpg';
        $this->request->post['msconf_allowed_download_types'] = 'zip,rar,pdf';

        $this->request->post['msconf_paypal_sandbox'] = 1;
        magic*/

        if (!isset($this->request->post['msconf_credit_order_statuses']))
            $this->request->post['msconf_credit_order_statuses'] = array();
        
        if (!isset($this->request->post['msconf_seller_required_fields']))
            $this->request->post['msconf_seller_required_fields'] = array();

        if (!isset($this->request->post['msconf_seller_show_fields']))
            $this->request->post['msconf_seller_show_fields'] = array();

        if (!isset($this->request->post['msconf_debit_order_statuses']))
            $this->request->post['msconf_debit_order_statuses'] = array();

        if (!isset($this->request->post['msconf_product_options']))
            $this->request->post['msconf_product_options'] = array();

        if (!isset($this->request->post['msconf_restrict_categories']))
            $this->request->post['msconf_restrict_categories'] = array();

        if (!isset($this->request->post['msconf_product_included_fields']))
            $this->request->post['msconf_product_included_fields'] = array();


        if (!isset($this->request->post['msconfig_allow_download'])) {
            $this->request->post['msconfig_allow_download'] = 0;
        }

        if (!isset($this->request->post['msconf_seller_required_fields']))
            $this->request->post['msconf_seller_required_fields'] = array();

        if (!isset($this->request->post['msconf_product_mandatory_fields'])) {
            $this->request->post['msconf_product_mandatory_fields'] = array();
        }
        if (!isset($this->request->post['msconf_seller_data_custom'])) {
            $this->request->post['msconf_seller_data_custom'] = array();
        }else{
            //validate custom data 
            $seller_data_error = array();
            foreach($this->request->post['msconf_seller_data_custom']  as $key=>$value){
                foreach($value['title'] as $k=>$v){
                    if(empty($v)){
                        $seller_data_error['title'] = $this->language->get('error_seller_field_title');
                    }
                } 
                
                if($value['field_type']['name'] == ''){
                    $seller_data_error['name'] = $this->language->get('error_seller_field_type');
                }
                if($value['field_type']['name']=='select' || $value['field_type']['name']=='radio' || $value['field_type']['name']=='checkbox'){
                    if(!$value['field_type']['option_value']){
                        $seller_data_error['option_field_name'] = $this->language->get('error_seller_field_type_options');
                    }else{
                        if(count($value['field_type']['option_value']) < 2){
                            $seller_data_error['options_count'] =  $this->language->get('seller_options_count');
                        }else{
                            foreach($value['field_type']['option_value'] as $kk=>$vv){
                                foreach($vv as $option_val){
                                    if(empty($option_val)){
                                        $seller_data_error['option_value'] =  $this->language->get('error_seller_field_type_options_language');
                                    }
                                }
                            }
                        }
                    }
                }
                
            }
            if(count($seller_data_error)){
                $result_json['success'] = '0';
                $result_json['errors'] = $seller_data_error;
                $result_json['errors']['warning'] = $this->language->get('seller_custom_data_warning');;
                $this->response->setOutput(json_encode($result_json));
                return;
            }
        }
   
        // todo setting validation
        $this->_editSettings();
        if ($this->session->data['error'] ){
            $result_json['success'] = '0';
            $result_json['errors'] = $this->session->data['error'];
            $result_json['errors']['warning'] = $this->language->get('seller_custom_data_warning');
            $this->response->setOutput(json_encode($result_json));
        }else{
            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_success');
            $this->response->setOutput(json_encode($result_json));
        }
    }

    public function index()
    {
        if (!$this->user->hasPermission('access', 'module/multiseller')) {
            return $this->response->redirect($this->url->link('error/permission', '', 'SSL'));
        }
        $this->load->model('marketplace/common');
        $market_appservice_status = $this->model_marketplace_common->hasApp('multiseller');
        if (!$market_appservice_status['hasapp']) {
            $this->redirect(
                $this->url->link(
                    'marketplace/app', 'id=' . $market_appservice_status['appserviceid'], 'SSL'
                )
            );
            return;
        }

        $this->validate(__FUNCTION__);

        foreach ($this->settings as $s => $v) {
            //var_dump($s,$this->config->get($s));
            $val = $this->config->get($s);
            if(isset($val))
                $this->data[$s] = $this->config->get($s);
            else
                $this->data[$s] = $this->settings[$s];
        }

        $this->load->model('localisation/language');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();

        $this->document->addScript('view/javascript/multimerch/settings.js');

        $this->data['seller_fields'] = $this->seller_fields;
            
        $this->load->model("localisation/order_status");
        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        $this->load->model("catalog/option");
        $this->data['options'] = $this->model_catalog_option->getOptions();

        $this->data['cancel'] = $this->url->link('marketplace/home', 'type=apps', 'SSL');

        $this->load->model('design/layout');
        $this->data['layouts'] = $this->model_design_layout->getLayouts();
        $this->data['currency_code'] = $this->config->get('config_currency');

        $this->data['button_add_module'] = $this->language->get('button_add_module');
        $this->data['button_remove'] = $this->language->get('button_remove');

        if (isset($this->error['image'])) {
            $this->data['error_image'] = $this->error['image'];
        } else {
            $this->data['error_image'] = array();
        }

        $this->data['config_download'] = $this->config->get('config_download');

        $this->load->model('catalog/information');
        $this->data['informations'] = $this->model_catalog_information->getInformations();
        $this->data['categories'] = $this->MsLoader->MsProduct->getCategories();

        $productFields = array(
            'model' => $this->language->get('ms_catalog_products_field_model'),
            'sku' => $this->language->get('ms_catalog_products_field_sku'),
            // 'upc' => $this->language->get('ms_catalog_products_field_upc'),
            // 'ean' => $this->language->get('ms_catalog_products_field_ean'),
            // 'jan' => $this->language->get('ms_catalog_products_field_jan'),
            // 'isbn' => $this->language->get('ms_catalog_products_field_isbn'),
            // 'mpn' => $this->language->get('ms_catalog_products_field_mpn'),
            'weight' => $this->language->get('ms_catalog_products_field_weight'),
            'dimensions' => $this->language->get('ms_catalog_products_field_dimensions'),
            'cost' => $this->language->get('ms_catalog_products_field_cost_price'),
            'manufacturer' => $this->language->get('ms_catalog_products_field_manufacturer'),
            'dateAvailable' => $this->language->get('ms_catalog_products_field_date_available'),
            'taxClass' => $this->language->get('ms_catalog_products_field_tax_class'),
            'subtract' => $this->language->get('ms_catalog_products_field_subtract'),
            'stockStatus' => $this->language->get('ms_catalog_products_field_stock_status'),
            'metaDescription' => $this->language->get('ms_catalog_products_field_meta_description'),
            'metaKeywords' => $this->language->get('ms_catalog_products_field_meta_keyword')
        );

        $this->data['product_included_fieds'] = $productFields;

        $productFields['quantity'] = $this->language->get('ms_catalog_products_field_quantity');
        $productFields['min_quantity'] = $this->language->get('ms_catalog_products_field_min_quantity');

        $this->data['product_mandatory_fields'] = $productFields;

        $this->document->setTitle($this->language->get('ms_settings_heading'));
        $this->data['heading_title'] = $this->language->get('ms_settings_heading');

        $this->data['breadcrumbs'] = $this->MsLoader->MsHelper->admSetBreadcrumbs(array(
            array(
                'text' => $this->language->get('ms_menu_multiseller'),
                'href' =>$this->url->link('module/multiseller', '', 'SSL'),
            ),
            array(
                'text' => $this->language->get('ms_settings_breadcrumbs'),
                'href' => $this->url->link('multiseller/settings', '', 'SSL'),
            )
        ));

        if (isset($this->session->data['error'])) {
            $this->data['error_warning'] = $this->session->data['error'];
            unset($this->session->data['error']);
        }

        $this->data['links'] = [
            'submit' => $this->url->link('module/multiseller/saveSettings', '', 'SSL'),
            'cancel' => $this->url->link('marketplace/home', '', 'SSL'),
        ];


        $this->template = 'multiseller/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        //to help add dynamic unlimited count of options in seller custom data
        $all_optoions_count = 0;
        foreach($this->data['msconf_seller_data_custom'] as $k => $v){
            if($v['field_type']['option_value']){ 
                foreach($v['field_type']['option_value'] as $index=>$options){
                    //options index are not in ascending order -- user may added and deleted options in inserting fields and indeices are not in order
                    $all_optoions_count = $index > $all_optoions_count ? $index : $all_optoions_count;
                }
               
            }
        }
        $this->data['all_optoions_count'] = $all_optoions_count;

        $this->response->setOutput($this->render());
    }

    public function upgradeDb()
    {
        $this->load->model("multiseller/upgrade");
        if ($this->MsLoader->MsHelper->isInstalled() && !$this->model_multiseller_upgrade->isDbLatest()) {
            $this->model_multiseller_upgrade->upgradeDb();
            $this->session->data['ms_db_latest'] = $this->language->get('ms_db_success');
        } else {
            $this->session->data['ms_db_latest'] = $this->language->get('ms_db_latest');
        }

        $this->redirect($this->url->link('module/multiseller', '', 'SSL'));
    }
}
