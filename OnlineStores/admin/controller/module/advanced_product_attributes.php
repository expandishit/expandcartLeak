<?php
class ControllerModuleAdvancedProductAttributes extends Controller {

	private $error = [];

	public function index(){
		$this->load->model('module/advanced_product_attributes/settings');
        $this->load->language('module/advanced_product_attributes');

        //Breadcrumbs
	    $this->data['breadcrumbs'] = $this->_createBreadcrumbs();

        //Get config settings
        $this->data['advanced_product_attribute_status']  = $this->config->get('advanced_product_attribute_status');

		//render view template
		$this->template = 'module/advanced_product_attributes/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
	}

	/**
	* Update Module Settings in DB settings table.
	*
	* @return JSON response.
	*/
	public function update(){
	 if( $this->_isAjax() && $this->request->server['REQUEST_METHOD'] == 'POST' ) {

	      //Validate form fields
	      if ( ! $this->_validateForm() ){
	        $result_json['success'] = '0';
	        $result_json['errors'] = $this->error;
	      }
	      else{
	        $this->load->model('setting/setting');
	        $this->load->language('module/advanced_product_attributes');

	        //Insert a record of quick gatway in the extension table in DB if not any
	        $this->model_setting_setting->checkIfExtensionIsExists('module', 'advanced_product_attributes', TRUE);

	        //Save module status & order status in settings table
	        $this->model_setting_setting->editSetting('advanced_product_attributes', $this->request->post );

	        $result_json['success_msg'] = $this->language->get('text_success');

	        $result_json['success']  = '1';
	      }

	      $this->response->setOutput(json_encode($result_json));
	    }
	    else{
	      $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
	    }
	}

	/**
	* Validate form fields.
	*
	* @return bool TRUE|FALSE
	*/
	private function _validateForm(){
		$this->load->language('module/advanced_product_attributes');

		if (!$this->user->hasPermission('modify', 'module/advanced_product_attributes')) {
		    $this->error['warning'] = $this->language->get('error_permission');
		}

		if($this->error && !isset($this->error['error']) ){
		  $this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
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
		    'href' => $this->url->link('common/dashboard', '', 'SSL')
		  ],
		  [
		    'text' => $this->language->get('text_module'),
		    'href' => $this->url->link('marketplace/home', '', 'SSL')
		  ],
		  [
		    'text' => $this->language->get('heading_title'),
		    'href' => $this->url->link('module/advanced_product_attributes', '', 'SSL')
		  ]
		];

