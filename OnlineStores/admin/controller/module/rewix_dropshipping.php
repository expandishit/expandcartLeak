<?php
use pcrov\JsonReader\JsonReader;

class ControllerModuleRewixDropshipping extends Controller
{  
    const LOG_DIR = BASE_STORE_DIR .'logs/';

	private $error = [];

    public function __construct($registry){
        parent::__construct($registry);

        //
        if (!is_dir(self::LOG_DIR)) {
          // dir doesn't exist, make it
          mkdir(self::LOG_DIR);
        }
    }

	public function index()
	{  
        $this->load->language('module/rewix_dropshipping');
        $this->document->setTitle($this->language->get('heading_title'));

		//Breadcrumbs
        $this->data['breadcrumbs'] = $this->_createBreadcrumbs();

        //Get config settings
        $this->data['rewix_dropshipping'] = $this->config->get('rewix_dropshipping');

        if(($images_count = count(json_decode(file_get_contents(self::LOG_DIR.'imagesurlsrewix.json'), true))) >= 1)
            $this->data['images_count'] = $images_count;
		
        $this->data['chunk_size'] = 400;
        //render view template
		$this->template = 'module/rewix_dropshipping/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

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
                $this->load->language('module/rewix_dropshipping');

                //Save App settings in settings table
                $this->model_setting_setting->insertUpdateSetting('rewix_dropshipping', $this->request->post );

                $result_json['success_msg'] = $this->language->get('text_success');
                $result_json['success']     = '1';
                $result_json['redirect'] = '1';
                $result_json['to'] = "module/rewix_dropshipping";
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
        $this->load->model('module/rewix_dropshipping/settings');
        $this->model_module_rewix_dropshipping_settings->install();
    }


    /**
     * Uninstall module/app
     *
     * @return void
     */
    public function uninstall() {
        $this->load->model('module/rewix_dropshipping/settings');
        $this->model_module_rewix_dropshipping_settings->uninstall();
    }

	public function importProducts()
	{

		$this->load->model('module/rewix_dropshipping/product');
		$response = $this->model_module_rewix_dropshipping_product->getProducts(); 
        if($response['status_code'] == 200){
			$products = [];
            $productsTotalCount = 0;

        	$reader = new JsonReader();
			$reader->json($response['result']);
			$chunkSize = 100; $counter = 0; //$max = 200;
			$reader->read("pageItems");

			$reader->read(); // Begin array

			while($reader->type() === JsonReader::OBJECT && $counter < $chunkSize /*&& $max > 0*/) {
			    $products[] = $reader->value();
			    $counter++;
                if($counter >= $chunkSize){
                    $this->model_module_rewix_dropshipping_product->insertBulkProducts($products);
                    $counter = 0;
                    $products = [];
                }
			    $reader->next();
                // $max--;
			}
            $reader->close();

            //Adding the remaining products less than chunksize
            if($counter > 0){
                $this->model_module_rewix_dropshipping_product->insertBulkProducts($products);
            }

            $images = $this->model_module_rewix_dropshipping_product->getImages();
            // $images['count'] = count($images);

            file_put_contents(self::LOG_DIR . 'imagesurlsrewix.json', json_encode($images));
            // $this->model_module_rewix_dropshipping_product->downloadImages($images, 5);

            $this->response->setOutput( json_encode([
                'images_count'   => count($images),
                'total_inserted' => $this->model_module_rewix_dropshipping_product->getTotalInserted()
            ]));            
        }
        else{
        	echo json_encode([
                'error' => $response['result']
            ]);
        }
	}

    /**
     * Download part of the images only because Rewix doesn't allow alot of requests 
     *  
     * 
     */
    public function downloadImages()
    {
        header('Content-type: application/json');

        $chunkSize = $this->request->post['chunk_size'] ?: 200;
        $images = json_decode(file_get_contents(self::LOG_DIR.'imagesurlsrewix.json'), true);

        //Get first chunk..
        $toBeDownloaded = array_slice($images, 0, $chunkSize);
        $this->load->model('module/rewix_dropshipping/product');
        $failed = $this->model_module_rewix_dropshipping_product->downloadImages($toBeDownloaded, 5);
        array_splice($images, 0, $chunkSize, $failed);

        file_put_contents(self::LOG_DIR.'imagesurlsrewix.json', json_encode($images));

        $this->response->setOutput( json_encode([
            'remaining' => count($images),
            'times'     => ceil(count($images)/$chunkSize),
            'total'     => count($images)
        ]));
        return;
    }

    public function syncProducts(){
        $this->load->model('module/rewix_dropshipping/product');

        //delete current rewix_dropshipping product
        $this->model_module_rewix_dropshipping_product->deleteProducts();

        //Add new rewix_dropshipping product from API...
        // $this->importProducts();
    }

	/** Private Methods ***/

    /**
     * Validate form fields.
     *
     * @return bool TRUE|FALSE
     */
    private function _validateForm(){

        $this->load->language('module/rewix_dropshipping');

        if (!$this->user->hasPermission('modify', 'module/rewix_dropshipping')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if( !\Extension::isInstalled('rewix_dropshipping') ){
            $this->error['not_installed'] = $this->language->get('error_not_installed');
        }

        if( utf8_strlen($this->request->post['rewix_dropshipping']['api_key']) < 36 ){
            $this->error['invalid_api_key_missing'] = $this->language->get('error_invalid_api_key');
        }

        if( utf8_strlen($this->request->post['rewix_dropshipping']['password']) < 6 ){
            $this->error['invalid_password_missing'] = $this->language->get('error_invalid_password');
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
                'href' => $this->url->link('module/rewix_dropshipping', '', 'SSL')
            ]
        ];
    }
}
