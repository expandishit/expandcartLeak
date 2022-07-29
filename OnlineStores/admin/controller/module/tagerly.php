<?php

class ControllerModuleTagerly extends Controller
{
    private $settings;
    protected $errors = [];
    private $route = 'module/tagerly';
    private $totalProducts = 0;
    private $queueFileLocation = DIR_SYSTEM . 'library/tagerly_queue.php';


    public function index()
    {

        $this->load->model('module/tagerly/settings');
        $this->language->load('module/tagerly');

        $this->document->setTitle($this->language->get('tagerly_heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_modules'),
            'href'      => $this->url->link(
                'marketplace/home',
                '',
                'SSL'
            ),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('tagerly_heading_title'),
            'href'      => $this->url->link('module/tagerly', '', 'SSL'),
            'separator' => ' :: '
        );


        // get app settings
        $this->data['settingsData'] = $this->model_module_tagerly_settings->getSettings();

        $this->load->model('catalog/category');
        $this->load->model('localisation/order_status');

        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        $this->data['categories'] = $this->model_catalog_category->getCategories();


        $this->data['action'] = $this->url->link('module/tagerly/updateSettings', '', 'SSL');
        $this->data['cancel'] = $this->url.'marketplace/home';

        if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] ) {
            $this->data['ajax_loader']  = HTTPS_SERVER .'view/image/knawat_ajax_loader.gif';
        }else{
            $this->data['ajax_loader']  = HTTP_SERVER .'view/image/knawat_ajax_loader.gif';
        }
        $this->data['tagerly_import_categories_ajax_url'] = $this->url->link( $this->route .'/ajax_import_categories', '', true);
        $this->data['tagerly_import_products_ajax_url'] = $this->url->link( $this->route .'/ajax_import_products', '', true);
        $this->data['tagerly_sync_products_ajax_url'] = $this->url->link( $this->route .'/ajax_sync_products', '', true);
        $this->data['egpCurrencyExist'] = $this->currency->has('EGP');

        $this->template = 'module/tagerly/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }

    public function ajax_import_categories(){
        $this->load->model('module/tagerly/category');
        $this->language->load('module/tagerly');
        $this->load->model('module/tagerly/settings');
        // get app settings
        $settingsData = $this->model_module_tagerly_settings->getSettings();

        $params = array('apikey' => $settingsData['apikey'],'secretkey' => $settingsData['secretkey'],'' => '');

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.tagerly.net/api/v2/json/type/get_all_cats",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => http_build_query($params) ,
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response,true);
        $categories_count = 0;
        if( is_array($response) && count($response) > 0){
            if($response['status'] == 200){
                foreach($response['content'] as $key=>$category){
                    if(!$this->model_module_tagerly_category->checkCategoryExists($category['ID'])){
                        $this->model_module_tagerly_category->add_category($category);
                        $categories_count++;
                    }
                }
            }
        }
        echo $categories_count;
        exit;

    }

    public function ajax_sync_products(){
        $this->language->load('module/tagerly');
        $this->load->model('module/tagerly/settings');
        $this->load->model('module/tagerly/product');
        // get app settings
        $settingsData = $this->model_module_tagerly_settings->getSettings();
        $this->load->model('catalog/product');

        $products = $this->model_catalog_product->getProductsFields(['sku']);

        $products_count = 0;
        foreach ($products as $key=> $product){
            $curl = curl_init();

            $params = array('apikey' => $settingsData['apikey'], 'secretkey' => $settingsData['secretkey'],'sku' => $product['sku']);

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://www.tagerly.net/api/v2/json/type/sync_stock",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => http_build_query($params),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            $response = json_decode($response,true);
            if($response['status'] == 200){
                if( is_array($response['content']) && count($response['content']) > 0){
                    $data['product_id'] = $product['product_id'];
                    $data['quantity'] = $response['content']['stock'] ;
                    $data['cost_price'] = $response['content']['price'] ;
                    //$data['status'] = ($response['content']['status'] ==  "publish") ? 1 : 0 ;
                    $this->model_module_tagerly_product->syncProduct($data);
                    $products_count++;
                }
            }

        }

        echo $products_count;
        exit;

    }

    public function ajax_import_products(){

        if ( $this->request->post )
        {
            $this->language->load('module/tagerly');
            if ( ! $this->validateImportProducts() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->errors;

                $this->response->setOutput(json_encode($result_json));


                return;
            }

            $data = $this->request->post['tagerly'];
            $this->load->model('module/tagerly/product');

            $this->language->load('module/tagerly');


            if($data['import_from'] == 'custom'){
                $file_location = $this->queueFileLocation;
                $categories_ids = implode(',', $data['categories_ids']);
                $storecode = STORECODE;
                $operation = "custom";
                $this->fetchProductsFromCustomCategories($categories_ids);
                shell_exec("php $file_location $operation $storecode $categories_ids  >/dev/null 2>&1 &");
            }elseif($data['import_from'] == 'list') {
                $file_location = $this->queueFileLocation;
                $storecode = STORECODE;
                $operation = "list";
                $this->fetchFromTagerlyList();
                shell_exec("php $file_location $operation $storecode >/dev/null 2>&1 &");
            }else {
                $file_location = $this->queueFileLocation;
                $storecode = STORECODE;
                $operation = "all";
                $this->fetchAllProducts();
                shell_exec("php $file_location $operation $storecode >/dev/null 2>&1 &");
            }

            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('job_started_message');
            $this->response->setOutput(json_encode($result_json));
            return;
        }

    }

    public function fetchProductsFromCustomCategories($categories_ids){

        $categories_ids = explode(',', $categories_ids);
        if(is_array($categories_ids) && count($categories_ids) > 0){
            $this->load->model('module/tagerly/settings');
            $this->load->model('catalog/product');
            // get total products
            $total_products = $this->model_catalog_product->getTotalProductsCount();

            // get app settings
            $settingsData = $this->model_module_tagerly_settings->getSettings();

            foreach($categories_ids as $category_id){
                $pagesCount = $this->getAllProductsPagesCountForCategory($category_id);
                for($i = 1; $i <= $pagesCount; $i++){
                    $curl = curl_init();

                    $params = array('apikey' => $settingsData['apikey'],'secretkey' => $settingsData['secretkey'],'cat_id' => $category_id,'page' => $i);

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://www.tagerly.net/api/v2/json/type/get_category_products",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS => http_build_query($params),
                    ));

                    $response = curl_exec($curl);
                    curl_close($curl);
                    $response = json_decode($response,true);

                    if($response['status'] == 200){
                        foreach ($response['content'] as $key=>$value){
                            if($total_products <= PRODUCTSLIMIT){
                                $this->add_all_data($value);
                                $total_products++;
                            }else{
                                return;
                            }
                        }
                    }
                }
            }
        }
    }

    public function fetchFromTagerlyList(){
        $this->load->model('module/tagerly/settings');
        $this->load->model('catalog/product');
        // get total products
        $total_products = $this->model_catalog_product->getTotalProductsCount();

        // get app settings
        $settingsData = $this->model_module_tagerly_settings->getSettings();

        $params = array('apikey' => $settingsData['apikey'],'secretkey' => $settingsData['secretkey'],'' => '');

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.tagerly.net/api/v2/json/type/get_listed_products",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => http_build_query($params),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response,true);

        if($response['status'] == 200){
            foreach ($response['content'] as $key=>$product){
                if($total_products <= PRODUCTSLIMIT){
                    $this->add_all_data($product);
                    $total_products++;
                }else{
                    return;
                }
            }
        }

    }

    public function fetchAllProducts(){
        $pagesCount = $this->getAllProductsPagesCount();
        $this->load->model('module/tagerly/settings');
        $this->load->model('catalog/product');
        // get total products
        $total_products = $this->model_catalog_product->getTotalProductsCount();
        // get app settings
        $settingsData = $this->model_module_tagerly_settings->getSettings();

        for($i = 1; $i <= $pagesCount; $i++){
            $curl = curl_init();

            $params = array('apikey' => $settingsData['apikey'],'secretkey' => $settingsData['secretkey'],'page' => $i);

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://beta.tagerly.net/api/v2.1/json/tg.php?type=get_all_products",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => http_build_query($params),
                CURLOPT_HTTPHEADER => array(
                    ": ",
                    ": "
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            $response = json_decode($response,true);
            $product_count = count($response['content']);
            if($response['status'] == 200){
                for($y = 0; $y <= $product_count; $y++){
                    if($total_products <= PRODUCTSLIMIT){
                        $this->add_all_data($response['content'][$y]);
                        $total_products++;
                    }else{
                        return;
                    }
                }
            }

        }

    }

    private function getAllProductsPagesCount(){

        $pagesCount = 0;
        $this->load->model('module/tagerly/settings');
        // get app settings
        $settingsData = $this->model_module_tagerly_settings->getSettings();

        $params = array('apikey' => $settingsData['apikey'],'secretkey' => $settingsData['secretkey'],'page' => '1');

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.tagerly.net/api/v2/json/type/get_all_products",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_HTTPHEADER => array(
                ": ",
                ": "
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $response = json_decode($response,true);

        if($response['status'] == 200){
            $pagesCount = ceil(($response['count'] / 100));
        }

        return $pagesCount;

    }

    private function getAllProductsPagesCountForCategory($category_id){
        $pagesCount = 0;

        $this->load->model('module/tagerly/settings');
        // get app settings
        $settingsData = $this->model_module_tagerly_settings->getSettings();

        $params = array('apikey' => $settingsData['apikey'],'secretkey' => $settingsData['secretkey'],'cat_id' => $category_id,'page' => '1');

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.tagerly.net/api/v2/json/type/get_category_products",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => http_build_query($params),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response,true);

        if($response['status'] == 200){
            $pagesCount = ceil(($response['count'] / 100));
        }

        return $pagesCount;
    }

    private function getTagerlyStates(){

        $this->load->model('module/tagerly/settings');
        // get app settings
        $settingsData = $this->model_module_tagerly_settings->getSettings();

        $params = array('apikey' => $settingsData['apikey'],'secretkey' => $settingsData['secretkey']);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.tagerly.net/api/v2/json/type/get_egy_states",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => http_build_query($params),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response,true);

        $states = [];

        if($response['status'] == 200){
            $states = $response['content'] ;
        }

        return $states;
    }

    private function add_all_data($value){
        $this->load->model('catalog/product');
        $this->load->model('module/tagerly/product');
        $this->load->model('module/tagerly/settings');
        $settingsData = $this->model_module_tagerly_settings->getSettings();

        // check product exists before
        if ($this->model_catalog_product->checkIfProductSkuIsExisted($value['product_serial'])) {
            return;
        }
        // add margin profit 
        if($settingsData['margin']){
            $value['margin_price'] = ($value['product_price'] *  $settingsData['margin'] / 100);
            $value['selling_price'] = $value['margin_price'] + $value['product_price'];    
        }
        $egpCurrencyExist = $this->currency->has('EGP');
        $productData['model'] = '';
        $productData['sku'] = $value['product_serial'];
        $productData['upc'] = '';
        $productData['ean'] = '';
        $productData['jan'] = '';
        $productData['isbn'] = '';
        $productData['mpn'] = '';
        $productData['location'] = '';
        $productData['quantity'] = (int)$value['product_stock'];
        $productData['minimum'] = 0;
        $productData['preparation_days'] = 0;
        $productData['maximum'] = 0;
        $productData['subtract'] = 0;
        $productData['notes'] = '';
        $productData['barcode'] = '';
        $productData['stock_status_id'] = 0;
        $productData['date_available'] = date('Y-m-d', time() - 86400);;
        $productData['manufacturer_id'] = 0;
        $productData['shipping'] = 1;
        $productData['image'] = (isset($value['product_images']) && count($value['product_images']) > 0) ? $this->save_image($value['product_images'][0],time()) : "";
        $productData['price'] = $this->currency->convert($value['selling_price'],'EGP',$this->config->get('config_currency'));
        $productData['cost_price'] = $this->currency->convert($value['product_price'],'EGP',$this->config->get('config_currency'));
        $productData['points'] = 0;
        $productData['weight'] = 0;
        $productData['weight_class_id'] = 0;
        $productData['length'] = 0;
        $productData['width'] = 0;
        $productData['height'] = 0;
        $productData['length_class_id'] = 0;
        $productData['status'] = (int)0;
        $productData['tax_class_id'] = 0;
        $productData['sort_order'] = 0;
        $productData['product_description'][1]['name'] = $value['product_title'];
        $productData['product_description'][1]['description'] = $value['product_description'];
        $productData['product_description'][2]['name'] = $value['product_title'];
        $productData['product_description'][2]['description'] = $value['product_description'];
        array_shift($value['product_images']);
        $productImages = array();
        foreach ($value['product_images'] as $imageData){
            $productImages[] =  $this->save_image($imageData,time());
        }
        $productData['product_image'] = $productImages;
        $productData['product_category'] = $value['product_cats'];
        $productData['product_store'] = array(0);
        if($egpCurrencyExist) {
            $this->model_module_tagerly_product->addProduct($productData);
        }
    }

    public function save_image( $image_url, $prefix = '' ){
        if( !empty( $image_url ) ){
            // Set variables for storage, fix file filename for query strings.
            preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $image_url, $matches );
            if ( ! $matches ) {
                return false;
            }

            $directory = 'image/data/tagerly/';

            if (!\Filesystem::isDirExists($directory)) {
                \Filesystem::createDir($directory);
                \Filesystem::setPath($directory)->changeMod('writable');
            }

            $image_name = $prefix . '_image_'.basename( $matches[0] );

            $full_image_path = $directory.$image_name;
            $catalog_path = 'data/tagerly/' . $image_name;

            if(\Filesystem::isExists($full_image_path)) {
                return $catalog_path;
            }else{
                // Download image.
                $ch = curl_init ( $image_url );
                curl_setopt( $ch, CURLOPT_HEADER, 0 );
                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt( $ch, CURLOPT_BINARYTRANSFER,1 );
                curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
                curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
                curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 120 );
                curl_setopt( $ch, CURLOPT_TIMEOUT, 120 );
                curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
                $raw_image_data = curl_exec( $ch );
                curl_close ($ch);

                try{

                    \Filesystem::setPath($full_image_path)->put($raw_image_data);
                    return $catalog_path;
                }catch( Exception $e ){
                    return false;
                }

            }
        }
        return false;
    }

    public function getTagerlyOrderData($order_id){
        $this->load->model('module/tagerly/settings');
        // get app settings
        $settingsData = $this->model_module_tagerly_settings->getSettings();
        $curl = curl_init();

        $params = array('apikey' => $settingsData['apikey'],'secretkey' => $settingsData['secretkey'],'order_id' => $order_id);

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.tagerly.net/api/v2/json/type/get_order",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => http_build_query($params) ,
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response,true);
        return $response;

    }

    public function sendOrder(){

        if (isset($this->request->get['order_id'])) {
            $order_id = $this->request->get['order_id'];
        } else {
            $order_id = 0;
        }
        $this->data['order_id'] = $order_id;
        // load model
        $this->load->model('sale/order');
        // get order details
        $order_info = $this->data['order_info'] = $this->model_sale_order->getOrder($order_id);
        // check order exists
        if($order_info){
            $this->language->load('module/tagerly');

            $this->data['action'] = $this->url->link("module/tagerly/sendOrder", 'order_id=' . $this->request->get['order_id'] . $url, 'SSL');

            $this->data['states'] = $this->getTagerlyStates();

            if ($this->request->server['REQUEST_METHOD'] == 'POST') {

                if ( ! $this->order_validate() )
                {
                    $result_json['success'] = '0';
                    $result_json['errors'] = $this->errors;

                    $this->response->setOutput(json_encode($result_json));

                    return;
                }

                $order_total_amount = round(($order_info['total'] * $order_info['currency_value']),2);
                $orderProducts = $this->model_sale_order->getOrderProducts($order_id);

                $order_items = '';
                foreach ($orderProducts as $product){
                    $this->load->model('catalog/product');
                    $poductData = $this->model_catalog_product->getProduct($product['product_id']);
                    $order_items .= $poductData['sku'].'@'.$product['quantity'].',';
                }

                //preparing request Data
                $tagerlyOrderData['customer_name'] = $this->request->post['name'];
                $tagerlyOrderData['customer_address'] = $this->request->post['address'];
                $tagerlyOrderData['customer_state'] = $this->request->post['state'];
                $tagerlyOrderData['customer_city'] = $order_info['shipping_city'];
                $tagerlyOrderData['customer_phone1'] = $this->request->post['phone'];
                $tagerlyOrderData['customer_phone2'] = $this->request->post['phone_2'];
                $tagerlyOrderData['customer_notes'] = $this->request->post['description'];
                $tagerlyOrderData['order_items'] = $order_items;
                $tagerlyOrderData['order_total'] = $order_total_amount;
                $tagerlyOrderData['order_id'] = $order_id;
                $tagerlyOrderData['payment_type'] = ($order_info['payment_code'] == 'cod') ? 'cod' : 'prepaid';

                // send order to tagerly
                $response = $this->sendOrderToTagerly($tagerlyOrderData);
                if ($response['status'] == 200) {
                    $responseData = $response['content'];
                    $responseData['expand_order_id'] = $order_id;
                    $this->model_module_tagerly_settings->addTagrlyOrder($responseData);

                    $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_order_success');
                    $result_json['success'] = '1';
                    $result_json['redirect'] = '1';
                    $result_json['to'] = (string) $this->url->link('sale/order/info', 'order_id=' . $this->request->get['order_id'], 'SSL');

                    $this->response->setOutput(json_encode($result_json));
                    return;
                }else{
                        $result_json['success'] = '0';
                        $result_json['errors'] = [$response['message']];
                        $result_json['errors']['warning'] = $this->language->get('error_warning');
                        $this->response->setOutput(json_encode($result_json));
                        return;
                }

            }

            $this->template = 'module/tagerly/sendOrderForm.expand';
            $this->children = array(
                'common/footer',
                'common/header'
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

    public function sendOrderToTagerly($data)
    {
        $this->load->model('module/tagerly/settings');
        // get app settings
        $settingsData = $this->model_module_tagerly_settings->getSettings();

        $order_id = $data['order_id'];

        $data['apikey'] = $settingsData['apikey'];
        $data['secretkey'] = $settingsData['secretkey'];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.tagerly.net/api/v2/json/type/add_order",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response, true);

        return $response;
    }

    public function updateSettings()
    {
        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
            $result_json['success'] = '0';
            $result_json['errors'] = 'Invalid Request';
        }else{
            $this->language->load('module/tagerly');

            if ( ! $this->settings_validate() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->errors;

                $this->response->setOutput(json_encode($result_json));

                return;
            }

            $this->load->model('module/tagerly/settings');

            $data = $this->request->post['tagerly'];

            $this->model_module_tagerly_settings->updateSettings(['tagerly' => $data]);

            $this->session->data['success'] = $this->language->get('text_settings_success');

            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
        }
        $this->response->setOutput(json_encode($result_json));
        return;
    }

    public function install()
    {
        $this->load->model('module/tagerly/settings');

        $this->model_module_tagerly_settings->install();
    }

    public function uninstall()
    {
        $this->load->model('module/tagerly/settings');

        $this->model_module_tagerly_settings->uninstall();
    }


    private function settings_validate()
    {
        $data = $this->request->post['tagerly'];

        if (empty($data['apikey'])) {
            $this->errors[] = $this->language->get('error_apikey_required');
        }

        if (empty($data['secretkey'])) {
            $this->errors[] = $this->language->get('error_secretkey_required');
        }

        if ($this->errors && !isset($this->errors['error'])) {
            $this->errors['warning'] = $this->language->get('error_warning');
        }
        return $this->errors ? false : true;
    }

    private function validateImportProducts()
    {
        $data = $this->request->post['tagerly'];

        if ($data['import_from'] == 'custom' && ( !is_array($data['categories_ids']) || count($data['categories_ids']) == 0)  ) {
            $this->errors[] = $this->language->get('error_categories_ids_required');
        }


        if ($this->errors && !isset($this->errors['error'])) {
            $this->errors['warning'] = $this->language->get('error_warning');
        }


        return $this->errors ? false : true;
    }

    private function order_validate()
    {
        $data = $this->request->post;

        if (empty($data['name'])) {
            $this->errors[] = $this->language->get('error_name_required');
        }

        if (empty($data['phone'])) {
            $this->errors[] = $this->language->get('error_phone_required');
        }

        if (empty($data['phone_2'])) {
            $this->errors[] = $this->language->get('error_phone_2_required');
        }

        if (empty($data['address'])) {
            $this->errors[] = $this->language->get('error_address_required');
        }

        if (empty($data['state'])) {
            $this->errors[] = $this->language->get('error_state_required');
        }

        if ($this->errors && !isset($this->errors['error'])) {
            $this->errors['warning'] = $this->language->get('error_warning');
        }
        return $this->errors ? false : true;
    }

}