		return $breadcrumbs;
	}

	/**
	* Check if comming response in AJAX or not.
	*
	* @return bool TRUE|FALSE
	*/
	private function _isAjax() {

    	return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
	}


	/**
	* Install module/app - add new column in Product_attribute table 'advanced_product_attribute_id' text column.
	*
	* @return void
	*/
	public function install() {
        $this->load->model('module/advanced_product_attributes/settings');
        $this->model_module_advanced_product_attributes_settings->install();
	}


	/**
	* Uninstall module/app - drop column 'advanced_product_attribute_id' in Product_attribute table .
	*
	* @return void
	*/
	public function uninstall() {
        $this->load->model('module/advanced_product_attributes/settings');
        $this->model_module_advanced_product_attributes_settings->uninstall();
	}

	/* /End of Settings Methods */







	/************** Attributes Methods ******************/

	/**
	* list advanced attributes
	*/
	public function attributes(): void
    {
	  $this->load->language('module/advanced_product_attributes');
	  $this->load->model('module/advanced_product_attributes/attribute');
	  $this->document->setTitle($this->language->get('heading_title'));

	  $this->data['attributes'] = $this->model_module_advanced_product_attributes_attribute->get(['order'=>'name','direction'=>'ASC']);

	  //Breadcrumbs
	  $this->data['breadcrumbs'] = $this->_createBreadcrumbs();

	  //render view template
	  $this->template = 'module/advanced_product_attributes/attribute/index.expand';
	  $this->children = [ 'common/header' , 'common/footer' ];

	  $this->response->setOutput($this->render());
	}


	public function deleteAttribute(){
		$this->load->language('module/advanced_product_attributes');
   		$this->load->model('module/advanced_product_attributes/attribute');

        if(isset($this->request->post["selected"])) {
            $ids = $this->request->post["selected"];

            $error = $this->validateDelete($ids);

            if ( $error !== true )
            {
                $result_json['success'] = '0';
                $result_json['error'] = $error;
                $this->response->setOutput(json_encode($result_json));
                return;
            }

            foreach ($ids as $id) {
                $this->model_module_advanced_product_attributes_attribute->delete($id);
            }
            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_bulkdelete_success');
        } else {
            $result_json['success'] = '0';
            $result_json['error'] = $this->language->get('text_bulkdelete_error');
        }

        $this->response->setOutput(json_encode($result_json));
        return;
    }

    protected function validateDelete($ids){
		if (!$this->user->hasPermission('modify', 'module/advanced_product_attributes/attribute'))
        {
            return array('error' => $this->language->get('error_permission'));
    	}

		$this->load->model('module/advanced_product_attributes/attribute');

		foreach ($ids as $advanced_attribute_id)
        {
			$product_total = $this->model_module_advanced_product_attributes_attribute->getTotalProductsByAttributeId($advanced_attribute_id);

			if ($product_total)
            {
                return sprintf( $this->language->get('error_product'), $product_total );
			}
	  	}

		return true;
  	}


	public function insertAttribute(){
		$this->load->language('module/advanced_product_attributes');
    $this->load->model('module/advanced_product_attributes/attribute');
		$this->document->setTitle($this->language->get('heading_title'));

		if ( $this->request->server['REQUEST_METHOD'] == 'POST' ){

          if ( ! $this->_validateAttributeForm() ){
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;

                $this->response->setOutput(json_encode($result_json));
                return;
          }
          $attributeId = $this->model_module_advanced_product_attributes_attribute->add($this->request->post);

				if($attributeId){
		  		$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
				  $result_json['success'] = '1';
					$result_json['redirect'] = '1';
          $result_json['to'] = (string)$this->url->link('module/advanced_product_attributes/attributes','','SSL')->format();
		  	}else{
		  		$this->error['warning'] = 'Exists!';
		  		$result_json['success'] = '0';
          $result_json['errors'] = $this->error;
		  	}

        $this->response->setOutput(json_encode($result_json));

        return;
		}

    	$this->getForm();
  	}


  	public function updateAttribute() {
	    $this->load->language('module/advanced_product_attributes');
	    $this->load->model('module/advanced_product_attributes/attribute');
	    $this->document->setTitle($this->language->get('heading_title'));

    	if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

    		if ( ! $this->_validateAttributeForm() ){
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                $this->response->setOutput(json_encode($result_json));
                return;
    		}

            $attribute_info = $this->model_module_advanced_product_attributes_attribute->getSimpleAttribute($this->request->get['advanced_attribute_id']);

    		if(count($attribute_info) > 0){
                $updated = $this->model_module_advanced_product_attributes_attribute->editSimpleAttribute($this->request->get['advanced_attribute_id'], $this->request->post);
            }else{
                $updated = $this->model_module_advanced_product_attributes_attribute->edit($this->request->get['advanced_attribute_id'], $this->request->post);
            }

    		if($updated){
    		    $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
    		    $result_json['success'] = '1';
			  }else{
			  		$this->error['warning'] = 'Exists!';
			  		$result_json['success'] = '0';
	          $result_json['errors'] = $this->error;
			  }

	        $this->response->setOutput(json_encode($result_json));
	        return;
    	}

			$this->getForm();
  	}

	protected function getForm() {

		$this->data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		$this->data['error_name'] = isset($this->error['name']) ? $this->error['name'] : [];

		$this->data['breadcrumbs'] = $this->_createBreadcrumbs();

		$this->data['action'] = !isset($this->request->get['advanced_attribute_id']) ? $this->url->link('module/advanced_product_attributes/insertAttribute', '', 'SSL') : $this->url->link('module/advanced_product_attributes/updateAttribute', 'advanced_attribute_id=' . $this->request->get['advanced_attribute_id'], 'SSL');
		$this->data['cancel'] = $this->url->link('module/advanced_product_attributes/attributes', '', 'SSL');

		$this->load->model('localisation/language');
		$this->data['languages'] = $this->model_localisation_language->getLanguages();

		$this->load->model('catalog/attribute_group');
		$this->data['attribute_groups'] = $this->model_catalog_attribute_group->getAttributeGroups();

		$this->data['types'] = [ 'text', 'single_select', 'multi_select' ];

		if (isset($this->request->get['advanced_attribute_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$attribute_info  = $this->model_module_advanced_product_attributes_attribute->get([], $this->request->get['advanced_attribute_id']);
            $count = count($attribute_info);
			$attribute_info['values'] = $this->model_module_advanced_product_attributes_attribute->getValues($this->request->get['advanced_attribute_id']);
            if ($count == 0){
                $attribute_info = $this->model_module_advanced_product_attributes_attribute->getSimpleAttribute($this->request->get['advanced_attribute_id']);
            }
		}

		if (isset($this->request->post['attribute_description'])) {
			$this->data['attribute_description'] = $this->request->post['attribute_description'];
		} elseif (isset($this->request->get['advanced_attribute_id'])) {
            $attribute_description= $this->model_module_advanced_product_attributes_attribute->getAttributeDescriptions($this->request->get['advanced_attribute_id']);

            if (empty($attribute_description)){
                $attribute_description= $this->model_module_advanced_product_attributes_attribute->getSimpleAttributeDescriptions($this->request->get['advanced_attribute_id']);
            }
            $this->data['attribute_description'] =$attribute_description;
		} else {
			$this->data['attribute_description'] = [];
		}

		if (isset($this->request->post['attribute_group_id'])) {
			$this->data['attribute_group_id'] = $this->request->post['attribute_group_id'];
		} elseif (!empty($attribute_info)) {
			$this->data['attribute_group_id'] = $attribute_info['attribute_group_id'];
		} else {
			$this->data['attribute_group_id'] = '';
		}

		if (isset($this->request->post['sort_order'])) {
			$this->data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($attribute_info)) {
			$this->data['sort_order'] = $attribute_info['sort_order'];
		} else {
			$this->data['sort_order'] = '';
		}

		if (isset($this->request->post['attribute_type'])) {
			$this->data['attribute_type'] = $this->request->post['attribute_type'];
		} elseif (!empty($attribute_info)) {
			$this->data['attribute_type'] = $attribute_info['type'];
		} else {
			$this->data['attribute_type'] = 'text';
		}


		if (isset($this->request->post['glyphicon'])) {
			$this->data['glyphicon'] = $this->request->post['glyphicon'];
		} elseif (!empty($attribute_info)) {
			$this->data['glyphicon'] = $attribute_info['glyphicon'];
		} else {
			$this->data['glyphicon'] = 'fa fa-check-circle';
		}

        $this->data['first_key_attribute_values']=0;

		if (isset($this->request->post['attribute_values'])) {
			$this->data['attribute_values'] = $this->request->post['attribute_values'];
		} elseif (!empty($attribute_info)) {
			$this->data['attribute_values'] = $attribute_info['values'];
			if(is_array($attribute_info['values'])){
                $this->data['first_key_attribute_values']= (int)array_key_first($attribute_info['values']);
            }
        } else {
			$this->data['attribute_values'] = [];
		}

		$this->template = 'module/advanced_product_attributes/attribute/form.expand';
		$this->children = [ 'common/header' , 'common/footer' ];

		$this->response->setOutput($this->render());
  	}

  	private function _validateAttributeForm(){
    	if ( !$this->user->hasPermission('modify', 'module/advanced_product_attributes') )
        {
      		$this->error['error'] = $this->language->get('error_permission');
    	}

    	foreach ( $this->request->post['attribute_description'] as $language_id => $value )
        {
            if ((utf8_strlen(ltrim($value['name'] ," "))< 2) || (utf8_strlen(ltrim($value['name'] ," ")) > 64))
            {
        		$this->error['name_' . $language_id] = $this->language->get('error_name');
      		}
    	}




        if( in_array($this->request->post['type'], ['single_select', 'multi_select']) ){
        	if( !isset($this->request->post['attribute_values']) || empty($this->request->post['attribute_values']) ){
      			$this->error['error'] = $this->language->get('error_');
        	}
        	else{
	      		foreach ( $this->request->post['attribute_values'] as $attribute_value ){
					foreach ($attribute_value as $language_id => $value_description) {
			            if ( utf8_strlen($value_description['value']) < 4 )
			            {
			        		$this->error['value_'] = 'insert attribute values in all languages';
			      		}
		      		}
		    	}
        	}
        }


		if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return $this->error ? false : true;
  	}



	public function groupedAutocomplete() {

	    if (isset($this->request->get['filter_name'])) {
	        $this->load->model('module/advanced_product_attributes/attribute');

	        $data = [
	            'attribute_name' => $this->request->get['filter_name'],
	            'start'       => 0,
	            'limit'       => 20
	        ];

	        $results = $this->model_module_advanced_product_attributes_attribute->getGroupedAttributes($data);

	        $attributes = [];
	        foreach ($results as $result) {
	            $attribute = [];

	            $attribute['text'] = $result['group_name'];

	            foreach ($result['advanced_attributes'] as $advanced_attribute) {
	                $attribute['children'][] = [
	                    'id'         => $advanced_attribute['advanced_attribute_id'],
	                    'text'       => $advanced_attribute['attribute_name'],
	                    'type'       => $advanced_attribute['attribute_type'],
	                    'group_name' => $result['group_name']
	                ];
	            }

	            $attributes[] = $attribute;
	        }

	    }
	    $this->response->setOutput(json_encode(['results' => $attributes]));
	}

	public function getAttributeValues(){
		$advanced_attribute_id = $this->request->get['advanced_attribute_id'];

		if( !$advanced_attribute_id ) return;

        $this->load->model('module/advanced_product_attributes/attribute');

        $result = $this->model_module_advanced_product_attributes_attribute->getAttributeValuesCurrentLanguage($advanced_attribute_id);
        $this->response->setOutput(json_encode($result));
	}
}
