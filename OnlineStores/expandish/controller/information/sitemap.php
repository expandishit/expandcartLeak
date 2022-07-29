<?php  
class ControllerInformationSitemap extends Controller {
	public function index() {
    	$this->language->load_json('information/sitemap');
 
		$this->document->setTitle($this->language->get('heading_title')); 

      	$this->data['breadcrumbs'] = array();

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
        	'separator' => false
      	);

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('information/sitemap'),      	
        	'separator' => $this->language->get('text_separator')
      	);	
		

		$this->load->model('catalog/category');
		$this->load->model('catalog/product');
		
		$this->data['categories'] = array();
					
		$categories_1 = $this->model_catalog_category->getCategories(0);
		
		foreach ($categories_1 as $category_1) {
			$level_2_data = array();
			
			$categories_2 = $this->model_catalog_category->getCategories($category_1['category_id']);
			
			foreach ($categories_2 as $category_2) {
				$level_3_data = array();
				
				$categories_3 = $this->model_catalog_category->getCategories($category_2['category_id']);
				
				foreach ($categories_3 as $category_3) {
					$level_4_data = array();
				
					$categories_4 = $this->model_catalog_category->getCategories($category_3['category_id']);
					foreach ($categories_4 as $category_4) {
						$level_4_data[] = array(
							'name' => $category_4['name'],
							'href' => $this->url->link('product/category', 'path=' . $category_1['category_id'] . '_' . $category_2['category_id'] . '_' . $category_3['category_id']. '_' . $category_4['category_id'])
						);
					}

					$level_3_data[] = array(
						'name' => $category_3['name'],
						'children' => $level_4_data,
						'href' => $this->url->link('product/category', 'path=' . $category_1['category_id'] . '_' . $category_2['category_id'] . '_' . $category_3['category_id'])
					);
				}
				
				$level_2_data[] = array(
					'name'     => $category_2['name'],
					'children' => $level_3_data,
					'href'     => $this->url->link('product/category', 'path=' . $category_1['category_id'] . '_' . $category_2['category_id'])	
				);					
			}
			
			$this->data['categories'][] = array(
				'name'     => $category_1['name'],
				'children' => $level_2_data,
				'href'     => $this->url->link('product/category', 'path=' . $category_1['category_id'])
			);
		}

		
		$this->load->model('catalog/information');
		
		$this->data['informations'] = array();
    	
		foreach ($this->model_catalog_information->getInformations() as $result) {
      		$this->data['informations'][] = array(
        		'title' => $result['title'],
        		'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id']) 
      		);
    	}

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/information/sitemap.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/information/sitemap.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/information/sitemap.expand';
        }
		
		$this->children = array(
			'common/footer',
			'common/header'
		);
				
 		$this->response->setOutput($this->render_ecwig());
	}
}
?>