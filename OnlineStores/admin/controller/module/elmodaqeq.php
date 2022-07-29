<?php
ini_set("display_errors", 0);

/**
 * ElModaqeq Settings Page Controller class
 *
 * @author Amira Sabry <amira.sabry@expandcart.com>
 */
class ControllerModuleElModaqeq extends Controller
{
    /**
     * @var array the validation errors array.
     */
    private $error = [];

	public function index()
	{
    	$this->load->language('module/elmodaqeq');

    	//Get config settings
        $this->data['elmodaqeq'] = $this->config->get('elmodaqeq');

        $this->data['breadcrumbs'] = $this->_createBreadcrumbs();

    	/*prepare settings.expand view data*/
	    $this->document->setTitle($this->language->get('heading_title'));
	    $this->template = 'module/elmodaqeq/settings.expand';
	    $this->children = ['common/header', 'common/footer'];
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
                $this->load->language('module/elmodaqeq');

                //Save App settings in settings table
                $this->model_setting_setting->insertUpdateSetting('elmodaqeq', $this->request->post );

                $result_json['success_msg'] = $this->language->get('text_success');
                $result_json['success']     = '1';
                $result_json['redirect'] = '1';
                $result_json['to'] = "module/elmodaqeq";
            }

            $this->response->setOutput(json_encode($result_json));
        }
        else{
            $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
        }
    }

	public function install()
	{
		$this->load->model('module/elmodaqeq/settings');
		$this->model_module_elmodaqeq_settings->install();
	}

	public function uninstall()
	{
		$this->load->model('module/elmodaqeq/settings');
		$this->model_module_elmodaqeq_settings->uninstall();
	}


	public function importProducts()
	{
        $this->load->model('module/elmodaqeq/product');
        $result = $this->model_module_elmodaqeq_product->getProducts();
        if( $result['success'] == 1){
            $products = $this->model_module_elmodaqeq_product->getNewProducts($result['products']);
            $this->model_module_elmodaqeq_product->insertBulkProducts($products);
            echo count($products);            
        }else{
            echo $result['message'] ?: $this->language->get('error_login_failed');
        }
	}

    public function importCategories()
    {
        $this->load->model('setting/setting');
        $this->load->model('module/elmodaqeq/category');
        $result = $this->model_module_elmodaqeq_category->getCategories();
        if( $result['success'] == 1 ) {
            $categories = $this->model_module_elmodaqeq_category->getNewCategories($categories);        
            $this->model_module_elmodaqeq_category->insertBulkCategories($categories);
            
            $settings = $this->config->get('elmodaqeq');    
            if( count($categories) > 0 ) {
                $settings['categories_inserted'] = 1;
                $this->model_setting_setting->insertUpdateSetting('elmodaqeq', [ 'elmodaqeq' => $settings ] );
            }

            echo json_encode([
                'categories_inserted' => $settings['categories_inserted'],
                'count' => count($categories)
            ]);
        }else{
            echo json_encode([
                'error_message' => $result['message'] ?: $this->language->get('error_login_failed'),
                'count' => 0
            ]);            
        }   
    }
	
	public function syncProducts()
	{
		$this->load->model('module/elmodaqeq/product');
        $result = $this->model_module_elmodaqeq_product->getProducts();
        if( $result['success'] == 1){
            //Update existed ones (price, quanitity, barcode, name ,description, category)
            $existed_products = $this->model_module_elmodaqeq_product->getExistedProducts($result['products']);
            $this->model_module_elmodaqeq_product->updateBulkProducts($existed_products);

            //Add new ones
            $new_products = $this->model_module_elmodaqeq_product->getNewProducts($result['products']);
            $this->model_module_elmodaqeq_product->insertBulkProducts($new_products);

            echo json_encode([
                'existed_products' => count($existed_products),
                'new_products' => count($new_products)
            ]);       
        }else{
            echo json_encode([
                'error_message' => $result['message'] ?: $this->language->get('error_login_failed'),
                'count' => 0
            ]);
        }
	}

    public function deleteProducts()
    {
        $this->load->model('module/elmodaqeq/product');
        echo $this->model_module_elmodaqeq_product->deleteProducts();
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
                'href' => $this->url->link('module/elmodaqeq', '', 'SSL')
            ]
        ];
    }

    public function _validateForm()
    {
        $this->load->language('module/elmodaqeq');

        if (!$this->user->hasPermission('modify', 'module/elmodaqeq')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if( !\Extension::isInstalled('elmodaqeq') ){
          $this->error['not_installed'] = $this->language->get('error_not_installed');
        }

        if( utf8_strlen($this->request->post['elmodaqeq']['username']) < 1 ){
          $this->error['error_username'] = $this->language->get('error_username');
        }

        if( utf8_strlen($this->request->post['elmodaqeq']['password']) < 1 ){
          $this->error['error_password'] = $this->language->get('error_password');
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
}

