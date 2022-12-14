<?php 
class ControllerAffiliateTracking extends Controller { 
	public function index() {
		if (!$this->affiliate->isLogged()) {
	  		$this->session->data['redirect'] = $this->url->link('affiliate/tracking', '', 'SSL');
	  
	  		$this->redirect($this->url->link('affiliate/login', '', 'SSL'));
    	} 
	
		$this->language->load_json('affiliate/tracking');

		$this->document->setTitle($this->language->get('heading_title'));

      	$this->data['breadcrumbs'] = array();

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
        	'separator' => false
      	); 

      	$this->data['breadcrumbs'][] = array(       	
        	'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('affiliate/account', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);

      	$this->data['breadcrumbs'][] = array(       	
        	'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('affiliate/tracking', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);
		
		$this->data['text_description'] = sprintf($this->language->get('text_description'), $this->config->get('config_name'));

    	$this->data['code'] = $this->affiliate->getCode();
		
		$this->data['continue'] = $this->url->link('affiliate/account', '', 'SSL');

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/affiliate/tracking.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/affiliate/tracking.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/affiliate/tracking.expand';
        }
		
		$this->children = array(
			'common/footer',
			'common/header'
		);
				
		$this->response->setOutput($this->render_ecwig());
  	}
	
	public function autocomplete() {
		$json = array();
		
		if (isset($this->request->get['filter_name'])) {
			$this->load->model('catalog/product');
			 
			$data = array(
				'filter_name' => $this->request->get['filter_name'],
				'start'       => 0,
				'limit'       => 20,
                'ignore_lang'  => isset($this->request->get['ignore_lang']) && $this->request->get['ignore_lang'] == true ? true : false

            );
			
			$results = $this->model_catalog_product->getProducts($data);
			
			foreach ($results as $result) {
				$json[] = array(
					'name' => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'link' => str_replace('&amp;', '&', $this->url->link('product/product', 'product_id=' . $result['product_id'] . '&tracking=' . $this->affiliate->getCode()))			
				);	
			}
		}

		$this->response->setOutput(json_encode($json));
	}
}
?>