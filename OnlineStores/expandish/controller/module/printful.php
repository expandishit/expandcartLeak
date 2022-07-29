<?php
class ControllerModulePrintful extends Controller
{
    private $guess_options = false;

    private $apiUrl = 'https://api.printful.com';

    public function settings()
    {
        if (!$this->checkModule() || !$this->customer->isLogged()) {
            $this->response->redirect($this->url->link('common/home', '', 'SSL'));
        }

        $this->load->model('catalog/category');
        $this->load->model('module/printful');
        $this->load->model('module/printful/api');
        $this->language->load_json('module/printful'); 

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_account'),
            'href'      => $this->url->link('seller/account', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );


        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('printful_settings'),
            'href'      => $this->url->link('module/printful/settings', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );

        $this->data['handle_import_url'] = $this->url->link('module/printful/handleImportAll', '', 'SSL');

        $this->data['categories'] = $this->MsLoader->MsProduct->getCategories(0, [
            'hide_restricted' => 1
        ]);

         //Check for session message
         if (isset($this->session->data['printful_message'])) {
            $this->data['printful_message'] = $this->session->data['printful_message'];
            unset($this->session->data['printful_message']);
        }
         
        $this->data['api_key'] = $this->model_module_printful->getAPIKey($this->customer->getId());
        $this->data['default_category'] = $this->model_module_printful->getGetDefaultCategory($this->customer->getId());
        
        if($this->data['api_key']){
            $this->data['printful_products'] = $this->model_module_printful_api->getProducts($this->data['api_key'],0,5);
            
            $this->data['imported_products'] = $this->model_module_printful_api->getImportedProducts($this->customer->getId(),0,50);
            
            $this->data['imported_products_count'] = $this->model_module_printful_api->getImportedProductsCount($this->customer->getId());
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        if (isset($this->session->data['error'])) {
            $this->data['error'] = $this->session->data['error'];
            unset($this->session->data['error']);
        }

        $this->document->setTitle($this->language->get('printful_settings'));

        if (file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/module/printful/settings.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/module/printful/settings.expand';
        } else {
            $this->template = 'default/template/module/printful/settings.expand';
        }

        $this->children = array(
            'common/footer',
            'common/header'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    /**
     * Initiate the background import job
     */
    public function handleImportAll()
    {
        $this->load->model('module/printful');
        $this->language->load_json('module/printful');

        //Start background job
        $file_location = DIR_SYSTEM.'library/import_from_printful_to_seller.php';
        $storecode = STORECODE;
        $seller_id = $this->customer->getId();
        $api_key = $this->model_module_printful->getAPIKey($this->customer->getId());

        //This should be run on production
        shell_exec("php $file_location $storecode $seller_id $api_key >/dev/null 2>&1 &");

        //Uncomment this for debug mode
        // echo shell_exec("php $file_location $storecode $seller_id $api_key");die;
        
        $this->session->data['printful_message'] = ['type' => 'success', 'message' => $this->language->get('job_started_message')];

        $this->response->redirect($this->url->link('module/printful/settings', '', 'SSL'));
    }

    /**
     * The background process to import all products from printful 
     * 
     * @param string $api_key
     * @param int $seller_id
     */
    public function handleImportInBackgroundForSeller(string $api_key='',int $seller_id=0)
    {
        $this->load->model('module/printful/api');

        
        try {
            $products = $this->model_module_printful_api->getProducts($api_key,0,2);
            
            $perPage = 100;
            $totalProducts = $products['paging']['total'];
            $totalPages = ceil($totalProducts / $perPage);
            $offset = 0;
            $lowest_price = null;
            $products = [];

            //Loop on all pages
            for ($i = 1; $i <= $totalPages; $i++) {
                //Loop on all products to import them
                foreach ($this->model_module_printful_api->getProducts($api_key, $offset, $perPage)['result'] as $product) {
                    //Add product to array
                    $products[$product['id']] = $product;

                    //Set lowest price to null
                    $lowest_price = null;

                    //Get product data and variants
                    $product_data = $this->model_module_printful_api->getProduct($api_key, $product['id']);

                    //Strip product name from all variants and guess options
                    foreach($product_data['result']['sync_variants'] as $variant){

                        $productDetails = $this->model_module_printful_api->getProductDetails(
                            $api_key, $variant['product']['variant_id']
                        );

                        $products[$product['id']]['description'] = $productDetails['result']['product']['description'];

                        //Set lowest price
                        if($variant['retail_price'] < $lowest_price || !$lowest_price){
                            $lowest_price = $variant['retail_price'];
                            $products[$product['id']]['lowest_price'] = $variant['retail_price'];
                        }
                        
                        //Get variant options
                        $variant_options = str_replace($product['name'],'',$variant['name']);
                        $variant_options = str_replace(' - ','',$variant_options);
                        
                        //check if the variant is empty then the product has no variants
                        if(!$variant_options){
                            continue;
                        }else{
                            //Loop on all variant options to guess it
    
                            //Remove whitespace
                            $variant_option = trim($variant_options);

                            if(!$this->guess_options){
                                $products[$product['id']]['options'][] = [
                                    'option_name' => 'variant',
                                    'option_value' => $variant_option,
                                    'option_data' => $variant
                                ];
                            }else{
                                /**
                                 * This code will try to split the option names and compare them to guess different options
                                 * It may however cause problems because we don't know exactly the type of data coming
                                 */
                                //Check if the option is size
                                //else, treat it as color
                                if(
                                    in_array($variant_option,$this->clothes_sizes) || 
                                    in_array($variant_option,$this->accessories_sizes) || 
                                    preg_match('#[0-9\.]*[×xX][0-9\.]*#',$variant_option)
                                ){
                                    //Add size to the products array
                                    $products[$product['id']]['options'][] = [
                                        'option_name' => 'size',
                                        'option_value' => $variant_option,
                                        'option_data' => $variant
                                    ];
                                }else{
                                    //Add color to the products array
                                    $products[$product['id']]['options'][] = [
                                        'option_name' => 'color',
                                        'option_value' => $variant_option,
                                        'option_data' => $variant
                                    ];
                                }
                            }
                        }
                    }
                }

                $offset += $perPage;
            }

            //Send all products to the model for insertion into DB
            $this->model_module_printful_api->importProductsToSeller($products,$this->guess_options,$seller_id);
        } catch (Exception $e) {
            // TODO: log error and redirect
        }
    }

    public function saveSettings()
    {
        if (!$this->checkModule() || !$this->customer->isLogged()) {
            return;
        }

        $this->load->model('module/printful');
        $this->language->load_json('module/printful');

        $sellerId = $this->customer->getId();
        $apiKey = trim($this->request->post['api_key']);
        $defaultCategory = trim($this->request->post['default_category']);

        $this->model_module_printful->insertAPIKey($sellerId, $apiKey);
        $this->model_module_printful->insertDefaultCategoryId($sellerId, $defaultCategory);

        $storeId = $this->getPrintfulStoreId($apiKey);

        if (!$storeId) {
            $this->session->data['error'] = $this->language->get('printful_token_error');
        } else {
            $this->model_module_printful->updateStoreId($sellerId, $storeId);
            $this->enableWebhook($apiKey, $sellerId);
        }

        if (!isset($this->session->data['error'])) {
            $this->session->data['success'] = $this->language->get('printful_txt_success');
        }

        $this->response->redirect($this->url->link('module/printful/settings', '', 'SSL'));
    }

    private function enableWebhook($api_key, $seller_id)
    {
        $ch = curl_init($this->apiUrl . '/webhooks');

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'url' => $this->url->link('module/printful/webhook', '', 'SSL'),
            'types' => ['product_updated'],
            'seller'
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($api_key)
        ]);

        $result = json_decode(curl_exec($ch), true);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if (curl_errno($ch)) {
            $this->session->data['error'] = $this->language->get('printful_generic_error');
        } else {
            if ($statusCode != 200) {
                $this->session->data['error'] = $this->language->get('printful_token_error');
            }
        }
    }

    public function webhook()
    {
        if (!$this->checkModule()) {
            return;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $productInfo = $this->getProductWithVariants($data['store'], $data['data']['sync_product']['id']);
        if (!$productInfo) {
            return;
        }
        $this->load->model('module/printful');
        $this->model_module_printful->insertProduct($productInfo);
    }

    private function getProductWithVariants($store_id, $product_id)
    {
        $this->load->model('module/printful');
        $apiKey = $this->model_module_printful->getAPIKeyByStoreId($store_id);

        $ch = curl_init($this->apiUrl . '/store/products/' . $product_id);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($apiKey)
        ]);

        return json_decode(curl_exec($ch), true) ?? false;
    }

    private function getPrintfulStoreId($api_key)
    {
        $ch = curl_init($this->apiUrl . '/store');

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($api_key)
        ]);
        return json_decode(curl_exec($ch), true)['result']['id'] ?? false;
    }

    private function checkModule()
    {
        if (\Extension::isInstalled('printful')) {
            $printfullEnabled = $this->config->get('printful')['status'] ?? 0;
            if ($printfullEnabled == 1) {
                return true;
            }
        }
        return false;
    }
}
