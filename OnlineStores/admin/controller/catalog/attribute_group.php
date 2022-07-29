<?php

class ControllerCatalogAttributeGroup extends Controller
{
	private $error = array();
   
  	public function index() {
        //$this->language->load('catalog/attribute_group');
        $this->load->language('module/advanced_product_attributes');
    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('catalog/attribute_group');
		
    	$this->getList();
  	}
              
  	public function insert() {
		//$this->language->load('catalog/attribute_group');
        $this->load->language('module/advanced_product_attributes');

        $this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('catalog/attribute_group');
			
		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( ! $this->validateForm() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

      		$this->model_catalog_attribute_group->addAttributeGroup($this->request->post);
		  	
			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';
            $result_json['redirect'] = '1';
            $result_json['to'] = (string)$this->url->link(
                'catalog/attribute_group',
                '',
                'SSL'
            )->format();
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
		}

        $this->data['links'] = [
            'submit' => $this->url->link('catalog/attribute_group/insert', '', 'SSL')
        ];

        $this->data['cancel'] = $this->url->link('catalog/attribute_group', '', 'SSL');
	
    	$this->getForm();
  	}

  	
    public function update()
    {
		$this->language->load('catalog/attribute_group');

        $this->load->language('module/advanced_product_attributes');

        $this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('catalog/attribute_group');
		
    	if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( ! $this->validateForm() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

	  		$this->model_catalog_attribute_group->editAttributeGroup($this->request->get['attribute_group_id'], $this->request->post);
			$result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_success');

            $this->response->setOutput(json_encode($result_json));
            return;
    	}

        $this->data['links'] = [
            'submit' => $this->url->link('catalog/attribute_group/update', 'attribute_group_id=' . $this->request->get['attribute_group_id'], 'SSL')
        ];

        $this->data['cancel'] = $this->url->link('catalog/attribute_group', '', 'SSL');
	
    	$this->getForm();
  	}

