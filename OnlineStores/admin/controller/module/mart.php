<?php

class ControllerModuleMart extends Controller
{
    private $settings;
    protected $errors = [];
    private $route = 'module/mart';
    private $totalProducts = 0;
    private $queueFileLocation = DIR_SYSTEM . 'library/mart_queue.php';


    public function index()
    {
        $this->load->model('module/mart/settings');
        $this->language->load('module/mart');

        $this->document->setTitle($this->language->get('mart_heading_title'));

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
            'text'      => $this->language->get('mart_heading_title'),
            'href'      => $this->url->link('module/mart', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['entry_status'] = $this->language->get('entry_status');
        $this->data['text_enabled'] = $this->language->get('text_enabled');
        $this->data['text_disabled'] = $this->language->get('text_disabled');
        $this->data['text_settings'] = $this->language->get('text_settings');


        // get app settings
        $this->data['settingsData'] = $this->model_module_mart_settings->getSettings();


        $this->data['action'] = $this->url->link('module/mart/updateSettings', '', 'SSL');
        $this->data['cancel'] = $this->url.'marketplace/home';

        if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] ) {
            $this->data['ajax_loader']  = HTTPS_SERVER .'view/image/knawat_ajax_loader.gif';
        }else{
            $this->data['ajax_loader']  = HTTP_SERVER .'view/image/knawat_ajax_loader.gif';
        }
        $this->data['mart_import_categories_ajax_url'] = $this->url->link( $this->route .'/ajax_import_categories', '', true);
        $this->data['mart_import_products_ajax_url'] = $this->url->link( $this->route .'/ajax_import_products', '', true);

        $this->template = 'module/mart/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }

    public function ajax_import_categories(){
        $this->load->model('module/mart/category');
        $this->language->load('module/mart');
        $xml=simplexml_load_file("https://mart.ps/modules/xmlfeeds/api/xml.php?id=2",'SimpleXMLElement', LIBXML_NOCDATA);
        $response_array = json_decode(json_encode($xml),true);

        $categories_count = 0;
        if(isset($response_array['category'] ) && is_array($response_array['category'] )){
            foreach($response_array['category'] as $key=>$data){
                if($data['category_id'] == 1 || $data['category_id'] == 2){
                    continue;
                }
                if($data['category_parent_id'] == 2){
                    $data['category_parent_id'] = 0;
                }
                $this->model_module_mart_category->add_category($data);
                $categories_count++;
            }
        }
        echo $categories_count;
        exit;

    }

    public function ajax_import_products(){

        if ( $this->request->post )
        {
            $this->language->load('module/mart');
            if ( ! $this->validateImportProducts() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->errors;

                $this->response->setOutput(json_encode($result_json));


                return;
            }

            $data = $this->request->post['mart'];
            $this->load->model('module/mart/product');
            $this->load->model('module/mart/option');
            $this->load->model('catalog/option');

            $this->language->load('module/mart');


            if($data['import_from'] == 'custom'){
                $file_location = $this->queueFileLocation;
                $page_from = $data['page_from'];
                $page_to = $data['page_to'];
                $storecode = STORECODE;
                $operation = "custom";
                shell_exec("php $file_location $operation $storecode $page_from $page_to  >/dev/null 2>&1 &");
            }else {
                $file_location = $this->queueFileLocation;
                $storecode = STORECODE;
                $operation = "all";
                shell_exec("php $file_location $operation $storecode >/dev/null 2>&1 &");
            }

            $result_json['success'] = '1';
           // $result_json['success_msg'] = sprintf($this->language->get('job_started_message'),$this->totalProducts);
            $result_json['success_msg'] = $this->language->get('job_started_message');
            $this->response->setOutput(json_encode($result_json));
            return;
        }

    }

    public function fetchProductsFromCustomPages($page_from,$page_to){

        for($i = $page_from; $i <= $page_to; $i++ ){
            $url = "http://mart.ps/modules/xmlfeeds/api/xml.php?id=3&part=".$i;
            $xml = simplexml_load_file($url,'SimpleXMLElement', LIBXML_NOCDATA);
            $response_array = json_decode(json_encode($xml),true);

            foreach ($response_array['product'] as $key=>$value){
                $this->add_all_data($value);
                $this->totalProducts++;
            }
        }
    }

    public function fetchAllProducts(){
        $xmlForAllUrls = simplexml_load_file("https://mart.ps/modules/xmlfeeds/api/xml.php?id=3", 'SimpleXMLElement', LIBXML_NOCDATA);
        $response_array_all = json_decode(json_encode($xmlForAllUrls), true);
        foreach ($response_array_all as $keyData=>$data) {
            if ($keyData == "feeds_total") {
                continue;
            }
            $url = $data;
            $xml = simplexml_load_file($url, 'SimpleXMLElement', LIBXML_NOCDATA);
            $response_array = json_decode(json_encode($xml), true);

            foreach ($response_array['product'] as $key => $value) {
                $this->add_all_data($value);
                $this->totalProducts++;
            }

        }
    }

