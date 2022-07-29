<?php
class ControllerCatalogCategory extends Controller {
	private $error = array();

	public function index() {
		$this->language->load('catalog/category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/category');

		$this->getList();
	}

	public function insert() {
		$this->language->load('catalog/category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/category');

		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
		{
			if ( !$this->validateForm() )
			{
				$result_json['success'] = '0';
				$result_json['errors'] = $this->error;
				
				$this->response->setOutput(json_encode($result_json));
				
				return;
			}

			$category_id = $this->model_catalog_category->addCategory($this->request->post);

			// Add new category to El-Modaqeq categories
            if( \Extension::isInstalled('elmodaqeq') && $this->config->get('elmodaqeq')['status'] == 1 ) {
                $this->load->model('module/elmodaqeq/category');
                $this->model_module_elmodaqeq_category->addCategory($category_id , $this->request->post);
            }
				// add data to log_history
				$this->load->model('setting/audit_trail');
				$pageStatus = $this->model_setting_audit_trail->getPageStatus("category");
				if($pageStatus){
					$log_history['action'] = 'add';
					$log_history['reference_id'] = $category_id;
					$log_history['old_value'] = NULL;
					$log_history['new_value'] = json_encode($this->request->post,JSON_UNESCAPED_UNICODE);
					$log_history['type'] = 'category';
					$this->load->model('loghistory/histories');
					$this->model_loghistory_histories->addHistory($log_history);
				}

            $inserted_category = $this->model_catalog_category->getCategory($category_id);

			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

			$result_json['success'] = '1';
            $result_json['category_id'] = $category_id;
            $result_json['path'] = $inserted_category['path'];
            $result_json['name'] = $inserted_category['name'];
			$result_json['redirect'] = '1';
            $result_json['to'] = $this->url->link(
                'catalog/component/collection',
                'content_url=catalog/category',
                'SSL'
            )->format();
			
			$this->response->setOutput(json_encode($result_json));
			
			return;
		}

		$this->getForm();
	}

	public function update() {
		$category_id = (int)$this->request->get['category_id'];
        if(!preg_match("/^[1-9][0-9]*$/", $category_id)) return false;

		$this->language->load('catalog/category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/category');
		$category_id = $this->request->get['category_id'];

		if ($this->request->server['REQUEST_METHOD'] == 'POST')
		{
			if ( !$this->validateForm() )
			{
				$result_json['success'] = '0';
				$result_json['error'] = $this->error;
				
				$this->response->setOutput(json_encode($result_json));
				
				return;
			}
			$this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("category");
            if($pageStatus){
				$oldValue['category_info'] = $this->model_catalog_category->getCategory($category_id);
				$oldValue['category_description'] = $this->model_catalog_category->getCategoryDescriptions($category_id);
				    // add data to log_history
					$log_history['action'] = 'update';
					$log_history['reference_id'] = $category_id;
					$log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
					$log_history['new_value'] = json_encode($this->request->post,JSON_UNESCAPED_UNICODE);
					$log_history['type'] = 'category';
					$this->load->model('loghistory/histories');
					$this->model_loghistory_histories->addHistory($log_history);
				}
			$this->model_catalog_category->editCategory($this->request->get['category_id'], $this->request->post);

			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

			$result_json['success'] = '1';
			
			$this->response->setOutput(json_encode($result_json));
			
			return;
		}

		$this->getForm();
	}

	public function delete() {
		$this->language->load('catalog/category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/category');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $category_id) {
				$this->model_catalog_category->deleteCategory($category_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->redirect($this->url->link(
                'catalog/component/collection',
                'content_url=catalog/category&token=' . $this->session->data['token'] . $url,
                'SSL')
            );
		}

		$this->getList();
	}

	public function repair() {
		$this->language->load('catalog/category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/category');

		if ($this->validateRepair()) {
			$this->model_catalog_category->repairCategories();

			$this->session->data['success'] = $this->language->get('text_success');

            $this->redirect($this->url->link(
                'catalog/component/collection',
                'content_url=catalog/category&token=' . $this->session->data['token'] ,
                'SSL')
            );

		}

		$this->getList();
	}