  	public function delete() {
		$this->language->load('catalog/attribute_group');
	
    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('catalog/attribute_group');
		
    	if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $attribute_group_id) {
				$this->model_catalog_attribute_group->deleteAttributeGroup($attribute_group_id);
			}
			      		
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			
			$this->redirect($this->url->link('catalog/attribute_group', 'token=' . $this->session->data['token'] . $url, 'SSL'));
   		}
	
    	$this->getList();
  	}
    
  	protected function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
		}
		
		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
				
		$url = '';
			
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
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
			'href'      => $this->url->link('catalog/attribute_group', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
							
		$this->data['insert'] = $this->url->link('catalog/attribute_group/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('catalog/attribute_group/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');
        $this->data['productattr'] = $this->url->link('catalog/attribute', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['attribute_groups'] = array();

		$data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit' => $this->config->get('config_admin_limit')
		);
		
		$attribute_group_total = $this->model_catalog_attribute_group->getTotalAttributeGroups();
	
		$results = $this->model_catalog_attribute_group->getAttributeGroups($data);
 
    	foreach ($results as $result) {
			$action = array();
			
			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('catalog/attribute_group/update', 'token=' . $this->session->data['token'] . '&attribute_group_id=' . $result['attribute_group_id'] . $url, 'SSL')
			);
						
			$this->data['attribute_groups'][] = array(
				'attribute_group_id' => $result['attribute_group_id'],
				'name'               => $result['name'],
				'sort_order'         => $result['sort_order'],
				'selected'           => isset($this->request->post['selected']) && in_array($result['attribute_group_id'], $this->request->post['selected']),
				'action'             => $action
			);
		}	
	
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_no_results'] = $this->language->get('text_no_results');
        $this->data['text_attributes'] = $this->language->get('text_attributes');
        $this->data['text_attrgroups'] = $this->language->get('text_attrgroups');

		$this->data['column_name'] = $this->language->get('column_name');
		$this->data['column_sort_order'] = $this->language->get('column_sort_order');
		$this->data['column_action'] = $this->language->get('column_action');		
		
		$this->data['button_insert'] = $this->language->get('button_insert');
		$this->data['button_delete'] = $this->language->get('button_delete');
 
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

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$this->data['sort_name'] = $this->url->link('catalog/attribute_group', 'token=' . $this->session->data['token'] . '&sort=agd.name' . $url, 'SSL');
		$this->data['sort_sort_order'] = $this->url->link('catalog/attribute_group', 'token=' . $this->session->data['token'] . '&sort=ag.sort_order' . $url, 'SSL');
		
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
												
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $attribute_group_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('catalog/attribute_group', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');
			
		$this->data['pagination'] = $pagination->render();

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

        $this->data['links'] = [
            'attribute' => $this->url->link('catalog/attribute', '', 'SSL')
        ];

		$this->template = 'catalog/attribute_group_list.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
  	}
  
  	protected function getForm() {
     	$this->data['heading_title'] = $this->language->get('heading_title');

    	$this->data['entry_name'] = $this->language->get('entry_name');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

    	$this->data['button_save'] = $this->language->get('button_save');
    	$this->data['button_cancel'] = $this->language->get('button_cancel');
    
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
		
		$url = '';
			
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
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
			'href'      => $this->url->link('catalog/attribute_group', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
		
        $this->data['breadcrumbs'][] = array(
            'text'      => !isset($this->request->get['attribute_group_id']) ? $this->language->get('breadcrumb_insert') : $this->language->get('breadcrumb_update'),
            'href'      => $this->url->link('catalog/attribute_group', 'token=' . $this->session->data['token'] . $url, 'SSL'),
            'separator' => ' :: '
        );

		if (!isset($this->request->get['attribute_group_id'])) {
			$this->data['action'] = $this->url->link('catalog/attribute_group/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('catalog/attribute_group/update', 'token=' . $this->session->data['token'] . '&attribute_group_id=' . $this->request->get['attribute_group_id'] . $url, 'SSL');
		}
			
		$this->data['cancel'] = $this->url->link('catalog/attribute_group', '', 'SSL');

		if (isset($this->request->get['attribute_group_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$attribute_group_info = $this->model_catalog_attribute_group->getAttributeGroup($this->request->get['attribute_group_id']);
		}
				
		$this->load->model('localisation/language');
		
		$this->data['languages'] = $this->model_localisation_language->getLanguages();
		
		if (isset($this->request->post['attribute_group_description'])) {
			$this->data['attribute_group_description'] = $this->request->post['attribute_group_description'];
		} elseif (isset($this->request->get['attribute_group_id'])) {
			$this->data['attribute_group_description'] = $this->model_catalog_attribute_group->getAttributeGroupDescriptions($this->request->get['attribute_group_id']);
		} else {
			$this->data['attribute_group_description'] = array();
		}

		if (isset($this->request->post['sort_order'])) {
			$this->data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($attribute_group_info)) {
			$this->data['sort_order'] = $attribute_group_info['sort_order'];
		} else {
			$this->data['sort_order'] = '';
		}

		$this->template = 'catalog/attribute_group_form.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());	
  	}
  	
	
    private function validateForm()
    {
    	if ( !$this->user->hasPermission('modify', 'catalog/attribute_group') )
        {
      		$this->error['error'] = $this->language->get('error_permission');
    	}
	
    	foreach ( $this->request->post['attribute_group_description'] as $language_id => $value )
        {
      		if ( (utf8_strlen($value['name']) < 3) || (utf8_strlen($value['name']) > 64) )
            {
        		$this->error['name_' . $language_id] = $this->language->get('error_name');
      		}
    	}
		
		if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        
        return $this->error ? false : true;
  	}


  	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'catalog/attribute_group')) {
      		$this->error['warning'] = $this->language->get('error_permission');
    	}

		$this->load->model('catalog/attribute');

		foreach ($this->request->post['selected'] as $attribute_group_id) {
			$attribute_total = $this->model_catalog_attribute->getTotalAttributesByAttributeGroupId($attribute_group_id);

			if($this->config->get('wk_amazon_connector_status')){ 
				$account_total = $this->Amazonconnector->getAccountByAttributeGroupId($attribute_group_id); 
				if($account_total){ 
					$this->error['warning'] = sprintf('This attribute group cannot be deleted as it is currently assigned to %s Amazon accounts! ', $account_total); 
				} 
			}

			if ($attribute_total) {
				$this->error['warning'] = sprintf($this->language->get('error_attribute'), $attribute_total);
			}
	  	}

		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}
  	}

    public function dtDelete()
    {
        $this->language->load('catalog/attribute_group');
        $this->load->model('catalog/attribute_group');
        $this->load->model('catalog/attribute');

        if ( isset($this->request->post["selected"]) )
        {
            $ids = $this->request->post["selected"];
            $total = $this->model_catalog_attribute_group->getTotalAttributeGroups();

            if ( count( $ids ) >= $total )
            {
                $result_json['success'] = '0';
                $result_json['message'] = $this->language->get('error_cant_delete_all_attribute_groups');
                $result_json['type'] = 'error';
                $result_json['title'] = $this->language->get('general_error_text');
                $this->response->setOutput(json_encode($result_json));
                return;
            }

            foreach ( $ids as $id )
            {
                // check if attribute  group included attributes value
                $totalAttributes = $this->model_catalog_attribute->getTotalAttributesByAttributeGroupId($id);
                if ($totalAttributes == '0'){
                    $this->model_catalog_attribute_group->deleteAttributeGroup( $id );
                }else{
                    $result_json['success'] = '0';
                    $result_json['message'] = $this->language->get('error_attribute');
                    $result_json['type'] = 'error';
                    $result_json['title'] = $this->language->get('general_error_text');
                    $this->response->setOutput(json_encode($result_json));
                    return;
                }
            }
            
            $result_json['success'] = '1';
            $result_json['message'] = $this->language->get('text_bulkdelete_success');
            $result_json['type'] = 'success';
            $result_json['title'] = $this->language->get('general_success_text');
        }
        else
        {
            $result_json['success'] = '0';
            $result_json['message'] = $this->language->get('text_bulkdelete_error');
            $result_json['type'] = 'error';
            $result_json['title'] = $this->language->get('general_error_text');
        }

        $this->response->setOutput(json_encode($result_json));
        return;
    }
}
