<?php

use ExpandCart\Foundation\String\Barcode\Generator as BarcodeGenerator;
use Facebook\Facebook;
use Facebook\Exceptions\FacebookSDKException;
use ExpandCart\Foundation\Support\Hubspot;

class ControllerCatalogProduct extends Controller
{
    private $error = array();
    private $duplicate = 0;
    private $all = array();
    private $plan_id = PRODUCTID;
    private $last_product_in_limit_id = null;
    private $queueFileLocation = DIR_SYSTEM . 'library/products_queue.php';
    private $deleteMaxLimit = 100;
    private $useQueueLimit = 10;
    private $products_limit = null;

    public function __construct($registry) {
        parent::__construct($registry);
        $this->load->model('plan/trial');
        $trial = $this->model_plan_trial->getLastTrial();
        if ($trial){
            $this->plan_id = $trial['plan_id'];
        }

        $this->products_limit = (STORECODE == 'BFSYE663') ? null : $this->genericConstants["plans_limits"][$this->plan_id]['products_limit'];

    }

    public function index()
    {
        // Product Option Image PRO module <<
        $this->language->load('module/product_option_image_pro');
        // >> Product Option Image PRO module

        $this->language->load('catalog/product');

        $this->load->model('catalog/product');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['direction'] = $this->language->get('direction');

        $this->data['plan_id'] =$this->plan_id;

        $this->load->model('catalog/product');

        //Check be supplier app, and dropna integration
        $this->data['is_supplier'] = false;

        $this->load->model('module/be_supplier');
        $this->load->model('api/clients');

        if ($this->model_module_be_supplier->isActive() && $this->model_api_clients->getDropnaClient()) {
            $this->data['is_supplier'] = true;

            $this->data['dropna_import_progress'] = true;
            $dropna_reference = $this->config->get('dropna_import_reference');

            $dropna_import_data = $this->model_module_be_supplier->getImportDate($dropna_reference);

            if ($dropna_import_data) {
                $this->data['dropna_import'] = 1;
                $this->data['dropna_import_total'] = $dropna_import_data['total'];
                $this->data['dropna_import_success'] = $dropna_import_data['success'];
                $this->data['dropna_import_wait'] = $dropna_import_data['wait'];

                if ($dropna_import_data['wait'] == 0) {
                    $this->data['dropna_import_progress'] = false;
                    $this->data['dropna_import_failed'] = $dropna_import_data['total'] - $dropna_import_data['success'];
                } else {
                    //Close Export button till current queue finish
                    $this->data['is_supplier'] = false;
                }
            }

            $this->data['dropna_import_closed'] = $this->config->get('dropna_import_closed');
        }
        ///////////////////////////////////////////////

        $this->data['DIR_IMAGE'] = DIR_IMAGE;
        // die(DIR_IMAGE);

        /////check multi seller
        $this->load->model('multiseller/status');
        $this->data['is_multiseller'] = $this->model_multiseller_status->is_installed();
        //////////////////////

        if(isset($_GET['category'])){
            $this->session->data['filter_by_category'] = (int) $_GET['category'];
        }else{
            unset($this->session->data['filter_by_category']);
        }

        if (isset($_GET['seller'])){	
            $this->session->data['filter_by_seller'] = (int) $_GET['seller'];	
        } else {	
            unset($this->session->data['filter_by_seller']);	
        }

        if( \Extension::isInstalled('facebook_business')) {
            $data["isFacebookInstalled"] = 1;

        }

        $total_products_count = $this->model_catalog_product->getTotalProductsCount();
        $data['limit_reached'] =
            ( $this->products_limit && ( ($total_products_count + 1) > $this->products_limit ) )
            ||
            ( KANAWAT_PRODUCTSLIMIT != -1 && $total_products_count >= KANAWAT_PRODUCTSLIMIT && $this->plan_id  == 52)
        ;

        $data['limit_warning'] =
            $this->products_limit &&
            (
                ($this->model_catalog_product->getTotalProductsCount())
                >=
                ($this->products_limit - $this->genericConstants["plans_limits"][$this->plan_id]['products_warning_diff'])
            )
        ;

        $this->getList();
    }

    public function insert()
    {
        if ($this->request->get['card_name']){
            $this->session->data['card_name'] = $this->request->get['card_name'];
        }

        // Product Option Image PRO module <<

        $this->language->load('module/product_option_image_pro');
        // >> Product Option Image PRO module

        $this->language->load('module/advanced_product_attributes');

        $this->language->load('catalog/product');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('catalog/product');
        $this->language->load('module/fit_and_shop');

        $this->load->model('setting/setting');

        $this->load->model('module/fit_and_shop');
        $fit_and_shop_settings = $this->config->get('fit_and_shop');
        if( \Extension::isInstalled('fit_and_shop') && $fit_and_shop_settings['status'] == 1 ){
            $this->data['is_installed_fit_and_shop'] = true;
            $this->data['measurments_categories'] = $this->model_module_fit_and_shop->get_categories();
            $this->data['fit_and_shop_get_collections_ajax_url'] = $this->url->link( 'module/fit_and_shop/ajax_get_collections', '', true);
			$this->data['fit_and_shop_api_key'] = $fit_and_shop_settings['apikey'];
        }

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {


            if ( $this->products_limit &&
                $this->model_catalog_product->getTotalProductsCount() + 1 > $this->products_limit  ) {

                $result_json['success'] = '0';
                $result_json['errors']['error'] = $this->language->get('error_maximum_product_number');

                $this->response->setOutput(json_encode($result_json));
                return;
            }


            $this->parse_product_inputs();


            if (!$this->validateForm()) {
                //todo 2
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                $result_json['duplicate'] = $this->duplicate;
                $this->response->setOutput(json_encode($result_json));
                return;
            }


            $this->request->post['product_special'] = $this->request->post['product_discount'] = [];

            foreach ($this->request->post['product_special_discount'] as $specialDiscount) {
                if ($specialDiscount['quantity'] > 1) {
                    $this->request->post['product_discount'][] = $specialDiscount;
                } else {
                    $this->request->post['product_special'][] = $specialDiscount;
                }
            }

            if (!isset($this->request->post['status'])) {
                $this->request->post['status'] = '0';
            }

            $this->request->post['image'] = (!empty($this->request->post['product_image'])) ? array_shift($this->request->post['product_image'])['image'] : 'image/no_image.jpg';

            $product_id = $this->model_catalog_product->addProduct($this->request->post);

            if ($product_id)
                $this->userActivation->processSoftActivation($this->userActivation::ADD_PRODUCT);

            // add data to log_history
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("product");
            if($pageStatus){
                $log_history['action'] = 'add';
                $log_history['reference_id'] = $product_id;
                $log_history['old_value'] = NULL;
                $log_history['new_value'] = json_encode($this->request->post,JSON_UNESCAPED_UNICODE);
                $log_history['type'] = 'product';
                $this->load->model('loghistory/histories');
                $this->model_loghistory_histories->addHistory($log_history);
            }

            //get product count
            $products_count = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE demo = 0 AND archived = 0")->row['total'];
            // ZOHO inventory create product if app is setup
            $this->load->model('module/zoho_inventory');
            $this->model_module_zoho_inventory->createProduct($product_id, $this->request->post);

           // Odoo create product if app is setup
           if (\Extension::isInstalled('odoo') && $this->config->get('odoo')['status']
           && $this->config->get('odoo')['products_integrate'])
           {
            $this->load->model('module/odoo/products');
            $this->model_module_odoo_products->createProduct($product_id, $this->request->post);
           }

            // Add new product to El-Modaqeq products
            if( \Extension::isInstalled('elmodaqeq') && $this->config->get('elmodaqeq')['status'] == 1) {
                $this->load->model('module/elmodaqeq/product');
                $this->model_module_elmodaqeq_product->addProduct($this->request->post);
            }

            /***************** Start ExpandCartTracking #347698  ****************/

            // send mixpanel add product event and update user products count
            $this->load->model('setting/mixpanel');
            $this->model_setting_mixpanel->updateUser(['$products count'=>$products_count]);
            $this->model_setting_mixpanel->trackEvent('Add Product From Backend');

            // send amplitude add product event and update user products count
            $this->load->model('setting/amplitude');
            $this->model_setting_amplitude->updateUser(['products count'=>$products_count]);
            $this->model_setting_amplitude->trackEvent('Add Product From Backend');

            $this->model_setting_amplitude->trackEvent('Product Added Successfully');


            /***************** End ExpandCartTracking #347698  ****************/



            //################### Freshsales Start #####################################
            try {
                $fields = array();
                $fields["boolean--Has--Added--Products"] = true;
                // autopilot_UpdateContactCustomFields(BILLING_DETAILS_EMAIL, $fields);


                $eventName = "Added a Product";

                FreshsalesAnalytics::init([
                    'domain' => 'https://' . FRESHSALES_SUBDOMAIN . '.freshsales.io',
                    'app_token' => FRESHSALES_TOKEN
                ]);

                FreshsalesAnalytics::trackEvent(array(
                    'identifier' => BILLING_DETAILS_EMAIL,
                    'name' => $eventName
                ));
            } catch (Exception $e) {
            }
            //################### Freshsales End #####################################

            //################### Intercom Start #####################################
            try {
                $url = 'https://api.intercom.io/events';
                $authid = INTERCOM_AUTH_ID;

                $cURL = curl_init();
                curl_setopt($cURL, CURLOPT_URL, $url);
                curl_setopt($cURL, CURLOPT_USERPWD, $authid);
                curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($cURL, CURLOPT_SSL_VERIFYHOST, FALSE);
                curl_setopt($cURL, CURLOPT_POST, true);
                curl_setopt($cURL, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Accept: application/json'
                ));
                $intercomData['event_name'] = 'product-added';
                $intercomData['created_at'] = time();
                $intercomData['user_id'] = STORECODE;
                curl_setopt($cURL, CURLOPT_POSTFIELDS, json_encode($intercomData));
                $result = curl_exec($cURL);
                curl_close($cURL);
            } catch (Exception $e) {}
            //################### Intercom End #######################################

            Hubspot::tracking('pe25199511_os_product_added', []);

            $this->session->data['success'] = $this->language->get('text_success');


            $this->tracking->updateGuideValue("ADD_PRODUCTS");

            $url = '';

            $queryAuctionModule = $this->db->query(
                "SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'special_count_down'"
            );

            if ($queryAuctionModule->num_rows) {
                if (isset($this->error['start_time'])) {
                    $this->data['error_start'] = $this->error['start_time'];
                } else {
                    $this->data['error_start'] = '';
                }

                if (isset($this->error['bid_price'])) {
                    $this->data['error_bid_price'] = $this->error['bid_price'];
                } else {
                    $this->data['error_bid_price'] = '';
                }


                if (isset($this->error['end_time'])) {
                    $this->data['error_end_time'] = $this->error['end_time'];
                } else {
                    $this->data['error_end_time'] = '';
                }


                if (isset($this->error['max_price'])) {

                    $this->data['error_max_price'] = $this->error['max_price'];

                } else {

                    $this->data['error_max_price'] = '';

                }
            }
	
		if( \Extension::isInstalled('fit_and_shop') && $fit_and_shop_settings['status'] == 1 ){
			if($this->request->post['fit_status']){
			    $fit_params = array();
			    $fit_params['fit_status'] = $this->request->post['fit_status'];
			    $fit_params['collection_sku'] = $this->request->post['categories_collections_select'];

			    $fit_params['measurment_category_id'] = $this->request->post['fit_measurment_cat'];
			    $fit_params['product_id'] = $product_id;
			    $this->model_module_fit_and_shop->insert_product_measurement($fit_params);
			}
		}
		    
			
            if( \Extension::isInstalled('lableb') ){
                $this->lablebIndexing($product_id);
            }


            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['redirect'] = '1';
            $result_json['to'] = $this->url->link(
                'catalog/product/update',
                'product_id=' . $product_id,
                'SSL'
            )->format();
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }

        if($this->products_limit && ($this->model_catalog_product->getTotalProductsCount() + 1) > $this->products_limit){
            $this->base = "common/base";
            $this->data = $this->language->load('error/permission');
            $this->document->setTitle($this->language->get('heading_title'));
            $this->template = 'error/permission.expand';
            $this->response->setOutput($this->render_ecwig());
            return;
        }

        $this->data['links'] = [
            'submit' => $this->url->link('catalog/product/insert', '', 'SSL'),
            'cancel' => $this->url->link('catalog/product', '', 'SSL'),
        ];

        $this->data['default_tax_class']  =  $this->config->get('default_tax_class');
        // Here we will handle the logic that will define wheather will we use insert wizard or not.
        //$this->data['insertWizard'] = true;