    private function add_all_data($value){

        $this->load->model('module/mart/product');
        $this->load->model('module/mart/option');
        $this->load->model('catalog/option');

        // check product exists before
        if ($this->model_module_mart_product->checkProductExists($value['product_id'])) {
            $this->model_module_mart_product->delete_product($value['product_id']);
        }

        if (isset($value['attributes'])) {
            foreach ($value['attributes'] as $key2 => $attr) {
                // check option exists
                $optionExists = $this->model_module_mart_option->checkOption($attr['group_name']);
                // yes exists
                if (count($optionExists) > 0) {
                    //check if option value exists before
                    $optionValueExists = $this->model_module_mart_option->checkOptionValue($attr['attribute_name'], $optionExists['option_id']);
                    if (count($optionValueExists) < 1) {
                        $optionValueData['option_id'] = $optionExists['option_id'];
                        $optionValueData['attribute_name'] = $attr['attribute_name'];
                        $option_value_id = $this->model_module_mart_option->add_option_value($optionValueData);
                    } else {
                        $option_value_id = $optionValueExists['option_value_id'];
                    }
                    $productOption_exists = $this->model_module_mart_option->checkProductOption($value['product_id'], $optionExists['option_id']);
                    if (count($productOption_exists) < 1) {
                        $productOptionData['product_id'] = $value['product_id'];
                        $productOptionData['option_id'] = $optionExists['option_id'];
                        $product_option_id = $this->model_module_mart_option->add_product_option($productOptionData);
                    } else {
                        $product_option_id = $productOption_exists['product_option_id'];
                    }
                    $productOptionValue['product_option_id'] = $product_option_id;
                    $productOptionValue['product_id'] = $value['product_id'];
                    $productOptionValue['option_id'] = $optionExists['option_id'];
                    $productOptionValue['option_value_id'] = $option_value_id;
                    $productOptionValue['quantity'] = $attr['quantity'];
                    $this->model_module_mart_option->add_product_option_value($productOptionValue);
                } else {
                    $optionData['name'] = $attr['group_name'];
                    $option_id = $this->model_module_mart_option->add_option($optionData);
                    $optionValueData['option_id'] = $option_id;
                    $optionValueData['attribute_name'] = $attr['attribute_name'];
                    $option_value_id = $this->model_module_mart_option->add_option_value($optionValueData);

                    $productOption_exists = $this->model_module_mart_option->checkProductOption($value['product_id'], $option_id);

                    if (count($productOption_exists) < 1) {
                        $productOptionData['product_id'] = $value['product_id'];
                        $productOptionData['option_id'] = $option_id;
                        $product_option_id = $this->model_module_mart_option->add_product_option($productOptionData);
                    } else {
                        $product_option_id = $productOption_exists['product_option_id'];
                    }
                    $productOptionValue['product_option_id'] = $product_option_id;
                    $productOptionValue['product_id'] = $value['product_id'];
                    $productOptionValue['option_id'] = $option_id;
                    $productOptionValue['option_value_id'] = $option_value_id;
                    $productOptionValue['quantity'] = $attr['quantity'];
                    $this->model_module_mart_option->add_product_option_value($productOptionValue);
                }
            }

        }

        $productData['product_id'] = $value['product_id'];
        $productData['model'] = '';
        $productData['sku'] = '';
        $productData['upc'] = '';
        $productData['ean'] = '';
        $productData['jan'] = '';
        $productData['isbn'] = '';
        $productData['mpn'] = '';
        $productData['location'] = '';
        $productData['quantity'] = (int)$value['quantity'];
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
        $image = "";
        if(isset($value['images']['image'])){
            if(is_array($value['images']['image'])){
                $image = $value['images']['image'][0];
            }else{
                $image = $value['images']['image'];
            }
        }
        $productData['image'] =  $this->save_image($image,$value['product_id']);
        $productData['price'] = $value['price'];
        $productData['cost_price'] = $value['price_sale'];
        $productData['points'] = 0;
        $productData['weight'] = 0;
        $productData['weight_class_id'] = 0;
        $productData['length'] = 0;
        $productData['width'] = 0;
        $productData['height'] = 0;
        $productData['length_class_id'] = 0;
        $productData['status'] = (int)$value['active'];
        $productData['tax_class_id'] = 0;
        $productData['sort_order'] = 0;
        $productData['name'] = $value['name'];
        $productData['description'] = $value['description_short'];
        array_shift($value['images']['image']);
        $productImages = array();
        foreach ($value['images']['image'] as $imageData){
            $productImages[] =  $this->save_image($imageData,$value['product_id']);
        }
        $productData['product_image'] = $productImages;
        $productData['category_id'] = $value['category_default_id'];
        $this->model_module_mart_product->add_product($productData);

    }

    public function save_image( $image_url, $prefix = '' ){
        if( !empty( $image_url ) ){
            // Set variables for storage, fix file filename for query strings.
            preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $image_url, $matches );
            if ( ! $matches ) {
                return false;
            }

            $directory = 'image/data/mart/';

            if (!\Filesystem::isDirExists($directory)) {
                \Filesystem::createDir($directory);
                \Filesystem::setPath($directory)->changeMod('writable');
            }

            $image_name = $prefix . '_image_'.basename( $matches[0] );

            $full_image_path = $directory.$image_name;
            $catalog_path = 'data/mart/' . $image_name;

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

    public function updateSettings()
    {
        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
             $result_json['success'] = '0';
             $result_json['errors'] = 'Invalid Request';
        }else{


            $this->load->model('module/mart/settings');
            $this->language->load('module/mart');

            $data = $this->request->post['mart'];

            $this->model_module_mart_settings->updateSettings(['mart' => $data]);

            $this->session->data['success'] = $this->language->get('text_settings_success');

            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
        }
        $this->response->setOutput(json_encode($result_json));
        return;
    }

    private function validateImportProducts()
    {
        $data = $this->request->post['mart'];

        if ($data['import_from'] == 'custom' && ( empty($data['page_from']) || (int) $data['page_from'] == 0)  ) {
            $this->errors[] = $this->language->get('error_page_from_required');
        }

        if ($data['import_from'] == 'custom' && ( empty($data['page_to']) || (int)$data['page_to'] == 0)  ) {
            $this->errors[] = $this->language->get('error_page_to_required');
        }

        if ($data['import_from'] == 'custom' &&  $data['page_to'] <  $data['page_from']  ) {
            $this->errors[] = $this->language->get('error_entry_page_to_less');
        }


        return $this->errors ? false : true;
    }
}