	protected function getList() {
		$this->data['HTTP_IMAGE'] = rtrim(\Filesystem::getUrl(), '/') . '/';
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

   		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('catalog/component/collection', 'content_url=catalog/category&token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);

        $this->data['text_lengthMenu'] = $this->language->get('text_lengthMenu');
        $this->data['text_zeroRecords'] = $this->language->get('text_zeroRecords');
        $this->data['text_info'] = $this->language->get('text_info');
        $this->data['text_infoEmpty'] = $this->language->get('text_infoEmpty');
        $this->data['text_infoFiltered'] = $this->language->get('text_infoFiltered');
        $this->data['text_search'] = $this->language->get('text_search');
		

		 //Get all tax classes
        $this->load->model('localisation/tax_class');
        $this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();


		$this->data['insert'] = $this->url->link('catalog/category/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('catalog/category/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['repair'] = $this->url->link('catalog/category/repair', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['categories'] = array();

		$data = array(
			'start' => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit' => $this->config->get('config_admin_limit')
		);

		$category_total = $this->model_catalog_category->getTotalCategories();

		$results = $this->model_catalog_category->getCategories($data);

		foreach ($results as $result) {
			$action = array();

            $action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('catalog/category/update', 'token=' . $this->session->data['token'] . '&category_id=' . $result['category_id'] . $url, 'SSL'),


            );
            $action[] = array(
                'text' => $this->language->get('text_view'),
                'href' => $this->fronturl->link('product/category' . '&path=' . $result['category_id'] . $url),


            );

			$this->data['categories'][] = array(
				'category_id' => $result['category_id'],
				'name'        => $result['name'],
				'sort_order'  => $result['sort_order'],
				'selected'    => isset($this->request->post['selected']) && in_array($result['category_id'], $this->request->post['selected']),
				'action'      => $action
			);
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_no_results'] = $this->language->get('text_no_results');

		$this->data['column_name'] = $this->language->get('column_name');
		$this->data['column_sort_order'] = $this->language->get('column_sort_order');
		$this->data['column_action'] = $this->language->get('column_action');
        $this->data['column_view'] = $this->language->get('column_view');


        $this->data['button_insert'] = $this->language->get('button_insert');
		$this->data['button_delete'] = $this->language->get('button_delete');
 		$this->data['button_repair'] = $this->language->get('button_repair');

 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		$this->data['all_cats'] = $this->model_catalog_category->getCategories();

		$pagination = new Pagination();
		$pagination->total = $category_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('catalog/component/collection', 'content_url=catalog/category&token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		if($category_total == 0){
            $this->template = 'catalog/category/empty.expand';
        }else{
            $this->template = 'catalog/category_list.expand';
        }

		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	protected function getForm()
	{
 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

 		if (isset($this->error['name'])) {
			$this->data['error_name'] = $this->error['name'];
		} else {
			$this->data['error_name'] = array();
		}

  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('catalog/component/collection', 'content_url=catalog/category&token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => !isset($this->request->get['category_id']) ? $this->language->get('breadcrumb_insert') : $this->language->get('breadcrumb_update'),
			'href'      => $this->url->link('catalog/component/collection', 'content_url=catalog/category&token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);

		if (!isset($this->request->get['category_id'])) {
			$this->data['action'] = $this->url->link('catalog/category/insert', 'token=' . $this->session->data['token'], 'SSL');

        } else {

            $this->data['action'] = $this->url->link('catalog/category/update', 'token=' . $this->session->data['token'] . '&category_id=' . $this->request->get['category_id'], 'SSL');
		}

		$this->data['cancel'] = $this->url->link(
            'catalog/component/collection',
            'content_url=catalog/category&token=' . $this->session->data['token'] ,
            'SSL'
        );

		if (isset($this->request->get['category_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
      		$category_info = $this->model_catalog_category->getCategory($this->request->get['category_id']);
    	}

		$this->data['token'] = $this->session->data['token'];

		$this->load->model('localisation/language');

		$this->data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['category_description'])) {
			$this->data['category_description'] = $this->request->post['category_description'];
		} elseif (isset($this->request->get['category_id'])) {
			$this->data['category_description'] = $this->model_catalog_category->getCategoryDescriptions($this->request->get['category_id']);
		} else {
			$this->data['category_description'] = array();
		}

		if (isset($this->request->post['path'])) {
			$this->data['path'] = $this->request->post['path'];
		} elseif (!empty($category_info)) {
			$this->data['path'] = $category_info['path'];
		} else {
			$this->data['path'] = '';
		}

		if (isset($this->request->post['parent_id'])) {
			$this->data['parent_id'] = $this->request->post['parent_id'];
		} elseif (!empty($category_info)) {
			$this->data['parent_id'] = $category_info['parent_id'];
		} else {
			$this->data['parent_id'] = 0;
		}

		$this->load->model('catalog/filter');

		if (isset($this->request->post['category_filter'])) {
			$filters = $this->request->post['category_filter'];
		} elseif (isset($this->request->get['category_id'])) {
			$filters = $this->model_catalog_category->getCategoryFilters($this->request->get['category_id']);
		} else {
			$filters = array();
		}

		$this->data['category_filters'] = array();

		foreach ($filters as $filter_id) {
			$filter_info = $this->model_catalog_filter->getFilter($filter_id);

			if ($filter_info) {
				$this->data['category_filters'][] = array(
					'filter_id' => $filter_info['filter_id'],
					'name'      => $filter_info['group'] . ' &gt; ' . $filter_info['name']
				);
			}
		}

		$this->data['all_cats'] = $this->model_catalog_category->getCategories();

        /**
         * Remove the current category from parent list.
         * becuase the category can't be parent itself
         */
        if (isset($this->request->get['category_id'])) {
            foreach ($this->data['all_cats'] as $key => $value){
                if($value['category_id'] == $this->request->get['category_id'])
                    unset($this->data['all_cats'][$key]);
            }
        }

		$this->load->model('setting/store');

		$this->data['stores'] = $this->model_setting_store->getStores();

		if (isset($this->request->post['category_store'])) {
			$this->data['category_store'] = $this->request->post['category_store'];
		} elseif (isset($this->request->get['category_id'])) {
			$this->data['category_store'] = $this->model_catalog_category->getCategoryStores($this->request->get['category_id']);
		} else {
			$this->data['category_store'] = array(0);
		}

		if (isset($this->request->post['keyword'])) {
			$this->data['keyword'] = $this->request->post['keyword'];
		} elseif (!empty($category_info)) {
			$this->data['keyword'] = $category_info['keyword'];
		} else {
			$this->data['keyword'] = '';
		}

		if (isset($this->request->post['image'])) {
			$this->data['image'] = $this->request->post['image'];
		} elseif (!empty($category_info)) {
			$this->data['image'] = $category_info['image'];
		} else {
			$this->data['image'] = '';
		}

        if (isset($this->request->post['icon'])) {
            $this->data['icon'] = $this->request->post['icon'];
        } elseif (!empty($category_info)) {
            $this->data['icon'] = $category_info['icon'];
        } else {
            $this->data['icon'] = '';
        }

		$this->load->model('tool/image');

		if (!empty($category_info) && $category_info['image']) {
			$this->data['thumb'] = $this->model_tool_image->resize($category_info['image'], 150, 150);
		} else {
			$this->data['thumb'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);
		}

        if (!empty($category_info) && $category_info['icon']) {
            $this->data['iconthumb'] = $this->model_tool_image->resize($category_info['icon'], 150, 150);
        } else {
            $this->data['iconthumb'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);
        }

		$this->data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);

		if (isset($this->request->post['top'])) {
			$this->data['top'] = $this->request->post['top'];
		} elseif (!empty($category_info)) {
			$this->data['top'] = $category_info['top'];
		} else {
			$this->data['top'] = 0;
		}

		if (isset($this->request->post['column'])) {
			$this->data['column'] = $this->request->post['column'];
		} elseif (!empty($category_info)) {
			$this->data['column'] = $category_info['column'];
		} else {
			$this->data['column'] = 1;
		}

		if (isset($this->request->post['sort_order'])) {
			$this->data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($category_info)) {
			$this->data['sort_order'] = $category_info['sort_order'];
		} else {
			$this->data['sort_order'] = 0;
		}

		if (isset($this->request->post['status'])) {
			$this->data['status'] = $this->request->post['status'];
		} elseif (!empty($category_info)) {
			$this->data['status'] = $category_info['status'];
		} else {
			$this->data['status'] = 1;
		}

		//MS Fee For multiseller
		$this->data['ms_fee'] = [];
		$this->data['ms_active'] = false;

		if (isset($this->request->post['ms_fee'])) {
			$this->data['ms_fee'] = $this->request->post['ms_fee'];
		} elseif (!empty($category_info)) {
			
			//Check if MS & MS Messaging is installed
	        $this->load->model('multiseller/status');
	        $multiseller = $this->model_multiseller_status->is_installed();
	        if($multiseller){
	        	$this->data['ms_active'] = true;
				$this->data['ms_fee'] = unserialize($category_info['ms_fee']);
	        }

		}
		////////////////////////

		///Category Droplist APP
		$this->data['category_droplist'] = 0;
		$category_droplist = $this->config->get('category_droplist');
        
        if(isset($category_droplist) && $category_droplist['status'] == 1){

			if (isset($this->request->post['droplist_show'])) {
				$this->data['droplist_show'] = $this->request->post['droplist_show'];
			}elseif (!empty($category_info)) {
				$this->data['droplist_show'] = $category_info['droplist_show'];
			}

            $this->data['category_droplist'] = 1;
        }
		///////////////////////

		$this->data['leftCurrencySymbol'] = $this->currency->getSymbolLeft();
        $this->data['rightCurrencySymbol'] = $this->currency->getSymbolRight();

		if (isset($this->request->post['category_layout'])) {
			$this->data['category_layout'] = $this->request->post['category_layout'];
		} elseif (isset($this->request->get['category_id'])) {
			$this->data['category_layout'] = $this->model_catalog_category->getCategoryLayouts($this->request->get['category_id']);
		} else {
			$this->data['category_layout'] = array();
		}

		$this->load->model('design/layout');

		$this->data['layouts'] = $this->model_design_layout->getLayouts();

		$this->template = 'catalog/category_form.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	
	private function validateForm()
	{
		$fullCategory=[];

		if ( !$this->user->hasPermission('modify', 'catalog/category') )
		{
			$this->error['error'] = $this->language->get('error_permission');
		}
 
		foreach ( $this->request->post['category_description'] as $language_id => $value )
		{
      if (utf8_strlen(ltrim($value['name'] ," ")) > 255)
			{
				$this->error['name_' . $language_id] = $this->language->get('error_name');
			}

			if($value['name']){
					$fullCategory = $value;
			}
		}

		if(!$fullCategory){
				$this->error['name_' . $language_id] = $this->language->get('error_name');
		}

		if ( $this->error && !isset($this->error['error']) )
		{
		    $this->error['warning'] = $this->language->get('error_warning');
		}
		
		return $this->error ? false : true;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'catalog/category')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

	protected function validateRepair() {
		if (!$this->user->hasPermission('modify', 'catalog/category')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('catalog/category');

			$data = array(
				'filter_name' => trim($this->request->get['filter_name']),
				'start'       => 0,
				'limit'       => 20
			);

			$results = $this->model_catalog_category->getCategories($data);

			foreach ($results as $result) {
				$json[] = array(
					'category_id' => $result['category_id'],
					'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'image' => \Filesystem::getUrl('image/' . $result['image']),
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->setOutput(json_encode($json));
	}

	public function editMainCategory()
    {
        $this->load->model('catalog/category');
        $this->language->load('catalog/category');

		$main_category_id = $this->request->post['main_category'];
		$cats_ids = $this->request->post['cats_ids'];
        //foreach( $this->request->post['cats_ids'] as $cat_id ) {
    	$this->model_catalog_category->updateMainCategory($main_category_id,$cats_ids);
        //}

        $this->response->setOutput( json_encode([
            'status'    => 'success',
            'title'     => $this->language->get('notification_success_title'),
            'message'   => $this->language->get('message_updated_successfully'),
        ]) );

        return;
	}
	
    public function dtHandler() {
        $this->load->model('catalog/category');
        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = !empty($request['start']) ? $request['start']:0;
        $length =!empty($request['length']) ? $request['length']:10 ;


        $columns = array(
            0 => 'id',
            1 => 'name',
        );
        $orderColumn = !empty($columns[$request['order'][0]['column']]) ? $columns[$request['order'][0]['column']] : "name";
        $orderType = $request['order'][0]['dir'];


        //    public function dtHandler($start=0, $length=10, $search = null, $orderColumn="name", $orderType="ASC")
        $return = $this->model_catalog_category->dtHandler($start, $length, $search, $orderColumn, $orderType);

        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        $this->load->model('tool/image');

        foreach ($records as $key => $record) {
            if($record['image']==""){
                $records[$key]['image'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);
            }
            else{
                $records[$key]['image'] = $this->model_tool_image->resize($record['image'], 150, 150);
            }
        }

        $json_data = array(
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data" => $records
        );
        $this->response->setOutput(json_encode($json_data));
        return;
    }

    public function dtDelete()
    {
        $this->load->model("catalog/category");
        if(isset($this->request->post["ids"])) {
            $ids = $this->request->post["ids"];
            foreach ($ids as $id) {
				$this->load->model('setting/audit_trail');
				$pageStatus = $this->model_setting_audit_trail->getPageStatus("category");
				if($pageStatus){
					$oldValue['category_info'] = $this->model_catalog_category->getCategory($id);
					$oldValue['category_description'] = $this->model_catalog_category->getCategoryDescriptions($id);
					 // add data to log_history

					 $log_history['action'] = 'delete';
					 $log_history['reference_id'] =$id;
					 $log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
					 $log_history['new_value'] = NULL;
					 $log_history['type'] = 'category';
					 $this->load->model('loghistory/histories');
					 $this->model_loghistory_histories->addHistory($log_history);
					}
                $this->model_catalog_category->deleteCategory($id);
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

    public function dtChangeStatus()
    {
        $this->load->model("catalog/category");
        if(isset($this->request->post["id"]) && isset($this->request->post["status"])) {
            $id = $this->request->post["id"];
            $status = $this->request->post["status"];

            $categoryData = $this->model_catalog_category->getCategory($id);
            $categoryData['category_description'] = $this->model_catalog_category->getCategoryDescriptions($id);
            $categoryData["status"] = $status;
            $this->model_catalog_category->editCategory($id, $categoryData, true);

            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_langstatus_success');
        } else {
            $result_json['success'] = '0';
            $result_json['error'] = $this->language->get('text_langstatus_error');
        }

        $this->response->setOutput(json_encode($result_json));
        return;
    }

    //// Update Categories slug
    public function updateCategoriesSlug()
    {   
        echo 'Updating......';
        $this->load->model('catalog/category');
        $all_descriptions = $this->model_catalog_category->getCategoriesDescription();
        
        foreach ($all_descriptions as $desc) {
            if(!$desc['slug']){
               $this->model_catalog_category->slugUpdate($desc['category_id'], $desc['language_id'], $desc['name']); 
            }
        }

        echo 'Done.';
    }
}