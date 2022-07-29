<?php
class ControllerModuleStockzones extends Controller {

	private $error = [];

    /**
     * Rendering app settings page for editing
     */
    public function index(){
        $this->load->language('module/stockzones');
        $this->document->setTitle($this->language->get('heading_title'));

        //Breadcrumbs
        $this->data['breadcrumbs'] = $this->_createBreadcrumbs();

        //Get config settings
        $this->data['stockzones'] = $this->config->get('stockzones');

        //render view template
        $this->template = 'module/stockzones/settings.expand';
        $this->children = [ 'common/header' , 'common/footer' ];
        $this->response->setOutput($this->render());
    }

    /**
     * Saving the changes in the app settings page
     */
    public function save(){
        if( $this->_isAjax() && $this->request->server['REQUEST_METHOD'] == 'POST' ) {

            //Validate form fields
            if ( ! $this->_validateForm() ) {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
            }
            else {
                $this->load->model('setting/setting');
                $this->load->language('module/stockzones');

                //Save App settings in settings table
                $this->model_setting_setting->insertUpdateSetting('stockzones', $this->request->post );

                $result_json['success_msg'] = $this->language->get('text_success');
                $result_json['success']     = '1';
                $result_json['redirect'] = '1';
                $result_json['to'] = "module/stockzones";
            }

            $this->response->setOutput(json_encode($result_json));
        }
        else{
            $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
        }
    }

    /**
     * Install module/app
     *
     * @return void
     */
    public function install() {
        $this->load->model('module/stockzones/settings');
        $this->model_module_stockzones_settings->install();
    }


    /**
     * Uninstall module/app
     *
     * @return void
     */
    public function uninstall() {
        $this->load->model('module/stockzones/settings');
        $this->model_module_stockzones_settings->uninstall();
    }


    public function importProducts(){
        $this->load->model('module/stockzones/product');
        $this->load->model('module/stockzones/category');
        $this->load->model('catalog/option');
        
        $stockzonesProducts = $this->model_module_stockzones_product->getstockzonesProducts();

        //if error
        if( isset($stockzonesProducts['error']) ){
            echo $stockzonesProducts['error'];
            return;
        }
        
        $imported_products_count     = 0;
        $not_imported_products_count = 0;

        foreach ($stockzonesProducts as $product) {

            //check if the category of product found, add product
            if( ($category_id = $this->model_module_stockzones_category->findBystockzonesId($product['category_id']) ) > 0 ){

                //check if the product was not added before
                if( !$this->model_module_stockzones_product->findBystockzonesId($product['_id']) ){
                    
                    //Add product options....
                    $options = $this->_getOptionsArray($product['attributes']);
 
                    //Add product
                    $this->model_module_stockzones_product->addProduct([
                        "stockzones_product_id"  => $product["_id"],
                        "parent_id"              => $product["parent_id"],
                        "orignal_variation_id"   => $product["orignal_variation_id"],
                        "slug"                   => $product["slug"],
                        "name"                   => $product["name"],
                        "wholesaler_id"          => $product["wholesaler_id"],
                        "product_category"     => [ $category_id ],
                        "product_description"  => [
                            "1" => [
                                "name" => $product['product_descriptions'][ModelModulestockzonesProduct::ENGLISH_LANGUAGE_CODE]['name'],
                                "description" => $product['product_descriptions'][ModelModulestockzonesProduct::ENGLISH_LANGUAGE_CODE]['description']
                            ],
                            "2" => [
                                "name" => $product['product_descriptions'][ModelModulestockzonesProduct::ARABIC_LANGUAGE_CODE]['name'],
                                "description" => $product['product_descriptions'][ModelModulestockzonesProduct::ARABIC_LANGUAGE_CODE]['description']
                            ],
                        ],
                        "price" => $product["wholesaler_price"],
                        "product_option" => $options
                    ]);
                    $imported_products_count++;
                }
            }
            else{
                $not_imported_products_count++;
            }
        }

        echo $imported_products_count;
    }


