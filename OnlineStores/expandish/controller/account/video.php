<?php 
class ControllerAccountVideo extends Controller { 

	public function index() {

		//If user is not logged in, 
		if (!$this->customer->isLogged()) {
	  		$this->session->data['redirect'] = $this->url->link('account/account', '', 'SSL');
	  
	  		$this->redirect($this->url->link('account/login', '', 'SSL'));
    	} 
	    
	    //If logged but App is not installed..
		$this->load->model('module/product_video_links');
		$is_installed = $this->model_module_product_video_links->isInstalled();
	    
	    if(!$is_installed){
	  		$this->session->data['redirect'] = $this->url->link('error/not_found', '', 'SSL');

            $this->redirect($this->url->link('error/not_found', '' , 'SSL'));
	    }
		
	    //View configuration..
		$this->language->load_json('account/video');
		
		$this->document->setTitle($this->language->get('video_heading_title'));

	    $this->data['breadcrumbs'] = $this->_createBreadcrumbs();

	    $this->_getProductsVideos($this->customer->getId());

	    $this->data['pagination'] = $this->_addPagination();


		//Render view template
        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/video.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/video.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/account/video.expand';
        }
        
        $this->children = array(
            'common/footer',
            'common/header'
        );

		$this->response->setOutput($this->render_ecwig());
	}


	/**
	* Form the breadcrumbs array.
	*
	* @return Array $breadcrumbs
	*/
	private function _createBreadcrumbs(){
		$breadcrumbs = [
		  [
		    'text' => $this->language->get('text_home'),
		    'href' => $this->url->link('common/home'),
        	'separator' => false
		  ],
		  [
		    'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
		  ],
		  [
		    'text' => $this->language->get('video_heading_title'),
			'href'      => $this->url->link('account/video', $url, 'SSL'),
			'separator' => $this->language->get('text_separator')
		  ]
		];

		return $breadcrumbs;
	}

	/**
	* Form the pagination array.
	*
	* @return Array $breadcrumbs
	*/
	private function _addPagination(){
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$pagination = new Pagination();
		$pagination->total = count($this->data['products']);
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('account/video', 'page={page}', 'SSL');

		return $pagination->render();
	}


	private function _getProductsVideos($customer_id){
		$this->load->model('catalog/product');
		$this->data['products'] = $this->model_catalog_product->getProductsWithVideos($customer_id);
	}
}