        $this->getForm();
    }
    
    private function flat_recurisve_array($key,$value){
        
        if(is_array($value)){
            foreach($value as $k => $va){
                return [$key=>$this->flat_recurisve_array($k,$va)];
            }
        }
        else{
            return [$key=>$value];
        }
    }

    private function NoExtraSpaces($input_text)
    {
        $first_index = 0;
        $last_index = 0;
        $input_text_length = strlen($input_text);
        for ($i=0; $i < $input_text_length; $i++)
        { 
            if($input_text[$i] != ' ')
            {
                $first_index = $i;
                break;
            }
        }

        for ($i= $input_text_length - 1; $i >= $first_index; $i--)
        { 
            if($input_text[$i] != ' ')
            {
                $last_index = $i;
                break;
            }
        }

        $output_text = "";
        
        for ($i=$first_index; $i <= $last_index; $i++)
        {
            $output_text .= $input_text[$i];
        }

        return $output_text;
    }
    
    private function repair_decoding($decoded_html)
    {
        $parts_of_decoded_html = explode('% ',$decoded_html);
        $count = count($parts_of_decoded_html);
        for ($i=0; $i < $count; $i++)
        { 
            $new_text = $this->NoExtraSpaces($parts_of_decoded_html[$i]);
            $parts_of_decoded_html[$i] = $new_text;
        }
        
        $decoded_html = implode('% ',$parts_of_decoded_html);
        $repaired_html = str_replace('% 26','&',$decoded_html);
        return $repaired_html;
    }

    private function parse_product_inputs()
    {
        $decoded_html = html_entity_decode($this->request->post['inputs']);
        $repaired_html = $this->repair_decoding($decoded_html);
        $inputs_data = json_decode($repaired_html);
        unset($this->request->post['inputs']);
        $data=[];
        $productCategoryCount = 0;
        $productAttributeValueCount = 0;
        $stockForecastingCount = 0;
        $i=0;
        $i2 = 1;
        $i4 = 1;
        foreach ($inputs_data as $input) {
            if(preg_match('/stock_forecasting\[\d+\]\[quantity\]/' ,$input->name)){
                $data = array_merge($data , ['stock_forecasting['.$stockForecastingCount.'][quantity]' => $input->value]);
            }
            else if(preg_match('/stock_forecasting\[\d+\]\[day\]/' ,$input->name)){
                $data = array_merge($data , ['stock_forecasting['.$stockForecastingCount++.'][day]' => $input->value]);
            }
            
            else if(preg_match('/product_advanced_attribute\[\d+\]\[values\]\[\]/' ,$input->name)){
                $data = array_merge($data , 
                    [
                        substr($input->name, 0, -1) . $productAttributeValueCount++ . "]" => $input->value 
                    ]);
            }
            else if($input->name == "product_category[]"){
                $data = array_merge($data,array("product_category[".$productCategoryCount++."]" => $input->value));        
            }
            else if($input->name == "product_related[]"){
                $data = array_merge($data,array("product_related[".$i++."]" => $input->value));        
            }
            else if($input->name == "product_bundles[]"){
                $data = array_merge($data,array("product_bundles[".$i++."]" => $input->value));        
            }
            else if($input->name == "product_download[]"){
                $data = array_merge($data,array("product_download[".$i++."]" => $input->value));        
            }
            else if(strcasecmp($input->name,"product_classification[$i2][year][]") == 0){
                foreach ($inputs_data as $input) {
                    if(strcasecmp($input->name,"product_classification[$i2][year][]") == 0){
                        $data = array_merge($data,array("product_classification[$i2][year][".$i++."]" => $input->value));
                    }
                }
                $i2++;
            }
            else if(strcasecmp($input->name,"product_classification[$i4][model][]") == 0){
                foreach ($inputs_data as $input) {
                    if(strcasecmp($input->name,"product_classification[$i4][model][]") == 0){
                        $data = array_merge($data,array("product_classification[$i4][model][".$i++."]" => $input->value));
                    }
                }
                $i4++;
            }
            else{
                if (
                    preg_match('/product_classification\[[0-9]\]\[year\]\[\]/',$input->name) || 
                    preg_match('/product_classification\[[0-9]\]\[model\]\[\]/',$input->name)
                    )
                {
                    $i = 0;
                    continue;
                }
                $i = 0;
                $data = array_merge($data, array($input->name => $input->value));
            }
        }

        $post_data=http_build_query($data);
        $exploded=explode('&',$post_data);

        foreach ($exploded as $input) {
            $temp = array();
            parse_str($input, $temp);
            list($key, $value) = each($temp);            
            $arr=$this->flat_recurisve_array($key,$value);
           // var_dump($arr);
            $this->request->post=array_replace_recursive($this->request->post,$arr);
        }
    }
    
    public function update()
    {

        // Product Option Image PRO module <<
        $this->language->load('module/product_option_image_pro');
        // >> Product Option Image PRO module
        
        $this->language->load('module/advanced_product_attributes');
        $this->language->load('module/fit_and_shop');
        $product_id = $this->request->get['product_id'];

        if(\Extension::isInstalled('trips') &&$this->config->get('trips')['status']==1)
        {
         $this->load->model('module/trips');
        $trip = $this->model_module_trips->IsTrip($product_id);
        if($trip){
            $this->data['isTrip']=true;
            $this->language->load('module/trips');
            $this->data['trip_data'] = $this->model_module_trips->getTripProduct($product_id);
         }

        }
        $this->language->load('catalog/product');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('catalog/product');
        $this->load->model('module/fit_and_shop');
        $this->data['product_id'] = $product_id;
        $fit_and_shop_settings = $this->config->get('fit_and_shop');
        $fit_and_shop_settings['flag'] = true;
        if( \Extension::isInstalled('fit_and_shop') && $fit_and_shop_settings['status'] == 1 ){
            $this->data['is_installed_fit_and_shop'] = true;
            $this->data['measurments_categories'] = $this->model_module_fit_and_shop->get_categories();
            $this->data['fit_and_shop_get_collections_ajax_url'] = $this->url->link( 'module/fit_and_shop/ajax_get_collections', '', true);
            $this->data['product_fit_app_data'] =  $this->model_module_fit_and_shop->get_product_measurment($product_id);
            $this->data['fit_status'] = $this->data['product_fit_app_data']['fit_status'];
            $this->data['fit_cat_id'] = $this->data['product_fit_app_data']['measurment_category_id'];
            $this->data['collection_sku'] = $this->data['product_fit_app_data']['collection_sku'];
            $this->data['fit_and_shop_api_key'] = $fit_and_shop_settings['apikey'];
			if($this->data['product_fit_app_data']){
                $this->data['collections'] = $this->model_module_fit_and_shop->getCollections($fit_and_shop_settings);
                $this->data['show_collections'] = true;
            }

        }

        if(\Extension::isInstalled('facebook_business')) {

            $result = $this->db->query("SELECT isPublishedOnCatalog, fbCatalogId from product where product_id =  " . $product_id);

            $isPublished = $result->row;

            if($isPublished["isPublishedOnCatalog"] == "1" && $isPublished["fbCatalogId"] != "") {

                $this->data["isPublishedOnCatalog"] = 1;
            } else {
                $this->data["isPublishedOnCatalog"] = 0;
            }

        }

        $limit_reached = false;
        if($this->products_limit){
            $this->last_product_in_limit_id= $this->model_catalog_product->getLastProductInLimitId($this->products_limit);
            if($this->last_product_in_limit_id && $product_id > $this->last_product_in_limit_id ){
                $this->data['limit_reached'] = '1';
                $limit_reached = true;
            }
        }

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {


            $this->parse_product_inputs();            

            $measurment_category_product_id = $this->request->post['measurment_category_product_id'];
            $this->request->post['product_special'] = $this->request->post['product_discount'] = [];

            foreach ($this->request->post['product_special_discount'] as $specialDiscount) {
                if ($specialDiscount['quantity'] > 1) {
                    $this->request->post['product_discount'][] = $specialDiscount;
                } else {
                    $this->request->post['product_special'][] = $specialDiscount;
                }
            }

            if (!$this->validateForm() || !$this->validateSkuVariations()) {
                $result_json['success'] = '0';
                $result_json['duplicate'] = $this->duplicate;
                $result_json['errors'] = $this->error;

                $this->response->setOutput(json_encode($result_json));

                return;
            }

            $this->request->post['pd_custom_price'] = json_encode($this->request->post['pd_custom_price']);

            if (!isset($this->request->post['status']) || $limit_reached) {
                $this->request->post['status'] = '0';
            }
            $this->request->post['image'] = array_shift($this->request->post['product_image'])['image'] ?: '';


            $this->load->model('multiseller/status');

            $isMultisellerInstalled = $this->model_multiseller_status->is_installed();

            // get product status before update if multiseller is installed
            if ($isMultisellerInstalled) {
                $productStatus = $this->model_catalog_product->getProductStatus($product_id);
                if (isset($productStatus['product_status'])) {
                    $productStatus = $productStatus['product_status'];
                }
            }

            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("product");
            if($pageStatus){
            // get old data
            $this->load->model('catalog/attribute');
            $this->load->model('catalog/option');

            $oldValue['product_info'] = $this->model_catalog_product->getProduct($product_id);
            $oldValue['product_description'] = $this->model_catalog_product->getProductDescriptions($product_id);
            $oldValue['product_store'] = $this->model_catalog_product->getProductStores($product_id);
            $oldValue['categories'] = $this->model_catalog_product->getProductCategories($product_id);
            $oldValue['filters'] = $this->model_catalog_product->getProductFilters($product_id);
            $oldValue['product_images'] = $this->model_catalog_product->getProductImages($product_id);
            $oldValue['product_discounts'] = $this->model_catalog_product->getProductDiscounts($product_id);
            $product_attributes = $this->model_catalog_product->getProductAttributes($product_id);

            $oldValue['product_attributes'] =  array();

            foreach ($product_attributes as $product_attribute) {
                $attribute_info = $this->model_catalog_attribute->getAttribute($product_attribute['attribute_id']);

                if ($attribute_info) {
                    $oldValue['product_attributes'][] = array(
                        'attribute_id' => $product_attribute['attribute_id'],
                        'name' => $attribute_info['name'],
                        'GroupName' => $attribute_info['GroupName'],
                        'product_attribute_description' => $product_attribute['product_attribute_description']
                    );
                }
            }

            $product_options = $this->model_catalog_product->getProductOptions($product_id);

            $oldValue['product_options'] = array();

            foreach ($product_options as $product_option) {
                $option_info = $this->model_catalog_option->getOption($product_option['option_id']);

                if ($option_info) {
                    if (
                        $option_info['type'] == 'select' ||
                        $option_info['type'] == 'radio' ||
                        $option_info['type'] == 'checkbox' ||
                        $option_info['type'] == 'image' ||
                        $option_info['type'] == 'product'
                    ) {
                        $product_option_value_data = array();

                        foreach ($product_option['product_option_value'] as $product_option_value) {
                            $optionValueData  = $this->model_catalog_option->getOptionValue($product_option_value['option_value_id']);
                            $product_option_value_data[] = array(
                                // Product Option Image PRO module <<
                                'images' => (isset($product_option_value['images']) ? $product_option_value['images'] : []),
                                // >> Product Option Image PRO module
                                'product_option_value_id' => $product_option_value['product_option_value_id'],
                                'option_value_id' => $product_option_value['option_value_id'],
                                'quantity' => $product_option_value['quantity'],
                                'subtract' => $product_option_value['subtract'],
                                'price' => $product_option_value['price'],
                                'price_prefix' => $product_option_value['price_prefix'],
                                'points' => $product_option_value['points'],
                                'points_prefix' => $product_option_value['points_prefix'],
                                'weight' => $product_option_value['weight'],
                                'weight_prefix' => $product_option_value['weight_prefix'],
                                'name' => $optionValueData['name']
                            );
                        }

                        $oldValue['product_options'][] = array(
                            'product_option_id' => $product_option['product_option_id'],
                            'product_option_value' => $product_option_value_data,
                            'option_id' => $product_option['option_id'],
                            'name' => $option_info['name'],
                            'type' => $option_info['type'],
                            'required' => $product_option['required']
                        );
                    } else {
                        $oldValue['product_options'][] = array(
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

            $oldValue['product_discounts'] = $this->model_catalog_product->getProductDiscounts($product_id);

            // add data to log_history

                $log_history['action'] = 'update';
                $log_history['reference_id'] = $product_id;
                $log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
                $log_history['new_value'] = json_encode($this->request->post,JSON_UNESCAPED_UNICODE);
                $log_history['type'] = 'product';
                $this->load->model('loghistory/histories');
                $this->model_loghistory_histories->addHistory($log_history);
            }

            $this->model_catalog_product->editProduct($product_id, $this->request->post);
            
            // ZOHO inventory update product if app is setup
            $this->load->model('module/zoho_inventory');
            $this->model_module_zoho_inventory->updateProduct($product_id, $this->request->post);

            // Odoo update product if app is setup
            if (\Extension::isInstalled('odoo') && $this->config->get('odoo')['status']
            && $this->config->get('odoo')['products_integrate'])
            {
                $this->load->model('module/odoo/products');
                $this->model_module_odoo_products->updateProduct($product_id, $this->request->post);
            }

            $this->load->model('module/advanced_product_attributes/attribute');
            $this->model_module_advanced_product_attributes_attribute->addProductAttributes($product_id, $this->request->post['product_advanced_attribute']);

            if (isset($productStatus)) {

                if ($productStatus == 2) {
                    $productStatus = 0;
                }
    
                // check if product status has already been changed to avoid sending redundant mails
                if ($productStatus != $this->request->post['status']) {
                    $this->load->model("multiseller/product");
                    $this->load->model("setting/setting");
                    $this->load->model("localisation/language");
        
                    $adminActiveLanguageCode = $this->model_setting_setting->getSetting("config")["config_admin_language"];
                    $activeLanguageId = $this->model_localisation_language->getLanguageByCode($adminActiveLanguageCode)["language_id"];
                    $sellerId = $this->model_multiseller_product->getProductSellerId($product_id)["seller_id"];
                    
                    if (!empty($sellerId)) {

                        $seller_info = $this->MsLoader->MsSeller->getSellerBasic($sellerId);
                        $productName = $this->model_catalog_product->getProductName($product_id, $activeLanguageId)["name"];
                        $_vars = [
                            'store_url'        => HTTP_CATALOG,
                            'seller_email'     => $seller_info['email'], 
                            'seller_firstname' => $seller_info['firstname'],
                            'seller_lastname'  => $seller_info['lastname'],
                            'seller_nickname'  => $seller_info['nickname'],
                            'product_name'     => $productName
                        ];
            
                        if($this->request->post['status'] == 0){
                            $mails[] = array(
                                        'type' => MsMail::SMT_PRODUCT_MODIFIED,
                                        'data' => array('class' => 'ControllerCatalogProduct', 
                                                        'function' => 'dtUpdateStatus_reject',
                                                        'vars'   => $_vars,
                                                        'recipients' => $seller_info['email'],
                                                        'addressee' => $seller_info['firstname']
                                                    )
                                    );
                        }else if($this->request->post['status'] == 1){
                            $mails[] = array(
                                        'type' => MsMail::SMT_PRODUCT_MODIFIED,
                                        'data' => array('class' => 'ControllerCatalogProduct', 
                                                        'function' => 'dtUpdateStatus_approve',
                                                        'vars'   => $_vars,
                                                        'recipients' => $seller_info['email'],
                                                        'addressee' => $seller_info['firstname']
                                                    )
                                    );
                        }
            
                        $this->MsLoader->MsMail->sendMails($mails);

                    }

                }

            }

           if( \Extension::isInstalled('fit_and_shop') && $fit_and_shop_settings['status'] == 1 ){
                if( $this->request->post['fit_status']){
                    //check if fit status is still active to update changes in db
                    $fit_params = array();
                    $fit_params['fit_status'] = $this->request->post['fit_status'];
                    $fit_params['collection_sku'] = $this->request->post['categories_collections_select'];
                    $fit_params['measurment_category_id'] = $this->request->post['fit_measurment_cat'];
                    if($measurment_category_product_id){
                        $fit_params['measurment_category_product_id'] = $measurment_category_product_id;
                        $this->model_module_fit_and_shop->update_product_measurement($fit_params);
                    }else{
                        $fit_params['product_id'] = $product_id;
                        $this->model_module_fit_and_shop->insert_product_measurement($fit_params);
                    }

                }else{
                    // if the user deactive fit data from this product
                    $this->model_module_fit_and_shop->delete_product_measurement($measurment_category_product_id);
                }
	    }
		
            if( \Extension::isInstalled('lableb')) {
                    $this->lablebIndexing($product_id);
            }
			

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            if(isset($this->request->post['duplicate']) && $this->request->post['duplicate'] == 1){
                $result_json['to'] = $this->url->link(
                    'catalog/product/update',
                    'product_id=' . $product_id,
                    'SSL'
                )->format();
                $result_json['redirect'] = '1';
            }
            $result_json['success'] = '1';

            $this->response->setOutput(json_encode($result_json));
            return;
        }

        //check if dropna product
        $this->data['isDropna'] = false;
        /*$dropnaProduct = $this->model_catalog_product->getDropnaProductById($product_id);
        if($dropnaProduct['dropna_product_id']){
            $this->data['isDropna']   = true;
            $this->data['dropna_url'] = DROPNA_DOMAIN.'client/products/'.$product_id;
        }
        else{
            $this->data['isDropna'] = false;
        }*/
        /////

        $this->data['links'] = [
            'submit' => $this->url->link(
                'catalog/product/update',
                'product_id=' . $product_id,
                'SSL'
            ),
            'cancel' => $this->url->link('catalog/component/product', '', 'SSL'),
        ];

        $onlineCourses = ['installed' => false, 'enabled' => false];
        if (\Extension::isInstalled('online_courses')) {
            $onlineCourses['installed'] = true;
            $_onlineCourses = $this->config->get('online_courses');
            $onlineCourses['enabled'] = $_onlineCourses['status'];
        }

        $this->data['online_courses'] = $onlineCourses;
        
        // Check if Custom Editor App is installed and active
        $this->load->model('module/custom_product_editor');
        if ($this->model_module_custom_product_editor->isActive()) {
            $this->data['allow_editor'] = 1;
        }

        $this->getForm();
    }

    public function delete()
    {
        // Product Option Image PRO module <<
        $this->language->load('module/product_option_image_pro');
        // >> Product Option Image PRO module

        $this->language->load('catalog/product');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('catalog/product');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            // ZOHO inventory delete products
            $this->load->model('module/zoho_inventory');
            $attrs = [];
            foreach ($this->request->post['selected'] as $product_id) {

                $this->load->model('setting/audit_trail');
                $pageStatus = $this->model_setting_audit_trail->getPageStatus("product");
                if($pageStatus) {
                // get old data
                $this->load->model('catalog/attribute');
                $this->load->model('catalog/option');

                $oldValue['product_info'] = $this->model_catalog_product->getProduct($product_id);
                $oldValue['product_description'] = $this->model_catalog_product->getProductDescriptions($product_id);
                $oldValue['product_store'] = $this->model_catalog_product->getProductStores($product_id);
                $oldValue['categories'] = $this->model_catalog_product->getProductCategories($product_id);
                $oldValue['filters'] = $this->model_catalog_product->getProductFilters($product_id);
                $oldValue['product_images'] = $this->model_catalog_product->getProductImages($product_id);
                $product_attributes = $this->model_catalog_product->getProductAttributes($product_id);

                $oldValue['product_attributes'] =  array();

                foreach ($product_attributes as $product_attribute) {
                    $attribute_info = $this->model_catalog_attribute->getAttribute($product_attribute['attribute_id']);

                    if ($attribute_info) {
                        $oldValue['product_attributes'][] = array(
                            'attribute_id' => $product_attribute['attribute_id'],
                            'name' => $attribute_info['name'],
                            'GroupName' => $attribute_info['GroupName'],
                            'product_attribute_description' => $product_attribute['product_attribute_description']
                        );
                    }
                }

                $product_options = $this->model_catalog_product->getProductOptions($product_id);

                $oldValue['product_options'] = array();

                foreach ($product_options as $product_option) {
                    $option_info = $this->model_catalog_option->getOption($product_option['option_id']);

                    if ($option_info) {
                        if (
                            $option_info['type'] == 'select' ||
                            $option_info['type'] == 'radio' ||
                            $option_info['type'] == 'checkbox' ||
                            $option_info['type'] == 'image' ||
                            $option_info['type'] == 'product'
                        ) {
                            $product_option_value_data = array();

                            foreach ($product_option['product_option_value'] as $product_option_value) {
                                $product_option_value_data[] = array(
                                    // Product Option Image PRO module <<
                                    'images' => (isset($product_option_value['images']) ? $product_option_value['images'] : []),
                                    // >> Product Option Image PRO module
                                    'product_option_value_id' => $product_option_value['product_option_value_id'],
                                    'option_value_id' => $product_option_value['option_value_id'],
                                    'quantity' => $product_option_value['quantity'],
                                    'subtract' => $product_option_value['subtract'],
                                    'price' => $product_option_value['price'],
                                    'price_prefix' => $product_option_value['price_prefix'],
                                    'points' => $product_option_value['points'],
                                    'points_prefix' => $product_option_value['points_prefix'],
                                    'weight' => $product_option_value['weight'],
                                    'weight_prefix' => $product_option_value['weight_prefix'],
                                    'name' => $product_option_value['name']
                                );
                            }

                            $oldValue['product_options'][] = array(
                                'product_option_id' => $product_option['product_option_id'],
                                'product_option_value' => $product_option_value_data,
                                'option_id' => $product_option['option_id'],
                                'name' => $option_info['name'],
                                'type' => $option_info['type'],
                                'required' => $product_option['required']
                            );
                        } else {
                            $oldValue['product_options'][] = array(
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

                $oldValue['product_discounts'] = $this->model_catalog_product->getProductDiscounts($product_id);
                // add data to log_history

                    $log_history['action'] = 'delete';
                    $log_history['reference_id'] = $product_id;
                    $log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
                    $log_history['new_value'] = NULL;
                    $log_history['type'] = 'product';
                    $this->model_loghistory_histories->addHistory($log_history);
                }

                $zoho_inventory_settings = $this->config->get('zoho_inventory');
                $this->model_catalog_product->deleteProduct($product_id);
                $this->model_module_zoho_inventory->deleteProduct($product_id,$zoho_inventory_settings['organization_id']);
				
				 if (\Extension::isInstalled('lableb')) {
                    $this->lablebDeleteIndexing($product_id);
                }

                $attrs[] = ['product_id' => $product_id];
            }

            $store_statistics = new StoreStatistics($this->user);
            $store_statistics->store_statistcs_push('products', 'delete', $attrs);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            $queryAuctionModule = $this->db->query(
                "SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'special_count_down'"
            );

            if ($queryAuctionModule->num_rows) {
                if (isset($this->error['start_time'])) {
                    $this->data['error_start'] = $this->error['start_time'];
                } else {
                    $this->data['error_start'] = '';
                }

                if (isset($this->error['bid_price'])) {
                    $this->data['error_bid_price'] = $this->error['bid_price'];
                } else {
                    $this->data['error_bid_price'] = '';
                }


                if (isset($this->error['end_time'])) {
                    $this->data['error_end_time'] = $this->error['end_time'];
                } else {
                    $this->data['error_end_time'] = '';
                }


                if (isset($this->error['max_price'])) {

                    $this->data['error_max_price'] = $this->error['max_price'];

                } else {

                    $this->data['error_max_price'] = '';

                }
            }

            if (isset($this->request->get['filter_name'])) {
                $url .= '&filter_name=' . urlencode(html_entity_decode(
                        $this->request->get['filter_name'],
                        ENT_QUOTES,
                        'UTF-8'
                    ));
            }

            if (isset($this->request->get['filter_model'])) {
                $url .= '&filter_model=' . urlencode(html_entity_decode(
                        $this->request->get['filter_model'],
                        ENT_QUOTES,
                        'UTF-8'
                    ));
            }

            if (isset($this->request->get['filter_price'])) {
                $url .= '&filter_price=' . $this->request->get['filter_price'];
            }

            if (isset($this->request->get['filter_quantity'])) {
                $url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
            }

            if (isset($this->request->get['filter_status'])) {
                $url .= '&filter_status=' . $this->request->get['filter_status'];
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

            $this->redirect($this->url->link('catalog/product', $url, 'SSL'));
        }

        $this->getList();
    }

    public function copy()
    {
        $this->load->model('catalog/product');

        if (
            $this->products_limit &&
            $this->model_catalog_product->getTotalProductsCount() + count($this->request->post['selected']) > $this->products_limit
        ) {

            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors'] = sprintf($this->language->get('error_maximum_product_number'),$this->products_limit);

            $this->response->setOutput(json_encode($response));
            return;
        }


        // Product Option Image PRO module <<
        $this->language->load('module/product_option_image_pro');
        // >> Product Option Image PRO module

        $this->language->load('catalog/product');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('catalog/product');

        if (isset($this->request->post['selected'])) {

            if (!$this->validateCopy()) {
                $response['status'] = '0';
                $response['title'] = $this->language->get('notification_error_title');
                $response['errors'] = $this->error;
                $this->response->setOutput(json_encode($response));
                return;
            }

            $attrs = [];
            foreach ($this->request->post['selected'] as $product_id) {
                $new_product_id = $this->model_catalog_product->copyProduct($product_id);
                $response['new_ids'][] = $new_product_id;
                $product = $this->model_catalog_product->getProduct($new_product_id);
                $attrs[] = [
                    'product_id' => $product_id,
                    'price' => $product['price']
                ];
            }

            $store_statistics = new StoreStatistics($this->user);
            $store_statistics->store_statistcs_push('products', 'create', $attrs);

            $response['status'] = '1';
            $response['success_msg'] = $this->language->get('text_success');
            $this->response->setOutput(json_encode($response));
            return;
        }
        $response['status'] = '0';
        $response['errors'] = $this->language->get('error_invalid_parameters');
        $this->response->setOutput(json_encode($response));
        return;
            /*
            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            $queryAuctionModule = $this->db->query(
                "SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'special_count_down'"
            );

            if ($queryAuctionModule->num_rows) {
                if (isset($this->error['start_time'])) {
                    $this->data['error_start'] = $this->error['start_time'];
                } else {
                    $this->data['error_start'] = '';
                }

                if (isset($this->error['bid_price'])) {
                    $this->data['error_bid_price'] = $this->error['bid_price'];
                } else {
                    $this->data['error_bid_price'] = '';
                }


                if (isset($this->error['end_time'])) {
                    $this->data['error_end_time'] = $this->error['end_time'];
                } else {
                    $this->data['error_end_time'] = '';
                }


                if (isset($this->error['max_price'])) {

                    $this->data['error_max_price'] = $this->error['max_price'];

                } else {

                    $this->data['error_max_price'] = '';

                }
            }

            if (isset($this->request->get['filter_name'])) {
                $url .= '&filter_name=' . urlencode(html_entity_decode(
                        $this->request->get['filter_name'],
                        ENT_QUOTES,
                        'UTF-8'
                    ));
            }

            if (isset($this->request->get['filter_model'])) {
                $url .= '&filter_model=' . urlencode(html_entity_decode(
                        $this->request->get['filter_model'],
                        ENT_QUOTES,
                        'UTF-8'
                    ));
            }

            if (isset($this->request->get['filter_price'])) {
                $url .= '&filter_price=' . $this->request->get['filter_price'];
            }

            if (isset($this->request->get['filter_quantity'])) {
                $url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
            }

            if (isset($this->request->get['filter_status'])) {
                $url .= '&filter_status=' . $this->request->get['filter_status'];
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

            $this->redirect($this->url->link('catalog/product', $url, 'SSL'));
        }

        $this->getList();
            */
    }

    public function getList()
    {
        $this->initializer([
            'filter' => 'catalog/product_filter'
        ]);

        $this->language->load('catalog/product_filter');

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('catalog/product', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->load->model('catalog/product');
        $countProducts = $this->model_catalog_product->countProducts();

        if ($countProducts == 0){
            $this->template = 'catalog/product/empty.expand';
        }
        else {
            $this->template = 'catalog/product/grid.expand';
        }
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->data['template'] = 'list';
        if (isset($this->request->request['view']) && $this->request->request['view'] == 'thumbnails') {
            $this->data['template'] = 'thumbnails';
        }


        $this->data['queryString'] = $this->request->server['QUERY_STRING'];

        $this->data['filterElements'] = $this->filter->getFilter();

        $this->data['filterData'] = $this->session->data['filterData'];
        
        $this->data['insertWizard'] = true;

        $this->data['wk_amazon_connector_status'] = $this->config->get('wk_amazon_connector_status');

        //Get all tax classes
        $this->load->model('localisation/tax_class');
        $this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

        // order product quantity
        $orderStatuses = $this->config->get('pending_order_statuses') ?? [];

        $this->data['show_order_product_quantity'] = count($orderStatuses) > 0 ? 1 : 0;

        $this->response->setOutput($this->render_ecwig());
    }

    public function getXHRList()
    {
        $this->language->load('catalog/product');
        
        $this->language->load('catalog/product_filter');

        $request = $this->request->request;

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['direction'] = $this->language->get('direction');

        $this->load->model('catalog/product');

        if ($this->request->server['REQUEST_METHOD'] == 'POST'&& !empty($this->request->post['filter'])) {
            parse_str(html_entity_decode($this->request->post['filter']), $filterData);
            $filterData = $filterData['filter'];
            
            $this->session->data['filterData'] = $filterData;
        }else{
            if (isset($this->request->post['resetFilters']) && $this->request->post['resetFilters'])
                $filterData = array();
            else
                $filterData = $this->session->data['filterData'];
        }

        $queryAuctionModule = $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'special_count_down'"
        );

        if ($queryAuctionModule->num_rows) {
            if (isset($this->error['start_time'])) {
                $this->data['error_start'] = $this->error['start_time'];
            } else {
                $this->data['error_start'] = '';
            }

            if (isset($this->error['bid_price'])) {
                $this->data['error_bid_price'] = $this->error['bid_price'];
            } else {
                $this->data['error_bid_price'] = '';
            }


            if (isset($this->error['end_time'])) {
                $this->data['error_end_time'] = $this->error['end_time'];
            } else {
                $this->data['error_end_time'] = '';
            }


            if (isset($this->error['max_price'])) {

                $this->data['error_max_price'] = $this->error['max_price'];

            } else {

                $this->data['error_max_price'] = '';

            }
        }

        $this->data['products'] = array();

        $start  = $request['start'];
        $length = $request['length'];

        $orderColumn = $request['columns'][$request['order'][0]['column']]['name'];
        $orderColumnData = $request['columns'][$request['order'][0]['column']]['data'];
        $orderType = $request['order'][0]['dir'];

        $filterData['search'] = null;
        if (isset($request['search']['value']) && strlen($request['search']['value']) > 0) {
            $filterData['search'] = $request['search']['value'];
        }

        $data = array(
            'filter_name' => $filter_name,
            'filter_model' => $filter_model,
            'filter_price' => $filter_price,
            'filter_quantity' => $filter_quantity,
            'filter_status' => $filter_status,
            'sort' => $orderColumn,
            'order' => $orderType,
            'start' => $request['start'],
            'limit' => $request['length']
        );

        if ($viewType == 'list') {
            unset($data['start'], $data['limit']);
        }

        if (isset($this->request->get['quantity']) && empty($this->request->post['filter']) ){
            $filterData['ranges']['quantity']['max']=$this->request->get['quantity'];
            $filterData['ranges']['quantity']['min']=$this->request->get['quantity'];
        }

        if(isset($this->session->data['filter_by_category'])){
            $filterData['categories'] = [(int) $this->session->data['filter_by_category']];
        } else if (isset($this->session->data['filter_by_seller'])) {
            $filterData['sellers'] = [(int) $this->session->data['filter_by_seller']];	
        }

        $this->load->model('tool/image');

        //$product_total = $this->model_catalog_product->getTotalProducts($data);

        $results = $this->model_catalog_product->getProductsToFilter($data, $filterData);


        $this->data['locales'] = [
            'show_x_from' => sprintf(
                $this->language->get('filter_show_x_from'),
                $results['totalFiltered'],
                $results['total']
            )
        ];

        $products = [];

        $pendingStatuses = $this->config->get('pending_order_statuses') ?? [];
        $pendingStatusesHasCount = count($pendingStatuses) > 0;

        if($this->products_limit) {
            $this->last_product_in_limit_id= $this->model_catalog_product->getLastProductInLimitId($this->products_limit);
        }

        foreach ($results['data'] as $result) {
            $image = $this->model_tool_image->resize($result['image'], 550, 550);
            $thumb = $this->model_tool_image->resize($result['image'], 40, 40);

            $special = false;

            $product_specials = $this->model_catalog_product->getProductSpecials($result['product_id']);

            foreach ($product_specials as $product_special) {
                $date_start = $product_special['date_start'];
                $date_end = $product_special['date_end'];
                if (
                    (!$date_start || $date_start == null || $date_start == '0000-00-00' || $date_start < date('Y-m-d')) &&
                    (!$date_end || $date_end == null || $date_end == '0000-00-00' || $date_end > date('Y-m-d'))
                ) {
                    $special = $product_special['price'];

                    break;
                }
            }

            $productReviews = $this->model_catalog_product->getProductReviews($result['product_id']);

            /**
             * if   app seller  is installed  and you need show seller name
             */
            $this->load->model('multiseller/status');
            $seller = 'Admin';
            $isMultisellerInstalled = $this->model_multiseller_status->is_installed();
            if ($isMultisellerInstalled){
                $seller = $this->MsLoader->MsSeller->getSellerByProductId($result['product_id']);
            }
            // $productName = (mb_strlen($result['localized_name']) > 20) ?
            //     mb_substr($result['localized_name'], 0, 20) . '...' :
            //     $result['localized_name'];

            if($this->config->get('wk_amazon_connector_status')){ 
                $product_amazon_info = $this->Amazonconnector->showProductInfo($result['product_id']); 
            } 
            else { 
                $product_amazon_info = 1; 
            }

            if ($result['barcode'] != '') {
                $barcodeGenerator = (new BarcodeGenerator())
                    ->setType($this->config->get('config_barcode_type'))
                    ->setBarcode($result['barcode']);
    
                $barcodeImageString = $barcodeGenerator->generate();
            } else {
                $barcodeImageString = 0;
            }

            $order_quantity = 0;

            if ($pendingStatusesHasCount) {
                $order_quantity = $this->model_catalog_product->getProductQuantityInOrders($result['product_id'], $pendingStatuses);
            }
            if(intval($result['quantity']) >= 0)
                $productQuantity = $result['quantity'];
            else
                $productQuantity = 0;

            $limit_reached=false;
            if($this->products_limit){
                if($this->last_product_in_limit_id && $result['product_id'] > $this->last_product_in_limit_id){
                    $limit_reached=true;
                }
            }
            $this->data['products'][] = array(
                'wk_amazon'=> $product_amazon_info,
                'product_id' => $result['product_id'],
                'name' => $result['localized_name'] ?? $result['name'],
                'category_name' => $result['category_name'],
                'category_id' => $result['category_id'],
                "isPublishedOnCatalog" => ($result["isPublishedOnCatalog"] == 1 && $result["fbCatalogId"] == $this->config->get("catalog_id")) ? "yes" : "no",
                'categories_ids'    => $result['categories_ids'],
                'categories_names'  => $result['categories_names'],
                'categories_images' => $result['categories_images'],
                'model' => $result['model'],
                'price' => number_format($result['price'], 2) . ' ' . $this->config->get('config_currency'),
                'order_quantity' => $order_quantity,
                'special' => $special,
                'image' => $image,
                'thumb' => $thumb,
                'quantity' => $result['unlimited'] ? $this->language->get('entry_unlimited_quantity'): $productQuantity,
                'status' => $result['status'],
                'sku' => $result['sku'],
                'barcode' => $result['barcode'],
                'barcode_image' => $barcodeImageString,
                'reviews' => $productReviews,
                'sellers' => ($seller != 'Admin') ? $seller['nickname'] : 'Admin' ,
                'date_added' => $result['date_added'],
                'links' => [
                    'update' => $this->url->link(
                        'catalog/product/update',
                        'product_id=' . $result['product_id'],
                        'SSL'
                    )
                ],
                'limit_reached'=>$limit_reached?"1":"0"
            );
        }

        $pagination = new Pagination();
        $pagination->total = $results['totalFiltered'];//$product_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_admin_limit');
        $pagination->text = $this->language->get('text_pagination');
        $pagination->url = $this->url->link('catalog/product', $url . '&page={page}', 'SSL');

        $this->data['pagination'] = $pagination->render();

        $this->data['filter_name'] = $filter_name;
        $this->data['filter_model'] = $filter_model;
        $this->data['filter_price'] = $filter_price;
        $this->data['filter_quantity'] = $filter_quantity;
        $this->data['filter_status'] = $filter_status;

        $this->data['sort'] = $sort;
        $this->data['order'] = $order;

        // Here we will handle the logic that will define wheather will we use insert wizard or not.
        $this->data['insertWizard'] = true;

        $this->template = 'catalog/product/thumbnails.expand';
        $this->template = 'catalog/product/list.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        
        $sortBy = array_column($this->data['products'], $orderColumnData);
        $orderBy = ($orderType == 'asc') ? SORT_ASC : SORT_DESC;
        array_multisort($sortBy, $orderBy, $this->data['products']);
        
        $this->response->setOutput(json_encode([
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($results['total']),
            "recordsFiltered" => $results['totalFiltered'],
            'data' => $this->data['products'],
            'heading' => $this->data['locales']['show_x_from']
        ]));
        return;
    }

    protected function getForm()
    {
        $data['module_wk_dropship_status'] = false;

	    if( \Extension::isInstalled('aliexpress_dropshipping') && $this->config->get('module_wk_dropship_status') ) {
		if($this->config->get('wk_dropship_user_group') == $this->user->getUserGroup()) {

		    if(isset($this->request->get['warehouse_id']) && $this->request->get['warehouse_id']) {
			$data['warehouse_id'] = $this->request->get['warehouse_id'];
		    }
		    if(!$this->config->get('wk_dropship_warehouse_manager_add_product')) {
			$this->load->language('aliexpress/products');
			$this->session->data['error_warning'] = $this->language->get('error_add_product_not_access');
			$this->response->redirect($this->url->link('catalog/product', '', true));
		    }
		    $data['module_wk_dropship_status'] = true;
		    $data['productTabs'] = $this->config->get('wk_dropship_product_tabs');
		}
	    }


        if(\Extension::isInstalled('facebook_business') && $this->config->get("fbe_access_token") != "") {
            $this->data["isFacebookInstalled"] = "1";
        }

        // Product Option Image PRO module <<
        $this->load->model('module/product_option_image_pro');
        $this->data['poip_installed'] = $this->model_module_product_option_image_pro->installed();
        $this->data['poip_settings_names'] = $this->model_module_product_option_image_pro->getSettingsNames();
        $this->data['poip_settings_values'] = $this->model_module_product_option_image_pro->getSettingsValues();
        $this->data['poip_module_name'] = $this->language->get('poip_module_name');
        $this->data['poip_saved_settings'] = $this->model_module_product_option_image_pro->getRealProductSettings(
            isset($this->request->get['product_id']) ? $this->request->get['product_id'] : 0
        );
        // >> Product Option Image PRO module


        // Product Option Image PRO module <<
        $this->data['entry_sort_order_short'] = $this->language->get('entry_sort_order_short');
        $this->data['poip_settings_select_options'] = [
            '0' => $this->language->get('entry_settings_default'),
            '2' => $this->language->get('entry_settings_yes'),
            '1' => $this->language->get('entry_settings_no')
        ];
        $this->load->model('module/product_option_image_pro');
        $settings_names = $this->model_module_product_option_image_pro->getSettingsNames(false);
        foreach ($settings_names as $setting_name) {
            $this->data['entry_' . $setting_name] = $this->language->get('entry_' . $setting_name);
            $this->data['entry_' . $setting_name . '_help'] = $this->language->get('entry_' . $setting_name . '_help');
        }
        $settings_values = $this->model_module_product_option_image_pro->getSettingsValues();
        foreach ($settings_values as $setting_value) {
            $this->data['entry_' . $setting_value] = $this->language->get('entry_' . $setting_value);
        }
        $this->data['settings_values'] = $settings_values;

        $this->data['entry_show_settings'] = $this->language->get('entry_show_settings');
        $this->data['entry_hide_settings'] = $this->language->get('entry_hide_settings');

        // >> Product Option Image PRO module

        // $queryAuctionModule = $this->db->query(
        //     "SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'special_count_down'"
        // );

        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else if (isset($this->session->data['errors']['quantity_count'])) {
            $this->data['error_warning'] = $this->session->data['errors']['quantity_count'];
            unset($this->session->data['errors']['quantity_count']);
        } else {
            $this->data['error_warning'] = '';
        }

        if (isset($this->error['name'])) {
            $this->data['error_name'] = $this->error['name'];
        } else {
            $this->data['error_name'] = array();
        }

        if (isset($this->error['variation'])) {
            $this->data['errors']['variation'] = $this->error['variation'];
        } else {
            $this->data['errors']['variation'] = '';
        }

        if (isset($this->error['meta_description'])) {
            $this->data['error_meta_description'] = $this->error['meta_description'];
        } else {
            $this->data['error_meta_description'] = array();
        }

        if (isset($this->error['description'])) {
            $this->data['error_description'] = $this->error['description'];
        } else {
            $this->data['error_description'] = array();
        }

        if (isset($this->error['model'])) {
            $this->data['error_model'] = $this->error['model'];
        } else {
            $this->data['error_model'] = '';
        }

        if (isset($this->error['date_available'])) {
            $this->data['error_date_available'] = $this->error['date_available'];
        } else {
            $this->data['error_date_available'] = '';
        }

        $url = '';

        $queryAuctionModule = $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'special_count_down'"
        );

        if ($queryAuctionModule->num_rows) {
            $this->data['AuctionModuleActive'] = 1;
            if (isset($this->error['start_time'])) {
                $this->data['error_start'] = $this->error['start_time'];
            } else {
                $this->data['error_start'] = '';
            }

            if (isset($this->error['bid_price'])) {
                $this->data['error_bid_price'] = $this->error['bid_price'];
            } else {
                $this->data['error_bid_price'] = '';
            }


            if (isset($this->error['end_time'])) {
                $this->data['error_end_time'] = $this->error['end_time'];
            } else {
                $this->data['error_end_time'] = '';
            }


            if (isset($this->error['max_price'])) {

                $this->data['error_max_price'] = $this->error['max_price'];

            } else {

                $this->data['error_max_price'] = '';

            }
        }

        if (isset($this->request->get['filter_name'])) {
            $url .= '&filter_name=' . urlencode(html_entity_decode(
                    $this->request->get['filter_name'],
                    ENT_QUOTES,
                    'UTF-8'
                ));
        }

        if (isset($this->request->get['filter_model'])) {
            $url .= '&filter_model=' . urlencode(html_entity_decode(
                    $this->request->get['filter_model'],
                    ENT_QUOTES,
                    'UTF-8'
                ));
        }

        if (isset($this->request->get['filter_price'])) {
            $url .= '&filter_price=' . $this->request->get['filter_price'];
        }

        if (isset($this->request->get['filter_quantity'])) {
            $url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
        }

        if (isset($this->request->get['filter_status'])) {
            $url .= '&filter_status=' . $this->request->get['filter_status'];
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

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('catalog/product', '' . $url, 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => !isset($this->request->get['product_id']) ? $this->language->get('breadcrumb_insert') : $this->language->get('breadcrumb_update'),
            'href' => $this->url->link('catalog/product', '' . $url, 'SSL'),
            'separator' => ' :: '
        );

        if (!isset($this->request->get['product_id'])) {
            $this->data['action'] = $this->url->link('catalog/product/insert', $url, 'SSL');
        } else {
            $this->data['action'] = $this->url->link(
                'catalog/product/update',
                'product_id=' . $this->request->get['product_id'] . $url,
                'SSL'
            );
        }

        $this->data['cancel'] = $this->url->link('catalog/product', $url, 'SSL');

        if (isset($this->request->get['product_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);
        }

        $this->data['token'] = null;

        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();

        $this->data['first_language_name'] = array_values($languages)[0]['name'];

        $this->data['languages'] = $languages;

        if (isset($this->request->post['product_description'])) {
            $this->data['product_description'] = $this->request->post['product_description'];
        } elseif (isset($this->request->get['product_id'])) {
            $this->data['product_description'] = $this->model_catalog_product->getProductDescriptions(
                $this->request->get['product_id']
            );
        } else {
            $this->data['product_description'] = array();
        }

        if (isset($this->request->post['model'])) {
            $this->data['model'] = $this->request->post['model'];
        } elseif (!empty($product_info)) {
            $this->data['model'] = $product_info['model'];
        } else {
            $this->data['model'] = '';
        }

        if (isset($this->request->post['is_simple'])) {
            $this->data['is_simple'] = $this->request->post['is_simple'];
        } elseif (!empty($product_info)) {
            $this->data['is_simple'] = $product_info['is_simple'];
        } else {
            $this->data['is_simple'] = 1;
        }

        $this->data['storable'] = 0;
        if (isset($product_info['storable'])) {
            $this->data['storable'] = $product_info['storable'];
        }

        if (isset($this->request->post['affiliate_link'])) {
            $this->data['affiliate_link'] = $this->request->post['affiliate_link'];
        } elseif (!empty($product_info) && isset($product_info['affiliate_link'])) {
            $this->data['affiliate_link'] = $product_info['affiliate_link'];
        } else {
            $this->data['affiliate_link'] = '';
        }

        if (isset($this->request->post['sku'])) {
            $this->data['sku'] = $this->request->post['sku'];
        } elseif (!empty($product_info)) {
            $this->data['sku'] = $product_info['sku'];
        } else {
            $this->data['sku'] = '';
        }

        //Seller Notes..
        $this->data['multiseller_installed'] = \Extension::isInstalled('multiseller');

        if($this->data['multiseller_installed']){
            
            if (isset($this->request->post['seller_notes'])) {
                $this->data['seller_notes'] = $this->request->post['seller_notes'];
            } elseif (isset($this->request->get['product_id'])) {
                $this->data['seller_notes'] = $this->model_catalog_product->getProductDescriptions($this->request->get['product_id']);
            } else {
                $this->data['seller_notes'] = [];
            }
            $ms_product_file =  $this->MsLoader->MsProduct->getProductFile($this->request->get['product_id']);
            if($ms_product_file){
                $ms_product_file_array['mask'] = end(explode('_',$ms_product_file));
                $ms_product_file_array['href'] = $this->url->link('multiseller/product/download', 'download=' . $ms_product_file, 'SSL');
                $this->data['ms_product_file'] = $ms_product_file_array;
            }
        }

        if (isset($this->request->post['upc'])) {
            $this->data['upc'] = $this->request->post['upc'];
        } elseif (!empty($product_info)) {
            $this->data['upc'] = $product_info['upc'];
        } else {
            $this->data['upc'] = '';
        }

        if (isset($this->request->post['ean'])) {
            $this->data['ean'] = $this->request->post['ean'];
        } elseif (!empty($product_info)) {
            $this->data['ean'] = $product_info['ean'];
        } else {
            $this->data['ean'] = '';
        }

        if (isset($this->request->post['jan'])) {
            $this->data['jan'] = $this->request->post['jan'];
        } elseif (!empty($product_info)) {
            $this->data['jan'] = $product_info['jan'];
        } else {
            $this->data['jan'] = '';
        }

        if (isset($this->request->post['isbn'])) {
            $this->data['isbn'] = $this->request->post['isbn'];
        } elseif (!empty($product_info)) {
            $this->data['isbn'] = $product_info['isbn'];
        } else {
            $this->data['isbn'] = '';
        }

        if (isset($this->request->post['mpn'])) {
            $this->data['mpn'] = $this->request->post['mpn'];
        } elseif (!empty($product_info)) {
            $this->data['mpn'] = $product_info['mpn'];
        } else {
            $this->data['mpn'] = '';
        }

        if (isset($this->request->post['location'])) {
            $this->data['location'] = $this->request->post['location'];
        } elseif (!empty($product_info)) {
            $this->data['location'] = $product_info['location'];
        } else {
            $this->data['location'] = '';
        }

        $this->load->model('setting/store');

        $this->data['stores'] = $this->model_setting_store->getStores();

        if (isset($this->request->post['product_store'])) {
            $this->data['product_store'] = $this->request->post['product_store'];
        } elseif (isset($this->request->get['product_id'])) {
            $this->data['product_store'] = $this->model_catalog_product->getProductStores(
                $this->request->get['product_id']
            );
        } else {
            $this->data['product_store'] = array(0);
        }

        if (isset($this->request->post['keyword'])) {
            $this->data['keyword'] = $this->request->post['keyword'];
        } elseif (!empty($product_info)) {
            $this->data['keyword'] = $product_info['keyword'];
        } else {
            $this->data['keyword'] = '';
        }

        if (isset($this->request->post['image'])) {
            $this->data['image'] = $this->request->post['image'];
        } elseif (!empty($product_info)) {
            $this->data['image'] = $product_info['image'];
        } else {
            $this->data['image'] = '';
        }

        $this->load->model('tool/image');

        if (!empty($product_info) && $product_info['image']) {
            $this->data['thumb'] = $this->model_tool_image->resize($product_info['image'], 150, 150);
        } else {
            $this->data['thumb'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);
        }

        if (isset($this->request->post['shipping'])) {
            $this->data['shipping'] = $this->request->post['shipping'];
        } elseif (!empty($product_info)) {
            $this->data['shipping'] = $product_info['shipping'];
        } else {
            $this->data['shipping'] = 1;
        }

        $this->load->model('module/rental_products/settings');

        if ($this->model_module_rental_products_settings->isActive()) {
            $this->data['rental_products'] = true;
        }

        $this->load->model('module/related_products/settings');
        if ($this->model_module_related_products_settings->isActive()) {
            $this->data['related_products_app_status'] = true;
        }

        if (isset($this->request->post['transaction_type'])) {
            $this->data['transaction_type'] = $this->request->post['transaction_type'];
        } elseif (!empty($product_info)) {
            $this->data['transaction_type'] = $product_info['transaction_type'];
        } else {
            $this->data['transaction_type'] = '';
        }

        if (isset($this->request->post['price'])) {
            $this->data['price'] = $this->request->post['price'];
        } elseif (!empty($product_info)) {
            $this->data['price'] = $product_info['price'];
        } else {
            $this->data['price'] = '';
        }

        //Printing Document
        $this->load->model('module/printing_document');
        
        if ($this->model_module_printing_document->isActive()) {
            $this->data['printing_document'] = true;
        }
        if (isset($this->request->post['printable'])) {
            $this->data['printable'] = $this->request->post['printable'];
        } elseif (!empty($product_info)) {
            $this->data['printable'] = $product_info['printable'];
        } else {
            $this->data['printable'] = '0';
        }

        //Sales booster
        $this->load->model('module/sales_booster');
        
        if ($this->model_module_sales_booster->isActive()) {
            $this->data['sales_booster'] = true;
        }

        $this->data['sls_bstr'] = '';
        if (isset($this->request->post['sls_bstr'])) {
            $this->data['sls_bstr'] = $this->request->post['sls_bstr'];
        } elseif (!empty($product_info)) {
            if($product_info['sls_bstr']){
                $this->data['sls_bstr'] = json_decode($product_info['sls_bstr'], true);
            }
        }

        //prize draw app
        $this->load->model('module/prize_draw');
        if ($this->model_module_prize_draw->isActive()){
            if (isset($this->request->post['product_prize_draw'])) {
                $this->data['product_prize_draw'] = $this->request->post['product_prize_draw'];
            } elseif (!empty($product_info)) {
                $this->data['product_prize_draw'] = $this->model_module_prize_draw->getProductPrize($product_info['product_id']);
            }

            $this->data['prize_draw_prizes'] = $this->model_module_prize_draw->getPrizes();

            if(count($this->data['prize_draw_prizes']))
                $this->data['prize_draw_status'] = 1;
        }
        /////////////////

        //Price Per Meter
        $this->load->model('module/price_per_meter/settings');
        $arrayPriceMeter = json_decode($product_info['price_meter_data'], true);

        // curtain seller
        $this->load->model('module/curtain_seller');

        if ($this->model_module_curtain_seller->isEnabled()) {
            $this->data['curtain_seller_settings'] = $this->model_module_curtain_seller->getSettings();
            $this->data['curtain_seller'] = $arrayPriceMeter['curtain_seller'];
            $this->data['curtain_seller']['isEnabled'] = true;
        }

        if ($this->model_module_price_per_meter_settings->isActive()) {
            $this->data['price_per_meter'] = true;
        }

        //Gold App
        $this->load->model('module/gold');
        if ($this->model_module_gold->isActive()) {
            $this->data['calibers'] = $this->model_module_gold->getCalibers();
            /*print_r($this->data['calibers'] );
            exit();*/
            if(count($this->data['calibers'])){
                $this->data['gold_pricing'] = true;
                if(!empty($product_info))
                    $this->data['product_caliber'] = $this->model_module_gold->getProductCaliber($product_info['product_id']);
            }
        }
        $this->data['currency_decimal_places'] = $this->currency->getDecimalPlace() ?? 2;

        if (isset($this->request->post['main_unit'])) {
            $this->data['main_unit'] = $this->request->post['main_unit'];
        } elseif (!empty($product_info)) {
            $this->data['main_unit'] = $arrayPriceMeter['main_unit'];
        } else {
            $this->data['main_unit'] = '0';
        }

        /// Main Unit
        $all_unites = array('main', 'skirtings', 'metalprofile');

        for ($i = 0; $i < 3; $i++) {

            if (isset($this->request->post[$all_unites[$i] . '_status'])) {
                $this->data[$all_unites[$i] . '_status'] = $this->request->post[$all_unites[$i] . '_status'];
            } elseif (!empty($product_info)) {
                $this->data[$all_unites[$i] . '_status'] = $arrayPriceMeter[$all_unites[$i] . '_status'];
            } else {
                $this->data[$all_unites[$i] . '_status'] = '0';
            }

            if (isset($this->request->post[$all_unites[$i] . '_meter_price'])) {
                $this->data[$all_unites[$i] . '_meter_price'] = $this->request->post[$all_unites[$i] . '_meter_price'];
            } elseif (!empty($product_info)) {
                $this->data[$all_unites[$i] . '_meter_price'] = $arrayPriceMeter[$all_unites[$i] . '_meter_price'];
            } else {
                $this->data[$all_unites[$i] . '_meter_price'] = '';
            }

            if (isset($this->request->post[$all_unites[$i] . '_package_size'])) {
                $this->data[$all_unites[$i] . '_package_size'] = $this->request->post[$all_unites[$i] . '_package_size'];
            } elseif (!empty($product_info)) {
                $this->data[$all_unites[$i] . '_package_size'] = $arrayPriceMeter[$all_unites[$i] . '_package_size'];
            } else {
                $this->data[$all_unites[$i] . '_package_size'] = '';
            }

            if (isset($this->request->post[$all_unites[$i] . '_price_percentage'])) {
                $this->data[$all_unites[$i] . '_price_percentage'] = $this->request->post[$all_unites[$i] . '_price_percentage'];
            } elseif (!empty($product_info)) {
                $this->data[$all_unites[$i] . '_price_percentage'] = $arrayPriceMeter[$all_unites[$i] . '_price_percentage'];
            } else {
                $this->data[$all_unites[$i] . '_price_percentage'] = '';
            }
        }

        $this->data['defaultCurrency'] = $this->config->get('config_currency');

        $this->load->model('module/dedicated_domains/domains');

        $this->data['dedicatedDomains'] = [];

        if ($this->model_module_dedicated_domains_domains->isActive()) {

            $this->load->language('module/dedicated_domains');
            $this->document->addScript('view/javascript/modules/dedicated_domains/product.js');

            $this->data['dedicatedDomains']['status'] = true;

            $this->data['dedicatedDomains']['link'] = $this->url->link(
                'module/dedicated_domains/listDomainForProducts',
                '',
                'SSL'
            )->format();

            if (isset($this->request->get['product_id']) && (int) $this->request->get['product_id'] > 0) {
                $domains = $this->model_module_dedicated_domains_domains->getDetailedDomains(
                    $this->request->get['product_id']
                );
            } else {
                $domains = $this->model_module_dedicated_domains_domains->getDomains();
            }

            $this->data['dedicatedDomains']['domains'] = $domains;

            $this->data['dedicatedDomains']['translation'] = htmlspecialchars(json_encode([
                'name' => $this->language->get('dedicated_domain_name'),
                'price' => $this->language->get('dedicated_domain_price'),
            ]));

            $this->data['dedicatedDomains']['jsonDomains'] = htmlspecialchars(json_encode(
                $this->model_module_dedicated_domains_domains->getDomains()
            ));

            $domainPrices = null;
            $productDomains = null;
            $productDomainsIds = null;

            if (isset($this->request->get['product_id'])) {

                $this->load->model('module/dedicated_domains/domain_prices');

                $this->initializer([
                    'productDomain' => 'module/dedicated_domains/product_domain',
                    'domainPrices' => 'module/dedicated_domains/domain_prices',
                ]);

                $domainPrices = $this->domainPrices->getDomainPricesByProductId(
                    $this->request->get['product_id']
                );

                $productDomains = $this->productDomain->getProductDomains($this->request->get['product_id']);

                $productDomainsIds = array_flip($this->productDomain->getProductDomainsIds($productDomains));
            }
            $this->data['dedicatedDomains']['domainPrices'] = $domainPrices;
            $this->data['dedicatedDomains']['productDomains'] = $productDomains;
            $this->data['dedicatedDomains']['productDomainsIds'] = $productDomainsIds;

        }

        if (isset($this->request->post['cost_price'])) {
            $this->data['cost_price'] = $this->request->post['cost_price'];
        } elseif (!empty($product_info)) {
            $this->data['cost_price'] = $product_info['cost_price'];
        } else {
            $this->data['cost_price'] = '';
        }

        if (isset($this->request->post['discount_price']) ){
            $this->data['discount_price'] = $this->request->post['discount_price'];
        }else if(isset($this->request->get['product_id'])){
            $default_discount=  $this->model_catalog_product->getProductSpecialDefault($this->request->get['product_id']);
            $this->data['discount_price'] = $default_discount['price'];
        }else{
            $this->data['discount_price'] = '';
        }

        //  get minimum deposit app and check installed or not
        $this->load->model('module/minimum_deposit/settings');

        if ($this->model_module_minimum_deposit_settings->isActive()){
            $this->data['minimum_deposit_check']  = $this->model_module_minimum_deposit_settings->getSettings();
            if ($this->data['minimum_deposit_check']['md_status_deposit'] ['md_status_deposit'] == '1'){
                if (isset($this->request->post['minimum_deposit_price'])) {
                    $this->data['minimum_deposit_price'] = $this->request->post['minimum_deposit_price'];
                } elseif (!empty($product_info)) {
                    $this->data['minimum_deposit_price'] = $product_info['minimum_deposit_price'];
                } else {
                    $this->data['minimum_deposit_price'] = '';
                }
            }
        }

        $this->load->model('localisation/tax_class');

        $this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

        if (isset($this->request->post['tax_class_id'])) {
            $this->data['tax_class_id'] = $this->request->post['tax_class_id'];
        } elseif (!empty($product_info)) {
            $this->data['tax_class_id'] = $product_info['tax_class_id'];
        } else {
            $this->data['tax_class_id'] = 0;
        }

        if (isset($this->request->post['date_available'])) {
            $this->data['date_available'] = $this->request->post['date_available'];
        } elseif (!empty($product_info)) {
            $this->data['date_available'] = date('Y-m-d', strtotime($product_info['date_available']));
        } else {
            $this->data['date_available'] = date('Y-m-d', time() - 86400);
        }

        if (isset($this->request->post['quantity'])) {
            $this->data['quantity'] = $this->request->post['quantity'];
        } elseif (!empty($product_info)) {
            $this->data['quantity'] = $product_info['quantity'];
        } else {
            $this->data['quantity'] = 1;
        }

        if (isset($this->request->post['unlimited'])) {
            $this->data['unlimited'] = $this->request->post['unlimited'];
        } elseif (!empty($product_info)) {
            $this->data['unlimited'] = $product_info['unlimited'];
        } else {
            $this->data['unlimited'] = 1;
        }


        if (isset($this->request->post['minimum'])) {
            $this->data['minimum'] = $this->request->post['minimum'];
        } elseif (!empty($product_info)) {
            $this->data['minimum'] = $product_info['minimum'];
        } else {
            $this->data['minimum'] = 1;
        }

        $this->load->model('module/product_preparation_period');

        $this->data['preparation_period_app_status'] = $this->model_module_product_preparation_period->isActive();

        if ($this->data['preparation_period_app_status']){
            if (isset($this->request->post['preparation_days'])) {
                $this->data['preparation_days'] = $this->request->post['preparation_days'];
            } elseif (!empty($product_info)) {
                $this->data['preparation_days'] = $product_info['preparation_days'];
            } else{
                $this->data['preparation_days'] = (int) $this->config->get('product_preparation_period')['days'] ;
            }
        }

        $this->load->model('module/products_notes');

        $this->data['products_notes_app_status'] = $this->model_module_products_notes->isActive();

        if ($this->data['products_notes_app_status']){

            if($this->config->get('products_notes')['general_use']){
                if (isset($this->request->post['general_use'])) {
                    $this->data['general_use'] = $this->request->post['general_use'];
                } elseif (!empty($product_info)) {
                    $this->data['general_use'] = $product_info['general_use'];
                } else {
                    $this->data['general_use'] = '';
                }
            }

            if($this->config->get('products_notes')['internal_notes']) {
                if (isset($this->request->post['notes'])) {
                    $this->data['notes'] = $this->request->post['notes'];
                } elseif (!empty($product_info)) {
                    $this->data['notes'] = $product_info['notes'];
                } else {
                    $this->data['notes'] = '';
                }
            }
        }

        if (isset($this->request->post['maximum'])) {
            $this->data['maximum'] = $this->request->post['maximum'];
        } elseif (!empty($product_info)) {
            $this->data['maximum'] = $product_info['maximum'];
        } else {
            $this->data['maximum'] = 0;
        }


        if (isset($this->request->post['subtract'])) {
            $this->data['subtract'] = $this->request->post['subtract'];
        } elseif (!empty($product_info)) {
            $this->data['subtract'] = $product_info['subtract'];
        } else {
            $config_product_default_subtract_stock = $this->config->get('product_default_subtract_stock');
            $this->data['subtract'] = ($config_product_default_subtract_stock == 1 ) ? 1 : 0;
        }

        // make sure that subtract is always disabled if rental app is installed
        if($this->data['rental_products'])  
            $this->data['subtract'] = 0 ;

        if (isset($this->request->post['sort_order'])) {
            $this->data['sort_order'] = $this->request->post['sort_order'];
        } elseif (!empty($product_info)) {
            $this->data['sort_order'] = $product_info['sort_order'];
        } else {
            $this->data['sort_order'] = 1;
        }

        $this->load->model('localisation/stock_status');

        $this->data['stock_statuses'] = $this->model_localisation_stock_status->getStockStatuses();

        if (isset($this->request->post['stock_status_id'])) {
            $this->data['stock_status_id'] = $this->request->post['stock_status_id'];
        } elseif (!empty($product_info)) {
            $this->data['stock_status_id'] = $product_info['stock_status_id'];
        } else {
            $this->data['stock_status_id'] = $this->config->get('config_stock_status_id');
        }

        if (isset($this->request->post['status'])) {
            $this->data['status'] = $this->request->post['status'];
        } elseif (!empty($product_info)) {
            $this->data['status'] = $product_info['status'];
        } else {
            $this->data['status'] = 1;
        }

        if (isset($this->request->post['weight'])) {
            $this->data['weight'] = $this->request->post['weight'];
        } elseif (!empty($product_info)) {
            $this->data['weight'] = $product_info['weight'];
        } else {
            $this->data['weight'] = '';
        }

        $this->load->model('localisation/weight_class');

        $this->data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses();

        if (isset($this->request->post['weight_class_id'])) {
            $this->data['weight_class_id'] = $this->request->post['weight_class_id'];
        } elseif (!empty($product_info)) {
            $this->data['weight_class_id'] = $product_info['weight_class_id'];
        } else {
            $this->data['weight_class_id'] = $this->config->get('config_weight_class_id');
        }

        if (isset($this->request->post['length'])) {
            $this->data['length'] = $this->request->post['length'];
        } elseif (!empty($product_info) && $product_info['length'] > 0) {
            $this->data['length'] = $product_info['length'];
        } else {
            $this->data['length'] = '';
        }

        if (isset($this->request->post['width'])) {
            $this->data['width'] = $this->request->post['width'];
        } elseif (!empty($product_info) && $product_info['width'] > 0) {
            $this->data['width'] = $product_info['width'];
        } else {
            $this->data['width'] = '';
        }

        if (isset($this->request->post['height'])) {
            $this->data['height'] = $this->request->post['height'];
        } elseif (!empty($product_info) && $product_info['height'] > 0) {
            $this->data['height'] = $product_info['height'];
        } else {
            $this->data['height'] = '';
        }

        $this->load->model('localisation/length_class');

        $this->data['length_classes'] = $this->model_localisation_length_class->getLengthClasses();

        if (isset($this->request->post['length_class_id'])) {
            $this->data['length_class_id'] = $this->request->post['length_class_id'];
        } elseif (!empty($product_info)) {
            $this->data['length_class_id'] = $product_info['length_class_id'];
        } else {
            $this->data['length_class_id'] = $this->config->get('config_length_class_id');
        }

        $this->load->model('catalog/manufacturer');

        if (isset($this->request->post['manufacturer_id'])) {
            $this->data['manufacturer_id'] = $this->request->post['manufacturer_id'];
        } elseif (!empty($product_info)) {
            $this->data['manufacturer_id'] = $product_info['manufacturer_id'];
        } else {
            $this->data['manufacturer_id'] = 0;
        }

        if (isset($this->request->post['manufacturer'])) {
            $this->data['manufacturer'] = $this->request->post['manufacturer'];
        } elseif (!empty($product_info)) {
            $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($product_info['manufacturer_id']);

            if ($manufacturer_info) {
                $this->data['manufacturer'] = $manufacturer_info['name'];
            } else {
                $this->data['manufacturer'] = '';
            }
        } else {
            $this->data['manufacturer'] = '';
        }

        // Categories
        $this->load->model('catalog/category');

        if (isset($this->request->post['product_category'])) {
            $categories = $this->request->post['product_category'];
        } elseif (isset($this->request->get['product_id'])) {
            $categories = $this->model_catalog_product->getProductCategories($this->request->get['product_id']);
        } else {
            $categories = array();
        }

        $this->data['product_categories'] = array();

        foreach ($categories as $category_id) {
            $categoryInfo = $this->model_catalog_category->getCategory($category_id);

            if ($categoryInfo) {
                $this->data['product_categories'][] = array(
                    'category_id' => $categoryInfo['category_id'],
                    'name' => ($categoryInfo['path'] ? $categoryInfo['path'] . ' &gt; ' : '') . $categoryInfo['name']
                );
            }
        }

        // Filters
        $this->load->model('catalog/filter');

        if (isset($this->request->post['product_filter'])) {
            $filters = $this->request->post['product_filter'];
        } elseif (isset($this->request->get['product_id'])) {
            $filters = $this->model_catalog_product->getProductFilters($this->request->get['product_id']);
        } else {
            $filters = array();
        }

        $this->data['product_filters'] = array();

        foreach ($filters as $filter_id) {
            $filter_info = $this->model_catalog_filter->getFilter($filter_id);

            if ($filter_info) {
                $this->data['product_filters'][] = array(
                    'filter_id' => $filter_info['filter_id'],
                    'name' => $filter_info['group'] . ' &gt; ' . $filter_info['name']
                );
            }
        }

        // Attributes
        $this->load->model('catalog/attribute');

        if (isset($this->request->post['product_attribute'])) {
            $product_attributes = $this->request->post['product_attribute'];
        } elseif (isset($this->request->get['product_id'])) {
            $product_attributes = $this->model_catalog_product->getProductAttributes(
                $this->request->get['product_id']
            );
        } else {
            $product_attributes = array();
        }

        $this->data['product_attributes'] = $product_attributes_ids = array();

        foreach ($product_attributes as $product_attribute) {
            $attribute_info = $this->model_catalog_attribute->getAttribute($product_attribute['attribute_id']);

            if ($attribute_info) {
                $this->data['product_attributes'][] = array(
                    'attribute_id' => $product_attribute['attribute_id'],
                    'name' => $attribute_info['name'],
                    'GroupName' => $attribute_info['GroupName'],
                    'product_attribute_description' => $product_attribute['product_attribute_description']
                );

                $product_attributes_ids[$product_attribute['attribute_id']] = $product_attribute['attribute_id'];
            }
        }

        $this->data['product_attributes_ids'] = $product_attributes_ids;
        $this->data['attributes'] = $this->model_catalog_attribute->getGroupedAttributes();

        // Options
        $this->load->model('catalog/option');

        $this->data['options_limit_reached'] = (($this->model_catalog_option->getTotalOptions()+ 1) > OPTIONSLIMIT )
            && $this->plan_id == '3'
        ;

        $this->getProductOptions();

        $this->language->load('catalog/option');

        $this->data['optionTypes'] = [
            'text_choose' => [
                'select',
                'radio',
                'checkbox',
                'image',
            ],
            'text_input' => [
                'text',
                'textarea',
            ],
            'text_file' => [
                'file'
            ],
            'text_date' => [
                'date',
                'time',
                'datetime',
            ]
        ];
        if (\Extension::isInstalled('product_builder') && $this->config->get('product_builder')['status']) {
            $this->data['optionTypes']['text_choose'][] = 'product';
        }

        $allOption = $this->model_catalog_option->getOptions([]);

        $productOptionsByOptionId = array_column($this->data['product_options'], null, 'option_id');

        $this->data['all_options'] = [];
        foreach ($allOption as $catalogOption) {
            if (
                $catalogOption['type'] == 'select' ||
                $catalogOption['type'] == 'radio' ||
                $catalogOption['type'] == 'checkbox' ||
                $catalogOption['type'] == 'image' ||
                $catalogOption['type'] == 'product'
            ) {
                if (!isset($this->data['option_values'][$catalogOption['option_id']])) {
                    $this->data['all_options'][$catalogOption['option_id']] = $catalogOption;

                    $this->data['all_options'][$catalogOption['option_id']]['values'] =
                        $this->model_catalog_option->getOptionValues($catalogOption['option_id']);

                        if (isset($productOptionsByOptionId[$catalogOption['option_id']])) {
                        $this->data['all_options'][$catalogOption['option_id']]['product_values'] = array_column(
                            $productOptionsByOptionId[$catalogOption['option_id']]['product_option_value'],
                            null,
                            'option_value_id'
                        );
                    } else {
                        $this->data['all_options'][$catalogOption['option_id']]['product_values'] = [];
                    }
                }
            }
        }

        //Check Stock Forecasting Quantity App
        if( \Extension::isinstalled('stock_forecasting') && $this->config->get('stock_forecasting_status') == 1 ){
            $this->data['stock_forecasting_installed'] = TRUE;

            $this->load->model('module/stock_forecasting');
            $this->data['product_stock_forecasting'] = $this->model_module_stock_forecasting->getProductStockForecasting($this->request->get['product_id']);
            $this->data['disabled_days'] = array_column($this->data['product_stock_forecasting'], 'day');
            // echo '<pre>'; print_r($this->data['disabled_days']); die();
            $this->load->language('module/stock_forecasting');            
        }


        //Check Advanced_Product_attributes App status
        $this->load->model('module/advanced_product_attributes/settings');
        $this->load->model('module/advanced_product_attributes/attribute');
        $advanced_product_attributes_app_installed = $this->model_module_advanced_product_attributes_settings->isInstalled();
        $this->data['advanced_product_attributes_status'] = $advanced_product_attributes_app_installed;

        //Check reward points pro app status
        $this->load->model('module/reward_points_pro');
        $reward_points_app_status = $this->model_module_reward_points_pro->isInstalled();
        $this->data['reward_points_app_status'] = $reward_points_app_status;

        if($advanced_product_attributes_app_installed){
            $this->data['advanced_attributes_by_groups']   = $this->model_module_advanced_product_attributes_attribute->getGroupedAttributes();
            $this->data['product_advanced_attributes_ids'] = $this->model_module_advanced_product_attributes_attribute->getCurrentProductAdvancedAttributesIds($this->request->get['product_id']);
            $this->data['product_advanced_attributes']     = $this->model_module_advanced_product_attributes_attribute->getProductAttributes($this->request->get['product_id']);
        }

        $this->data['option_values'] = array();

        $productsoptions_sku_status = $this->config->get('productsoptions_sku_status');
        $this->data['productsoptions_sku_status'] = $productsoptions_sku_status;
        $this->data['knawat_status'] = $this->model_catalog_product->isKnawatProduct($this->request->get['product_id']);
        $this->data['product_affiliate_link_status'] = $this->config->get('product_affiliate_link_status');
        $product_options = $this->model_catalog_product->getProductOptions($this->request->get['product_id']);
        if ($productsoptions_sku_status) {
            if (!empty($product_options) && count($product_options) > 0) {

                if (isset($this->request->get['product_id'])) {
                    $custom_product_options = $this->model_catalog_product->getProductOptionsCustom(
                        $this->request->get['product_id']
                    );
                } else {
                    $custom_product_options = array();
                }

                foreach ($custom_product_options as $option) {
                    if (
                        $option['type'] == 'select' ||
                        $option['type'] == 'image' ||
                        $option['type'] == 'radio' ||
                        $option['type'] == 'checkbox' ||
                        $option['type'] == 'image' ||
                        $option['type'] == 'product'
                    ) {
                        $option_value_data = array();

                        foreach ($option['option_value'] as $option_value) {
                            if (
                                (
                                    ($this->config->get('config_customer_price')) ||
                                    !$this->config->get('config_customer_price')
                                ) &&
                                (float)$option_value['price']
                            ) {
                                $price = $option_value['price'];
                            } else {
                                $price = false;
                            }

                            $option_value_data[] = array(
                                'product_option_value_id' => $option_value['product_option_value_id'],
                                'option_value_id' => $option_value['option_value_id'],
                                'name' => $option_value['name'],
                                'price' => $price,
                                'price_prefix' => $option_value['price_prefix']
                            );
                        }

                        $data['options'][] = array(
                            'product_option_id' => $option['product_option_id'],
                            'option_id' => $option['option_id'],
                            'name' => $option['name'],
                            'type' => $option['type'],
                            'option_value' => $option_value_data,
                            'required' => $option['required']
                        );
                    } elseif (
                        $option['type'] == 'text' ||
                        $option['type'] == 'textarea' ||
                        $option['type'] == 'file' ||
                        $option['type'] == 'date' ||
                        $option['type'] == 'datetime' ||
                        $option['type'] == 'time'
                    ) {
                        $data['options'][] = array(
                            'product_option_id' => $option['product_option_id'],
                            'option_id' => $option['option_id'],
                            'name' => $option['name'],
                            'type' => $option['type'],
                            'option_value' => $option['option_value'],
                            'required' => $option['required']
                        );
                    }
                }
                foreach ($data['options'] as $key => $option) {

                    if (
                        $option['type'] == 'select' ||
                        $option['type'] == 'image' ||
                        $option['type'] == 'radio' ||
                        $option['type'] == 'checkbox'
                    ) {
                        $temp_option[$key]['name'] = $option['name'];
                        $temp_option[$key]['option_id'] = $option['option_id'];
                        $temp_option[$key]['option'] = true;

                        foreach ($option['option_value'] as $gh) {

                            $temp_option[$key]['value'][] = [
                                'value' => $gh['name'],
                                'option_value_id' => $gh['option_value_id']
                            ];
                        }
                    }
                }
                $this->data['option_combinations'] = $this->combos($temp_option);
            }
        }

        foreach ($this->data['product_options'] as $product_option) {
            if (
                $product_option['type'] == 'select' ||
                $product_option['type'] == 'radio' ||
                $product_option['type'] == 'checkbox' ||
                $product_option['type'] == 'image' ||
                $product_option['type'] == 'product'
            ) {
                if (!isset($this->data['option_values'][$product_option['option_id']])) {
                    $this->data['option_values'][$product_option['option_id']] = array_column(
                        $this->model_catalog_option->getOptionValues($product_option['option_id']),
                        null,
                        'option_value_id'
                    );
                }
            }
        }


        $this->data['lang'] = [
            'option_entry_name' => $this->language->get('entry_name')
        ];

        $this->load->model('sale/customer_group');

        $this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();

        $reconstructed_combinations = true;
        if ($productsoptions_sku_status) {
            if (!empty($product_options) && count($product_options) > 0) {
                $product_variation_skus = $this->model_catalog_product->getProductVariationSku(
                    $this->request->get['product_id']
                );
                if (!empty($product_variation_skus)) {
                    foreach ($product_variation_skus as $value) {
                        $product_sku_required_values[$value['option_value_ids']] = [
                            'sku' => $value['product_sku'],
                            'quantity' => $value['product_quantity'],
                        ];
                    }
                    $this->data['product_variation_skus'] = $product_sku_required_values;
                    if ($this->model_catalog_product->isKnawatProduct($this->request->get['product_id'])) {
                        $reconstructed_combinations = false;
                    }
                }
            }


            $optionsVariationValues = [];
            $optionsHeaders = [];
            if (!$reconstructed_combinations) {
                foreach ($product_variation_skus as $key => $combination) {
                    $_combination = [];
                    $opv_ids = explode(',', $combination['option_value_ids']);

                    foreach ($opv_ids as $opv_id) {
                        $option_value = $this->model_catalog_product->getOptionValue($opv_id);
                        $optionsHeaders[$option_value['o_name']] = $option_value['o_name'];
                        $_combination[] = [
                            'name' => '',
                            'option' => '',
                            'parent_id' => $option_value['option_id'],
                            'value' => $option_value['name'],
                            'option_value_id' => $option_value['option_value_id'],
                        ];
                    }

                    $_combination['sku'] = [
                        'name' => 'sku',
                        'value' => $combination['product_sku'],
                        'option_value_id' => $combination['option_value_ids'],
                        'id' => $combination['id'],
                        'input' => true,
                    ];
                    $_combination['quantity'] = [
                        'name' => 'quantity',
                        'value' => $combination['product_quantity'],
                        'option_value_id' => $combination['option_value_ids'],
                        'id' => $combination['id'],
                        'input' => true,
                    ];
                    $_combination['price'] = [
                        'name' => 'price',
                        'value' => $combination['product_price'],
                        'option_value_id' => $combination['option_value_ids'],
                        'id' => $combination['id'],
                        'input' => true,
                    ];
                    $_combination['barcode'] = [
                        'name' => 'barcode',
                        'value' => $combination['product_barcode'],
                        'option_value_id' => $combination['option_value_ids'],
                        'id' => $combination['id'],
                        'input' => true,
                    ];
                    $optionsVariationValues[] = $_combination;
                }

            } else {
                
                foreach ($this->data['option_combinations'] as $key => $combination) {
                    $combination['sku'] = ['name' => 'sku', 'input' => true,];
                    $combination['quantity'] = ['name' => 'quantity', 'input' => true,];
                    $combination['price'] = ['name' => 'price', 'input' => true,];
                    $combination['barcode'] = ['name' => 'barcode', 'input' => true,];
                    $combinationValuesId = array_column($combination, 'option_value_id');
                    foreach ($product_variation_skus as $variationKey => $variation) {
                        $variations = explode(',', $variation['option_value_ids']);
    
                        if (!array_diff($variations, $combinationValuesId)) {
                            $combination['sku'] = [
                                'name' => 'sku',
                                'value' => $variation['product_sku'],
                                'option_value_id' => $variation['option_value_ids'],
                                'id' => $variation['id'],
                                'input' => true,
                            ];
                            $combination['quantity'] = [
                                'name' => 'quantity',
                                'value' => $variation['product_quantity'],
                                'option_value_id' => $variation['option_value_ids'],
                                'id' => $variation['id'],
                                'input' => true,
                            ];
                            $combination['price'] = [
                                'name' => 'price',
                                'value' => $variation['product_price'],
                                'option_value_id' => $variation['option_value_ids'],
                                'id' => $variation['id'],
                                'input' => true,
                            ];
                            $combination['barcode'] = [
                                'name' => 'barcode',
                                'value' => $variation['product_barcode'],
                                'option_value_id' => $variation['option_value_ids'],
                                'id' => $variation['id'],
                                'input' => true,
                            ];
    
                            if (isset($this->request->post['product_options_variations'])) {
    
                                $postedVariationsParentIds = array_column(
                                    $this->request->post['product_options_variations'], 'quantity', 'parent_id'
                                );
    
                                if (isset($postedVariationsParentIds[$variation['id']])) {
                                    $combination['quantity']['value'] = $postedVariationsParentIds[$variation['id']];
                                }
                            }
                        }
                    }
                    $optionsVariationValues[] = $combination;
                }
            }
          
            if ($reconstructed_combinations) {
                $optionsHeaders = array_column($this->data['product_options'], 'name');
            }
            $this->data['optionsHeaders'] = $optionsHeaders;
            $this->data['optionsVariationValues'] = $optionsVariationValues;

        }

        if (isset($this->request->post['product_discount'])) {
            $this->data['product_discounts'] = $this->request->post['product_discount'];
        } elseif (isset($this->request->get['product_id'])) {
            $this->data['product_discounts'] = $this->model_catalog_product->getProductDiscounts(
                $this->request->get['product_id']
            );
        } else {
            $this->data['product_discounts'] = array();
        }

        if (isset($this->request->post['product_special'])) {
            $this->data['product_specials'] = $this->request->post['product_special'];
        } elseif (isset($this->request->get['product_id'])) {
            $this->data['product_specials'] = $this->model_catalog_product->getProductSpecials(
                $this->request->get['product_id']
            );
        } else {
            $this->data['product_specials'] = array();
        }

        if ($this->model_module_dedicated_domains_domains->isActive()) {
            $this->data['product_discounts'] = array_map(function ($product) {
                if ($product['dedicated_domains']) {

                    $domain = $this->model_module_dedicated_domains_domains->getDomainById(
                        $product['dedicated_domains']
                    );

                    $product['domain_name'] = $domain['domain'] . ' - ' . $domain['currency'];
                } else {
                    $product['domain_name'] = $this->language->get('all_domains') . ' - ' .
                        $this->config->get('config_currency');
                }

                return $product;
            }, $this->data['product_discounts']);

            $this->data['product_specials'] = array_map(function ($product) {
                if ($product['dedicated_domains']) {
                    $domain = $this->model_module_dedicated_domains_domains->getDomainById(
                        $product['dedicated_domains']
                    );

                    $product['domain_name'] = $domain['domain'] . ' - ' . $domain['currency'];
                } else {
                    $product['domain_name'] = $this->language->get('all_domains') . ' - ' .
                        $this->config->get('config_currency');
                }

                return $product;
            }, $this->data['product_specials']);
        }

        $this->data['product_specials_discounts'] = array_merge(
            $this->data['product_discounts'],
            $this->data['product_specials']
        );

        $this->data['discounts_limit_reached'] =
            (count($this->data['product_specials_discounts']) + 1) > DISCOUNTSLIMIT && $this->plan_id == '3'
        ;

        // start get product classification data if app active
        $this->load->model('module/product_classification/settings');

        if ($this->model_module_product_classification_settings->isActive()) {
            $pc_array_data = array();
            if(isset($this->request->get['product_id']))
            {
                $productClassificationData = $this->model_catalog_product->getProductClassificationData($this->request->get['product_id']);

                $this->load->model('module/product_classification/brand');
                $this->load->model('module/product_classification/model');
                if(count($productClassificationData) > 0){
                    $newPcData = [];
                    foreach ($productClassificationData as $key=>$pc_data){
                       $modelsData =  $this->model_catalog_product->getProductClassificationModelsByRowData($pc_data['pc_brand_id'],$pc_data['pc_row_key'],$this->request->get['product_id']);
                       $yearsData =  $this->model_catalog_product->getProductClassificationyearsByRowData($pc_data['pc_brand_id'],$pc_data['pc_row_key'],$this->request->get['product_id']);
                       $models_ids_array = [];
                       $years_ids_array = [];
                       foreach ($modelsData as $modelData){
                           $models_ids_array[] = $modelData['pc_model_id'];
                       }
                        foreach ($yearsData as $yearData){
                            $years_ids_array[] = $yearData['pc_year_id'];
                        }

                       $newPcData[$pc_data['pc_row_key']] = [
                                    "row_key" => $pc_data['pc_row_key'],
                                    "brand_id" => $pc_data['pc_brand_id'],
                                    "model_id" =>$models_ids_array,
                                    "year_id" =>$years_ids_array,
                        ];

                        $newPcData[$pc_data['pc_row_key']]['brand_models'] = $this->model_module_product_classification_brand->getModelsByBrandId($pc_data['pc_brand_id']);
                        $newPcData[$pc_data['pc_row_key']]['model_years'] = $this->model_module_product_classification_model->getYearsByModelId($models_ids_array);

                    }
                }
            }

            $this->data['productClassificationData'] = $newPcData;
        }

        // Images
        if (isset($this->request->post['product_image'])) {
            $product_images = $this->request->post['product_image'];
        } elseif (isset($this->request->get['product_id'])) {
            $product_images = $this->model_catalog_product->getProductImages($this->request->get['product_id']);
        } else {
            $product_images = array();
        }

        $this->data['product_images'] = array();

        if (isset($this->request->get['product_id']) && $this->data['image'] != null && $this->data['image'] != "image/no_image.jpg") {
            $this->data['product_images'][] = [
                'image' => $this->data['image'],
                'thumb' => $this->model_tool_image->resize($this->data['image'], 150, 150),
                'sort_order' => 0,
                'size' => \Filesystem::getSize('image/' . $this->data['image']),
                'name' => basename($this->data['image']),
                'href' => \Filesystem::getUrl('image/' . $this->data['image']),
            ];
        }

        foreach ($product_images as $product_image) {
            if ($product_image['image']) {
                $image = $product_image['image'];
                $thumb = $this->model_tool_image->resize($image, 150, 150);
            } else {
                $image = 'no_image.jpg';
                $thumb = $this->model_tool_image->resize($image, 150, 150);
            }

            $this->data['product_images'][] = array(
                'image' => $image,
                'thumb' => $thumb,
                'sort_order' => $product_image['sort_order'],
                'size' => \Filesystem::getSize('image/' . $image),
                'name' => basename($image),
                'href' => \Filesystem::getUrl('image/' . $image),
                'class'=>'preview_product_image',
                'imageType'=>'product_image'
            );
        }
        /**
         * Check if the Rotate360 is installed then read the images
         * form "rotate360_images" table to preview it in product page
         */
        $this->load->model('module/rotate360');
        if($this->model_module_rotate360->installed()) {
            //_________Rotate360 ARRAY_____________
            $rotate360_images = array();
            if (isset($this->request->get['product_id'])) {
                $rotate360_images = $this->model_module_rotate360->getImagesByProductId($this->request->get['product_id']);

                foreach ($rotate360_images as $rotate360_image) {
                    if ($rotate360_image['image_path']) {
                        $image = $rotate360_image['image_path'];
                    } else {
                        $image = 'no_image.jpg';
                    }

                    $this->data['rotate360_images'][] = array(
                        'image' => $image,
                        'thumb' => $this->model_tool_image->resize($image, 150, 150),
                        'sort_order' => $rotate360_image['image_order'],
                        'size' => \Filesystem::getSize('image/' . $image),
                        'name' => basename($image),
                        'href' => \Filesystem::getUrl('image/' . $image),
                        'class' => 'preview_rotate360_image',
                        'imageType'=>'rotate360_image'
                    );
                }
            }
        }
        if (isset($this->data['poip_installed']) && $this->data['poip_installed'] == true) {
            foreach ($this->data['product_options'] as $op) {

                foreach ($op['product_option_value'] as $opv) {

                    foreach ($opv['images'] as $imgK => $img) {
                        $count = 0;
						$image = $img['image'];
						$found = false;
						foreach($this->data['product_images'] as $product_img ){
						if ($product_img['image']==$image && $image != 'no_image.jpg'){

                        $this->data['product_images'][$count] = array(
                            'image' => $image,
                            'thumb' => $this->model_tool_image->resize($image, 150, 150),
                            'sort_order' => $img['srt'],
                            'size' => \Filesystem::getSize('image/' . $image),
                            'name' => basename($image),
                            'href' => \Filesystem::getUrl('image/' . $image),
                            'isOption' => true,
                            'option' => [
                                'id' => $op['option_id'],
                                'value_id' => $opv['option_value_id']
                            ]
                        );
						$found = true;
						break;
						}
						$count++ ;
						}
						
						/*
						to handle the old way issue of saving pro-images, 
						for pro images that already saved on pro images table
						& not saved at product images table.
						*/
						if(!$found){
							$this->data['product_images'][] = array(
								'image' => $image,
								'thumb' => $this->model_tool_image->resize($image, 150, 150),
								'sort_order' => $img['srt'],
								'size' => \Filesystem::getSize('image/' . $image),
								'name' => basename($image),
								'href' => \Filesystem::getUrl('image/' . $image),
								'isOption' => true,
								'option' => [
									'id' => $op['option_id'],
									'value_id' => $opv['option_value_id']
								]
							);	
						}
                    }
                }
            }
        }

        $this->data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);

        // Downloads

        $this->load->model('module/product_attachments');

        $this->data['product_attachments_app_status'] = $this->model_module_product_attachments->isActive();

        if ($this->data['product_attachments_app_status']){

            $this->load->model('catalog/download');

            if (isset($this->request->post['product_download'])) {
                $product_downloads = $this->request->post['product_download'];
            } elseif (isset($this->request->get['product_id'])) {
                $product_downloads = $this->model_catalog_product->getProductDownloads($this->request->get['product_id']);
            } else {
                $product_downloads = array();
            }

            $this->data['product_downloads'] = array();

            foreach ($product_downloads as $download_id) {
                $download_info = $this->model_catalog_download->getDownload($download_id);

                if ($download_info) {
                    $this->data['product_downloads'][] = array(
                        'download_id' => $download_info['download_id'],
                        'name' => $download_info['name']
                    );
                }
            }
        }

        if (isset($this->request->post['product_related'])) {
            $products = $this->request->post['product_related'];
        } elseif (isset($this->request->get['product_id'])) {
            $products = $this->model_catalog_product->getProductRelated($this->request->get['product_id']);
        } else {
            $products = array();
        }

        $this->data['product_related'] = array();

        foreach ($products as $product_id) {
            $related_info = $this->model_catalog_product->getProduct($product_id);

            if ($related_info) {
                $this->data['product_related'][] = array(
                    'product_id' => $related_info['product_id'],
                    'name' => $related_info['name']
                );
            }
        }

        // prodcut_bundles app
        $this->load->model('module/product_bundles/settings');
        $this->data['product_bundles_app_status'] = $this->model_module_product_bundles_settings->isActive();
        if($this->data['product_bundles_app_status']){
            if (isset($this->request->post['product_bundles'])) {
                // inserting
                $product_bundles = $this->request->post['product_bundles'];
            } elseif (isset($this->request->get['product_id'])) {
                // updating
                $product_bundles = $this->model_catalog_product->getProductBundles($this->request->get['product_id']);
            } else {
                $product_bundles = array();
            }
            $this->data['product_bundles'] = array();
            foreach ($product_bundles as $product_bundle) {
                $bundle_info = $this->model_catalog_product->getProduct($product_bundle['bundle_product_id']);

                if ($bundle_info) {
                    $this->data['product_bundles'][] = array(
                        'product_id' => $bundle_info['product_id'],
                        'name' => $bundle_info['name']
                    );
                }
            }
            // at this stage we save the same fixed discount for all the bundle products, but later we may make a specific discount for each bundle product
            $this->data['product_bundles_discount'] = ($product_bundles[0]['discount'] * 100) ?? NULL;
        }
        // end of prodcut_bundles app

        if (isset($this->request->post['points'])) {
            $this->data['points'] = $this->request->post['points'];
        } elseif (!empty($product_info)) {
            $this->data['points'] = $product_info['points'];
        } else {
            $this->data['points'] = '';
        }

        // Product Deisgner Module
        $pdModule = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'product_designer'");
        $pdModuleSettings = $this->config->get('tshirt_module');
        if ($pdModule->num_rows != 0) {
            $this->data['pdModuleStatus'] = $pdModuleSettings['pd_status'];
            $this->language->load('module/product_designer');

            if ($product_info['pd_custom_price'] == '') {
                $product_info['pd_custom_price'] = '{"front": "0.00", "back": "0.00"}';
            }

            $this->data['pd_custom_price'] = json_decode($product_info['pd_custom_price'], true);
            $this->data['pd_is_customize'] = $product_info['pd_is_customize'];
            $this->data['pd_custom_min_qty'] = $product_info['pd_custom_min_qty'];
            $this->data['pd_back_image'] = $product_info['pd_back_image'];

            if (!empty($product_info) && $product_info['pd_back_image']) {
                $this->data['pd_back_thumb'] = $this->model_tool_image->resize(
                    $product_info['pd_back_image'],
                    100,
                    100
                );
            } else {
                $this->data['pd_back_thumb'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);
            }

        }

        // Product Classification Module

        if ($this->model_module_product_classification_settings->isActive()) {
            $pcModuleSettings = $this->config->get('product_classification');
            $this->language->load('module/product_classification');

            $this->data['pcModuleStatus'] = $pcModuleSettings['status'];

            $this->load->model('module/product_classification/brand');
            $this->data['pc_brands'] = array();
            // get all brands
            $pc_brands = $this->model_module_product_classification_brand->getBrands();

            foreach ($pc_brands as $pc_brand) {

                $this->data['pc_brands'][] = array(
                    'brand_id'    => $pc_brand['pc_brand_id'],
                    'name'            => $pc_brand['name'],
                );
            }
        }

        if (isset($this->request->post['product_reward'])) {
            $this->data['product_reward'] = $this->request->post['product_reward'];
        } elseif (isset($this->request->get['product_id'])) {
            $this->data['product_reward'] = $this->model_catalog_product->getProductRewards(
                $this->request->get['product_id']
            );
        } else {
            $this->data['product_reward'] = array();
        }

        if (isset($this->request->post['product_layout'])) {
            $this->data['product_layout'] = $this->request->post['product_layout'];
        } elseif (isset($this->request->get['product_id'])) {
            $this->data['product_layout'] = $this->model_catalog_product->getProductLayouts(
                $this->request->get['product_id']
            );
        } else {
            $this->data['product_layout'] = array();
        }

        $this->load->model('design/layout');

        $this->data['layouts'] = $this->model_design_layout->getLayouts();

        $queryAuctionModule = $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'special_count_down'"
        );

        $this->data['auctionModuleEnabled'] = false;
        if ($queryAuctionModule->num_rows) {

            $this->data['auctionModuleEnabled'] = true;

            if (!isset($this->request->get['product_id'])) {
                $this->data['product_id'] = '';
            } else {
                $this->data['product_id'] = $this->request->get['product_id'];
                $this->data['productbidid'] = '';
            }

            if (isset($this->request->get['product_id'])) {
                $this->data['path'] =
                    HTTP_CATALOG . 'index.php?route=product/product?product_id=' . $this->request->get['product_id'];
            }

            if (!empty($this->data['productbidid'])) {
                $this->data['product_bid_id'] = $this->data['productbidid'];
            } else {
                $this->data['product_bid_id'] = '';
            }

            if (isset($this->request->get['product_id'])) {
                $auction = $this->model_catalog_product->getproductauction($this->request->get['product_id']);
            } else {
                $auction = "";
            }

            if (isset($this->request->post['use_max_price_on'])) {
                $this->data['use_max_price_on'] = $this->request->post['use_max_price_on'];
            } elseif (!empty($auction['max_price_status'])) {
                $this->data['use_max_price_on'] = $auction['max_price_status'];
            } else {
                $this->data['use_max_price_on'] = "";
            }

            if (isset($this->request->post['status_on'])) {
                $this->data['status_on'] = $this->request->post['status_on'];
            } elseif (!empty($auction['auction_status'])) {
                $this->data['status_on'] = $auction['auction_status'];
            } else {
                $this->data['status_on'] = "";
            }

            if (isset($this->request->post['start_time'])) {
                $this->data['start_time'] = $this->request->post['start_time'];
            } elseif (
                !empty($auction['bid_date_start']) &&
                ($auction['bid_date_start'] != '0000-00-00 0:0:0' && $auction['bid_date_start'] != null)
            ) {
                $this->data['start_time'] = $auction['bid_date_start'];
            } else {
                $this->data['start_time'] = "";
            }

            if (isset($this->request->post['end_time'])) {
                $this->data['end_time'] = $this->request->post['end_time'];
            } elseif (
                !empty($auction['bid_date_end']) &&
                ($auction['bid_date_end'] != '0000-00-00 0:0:0' && $auction['bid_date_end'] != null)
            ) {
                $this->data['end_time'] = $auction['bid_date_end'];
            } else {
                $this->data['end_time'] = '';
            }

            if (isset($this->request->post['start_price'])) {
                $this->data['start_price'] = $this->request->post['start_price'];
            } elseif (!empty($auction)) {
                $this->data['start_price'] = $auction['bid_start_price'];
            } else {
                $this->data['start_price'] = '';
            }

            if (isset($this->request->post['max_price'])) {
                $this->data['max_price'] = $this->request->post['max_price'];
            } elseif (!empty($auction['bid_max_price'])) {
                $this->data['max_price'] = $auction['bid_max_price'];
            } else {
                $this->data['max_price'] = '';
            }

            if (isset($this->request->post['min_offer_step'])) {
                $this->data['min_offer_step'] = $this->request->post['min_offer_step'];
            } elseif (!empty($auction['bid_min_price'])) {
                $this->data['min_offer_step'] = $auction['bid_min_price'];
            } else {
                $this->data['min_offer_step'] = 1;
            }
        }

        if (isset($this->request->post['barcode'])) {
            $this->data['barcode'] = $this->request->post['barcode'];
        } elseif (!empty($product_info)) {
            $this->data['barcode'] = $product_info['barcode'];
        } else {
            $this->data['barcode'] = '';
        }

        if ($this->data['barcode'] != '') {
            $barcodeGenerator = (new BarcodeGenerator())
                ->setType($this->config->get('config_barcode_type'))
                ->setBarcode($this->data['barcode']);

            $this->data['barcode_image'] = $barcodeGenerator->generate();
        }

        // check cardless app 
        $this->data['is_cardless_app_installed'] = \Extension::isinstalled('cardless');
        if ($this->data['is_cardless_app_installed']) {
            $this->load->model('module/cardless');
            $cardless_product = $this->model_module_cardless->isCardlessProduct($product_info['product_id']);
            if ($cardless_product) {
                $this->data['cardless_sku'] = $cardless_product['cardless_sku'];
            }
        }

        // append products for option values 
		$this->load->model('catalog/product');
		$this->data['products'] = $this->model_catalog_product->getProductsFields(['name']);
        $this->data['all_cats'] = $this->model_catalog_category->getCategories();

        ///Category Droplist APP
        $this->data['category_droplist'] = 0;
        $category_droplist = $this->config->get('category_droplist');
        if(isset($category_droplist) && $category_droplist['status'] == 1){
            $this->data['category_droplist'] = 1;
        }

        //Check if MS & MS Messaging is installed
        $this->load->model('multiseller/status');
        $multiseller = $this->model_multiseller_status->is_installed();
        if($multiseller){
            $this->data['ms_active'] = true;
        }

        //Check if Product Video Links App installed, Then get Video information to form
        $this->_addProductVideoLinksAppData($product_info['product_id']);

        //Check if notify me when available app installed and product has requests
        $this->productNotifyMeCheck($product_info['product_id']);

        //$this->document->addScript('view/javascript/cube/dropzone.min.js');
        $this->document->addScript('view/javascript/cube/scripts.js');

        if (isset($this->data['insertWizard']) && $this->data['insertWizard'] == true) {
            $this->template = 'catalog/product/insertWizard.expand';
        } else {
            $this->template = 'catalog/product/form.expand';
        }
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->data['default_store_id'] = $this->config->get('config_store_id') ?: 0;

        $this->response->setOutput($this->render());
    }


    /**
    * Add Product Video Links App data if installed
    *
    * @return void
    */
    private function _addProductVideoLinksAppData($product_id){
        $this->load->model('module/product_video_links');
        $product_video_links_installed = $this->model_module_product_video_links->isInstalled();

        if($product_video_links_installed){

            $this->data['product_video_links_installed']  = $product_video_links_installed;

            $this->load->model('catalog/product');
            $video_info = $this->model_catalog_product->getVideoURLInfo($product_id);
            $this->data['order_status_id_evu']  = $video_info['order_status_id_evu'];
            $this->data['external_video_url']   = $video_info['external_video_url'];
        }
    }

    /**
     * Notify me when available App
     *
     * @return void
     */
    private function productNotifyMeCheck($product_id){
        $this->load->model('module/product_notify_me');
        $product_notify_me = $this->model_module_product_notify_me->isActive();

        if($product_notify_me && $this->model_module_product_notify_me->isAutoNotify() && $this->model_module_product_notify_me->productHasRequests($product_id)){
            $this->data['product_notify_me']  = true;
        }
    }

    protected function validateSkuVariations()
    {
        if ($this->request->post['subtract'] == 0) {
            return true;
        }


        $variationQuantities = [];
        foreach ($this->request->post['product_options_variations'] as $variation) {
            foreach ($variation['options'] as $option) {
                $variationQuantities[$option] += $variation['quantity'];
            }
        }

        $optionsQuantities = [];
        foreach ($this->request->post['product_option'] as $option) {
            $optionsQuantities = array_replace(
                $optionsQuantities,
                array_column($option['product_option_value'], 'quantity', 'option_value_id')
            );

        }

        foreach ($optionsQuantities as $optionsKey => $optionsQuantity) {
            if ($optionsQuantity < $variationQuantities[$optionsKey]) {
                $this->error['variation'][] = $optionsKey . ': ' .$this->language->get('error_big_sku_quantity').'<br/>';
            }
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        if (count($this->error) > 0) {
            return false;
        } else {
            return true;
        }
    }

    
    private function validateForm()
    {
        $fullProduct=[];
        $duplicate=0;
        if ( ! $this->user->hasPermission('modify', 'catalog/product') )
        {
            $this->error['error'] = $this->language->get('error_permission');
        }

        foreach ( $this->request->post['product_description'] as $language_id => $value )
        {
            if(!$fullProduct && ($value['name'] && strip_tags($value['description']))){
                $fullProduct = $value;
                break;
            }
        }
        $i=0;
        $first_name_value="";
        $first_description_value="";
        foreach ( $this->request->post['product_description'] as $language_id => $value )
        {
            if ($i == 0 && ((utf8_strlen(ltrim($value['name'] ," "))< 2) || (utf8_strlen(ltrim($value['name'] ," ")) > 255)))
            {
                $this->error['name_'.$language_id] = $this->language->get('error_name');
            }
            if ($i==0){
                $first_name_value = $this->request->post['product_description'][$language_id]['name'];
                $first_description_value = $this->request->post['product_description'][$language_id]['description'];
            }

            if ($i !=0 && utf8_strlen(ltrim($value['name'] ," "))< 1)
            {
                $this->request->post['product_description'][$language_id]['name']=$first_name_value;
                $duplicate=1;
            }

            if ($i ==0 && !$fullProduct && utf8_strlen(ltrim(strip_tags($value['description'])," ")) < 1)
            {
                $this->error['description_' . $language_id] = $this->language->get('error_field_cant_be_empty');
            }

            if ($i !=0 && utf8_strlen(ltrim(strip_tags($value['description']) ," "))< 1)
            {
                $this->request->post['product_description'][$language_id]['description']=$first_description_value;
                $duplicate=1;
            }
            $i++;

        }

        //Check if the data doesn't have at least one full product
//        if(!$fullProduct){
//            $this->error['empty_fields'] = $this->language->get('error_field_empty');
//        }

        $queryAuctionModule = $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'special_count_down'"
        );

        if ( $queryAuctionModule->num_rows )
        {
            if ( isset($this->request->post['status_on']) )
            {
                if (empty($this->request->post['start_time'])) {
                    $this->error['start_time'] = $this->language->get('error_start');
                }

                if (empty($this->request->post['start_price'])) {
                    $this->error['bid_price'] = $this->language->get('error_bid_price');
                }
            }

            if (isset($this->request->post['status_on'])) {
                if (empty($this->request->post['end_time'])) {
                    $this->error['end_time'] = $this->language->get('error_end_time');
                }
            }

            if (isset($this->request->post['use_max_price_on'])) {
                if (empty($this->request->post['max_price'])) {
                    $this->error['max_price'] = $this->language->get('error_max_price');
                }
            }
        }

        foreach ($this->request->post['product_option'] as $option) {
            if (
                $this->request->post['subtract'] == 1 &&  $this->request->post['unlimited'] != 1 &&
                array_sum(array_column($option['product_option_value'], 'quantity')) > $this->request->post['quantity']
            ) {
                $this->error['quantity_count'] = $this->language->get('error_quantity') . ' ( ' . $this->request->post['quantity'] . ' )';
                $this->session->data['errors']['quantity_count'] = $this->error['quantity_count'];
            }
        }

        if ($this->config->get('unique_barcode') == 1) {

            $product_id = isset($this->request->get['product_id']) ? $this->request->get['product_id'] : 0;

            $barcode = trim($this->request->post['barcode']);

            if ($barcode != '') {

                $productByBarcode = $this->model_catalog_product->getProductByBarcode($barcode);
                $productByBarcode = array_column($productByBarcode, 'product_id', 'product_id');
                unset($productByBarcode[$product_id]);

                if (
                    $productByBarcode && (
                        $product_id == 0 || count($productByBarcode) > 0
                    )
                ) {
                    $duplicatedProducts = [];
                    foreach ($productByBarcode as $k => $v) {
                        $duplicatedProducts[] = sprintf(
                            "<a href='%s'>#$v</a>",
                            $this->url->link('catalog/product/update', 'product_id=' . $v)
                        );
                    }
                    $this->error['barcode'] = sprintf(
                        $this->language->get('error_barcode_exists'),
                        implode(',', $duplicatedProducts)
                    );
                }

            }

        }

        if ($this->config->get('unique_sku') == 1) {

            $product_id = isset($this->request->get['product_id']) ? $this->request->get['product_id'] : 0;

            $sku = trim($this->request->post['sku']);

            if ($sku != '') {

                $productBySku = $this->model_catalog_product->getProductBySku($sku);
                $productBySku = array_column($productBySku, 'product_id', 'product_id');
                unset($productBySku[$product_id]);

                if (
                    $productBySku && (
                        $product_id == 0 || count($productBySku) > 0
                    )
                ) {
                    $duplicatedProducts = [];
                    foreach ($productBySku as $k => $v) {
                        $duplicatedProducts[] = sprintf(
                            "<a href='%s'>#$v</a>",
                            $this->url->link('catalog/product/update', 'product_id=' . $v)
                        );
                    }
                    $this->error['sku'] = sprintf(
                        $this->language->get('error_sku_exists'),
                        implode(',', $duplicatedProducts)
                    );
                }

            }

        }
        
        if (!empty($this->request->post['external_video_url'])){
            if(!filter_var($this->request->post['external_video_url'], FILTER_VALIDATE_URL)) {
                $this->error['external_video_url'] = $this->language->get('error_invalid_url');
            }
        }

        $this->load->model('module/gold');
        $is_gold_app = $this->model_module_gold->isActive();

        if (!isset($this->request->post['price']) || ($this->request->post['price'] <= 0 && $this->request->post['price'] != -1)) {
            if(!$is_gold_app){
                $this->error['price'] = $this->language->get('error_price');
            }
        }

        if (isset($this->request->post['discount_price']) &&
            !empty($this->request->post['price']) &&
            $this->request->post['discount_price'] >= $this->request->post['price'] &&
            $this->request->post['price'] != -1)
        {
            $this->error['discount_price'] = $this->language->get('error_discount_price');
        }


        if ($is_gold_app && (!isset($this->request->post['gold_caliber_weight']) || $this->request->post['gold_caliber_weight'] <= 0 )) {
            $this->error['gold_caliber_weight'] = $this->language->get('error_gold_caliber_weight');
        }

        if (count($this->request->post['product_special_discount']) > DISCOUNTSLIMIT && $this->plan_id == "3"){
            $this->error['product_special_discount'] = $this->language->get('error_discount_limit_reached');
        }

        if ( $this->error && !isset($this->error['error']) )
        {
           // $this->error['warning'] = $this->language->get('error_warning');
        }

        if(
            (!isset($this->request->post['duplicate']) || $this->request->post['duplicate'] != 1)
            && $duplicate==1 && !$this->error
        ){
            $this->duplicate = 1;
            return false;
        }


        //Validate Stock Forecasting App Sum of Quantities...
        if($this->request->post['stock_forecasting']){
            $quantity = $this->request->post['quantity'];
            
            $sum_quantity = 0;

            foreach ($this->request->post['stock_forecasting'] as $record) {
                $sum_quantity += $record['quantity'];
            }

            if($sum_quantity > $quantity){
                $this->error['warning'] = 'Stock Forecasting App:';
                $this->error['stock_forecasting']  = "Sum of quantities is greater than the product quantity";
            }
        }

        return $this->error ? false : true;
    }


    protected function validateDelete()
    {
        if (!$this->user->hasPermission('modify', 'catalog/product')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    protected function validateCopy()
    {
        if (!$this->user->hasPermission('modify', 'catalog/product')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    public function combos(
        $data,
        $group = array(),
        $val = null,
        $i = 0,
        $option_name = NULL,
        $option_value_id = NULL,
        $option_id = NULL,
        $isoption = NULL
    )
    {

        $comb_array = array();
        if (isset($val)) {
            $group[]['name'] = $option_name;
            end($group);
            $group[key($group)]['option'] = $isoption;
            $group[key($group)]['parent_id'] = $option_id;
            $group[key($group)]['value'] = $val;
            $group[key($group)]['option_value_id'] = $option_value_id;
        }
        if ($i >= count($data)) {
            array_push($this->all, $group);

        } else {
            foreach ($data[$i]['value'] as $v) {
                $this->combos(
                    $data,
                    $group,
                    $v['value'],
                    $i + 1,
                    $data[$i]['name'],
                    $v['option_value_id'],
                    $data[$i]['option_id'],
                    $isoption
                );
            }
        }
        return $this->all;
    }

    public function autocomplete()
    {
        $customerGroup = null;
        
        if (isset($this->request->get['customer_group_id'])) {
            if (preg_match('#^[0-9]+$#', $this->request->get['customer_group_id'])) {
                $customerGroup = $this->request->get['customer_group_id'];
            }
        }

        $json = array();

        if (
            isset($this->request->get['filter_name']) ||
            isset($this->request->get['filter_model']) ||
            isset($this->request->get['filter_category_id']) ||
            isset($this->request->get['filter_manufacturer_id']) ||
            isset($this->request->get['filter_status'])
        ) {
            $this->load->model('catalog/product');
            $this->load->model('catalog/option');

            if (isset($this->request->get['filter_name'])) {
                $filter_name = $this->request->get['filter_name'];
            } else {
                $filter_name = '';
            }

            if (isset($this->request->get['filter_model'])) {
                $filter_model = $this->request->get['filter_model'];
            } else {
                $filter_model = '';
            }

            if (isset($this->request->get['filter_barcode'])) {
                $filter_barcode = $this->request->get['filter_barcode'];
            } else {
                $filter_barcode = '';
            }

            if (isset($this->request->get['filter_category_id'])) {
                $filter_category_id = $this->request->get['filter_category_id'];
            } else {
                $filter_category_id = '';
            }

            if (isset($this->request->get['filter_manufacturer_id'])) {
                $filter_manufacturer_id = $this->request->get['filter_manufacturer_id'];
            } else {
                $filter_manufacturer_id = '';
            }

            if (isset($this->request->get['filter_status'])) {
                $filter_status = $this->request->get['filter_status'];
            } else {
                $filter_status = '';
            }

            if (isset($this->request->get['limit'])) {
                $limit = $this->request->get['limit'];
            } else {
                $limit = 20;
            }

            $data = array(
                'filter_name' => $filter_name,
                'filter_model' => $filter_model,
                'filter_barcode' => $filter_barcode,
                'filter_category_id' => $filter_category_id,
                'filter_manufacturer_id' => $filter_manufacturer_id,
                'filter_status' => $filter_status,
                'start' => 0,
                'limit' => $limit
            );

            if (isset($this->request->get['filter_status'])) {
                $data['filter_status'] = $this->request->get['filter_status'];
            }

            $results = $this->model_catalog_product->getProducts($data);

            foreach ($results as $result) {
                $option_data = array();

                $product_options = $this->model_catalog_product->getProductOptions($result['product_id']);

                // Get Product special prices                 
                // $product_specials = $this->model_catalog_product->getProductSpecials($result['product_id']);
                $product_specials = $this->model_catalog_product->getProductDiscountsAndSpecials([
                    'product_id' => $result['product_id'],
                    'customer_group_id' => $customerGroup,
                ]);
                
                foreach ($product_options as $product_option) {
                    $option_info = $this->model_catalog_option->getOption($product_option['option_id']);

                    if ($option_info) {
                        if (
                            $option_info['type'] == 'select' ||
                            $option_info['type'] == 'radio' ||
                            $option_info['type'] == 'checkbox' ||
                            $option_info['type'] == 'image' ||
                            $option_info['type'] == 'product'
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
                                        'price_prefix' => $product_option_value['price_prefix'],
                                        'quantity' =>$product_option_value['quantity']
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

                $result['discount_price'] = 0;
                $result['discount_quantity'] = 0;

                if(count($product_specials) > 0){
                    foreach ($product_specials as $special_price) {

                        $date_start = $special_price['date_start'];
                        $date_end = $special_price['date_end'];
                        
                        if(
                            ($date_start == null || !$date_start || $date_start == "0000-00-00") &&
                            ($date_end == null || !$date_end || $date_end == "0000-00-00")
                        ) {
                            if ($special_price['type'] == 'discount') {
                                $result['discount_price'] = $special_price['price'];
                                $result['discount_quantity'] = $special_price['quantity'];
                                break;
                            } else if ($special_price['type'] == 'special') {
                                $result['price'] = $special_price['price'];
                                $result['discount_quantity'] = 1;
                                break;
                            }
                        } else {
                            if ($special_price['date_end'] >= date("Y-m-d",time())) {
                                if (!$date_start || $date_start =="0000-00-00" || $date_start <= date("Y-m-d",time())){
                                    if ($special_price['type'] == 'discount') {
                                        $result['discount_price'] = $special_price['price'];
                                        $result['discount_quantity'] = $special_price['quantity'];
                                        break;
                                    } else if ($special_price['type'] == 'special') {
                                        $result['price'] = $special_price['price'];
                                        $result['discount_quantity'] = 1;
                                        break;
                                    }
                                }
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
                    'discount_price' => $result['discount_price'],
                    'discount_quantity' => $result['discount_quantity'],
                    'total' => $result['price'],
                    'image' => \Filesystem::getUrl('image/' . $result['image']),
                    'quantity' => $result['quantity'],
                    'unlimited'=>$result['unlimited']
                );
            }
        }

        $this->response->setOutput(json_encode($json));
    }

    public function massEdit()
    {
        $product_id = (int)$this->request->get['product_id'];
        if(!preg_match("/^[1-9][0-9]*$/", $product_id)) return false;

        $this->language->load('catalog/product');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->initializer([
            'catalog/product',
            'catalog/manufacturer'
        ]);

        if (isset($this->request->get['product_id']) == false) {
            // handle error

            return 'error';
        }

        $ids = $this->request->get['product_id'];

        if (count($ids) < 1) {
            // handle error

            return 'error';
        }

        $products = $this->product->getProductsByIds($ids);

        $this->data['manufacturers'] = $this->manufacturer->getManufacturers();

        $this->data['products'] = $products;

        $this->template = 'catalog/product/massEdit.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->data['links'] = [
            'massUpdate' => $this->url->link('catalog/product/massUpdate', '', 'SSL'),
            'cancel' => $this->url->link('catalog/product', '', 'SSL'),
            'return_product' => $this->url->link('catalog/product', '', 'SSL'),
            'manufacturers' => $this->url->link('catalog/manufacturer/autocomplete', '', 'SSL'),
        ];

        $this->response->setOutput($this->render_ecwig());
    }

    public function massUpdate()
    {
        $this->language->load('catalog/product');

        $response = [];

        if ($this->request->server['REQUEST_METHOD'] != 'POST') {
            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors'] = $this->language->get('error_invalid_request');

            $this->response->setOutput(json_encode($response));
            return;
        }

        if (!isset($this->request->post['data'])) {
            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors'] = $this->language->get('error_invalid_parameters');

            $this->response->setOutput(json_encode($response));
            return;
        }

        $data = $this->request->post['data'];

        if (count($data) == 0) {
            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors'] = $this->language->get('error_nothing_to_update');

            $this->response->setOutput(json_encode($response));
            return;
        }

        $this->initializer([
            'catalog/product'
        ]);

        $products = array_column($data, null, 'product_id');

        foreach ($products as $productId => $product) {
            $this->product->massEditProducts($productId, $product);
        }

        $response['status'] = 'success';
        $response['title'] = $this->language->get('notification_success_title');
        $response['message'] = $this->language->get('message_updated_successfully');

        $this->response->setOutput(json_encode($response));
        return;
    }

    public function updateFromFacebook() {

        if($this->request->server['REQUEST_METHOD'] == "POST") {

            if(isset($this->request->post["product_id"])) {


                $this->initializer([
                    'catalog/product'
                ]);

                $product_id         = $this->request->post["product_id"];

                $data["name"]       = $this->request->post["name"];

                $data["price"]      = $this->request->post["price"];

                $this->product->updateNameAndPrice($product_id, $data);
            }
        }
    }

    public function dtUpdateStatus()
    {
        $this->language->load('catalog/product');
        // check for updating status permission
        if (!$this->user->hasPermission('modify', 'catalog/product')) {
            $this->error['warning'] = $this->language->get('error_permission');
            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors'] = $this->error;
            $this->response->setOutput(json_encode($response));
            return;
        }
        $this->load->model("catalog/product");

        if($this->products_limit){
            $this->last_product_in_limit_id= $this->model_catalog_product->getLastProductInLimitId($this->products_limit);
        }

        if($this->last_product_in_limit_id != null && $this->request->post["id"] > $this->last_product_in_limit_id ){
            $response['limit_reached'] = '1';
            $this->response->setOutput(json_encode($response));
            return;
        }

        if(\Extension::isInstalled('multiseller')){
            $this->load->model("multiseller/product");
        }
        $this->load->model("setting/setting");
        $this->load->model("localisation/language");
        if (isset($this->request->post["id"]) && isset($this->request->post["status"])) {
            $id = $product_id = $this->request->post["id"];
            $status = $this->request->post["status"];

            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("product");
            if($pageStatus) {
            // get old data
            $this->load->model('catalog/attribute');
            $this->load->model('catalog/option');

            $oldValue['product_info'] = $this->model_catalog_product->getProduct($product_id);
            $oldValue['product_description'] = $this->model_catalog_product->getProductDescriptions($product_id);
            $oldValue['product_store'] = $this->model_catalog_product->getProductStores($product_id);
            $oldValue['categories'] = $this->model_catalog_product->getProductCategories($product_id);
            $oldValue['filters'] = $this->model_catalog_product->getProductFilters($product_id);
            $oldValue['product_images'] = $this->model_catalog_product->getProductImages($product_id);
            $product_attributes = $this->model_catalog_product->getProductAttributes($product_id);

            $oldValue['product_attributes'] =  array();

            foreach ($product_attributes as $product_attribute) {
                $attribute_info = $this->model_catalog_attribute->getAttribute($product_attribute['attribute_id']);

                if ($attribute_info) {
                    $oldValue['product_attributes'][] = array(
                        'attribute_id' => $product_attribute['attribute_id'],
                        'name' => $attribute_info['name'],
                        'GroupName' => $attribute_info['GroupName'],
                        'product_attribute_description' => $product_attribute['product_attribute_description']
                    );
                }
            }

            $product_options = $this->model_catalog_product->getProductOptions($product_id);

            $oldValue['product_options'] = array();

            foreach ($product_options as $product_option) {
                $option_info = $this->model_catalog_option->getOption($product_option['option_id']);

                if ($option_info) {
                    if (
                        $option_info['type'] == 'select' ||
                        $option_info['type'] == 'radio' ||
                        $option_info['type'] == 'checkbox' ||
                        $option_info['type'] == 'image' ||
                        $option_info['type'] == 'product'
                    ) {
                        $product_option_value_data = array();

                        foreach ($product_option['product_option_value'] as $product_option_value) {
                            $product_option_value_data[] = array(
                                // Product Option Image PRO module <<
                                'images' => (isset($product_option_value['images']) ? $product_option_value['images'] : []),
                                // >> Product Option Image PRO module
                                'product_option_value_id' => $product_option_value['product_option_value_id'],
                                'option_value_id' => $product_option_value['option_value_id'],
                                'quantity' => $product_option_value['quantity'],
                                'subtract' => $product_option_value['subtract'],
                                'price' => $product_option_value['price'],
                                'price_prefix' => $product_option_value['price_prefix'],
                                'points' => $product_option_value['points'],
                                'points_prefix' => $product_option_value['points_prefix'],
                                'weight' => $product_option_value['weight'],
                                'weight_prefix' => $product_option_value['weight_prefix'],
                                'name' => $product_option_value['name']
                            );
                        }

                        $oldValue['product_options'][] = array(
                            'product_option_id' => $product_option['product_option_id'],
                            'product_option_value' => $product_option_value_data,
                            'option_id' => $product_option['option_id'],
                            'name' => $option_info['name'],
                            'type' => $option_info['type'],
                            'required' => $product_option['required']
                        );
                    } else {
                        $oldValue['product_options'][] = array(
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

            $oldValue['product_discounts'] = $this->model_catalog_product->getProductDiscounts($product_id);

            // add data to log_history

                $log_history['action'] = 'update';
                $log_history['reference_id'] = $product_id;
                $log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
                $oldValue['product_info']['status'] = $status;
                $log_history['new_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
                $log_history['type'] = 'product';
                $this->load->model('loghistory/histories');
                $this->model_loghistory_histories->addHistory($log_history);
            }

            $this->model_catalog_product->updateProductStatus($id, $status);
                
            // ZOHO inventory update product status
            $this->load->model('module/zoho_inventory');
            $this->model_module_zoho_inventory->changeProductStatus($id, $status);

            // Odoo update product status
            if (\Extension::isInstalled('odoo') && $this->config->get('odoo')['status']
            && $this->config->get('odoo')['products_integrate'])
            {
                $this->load->model('module/odoo/products');
                $this->model_module_odoo_products->changeProductStatus($id, $status);
            }
			
			//lableb indexing 
			if( \Extension::isInstalled('lableb')) {
                    $this->lablebIndexing($id);
            }


            $response['status'] = 'success';
            $response['title'] = $this->language->get('notification_success_title');
            $response['message'] = $this->language->get('message_updated_successfully');

            $adminActiveLanguageCode = $this->model_setting_setting->getSetting("config")["config_admin_language"];
            $activeLanguageId = $this->model_localisation_language->getLanguageByCode($adminActiveLanguageCode)["language_id"];
            if(\Extension::isInstalled('multiseller')){
                $sellerId = $this->model_multiseller_product->getProductSellerId($id)["seller_id"];
                $seller_info = $this->MsLoader->MsSeller->getSellerBasic($sellerId);
            }
            $productName = $this->model_catalog_product->getProductName($id, $activeLanguageId)["name"];
            $_vars = [
                         'store_url'        => HTTP_CATALOG,
                         'seller_email'     => $seller_info['email'], 
                         'seller_firstname' => $seller_info['firstname'],
                         'seller_lastname'  => $seller_info['lastname'],
                         'seller_nickname'  => $seller_info['nickname'],
                         'product_name'     => $productName
                     ];

            if($status == 0){
                $mails[] = array(
                            'type' => MsMail::SMT_PRODUCT_MODIFIED,
                            'data' => array('class' => 'ControllerCatalogProduct', 
                                            'function' => 'dtUpdateStatus_reject',
                                            'vars'   => $_vars,
                                            'recipients' => $seller_info['email'],
                                            'addressee' => $seller_info['firstname']
                                        )
                        );
            // 2 is Update a product in audit_trial_event_type table
            $this->audit_trail->log([
                'event_type'=> 2,
                'event_desc'=>'Disable Product',
                'resource_id' => $id,
                'resource_type' => 'product'
            ]);
            }else if($status == 1){
                $mails[] = array(
                            'type' => MsMail::SMT_PRODUCT_MODIFIED,
                            'data' => array('class' => 'ControllerCatalogProduct', 
                                            'function' => 'dtUpdateStatus_approve',
                                            'vars'   => $_vars,
                                            'recipients' => $seller_info['email'],
                                            'addressee' => $seller_info['firstname']
                                        )
                        );
                // 2 is Update a product in audit_trial_event_type table
                $this->audit_trail->log([
                    'event_type'=> 2,
                    'event_desc'=>'Enable Product',
                    'resource_id' => $id,
                    'resource_type' => 'product'
                ]);
            }
            if($this->config->get('multiseller')){
                $this->MsLoader->MsMail->sendMails($mails);
            }

        } else {
            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors'] = $this->language->get('notification_unknoen_error');
        }

        $this->response->setOutput(json_encode($response));
        return;
    }

    public function dtDelete()
    {
        $this->language->load('catalog/product');

        $this->load->model('catalog/product');

        $this->load->model('setting/setting');
        // check if a product has orders then show a confirmation message
        if (
            $this->model_catalog_product->getRelatedOrderProductsByIds($this->request->post['selected'], 'num_rows') > 0 &&
            (!isset($this->request->post['hard_delete']) || $this->request->post['hard_delete'] == 'n')
        ) {
            $response['status'] = 'warning';
            $response['title'] = 'are you sure?';
            $response['errors'] = 'sure sure?';
            $this->response->setOutput(json_encode($response));
            return;
        }
        // delete steps
        // 1.  check not to exceed maximum allowed limit to delete at one time
        // 2.  check to ordinary delete or archive depending on "useQueueLimit" if number exceeds then archive, if not delete directly 
        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            if(count($this->request->post['selected']) > $this->deleteMaxLimit){
                $response['status'] = 'error';
                $response['title'] = $this->language->get('notification_error_title');
                $response['errors'][] = sprintf($this->language->get('error_delete_exceeded'),$this->deleteMaxLimit);
                $this->response->setOutput(json_encode($response));
                return;                    
            }
            else{
                // check if number exeeds useQueueLimit or not
                if(count($this->request->post['selected']) > $this->useQueueLimit){
                    // archive
                    $response = $this->archive($this->request->post['selected']);
                }
                else{
                    // ordinary deletion
                    $response = $this->dtDeleteProcess($this->request->post);
                }
            }
        } 
        else {
            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors'] = $this->error;
        }
        $this->response->setOutput(json_encode($response));
        return;
    }

    //Export Products to Dropna
    public function dropnaExport()
    {
        $this->language->load('catalog/product');

        $this->load->model('catalog/product');

        $ids = $this->request->post['selected'];
        if (isset($ids)) {
            // Add reference to track progress
            $uniqid = uniqid();
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting(
                'dropna_import', ['dropna_import_reference' => $uniqid, 'dropna_import_closed' => 0 ]
            );

            $values = [];
            foreach ($this->request->post['selected'] as $product_id) {
                $values[] = "(".$product_id.",". "'wait', '".$uniqid."')";
            }
            $values = implode($values, ',');

            //Use Dropna schedule
            if(count($ids) > 20){
               
                $schedStatus = $this->model_catalog_product->dropnaScheduleProduct($values, true);

                if($schedStatus){
                    $response['status'] = 'success';
                    $response['title'] = $this->language->get('notification_success_title');
                    $response['message'] = $this->language->get('message_dropnaExport_progress');
                }else{
                    $response['status'] = 'error';
                    $response['title']  = $this->language->get('notification_error_title');
                    $response['errors'] = ['Dropna API Error'];
                }
            //Direct Export to Dropna
            }else{
                $this->model_catalog_product->dropnaScheduleProduct($values);
                $this->model_catalog_product->dropnaExportProducts();
                /*foreach ($ids as $product_id) {
                    $this->model_catalog_product->dropnaExportProduct($product_id);
                }*/

                $response['status'] = 'success';
                $response['title'] = $this->language->get('notification_success_title');
                $response['message'] = $this->language->get('message_dropnaExport_progress'); 
            }
        } else {
            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors'] = ['No Selection'];
        }

        $this->response->setOutput(json_encode($response));
        return;
    }

    //Get Dropna Shcedule data
    public function dropnaScheduleDt(){
        $dropna_reference = $this->config->get('dropna_import_reference');

        $this->load->model('module/be_supplier');
        $dropna_import_data = $this->model_module_be_supplier->getImportDate($dropna_reference);

        $status = 0;
        $data = [];

        if( $dropna_import_data ){
            $status = 1;
            $data['dropna_import_total'] = $dropna_import_data['total'];
            $data['dropna_import_success'] = $dropna_import_data['success'];
            $data['dropna_import_wait'] = $dropna_import_data['wait'];
        }

        $this->response->setOutput(json_encode(['status' => $status, 'data' => $data]));
        return;
    }

    //Update Dropna Progress Alert Status
    public function updateDropnaAlert(){
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSettingValue(
            'dropna_import', 'dropna_import_closed', 1
        );
    } 
    
    public function barcode()
    {
        if ($this->request->server['REQUEST_METHOD'] != 'POST') {

            $this->response->setOutput(json_encode(['status' => 'fail']));
            return;

        }
        if (isset($this->request->post['barcode'])) {
            $this->data['barcode'] = $this->request->post['barcode'];
        } else {
            $this->data['barcode'] = '';
        }

        if ($this->data['barcode'] != '') {
            $barcodeGenerator = (new BarcodeGenerator())
                ->setType($this->config->get('config_barcode_type'))
                ->setBarcode($this->data['barcode']);

            $response['status'] = 'success';
            $response['barcode'] = $barcodeGenerator->generate();

            $this->response->setOutput(json_encode($response));
            return;
        }
    }


    public function massCategoryUpdate()
    {
        $this->load->model('catalog/product');
        $this->language->load('catalog/product');

        $overwrite = ! empty($this->request->post['overwrite']) && $this->request->post['overwrite'] == 'true' ? true : false;

        foreach( $this->request->post['product_ids'] as $product_id ) {
            $this->model_catalog_product->updateProductCategory($product_id, $this->request->post, $overwrite);
        }

        $this->response->setOutput( json_encode([
            'status'    => 'success',
            'title'     => $this->language->get('notification_success_title'),
            'message'   => $this->language->get('message_updated_successfully'),
        ]) );

        return;
    }

    //// Update Products slug
    public function updateProductsSlug()
    {   
        echo 'Updating......';
        $this->load->model('catalog/product');
        $all_descriptions = $this->model_catalog_product->getProductsDescription();
        
        foreach ($all_descriptions as $desc) {
            if(!$desc['slug']){
               $this->model_catalog_product->slugUpdate($desc['product_id'], $desc['language_id'], $desc['name']); 
            }
        }

        echo 'Done.';
    }
    
    public function editor()
    {
        $this->language->load('catalog/editor');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('catalog/product');

        $product_id = $this->request->get['product_id'];

        $language_id = $this->request->get['language_id'];

        $product_info = $this->model_catalog_product->getProduct($product_id);

        if (!$product_info) {
            $this->session->data['error_warning'] = $this->language->get('error_product_not_found');
            return $this->response->redirect($this->url->link('catalog/product', '', true));
        }

        // Check if App Ext. is installed and active
        $this->load->model('module/custom_product_editor');
        if (!$this->model_module_custom_product_editor->isActive()) {
            $this->session->data['error_warning'] = $this->language->get('error_extension_not_installed_or_not_active');
            return $this->response->redirect($this->url->link('catalog/product', '', true));
        }

        $this->data['product_id'] = $product_id;

        // Save HTML Markup
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $language_id = $this->request->post['language_id'];
            if (!$language_id) {
                return $this->response->setOutput(json_encode(['status' => '0', 'error_msg' => $this->language->get('error_product_language_required')]));
            }

            $description = $this->model_catalog_product->getProductDescriptions($product_id, $language_id);

            if (!$description) {
                return $this->response->setOutput(json_encode(['status' => '0', 'error_msg' => $this->language->get('error_product_language_required')]));
            }

            $description = array_merge(current($description), ['custom_html' => $this->request->post['custom_html']]);

            $this->model_catalog_product->updateProductDescription($product_id, $language_id, $description);

            $this->model_catalog_product->updateProductCustomHtmlPageSettings($product_id, [
                'custom_html_status' => (int) $this->request->post['custom_html_status'] ?? 0,
                'display_main_page_layout' => (int) $this->request->post['display_main_page_layout'] ?? 0,
            ]);

            $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';

            return $this->response->setOutput(json_encode($result_json));
        }

        $url = "&product_id=" . $product_id;

        if ($language_id) {
            $url .= "&language_id=" . $language_id;
        }

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('products'),
            'href' => $this->url->link('catalog/product', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('update_product'),
            'href' => $this->url->link('catalog/product/update', "&product_id=" . $product_id, 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('catalog/product/editor', '' . $url, 'SSL'),
            'separator' => ' :: '
        );

        $this->data['links'] = [
            'submit' => $this->url->link(
                'catalog/product/editor',
                'product_id=' . $product_id,
                'SSL'
            ),
            'fetch_page' => $this->url->link(
                'catalog/product/fetchPage',
                'product_id=' . $product_id,
                'SSL'
            ),
            'cancel' => $this->url->link(
                'catalog/product/update',
                'product_id=' . $product_id,
                'SSL'
            )
        ];

        $descriptions = $this->model_catalog_product->getProductDescriptions(
            $product_id
        );

        $this->data['product_languages'] = array();

        $this->load->model('localisation/language');

        foreach ($descriptions as $desc) {
            $language = $this->model_localisation_language->getLanguage($desc['language_id']);
            $this->data['product_languages'][] = [
                'language_id' => $language['language_id'],
                'name' => $language['name'],
            ];

            if ($language_id && (int) $language_id === (int) $language['language_id']) {
                $this->data['selected_custom_html'] = $desc['custom_html'] ?? "";
                $this->data['selected_language_id'] = $language_id;
            }
        }

        if (!isset($this->data['selected_custom_html']) && !empty($descriptions)) {
            $this->data['selected_custom_html'] = current($descriptions)['custom_html'] ?? "";
            $this->data['selected_language_id'] = current($descriptions)['language_id'];
        }

        $this->data['custom_html_status'] = $product_info['custom_html_status'];
        $this->data['display_main_page_layout'] = $product_info['display_main_page_layout'];

        $this->template = 'catalog/product/editor.expand';

        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }
    
    public function fetchPage()
    {
        $result_json['success'] = '0';
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $page = $this->request->post['page'];
            //TODO: FIX SSRF vulnarbility https://seclab.nu/static/publications/sac21-prevent-ssrf.pdf
            //$data = file_get_contents($page);

            $result_json['data'] = $data;

            $result_json['success'] = '1';

        }
        
        return $this->response->setOutput(json_encode($result_json));
    }
    
    public function updateSwitchModeCounter(){

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' && isset($this->request->post['switch_mode_counter'])){
            $this->load->model('setting/setting');
            $this->model_setting_setting->insertUpdateSetting('config', $this->request->post);
            $result_json['success'] = '1';
            $this->response->setOutput(json_encode($result_json));
            return;
        }
        $result_json['success'] = '0';
        $this->response->setOutput(json_encode($result_json));
    }

    public function updateProductDefaultMode(){

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' && isset($this->request->post['enable_advanced_mode'])){
            $this->load->model('setting/setting');
            $this->model_setting_setting->insertUpdateSetting('config', $this->request->post);
            $result_json['success'] = '1';
            $this->response->setOutput(json_encode($result_json));
            return;
        }
        $result_json['success'] = '0';
        $this->response->setOutput(json_encode($result_json));
    }

    public function removeDemoProducts(){
        if ( $this->request->server['REQUEST_METHOD'] == 'POST' ){
            $this->load->model('catalog/product');
            $this->model_catalog_product->removeDemoProducts();
            $result_json['success'] = '1';
            $this->response->setOutput(json_encode($result_json));
            return;
        }
        $result_json['success'] = '0';
        $this->response->setOutput(json_encode($result_json));
    }
 
   private function lablebIndexing($product_id){
       $lableb_settings = $this->config->get('lableb');
        $this->load->model('catalog/product');
        $this->load->model('module/lableb');
		//return multiple products according to languages founds | false for un-needed check for indexing 
		$lableb_products  = $this->model_catalog_product->getLablebProducts($product_id,false);
		
		/*
		 * lableb_products array will be empty if the product status =0 
		 * we didnt indexing in-active products 
		 * will be indexed when activate it at the product it 
		 *
		 */
		if(!empty($lableb_products)){
			$indexing_result = $this->model_module_lableb->indexProducts($lableb_products,$lableb_settings['project'],$lableb_settings['index_apikey']);
		}
    }

   private function lablebDeleteIndexing($product_id){
       $this->load->model('module/lableb');
		$indexing_result = $this->model_module_lableb->deleteIndexes($product_id);
    }


    public function getProductOptions()
    {
        $product_id = $this->request->get['product_id'];

        $this->load->model('catalog/option');
        $this->load->model('catalog/product');
        if (isset($this->request->post['product_option'])) {
            $product_options = $this->request->post['product_option'];
        } elseif (isset($this->request->get['product_id'])) {
            $product_options = $this->model_catalog_product->getProductOptions($product_id,$this->request->get);
        } else {
            $product_options = array();
        }

        $this->data['product_options'] = array();
        $json_result = array();
        foreach ($product_options as $product_option) {
            $option_info = $this->model_catalog_option->getOption($product_option['option_id']);

            if ($option_info) {
                if (
                    $option_info['type'] == 'select' ||
                    $option_info['type'] == 'radio' ||
                    $option_info['type'] == 'checkbox' ||
                    $option_info['type'] == 'image' ||
                    $option_info['type'] == 'product'
                ) {
                    $product_option_value_data = array();

                    foreach ($product_option['product_option_value'] as $product_option_value) {
                        $json_result['ids'][$product_option['option_id']][] = $product_option_value['option_value_id'];
                        $product_option_value_data[] = array(
                            // Product Option Image PRO module <<
                            'images' => (isset($product_option_value['images']) ? $product_option_value['images'] : []),
                            // >> Product Option Image PRO module
                            'product_option_value_id' => $product_option_value['product_option_value_id'],
                            'option_value_id' => $product_option_value['option_value_id'],
                            'quantity' => $product_option_value['quantity'],
                            'subtract' => $product_option_value['subtract'],
                            'price' => $product_option_value['price'],
                            'price_prefix' => $product_option_value['price_prefix'],
                            'points' => $product_option_value['points'],
                            'points_prefix' => $product_option_value['points_prefix'],
                            'weight' => $product_option_value['weight'],
                            'weight_prefix' => $product_option_value['weight_prefix'],
                            'name' => $product_option_value['name']
                        );
                    }

                    $this->data['product_options'][] = array(
                        'product_option_id' => $product_option['product_option_id'],
                        'product_option_value' => $product_option_value_data,
                        'option_id' => $product_option['option_id'],
                        'name' => $option_info['name'],
                        'type' => $option_info['type'],
                        'required' => $product_option['required'],
                        'total'=> $product_option['total']

                    );
                } else {
                    $json_result['ids'][$product_option['option_id']][] = $product_option_value['option_value_id'];
                    $this->data['product_options'][] = array(
                        'product_option_id' => $product_option['product_option_id'],
                        'option_id' => $product_option['option_id'],
                        'name' => $option_info['name'],
                        'type' => $option_info['type'],
                        'option_value' => $product_option['option_value'],
                        'required' => $product_option['required'],
                        'total'=> $product_option['total']

                    );
                }
            }
        }
        $json_result['data'] = $this->data['product_options'];
        $json_result['success'] = '1';
        $json_result['success_msg'] = $this->language->get('text_success');
        $this->response->setOutput(json_encode($json_result));

    }
    public function deleteOptionValue(){
        
        $this->load->model('catalog/product');
         $is_deleted = $this->model_catalog_product->deleteOptionValue($this->request->post['id'], $this->request->post['product_id']);
         if($is_deleted) 
         return $this->response->setOutput(json_encode(['status' => 'OK', ]));
        else 
        return $this->response->setOutput(json_encode(['status' => 'false', ]));
   }

   public function revokeOption(){

       $this->load->model('catalog/product');

       $is_deleted = $this->model_catalog_product->revokeOption($this->request->post['product_id'], $this->request->post['option_id']);

       if($is_deleted)
           return $this->response->setOutput(json_encode(['status' => 'OK', ]));
       else
           return $this->response->setOutput(json_encode(['status' => 'false', ]));
   }


    public function dtDeleteProcess($postData,$process_id = 0)
    {
        $this->language->load('catalog/product');
        $this->load->model('catalog/product');
        $this->load->model('setting/setting');
        $this->load->model('catalog/attribute');
        $this->load->model('catalog/option');
        // ZOHO inventory delete product if app is setup
        $this->load->model('module/zoho_inventory');
        foreach ($postData['selected'] as $product_id) {
            // start looping on each product
            $attrs[] = ['product_id' => $product_id];
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("product");
            // check if audit_trail is applied to products module, if so retrieve old values to save them
            if($pageStatus) {
                // get old data
                $oldValue['product_info'] = $this->model_catalog_product->getProduct($product_id);
                $oldValue['product_description'] = $this->model_catalog_product->getProductDescriptions($product_id);
                $oldValue['product_store'] = $this->model_catalog_product->getProductStores($product_id);
                $oldValue['categories'] = $this->model_catalog_product->getProductCategories($product_id);
                $oldValue['filters'] = $this->model_catalog_product->getProductFilters($product_id);
                $oldValue['product_images'] = $this->model_catalog_product->getProductImages($product_id);
                $product_attributes = $this->model_catalog_product->getProductAttributes($product_id);
                $oldValue['product_attributes'] =  array();
                foreach ($product_attributes as $product_attribute) {
                    $attribute_info = $this->model_catalog_attribute->getAttribute($product_attribute['attribute_id']);

                    if ($attribute_info) {
                        $oldValue['product_attributes'][] = array(
                            'attribute_id' => $product_attribute['attribute_id'],
                            'name' => $attribute_info['name'],
                            'GroupName' => $attribute_info['GroupName'],
                            'product_attribute_description' => $product_attribute['product_attribute_description']
                        );
                    }
                }
                $product_options = $this->model_catalog_product->getProductOptions($product_id);
                $oldValue['product_options'] = array();
                foreach ($product_options as $product_option) {
                    $option_info = $this->model_catalog_option->getOption($product_option['option_id']);

                    if ($option_info) {
                        if (
                            $option_info['type'] == 'select' ||
                            $option_info['type'] == 'radio' ||
                            $option_info['type'] == 'checkbox' ||
                            $option_info['type'] == 'image' ||
                            $option_info['type'] == 'product'
                        )
                        {
                            $product_option_value_data = array();

                            foreach ($product_option['product_option_value'] as $product_option_value) {
                                $product_option_value_data[] = array(
                                    // Product Option Image PRO module <<
                                    'images' => (isset($product_option_value['images']) ? $product_option_value['images'] : []),
                                    // >> Product Option Image PRO module
                                    'product_option_value_id' => $product_option_value['product_option_value_id'],
                                    'option_value_id' => $product_option_value['option_value_id'],
                                    'quantity' => $product_option_value['quantity'],
                                    'subtract' => $product_option_value['subtract'],
                                    'price' => $product_option_value['price'],
                                    'price_prefix' => $product_option_value['price_prefix'],
                                    'points' => $product_option_value['points'],
                                    'points_prefix' => $product_option_value['points_prefix'],
                                    'weight' => $product_option_value['weight'],
                                    'weight_prefix' => $product_option_value['weight_prefix'],
                                    'name' => $product_option_value['name']
                                );
                            }
                            $oldValue['product_options'][] = array(
                                'product_option_id' => $product_option['product_option_id'],
                                'product_option_value' => $product_option_value_data,
                                'option_id' => $product_option['option_id'],
                                'name' => $option_info['name'],
                                'type' => $option_info['type'],
                                'required' => $product_option['required']
                            );
                        } else {
                            $oldValue['product_options'][] = array(
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
                $oldValue['product_discounts'] = $this->model_catalog_product->getProductDiscounts($product_id);
                // add data to log_history
                $this->load->model('loghistory/histories');
                $log_history['action'] = 'delete';
                $log_history['reference_id'] = $product_id;
                $log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
                $log_history['new_value'] = NULL;
                $log_history['type'] = 'product';
                $this->model_loghistory_histories->addHistory($log_history);
            }
            // end of saving old values to audit trail app

            // delete main product from expand
            $this->model_catalog_product->deleteProduct($product_id);
            // check for each product at our integrations to delete from the other sides
            $zoho_settings = $this->config->get('zoho_inventory');
            $this->model_module_zoho_inventory->deleteProduct($product_id,$zoho_settings['organization_id']);
            // Odoo delete product if app is setup
            if (\Extension::isInstalled('odoo') && $this->config->get('odoo')['status']
            && $this->config->get('odoo')['products_integrate']){   
                $odoo_settings = $this->config->get('odoo');
                $this->load->model('module/odoo/products');
                $this->model_module_odoo_products->deleteProduct($product_id,$odoo_settings);
            }
            // lableb delete product if app is setup            
            if (\Extension::isInstalled('lableb') && $this->config->get('lableb')['status']) {
                // This -- $this->lableb->deleteProduct($product_id)-- does not exist!!
                //$this->initializer(['lableb' => 'module/lableb/settings']);
                //$this->lableb->deleteProduct($product_id);
                $this->lablebDeleteIndexing($product_id);
            }
            // end of looping on each product
        }

        // check to delete all products from the other side of our integrations
        if (\Extension::isInstalled('printful')) {
            $this->initializer(['printfulapi' => 'module/printful/api']);
            $this->printfulapi->deletePrintfulProductByEspandProductIds($postData['selected']);
        }

        if (\Extension::isInstalled('facebook_import')) {
            $this->initializer(['facebookImport' => 'module/facebook_import/facebook_import']);
            $this->facebookImport->detachProductByExpandProductIds($postData['selected']);
        }
        // save statistics
        $store_statistics = new StoreStatistics($this->user);
        $store_statistics->store_statistcs_push('products', 'delete', $attrs);
        // update process status to done if there is a process working
        if($process_id)
            $this->model_catalog_product->updateDeleteProcessStatus($process_id);

        $response['status'] = 'success';
        $response['title'] = $this->language->get('notification_success_title');
        $response['message'] = $this->language->get('message_deleted_successfully');
        return $response;
    }

    public function archive($product_ids)
    {
        //archive products
        $this->model_catalog_product->archive($product_ids); 
        // insert process to processes table
        $this->model_catalog_product->insertDeleteProcess($product_ids); 
        $response['status'] = 'success';
        $response['title'] = $this->language->get('notification_success_title');
        $response['message'] = $this->language->get('message_deleted_successfully');
        return $response;
    }

    public function backgroundDeleteProcess()
    {
        $this->language->load('catalog/product');
        $this->load->model('catalog/product');
        // check for incompleted delete processes
        $incompletedProcesses = $this->model_catalog_product->getIncomlpetedDeleteProcesses();
        if(count($incompletedProcesses)){
            // run at the background
            $file_location = $this->queueFileLocation;
            $operation = escapeshellarg("delete");
            $storecode = escapeshellarg(STORECODE);
            $productIds = escapeshellarg($incompletedProcesses[0]['products']);            
            $processId = escapeshellarg($incompletedProcesses[0]['id']);    
            // production environment
            //shell_exec("php $file_location $operation $storecode $processId $productIds  >/dev/null 2>&1 &");
            // local environment 
            shell_exec("php $file_location $operation $storecode $processId $productIds");            
        }
    }

}