    public function importCategories(){
        $this->load->model('module/stockzones/category');
        $stockzonesCategories = $this->model_module_stockzones_category->getstockzonesCategories();

        //if error
        if( isset($stockzonesCategories['error']) ){
            echo $stockzonesCategories['error'];
            return;
        }
        
        $imported_categories_count = 0;

        foreach ($stockzonesCategories as $category) {
            if( !$this->model_module_stockzones_category->findBystockzonesId($category['_id']) ){
                $this->model_module_stockzones_category->addCategory([
                    //Stockzones category table fields...
                    'stockzones_category_id'  => $category['_id'],
                    'stockzones_parent_id'    => $category['parent_id'],
                    'stockzones_name'         => $category['name'],
                    'stockzones_slug'         => $category['slug'],
                    'stockzones_created'   => $category['created'],

                    //ExpandCart Fields...
                    'parent_id'             => $this->model_module_stockzones_category->findBystockzonesId($category['parent_id']),
                    'status'                => 1,
                    'category_description'  => [
                        '1'  =>  [
                            'name' => $category['name'],
                            'description' => $category['name'],
                        ],
                        '2'  =>  [
                            'name' => $category['name'],
                            'description' => $category['name'],
                        ]
                    ],

                ]);
                $imported_categories_count++;
            }
        }
        echo $imported_categories_count;
    }


    public function syncProducts(){
        $this->load->model('module/stockzones/product');

        //delete current stockzones product
        $this->model_module_stockzones_product->deleteStockzonesProducts();

        //Add new stockzones product from API...
        $this->importProducts();
    }


    
	/** Private Methods ***/

    /**
     * Validate form fields.
     *
     * @return bool TRUE|FALSE
     */
    private function _validateForm(){
        $this->load->language('module/stockzones');

        if (!$this->user->hasPermission('modify', 'module/stockzones')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if( !\Extension::isInstalled('stockzones') ){
            $this->error['not_installed'] = $this->language->get('error_not_installed');
        }

        if( utf8_strlen($this->request->post['stockzones']['access_token']) < 60 ){
            $this->error['invalid_access_token_missing'] = $this->language->get('error_invalid_access_token');
        }

        if( utf8_strlen($this->request->post['stockzones']['access_key']) < 32 ){
            $this->error['invalid_access_key_missing'] = $this->language->get('error_invalid_access_key');
        }

        if($this->error && !isset($this->error['error']) ){
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }

    /**
     * Checking if the request coming via ajax request or not.
     */
    private function _isAjax() {

        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    /**
     * Form the breadcrumbs array.
     *
     * @return Array $breadcrumbs
     */
    private function _createBreadcrumbs(){
        return [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', '', 'SSL')
            ],
            [
                'text' => $this->language->get('text_module'),
                'href' => $this->url->link('marketplace/home', '', 'SSL')
            ],
            [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('module/stockzones', '', 'SSL')
            ]
        ];
    }


    /**
     * 
     * @param $product_options : array of the options from stockzones API for a specific product.
     * @return array of options and their values ids in Expandcart system.
     */
    private function _getOptionsArray($product_options){
        $options = [];

        $expandcartOptionsIds = $this->model_catalog_option->getOptionsIds();
        $expandcartOptionsValuesIds = $this->model_catalog_option->getOptionsValuesIds();
        // echo '<pre>'; print_r($expandcartOptionsIds); die('ok');

        foreach ($product_options as $option) {

            // if( ($option_id = $this->model_catalog_option->findOptionByName($option['attribute_name'])) > 0 ){
            if( $option_id = $expandcartOptionsIds[$option['attribute_name']]  ){
                if( $option_value_id = $expandcartOptionsValuesIds[$option['value']] ){
                    // add new value for this option..
                    $option_value_id = $this->model_catalog_option->addOptionValues([
                        'option_id'    => $option_id,
                        'option_value' => [
                            '1' => [
                                'option_value_description' => [
                                    '1' => ['name' => $option['value']],
                                    '2' => ['name' => $option['value']]
                                ]
                            ]
                        ],
                        'option_type' => 'select' // $option['type']
                    ])[0];
                }
            }else{ //not found...add new option & value
                $option_id = $this->model_catalog_option->addOption([
                    'type' => 'select', //$option['type']
                    'option_description' => [
                        '1' => [ 'name' => $option['attribute_name'] ],
                        '2' => [ 'name' => $option['attribute_name'] ]
                    ]
                ]);

                $option_value_id = $this->model_catalog_option->addOptionValues([
                    'option_id'    => $option_id,
                    'option_value' => [
                        '1' => [
                            'option_value_description' => [
                                '1' => ['name' => $option['value']],
                                '2' => ['name' => $option['value']]
                            ]
                        ]
                    ],
                    'option_type' => 'select' // $option['type']
                ])[0];
            }

            $options[$option_id] = [
                "name"      => $option['attribute_name'],
                "option_id" => $option_id,
                "type"      => 'select',
                "product_option_value" => [
                    $option_value_id  => [
                        'option_value_id' => $option_value_id,
                    ]
                ],
                "required" => 0
            ];
        }

        return $options;
    }
}
